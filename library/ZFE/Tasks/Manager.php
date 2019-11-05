<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Менеджер отложенных задач.
 */
class ZFE_Tasks_Manager
{
    const STATE_TODO = 0;
    const STATE_DONE = 10;

    /**
     * @var ZFE_Tasks_Manager
     */
    protected static $instance = null;

    /**
     * @param Zend_Config $cfg
     *
     * @return ZFE_Tasks_Manager
     */
    public static function getInstance(Zend_Config $cfg = null)
    {
        if (static::$instance === null) {
            $config = $cfg ?? Zend_Registry::get('config');
            static::$instance = new static($config);
        }

        return static::$instance;
    }

    /** @var Zend_Config */
    protected $config;

    /** @var array */
    protected $performers;

    /** @var $logger Zend_Log */
    protected $logger;

    /**
     * ZFE_Tasks_Manager constructor.
     *
     * @param Zend_Config $cfg
     *
     * @throws ZFE_Tasks_Exception
     */
    protected function __construct(Zend_Config $cfg)
    {
        $this->config = $cfg->tasks;

        if (empty($this->config->performers)) {
            throw new ZFE_Tasks_Exception('В конфигурации не перечислены исполнители задач: tasks.performers');
        }

        foreach ($this->config->performers as $performerClassName) {
            $performer = new $performerClassName;
            if ($performer instanceof ZFE_Tasks_Performer) {
                $this->performers[$performer->getCode()] = $performer;
            } else {
                throw new ZFE_Tasks_Exception("Класс ${performerClassName} не является классом-исполнителем");
            }
        }
    }

    /**
     * Получить список исполнителей задач
     * @return array
     */
    public function getPerformers() : array
    {
        return $this->performers;
    }

