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


/**
 * Helper para criação de botões nas listas
 */
class HandsOn_View_Helper_ListAction extends Zend_View_Helper_Abstract
{
    public function listAction(
            $url, $action, $text = null, $class = 'ajaxTrigger') {
    	$baseUrl =  Zend_Controller_Front::getInstance()->getBaseUrl();
    	$src = $baseUrl . '/images/icons/16x16/' . $action . '.png';
    	if (!$text) {
    		switch ($action) {
    			case 'edit':
    				$text = 'Editar';
    				break;
    			case 'subscribe':
    				$text = 'Inscrever';
    				break;
                case 'programming':
    				$text = 'programação';
    				break;
                case 'history':
    				$text = 'histórico';
    				break;
                case 'view':
    				$text = 'Visualizar';
    				break;
                case 'chain':
    				$text = 'Enviar Link';
    				break;
                case 'credit':
                    $text = 'Créditos';
    				break;
                case 'boxes':
                    $text = 'Caixas';
    				break;
    		}
    	}
    	$url = implode('/', $url);
  	    $url = ($class == 'ajaxTrigger') ? '#' . $url : $url;
        return sprintf(
                '<a href="%s" class="%s"><img src="%s" alt="%s" title="%s"/></a>',
                $url, $class, $src, $text, $text
        );
    }
} 