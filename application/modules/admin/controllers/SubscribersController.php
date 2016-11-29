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


class Admin_SubscribersController extends HandsOn_Controller_Action
{
    public function init() {
        $this->_helper->acl->allow('administrativo');
        $this->_helper->acl->allow('funcionario');
        $this->_helper->acl->allow('financeiro1');
        $this->_helper->acl->allow('financeiro2');
        $this->_helper->acl->deny('funcionario', 'modify');
        $this->_helper->acl->deny('financeiro1', 'modify');
        $this->_helper->acl->deny('financeiro2', 'modify');

        $this->_helper->acl->allow(Users::PROFILE_DIST);
        $this->_helper->acl->allow(Users::PROFILE_REDE);
        $this->_helper->acl->allow(Users::PROFILE_SELLER);
        $this->_helper->acl->allow(Users::PROFILE_LAB);
    }

    public function indexAction() {
        $columns = array(
           'name' => array('Nome', 191),
           'code' => array('Código', 90),
           'email' => array('E-mail', 180),
           'action' => array('Ações', 50, false)
        );
        $searchItems = array(
           'name' => 'Nome',
           'code' => 'Código'
        );

        $user = Users::getLoggedUser();


        $buttons = array();
        if($user->isAdmin() || $user->isFin1() 
                || $user->isFin2() || $user->isFunc()) {
            $buttons = array(
                array('add', 'subscribers/add/', 'Novo assinante'),
            );
            if ($user->isAdmin()) {
                $buttons[] = array('remove',
                      'admin/subscribers/modify',
                      'Remover',
                      'Tem certeza que deseja remover os assinantes selecionados?',
                      'Nenhuma página foi selecionada.'
                );
            }
        }

        $this->view->title = 'Assinantes';
        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig(
                $columns, $searchItems, $buttons, 'subscribers/list');
    }

