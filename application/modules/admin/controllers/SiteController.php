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

class Admin_SiteController extends HandsOn_Controller_Action
{
    public function init()
    {
        $this->_helper->acl->allow('administrativo');
    }

    public function indexAction()
    {
        $columns = array(
           'title' => array('Título', 200),
           'modification_date' => array('Data da Modificação', 181),
           'action' => array('Ações', 80, false)
        );

        $searchItems = null;

        $buttons = null;

        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig($columns, $searchItems, $buttons, 'site/list');
        $this->view->title = 'Lista de Conteúdos';
    }

    public function listAction()
    {

        if ($this->getRequest()->isPost())
        {
            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array('title'=>'title', 'modification_date' => 'modification_date');
            $postValues['filterColumns'] = array('title' => 'title');
            $values = $this->view->gridValues($postValues);
            
            $pages = new Pages();
            $pageList = $pages->getSiteContentValues(array('title', 'modification_date'), $values);

            $rows = array();
            foreach ($pageList as $content) {
               
                $rows[] = array(
                    'id' => $content['id'],
                    'cell' => array(
                        $content['title'],
                        $pages->convertDate($content['modification_date']),
                        $this->view->listAction(array('site', 'edit', 'id', $content['id']), 'edit')
                    )
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = $pages->countSite($values['filterColumn'], $values['filter']);
            $this->view->rows = $rows;
        }
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $pages = new Pages();

        $search = $pages->getSiteContentByID($id);

        if(!empty ($search)){

            $form = new EditContentForm();
            $form->setAction($this->view->baseUrl() . '/cms/site/edit/id/'.$id);
            $values = array('title' => $search['title'], 'text' => $search['content']);
            $form->populate($values);

            if ($this->getRequest()->isPost()) {

                if ($form->isValid($this->getRequest()->getPost())) {

                    $date = new Zend_Date();
                    $date = $date->toString("yyyy-MM-dd");           
               
                    $siteContentValues = array(
                        'id'     => $id,
                        'title'  => $form->getValue('title'),
                        'date'   => $date,
                        'text'   => $form->getValue('text'),
                    );

                    $pages->updateSiteContent($siteContentValues);
                    $this->view->response = 'formSuccess';
                    $this->view->message = 'O conteúdo de '.$siteContentValues['title'].' foi atualizado com sucesso!';
                    $this->view->redirect = 'site';
                    return;
                }else{
                    $this->view->response = 'formError';
                    $this->view->message = 'Alguns campos foram preenchidos de maneira incorreta. Verifique os campos destacados em vermelho.';
                }
            }
            $this->view->title = 'Editar Conteúdo: '.$values['title'];
            $this->view->type = 'form';
            $this->view->config = array('form' => $form->render());
        }

    }
}