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

/**
 * Numera_Form
 *
 * 
 */
class Numera_Form
{
    /**
     * Propriedades dos elementos do formulário
     * @var array
     */
    protected $_elements = array();

    /**
     * Construtor
     *
     * 
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }

        $this->init();
    }

    /**
     * Converte as propriedades do formulário para array
     *
     * @return array
     */
    public function toArray()
    {
        $elements = $this->_elements;
        foreach ($elements as $key => $element) {
            unset($elements[$key]['filters']);
            unset($elements[$key]['validators']);
        }
        return $elements;
    }

    /**
     * Set form state from options array
     *
     * @param  array $options
     * @return Zend_Form
     */
    public function setOptions(array $options)
    {
    }

    /**
     * Inicializa o formulário (usado por classes extensoras)
     * 
     * @return void
     */
    public function init()
    {
    }

    /**
     * Adiciona um novo elemento ao formulário
     *
     * $type é uma string referente a um tipo de Zend_Form_Element.
     * $options é um array com as opções desse elemento, podendo os parâmetros
     * serem quaisquer aceitos por ele.
     *
     * @param  string $type
     * @param  string $name
     * @param  array $options
     * @return Numera_Form
     */
    public function addElement($type, $name, $options = null)
    {
        $element = $options;
        $element['name'] = $name;
        $element['type'] = $type;
        if (!isset($element['required'])) {
            $element['required'] = false;
        }
        ksort($element);
        $this->_elements[] = $element;

        return $this;
    }
}