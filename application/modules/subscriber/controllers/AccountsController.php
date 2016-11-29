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

class Subscriber_AccountsController extends HandsOn_Controller_Action {

    /** tipo: sms, lab, java */
    const DESCRFIELD_TYPE = 0;

    /** descrição */
    const DESCRFIELD_DESCRIPTION = 9;

    /** data de inicio */
    const DESCRFIELD_DT_START = 1;

    /** data de termino (null) */
    const DESCRFIELD_DT_END = 2;

    /** array de horários */
    const DESCRFIELD_TIMES = 3;

    /** reminder (bool) */
    const DESCRFIELD_REMINDER = 4;

    /** repetição */
    const DESCRFIELD_REPETT = 5;

    /** frequencia */
    const DESCRFIELD_FREQ = 6;

    /** durante... */
    const DESCRFIELD_DURING = 7;

    /** interrupção (em dias) */
    const DESCRFIELD_INTERRUPT = 8;

    /** id do remédio */
    const DESCRFIELD_REMEDY = 10;

    /** quantidade de pilulas */
    const DESCRFIELD_QTY_PILL = 11;

    /** descrição do remédio */
    const DESCRFIELD_REMDESCR = 12;

    /** quantidade de caixas */
    const DESCRFIELD_QTY_BOX = 13;

    public function init() {
        $this->_helper->acl->allow('assinante');
    }

    public function indexAction() {
        $columns = array(
            'name' => array('Nome', '380'),
            'cell_phone' => array('Celular', '150'),
            'action' => array('Ações', '100', false)
        );
        $searchItems = array(
            'name' => 'Nome',
            'cell_phone' => 'Celular'
        );
        $buttons = array(
            array('add', 'accounts/add/', 'Nova conta'),
                //array(
                //    'remove',
                //    'subscriber/accounts/modify',
                //    'Remover',
                //    'Tem certeza que deseja remover as contas selecionadas?',
                //    'Nenhuma página foi selecionada.'
                //)
        );

        $this->view->title = 'Contas';
        $this->view->type = 'list';
        $this->view->config = $this->view
                ->gridConfig($columns, $searchItems, $buttons, 'accounts/list');
    }

    public function listAction() {
        if ($this->getRequest()->isPost()) {

            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array(
                'name' => 'name',
                'cell_phone' => 'cell_phone');
            $postValues['filterColumns'] = array(
                'name' => 'name',
                'cell_phone' => 'cell_phone');
            $values = $this->view->gridValues($postValues);

            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $idUser = (null != $userIdentity) ? $userIdentity->id : null;

            $accounts = new Accounts();
            $accountList = $accounts->get($idUser, array('id', 'name', 'in_send_option', 'cell_phone'), $values, false);

            $rows = array();
            foreach ($accountList as $account) {
                $ddd = substr($account['cell_phone'], 0, 2);
                $celular = substr($account['cell_phone'], 2);

                $acoes = $this->fazAcoesDaListaParaAccount($account);
                //print($chain);
                $rows[] = array(
                    'id' => $account['id'],
                    'cell' => array(
                        $account['name'],
                        //$account['cell_phone'],
                        '(' . $ddd . ')' . $celular,
                        $acoes,
                    )
                );
            }

            $this->view->page = $values['page'];
            $this->view->total = $accounts->count(
                    $idUser, $values['filterColumn'], $values['filter'], false);
            $this->view->rows = $rows;
        }
    }

    private function fazAcoesDaListaParaAccount($account) {
        $ret = '';
        $ret = $this->view->listAction(
                array('accounts', 'edit', 'id', $account['id']), 'edit');
        $ret .= $this->view->listAction(
                array('accounts', 'programming', 'id', $account['id']), 'programming');
        $ret .= $this->view->listAction(
                array('programming', 'history', 'id', $account['id']), 'history');

        switch ($account['in_send_option']) {
            case Accounts::SEND_OPTION_JAVA:
                $ret .= $this->view->listAction(
                        array('accounts', 'sendsms', 'id', $account['id']), 'chain', null, 'infoLink');
                break;
            case Accounts::SEND_OPTION_SMS:
                $ret .= $this->view->listAction(
                        array('accounts', 'buysms', 'id', $account['id']), 'buysms');
                break;
            case Accounts::SEND_OPTION_LAB:
            default:
                break;
        }
        return $ret;
    }

