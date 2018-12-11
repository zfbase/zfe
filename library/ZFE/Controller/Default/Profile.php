<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Управление пользователей своим профилем.
 */
class ZFE_Controller_Default_Profile extends Controller_Abstract
{
    protected static $_profileFormName = 'ZFE_Form_Default_Profile';

    public function indexAction()
    {
        /** @var $user Editors */
        $user = Zend_Registry::get('user')->data;
        $form = new self::$_profileFormName();

        if ($this->_request->isPost()) {
            $post = $this->_request->getPost();
            if ($form->isValid($post)) {
                $data = $form->getValues();
                $q = ZFE_Query::create()
                    ->select('id')
                    ->from('Editors')
                    ->where('id = ?', $user->id)
                    ->andWhere('password = ' . Editors::$credentialTreatment, $data['password'])
                    ->setHydrationMode(Doctrine_Core::HYDRATE_SINGLE_SCALAR)
                ;
                if ($q->execute() !== $user->id) {
                    $form->getElement('password')->addError('Не верный пароль');
                } else {
                    if ($data['password_new']) {
                        $data['password'] = $data['password_new'];
                        unset($data['password_new'], $data['password_new2']);
                    }

                    $user->fromArray($data);
                    $user->save();
                    $this->_helper->Notices->ok('Профиль успешно обновлен');
                    $this->_redirect('/profile');
                }
            }
        } else {
            $form->populate($user->toArray());
        }

        $this->view->user = $user;
        $this->view->form = $form;
    }
}
