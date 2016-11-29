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


class Admin_RemedyController extends HandsOn_Controller_Action {
    public function init() {
        $this->_helper->acl->allow(Users::PROFILE_ADMIN);
        $this->_helper->acl->allow(Users::PROFILE_FUNC);
        $this->_helper->acl->allow(Users::PROFILE_LAB);
    }

    public function indexAction() {
        $userIdentity = Zend_Auth::getInstance()->getIdentity();
        $userLabel = Users::getProfileLabel($userIdentity->id_profile);
        $columns = array(
           'name'     => array('Remédio', 150),
           'qty'        => array('Quantidade', 150),
           'approval'   => array('Status', 100),
           'action'     => array('Ações', 80, false),
        );
        $searchItems = array(
           'name' => 'Remédio',
        );
        $buttons = $this->montaBotoesGrid($userLabel);

        $this->view->title = 'Remédios';
        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig(
                $columns, $searchItems, $buttons, 'remedy/list');
    }

    public function listAction() {
        if ($this->getRequest()->isPost()) {
            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array(
                    'name' => 'name',
                    'qty'    => 'qty',
                    'approval' => 'approval');
            $postValues['filterColumns'] = array(
                    'name' => 'name',
                    );
            $values = $this->view->gridValues($postValues);
            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $idProfile = (null != $userIdentity)
                    ? $userIdentity->id_profile : null;
            $labelProfile = Users::getProfileLabel($idProfile);
            if ($labelProfile == Users::PROFILE_LAB) {
                $values['filterOwner'] = $userIdentity->id;
            }

            $remedyList = Remedy::get(
                    array('*'),
                    $values);

            $rows = array();
            foreach ($remedyList as $earemedy) {
                $acoes = $this->montaAcoesRemedio($earemedy, $labelProfile);
                $rows[] = array(
                    'id' => $earemedy['id'],
                    'cell' => array(
                        $earemedy['name'],
                        $earemedy['qty'],
                        $earemedy['approval'] ? 'Aprovado' : 'Pendente',
                        $acoes
                    )
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = Remedy::count($idProfile, $values);
            $this->view->rows = $rows;
        }
    }

    public function addAction() {
        $form = new RemedyForm();
        $form->setAction($this->view->baseUrl() . '/admin/remedy/add');

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                // os Laboratórios são "donos" dos vendedores
                $luser = $this->getLoggedUser();

                $remedy = new Remedy(array(
                    'name'      => $form->getValue('name'),
                    'descr'     => $form->getValue('descr'),
                    'qty'       => $form->getValue('qty'),
                    'val'       => $form->getValue('val'),
                    'id_owner'  => $luser->id,
                    ));

                $remedy->save();

                Util::sendEmailToAdministrator(
                    'Novo remédio cadastrado: ' . $remedy->name,
                    'Um novo remédio foi cadastrado e aguarda sua '
                    . 'aprovação:' . "\n\n"
                    . 'Remédio: ' . $remedy->name . "\n"
                    . 'Usuário: ' . $luser->name . "\n"
                    . 'http://www.lembrefacil.com.br/admin'
                    . "\n\n"
                );

                $this->view->response = 'formSuccess';
                $this->view->message = 'Remédio <b>"'
                        . $remedy->name
                        . '"</b> cadastrado com sucesso!';
                $this->view->redirect = 'remedy';
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de '
                        . 'maneira incorreta. Verifique os campos destacados '
                        . 'em vermelho.';
            }
        }

        $this->view->title = 'Adicionar novo remédio';
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());

    }

    public function editAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $form = new RemedyForm(array('id' => $id));
        $form->setAction($this->view->baseUrl() . '/admin/remedy/edit/id/'.$id);
        $luser = $this->getLoggedUser();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $remedy = new Remedy(array(
                    'id'    => $id,
                    'name'  => $form->getValue('name'),
                    'descr'  => $form->getValue('descr'),
                    'qty'   => $form->getValue('qty'),
                    'val'   => $form->getValue('val'),
                ));
                $remedy->read(array('id_owner'));
                $remedy->approval = 0;
                $remedy->save();

                Util::sendEmailToAdministrator(
                    'Novo remédio cadastrado: ' . $remedy->name,
                    'Um remédio foi editado e aguarda sua '
                    . 'aprovação:' . "\n\n"
                    . 'Remédio: ' . $remedy->name . "\n"
                    . 'Usuário: ' . $luser->name . "\n"
                    . 'http://www.lembrefacil.com.br/admin'
                    . "\n\n"
                );

                $this->view->response = 'formSuccess';
                $this->view->message = 
                        'Remédio <b>"'
                        . $form->getValue('name')
                        . '"</b> atualizado com sucesso!';
                $this->view->redirect = 'remedy';
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
            $remedy = new Remedy(array('id' => $id));
            $remedy->read(array('name', 'descr','qty','val'));
            $values = array(
                    'name' => $remedy->name,
                    'descr' => $remedy->descr,
                    'qty'  => $remedy->qty,
                    'val'  => $remedy->val);
            $form->populate($values);
        }

        $this->view->title = 'Editar Remédio: ' . $remedy->name;
        $this->view->type = 'form';
        //$fff = new CreditForm();
        $this->view->config = array('form' => $form->render());

    }

    public function modifyAction() {
        if ($this->getRequest()->isPost()) {
            $rid = (int)$this->getRequest()->getPost('id');
            $remedy = new Remedy(array('id' => $rid));

            switch ($this->getRequest()->getPost('command')) {
                case 'delete':  $remedy->delete(); break;
                case 'approve': $remedy->approve(); break;
            }
        }
    }

    /**
     * Monta a coluna dos botoes de acoes para os remédios.
     * Botao de aprovação se o perfiol do usuário atual for Admin ou Func.
     * @param <HashResultSet> $remedio um objeto genérico com algumas
     *      propriedades do remedio
     */
    private function montaAcoesRemedio($remedio, $labelProfile) {
        $acoes = '';
        if ($labelProfile == Users::PROFILE_LAB
                || $labelProfile == Users::PROFILE_ADMIN) {
            $acoes = $this->view->listAction(
                    array('remedy/edit/id', $remedio['id']),
                    'edit');
        }
        return $acoes;
    }

    /**
     * Monta o array de botões para o grid de remédios. Implementa a lógica
     * de quem vê o quê.
     * @param <type> $userLabel o prfil do usuario logado
     * @return <type> array com os botões permitidos para o userLabel
     */
    function montaBotoesGrid($userLabel) {
        $butAdd = array('add', 'remedy/add/', 'Novo remédio');
        $butDel = array('remove',
                'admin/remedy/modify',
                'Remover',
                'Tem certeza que deseja remover os remédios selecionados?',
                'Nenhum remédio foi selecionada.'
                );
        $butAppr = array('approve',
                'admin/remedy/modify',
                'Aprovar',
                'Tem certeza que deseja aprovar os remédios selecionados?',
                'Nenhum remédio foi selecionado.'
                );
        switch ($userLabel) {
            case Users::PROFILE_ADMIN:
                return array($butDel, $butAppr);
                break;
            case Users::PROFILE_FUNC:
                return array($butAppr);
                break;
            case Users::PROFILE_LAB:
                return array($butAdd, $butDel);
                break;
            default:
                return array();
                break;
        }
    }


}
