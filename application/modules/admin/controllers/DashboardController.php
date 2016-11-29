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

class Admin_DashboardController extends HandsOn_Controller_Action
{
    public function init()
    {
          $this->_helper->acl->allow(null);
    }
    
    public function indexAction()
    {
    	$this->view->title = 'Acesso à Área Administrativa';
        $this->view->type = 'data';
        $this->view->data = '<p class="tip">Escolha ao lado qual opção você deseja atualizar</p>';
    }
    public function helpAction() {
    	$this->view->title = 'Ajuda';
        $this->view->type = 'data';
        $this->view->data = '<a href="http://www.lembrefacil.com.br/panel/manual_ass.pdf">Manual do Assinante</a><hr/>
            <iframe title="YouTube video player"'
                .' class="youtube-player" type="text/html" width="610"'
                .' height="375" '
                .' src="http://www.youtube.com/embed/m3mJzuq_vg0?rel=0"'
                .' frameborder="0" allowFullScreen></iframe>';
    }
}
