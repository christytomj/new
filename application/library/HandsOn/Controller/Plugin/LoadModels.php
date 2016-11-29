<?php

class HandsOn_Controller_Plugin_LoadModels extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$module = $request->getModuleName();
    	if (empty($module)) {
    		$module = 'default';
    	}
        set_include_path(get_include_path()
            . PATH_SEPARATOR . APPLICATION_PATH . '/modules/' . $module . '/models/'
            . PATH_SEPARATOR . APPLICATION_PATH . '/modules/' . $module . '/forms/'
        );
    }
}