    public function buysmsAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $form = new CreditForm(array('id' => $id));
        $form->setAction($this->view->baseUrl()
                . '/subscriber/accounts/buysms/id/' . $id);

        $account = Accounts::getAccountById($id);
        $values = array(
            'name' => $account->name,
            'credit' => $account->credit);

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $valComprado = $form->getValue('credit');

                if (($account->credit + $valComprado) <= 500) {
                    $account->credit += $valComprado; // SOMA!

                    $account->saveCredit();

                    $smsb = new SmsBuy();
                    $smsb->id_account = $account->id;
                    $smsb->qty = $valComprado;
                    $smsb->save();

                    $this->view->response = 'formSuccess';
                    $this->view->message = 'Créditos de <b>"'
                            . $account->name . '"</b> atualizados com sucesso!'
                            . '<br/><br/>'
                            . 'Lembre-se de que todos os créditos comprados '
                            . 'para as contas serão cobrados posteriormente.';
                    $this->view->redirect = '';
                    $this->_formatFileFormResponseView();
                    return;
                } else {
                    $this->view->response = 'formError';
                    $this->view->message = 'Atenção:<br/> '
                            . 'As contas não podem ter mais de 500 créditos.';
                    $form->populate($form->getValues());
                }
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

        $this->view->title = 'Compra SMS';
        $this->view->type = 'form';

