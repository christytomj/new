<?php
/**
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
 * Base de value object para modelos
 */
abstract class Numera_Model_Value
{
    /**
     * Propriedades permitidas
     * @var array
     */
    protected $_allowed = array();

    /**
     * Propriedades definidas
     * @var array
     */
    protected $_data    = array();

    /**
     * Construtor
     *
     * Configura metadados do valor e, se passado, as opções.
     * 
     * @param  array|object $data 
     * @param  array|object $options 
     * @return void
     */
    public function __construct($data, $options = null)
    {
        if (empty($this->_allowed)) {
            throw new Numera_Model_Exception('Nenhum campo permitido especificado!');
        }

        $this->setOptions($options)
             ->populate($data);
    }

    /**
     * Configura opções
     * 
     * @param  array $options 
     * @return Numera_Model_Value
     */
    public function setOptions($options)
    {
        if (null === $options) {
            return $this;
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_object($options)) {
            $options = (array) $options;
        }

        if (!is_array($options)) {
            throw new Numera_Model_Exception('Opções inválidas; deve ser um array ou objeto');
        }

        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Sobrecarga: Método mágico para definir propriedade do objeto
     *
     * Se não estiver no array {$_allowed}, a opção é rejeitada.
     * 
     * @param  string $key 
     * @param  mixed $value 
     * @return void
     * @throws Numera_Model_Exception
     */
    public function __set($key, $value)
    {
        if (!in_array($key, $this->_allowed)) {
            throw new Numera_Model_Exception('Propriedade não permitida ("' . $key . '")');
        }
        $this->_data[$key] = $value;
    }

    /**
     * Sobrecarga: Método mágico para pegar propriedade do objeto
     * 
     * @param  string $key 
     * @return mixed
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_data)) {
            return null;
        }
        return $this->_data[$key];
    }

    /**
     * Sobrecarga: Método mágico para verificar se a propriedade está definida
     * 
     * @param  string $key 
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Sobrecarga: Método mágico para remover a propriedade
     * 
     * @param  string $key 
     * @return void
     */
    public function __unset($key)
    {
        if ($this->__isset($key)) {
            unset($this->_data[$key]);
        }
    }

    /**
     * Popula objeto com os dados providos
     * 
     * @param  array|object $data 
     * @return Numera_Model_Value
     * @throws Numera_Model_Exception para $data do tipo inválido
     */
    public function populate($data)
    {
        if (null === $data) {
            return;
        }

        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            throw new Numera_Model_Exception('Dados com tipo inválido');
        }

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Converte valor para array
     * 
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->_data as $key => $value) {
            if (null !== $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
