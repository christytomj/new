<?php
/**
 * HandsOn CMS Framework
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
 * @license    http://www.numera.com.br/license/handson     HandsOn 1.0 License
 * @version    $Id$
 */

return array(
    Zend_Validate_NotEmpty::IS_EMPTY        => 'Preenchimento obrigatório',
    Zend_Validate_StringLength::TOO_SHORT   => 'Tamanho mínimo de %min% caracteres',
    //Zend_Validate_StringLength::TOO_SHORT   => 'Valor inserido não possui o número mínimo de caracteres',
    Zend_Validate_StringLength::TOO_LONG    => 'Tamanho máximo de %max% caracteres',
    Zend_Validate_Date::NOT_YYYY_MM_DD      => 'Must use YYYY-MM-DD format',
    Zend_Validate_Date::INVALID             => 'Not valid date',
    Zend_Validate_Date::FALSEFORMAT         => 'Invalid date format',
    Zend_Validate_Alpha::NOT_ALPHA          => 'Valor inserido não possui somente caracteres alfabéticos',
    Zend_Validate_Alnum::NOT_ALNUM          => 'Valor inserido não possui somente caracteres alfanuméricos',
    Zend_Validate_Date::INVALID             => 'Data inserida não é uma data válida',
    Zend_Validate_Digits::NOT_DIGITS        => 'Valor inserido não possui somente caracteres numéricos',
    Zend_Validate_EmailAddress::INVALID     => 'E-mail inserido não é um endereço de e-mail válido',
    Zend_Validate_GreaterThan::NOT_GREATER  => 'Preenchimento obrigatório',
    Zend_Validate_File_Size::TOO_BIG        => 'Arquivo maior do que %max% KB',
    Zend_Validate_File_MimeType::FALSE_TYPE => 'Tipo do arquivo inválido',
);