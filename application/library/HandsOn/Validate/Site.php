<?php
/**
 * Singular - Academic Resource Planning
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
 * @license    http://www.numera.com.br/license/singular     Singular 1.0 License
 * @version    $Id$
 */

class Singular_Validate_Site extends Zend_Validate_Abstract
{
    const NOT_VALID = 'notValid';

    protected $_messageTemplates = array(
        self::NOT_VALID => 'Site inserido não é um site válido'
    );

    /**
     * Verifica se uma string tem formato válido de URL.
     *
     * @param $url
     *   String com CEP que se deseja validar.
     *
     * @return
     *   TRUE se o formato for válido ou FALSE caso contrário.
     */
    protected function _verifySite($url) 
    {
        return preg_match('/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $url);
    }

    public function isValid($value)
    {
        $value = (string) $value;
        $this->_setValue($value);
        
        if (isset($value) && true == $this->_verifySite($value)) {
            return true;
        }
        $this->_error(self::NOT_VALID);
        return false;
    }
}