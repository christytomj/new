<?php
/*
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

class Numera_Auth_Adapter_Url implements Zend_Auth_Adapter_Interface
{
    protected $_identity;

    public function __construct($email, $password)
    {
        $this->_identity = new stdClass();
        $this->_identity->email = $email;
        $this->_identity->password = $password;
    }

    protected function _validateUser()
    {
        $data = array('email' => $this->_identity->email, 'password' => $this->_identity->password);
        $response = $this->postRequest(Zend_Registry::get('config')->auth->url, $data);
        $values = Zend_Json::decode($response);
        if (!empty($values)) {
            $this->_identity->id = $values['id'];
            $this->_identity->name = $values['name'];
            return true;
        }
        return false;
    }

    protected function postRequest($url, $data, $optional_headers = null)
    {
        $data = http_build_query($data);
        $params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $url, $php_errormsg");
        }
        return $response;
    }


    public function authenticate()
    {
        $code = Zend_Auth_Result::FAILURE;
        $identity = null;
        $messages = array();

        if ($this->_validateUser()) {
            $code = Zend_Auth_Result::SUCCESS;
            $identity = $this->_identity;
            unset($identity['password']);
        } else {
            $messages[] = 'Authentication error';
        }

        return new Zend_Auth_Result($code, $identity, $messages);
    }
}