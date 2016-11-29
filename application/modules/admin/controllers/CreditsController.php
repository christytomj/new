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


class Admin_CreditsController extends HandsOn_Controller_Action
{

    public function init()
    {
        $this->_helper->acl->allow('administrativo');
        $this->_helper->acl->allow('financeiro1');
        $this->_helper->acl->allow('financeiro2');
    }

    public function indexAction()    
    {
        $columns = array(
           'name' => array('Nome', 165),
           'cell_phone' => array('Celular', 80),
           'subscriber' => array('Assinante', 165),
           'credit' => array('Créditos', 50),
           'action' => array('Ações', 50, false)

        );
        $searchItems = array(
           'a.name' => 'Nome',
           'cell_phone' => 'Celular'
        );
        $buttons = array(
            array('remove',
                  'admin/credits/modify',
                  'Remover',
                  'Tem certeza que deseja remover as contas selecionadas?',
                  'Nenhuma página foi selecionada.'
            )
        );                    

        $this->view->title = 'Contas SMS';
        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig(
                $columns, $searchItems, $buttons, 'credits/list');
    }

    public function listAction()
    {
        if ($this->getRequest()->isPost()) {

            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array(
                'name'=>'name',
                'cell_phone' => 'cell_phone',
                'credit' => 'credit',
                'subscriber' => 'subscriber');
            $postValues['filterColumns'] = array(
                'a.name' => 'a.name',
                'cell_phone' => 'cell_phone');
            $values = $this->view->gridValues($postValues);

            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $idUser = (null != $userIdentity) ? $userIdentity->id : null;

            $accounts = new Accounts();
            $accountList = $accounts->get(
                $idUser,
                array('id', 'name', 'cell_phone', 'id_user', 'credit', 
                    'subscriber', 'in_send_option'),
                $values,
                true);

            $rows = array();
            foreach ($accountList as $account) {
                $ddd = substr($account['cell_phone'], 0, 2);
                $celular = substr($account['cell_phone'], 2);
                $isSMS = ($account['in_send_option']
                            == Accounts::SEND_OPTION_SMS);

                $rows[] = array(
                    'id' => $account['id'],
                    'cell' => array(
                        $account['name'],
                        '('.$ddd.')'.$celular,
                        $account['subscriber'],
                        $isSMS ? $account['credit'] : '-',
                        $isSMS
                        ? $this->view->listAction(
                            array('credits', 'edit', 'id', $account['id']),
                            'buysms')
                        : null
                    )
                );
            }

            $this->view->page = $values['page'];
            $this->view->total = $accounts->count(
                    $idUser, $values['filterColumn'], $values['filter'], true);
            $this->view->rows = $rows;
        }
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $form = new CreditForm(array('id' => $id));
        $form->setAction(
                $this->view->baseUrl() . '/admin/credits/edit/id/' . $id);

        $account = Accounts::getAccountById($id);
        $values = array(
            'name' => $account->name,
            'cell_phone' => $account->cell_phone,
            'credit' => $account->credit);


        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $valVelho = $account->credit;
                $valNovo = $form->getValue('credit');

                if ($valNovo != $valVelho) {
                    $account->credit = $valNovo;

                    $account->saveCredit();

                    $smsb = new SmsBuy();
                    $smsb->id_account = $account->id;
                    $smsb->qty = $valNovo - $valVelho;
                    $smsb->save();
                    $this->view->message = 'Créditos de <b>"'
                            . $account->name
                            . '"</b> atualizados com sucesso!';
                } else {
                    $this->view->message = 'Créditos de <b>"'
                            . $account->name
                            . '"</b> inalterados!';
                }

                $this->view->response = 'formSuccess';
                $this->view->redirect = 'credits';
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

//        $ddd = substr($account->cell_phone, 0, 2);
//        $celular = substr($account->cell_phone, 2);
//
//        $this->view->title = 'Editar Créditos: (' . $ddd . ')' . $celular;
        $this->view->title = 'Editar Créditos: ' . $account->name
                        . ' - ' . $account->cell_phone;
        $this->view->type = 'form';

        /*
        $view = new Zend_View();

        $view->actual = $account->credit;
        $view->setBasePath(APPLICATION_PATH . '/modules/admin/views/');
        $view->actionUrl = $this->view->baseUrl()
                . '/admin/credits/edit/id/' . $id;
         *
         */
        $this->view->config =
                array('form' => $form->render());

        //$this->view->config = array('form' => $form->render());
        //$this->_formatFileFormResponseView();
    }

    public function modifyAction()
    {
        if ($this->getRequest()->isPost()) {
            $account = new Accounts(array(
                'id' => (int)$this->getRequest()->getPost('id')));
            switch ($this->getRequest()->getPost('command')) {
                case 'block':   $account->lock(); break;
                case 'unblock': $account->unlock(); break;
                case 'delete':  $account->delete(); break;
            }
        }
        $this->view->response = 'formSuccess';
        $this->view->redirect = 'admin';

    }
}