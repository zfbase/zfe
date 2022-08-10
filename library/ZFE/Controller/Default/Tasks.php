<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Управление отложенными задачами.
 */
class ZFE_Controller_Default_Tasks extends Controller_AbstractResource
{
    protected static $_modelName = Tasks::class;
    protected static $_searchFormName = ZFE_Form_Default_Search_Tasks::class;
    protected static $_canCreate = false;

    /**
     * Построитель Doctrine DQL-запроса.
     */
    protected static $_doctrineQueryBuilder = ZFE_Searcher_Default_TasksDoctrine::class;

    public static function getSearcher()
    {
        if (!static::$_searcher) {
            static::$_searcher = new ZFE_Searcher_Doctrine(static::$_modelName);
            static::$_searcher->setQueryBuilder(new static::$_doctrineQueryBuilder(static::$_modelName));
        }
        return static::$_searcher;
    }

    /**
     * Перезапустить задачу.
     */
    public function restartAction()
    {
        /** @var Tasks $task */
        $task = $this->_loadItemOrFall();

        if (!$task->getChild() || $this->getParam('force')) {
            try {
                $child = ZFE_Tasks_Manager::getInstance()->revision($task);
                $this->_json(static::STATUS_SUCCESS, [
                    'id' => $child->id,
                    'created' => $child->datetime_created,
                    'revision' => $child->revision,
                ]);
            } catch (Throwable $e) {
                $this->error("Не удалось перезапустить задачу #{$task->id}", $e);
            }
        } else {
            $this->warning('Задача уже была перезапущена');
        }

        $this->redirect(Tasks::getIndexUrl());
    }

    /**
     * Убрать отсрочку задачи.
     */
    public function clearScheduleAction()
    {
        /** @var Tasks $task */
        $task = $this->_loadItemOrFall();
        if ($task->inTodo()) {
            try {
                $task->datetime_schedule = null;
                $task->save();

                $this->success("Убрана отсрочка исполнения задачи #{$task->id}");
            } catch (Throwable $e) {
                $this->error("Не удалось убрать отсрочку исполнения задачи #{$task->id}", $e);
            }
        } else {
            $this->error('Убрать отсрочку можно только у задач ожидающих исполнения');
        }

        $this->redirect(Tasks::getIndexUrl());
    }
}
