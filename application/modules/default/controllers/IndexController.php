<?php

/**
 * HandsOn CMS Framework
 *
 * LICENÇA
 *
 * Este arquivo-fonte é propriedade da Númera Soluções e Sistemas Ltda.,
 * empresa brasileira inscrita no CNPJ/MF sob nº 08.179.010/0001-48.
 * A reprodução parcial ou total do conteúdo é expressamente vedada, conforme
 * descrição detalhada da licença, disponível no documento "docs/license.txt".
 * Se o arquivo estiver ausente, por favor entre em contato pelo email
 * license@numera.com.br para que possamos enviar uma cópia imediatamente.
 *
 * @copyright  Copyright (c) 2008 Númera Soluções e Sistemas Ltda. (http://www.numera.com.br)
 * @license    http://www.numera.com.br/license/handson     HandsOn 1.0 License
 * @version    $Id$
 */
class IndexController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->acl->allow(null);
        Zend_Layout::startMvc(array(
                    'layoutPath' => APPLICATION_PATH . '/modules/default/views/layouts',
                    'layout' => 'public'
                ));
    }

    public function indexAction() {
        $form = new LoginForm();
        $form->setAction($this->view->baseUrl() . '/');
        $this->view->formResponse = '';

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $values = $form->getValues();
                $authAdapter = new Zend_Auth_Adapter_DbTable(
                                Zend_Db_Table::getDefaultAdapter());

                //$treatment = 'in_excluded = 0 AND SHA1(?)';
                $treatment = 'SHA1(?)';
                $authAdapter->setTableName('users')
                        ->setIdentityColumn('email')
                        ->setCredentialColumn('password')
                        ->setIdentity($values['email'])
                        ->setCredential($values['password'])
                        ->setCredentialTreatment($treatment);

                $select = $authAdapter->getDbSelect();
                $select->where('in_excluded = 0');

                $auth = Zend_Auth::getInstance();

                if ($auth->authenticate($authAdapter)->isValid()) {
                    $userData = $authAdapter->getResultRowObject(
                                    array('id', 'id_profile', 'name', 'email'));
                    $auth->getStorage()->write($userData);

                    $role = Users::getProfileLabel($userData->id_profile);
                    if ($role == Users::PROFILE_SUBSCRIBER) {
                        $this->_redirect('subscriber/#accounts/programming/');
                    }
                    $this->_redirect('admin/');
                } else {
                    Zend_Auth::getInstance()->clearIdentity();
                    $this->view->formResponseTitle =
                            'E-mail ou senha incorretos.';
                    $this->view->formResponse =
                            'Tente novamente observando atentamente todos '
                            . 'caracteres, inclusive letras maiúscula e '
                            . 'minúsculas.';
                }
            } else {
                Zend_Auth::getInstance()->clearIdentity();
                $this->view->formResponseTitle = 'E-mail ou senha incorretos.';
                $this->view->formResponse =
                        'Tente novamente observando atentamente todos '
                        . 'caracteres, inclusive letras maiúscula e '
                        . 'minúsculas.';
            }
        }
        $this->view->form = $form;
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('.');
    }

}