    public function listAction() {
        if ($this->getRequest()->isPost()) {
            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $userProfile = Users::getProfileLabel($userIdentity->id_profile);
            // quais perfis podem ver a opção "caixas" do assinante?
            $profilesBoxers = array(Users::PROFILE_DIST, Users::PROFILE_REDE, Users::PROFILE_SELLER);
            $boxesAllowed =
                    (array_search($userProfile, $profilesBoxers) !== false);
            $editAllowed = $userProfile == Users::PROFILE_ADMIN;

            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array(
                    'name'=>'name',
                    'email' => 'email',
                    'code' => 'code');
            $postValues['filterColumns'] = array(
                    'name' => 'name',
                    'code' => 'code');
            $values = $this->view->gridValues($postValues);

            $subscribers = new Subscribers();
            $subscriberList = $subscribers->get(
                    array('id', 'name', 'email', 'code'), $values);

            $rows = array();
            //$date = new Zend_Date();
            foreach ($subscriberList as $subscriber) {
                $action = '';
                if ($editAllowed) {
                    $action .= $this->view->listAction(
                            array('subscribers/edit/id', $subscriber['id']),
                            'edit');
                }
                if ($boxesAllowed) {
                    $action .= $this->view->listAction(
                            array('subscribers/boxes/id', $subscriber['id']),
                            'boxes');
                }

                $rows[] = array(
                    'id' => $subscriber['id'],
                    'cell' => array(
                        $subscriber['name'],
                        $subscriber['code'],
                        $subscriber['email'],
                        $action,
                    )
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = $subscribers->count(
                    $values['filterColumn'], $values['filter']);
            $this->view->rows = $rows;
        }
    }


    public function boxesAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $recvSubscr = Users::getUserById($id);
        /** @var Users usuário atualmente logado. */
        $cUser = $this->getLoggedUser();

        $alboxes = $this->getRequest()->getParam('remedyBoxesValues');
        if (!empty($alboxes)) {
            $alboxes = Zend_Json::decode($alboxes);


            if (!empty($alboxes)) {
                switch ($cUser->label) {
                    /* Se é LAb ou vendedor, desconta dos créditos
                     * unitários do Lab, se é distribuidor, desconta dos
                     * BoxAlloc dele.
                     */
                    case Users::PROFILE_ADMIN:
                        $this->view->response = 'formError';
                        $this->view->message =
                                'Usuário não possui créditos suficientes.';
                        break;
                    case Users::PROFILE_LAB:
                        $totCred = 0;

                        $totCred = ControllersCommon::calclaTotalCreditosBoxes($alboxes);

                        if ($cUser->labcredit >= $totCred) {
                            // descontaa sempre do lab, $descUser
                            ControllersCommon::descontaDoLab($cUser, $totCred);
                            // aloca com origem no user real, $cUser
                            ControllersCommon::alocaBoxes(
                                    $cUser->id, $alboxes, $recvSubscr->id);

                            // retorna ok pro form
                            $this->view->response = 'formSuccess';
                            $this->view->message =
                                    'Alocação de remédios '
                                    . 'realizada com sucesso!';
                            $this->view->redirect = 'subscribers';
                        } else {
                            $this->view->response = 'formError';
                            $this->view->message =
                                    'Usuário não possui créditos suficientes.';
                        }
                        break;
                    case Users::PROFILE_SELLER:
                        $totCred = 0;
                        // é vendedor, desconta do lab
                        $descUser = Users::getUserById($cUser->id_owner);
                        
                        $totCred = ControllersCommon::
                                    calclaTotalCreditosBoxes($alboxes);

                        if ($descUser->labcredit >= $totCred) {
                            // desconta sempre do lab, $descUser
                            ControllersCommon::descontaDoLab(
                                    $descUser, $totCred);
                            // se é vendedor, aloca uns boxes do lab dele
                            ControllersCommon::alocaBoxes(
                                    $descUser->id, $alboxes, $cUser->id);
                            // e aloca dele pro usuário final.
                            $sellerRemedy =
                                    BoxAlloc::listByAllocated($cUser->id);
                            ControllersCommon::changeRemedyOwn(
                                    $alboxes, $sellerRemedy, 
                                    $cUser, $recvSubscr);

                            // retorna ok pro form
                            $this->view->response = 'formSuccess';
                            $this->view->message =
                                    'Alocação de remédios '
                                    . 'realizada com sucesso!';
                            $this->view->redirect = 'index';
                        } else {
                            $this->view->response = 'formError';
                            $this->view->message =
                                    'Usuário não possui créditos suficientes.';
                        }
                        break;
                    case Users::PROFILE_DIST:
                        $myRemedy = BoxAlloc::listByAllocated($cUser->id);
                        // checa se tem crédito
                        if ($this->checkRemedyOwn($alboxes, $myRemedy)) {
                            // aloca as caixas

                            ControllersCommon::changeRemedyOwn(
                                    $alboxes, $myRemedy, $cUser, $recvSubscr);

                            // retorna ok pro form
                            $this->view->response = 'formSuccess';
                            $this->view->message =
                                    'Alocação de remédios '
                                    . 'realizada com sucesso!';
                            $this->view->redirect = 'subscribers';
                        } else {
                            $this->view->response = 'formError';
                            $this->view->message =
                                    'Usuário não possui créditos suficientes.';
                        }
                        break;
                }
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Nenhum campo foi preenchido.';
            }
        }

        $this->view->title = 'Remédios';
        $this->view->type = 'form';
        $view = new Zend_View();
        $view->username = $recvSubscr->name;
        $recvCity = $recvSubscr->getCity();
        $view->cityName = $recvCity->name;
        $view->uf = $recvCity->uf;
        $view->sendOption = $recvSubscr->in_send_option;
        $view->profile = $recvSubscr->label;
        $view->setBasePath(APPLICATION_PATH . '/modules/admin/views/');
        $view->actionUrl = $this->view->baseUrl()
                . '/admin/subscribers/boxes/id/' . $id;

        $view->remedySelect = $this->makeRemedySelectForProfile($cUser);
        $this->view->config =
                array('form' => $view->render('boxes/add.phtml'));
    }

    private function makeRemedySelectForProfile(Users $cUser) {
        $lista = null;
        if ($cUser->isLab()) {
            $lista = RemedyDAO::listApprovedRemediesForLab($cUser->id);
        } elseif ($cUser->isSeller()) {
            $lista = RemedyDAO::listApprovedRemediesForLab($cUser->id_owner);
        } else {
            $lista = RemedyDAO::listRemediesAvailableForUser($cUser->id);
        }
        
        $rems = HtmlUtils::makeSelectForRemedy($lista);
        return $rems;
                
    }

    /**
     * Checa se o usuario possui todas as caixas de remédio em $alboxes
     *
     * @param <array> $alboxes um array de remedios e quantidades para alocar
     *      no formato [[idRemedio, quantidade], [], ...]
     * @param <array BoxAlloc> $myRemedy um array de BoxAlloc de um usuário
     * @return <boolean> false se não tem quantidade suficiente de
     *      algum remédio.
     */
    private function checkRemedyOwn($alboxes, $myRemedy) {
        foreach ($alboxes as $eaal) {
            $idRemedio = $eaal[0];
            $qtRemedio = $eaal[1];
            $rems = Util::findObjectByProperty(
                    $myRemedy, 'id_remedy', $idRemedio);
            if (count($rems) == 0) {
                // nao tem este remédio...
                return false;
            }
            foreach ($rems as $earem) {
                if ($earem->getCredit() >= $qtRemedio) {
                    // Se alguma alocação ainda tem crédito:
                    // continua o loop do $alboxes, pulando o
                    // 'return false' abaixo
                    continue 2;
                }
            }
            // só cai aqui se fez todo o foreach do $rems sem entrar no if.
            return false;
        }
        return true;
    }

    public function addAction() {
        $form = new SubscriberForm();
        $form->setAttrib('autocompleteurl',
                $this->view->baseUrl()
                . '/admin/subscribers/autocomplete');
        $form->setAction($this->view->baseUrl() . '/admin/subscribers/add');

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $newSubscriber = new Subscribers(array(
                    'name'            => $form->getValue('name'),
                    'code'            => $form->getValue('code'),
                    'email'           => $form->getValue('email'),
                    'password'        => $form->getValue('password'),
                    'id_city'         => $form->getValue('city'),
                ));
                $email = Users::getUserByEmail($newSubscriber->email);
                $user = Users::getUserByCode($newSubscriber->code);
                if (!empty($user)){
                    $this->view->response = 'formError';
                    $this->view->message =
                            'Esse código de assinante já existe no sistema!';
                } elseif (!empty($email)) {
                    $this->view->response = 'formError';
                    $this->view->message =
                            'Esse email já esta cadastrado no sistema!';
                } else {
                    $newSubscriber->save();

                    $newSubscriber->send();

                    $this->view->response = 'formSuccess';
                    $this->view->message = 'Assinante <b>"' 
                            . $newSubscriber->name
                            . '"</b> cadastrado com sucesso!';
                    $this->view->redirect = 'subscribers';
                    return;                    
                }

            } else {
                $this->view->response = 'formError';
                $this->view->message = 
                        'Alguns campos foram preenchidos de maneira incorreta. '
                        . 'Verifique os campos destacados em vermelho.';
            }
            $uff = $form->getValue('uf');
            if (!empty($uff)) {
                $cty = $form->getElement('city');
                $hashCity = City::listHashIdName($uff);
                $cty->setOptions(array(
                    'multiOptions' => $hashCity
                ));
            }
        }

