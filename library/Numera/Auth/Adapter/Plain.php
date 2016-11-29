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

class Numera_Auth_Adapter_Plain implements Zend_Auth_Adapter_Interface
{
    protected $_email;
    protected $_password;

    public function __construct($email, $password)
    {
        $this->_email = $email;
        $this->_password = $password;
    }

    protected function _validateUser()
    {
        $users = new Zend_Config_Ini(DATA_PATH . '/configuration/users.ini', 'users', array('nestSeparator' => '->'));
        $password = $users->get('juliano@numera.com.br');
        if (null != $password && $this->_password == $password) {
            return true;
        }
        return false;
    }

    public function authenticate()
    {
        $code = Zend_Auth_Result::FAILURE;
        $identity = null;
        $messages = array();

        if ($this->_validateUser()) {
            $code = Zend_Auth_Result::SUCCESS;
            $identiy = new stdClass();
            $identity->email = $this->_email;
        } else {
            $messages[] = 'Authentication error';
        }

        return new Zend_Auth_Result($code, $identity, $messages);
    }
}