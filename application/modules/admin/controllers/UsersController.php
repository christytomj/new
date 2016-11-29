<?php
/*
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
 * @copyright   Copyright (c) 2008 Númera Soluções e Sistemas Ltda.
 *              (http://www.numera.com.br)
 * @license     http://www.numera.com.br/license/handson
 *              HandsOn 1.0 License
 * @version     $Id$
*/

class Admin_UsersController extends HandsOn_Controller_Action {
    public function init() {
        $this->_helper->acl->allow(Users::PROFILE_ADMIN);
        $this->_helper->acl->allow(Users::PROFILE_FUNC, 'password');
        $this->_helper->acl->allow(Users::PROFILE_FIN1, 'password');
        $this->_helper->acl->allow(Users::PROFILE_FIN2, 'password');

        $this->_helper->acl->allow(Users::PROFILE_DIST);
        $this->_helper->acl->allow(Users::PROFILE_REDE);
        $this->_helper->acl->allow(Users::PROFILE_SELLER);
        $this->_helper->acl->allow(Users::PROFILE_LAB);

        $this->_helper->acl->deny(Users::PROFILE_SELLER, 'modify');
        $this->_helper->acl->deny(Users::PROFILE_SELLER, 'add');
    }

    public function indexAction() {
        $userIdentity = Zend_Auth::getInstance()->getIdentity();
        $user = $this->getLoggedUser();
        $columns = array(
                'name' => array('Nome', 150),
                'email' => array('E-mail', 150),
                'title' => array('Perfil', 100),
                'action' => array('Ações', 80, false)
        );
        $searchItems = array(
                'name' => 'Nome',
                'title' => 'Perfil'
        );

        // Vend não ve nada, Lab não ve Del. Add e Del pros outros
        $buttons = null;
        if ($user->isSeller()) {
            $buttons = array();
        } else {
            $buttons = array(
                    array('add', 'users/add/', 'Novo usuário'),
            );
            if (!$user->isLab()) {
                $buttons[] = array('remove',
                        'admin/users/modify',
                        'Remover',
                        'Tem certeza que deseja remover os usuários '
                                . 'selecionados?',
                        'Nenhuma página foi selecionada.'
                );
            }
        }


        $this->view->title = 'Usuários';
        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig(
                $columns, $searchItems, $buttons, 'users/list');
    }

