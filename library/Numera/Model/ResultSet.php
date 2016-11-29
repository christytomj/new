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
 * Base para conjunto de resultados (record set) dos modelos
 */
abstract class Numera_Model_ResultSet implements Iterator,Countable
{
    /**
     * Guarda o número de items do conjunto
     * @var int
     */
    protected $_count;

    /**
     * Classe do item individual do conjunto
     * @var string
     */
    protected $_resultClass;

    /**
     * Conjunto de resultados
     * @var array
     */
    protected $_resultSet;

    /**
     * Construtor
     * 
     * @param  array|Traversable $results 
     * @return void
     */
    public function __construct($results)
    {
        if (!$this->_resultClass) {
            throw new Numera_Model_Exception('Classe dos resultados não definida!');
        }

        if (!is_array($results) && (!$results instanceof Traversable)) {
            throw new Numera_Model_Exception('Conjunto de resultados inválido; deve ser array or Traversable');
        }

        $this->_resultSet = $results;
    }

    /**
     * Countable: retorna o número de itens no conjunto
     * 
     * @return int
     */
    public function count()
    {
        if (null === $this->_count) {
            $this->_count = count($this->_resultSet);
        }
        return $this->_count;
    }

    /**
     * Iterator: retorna o item atual
     * 
     * @return Numera_Model_Value
     */
    public function current()
    {
        if (is_array($this->_resultSet)) {
            $result = current($this->_resultSet);
        } else {
            $result = $this->_resultSet->current();
        }

        if (is_array($result)) {
            $key = key($this->_resultSet);
            $this->_resultSet[$key] = new $this->_resultClass($result);
            $result = $this->_resultSet[$key];
        }
        return $result;
    }

    /**
     * Iterator: return a chave atual
     * 
     * @return int|string
     */
    public function key()
    {
        if (is_array($this->_resultSet)) {
            return key($this->_resultSet);
        }
        return $this->_resultSet->key();
    }

    /**
     * Iterator: avança para o próximo item do conjunto
     * 
     * @return void
     */
    public function next()
    {
        if (is_array($this->_resultSet)) {
            return next($this->_resultSet);
        }
        return $this->_resultSet->next();
    }

    /**
     * Iterator: retorna para o item anterior do conjunto
     * 
     * @return void
     */
    public function rewind()
    {
        if (is_array($this->_resultSet)) {
            return reset($this->_resultSet);
        }
        return $this->_resultSet->rewind();
    }

    /**
     * Iterator: o item atual é válido
     * 
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }
}
