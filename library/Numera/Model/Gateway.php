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
 * Acesso base
 *
 * Define métodos para configurar opções, recuperar carregadores de recursos,
 * criar e manipular hooks de plugins, e registrar e manipular plugins.
 */
abstract class Numera_Model_Gateway
{
    /**
     * Métodos da classe
     * @var array
     */
    protected $_classMethods;

    /**
     * Registro de objetos do tipo tabela
     * @var array
     */
    protected $_dbTables = array();

    /**
     * Validador padrão; usuado para construir o formulário accessor
     * @var string
     */
    protected $_defaultValidator;

    /**
     * Tabela primária para operações
     * @var string
     */
    protected $_primaryTable = 'user';

    /**
     * Colunas protegidas nas operações de salvamento
     * @var array
     */
    protected $_protectedColumns = array();

    /**
     * Se deve ou não utilizar paginador para os conjuntos de resultados
     * @var bool
     */
    protected $_usePaginator = false;

    /**
     * Construtor
     * 
     * @param  array|Zend_Config|null $options 
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options using setter methods
     * 
     * @param  array $options 
     * @return Spindle_Model_Paste
     */
    public function setOptions(array $options)
    {
        if (null === $this->_classMethods) {
            $this->_classMethods = get_class_methods($this);
        }
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $this->_classMethods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set flag indicating whether or not to use paginator
     * 
     * @param  bool $flag 
     * @return Spindle_Model_Model
     */
    public function setUsePaginator($flag)
    {
        $this->_usePaginator = (bool) $flag;
        return $this;
    }

    /**
     * Use a paginator?
     * 
     * @return bool
     */
    public function usePaginator()
    {
        return $this->_usePaginator;
    }

    /**
     * Lazy loaded DB Table registry
     * 
     * @param  string $name 
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable($name)
    {
        if (!isset($this->_dbTables[$name])) {
            $class = 'Spindle_Model_DbTable_' . ucfirst($name);
            $this->_dbTables[$name] = new $class;
        }
        return $this->_dbTables[$name];
    }
}