    public function listAction() {
        if ($this->getRequest()->isPost()) {
            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array(
                    'name'=>'name',
                    'email' => 'email',
                    'title' => 'title');
            $postValues['filterColumns'] = array(
                    'name' => 'name',
                    'title' => 'title');
            $values = $this->view->gridValues($postValues);



            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $idProfile = (null != $userIdentity)
                    ? $userIdentity->id_profile : null;
            $cUser = $this->getLoggedUser();
            $labelProfile = $cUser->label;

            // ninguem vê assinantes aqui
            $values['hiddenProfiles'] =
                    Users::listDisalowedViewProfilesFor($labelProfile);
            error_log(__METHOD__ . " - ($labelProfile) hiddens = ".join(', ', $values['hiddenProfiles']));
            $userList = Users::get(
                    array('id', 'name', 'email', 'title', 'label', 'id_owner'),
                    $values);

            $rows = array();
            foreach ($userList as $user) {
                if ($cUser->isLab()
                        && $user['label'] == Users::PROFILE_SELLER
                        && $user['id_owner'] != $cUser->id) {
                    // Laboratorios só veem SEUS vendedores
                    continue;
                }
                $acoes = $this->montaAcoesUsuario($cUser, $user);
                $rows[] = array(
                        'id' => $user['id'],
                        'cell' => array(
                                $user['name'],
                                $user['email'],
                                $user['title'],
                                $acoes
                        )
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = Users::count($idProfile,
                    $values['filterColumn'], $values['filter'],
                    $values['hiddenProfiles']);
            $this->view->rows = $rows;
        }
    }

    public function addAction() {
        $form = new UserForm();
        $form->setAction($this->view->baseUrl() . '/admin/users/add');

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                // os Laboratórios são "donos" dos vendedores
                $ownerId = null;
                $userIdentity = Zend_Auth::getInstance()->getIdentity();
                $userLabel = Users::getProfileLabel(
                        $userIdentity->id_profile);
                $newbyLabel = Users::getProfileLabel(
                        $form->getValue('id_profile'));

                if ($userLabel == Users::PROFILE_LAB
                        && ($newbyLabel == Users::PROFILE_SELLER
                                || $newbyLabel == Users::PROFILE_DIST
                                || $newbyLabel == Users::PROFILE_REDE)) {
                    $ownerId = $userIdentity->id;
                }
                $users = new Users(array(
                                'id_profile'    => $form->getValue('id_profile'),
                                'name'          => $form->getValue('name'),
                                'email'         => $form->getValue('email'),
                                'password'      => $form->getValue('password'),
                                'id_owner'      => $ownerId,
                ));

                $user = Users::getUserByEmail($form->getValue('email'));
                if(!empty($user)) {
                    $this->view->response = 'formError';
                    $this->view->message =
                            'Esse email já esta cadastrado no sistema!';
                } else {
                    $users->save();

                    $users->send();

                    $this->view->response = 'formSuccess';
                    $this->view->message = 'Perfil <b>"'
                            . $form->getValue('name')
                            . '"</b> cadastrado com sucesso!';
                    $this->view->redirect = 'users';
                    return;
                }
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de '
                        . 'maneira incorreta. Verifique os campos destacados '
                        . 'em vermelho.';
            }
        }

        $this->view->title = 'Adicionar novo usuário';
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());

    }

    public function editAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $form = new UserForm(array('id' => $id));
        $form->setAction($this->view->baseUrl()
                . '/admin/users/edit/id/' . $id);

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $user = new Users(array(
                                'id'          => $id,
                                'id_profile'  => $form->getValue('id_profile'),
                                'name'        => $form->getValue('name'),
                                'email'       => $form->getValue('email'),
                                'password'    => $form->getValue('password'),

                ));
                $user->save();

                $pass = $form->getValue('password');

                if (!empty($pass)) {
                    $user->send('new_pass');
                }

                $this->view->response = 'formSuccess';
                $this->view->message = 'Usuário <b>"'
                        . $form->getValue('name')
                        . '"</b> atualizado com sucesso!';
                $this->view->redirect = 'users';
                $this->_formatFileFormResponseView();
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos '
                        . 'de maneira incorreta. Verifique os '
                        . 'campos destacados em vermelho.';
                $form->populate($form->getValues());
            }
        } else {
            $user = new Users(array('id' => $id));
            $user->read(array('id_profile', 'name','email','password'));
            $values = array(
                    'id_profile' => $user->id_profile,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password);
            $form->populate($values);
        }
        $this->view->title = 'Editar Usuário: ' . $user->name;
        $this->view->type = 'form';
        //$fff = new CreditForm();
        $this->view->config = array('form' => $form->render());
    }

    public function creditAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $form = new CreditForm(array('id' => $id));
        $form->setAction($this->view->baseUrl()
                . '/admin/users/credit/id/' . $id);

        $userCredit = Users::getUserById($id);
        $values = array(
                'name' => $userCredit->name,
                'credit' => $userCredit->labcredit,
        );

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $valVelho = $userCredit->labcredit;
                $valNovo = $form->getValue('credit');

                if ($valNovo != $valVelho) {
                    $userCredit->labcredit = $valNovo;

                    $userCredit->saveLabCredit();

                    $smsb = new SmsBuy();
                    $smsb->id_user = $userCredit->id;
                    $smsb->qty = $valNovo - $valVelho;
                    $smsb->save();
                    $this->view->message = 'Créditos de <b>"'
                            . $userCredit->name
                            . '"</b> atualizados com sucesso!';
                } else {
                    $this->view->message = 'Créditos de <b>"'
                            . $userCredit->name
                            . '"</b> inalterados!';
                }

                $this->view->response = 'formSuccess';
                $this->view->redirect = 'users';
                //$this->_formatFileFormResponseView();
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de '
                        . 'maneira incorreta. Verifique os campos destacados '
                        . 'em vermelho.';
                $form->populate($form->getValues());
            }
        } else {
            $form->populate($values);
        }

        $this->view->title = 'Editar Créditos: ' . $userCredit->name;
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
        //$this->_formatFileFormResponseView();
    }

    public function modifyAction() {
        if ($this->getRequest()->isPost()) {
            $user = new Users(
                    array('id' => (int)$this->getRequest()->getPost('id'))
            );
            switch ($this->getRequest()->getPost('command')) {
                case 'block':   $user->lock();
                    break;
                case 'unblock': $user->unlock();
                    break;
                case 'delete':  $user->delete();
                    break;
            }
        }
    }

    public function passwordAction() {

        $form = new PasswordForm();
        $form->setAction($this->view->baseUrl() . '/admin/users/password/');

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
                $this->view->message =
                        'Sua senha foi alterada com sucesso e pode ser '
                        . 'utilizada no seu próximo acesso ao sistema.';
                $this->view->redirect = '';
                $this->_formatFileFormResponseView();
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message =
                        'Alguns campos foram preenchidos de maneira incorreta. '
                        . 'Verifique os campos destacados em vermelho.';
                $form->populate($form->getValues());
            }
        }

        $this->view->title = 'Trocar Senha: ';
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
        $this->_formatFileFormResponseView();
    }

    public function boxesAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $recvUser = Users::getUserById($id);
        $cUser = $this->getLoggedUser();

        if ($this->getRequest()->getParam('remedyBoxesValues')) {
            $alboxes = $this->getRequest()->getParam('remedyBoxesValues');
            $alboxes = Zend_Json::decode($alboxes);

            if(!empty($alboxes)) {
                // cUser é quem tá alocando
                switch ($cUser->label) {
                    /* Se é LAb ou vendedor, desconta dos créditos
                     * unitários do Lab, se é distribuidor, desconta dos
                     * BoxAlloc dele.
                    */
                    case Users::PROFILE_LAB:
                        $totCred = 0;

                        $totCred = ControllersCommon::
                                calclaTotalCreditosBoxes($alboxes);

                        if ($cUser->labcredit >= $totCred) {
                            // descontaa sempre do lab, $descUser
                            ControllersCommon::descontaDoLab($cUser, $totCred);
                            // aloca com origem no user real, $cUser
                            ControllersCommon::alocaBoxes(
                                    $cUser->id, $alboxes, $recvUser->id);

                            // retorna ok pro form
                            $this->view->response = 'formSuccess';
                            $this->view->message =
                                    'Alocação de remédios '
                                    . 'realizada com sucesso!';
                            $this->view->redirect = 'users';
                        } else {
                            $this->view->response = 'formError';
                            $this->view->message =
                                    'Usuário não possui créditos suficientes.';
                        }
                        break;
                    case Users::PROFILE_SELLER:
                        $totCred = 0;
                        // é vendedor, desconta do lab
                        if (! $cUser->id_owner) {
                            $this->view->response = 'formError';
                            $this->view->message =
                                    'Usuário não possui créditos.';
                            break;
                        }
                        $descUser = Users::getUserById($cUser->id_owner);

                        $totCred = ControllersCommon::
                                calclaTotalCreditosBoxes($alboxes);

                        if ($descUser->labcredit >= $totCred) {
                            // desconta sempre do lab, $descUser
                            ControllersCommon::
                                    descontaDoLab($descUser, $totCred);
                            // se é vendedor, aloca uns boxes do lab dele
                            ControllersCommon::alocaBoxes(
                                    $descUser->id, $alboxes, $cUser->id);
                            // e aloca dele pro usuário final.
                            $sellerRemedy =
                                    BoxAlloc::listByAllocated($cUser->id);
                            $retErro = ControllersCommon::changeRemedyOwn(
                                    $alboxes, $sellerRemedy,
                                    $cUser, $recvUser);

                            if (empty ($retErro)) {
                                // retorna ok pro form
                                $this->view->response = 'formSuccess';
                                $this->view->message = 
                                        'Alocação de remédios '
                                            . 'realizada com sucesso!';
                                $this->view->redirect = 'users';
                            } else {
                                $this->view->response = 'formError';
                                $this->view->message =
                                        'Usuário não possui créditos suficientes.';
                            }
                                
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

        $this->view->title = 'Adicionar caixas';
        $this->view->type = 'form';
        $view = new Zend_View();
        //$view->sendOption = $account->in_send_option;
        $view->username = $recvUser->name;
        $view->setBasePath(APPLICATION_PATH . '/modules/admin/views/');
        $view->actionUrl = $this->view->baseUrl()
                . '/admin/users/boxes/id/' . $id;

        $remsToShow = null;
        if ($cUser->isLab() || $cUser->isSeller()) {
            $idLab = $cUser->isLab() ? $cUser->id : $cUser->id_owner;
            $remsToShow = RemedyDAO::listApprovedRemediesForLab($idLab);
        } else {
            $remsToShow = RemedyDAO::listRemediesAvailableForUser($cUser);
        }

        $view->profile = $recvUser->label;

        $view->remedySelect = HtmlUtils::makeSelectForRemedy($remsToShow);
        $this->view->config =
                array('form' => $view->render('boxes/add.phtml'));
    }

    public function historyAction() {
        if ($this->getRequest()->isPost()) {
            // retorna ok pro form
            $id = $this->getRequest()->getParam('id');
            $POST = $this->getRequest()->getPost();
            $dt_inicio = isset($POST[UserHistoryForm::FIELD_DT_INICIO])
                    ? $POST[UserHistoryForm::FIELD_DT_INICIO]
                    : '';
            $dt_fim = isset($POST[UserHistoryForm::FIELD_DT_FIM])
                    ? $POST[UserHistoryForm::FIELD_DT_FIM]
                    : null;
            $dt_inicio = strtr($dt_inicio, '/', '.');
            $dt_fim = strtr($dt_fim, '/', '.');
            $this->view->response = 'formRedirect';
            $this->view->redirect =
                    'users/userhistory/id/'.$id
                    . '/i/'.$dt_inicio
                    . '/f/'.$dt_fim;
        } else {
            $this->showDateSearch();
        }
    }

    public function userhistoryAction() {
        $id = $this->getRequest()->getParam('id');
        $user = Users::getUserById($id);

        $POST = $this->getRequest()->getPost();

        $dtIni = $this->getRequest()->getParam('i');
        $dtFim = $this->getRequest()->getParam('f');
        $dtIni = empty($dtIni)
                ? null
                : new Zend_Date($dtIni, 'pt_BR');
        /** @var $dtFim Zend_Date */
        $dtFim = empty($dtFim)
                ? null
                : new Zend_Date($dtFim, 'pt_BR');
        if ($dtFim) {
            $dtFim = $dtFim->addDay(1);
        }

        $config['group'][0]['label'] =
                ucfirst($user->label) . ': ' . $user->name;

        $totCreds = 0;
        $dataSubscr = array(); // dados dos assinantes
        $dataElse = array(); // dados dos outros usuários
        list($dataSubscr, $dataElse, $totCreds) =
                $this->gerUserHistoryData(
                        $id, $dtIni, $dtFim);

        foreach ($dataSubscr as &$ead) {
            array_splice($ead, 6);
        }

        $config['group'][1]['label'] =
                'Total de créditos inseridos: ' . $totCreds;
        $config['group'][1]['data'] = array(array(
                        'type' => 'table',
                        'header' => array('Hora', 'Remédio', 'Quantidade',
                                'Assinante', 'Estado', 'Cidade'),
                        'rows' => $dataSubscr,
        ));

        if (count($dataElse)) {
            foreach ($dataElse as &$ead) {
                array_splice($ead, 4, 2);
            }
            $config['group'][2]['label'] = 'Distribuidores';
            $config['group'][2]['data'] = array(array(
                            'type' => 'table',
                            'header' => array('Hora', 'Remédio', 'Quantidade',
                                    'Distribuidor', 'Destino'),
                            'rows' => $dataElse,
            ));
        }

        $this->view->title = 'Histórico';
        $this->view->type = 'report';
        $this->view->config = $config;
    }

    private function gerUserHistoryData(
            $id, $dtIni, $dtFim) {
        $totCreds = 0;
        $dataSubscr = array(); // dados dos assinantes
        $dataElse = array(); // dados dos outros usuários

        $listAllocs = BoxAlloc::listByOrigin($id);
        $theUser = Users::getUserById($id);

        foreach ($listAllocs as $eaAlloc) {
            $reme = RemedyDAO::getById($eaAlloc->id_remedy);
            
            // e compensa. 
            $assi = Users::getUserById($eaAlloc->id_allocated);
            $city = City::getById($assi->id_city);

            $destinatario = null;
            if ($theUser->isSeller()) {
                $destinatario = Users::getUserById($eaAlloc->id_allocated);
            } else {
                $nextAlloc = BoxAlloc::getById($eaAlloc->id+1);
                $destinatario = Users::getUserById($nextAlloc->id_allocated);
            }
            $dt = new Zend_Date($eaAlloc->dt_alloc, 'pt_BR');
            if ($dtIni && $dt->isEarlier($dtIni)) {
                continue;
            }
            if ($dtFim && $dt->isLater($dtFim)) {
                continue;
            }

            $dtal = new Zend_Date($eaAlloc->dt_alloc, 'pt_BR');
            $sdtal =
                $dtal->get(Util::DATE_FORMAT) . ' '
                . $dtal->get(Util::TIME_FORMAT);
            $val = array(
                    $sdtal,
                    $reme->name,
                    $eaAlloc->qty,
                    $assi->name,
                    $city->uf,
                    $city->name,
                    $destinatario->name,
            );

            if ($assi->isSubscriber()) {
                $dataSubscr[] = $val;
            } else {
                $dataElse[] = $val;
            }
            $totCreds += $eaAlloc->qty * $reme->qty;
        }
        return array(
            $dataSubscr,
            $dataElse,
            $totCreds);
    }


    public function userhistorypdfAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $start = $this->dateGetFromParam('start');
        $end = $this->dateGetFromParam('end');

        //$sendReports = new Programming();
        //$valores = $sendReports->sendprogrammingReport(
        //        array('id','dt_register','in_send_option','name','cell_phone'),
        //        $id, $start, $end);
        //print_r($valores);
        //exit();
        $subscriber = Users::getUserById($id);

        $totCreds = 0;
        $dataSubscr = array(); // dados dos assinantes
        $dataElse = array(); // dados dos outros usuários
        list($dataSubscr, $dataElse, $totCreds) =
                $this->gerUserHistoryData(
                        $id, $dtIni, $dtFim);

        //Relátorio de Envio Individual

        require_once 'Zend/Pdf.php';
        $pdf = new Zend_Pdf();
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontBold =
                Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

        //Incluir uma Página
        $page = $this->newPdfPage();

        $page->setFont($fontBold, 14);
        $titulo = ucfirst($subscriber->label);

        $page->drawText(
                $titulo.': '.$subscriber->name.' - '.$subscriber->email,
                41 ,720);

        $page->setFont($fontBold, 14);
        $page->drawText('Total de créditos inseridos: '.$totCreds,
                41, 690, 'UTF-8');

        /*
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 12);
        $page->drawText($subscriber->name.' - '.$subscriber->email,122 ,700);
         * 
         */

        $page->drawLine(30, 625, 570, 625);
        $page->drawLine(30, 675, 570, 675);
        $page->setFont($fontBold, 14);
        $page->drawText('Data', 41, 650);
        $page->drawText('Remédio', 140 ,650, 'UTF-8');
        $page->drawText('Quant.', 240, 650);
        $page->drawText('Assinante',300 ,650);
        $page->drawText('Estado',410 ,650);
        $page->drawText('Cidade',460 ,650, 'UTF-8');

        $line = 600;
        $tableLine = 575;
        $page->setFont($font, 12);

        foreach ($dataSubscr as $valor) {
            $zdt = new Zend_Date($valor[0], 'pt_BR');
            $sdt =
                $zdt->get(Util::DATE_FORMAT) . ' '
                . $zdt->get(Util::TIME_FORMAT);
            $array[$key] = array(
                    $page->drawText($sdt, 41, $line, 'UTF-8'),
                    $page->drawText($valor[1], 140, $line, 'UTF-8'),
                    $page->drawText($valor[2], 240, $line, 'UTF-8'),
                    $page->drawText($valor[3], 300, $line, 'UTF-8'),
                    $page->drawText($valor[4], 410, $line, 'UTF-8'),
                    $page->drawText($valor[5], 460, $line, 'UTF-8'),
            );
            $line -= 25;
            $tableLine -= 25;
            if ($tableLine < 72) {
                //Criar novo pdf
                //                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                //                $page->setFont($font, 14);
                //                $page->drawText('Pagina:'." ".$pageNumber, 41, $line);
                array_push($pdf->pages, $page);
                $page = $this->newPdfPage();
                $page->drawLine(30, 725, 570, 725);
                $page->drawLine(30, 675, 570, 675);
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $page->setFont($fontBold, 14);
                $page->drawText('Hora', 41, 650);
                $page->drawText('Remédio', 110 ,650, 'UTF-8');
                $page->drawText('Quant.', 180, 650);
                $page->drawText('Assinante',240 ,650);
                $page->drawText('Estado',380 ,650);
                $page->drawText('Cidade',460 ,650, 'UTF-8');
                $line = 600;
                $tableLine = 575;
                $page->setFont($font, 12);
            }
        }


        ///////////////////
        $line -= 50;
        $page->drawLine(30, $line, 570, $line);
        $line -= 25;
        $page->setFont($fontBold, 14);
        $page->drawText('Hora', 41, $line);
        $page->drawText('Remédio', 110 ,$line, 'UTF-8');
        $page->drawText('Quant.', 180, $line);
        $page->drawText('Distribuidor',240 ,$line);
        $line -= 25;
        $page->drawLine(30, $line, 570, $line);
        
        $line -= 25;
        //$line = 600;
        //$tableLine = 575;
        $page->setFont($font, 12);

        foreach ($dataElse as $valor) {
            $hr = explode(' ', $valor[0]);
            $array[$key] = array(
                    $page->drawText($hr[1], 41, $line, 'UTF-8'),
                    $page->drawText($valor[1], 110, $line, 'UTF-8'),
                    $page->drawText($valor[2], 180, $line, 'UTF-8'),
                    $page->drawText($valor[3], 240, $line, 'UTF-8'),
            );
            $line -= 25;
            $tableLine -= 25;
            if ($tableLine < 72) {
                //Criar novo pdf
                //   $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                //   $page->setFont($font, 14);
                //   $page->drawText('Pagina:'." ".$pageNumber, 41, $line);
                array_push($pdf->pages, $page);
                $page = $this->newPdfPage();
                $page->drawLine(30, 725, 570, 725);
                $page->drawLine(30, 675, 570, 675);
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $page->setFont($fontBold, 14);
                $page->drawText('Hora', 41, $line);
                $page->drawText('Remédio', 110 ,$line, 'UTF-8');
                $page->drawText('Quant.', 180, $line);
                $page->drawText('Distribuidor',240 ,$line);
                $line = 600;
                $tableLine = 575;
                $page->setFont($font, 12);
            }
        }
        //////////////////////





        array_push($pdf->pages, $page);

        //Salvar o PDF
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="Arquivo.pdf"');

        //Mostrar o PDF na tela
        echo $pdf->render();

        exit();
    }

    private function newPdfPage() {

        //Incluir uma Página
        $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        $pageHeight = $page->getHeight();
        $pageWidth = $page->getWidth();
        //        echo "$pageHeight - ";
        //        echo "$pageWidth";
        //exit();

        //Estilo da Página
        $style = new Zend_Pdf_Style();
        $style->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $style->setLineWidth(2);
        $page->setStyle($style);

        //Incluir uma Imagem
        $imagem = Zend_Pdf_Image::imageWithPath(APPLICATION_PATH
                . '/../public/images/public/logo.png');
        $imageHeight = 35;
        $imageWidth = 157;
        $topPos = $pageHeight - 36;
        $leftPos = 36;
        $bottomPos = $topPos - $imageHeight;
        $rightPos = $leftPos + $imageWidth;
        $page->drawImage($imagem, $leftPos, $bottomPos, $rightPos, $topPos);

        $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
        $page->setLineColor($color1);
        $page->setLineWidth(0.5);

        return $page;
    }

    public function balanceAction() {
        if ($this->getRequest()->isPost()) {
            // retorna ok pro form
            $id = $this->getRequest()->getParam('id');
            if (empty($id)) {
                $id = Zend_Auth::getInstance()->getIdentity()->id;

            }
            $POST = $this->getRequest()->getPost();
            $dt_inicio = isset($POST[UserHistoryForm::FIELD_DT_INICIO])
                    ? $POST[UserHistoryForm::FIELD_DT_INICIO]
                    : '';
            $dt_fim = isset($POST[UserHistoryForm::FIELD_DT_FIM])
                    ? $POST[UserHistoryForm::FIELD_DT_FIM]
                    : null;
            $dt_inicio = strtr($dt_inicio, '/', '.');
            $dt_fim = strtr($dt_fim, '/', '.');
            $this->view->response = 'formRedirect';
            $this->view->redirect =
                    'users/userbalance/id/'.$id
                    . '/i/'.$dt_inicio
                    . '/f/'.$dt_fim;
        } else {
            $this->showDateSearch('Balanço', 'balance');
        }
    }

    public function userbalanceAction() {
        $id = $this->getRequest()->getParam('id');
        $user = Users::getUserById($id);

        /** @var Zend_Date data inicial da pesquisa */
        $dtIni = null;
        /** @var Zend_Date data final da pesquisa */
        $dtFim = null;

        list($dtIni, $dtFim) = $this->getParamDates();

        // creditos comprados no periodo
        $valBought = $this->calcValBought($user, $dtIni, $dtFim);

        // creditos usados no periodo
        $valGiven = $this->calcValGiven($user, $dtIni, $dtFim);
        
        // créditos retornados no periodo
        $credRets = $this->getCreditosRetornados($user, $dtIni, $dtFim);

        $config['group'][0]['label'] =
                ucfirst($user->label) . ': ' . $user->name;

        $saldoFinal = $this->calcSaldoFinal($user, $dtFim);

        $idx = 1;

        if ($dtIni && $dtFim) {
            $msg = 'Período: ';
            if ($dtIni) {
                $msg .= ' de ' . $dtIni->get(Util::DATE_FORMAT);
            }
            if ($dtIni) {
                $msg .= ' até ' . $dtFim->subDay(1)->get(Util::DATE_FORMAT);
            }
            $config['group'][$idx++]['label'] = $msg;
        }

        /* conta de chegada */
        $saldoAnt = $saldoFinal - $valBought + $valGiven - $credRets;

        $config['group'][$idx++]['label'] =
                'Saldo inicial: ' . $saldoAnt;

        $config['group'][$idx++]['label'] =
                'Total de créditos comprados: ' . $valBought;

        $config['group'][$idx++]['label'] =
                'Total de créditos usados: ' . $valGiven;

        $config['group'][$idx++]['label'] =
                'Total de créditos retornados: ' . $credRets;

        $config['group'][$idx++]['label'] =
                'Saldo final: ' . $saldoFinal;


        $this->view->title = 'Balanço';
        $this->view->type = 'report';
        $this->view->config = $config;
    }

    private function calcSaldoFinal($user, $dtFim) {
        $saldoFinal = 0;
        if ($dtFim != null) {
            // creditos comprados no periodo
            $posBought = $this->calcValBought($user, $dtFim, null);
            // creditos usados no periodo
            $posGiven = $this->calcValGiven($user, $dtFim, null);
            // créditos retornados no periodo
            $posRets = $this->getCreditosRetornados($user, $dtFim, null);

            $saldoFinal = $user->labcredit - $posBought - $posRets + $posGiven;
        } else {
            $saldoFinal = $user->labcredit;
        }

        return $saldoFinal;
    }
    private function calcValGiven($user, $dtIni, $dtFim) {
        $boxesGiven = BoxAlloc::listByOrigin($user->id, $dtIni, $dtFim);
        /** @var $eabx BoxAlloc */
        $valGiven = 0;
        foreach ($boxesGiven as $eabx) {
            $rem = $eabx->getRemedy();
            $valGiven += $eabx->qty * $rem->qty;
        }
        return $valGiven;
    }

    private function calcValBought($user, $dtIni, $dtFim) { 
        $listBuys = SmsBuy::listByBuyer($user->id, $dtIni, $dtFim);
        $valBought = 0;
        foreach ($listBuys as $eaBuy) {
            $valBought += $eaBuy->qty;
        }
        return $valBought;
    }

    private function getParamDates() {
        $pdtIni = $this->getRequest()->getParam('i');
        $pdtFim = $this->getRequest()->getParam('f');
        /** @var $dtIni Zend_Date */
        $dtIni = empty($pdtIni)
                ? null
                : new Zend_Date($pdtIni, 'pt_BR');
        /** @var $dtFim Zend_Date */
        $dtFim = empty($pdtFim)
                ? null
                : new Zend_Date($pdtFim, 'pt_BR');
        if ($dtFim) {
            $dtFim = $dtFim->addDay(1);
        }
        return array($dtIni, $dtFim);
    }
    
    private function getCreditosRetornados($user, $dtIni, $dtFim) {
        return CredRefund::sumQtyByUser($user->id, $dtIni, $dtFim);
    }


    public function userbalancepdfAction() {
        $id = $this->getRequest()->getParam('id');
        $user = Users::getUserById($id);

        $POST = $this->getRequest()->getPost();

        $pdtIni = $this->getRequest()->getParam('i');
        $pdtFim = $this->getRequest()->getParam('f');
        /** @var $dtIni Zend_Date */
        $dtIni = empty($pdtIni)
                ? null
                : new Zend_Date($pdtIni, 'pt_BR');
        /** @var $dtFim Zend_Date */
        $dtFim = empty($pdtFim)
                ? null
                : new Zend_Date($pdtFim, 'pt_BR');
        if ($dtFim) {
            $dtFim = $dtFim->addDay(1);
        }

        $listBuys = SmsBuy::listByBuyer($user->id, $dtIni, $dtFim);
        $valBought = 0;
        foreach ($listBuys as $eaBuy) {
            $valBought += $eaBuy->qty;
        }

        $credRets = $this->getCreditosRetornados($id, $dtIni, $dtFim);

        $boxesGiven = BoxAlloc::listByOrigin($user->id);
        /** @var $eabx BoxAlloc */
        $valGiven = 0;
        foreach ($boxesGiven as $eabx) {
            $rem = $eabx->getRemedy();
            $valGiven += $eabx->qty * $rem->qty;
        }

        require_once 'Zend/Pdf.php';
        $pdf = new Zend_Pdf();
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontBold =
                Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

        //Incluir uma Página
        $page = $this->newPdfPage();

        $page->setFont($fontBold, 14);

        $line = 700;

        if ($dtIni && $dtFim) {
            $msg = 'Período: ';
            if ($dtIni) {
                $msg .= ' de ' . $dtIni->get(Util::DATE_FORMAT);
            }
            if ($dtIni) {
                $msg .= ' até ' . $dtFim->subDay(1)->get(Util::DATE_FORMAT);
            }
            $page->drawText(
                $msg,
                41, $line);
            $line -= 50;
        }

        $page->drawText(
                ucfirst($user->label) . ': ' . $user->name,
                41 ,$line);
        $page->drawLine(30, $line-5, 570, $line-5);
        $line -= 50;

        $page->drawText(
                'Saldo anterior: ' . ($user->labcredit-$valBought+$valGiven),
                41, $line, 'UTF-8');
        $page->drawLine(30, $line-5, 570, $line-5);
        $line -= 50;

        $page->drawText(
                'Total de créditos comprados: ' . $valBought,
                41, $line, 'UTF-8');
        $page->drawLine(30, $line-5, 570, $line-5);
        $line -= 50;


        $page->drawText(
                'Total de créditos usados: ' . $valGiven,
                41, $line, 'UTF-8');
        $page->drawLine(30, $line-5, 570, $line-5);
        $line -= 50;


        $page->drawText(
                'Total de créditos retornados: ' . $credRets,
                41, $line, 'UTF-8');
        $page->drawLine(30, $line-5, 570, $line-5);
        $line -= 50;


        $page->drawText(
                'Saldo atual: ' . $user->labcredit,
                41, $line, 'UTF-8');
        $page->drawLine(30, $line-5, 570, $line-5);
        $line -= 50;

        array_push($pdf->pages, $page);

        //Mostrar o PDF na tela
        //Salvar o PDF
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="Arquivo.pdf"');
        echo $pdf->render();

        exit();
    }

    private function showDateSearch($title='Histórico', $action='history') {
        $form = new UserHistoryForm();
        $id = $this->getRequest()->getParam('id');
        $form->setAction($this->view->baseUrl()
                . '/admin/users/'.$action.'/id/' . $id);

        $this->view->title = $title;
        $this->view->type = 'form';
        //$fff = new CreditForm();
        $this->view->config = array('form' => $form->render());
    }

    public function historylistAction() {
        if ($this->getRequest()->isPost()) {

            $postValues = $this->getRequest()->getPost();

            if (!empty($postValues['query'])) {
                $filter = array(); //Zend_Json::decode($postValues['query']);
                if(!empty($postValues['dt_start'])) {
                    $start = new Zend_Date($postValues['dt_start'], 'pt_BR');
                    $filter['start'] = $start->get(Zend_Date::TIMESTAMP);
                }
                if(!empty($postValues['dt_end'])) {
                    $end = new Zend_Date($postValues['end'], 'pt_BR');
                    $filter['end'] = $end->get(Zend_Date::TIMESTAMP);
                }

                //$values = $this->view->gridValues($postValues);

                $rows = array();
                $this->view->rows = $rows;
            } else {
                $postValues['sortColumns'] = array(
                        'name'=>'name',
                        'email' => 'email');
                //$values = $this->view->gridValues($postValues);
                $rows = array();
                $this->view->rows = $rows;
            }
        }
        $this->view->title = 'Histórico de Créditos';
        $this->view->type = 'report';

        $config['group'][0]['label'] =
                'Assinante: label';
        $config['group'][0]['data'][] = array(
                'type' => 'table',
                'header' => array(
                        'Data',
                        'Hora',
                        'Nome',
                        'Celular',
                        'Opção de envio'),
                'rows' => array(array('Dt','Hr','Nm','Celu','Opçenv'))
        );
        $config['group'][0]['data'][] = array(
                'type' => 'list',
                'items' => array(array(
                                'label' => 'Total de programações de "label"',
                                'value' => 99)),
        );
        $rows = array();
        $config['group'][1]['label'] = '';
        $config['group'][1]['data'][] = array(
                'type' => 'list',
                'items' => array(
                        array(
                                'label' => 'Total de programações',
                                'value' => 100
                        )
                ),
        );
        // } // for()
        if (empty($config)) {
            $config['data'] = array(
                    0 => array(
                            'type' => 'message',
                            'message' =>
                            '<div id="reportInfo">'
                                    . 'Não existem programações.</div>'
                    )
            );
        }


        $this->view->config = $config;
    }

    /**
     * Monta os links para a coluna de ações na lista de usuários.
     * Obs: Somente admins, vendedores, labs, e dists veem lista de usuários
     *
     * @param Users $currentUser o prfil do usuário logado para ver permissões.
     * @param $seenUser um hash com as propriedades do usuário.
     *      pelo menos [id, label].
     */
    private function montaAcoesUsuario($currentUser, $seenUser) {
        $uid = $seenUser['id'];
        $acoes = '';

        // vendedor vendo
        if  ($currentUser->isSeller()) {
            $acoes = $this->view->listAction(
                    array('users', 'boxes', 'id', $uid),
                    'boxes');
        } else if ($currentUser->isAdmin()) {
            $acoes .= $this->view->listAction(
                    array('users', 'edit', 'id', $uid),
                    'edit');
            if ($seenUser['label'] == Users::PROFILE_DIST
            ||  $seenUser['label'] == Users::PROFILE_LAB
            ||  $seenUser['label'] == Users::PROFILE_SELLER
            ||  $seenUser['label'] == Users::PROFILE_REDE) 
            {
                $acoes .= $this->view->listAction(
                        array('users', 'history', 'id', $uid),
                        'history');
            }
            if ($seenUser['label'] == Users::PROFILE_LAB) 
            {
                $acoes .= $this->view->listAction(
                        array('users', 'credit', 'id', $uid),
                        'credit');
                $acoes .= $this->view->listAction(
                        array('users', 'balance', 'id', $uid),
                        'balance');
            }
        } else if ($currentUser->isLab ()) {
            if ($seenUser['label'] != Users::PROFILE_SELLER) {
                $acoes .= $this->view->listAction(
                        array('users', 'edit', 'id', $uid),
                        'edit');
                $acoes .= $this->view->listAction(
                        array('users', 'history', 'id', $uid),
                        'history');
            }
        } else if ($currentUser->isDist()) {
            // só vê redes
            $acoes .= $this->view->listAction(
                    array('users', 'boxes', 'id', $uid),
                    'boxes');
        }
        return $acoes;
    }

}
