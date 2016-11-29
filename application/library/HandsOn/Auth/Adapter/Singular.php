<?php

class HandsOn_Auth_Adapter_Singular implements Zend_Auth_Adapter_Interface
{
//    protected $_user;
//    protected $_password;
//
//    public function __construct($username, $password)
//    {
//        $this->_user = new HandsOn_User(array('email' => $username));
//        $this->_password = $password;
//    }
//
//    protected function _validateUser()
//    {
//        $data = array('email' => $this->_user->email, 'password' => $this->_password);
//        /*
//         * @todo mudar o response que esta apontando para o servidor
//         */
//        //$response = $this->postRequest(Zend_Registry::get('config')->auth->url, $data);
//        $response = $this->postRequest(Zend_Registry::get('config')->auth->url, $data);
//        $values = Zend_Json::decode($response);
//        if (!empty($values)) {
//            $this->_user->id = $values['id'];
//            $this->_user->name = $values['name'];
//            return true;
//        }
//        return false;
//    }
//
//    protected function postRequest($url, $data, $optional_headers = null)
//    {
//        $data = http_build_query($data);
//        $params = array('http' => array(
//                  'method' => 'POST',
//                  'content' => $data
//        ));
//        if ($optional_headers !== null) {
//            $params['http']['header'] = $optional_headers;
//        }
//        $ctx = stream_context_create($params);
//        $fp = @fopen($url, 'rb', false, $ctx);
//        if (!$fp) {
//            throw new Exception("Problem with $url, $php_errormsg");
//        }
//        $response = @stream_get_contents($fp);
//        if ($response === false) {
//            throw new Exception("Problem reading data from $url, $php_errormsg");
//        }
//        return $response;
//    }
//
//
//    public function authenticate()
//    {
//        $code = Zend_Auth_Result::FAILURE;
//        $identity = null;
//        $messages = array();
//
//        if ($this->_validateUser()) {
//            $code = Zend_Auth_Result::SUCCESS;
//            $identity = $this->_user;
//        } else {
//            $messages[] = 'Authentication error';
//        }
//
//        return new Zend_Auth_Result($code, $identity, $messages);
//    }
}