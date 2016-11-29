<?php
/**
 * Númera Framework
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
 * @copyright  Copyright (c) 2009 Númera Soluções e Sistemas Ltda. (http://www.numera.com.br)
 * @license    http://www.numera.com.br/license/framework     Númera Framework 1.0 License
 * @version    $Id$
 */

/**
 * Adiciona o diretório "models" do controller selecionado no path.
 */
class Numera_Controller_Plugin_LoadModels extends Zend_Controller_Plugin_Abstract
{
    /**
     * preDispatch() plugin hook -- Adiciona o diretório "models" do controller
     * selecionado no path.
     *
     * Se nenhum módulo estiver especificado, utiliza-se a constante DEFAULT_MODULE
     * da classe Bootstrap.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$module = $request->getModuleName();
    	if (empty($module)) {
    		$module = Bootstrap::DEFAULT_MODULE;
    	}
        set_include_path(
            get_include_path() .
            PATH_SEPARATOR . APPLICATION_PATH . '/modules/' . $module . '/models/'
        );
    }
}