        $this->view->title = 'Adicionar novo assinante';
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
    }

    public function editAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $form = new SubscriberForm(array('id' => $id));
        $form->setAction($this->view->baseUrl()
                . '/admin/subscribers/edit/id/' . $id);
        $form->setAttrib('autocompleteurl',
                $this->view->baseUrl()
                . '/admin/subscribers/autocomplete');
//        $form->setAttrib('autocompleteurl',
//                'http://www.devbridge.com/projects/autocomplete/'
//                . 'service/autocomplete.ashx?country=Yes&query=a');

        $subscriber = new Subscribers(array('id' => $id));
        $subscriber->read(array('code', 'name','email','password', 'id_city'));
        $subCity = is_numeric($subscriber->id_city)
                ? City::getById($subscriber->id_city)
                : null;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $subscriber = new Subscribers(array(
                    'id'        => $id,
                    'name'      => $form->getValue('name'),
                    'code'      => $form->getValue('code'),
                    'email'     => $form->getValue('email'),
                    'password'  => $form->getValue('password'),
                    'city'      => $form->getValue('city'),
                    'uf'        => $form->getValue('uf'),
                ));
                $subscriber->save();

                $pass = $form->getValue('password');
                
                if (!empty($pass)){
                    $subscriber->send('new_pass');
                }
                
                $this->view->response = 'formSuccess';
                $this->view->message = 'Assinante <b>"' 
                        . $form->getValue('name')
                        . '"</b> atualizado com sucesso!';
                $this->view->redirect = 'subscribers';
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de '
                        . 'maneira incorreta. Verifique os campos destacados '
                        . 'em vermelho.';
                $form->populate($form->getValues());
            }
        } else {
            $values = array(
                'code' => $subscriber->code,
                'name' => $subscriber->name,
                'email' => $subscriber->email,
                'password' => $subscriber->password,
            );
            if (is_object($subCity)) {
                $values['uf'] = $subCity->uf;
                $values['city'] = $subCity->id;
                $formCity = $form->getElement('city');

                if (! empty($subCity->uf)) {
                    $cidades = array();
                    $cidades = City::listHashIdName($subCity->uf);
                    $formCity->setOptions(array(
                        'multiOptions'  => $cidades));
                }

            }
            $form->populate($values);
        }

        $this->view->title = 'Editar Assinante: ' . $subscriber->name;
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
        $this->_formatFileFormResponseView();
    }
    
    public function autocompleteAction() {
        $autoq = $this->getRequest()->getParam('query');
        $uf = $this->getRequest()->getParam('uf');
        $form = new SubscriberForm();
        $formCity = $form->getElement('city');

        $cidades = array();
        if (! empty($uf)) {
            $cidades = City::listHashIdName($uf);
        }

        $formCity->setOptions(array(
                'multiOptions'  => $cidades));

        print $formCity->render();
        exit();
    }

    public function modifyAction() {
        if ($this->getRequest()->isPost()) {
            $sid = (int)$this->getRequest()->getPost('id');
            $subscriber = new Subscribers(array('id' => $sid));
            switch ($this->getRequest()->getPost('command')) {
                case 'block':   $subscriber->lock(); break;
                case 'unblock': $subscriber->unlock(); break;
                case 'delete':  $subscriber->delete(); break;
            }
        }
    }
}