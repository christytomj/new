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



class HandsOn_Validate_Confirm extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'confirmationNotMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'A confirmação não combina'
    );

    protected $_fieldsToMatch = array();

    public function __construct($fieldsToMatch = array())
    {
        if (is_array($fieldsToMatch))
        {
            foreach ($fieldsToMatch as $field)
            {
                $this->_fieldsToMatch[] = (string) $field;
            }
        } else
        {
            $this->_fieldsToMatch[] = (string) $fieldsToMatch;
        }
    }

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        foreach ($this->_fieldsToMatch as $fieldName)
        {
            if (!isset($context[$fieldName]) || $value !== $context[$fieldName])
            {
                $this->_error(self::NOT_MATCH);
                return false;
            }
        }

        return true;
    }
}
