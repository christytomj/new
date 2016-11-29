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
 * Helper para obtenção da raiz do endereço da página
 */
class HandsOn_View_Helper_BaseUrl extends Zend_View_Helper_Abstract
{
    protected $_baseUrl;
    
    public function __construct()
    {
        $this->_baseUrl =  Zend_Controller_Front::getInstance()->getBaseUrl();
    }
    
    public function baseUrl()
    {
        return $this->_baseUrl;
    }
}