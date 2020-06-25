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
     */
    public static function getInstance(): ZFE_Tasks_Manager
    {
        if (static::$instance === null) {
            static::$instance = new static();
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
     * Конструктор.
     *
     * @throws ZFE_Tasks_Exception
     */
    protected function __construct()
    {
        $config = Zend_Registry::get('config');
        if (empty($config->tasks->performers) || !is_iterable($config->tasks->performers)) {
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
    }

    /**
     * Получить список исполнителей задач.
     * 
     * @param $init вернуть инициализированные исполнители?
     * 
     * @return array|string[]              массив классов исполнителей
     * @return array|ZFE_Tasks_Performer[] массив инициализированных исполнителей
     */
    public function getPerformers(bool $init = false): array
    {
        return array_map(function ($performer) use ($init) {
            if ($init) {
                return is_string($performer) ? $performer::factory() : $performer;
            } else {
                return is_string($performer) ? $performer : get_class($performer);
            }
        }, $this->performers);
    }

    /**
     * Получить исполнителя задачи по коду.
     * 
     * @param $init вернуть инициализированного исполнителя?
     * 
     * @return string              класс исполнителя
     * @return ZFE_Tasks_Performer инициализированный исполнитель
     */
    public function getPerformer(string $code, bool $init = false)
    {
        if (!array_key_exists($code, $this->performers)) {
            throw new ZFE_Tasks_Exception("Исполнитель с кодом {$code} не зарегистрирован");
        }

        $performer = $this->performers[$code];

        if ($init) {
            return is_string($performer) ? $performer::factory() : $performer;
        } else {
            return is_string($performer) ? $performer : get_class($performer);
        }
    }

    /**
     * Найти все повторные задачи для указанной с любым статусом.
     *
     * @return Doctrine_Collection<Tasks>
     */
    public function findAllRevisionsFor(Tasks $task): ?Doctrine_Collection
    {
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from('Tasks x')
            ->where('x.parent_id = ?', $task->parent_id ?: $task->id)
            ->andWhere('x.revision > ?', $task->revision)
            ->orderBy('x.datetime_created DESC')
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
            ->addWhere('x.datetime_started IS NULL')
            ->addWhere('x.datetime_done IS NULL')
            ->addWhere('x.return_code IS NULL')
            ->addWhere('x.datetime_canceled IS NULL')
            ->orderBy('x.priority ASC')
            ->addOrderBy('x.datetime_created ASC')
            ->limit(1)
        ;
        return $q->fetchOne() ?: null;
    }

    /**
     * Найти последнюю задачу.
     *
     * Позволяет проверить текущий статус выполнения операции без привязки к конкретной задачи.
     */
    public function getLastTask(string $code, int $relatedId): ?Tasks
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
     * Найти все задачи для выполнения в порядке убывания приоритета.
     * 
     * @return Doctrine_Collection_OnDemand<Tasks>
     */
    public function findAllToDo(int $limit = 100): Doctrine_Collection_OnDemand
    {
        $q = ZFE_Query::create()
            ->select('x.*')
            ->from('Tasks x')
            ->where('x.datetime_started IS NULL')
            ->addWhere('x.datetime_done IS NULL')
            ->addWhere('x.return_code IS NULL')
            ->addWhere('x.datetime_canceled IS NULL')
            ->addWhere('x.datetime_schedule IS NULL OR (x.datetime_schedule IS NOT NULL AND x.datetime_schedule < NOW())')
            ->orderBy('x.priority ASC')
            ->addOrderBy('x.datetime_created ASC')
            ->limit($limit)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ON_DEMAND)
        ;
        return $q->execute();
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
     * @param array|Tasks[]|Doctrine_Collection<Tasks>|Doctrine_Collection_OnDemand<Tasks> $tasks
     *
     * @return Количество успешно выполненных задач.
     */
    final public function manage($tasks, Zend_Log $logger = null): int
    {
        if (!is_array($tasks) && !($tasks instanceof Doctrine_Collection) && !($tasks instanceof Doctrine_Collection_OnDemand)) {
            throw new ZFE_Tasks_Exception(
                'Выполнить можно задачи только в коллекции Doctrine_Collection или Doctrine_Collection_OnDemand либо массиве'
            );
        }

        $managed = 0;

        foreach ($tasks as $task) {  /** @var Tasks $task */
            if (!($task instanceof ZFE_Model_Default_Tasks)) {
                throw new ZFE_Tasks_Exception('Выполняемые задачи должны быть наследниками ZFE_Model_Default_Tasks');
            }

            if (!$task->inTodo()) {
                continue;
            }

            try {
                $performer = $this->assign($task);
            } catch (ZFE_Tasks_Exception $e) {
                ZFE::popupException($e);
                $this->logHelper($logger, $e->getMessage(), Zend_Log::ERR);
                continue;
            }

            try {
                $this->logHelper($logger, "Start task # {$task->id}");
                $task->perform();

                $resultCode = $performer->perform($task->related_id, $logger);

                $task->done($resultCode);
                $this->logHelper($logger, "Task #{$task->id} performed successfully");

                $managed++;
            } catch (ZFE_Tasks_Performer_Exception $e) {
                ZFE::popupException($e);

                $task->errors = $e->getMessage();
                $task->save();

                $this->logHelper($logger, "Task #{$task->id} performed with error: {$e->getMessage()}");
            }
        }

        return $managed;
    }

    /**
     * Записать в лог.
     *
     * @return ZFE_Tasks_Manager
     */
    protected function logHelper(?Zend_Log $logger, string $message, int $level = Zend_Log::INFO)
    {
        if ($logger) {
            $logger->log($message, $level);
        }

        return $this;
    }

    /**
     * Запланировать задачу.
     * 
     * @param $performerCode    код исполнителя
     * @param $related          объект исполнения
     * @param $scheduleDateTime срок начала исполнения (не раньше, но можно позднее)
     * @param $priority         приоритет (чем выше, тем раньше выполнится)
     */
    public function plan(
        string $performerCode,
        AbstractRecord $related,
        DateTime $scheduleDateTime = null,
        int $priority = 0
    ): Tasks
    {
        if (!array_key_exists($performerCode, $this->performers)) {
            throw new ZFE_Tasks_Exception("Исполнитель с кодом {$performerCode} не зарегистрирован");
        }

        $performerClass = $this->getPerformer($performerCode, false);
        $relatedId = $related->id;
        if (!$performerClass::checkRelated($related)) {
            $relatedClass = get_class($related);
            throw new ZFE_Tasks_Exception(
                "Объект исполнения {$relatedClass} #{$relatedId} не поддерживается исполнителем {$performerClass}"
            );
        }

        $task = $this->findOnePlanned($performerCode, $relatedId);
        if ($task === null) {
            $task = new Tasks;
            $task->performer_code = $performerCode;
            $task->related_id = $relatedId;
            $task->priority = $priority;
            $task->datetime_created = new Doctrine_Expression('NOW()');
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
     */
    public function revision(Tasks $task): Tasks
    {
        if ($task->isPerformed()) {
            throw new ZFE_Tasks_Exception('Невозможно перезапустить задачу во время её выполнения.');
        }

        if (!$task->isDone()) {
            throw new ZFE_Tasks_Exception('Задача еще не выполнена, перезапуск не возможен.');
        }

        if ($task->inTodo()) {
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
}