    /**
     * @param Zend_Log $logger
     *
     * @return $this
     */
    public function setLogger(Zend_Log $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param $message
     * @param int $level
     *
     * @return $this
     */
    protected function log($message, $level = Zend_Log::INFO)
    {
        if ($this->logger) {
            $this->logger->log($message, $level);
        }
        return $this;
    }

    /**
     * Найти все повторные задачи для указанной.
     *
     * @param ZFE_Model_Default_Tasks $task
     *
     * @return Doctrine_Collection|null
     */
    public function findAllRevisionsFor(ZFE_Model_Default_Tasks $task)
    {
        $table = Doctrine_Core::getTable('Tasks');
        $q = $table->createQuery('x')
            ->select('x.*')
            ->where('x.id != ?', $task->id)
            ->orderBy('x.id DESC')
        ;

        $treeObject = $table->getTree();
        $treeObject->setBaseQuery($q);
        $tree = $treeObject->fetchTree();
        if (!($tree instanceof Doctrine_Collection)) {
            return null;
        }

        return $tree;
    }

    /**
     * Для данной записи найди задачу, которая уже запланирована, но еще не выполнена.
     *
     * @param Doctrine_Record $record
     * @param string          $code
     *
     * @return null|ZFE_Model_Default_Tasks
     */
    public function findOnePlanned(Doctrine_Record $record, string $code)
    {
        $table = Doctrine_Core::getTable('Tasks');
        $q = $table->createQuery('x')
            ->where('x.related_id = ?', $record->id)
            ->addWhere('x.performer_code = ?', $code)
            ->addWhere('x.state = ?', static::STATE_TODO)
            ->limit(1)
            ->orderBy('x.id asc')
        ;
        $task = $q->fetchOne();
        return $task ?: null;
    }

    /**
     * Найти все задачи для выполнения.
     *
     * @param int  $limit
     * @param bool $onDemand возвращать ленивую коллекцию?
     *
     * @return Doctrine_Collection_OnDemand|Doctrine_Collection
     */
    public function findAllToDo(int $limit = 100, $onDemand = true)
    {
        $limit = min(100, $limit);

        $table = Doctrine_Core::getTable('Tasks');
        $q = $table->createQuery('x')
            ->where('x.state = ?', static::STATE_TODO)
            ->addWhere('x.scheduled_at is null OR (x.scheduled_at is not null AND x.scheduled_at < NOW())')
            ->orderBy('x.datetime_created ASC')
            ->limit($limit)
        ;
        if ($onDemand) {
            $q->setHydrationMode(Doctrine_Core::HYDRATE_ON_DEMAND);
        }

        return $q->execute();
    }

    /**
     * Подобрать исполнителя для запланированной задачи.
     *
     * @param ZFE_Model_Default_Tasks $task
     *
     * @throws ZFE_Tasks_Exception
     *
     * @return ZFE_Tasks_Performer
     */
    public function assign(ZFE_Model_Default_Tasks $task)
    {
        $code = $task->getPerformerCode();
        if (!array_key_exists($code, $this->performers)) {
            throw new ZFE_Tasks_Exception("Для задачи с кодом [{$code}] не задан исполняющий класс");
        }

        return $this->performers[$code];
    }

    /**
     * Главный метод, который:
     * 1. перебирает данные задачи
     * 2. определяет исполнителя для каждой
     * 3. передает задачу на исполнение
     * 4. сохраняет результат
     *
     * Возвращает кол-во успешно выполненных задач.
     *
     * @param Doctrine_Collection_OnDemand $tasks
     *
     * @return int
     */
    final public function manage(Doctrine_Collection_OnDemand $tasks)
    {
        $managed = 0;
        foreach ($tasks as $task) {  /** @var ZFE_Model_Default_Tasks $task */
            if ($task->getState() === static::STATE_DONE) {
                continue;
            }

            try {
                $performer = $this->assign($task);
            } catch (ZFE_Tasks_Exception $e) {
                $this->log($e->getMessage(), Zend_Log::ERR);
                continue;
            }

            try {
                $performer->perform($task->getRelatedId());

                $task = $this->finish($task);
                $this->log("{$task->id} performed successfully");

                $managed++;
            } catch (ZFE_Tasks_Performer_Exception $e) {
                $task->setErrors($e->getMessage());
                $task->save();

                $this->log("{$task->id} performed with error: {$e->getMessage()}");
            }
        }

        return $managed;
    }

    /**
     * Запланировать задачу для данной записи.
     *
     * @param Doctrine_Record     $record
     * @param ZFE_Tasks_Performer $performer
     * @param DateTime|null       $scheduleDateTime
     *
     * @return null|Tasks
     */
    public function plan(
        Doctrine_Record $record,
        ZFE_Tasks_Performer $performer,
        DateTime $scheduleDateTime = null
    ) {
        $task = $this->findOnePlanned($record, $performer->getCode());
        if ($task === null) {
            $task = new Tasks;
            $task->setPerformerCode($performer->getCode());
            $task->setRelatedId($record->id);
            $task->saveAsRoot();
        }

        if ($scheduleDateTime) {
            $task->setScheduledAt($scheduleDateTime);
            $task->save();
        }

        return $task;
    }

    /**
     * Запланировать повторное выполнение задачи.
     *
     * Запланировать повторное выполнение успешно выполненной задачи
     * возможно только с параметром $force.
     *
     * @param ZFE_Model_Default_Tasks $task
     * @param bool                    $force
     *
     * @throws ZFE_Tasks_Exception
     *
     * @return Tasks
     */
    public function revision(ZFE_Model_Default_Tasks $task, $force = false)
    {
        if (!empty($task->getErrors()) || $force) {
            $this->finish($task);

            $taskRevision = new Tasks;  /** @var ZFE_Model_Default_Tasks $task */
            $taskRevision->saveAsChildOf($task);
            return $taskRevision;
        }

        throw new ZFE_Tasks_Exception('Задача была выполнена успешно, доработка невозможна');
    }

    /**
     * Сохранить задачу как выполненную.
     *
     * @param ZFE_Model_Default_Tasks $task
     *
     * @return ZFE_Model_Default_Tasks
     */
    public function finish(ZFE_Model_Default_Tasks $task)
    {
        $task->setDoneAt(new Doctrine_Expression('NOW()'));
        $task->setState(static::STATE_DONE);
        $task->save();

        return $task;
    }
}
