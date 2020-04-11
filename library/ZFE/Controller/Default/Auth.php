<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Контроллер авторизации.
 */
class ZFE_Controller_Default_Auth extends Controller_Abstract
{
    /**
     * Дополнительное условие проверки учетных данных.
     *
     * Ограничение по delete не обязательно, т.к. Doctrine и так добавляет его во все запросы,
     * но учитывая, что это дополнение запросов может отключаться, лучше его указывать явно
     *
     * @var string
     */
    protected static $_credentialTreatmentAdditional = 'AND status = 0 AND deleted = 0';

    /**
     * Вход в систему.
     */
    public function loginAction()
    {
        $auth = Zend_Auth::getInstance();

        // Страница доступна только для неавторизованных пользователей.
        // Авторизованных сразу перенаправляем на внутреннюю по умолчанию.
        if ($auth->hasIdentity()) {
            $module = $this->getRequest()->getModuleName();
            $uri = $module ? '/' . $module . '/' : '/';
            $this->_redirect($uri);
        }

        if ('show' !== $this->getParam('develtoolbar')) {
            $this->view->develToolbar = false;
        }

        $form = new Application_Form_Login();
        $requestUri = $this->_request->getRequestUri();

        if ($this->getRequest()->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                if (!empty($formData['remember'])) {
                    Zend_Session::rememberMe();
                }

                $authAdapter = $this->getAuthAdapter($formData);
                $result = $auth->authenticate($authAdapter);

                if (!$result->isValid()) {
                    $this->view->error = 'Неверный логин или пароль';
                } else {
                    $data = $authAdapter->getResultRowObject();
                    $auth->getStorage()->write(['id' => $data->id]);

                    $this->onAuthSuccess($data);

                    // Исполнение требования принудительной смены пароля
                    if (!empty($data->request_password_change)) {
                        $passwordCheck = $this->_getCheckPasswordSession($data->id);
                        $passwordCheck->setExpirationSeconds(120);
                        $passwordCheck->code = uniqid();

                        $fpcUrl = '/auth/force-password-change';
                        if (!empty($requestUri) && '/auth/logout' !== $requestUri) {
                            $fpcUrl .= '/redirect/' . urlencode($requestUri);
                        }
                        $this->_redirect($fpcUrl);
                    }

                    if (empty($requestUri) || '/auth/' === mb_substr($requestUri, 0, 6)) {
                        $requestUri = '/';
                    }
                    if ($this->getParam('redirect')) {
                        $requestUri = $this->getParam('redirect');
                    }

                    $this->_redirect($requestUri);
                }
            } else {
                // Вполне достаточно сообщения только о первой ошибке
                foreach ($form->getMessages() as $errors) {
                    foreach ($errors as $error) {
                        $this->view->error = $error;

                        break 2;
                    }
                }
            }
        }

        $this->view->form = $form;
        $this->view->redirect = $requestUri;
    }

    /**
     * Получить настроенный адаптер авторизации.
     *
     * @return Zend_Auth_Adapter_Interface
     */
    protected function getAuthAdapter($data)
    {
        $tableConn = Doctrine_Core::getConnectionByTableName('editors');
        $authAdapter = new ZFE_Auth_Adapter_Doctrine($tableConn);
        $authAdapter->setTableName('editors')
            ->setIdentityColumn(Editors::$identityColumn)
            ->setCredentialColumn('password')
            ->setCredentialTreatment(Editors::$credentialTreatment . ' ' . self::$_credentialTreatmentAdditional)
            ->setIdentity($data['login'])
            ->setCredential($data['password'])
        ;
        return $authAdapter;
    }

    /**
     * Выход из системы.
     */
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();

        $this->_redirect('/');
    }

    /**
     * Смена роли в рамках текущего сеанса.
     */
    public function setRoleAction()
    {
        $auth = Zend_Auth::getInstance();

        if (!Zend_Registry::get('user')->canSwitchRoles) {
            $this->abort(403, 'Изменение роли запрещено');
        }

        $role = $this->getParam('role');
        if (in_array($role, array_keys(Editors::$roles))) {
            if ($auth->hasIdentity()) {
                $storage = $auth->getStorage();
                $data = $storage->read();
                $data['role'] = $role;
                $storage->write($data);
            }
        }

        $referer = $this->_request->getHeader('referer');
        if (empty($referer)) {
            $referer = '/';
        }
        $this->_redirect($referer);
    }

    /**
     * Принудительная смена пароля пользователя.
     */
    public function forcePasswordChangeAction()
    {
        $userId = Zend_Registry::get('user')->data->id;
        $passwordCheck = (bool) $this->_getCheckPasswordSession($userId)->code;

        $this->view->form = $form = new ZFE_Form_Default_ForcePasswordChange();
        if ($passwordCheck) {
            // Если после авторизации прошло менее 2 минут, спрашивать только что введенный пароль не требуется
            $form->removeElement('password');
        }
        if ($this->_request->isPost()) {
            $post = $this->_request->getPost();
            if ($form->isValid($post)) {
                $q = ZFE_Query::create()
                    ->select('*')
                    ->from('Editors')
                    ->where('id = ?', $userId)
                ;
                if (!$passwordCheck) {
                    $q->andWhere('password = ' . Editors::$credentialTreatment, $form->getValue('password'));
                }
                $user = $q->fetchOne(); /** @var Editors $user */
                if ($user) {
                    $user->setPassword($form->getValue('password_second'));
                    if ($user->contains('request_password_change')) {
                        $user->request_password_change = 0;
                    }
                    $user->save();

                    $redirect = $this->getParam('redirect');
                    if (empty($redirect) || '/auth/logout' === $redirect) {
                        $redirect = '/';
                    }
                    $this->_redirect($redirect);
                } else {
                    $form->getElement('password')->addError('Не верный пароль');
                }
            }
        }

        $this->_helper->layout()->setLayout('layout_guest');
    }

    /**
     * Получить сессию для запоминания только что введенного пароля.
     *
     * @param int $userId ID пользователя
     *
     * @return Zend_Session_Namespace
     */
    protected function _getCheckPasswordSession($userId)
    {
        return new Zend_Session_Namespace("User_{$userId}_CheckPassword");
    }

    protected function onAuthSuccess($resultRow)
    {
    }
}
