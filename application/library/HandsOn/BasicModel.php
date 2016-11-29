<?php

class HandsOn_BasicModel
{
    /** @var Zend_Db_Table_Abstract */
    protected static $_db = null;
    protected $_data = array();
    protected static $_dataSource = array();

    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->populate($data);
        }
    }

    static function staticInit() {
        if (self::$_db === null) {
            self::$_db = Zend_Db_Table::getDefaultAdapter();
        }
    }

    public function populate($data)
    {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array or object');
        }

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $this->_data)) {
                continue;
            }
            $this->$key = $value;
        }
        return $this;
    }

    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid property "' . $name . '"');
        }
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return null;
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        if (isset($this->$name)) {
            $this->_data[$name] = null;
        }
    }    
    
    public function clear()
    {
        foreach ($this->_data as $key => $value) {
            $this->_data[$key] = null;
        }
    }
    
    public function toArray()
    {
        return $this->_data;
    }
}

HandsOn_BasicModel::staticInit();
