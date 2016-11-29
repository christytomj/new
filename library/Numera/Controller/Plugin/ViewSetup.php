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
 * Realiza configurações gerais da view.
 */
class Numera_Controller_Plugin_ViewSetup extends Zend_Controller_Plugin_Abstract
{
    /**
     * dispatchLoopStartup() plugin hook -- Realiza configurações gerais da view
     * antes de disparar qualquer ação.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->init();
        $view = $viewRenderer->view;
        
        // Seta doctype para garantir marcação XHTML válida
        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');
        
        // Adiciona caminho do helper path
        $prefix = 'Numera_View_Helper';
        $dir = APPLICATION_PATH . '/../library/Numera/View/Helper';
        $view->addHelperPath($dir, $prefix);

        // Seta cabeçalho inicial da página
        $view->siteTitle = Zend_Registry::get('app')->title;
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        $view->headTitle($view->siteTitle);
        
        // Seta variáveis comum a todas as views
        // Deve-se utilizar o formato $view->variavel = $valor;
        // Como exemplo, é disponibilizado os nomes do módulo/controller/action
        $view->module = $request->getModuleName();
        $view->controller = $request->getControllerName();
        $view->action = $request->getActionName();
    }
}