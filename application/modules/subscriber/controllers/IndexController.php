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

class Subscriber_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->acl->allow('assinante');
    }

    public function indexAction() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_forward('index', 'index', 'default');
        } else {
            $auth = Zend_Auth::getInstance();

            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $this->view->userMail = (null != $userIdentity) ? $userIdentity->email : null;

            $this->addCSS('reset.css', 'all');
            $this->addCSS('form.css' , 'all');
            $this->addCSS('jquery.modal.css' , 'all');
            $this->addCSS('jquery.flexigrid.css' , 'all');
            $this->addCSS('ui.datepicker.css' , 'all');
            $this->addCSS('subscriber.css' , 'screen');
            $this->addCSS('jquery.autocomplete.css' , 'screen');

            $this->addJS('jquery.js');
            $this->addJS('jquery.json.js');
            $this->addJS('jquery.form.js');
            $this->addJS('jquery.modal.js');
            $this->addJS('jquery.flexigrid.js');
            $this->addJS('jquery.maxlength.js');
            $this->addJS('numera.ajaxarea.js');
            $this->addJS('numera.grid.js');
            $this->addJS('numera.form.js');
            $this->addJS('numera.validate.js');
            $this->addJS('numera.report.js');
            $this->addJS('singular.dialog.js');
            $this->addJS('numera.dynamicgroup.js');
            $this->addJS('jquery.ui.datepicker.js');
            $this->addJS('jquery.ui.datepicker-ptbr.js');
            $this->addJS('lembrefacil.programminggroup.js');
            $this->addJS('lembrefacil.programminglab.js');
            $this->addJS('lembrefacil.subscriber.js');
            $this->addJS('jquery.autocomplete.js');

            $this->view->ie6fix = 
                    "\n".'<!--[if IE 6]>'."\n"
                    . '<link rel="stylesheet" href="'
                        . $this->view->baseUrl()
                        . '/css/ie6fix.subscriber.css" type="text/css"/>'
                        . "\n"
                    . '<![endif]-->';

        $this->view->ie7fix = 
                "\n" . '<!--[if IE 7]>' . "\n"
                    . '<link rel="stylesheet" href="'
                    . $this->view->baseUrl()
                    . '/css/ie7fix.subscriber.css" type="text/css"/>'
                    . "\n"
                . '<![endif]-->';
        }
    }

    private function addCSS($base, $area) {
        $this->view->headLink()->appendStylesheet(
                $this->view->baseUrl() . '/css/'.$base , $area);
    }

    private function addJS($base) {
        $this->view->headScript()->appendFile(
            $this->view->baseUrl().'/js/'.$base);
    }

}

