<?php
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Front Controller plug in to set up the view with the Places view helper
 * path and some useful request variables.
 *
 */
class HandsOn_Controller_Plugin_ViewSetup extends Zend_Controller_Plugin_Abstract
{    
    /**
     * @var Zend_View
     */
    protected $_view;
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->init();
        
        $view = $viewRenderer->view;
        $this->_view = $view;
        
        // set up common variables for the view
        //$view->originalModule = $request->getModuleName();
        //$view->originalController = $request->getControllerName();
        //$view->originalAction = $request->getActionName();

        //$userIdentity = Zend_Auth::getInstance()->getIdentity();
        //$view->userMail = (null != $userIdentity) ? $userIdentity->email : null;
//        $this->_controller = $request->getControllerName();
//        $this->_action = $request->getActionName();

        // set up doctype for any view helpers that use it
        $view->doctype('XHTML1_STRICT');
        
        // add helper path to View/Helper directory within this library
        $prefix = 'HandsOn_View_Helper';
        $dir = dirname(__FILE__) . '/../../View/Helper';
        $view->addHelperPath($dir, $prefix);
        
        // setup initial head place holders
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');

    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (!$request->isDispatched()) {
            return;
        }
        $view = $this->_view;
        
        if (count($view->headTitle()->getValue()) == 0) {
            $view->headTitle($view->title);
        }
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle(Zend_Registry::get('config')->title);
    }
}