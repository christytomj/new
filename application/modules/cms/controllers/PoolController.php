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


class Admin_PoolController extends HandsOn_Controller_Action
{
    public function indexAction()
    {
        $columns = array(
           'name'   => array('Nome', 100),
           'link'   => array('Link', 100, false),
           'date'   => array('Modificado', 40, false)
        );
        $searchItems = array('name' => 'Nome');
        $buttons = array(
            array('add', 'pool/add', 'Novo arquivo'),
            array('remove',
                  'admin/pool/modify',
                  'Remover',
                  'Tem certeza que deseja remover os arquivos selecionados?',
                  'Nenhum arquivo foi selecionado.'
            )
        );                    

        $this->view->title = 'Arquivos';
        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig($columns, $searchItems, $buttons, 'pool/list');
    }
    
    public function listAction()
    {
        if ($this->_request->isPost()) {
            $postValues = $this->_request->getPost();
            $postValues['sortColumns'] = array('name' => 'name');
            $postValues['filterColumns'] = array('name' => 'name');
            $values = $this->view->gridValues($postValues);
    
            $files = new Pool();
            $fileList = $files->get(array('name', 'dateModification', 'link'), $values);
    
            $rows = array();
            $date = new Zend_Date();
            foreach ($fileList as $file) {
                $date->set($file['dateModification'], Zend_Date::TIMESTAMP);
                $rows[] = array(
                    'id' => $file['name'],
                    'cell' => array($file['name'], $file['link'], $date->get("dd'/'MM'/'YYYY"))
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = $files->count();
            $this->view->rows = $rows;
        }
    }
    
    public function addAction()
    {
        $form = new PoolForm();
        $form->setAction($this->view->baseUrl() . '/admin/pool/add');
        
        if ($this->_request->isPost()) {
            $upload = $form->getElement('file');
            if ($upload->hasFiles()) {
                $file = $upload->receive();
            } else {
                $upload->addError('Preenchimento obrigatório');
            }
            if ($form->isValid($this->_request->getPost())) {
                $pool = new Pool();
                $pool->save($file);
                
                $this->view->response = 'formSuccess';
                $this->view->message = 'Arquivo enviado com sucesso! '.$t;
                $this->view->redirect = 'pool/index';
                $this->_formatFileFormResponseView();
                return;
            } else {
                $this->view->response = 'formError';
                $this->view->message = 'Erro no envio do arquivo.';
            }
        }
        
        $this->view->title = 'Adicionar novo arquivo';
        $this->view->type = 'form';
        $this->view->config = array('form' => (string)$form);
        $this->_formatFileFormResponseView();
    }
    
    public function modifyAction()
    {
        if ($this->_request->isPost()) {
            $pool = new Pool();
            switch ($this->_request->getPost('command')) {
                case 'delete':  $pool->delete($this->_request->getPost('id')); break;
            }
        }
    }
    
    public function filesAction()
    {
        $filter = $this->_request->getParam('filter');
        $options = (!empty($filter)) ? array('filterColumn' => 'type', 'filter' => $filter) : null;
        $files = new Pool();
        $this->view->files = $files->get(array('name', 'link'), $options);
    }
}