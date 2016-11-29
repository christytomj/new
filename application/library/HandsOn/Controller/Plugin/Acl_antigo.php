<?php

class HandsOn_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('atdws'));
    	if (null == Zend_Auth::getInstance()->getIdentity()) {
	        if ($request->module == 'admin' &&
	            ($request->controller != 'index' && $request->controller != 'auth')) {
	            $this->_accessDenied($request);
	        }
    		
    	}
    }
    
    private function _accessDenied(Zend_Controller_Request_Abstract $request)
    {
        $request->setControllerName('error')->setActionName('error');
    }
}