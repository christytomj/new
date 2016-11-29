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

class PasswordController extends Zend_Controller_Action {
    public function init() {
        $this->_helper->acl->allow(null);
        Zend_Layout::startMvc(array(
            'layoutPath' => APPLICATION_PATH . '/modules/default/views/layouts',
            'layout' => 'public'
            ));
    }

    public function indexAction() {
        $form = new PasswordForm();
        $form->setAction($this->view->baseUrl() . '/password');
        $this->view->form = $form;
        $page = Zend_Registry::get('config')->url . $this->view->baseUrl();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $email = $form->getValue('email');
                
                $verificaEmail = Users::getUserByEmail($email);

                if (!empty($verificaEmail)) {
                    $user = $verificaEmail;
                    Users::savePasswordLink($user);
                    $link = Users::getPasswordLink($user);
                    Users::sendEmail($user, $link, $page);
                    $this->view->formResponseTitle =
                            'Solicitação efetuada com sucesso!';
                    $this->view->formResponse =
                            'Um link foi enviado para seu email '
                            . $email
                            . '<br/> com as instruções para alterar sua senha.';
                    $this->view->formLink =
                            '<a href='.$page
                            . '>Clique aqui para voltar a página de acesso.</a>';
                    return;
                    // $this->_redirect('/');
                } else {
                    $this->view->formErrorResponse = 'Email inválido!';
                }
            }
        }
    }

    public function resetAction() {
        $form = new ResetForm();
        $form->setAction($this->view->baseUrl() . '/password/reset');
        $this->view->form = $form;

        $users = new Users();
        $code = $this->getRequest()->getParam('code');
        $page = Zend_Registry::get('config')->url . $this->view->baseUrl();

        if (!empty($code)) {
            if (Users::checkPasswordLinkCode($code)) {
                $form->setAction($this->view->baseUrl() . '/password/reset/code/'.$code);
                if ($this->getRequest()->isPost()) {
                    if ($form->isValid($this->getRequest()->getPost())) {
                        $data = array(
                            'linkCodeData' => Users::getLinkCodeData($code),
                            'email' =>  $form->getValue('email'),
                            'password' => $form->getValue('password'),
                            'passwordConfirm' =>  $form->getValue('passwordConfirm')
                        );
                        if ((Users::checkResetPassData($data))) {
                            $user = new Users(array(
                                'id'          => $data['linkCodeData']['id_user'],
                                'password'    => $data['passwordConfirm'],
                                ));
                            $user->saveNewPassword();
                            Users::deleteLinkCode($data['linkCodeData']['id']);
                            $this->view->formResponseTitle =
                                    'Sua senha foi alterada com sucesso!';
                            $this->view->formResponse = 
                                    'Você já pode utilizar a nova senha no '
                                    . 'seu próximo acesso<br/> ao sistema.';
                            $this->view->formLink =
                                    '<a href='.$page.
                                    '>Clique aqui para voltar a página de '
                                    . 'acesso.</a>';
                        } else {
                            $this->view->formErrorResponse =
                                    'Alguns campos foram preenchidos de '
                                    . 'maneira incorreta. Verifique email '
                                    . 'e senhas.';
                        }
                    } else {
                        $this->view->formErrorResponse = 
                                'Alguns campos foram preenchidos de maneira '
                                . 'incorreta. Verifique email e senhas.';
                    }
                }
                $this->view->type = 'form';
                $this->view->form = $form;
            } else {
                $this->_redirect('/');
            }
        } else {
            $this->_redirect('/');
        }

    }
}