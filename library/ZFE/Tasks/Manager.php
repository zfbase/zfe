<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Менеджер отложенных задач.
 */
class ZFE_Tasks_Manager
{
    /**
     * @var ZFE_Tasks_Manager
     */
    protected static $instance = null;

    /**
     * Получить экземпляр менеджера отложенных задач.
     *
     * @return ZFE_Tasks_Manager
     */
    public static function getInstance(?Zend_Config $config = null, Zend_Log $logger = null)
    {
        if (static::$instance === null) {
            $config = $config ?? Zend_Registry::get('config');
            static::$instance = new static($config, $logger);
        }

        return static::$instance;
    }

    /**
     * Зарегистрированные обработчики.
     *
     * Список обработчиков для регистрации задаётся в конфигурации.
     * Пример перечисления обработчиков в application.ini:
     * tasks.performers[] = "Task_Example"
     *  
     * @var array|string[]|ZFE_Tasks_Performer[]
     */
    protected $performers = [];

    /**
     * Логгер.
     *
     * @var $logger Zend_Log
     */
    protected $logger;

    /**
     * Конструктор.
     *
     * @throws ZFE_Tasks_Exception
     */
    protected function __construct(Zend_Config $config, Zend_Log $logger = null)
    {
        if (empty($config->tasks) || empty($config->tasks->performers)) {
            throw new ZFE_Tasks_Exception('В конфигурации не перечислены исполнители задач: tasks.performers');
        }

        foreach ($config->tasks->performers as $performerClassName) {
            if (is_a($performerClassName, ZFE_Tasks_Performer::class, true)) {
                $performerCode = $performerClassName::getCode();
                if (array_key_exists($performerCode, $this->performers)) {
                    throw new ZFE_Tasks_Exception("Исполнитель задач с кодом {$performerCode} уже зарегистрирован");
                }
                $this->performers[$performerCode] = $performerClassName;
            } else {
                throw new ZFE_Tasks_Exception("Класс ${performerClassName} не является классом-исполнителем");
            }
        }

        if ($logger) {
            $this->logger = $logger;
        }
    }

    /**
     * Получить список исполнителей задач.
     * 
     * @param $init вернуть инициализированные исполнители?
     * 
     * @return array|string[]              массив классов исполнителей
     * @return array|ZFE_Tasks_Performer[] массив инициализированных исполнителей
     */
    public function getPerformers(bool $init = false) : array
    {
        return array_map(function ($performer) {
            if ($init) {
                return is_string($performer) ? $performer::factory() : $performer;
            } else {
                return is_string($performer) ? $performer : get_class($performer);
            }
        }, $this->performers);
    }

    /**
     * Записать в лог.
     *
     * @return ZFE_Tasks_Manager
     */
    protected function log(string $message, int $level = Zend_Log::INFO)
    {
        if ($this->logger) {
            $this->logger->log($message, $level);
        }

        return $this;
    }

    /**
     * Найти все повторные задачи для указанной.
     */
    public function findAllRevisionsFor(Tasks $task): ?Doctrine_Collection
    {
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from('Tasks x')
            ->where('x.parent_id = ?', $task->parent_id ?: $task->id)
            ->andWhere('x.revision > ?', $task->revision)
            ->orderBy('x.id DESC')
        ;
        $tasks = $q->execute();
        return $tasks->count() ? $tasks : null;
    }

    /**
     * Для данной записи найди задачу, которая уже запланирована, но еще не выполнена.
     * 
     * Важно! Может вернуть задачу, время исполнения которой по расписанию еще не наступило.
     */
    public function findOnePlanned(string $code, int $relatedId): ?Tasks
    {
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from('Tasks x')
            ->where('x.related_id = ?', $relatedId)
            ->addWhere('x.performer_code = ?', $code)
            ->addWhere('x.state = ?', Tasks::STATE_TODO)
            ->orderBy('x.datetime_created ASC')
            ->limit(1)
        ;
        return $q->fetchOne() ?: null;
    }

