<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики удаления и восстановления записи.
 *
 * @category  ZFE
 */
trait ZFE_Controller_AbstractResource_Delete
{
    /**
     * Возможность удаления объектов.
     *
     * @var bool
     */
    protected static $_canDelete = true;

    /**
     * Удаление объекта.
     *
     * @param bool|string $redirectUrl Адрес для перенаправления; если адрес равен FALSE, то перенаправление не происходит
     *
     * @throws Zend_Controller_Action_Exception
     * @throws ZFE_Controller_Exception
     *
     * @return bool|void В случае отсутствия перенаправления, возвращает TRUE или FALSE в зависимости от успеха удаления
     */
    public function deleteAction($redirectUrl = null)
    {
        if ( ! in_array('delete', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "delete" does not exist', 404);
        }

        if ( ! static::$_canDelete) {
            throw new ZFE_Controller_Exception('Невозможно удалить ' . mb_strtolower($modelName::$nameSingular) . ': доступ запрещен', 403);
        }

        $modelName = static::$_modelName;

        /** @var $item AbstractRecord */
        $item = $modelName::find($this->getParam('id'));
        if (empty($item)) {
            throw new Zend_Controller_Action_Exception($modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'), 404);
        }

        try {
            $item->delete();

            $msg = $modelName::decline('%s успешно удален.', '%s успешно удалена.', '%s успешно удалено.');
            if ($item->canUndeleted()) {
                $msg .= ' <a href="' . $item->getUndeleteUrl() . '">Отменить удаление?</a>';
            }

            if ($this->_request->isXmlHttpRequest()) {
                $this->_json(self::STATUS_SUCCESS, [], $msg);
            }

            $this->_helper->Notices->ok($msg);

            if (false !== $redirectUrl) {
                if (null === $redirectUrl) {
                    if ($upData = $this->view->hopsHistory()->getUpData()) {
                        $redirectUrl = $upData['url'];
                    } else {
                        $redirectUrl = $modelName::getIndexUrl();
                    }
                }
                $this->_redirect($redirectUrl);
            } else {
                return true;
            }
        } catch (Exception $ex) {
            $msg = 'Не удалось удалить ' . mb_strtolower($modelName::$nameSingular);
            if (Zend_Registry::get('user')->noticeDetails) {
                $msg .= ': ' . $ex->getMessage();
            }

            if ($this->_request->isXmlHttpRequest()) {
                $this->_json(self::STATUS_FAIL, [], $msg);
            }

            $this->_helper->Notices->err($msg);

            if ($log = Zend_Registry::get('log')) {
                $log->log(
                    $ex->getMessage(),
                    Zend_Log::ERR,
                    [
                        'errno' => $ex->getCode(),
                        'file' => $ex->getFile(),
                        'line' => $ex->getLine(),
                        'context' => $ex->getTraceAsString(),
                    ]
                );
            }

            if (false !== $redirectUrl) {
                if (null === $redirectUrl) {
                    $redirectUrl = $item->getEditUrl() . $this->view->hopsHistory()->getSideHash('?');
                }
                $this->_redirect($redirectUrl);
            } else {
                return false;
            }
        }
    }

    /**
     * Восстановление объекта.
     *
     * @param bool|string $redirectUrl Адрес для перенаправления; если адрес равен FALSE, то перенаправление не происходит
     *
     * @throws Zend_Controller_Action_Exception
     * @throws ZFE_Controller_Exception
     *
     * @return bool Возвращает TRUE или FALSE в зависимости от успеха восстановления
     */
    public function undeleteAction($redirectUrl = null)
    {
        if ( ! in_array('undelete', static::$_enableActions, true)) {
            throw new Zend_Controller_Action_Exception('Action "undelete" does not exist', 404);
        }

        if ( ! static::$_canRestore) {
            throw new ZFE_Controller_Exception('Невозможно восстановить ' . mb_strtolower($modelName::$nameSingular) . ': доступ запрещен', 403);
        }

        $modelName = static::$_modelName;

        /** @var $item AbstractRecord */
        $item = $modelName::hardFind($this->getParam('id'));
        if (empty($item)) {
            throw new Zend_Controller_Action_Exception($modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'), 404);
        }

        try {
            $item->undelete();

            $msg = $modelName::decline('%s успешно восстановлен.', '%s успешно восстановлена.', '%s успешно восстановлено.');

            if ($this->_request->isXmlHttpRequest()) {
                $this->_json(self::STATUS_SUCCESS, [], $msg);
            }

            $this->_helper->Notices->ok($msg);

            if (false !== $redirectUrl) {
                if (null === $redirectUrl) {
                    $redirectUrl = $item->getEditUrl() . $this->view->hopsHistory()->getSideHash('?');
                }
                $this->_redirect($redirectUrl);
            } else {
                return true;
            }
        } catch (Exception $ex) {
            $msg = 'Не удалось восстановить ' . mb_strtolower($modelName::$nameSingular);
            if (Zend_Registry::get('user')->noticeDetails) {
                $msg .= ': ' . $ex->getMessage();
            }

            if ($this->_request->isXmlHttpRequest()) {
                $this->_json(self::STATUS_FAIL, [], $msg);
            }

            $this->_helper->Notices->err($msg);

            if ($log = Zend_Registry::get('log')) {
                $log->log(
                    $ex->getMessage(),
                    Zend_Log::ERR,
                    [
                        'errno' => $ex->getCode(),
                        'file' => $ex->getFile(),
                        'line' => $ex->getLine(),
                        'context' => $ex->getTraceAsString(),
                    ]
                );
            }

            if (false !== $redirectUrl) {
                if (null === $redirectUrl) {
                    if ($upData = $this->view->hopsHistory()->getUpData()) {
                        $redirectUrl = $upData['url'];
                    } else {
                        $redirectUrl = $modelName::getIndexUrl();
                    }
                }
                $this->_redirect($redirectUrl);
            } else {
                return false;
            }
        }
    }
}
