<?php

class Lembrefacil_Acl extends Zend_Acl
{
    public function __construct(){
        $config = Zend_Registry::get('config');
        $roles = $config->acl->roles;
        $this->_addRoles($roles);
    }

    protected function _addRoles($roles){
        foreach($roles as $name=>$parents){
            if(!$this->hasRole($name)){
                if (empty($parents)){
                    $parents = null;
                } else {
                    $parents = explode(',', $parents);
                }
             $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }
}
