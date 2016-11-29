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


class Subscriber_PasswordController extends HandsOn_Controller_Action
{

    public function init()
    {
        $this->_helper->acl->allow('assinante');
    }

    public function passwordchangeAction()
    {
        $form = new PasswordForm();
        $form->setAction($this->view->baseUrl() . '/subscriber/password/passwordchange/');

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $userIdentity = Zend_Auth::getInstance()->getIdentity();
                $id = (null != $userIdentity) ? $userIdentity->id : null;

                $user = new Users(array(
                    'id'          => $id,
                    'password'    => $form->getValue('password'),

                ));
                $user->saveNewPassword();

                $this->view->response = 'formSuccess';
                $this->view->message = 'Sua senha foi alterada com sucesso e pode ser utilizada no seu próximo acesso ao sistema.';
                $this->view->redirect = '';
                $this->_formatFileFormResponseView();
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de maneira incorreta. Verifique os campos destacados em vermelho.';
                $form->populate($form->getValues());
            }
        }

        $this->view->title = 'Trocar Senha: ';
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
    }
}