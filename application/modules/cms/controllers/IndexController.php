<?php
/**
 * HandsOn CMS Framework
 *
 * LICENÇA
 *
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

class Cms_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        //$this->_helper->acl->allow(null);
        $this->_helper->acl->allow('administrativo');
    }

    public function indexAction()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_forward('index', 'index', 'default');
        } else {
            $auth = Zend_Auth::getInstance();

            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $this->view->userMail = (null != $userIdentity) ? $userIdentity->email : null;

            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/reset.css' , 'all');
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/form.css' , 'all');
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/jquery.modal.css' , 'all');
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/jquery.flexigrid.css' , 'all');
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/ui.datepicker.css' , 'all');

                        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/jquery.rte.css' , 'screen');
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/admin.css' , 'screen');



            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.json.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.form.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.modal.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.flexigrid.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.maxlength.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/numera.ajaxarea.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/numera.grid.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/numera.form.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/numera.validate.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/numera.report.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/numera.dynamicgroup.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/singular.dialog.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.ui.datepicker-ptbr.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.ui.datepicker.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/lembrefacil.admin.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/jquery.rte.js');
            $this->view->headScript()->appendFile($this->view->baseUrl().'/js/singular.rte.tb.js');
            
            $this->view->ie6fix = '<!--[if IE 6]>
    <link rel="stylesheet" href="'.$this->view->baseUrl().'/css/ie6fix.admin.css" type="text/css"/>
    <![endif]-->';
            $this->view->ie7fix = '<!--[if IE 7]>
    <link rel="stylesheet" href="'.$this->view->baseUrl().'/css/ie7fix.admin.css" type="text/css"/>
    <![endif]-->';
        }
    }

}