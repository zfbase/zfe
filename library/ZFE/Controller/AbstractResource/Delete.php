<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные обработчики удаления и восстановления записи.
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
     * @return bool|void В случае отсутствия перенаправления, возвращает TRUE или FALSE в зависимости от успеха удаления
     */
    public function deleteAction($redirectUrl = null)
    {
        $modelName = static::$_modelName;

        if (!in_array('delete', static::$_enableActions)) {
            $this->abort(404);
        }

        if (!static::$_canDelete) {
            $this->abort(403, 'Невозможно удалить ' . mb_strtolower($modelName::$nameSingular) . ': доступ запрещен');
        }

        /** @var AbstractRecord $item */
        $item = $this->_loadItemOrFall();

        try {
            $item->delete();

            $msg = $modelName::decline('%s успешно удален.', '%s успешно удалена.', '%s успешно удалено.');
            if ($item->canUndeleted()) {
                $msg .= ' <a href="' . $item->getUndeleteUrl() . '">Отменить удаление?</a>';
            }
            $this->success($msg, false !== $redirectUrl);

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
        } catch (Throwable $ex) {
            $this->error('Не удалось удалить ' . mb_strtolower($modelName::$nameSingular), $ex, false !== $redirectUrl);

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
     * @return bool Возвращает TRUE или FALSE в зависимости от успеха восстановления
     */
    public function undeleteAction($redirectUrl = null)
    {
        $modelName = static::$_modelName;

        if (!in_array('undelete', static::$_enableActions)) {
            $this->abort(404);
        }

        if (!static::$_canRestore) {
            $this->abort(403, 'Невозможно восстановить ' . mb_strtolower($modelName::$nameSingular) . ': доступ запрещен');
        }

        /** @var AbstractRecord $item */
        $item = $modelName::hardFind($this->getParam('id'));
        if (empty($item)) {
            $this->abort(404, $modelName::decline('%s не найден.', '%s не найдена.', '%s не найдено.'));
        }

        try {
            $item->undelete();

            $this->success(
                $modelName::decline('%s успешно восстановлен.', '%s успешно восстановлена.', '%s успешно восстановлено.'),
                false !== $redirectUrl
            );

            if (false !== $redirectUrl) {
                if (null === $redirectUrl) {
                    $redirectUrl = $item->getEditUrl() . $this->view->hopsHistory()->getSideHash('?');
                }
                $this->_redirect($redirectUrl);
            } else {
                return true;
            }
        } catch (Throwable $ex) {
            $this->error('Не удалось восстановить ' . mb_strtolower($modelName::$nameSingular), $ex, false !== $redirectUrl);

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