    /**
     * Найти все задачи для выполнения.
     */
    public function findAllToDo(int $limit = 100): Doctrine_Collection_OnDemand
    {
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from('Tasks x')
            ->where('x.state = ?', Tasks::STATE_TODO)
            ->addWhere('x.datetime_schedule IS NULL OR (x.datetime_schedule IS NOT NULL AND x.datetime_schedule < NOW())')
            ->orderBy('x.datetime_created ASC')
            ->limit($limit)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ON_DEMAND)
        ;
        return $q->execute();
    }

    /**
     * Найти последнюю задачу.
     */
    public function getLastTask(string $code, int $relatedId)
    {
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from('Tasks x')
            ->where('x.related_id = ?', $relatedId)
            ->addWhere('x.performer_code = ?', $code)
            ->orderBy('x.datetime_created ASC')
            ->limit(1)
        ;
        return $q->fetchOne() ?: null;
    }

    /**
     * Подобрать исполнителя для запланированной задачи.
     *
     * @throws ZFE_Tasks_Exception
     */
    public function assign(Tasks $task): ZFE_Tasks_Performer
    {
        $code = $task->performer_code;
        if (!array_key_exists($code, $this->performers)) {
            throw new ZFE_Tasks_Exception("Для задачи с кодом [{$code}] не задан исполняющий класс");
        }

        $performer = $this->performers[$code];
        return is_string($performer) ? $performer::factory() : $performer;
    }

    /**
     * Выполнить задачи.
     *
     * Главный метод, который:
     * 1. перебирает данные задачи
     * 2. определяет исполнителя для каждой
     * 3. передает задачу на исполнение
     * 4. сохраняет результат
     *
     * @return Количество успешно выполненных задач.
     */
    final public function manage(Doctrine_Collection_OnDemand $tasks): int
    {
        $managed = 0;

        foreach ($tasks as $task) {  /** @var Tasks $task */
            if ($task->state != Tasks::STATE_TODO) {
                continue;
            }

            try {
                $performer = $this->assign($task);
            } catch (ZFE_Tasks_Exception $e) {
                $this->log($e->getMessage(), Zend_Log::ERR);
                continue;
            }

            try {
                $task->perform();
                $performer->perform($task->related_id);

                $task->done();
                $this->log("Task #{$task->id} performed successfully");

                $managed++;
            } catch (ZFE_Tasks_Performer_Exception $e) {
                $task->errors = $e->getMessage();
                $task->save();

                $this->log("Task #{$task->id} performed with error: {$e->getMessage()}");
            }
        }

        return $managed;
    }

    /**
     * Запланировать задачу.
     * 
     * @param $performerClass   класс исполнителя
     * @param $related          объект исполнения
     * @param $scheduleDateTime срок начала исполнения (не раньше, но можно позднее)
     */
    public function plan(string $performerClass, AbstractRecord $related, DateTime $scheduleDateTime = null): Tasks
    {
        if (!is_a($performerClass, ZFE_Tasks_Performer::class, true)) {
            throw new ZFE_Tasks_Exception("{$performerClass} не является классом исполнителя");
        }

        $performerCode = $performerClass::getCode();
        if (!array_key_exists($performerCode, $this->performers)) {
            throw new ZFE_Tasks_Exception("Исполнителя с кодом {$performerCode} не зарегистрировано");
        }

        $relatedId = $related->id;
        if (!$performerClass::checkRelated($related)) {
            $relatedClass = $related::class();
            throw new ZFE_Tasks_Exception(
                "Объект исполнения {$relatedClass}#{$relatedId} не поддерживается исполнителем {$performerClass}"
            );
        }

        $task = $this->findOnePlanned($performerCode, $relatedId);
        if ($task === null) {
            $task = new Tasks;
            $task->performer_code = $performerCode;
            $task->related_id = $relatedId;
        }

        if ($scheduleDateTime) {
            $task->datetime_schedule = $scheduleDateTime->format('Y-m-d');
        }

        $task->save();
        return $task;
    }

    /**
     * Запланировать повторное выполнение задачи.
     *
     * @throws ZFE_Tasks_Exception
     *
     * @param $force запланировать даже если она успешно выполнена.
     */
    public function revision(Tasks $task, bool $force = false): Tasks
    {
        if ($task->state == Tasks::STATE_PERFORM) {
            throw new ZFE_Tasks_Exception('Невозможно перезапустить задачу во время её выполнения.');
        }

        if ($task->errors || $force) {
            if ($task->state == Tasks::STATE_TODO) {
                $task->cancel();
            }

            $taskRevision = new Tasks;
            $taskRevision->performer_code = $task->performer_code;
            $taskRevision->related_id = $task->related_id;
            $taskRevision->parent_id = $task->parent_id ?: $task->id;
            $taskRevision->revision = $task->revision + 1;
            $taskRevision->save();
            return $taskRevision;
        }

        if ($task->state == Tasks::STATE_DONE) {
            throw new ZFE_Tasks_Exception('Задача была выполнена успешно, доработка невозможна');
        }

        throw new ZFE_Task_Exception('Задача еще не выполнена, перезапуск не возможен.');
    }
}
