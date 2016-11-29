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

class Subscriber_DashboardController extends HandsOn_Controller_Action
{
    public function init()
    {
        $this->_helper->acl->allow('assinante');
    }
    
    public function indexAction()
    {
    	$this->view->title = 'Acesso à Área Administrativa';
        $this->view->type = 'data';
        $this->view->data = '<p class="tip">Escolha ao lado qual opção você deseja atualizar</p>';
    }

    public function helpAction(){
        $this->view->title = 'Ajuda';
        $this->view->type = 'data';
        $this->view->data = 
            '<style>#sidebar{display:none;}</style><div id="help_links"><h1>Clique no link para fazer o download do manual.</h1>'
            .'<div><a href="../manual_ass.pdf">Manual Lembre Fácil</a></div>'
            .'<div><a href="../manual_jme.pdf">Manual do Aplicativo</a></div>'
            .'<div>'
                .'<iframe title="YouTube video player"'
                .' class="youtube-player" type="text/html" width="610"'
                .' height="375" '
                .' src="http://www.youtube.com/embed/m3mJzuq_vg0?rel=0"'
                .' frameborder="0" allowFullScreen></iframe>'
                //.'<a href="../vid.wmv" class = "film">Video de Treinamento</a>'
            .'</div>'
            .'</div>';
    }
}