        $view = new Zend_View();
        //$view->sendOption = $account->in_send_option;
        $view->username = $account->name;
        $view->cellnumber = $account->cell_phone;
        $view->actual = $account->credit;
        $view->setBasePath(APPLICATION_PATH . '/modules/subscriber/views/');
        $view->actionUrl = $this->view->baseUrl()
                . '/subscriber/accounts/buysms/id/' . $id;
        $this->view->config = array('form' => $view->render('programming/buysms.phtml'));
    }

    public function addAction() {

        $status = "new";

        if (isset($_GET["check"])) {
            $status = "login";
        }

        $autourl = $this->view->baseUrl() . '/subscriber/accounts/autocomplete';
        $form = new AccountForm($autourl);


        $form->setAction($this->view->baseUrl() . '/subscriber/accounts/add');
        // no add não tem code.
        $form->removeElement('code');

        /** @var Users */
        $cuser = Users::getLoggedUser();

        if ($this->getRequest()->isPost()) {
            if ($this->addFormPosted($form, $cuser)) {
                return;
            }
        }

        //$view->sendOption = $account->in_send_option;
        $data = array("extra" => $status);
        $form->setDefaults($data);
//       $form->populate($data);

        $this->view->title = 'Adicionar nova conta';
        $this->view->type = 'form';

        $this->view->config = array('form' => $form->render());
    }

    /**
     * Valida o $form e adiciona a conta, se for o caso.
     *
     * @param AccountForm $form
     * @param Users $cuser usuário logado
     * @return bool true pra formSuccess; false pra formError
     */
    private function addFormPostedNew($form, $cuser) {


//        $termChecked = $form->getValue('term_of_use_check[]');


        /* @var $existCelltoSubscriber <type> */
        $existCelltoSubscriber = Accounts::getCellFromSubscriber(
                        $this->getRequest()->getParam('cell_phone'), $cuser->id);
        ;
        if (!empty($existCelltoSubscriber)) {
            
            return $existCelltoSubscriber['id'];
            
        } else {
            $phone = $this->getRequest()->getParam('cell_phone');
            $phone = Util::cleanPhoneNumber($phone);

            $inSendOption = 3;

            $isActive = false;
            if ($inSendOption == Accounts::SEND_OPTION_JAVA) {
                $isActive = Accounts::isActiveJAVAForNumber($phone);
            } else {
                $isActive = Accounts::isActiveSMSForNumber($phone);
            }

            $res = $this->doSaveAddForm($cuser->id, $form, $isActive);

            $messageComplement = '';
            if (!$isActive && $inSendOption != Accounts::SEND_OPTION_JAVA) {
                $messageComplement = '<br/><br/>'
                        . 'A conta foi criada e já pode receber avisos';
            }


            return $res->id;
        }
    }

    private function addFormPosted($form, $cuser) {
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->response = 'formError';
            $this->view->message = 'Alguns campos foram preenchidos de maneira '
                    . 'incorreta. Verifique os campos destacados '
                    . 'em vermelho.';
            return false;
        }

        $termChecked = $form->getValue('term_of_use_check[]');
        if (!empty($termChecked)) {
            $this->view->response = 'formError';
            $this->view->message = 'O termo de uso deve ser aceito!';
            return false;
        }

        /* @var $existCelltoSubscriber <type> */
        $existCelltoSubscriber = Accounts::getCellFromSubscriber(
                        $form->getValue('cell_phone'), $cuser->id);

        if (!empty($existCelltoSubscriber)) {
            $this->view->response = 'formError';
            $this->view->message = 'Celular já cadastrado para seu usuário!';
            return false;
        }

        $phone = $form->getValue('cell_phone');
        $phone = Util::cleanPhoneNumber($phone);

        $inSendOption = $form->getValue('in_send_option');

        $isActive = false;
        if ($inSendOption == Accounts::SEND_OPTION_JAVA) {
            $isActive = Accounts::isActiveJAVAForNumber($phone);
        } else {
            $isActive = Accounts::isActiveSMSForNumber($phone);
        }
        $res = $this->doSaveAddForm($cuser->id, $form, $isActive);

        $messageComplement = '';
        if (!$isActive && $inSendOption != Accounts::SEND_OPTION_JAVA) {
            $messageComplement = '<br/><br/>'
                    . 'A conta foi criada e já pode receber avisos';
            // SEMOPTIN - este código era gerado para o optin inicial
            // . 'Atenção, é necessário colocar o código de '
            // . 'ativação que a conta receberá no celular '
            // . 'para começar a receber os avisos. Para '
            // . 'voltar a esta tela e colocar o código de '
            // . 'ativação, basta selecionar o botão Editar '
            // .'da conta desejada.';
        }
        $this->view->response = 'formSuccess';
        $this->view->message = 'Conta <b>"'
                . $form->getValue('name')
                . '"</b> cadastrada com sucesso!'
                . $messageComplement;


        if ($this->getRequest()->getPost("extra") == "new") {
            $this->view->redirect = 'accounts';
        } else {
            $this->view->redirect = 'accounts/programming/id/' . $res->id;
        }

        return true;
    }

    /**
     * Extraido do addActon que tava muito grande.
     * 
     * @param int $idUser
     * @param AccountForm $form
     * @param bool $alreadyActive
     * @return o id da Account recem criada.
     */
    private function doSaveAddForm($idUser, $form, $alreadyActive) {
//        $inSendOption = $form->getValue('in_send_option');
        $inSendOption = 3;
        $phone = Util::cleanPhoneNumber($this->getRequest()->getParam('cell_phone'));
        $newAccount = new Accounts(array(
            'id_user' => $idUser,
//            'name' => $form->getValue('name'),
            'name' => "user",
            'cell_phone' =>
            $phone,
            'in_send_option' =>
            $inSendOption,
            'term_of_use_check' =>
//            $form->getValue('term_of_use_check'),
            1,
        ));

        if ($alreadyActive) {
            if ($newAccount->isOptJava()) {
                $newAccount->code = Accounts::getCodeForCell($newAccount->cell_phone);
                $newAccount->in_executed_download = 'Y';
            } else {
                $newAccount->is_active = 1;
            }
        }
        $data = $newAccount->save();
        if ($alreadyActive && $newAccount->isOptJava()) {
            $newAccount->setActive(true);
        }

        if (!$alreadyActive) {
            switch ($inSendOption) {
                case Accounts::SEND_OPTION_JAVA:
                    SMSSender::sendSMS(array(
                        'phone' => $phone,
                        'message' =>
                        'Voce foi cadastrado no sistema Lembre '
                        . 'Facil! Segue o link para download do '
                        . 'aplicativo. '
                        . 'http://www.lembrefacil.com.br/app/'
                        . $newAccount->code));
                    break;
                case Accounts::SEND_OPTION_LAB:
                case Accounts::SEND_OPTION_SMS:
                /*
                  SMSSender::sendSMS(array(
                  'phone' => $phone,
                  'message' =>
                  'Voce foi cadastrado no sistema Lembre '
                  .'Facil! Seu codigo de ativacao e "'
                  . $newAccount->activation_code
                  . '".'
                  ));
                 */
            }
        }
        return $newAccount;
    }

    public function editAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $form = new AccountForm(array('id' => $id));
        $form->setAction(
                $this->view->baseUrl() . '/subscriber/accounts/edit/id/' . $id);
        $elmISO = $form->getElement('in_send_option');
        $elmISO->setAttrib('disabled', 'disabled');
        $elmISO->setRequired(false);

        $account = new Accounts(array('id' => $id));
        $account->read(array(
            'name', 'cell_phone', 'in_send_option',
            'is_active', 'activation_code'));
        $mensagemAtivacao = '';

        if ($account->is_active != 0 || $account->in_send_option == Accounts::SEND_OPTION_JAVA) {
            // tipo java e contas já ativas.
            $form->removeElement('code');
        }

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $termChecked = $form->getValue('term_of_use_check');
                if (!empty($termChecked)) {
                    $existCell = Accounts::getCell(
                                    $form->getValue('cell_phone'));
                    $existCelltoSubscriber = Accounts::getCellFromSubscriber(
                                    $form->getValue('cell_phone'), $id);

                    if (!empty($existCelltoSubscriber)) {
                        $this->view->response = 'formError';
                        $this->view->message = 'Celular já cadastrado para seu usuário!';
                    } else {
                        $account->id = $id;
                        $account->name = $form->getValue('name');
                        $account->cell_phone = $form->getValue('cell_phone');
                        //$account->in_send_option =
                        //       $form->getValue('in_send_option');
                        $account->term_of_use_check = $form->getValue('term_of_use_check');

                        $formcode = $form->getValue('code');

                        if ($account->is_active == 0 && !empty($formcode)) {

                            if ($account->activation_code == $formcode) {
                                $account->is_active = 1;
                                $mensagemAtivacao = '<br/><br/>A conta foi ativada, '
                                        . 'está pronta para receber mensagens SMS';
                            } else {
                                $mensagemAtivacao = '<br/><br/>o código de '
                                        . 'ativação está incorreto! A sua conta '
                                        . ' NÃO foi ativada';
                            }
                        }

                        $code = $account->save();

                        $sendOpt = $form->getValue('in_send_option');

                        if (($sendOpt == Accounts::SEND_OPTION_JAVA) && (
                                empty($existCell) ||
                                ($existCell['in_executed_download'] == 'N')
                                )) {
                            SMSSender::sendSMS(array(
                                'phone' => $existCell['cell_phone'],
                                'message' => 'Voce foi cadastrado no sistema '
                                . 'Lembre Facil! '
                                . 'Segue o link para download do aplicativo. '
                                . 'http://www.lembrefacil.com.br/app/' . $code));
                        }

                        $this->view->response = 'formSuccess';
                        $this->view->message = 'Conta <b>"'
                                . $form->getValue('name')
                                . '"</b> atualizado com sucesso!'
                                . $mensagemAtivacao;
                        $this->view->redirect = 'accounts';
                        return;
                    }
                } else {
                    $this->view->response = 'formError';
                    $this->view->message = 'O termo de uso deve ser aceito!';
                }
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de maneira incorreta. '
                        . 'Verifique os campos destacados em vermelho.';
                $form->populate($form->getValues());
            }
        } else {
            $ddd = substr($account->cell_phone, 0, 2);
            $celular = substr($account->cell_phone, 2);
            $values = array(
                'term_of_use_check' => '1',
                'name' => $account->name,
                'cell_phone' => '(' . $ddd . ')' . $celular,
                'in_send_option' => $account->in_send_option);
            $form->populate($values);
        }

        $this->view->title = 'Editar Conta: ' . $account->name;
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
    }

    public function modifyAction() {
        if ($this->getRequest()->isPost()) {
            $account = new Accounts(array('id' => (int) $this->getRequest()->getPost('id')));
            switch ($this->getRequest()->getPost('command')) {
                case 'block':
                    $account->lock();
                    break;
                case 'unblock':
                    $account->unlock();
                    break;
                case 'delete':
                    $account->delete();
                    break;
            }
        }
    }

    public function sendsmsAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $accounts = new Accounts();
        $account = new Accounts(array('id' => $id));
        $account->read(array('name', 'cell_phone', 'code'));
        SMSSender::sendSMS(array(
            'phone' => $account->cell_phone,
            'message' =>
            'Lembre Facil! Segue o link para download do aplicativo. '
            . 'http://www.lembrefacil.com.br/app/' . $account->code));
        $this->_forward('index', 'accounts', 'subscriber');
    }

    public function programmingAction() {

        $identity = Zend_Auth::getInstance()->getIdentity();

//        $idAccount = (int) $this->getRequest()->getParam('id');
//        $account = Accounts::getAccountById($idAccount);

        if ($this->getRequest()->isPost()) {
            $check = TRUE;
            $end_date_flag = 0;
            if ($this->getRequest()->getParam('use_continue')) {
                $end_date_flag = 1;
            }
            if (empty($this->getRequest()->getParam('cell_phone')) || ($this->getRequest()->getParam('cell_phone') == "" ) || (strlen($this->getRequest()->getParam('cell_phone')) == 0) || empty($this->getRequest()->getParam('descriptionsProgrammingValues'))) {
                $check = FALSE;
            }


            $descriptions = Zend_Json::decode(
                            $this->getRequest()->getParam(
                                    'descriptionsProgrammingValues'));
            $checkform = Zend_Json::decode(
                            $this->getRequest()->getParam(
                                    'checkform'));


            $idUser = Zend_Auth::getInstance()->getIdentity()->id;
            if ((!empty($descriptions) || ($checkform == 1)) && $check) {
                try {

                    $autourl = $this->view->baseUrl() . '/subscriber/accounts/autocomplete';
                    $form = new AccountForm($autourl);

                    $form->removeElement('code');
                    $cuser = Users::getLoggedUser();
                    $idAccount = $this->addFormPostedNew($form, $cuser);
                    if ($idAccount) {
//                   return;
                    }

                    Zend_Db_Table::getDefaultAdapter()->beginTransaction();
                    $programming = $this->createProgramming($idUser, $idAccount);
                     $account = Accounts::getAccountById($idAccount);
                    if (is_array($descriptions))
                        foreach ($descriptions as $d) {
                            $description = $this->makeDescription($d, $programming, $end_date_flag);

                            if ($description === false) {
                                $this->view->response = 'formError';
                                $this->view->message = 'Alguns campos foram preenchidos de maneira '
                                        . 'incorreta. Faça a sua programação novamente.';
                            } else {
                                if ($d[self::DESCRFIELD_TYPE] == Accounts::SEND_OPTION_LAB) {
                                    $credGanho = $this->descontaRemedio(
                                            $idUser, $d[self::DESCRFIELD_REMEDY], $d[self::DESCRFIELD_QTY_PILL], $d[self::DESCRFIELD_QTY_BOX]
                                    );

                                    $description->rcredit = $credGanho;
                                    $description->id_remedy = $d[self::DESCRFIELD_REMEDY];

                                    if ($d[self::DESCRFIELD_QTY_PILL]) {
                                        $this->retornaCreditoLab(
                                                $d[self::DESCRFIELD_REMEDY], $d[self::DESCRFIELD_QTY_PILL], $account);
                                    }
                                }

                                $description->saveDescription();

                                //Schedule::updateSchedule();
                            }
                        }
                    $this->view->response = 'formSuccess';
//                    $this->view->message = 'Programação para <b>"';
                    $this->view->message = 'Programação realizada com sucesso!';
//                            . '"</b> realizada com sucesso!';
                    $this->view->redirect = 'accounts/programming/';
                    Zend_Db_Table::getDefaultAdapter()->commit();
                    return;
                } catch (Exception $ex) {
                    Zend_Db_Table::getDefaultAdapter()->rollBack();
                    $this->view->response = 'formError';
                    $this->view->message = $ex->getMessage();
                }
            } else {

                if ($check == FALSE) {
                    $this->view->response = 'formError';
                    $this->view->message = 'Sem dados suficientes para criar nova conta';
                } else {
                    $this->view->response = 'formError';
                    $this->view->message = 'Nenhuma descrição foi adicionada.';
                }
            }
        } else {
            $this->view->response = 'formError';
            $this->view->message = 'Alguns campos foram preenchidos de maneira incorreta. '
                    . 'Verifique os campos destacados em vermelho.';
        }

        $this->view->title = 'Nova programação';
        $this->view->type = 'form';
        $view = new Zend_View();
//        $view->sendOption = $account->in_send_option;
        $view->setBasePath(APPLICATION_PATH . '/modules/subscriber/views/');
        $view->actionUrl = $this->view->baseUrl()
                . '/subscriber/accounts/programming';

        /** @var string $renderForm */
        $renderForm = '';
//        if ($account->in_send_option == Accounts::SEND_OPTION_LAB) {
        $view->remedySelect = HtmlUtils::makeSelectForRemedy(
                        RemedyDAO::listRemediesAvailableForUser($identity->id));
        $renderForm = 'programming/addRemedy.phtml';
//        } else {
//            $renderForm = 'programming/add.phtml';
//        }
        $this->view->config = array('form' => $view->render($renderForm));
    }

    /**
     *
     * @param int $idRem
     * @param int $jaDesc
     */
    private function retornaCreditoLab($idRem, $jaDesc, $account) {
        $rem = Remedy::getById($idRem);
//        $volta = $rem->qty - $jaDesc;
        $volta = $jaDesc;
        error_log('retornaCreditoLab:volta ' . $volta);
        if ($volta > 0) {
            $lab = Users::getUserById($rem->id_owner);
            $lab->labcredit += $volta;
            $lab->saveLabCredit();

            $refund = new CredRefund();
            $refund->id_user = $rem->id_owner;
            $refund->id_account = $account->id;
            $refund->qty = $volta;
            $refund->save();
        }
    }

    private function descontaRemedio($idUser, $idRem, $qtPill, $qtBox) {
        $remsAllocs = BoxAlloc::listByAllocatedRem($idUser, $idRem);
        if (($qtBox * $qtPill) != 0 ||
                ($qtBox + $qtPill) == 0
        ) {
            throw new Exception('É necessário quantidade de caixas ou pilulas');
        }
        $totCred = 0;
        if ($qtBox) {
            // desconta $qtBox caixas e retorna o total de remédios delas.
            foreach ($remsAllocs as $cadaAlloc) {
                if ($cadaAlloc->qty > $cadaAlloc->used) {
                    $restante = $cadaAlloc->qty - $cadaAlloc->used;
                    $qtdesc = min($restante, $qtBox);
                    $cadaAlloc->used += $qtdesc;
                    $cadaAlloc->save();
                    $qtBox -= $qtdesc;

                    $totCred += $qtdesc * $cadaAlloc->getRemedy()->qty;

                    if ($qtBox == 0) {
                        break;
                    }
                }
            }
//            if ($qtBox > 0) {
//                throw new Exception('Não há remédios suficientes para esta programação');
//            }
        } else if ($qtPill) {
            foreach ($remsAllocs as $cadaAlloc) {
                if ($cadaAlloc->qty > $cadaAlloc->used) {
                    ++$cadaAlloc->used;
                    $cadaAlloc->save();
                    $totCred += $qtPill;
                    break;
                }
            }
        }
        return $totCred;
    }

    /**
     * Cria a programação e salva, setando o id.
     *
     * @param int $idUser
     * @param int $idAccount
     */
    private function createProgramming($idUser, $idAccount) {
        $programming = new Programming(array(
            'id_subscriber' => $idUser,
            'id_account' => $idAccount,
        ));

        $programming->save(); // já seta o novo id_programming
        return $programming;
    }

    /**
     * Converte o array de dados do form para uma descrição de programação e
     * salva.
     * @param array $data os dados do form
     * @param Programming $programming a programação (só pra ver o ID)
     * @return Programming|bool a programação ou false (===)
     */
    private function makeDescription($data, $programming, $end_date_flag) {
        $error = 0;

        $tipo = $data[self::DESCRFIELD_TYPE];

        if (!is_numeric($programming->id_programming)) {
            ++$error;
        }
        /** @var string */
        $ddesc = ($tipo == Accounts::SEND_OPTION_LAB) ? $data[self::DESCRFIELD_REMDESCR] : $data[self::DESCRFIELD_DESCRIPTION];
        if (mb_strlen($ddesc) > 150 && !empty($ddesc)) {
            ++$error;
        }
        /** @var Zend_Date */
        $dtStart = empty($data[self::DESCRFIELD_DT_START]) ? Zend_Date::now() : new Zend_Date($data[self::DESCRFIELD_DT_START], 'pt_BR');
        /** @var array */
        $dhor = $data[self::DESCRFIELD_TIMES];
        if (!is_array($dhor) OR empty($dhor)) {
            ++$error;
        }
//        $drep = $data[self::DESCRFIELD_REPETT];
       
        if($data[self::DESCRFIELD_QTY_BOX] == ''){
            $drep = 1;
        }else{
            $drep = $data[self::DESCRFIELD_QTY_BOX];
        } 
        
        if($data[self::DESCRFIELD_QTY_PILL] == ''){
            $rem = Remedy::getById($data[self::DESCRFIELD_REMEDY]);
            $qtd_new = $rem->qty;
        }else{
            $qtd_new = $data[self::DESCRFIELD_QTY_PILL];
        } 
        
//        switch ($drep) {
//            case 0:
//            case 1:
//            case 2:
//                break;
//            default:
//                ++$error;
//        }
        /** @var Zend_Date */
      
        if ($end_date_flag == 0) {
            $dateEnd = !empty($data[self::DESCRFIELD_DT_END]) ? new Zend_Date($data[self::DESCRFIELD_DT_END], 'pt_BR') : null;
        } else {
            $dateEnd = NULL;
        }

        $description = new Programming(array(
            'id_programming' => $programming->id_programming,
            'description' => $ddesc,
            'dt_start' => $dtStart,
            'dt_end' => $dateEnd,
            'times' => $dhor,
            'reminder' => $data[self::DESCRFIELD_REMINDER],
            'in_repetition' => $drep,
            'in_frequency' => 1,
            'week_days' => null,
            'month_days' => null,
            'day' => null,
            'month' => null,
            'during_days' => null,
            'interrupt_days' => null,
            'interval_days' => null,
            'qtd_new' => $qtd_new
        ));
        //exit();
        switch ($data[self::DESCRFIELD_REPETT]) {
            case 1:
                $dfreq = $data[self::DESCRFIELD_FREQ];
                $description->in_frequency = $data[self::DESCRFIELD_FREQ];
                switch ($dfreq) {
                    case 1: // sem frequencia.
                        break;
                    case 2:
                        $ddur = $data[self::DESCRFIELD_DURING];
                        if (!is_array($ddur) || empty($ddur)) {
                            ++$error;
                        }
                        $description->week_days = $ddur;
                        break;
                    case 3:
                        $ddur = $data[self::DESCRFIELD_DURING];
                        if (!is_array($ddur) || empty($ddur)) {
                            ++$error;
                        }
                        $description->month_days = $ddur;
                        break;
                    case 4:
                        $ddur = $data[self::DESCRFIELD_DURING];
                        if (!is_numeric($ddur) || empty($ddur)) {
                            ++$error;
                        }
                        $description->day = $ddur;
                        $dint = $data[self::DESCRFIELD_INTERRUPT];
                        if (!is_numeric($dint) || empty($dint)) {
                            ++$error;
                        }
                        $description->month = $dint;
                        break;
                    default:
                        ++$error;
                }
                break;
            case 2:
                $dfreq = $data[self::DESCRFIELD_FREQ];
                $description->in_frequency = $dfreq;
                switch ($dfreq) {
                    case 0:
                        $ddur = $data[self::DESCRFIELD_DURING];
                        if (!is_numeric($ddur) || empty($ddur)) {
                            ++$error;
                        }
                        $description->during_days = $ddur;
                        $dint = $data[self::DESCRFIELD_INTERRUPT];
                        if (!is_numeric($dint) || empty($dint)) {
                            ++$error;
                        }
                        $description->interrupt_days = $dint;
                        break;
                    case 1;
                        $ddur = $data[self::DESCRFIELD_DURING];
                        if (!is_numeric($ddur) || empty($ddur)) {
                            ++$error;
                        }
                        $description->interval_days = $ddur;
                        break;
                    default:
                        ++$error;
                        break;
                }
                break;
        }

// error_log(var_export($description, true));

        return $error ? false : $description;
    }

    public function autocompleteAction() {
        if ($this->getRequest()->isPost()) {
            $cellPhone = $this->getRequest()->getParam('cell_phone');
            $account = Accounts::getAccountByCell($cellPhone);
            if (count($account) > 0) {
                echo json_encode($account[0]->toArray());
            }
            exit();
        }
    }

}
