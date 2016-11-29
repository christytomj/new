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

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        //$this->_helper->acl->allow(null);
        //$this->_helper->acl->allow(false);
        $this->_helper->acl->allow('administrativo');
        $this->_helper->acl->allow('funcionario');
        $this->_helper->acl->allow('financeiro1');
        $this->_helper->acl->allow('financeiro2');

        $this->_helper->acl->allow(Users::PROFILE_DIST);
        $this->_helper->acl->allow(Users::PROFILE_REDE);
        $this->_helper->acl->allow(Users::PROFILE_LAB);
        $this->_helper->acl->allow(Users::PROFILE_SELLER);
    }

    public function indexAction() {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_forward('index', 'index', 'default');
        } else {
            $auth = Zend_Auth::getInstance();

            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $this->view->userMail = (null != $userIdentity)
                    ? $userIdentity->email : null;

            $this->addCSS('reset.css' , 'all');
            $this->addCSS('form.css' , 'all');
            $this->addCSS('jquery.modal.css' , 'all');
            $this->addCSS('jquery.flexigrid.css' , 'all');
            $this->addCSS('ui.datepicker.css' , 'all');
            $this->addCSS('jquery.rte.css' , 'screen');
            $this->addCSS('admin.css' , 'screen');
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
            $this->addJS('numera.dynamicgroup.js');
            $this->addJS('numera.dynamicgroup.js');
            $this->addJS('singular.dialog.js');
            $this->addJS('jquery.ui.datepicker-ptbr.js');
            $this->addJS('jquery.ui.datepicker.js');
            $this->addJS('lembrefacil.admin.js');
            $this->addJS('jquery.rte.js');
            $this->addJS('singular.rte.tb.js');
            $this->addJS('lembrefacil.remedyboxes.js');
            $this->addJS('jquery.autocomplete.js');

            $this->view->ie6fix = '<!--[if IE 6]>
                <link rel="stylesheet" href="'
                .$this->view->baseUrl()
                .'/css/ie6fix.admin.css" type="text/css"/>
                <![endif]-->';
            $this->view->ie7fix = '<!--[if IE 7]>
                <link rel="stylesheet" href="'
                .$this->view->baseUrl()
                .'/css/ie7fix.admin.css" type="text/css"/>
                <![endif]-->';
        }
    }

    private function addJS($nm) {
        $this->view->headScript()->appendFile(
            $this->view->baseUrl().'/js/'.$nm);
    }
    private function addCSS($nm, $area) {
        $this->view->headLink()->appendStylesheet(
                $this->view->baseUrl() . '/css/' . $nm,
                $area
                );
    }

    public function menuAction(){
        if (Zend_Auth::getInstance()->hasIdentity())
        {
            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $id_profile = (null != $userIdentity)
                    ? $userIdentity->id_profile : null;

            if(!empty($id_profile) || $id_profile == 0){
                $role = Users::getProfileLabel($id_profile);
            }

            if(!empty($role)){
                switch($role){
                    case Users::PROFILE_ADMIN:
                        $menu = array(
                                'Usuários' => 'users',
                                'Assinantes' => 'subscribers',
                                'Relatórios de Envio' => 'reports',
                                'Contas SMS' => 'credits',
                                'Remédios' => 'remedy',
                                'Conteúdo do Site' => 'site',
                                'Celulares' => 'cellphone',
                                );
                        break;
                    case Users::PROFILE_FUNC:
                        $menu = array(
                                'Assinantes' => 'subscribers',
                                'Remédios' => 'remedy',
                                );
                        break;
                    case Users::PROFILE_FIN1:
                        $menu = array(
                                'Assinantes' => 'subscribers',
                                'Contas SMS' => 'credits',
                                );
                        break;
                    case Users::PROFILE_FIN2:
                        $menu = array(
                                'Assinantes' => 'subscribers',
                                'Relatórios de Envio' => 'reports',
                                'Contas SMS' => 'credits',
                                );
                        break;
                    case Users::PROFILE_LAB:
                        $menu = array(
                                'Usuários' => 'users',
                                'Assinantes' => 'subscribers',
                                'Remédios' => 'remedy',
                                'Balanço' => 'users/balance',
                                );
                        break;
                    case Users::PROFILE_DIST:
                        $menu = array(
                                'Usuários' => 'users',
                                'Assinantes' => 'subscribers'
                                );
                        break;
                    case Users::PROFILE_REDE:
                        $menu = array(
                                'Assinantes' => 'subscribers'
                                );
                        break;
                    case Users::PROFILE_SELLER:
                        $menu = array(
                                'Usuários' => 'users',
                                'Assinantes' => 'subscribers'
                                );
                        break;
                }
            }

            $this->view->menu = $menu;
        }
    }
}