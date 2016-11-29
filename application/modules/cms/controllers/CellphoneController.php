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

class Cms_CellphoneController extends HandsOn_Controller_Action {
    public function init() {
        $this->_helper->acl->allow(null);
    }

    public function indexAction() {
        $columns = array(
            'manufacturer' => array('Fabricante', 200),
            'model' => array('Modelo', 181),
            'action' => array('Ações', 80, false)
        );

        $searchItems = array(
            'model' => 'Modelo'
        );

        $buttons = array(
            array('add', 'cellphone/add/', 'Novo modelo'),
            array('remove',
            'cms/cellphone/modify',
            'Remover',
            'Tem certeza que deseja remover os modelos selecionados?',
            'Nenhuma página foi selecionada.'
            )
        );

        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig($columns, $searchItems, $buttons, 'cellphone/list');
        $this->view->title = 'Lista de Modelos';
    }

    public function listAction() {
        $postValues = $this->_request->getPost();
        $postValues['sortColumns'] = array('manufacturer'=>'manufacturer', 'model' => 'model');
        $postValues['filterColumns'] = array('model' => 'model');
        $values = $this->view->gridValues($postValues);

        if ($this->_request->isPost()) {
            $cellphone = new Cellphone();
            $cellPhoneList = $cellphone->getCellPhones(array('id', 'manufacturer', 'model'), $values);

            $rows = array();
            foreach ($cellPhoneList as $content) {
                $rows[] = array(
                    'id' => $content['id'],
                    'cell' => array(
                    $content['manufacturer'],
                    $content['model'],
                    $this->view->listAction(array('cellphone', 'edit', 'id', $content['id']), 'edit')
                    )
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = $cellphone->count($values['filterColumn'], $values['filter']);
            $this->view->rows = $rows;
        }
    }

    public function addAction() {
        $form = new CellPhoneForm();
        $form->setAction($this->view->baseUrl() . '/cms/cellphone/add');

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $upload = $form->getElement('image');
                $image = null;
                if ($upload->hasFiles()) {
                    $image = $upload->receive();
                }

                $cellphone = new Cellphone(array(
                    'manufacturer'   => $form->getValue('manufacturer'),
                    'model'          => $form->getValue('model'),
                    'file'           => $image
                ));

                $cellphone->save();

                $this->view->response = 'formSuccess';
                $this->view->message = 'Modelo '.$form->getValue('model').' cadastrado com sucesso!';
                $this->view->redirect = 'cellphone';
                $this->_formatFileFormResponseView();
                return;

            } else {

                $this->view->response = 'formError';
                $this->view->message = 'Alguns campos foram preenchidos de maneira incorreta. Verifique os campos destacados em vermelho.';

            }
        }

        $this->view->title = 'Adicionar novo modelo';
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());
    }


    public function editAction() {
        $id = (int)$this->_request->getParam('id');

        $cellphone = new Cellphone();

        $search = $cellphone->getPhoneByID($id);

        $form = new CellPhoneForm();
        $form->setAction($this->view->baseUrl() . '/cms/cellphone/edit/id/'.$id);
        $values = array('manufacturer' => $search['manufacturer'], 'model' => $search['model']);
        $form->populate($values);

        if ($this->_request->isPost()) {

            if ($form->isValid($this->_request->getPost())) {
                $upload = $form->getElement('image');
                $image = null;

                if ($upload->hasFiles()) {
                    $image = $form->getElement('image')->receive();
                }

                $formValues = array(
                    'id'             => $id,
                    'manufacturer'   => $form->getValue('manufacturer'),
                    'model'          => $form->getValue('model'),
                    'file'           => $image
                );

                $cellphone = new Cellphone($formValues);

                $cellphone->save();

                $this->view->response = 'formSuccess';
                $this->view->message = 'Modelo '.$form->getValue('model').' atualizado com sucesso!';
                $this->view->redirect = 'cellphone';
                $this->_formatFileFormResponseView();
                return;
            }else {
                $this->view->response = 'formError';
                $this->view->message = 'Erro!';
            }
        }
        $this->view->title = 'Editar Modelo: '.$values['model'];
        $this->view->type = 'form';
        $this->view->config = array('form' => $form->render());


    }

    public function modifyAction() {
        if ($this->_request->isPost()) {
            $cellphone = new Cellphone(array('id' => (int)$this->_request->getPost('id')));
            switch ($this->_request->getPost('command')) {
                case 'delete':  $cellphone->delete(); break;
            }
        }
    }

    }