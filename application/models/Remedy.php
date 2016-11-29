<?php

class Remedy extends HandsOn_BasicModel {

    protected $_data = array(
        'id'        => null,
        'name'      => null,
        'descr'     => null,
        'qty'       => null,
        'val'       => null,
        'approval'  => null,
        'id_owner'  => null,
    );

    protected static $_allowedData = array(
        'id',
        'name',
        'descr',
        'qty',
        'val',
        'approval',
        'id_owner',
    );

    protected static $_dataSource = array(
        'id'        => 'id',
        'name'      => 'name',
        'descr'     => 'descr',
        'qty'       => 'qty',
        'val'       => 'val',
        'approval'  => 'approval',
        'id_owner'  => 'id_owner',
    );
    
    public function getCredit() {
        throw new Exception('OBJETO Remedy não tem método GET CREDIT!!!');
    }

    /**
     * Testa se um remédio deste tipo alocado em $dateAlloc ainda está na validade
     * @param Zend_Date $dateAlloc a data em que o remedio foi alocado
     * @return true se o remedio ainda esta na validade em relação a $dateAlloc.
     */
    public function isValidSince(Zend_Date $dateAlloc) {
        $dateAlloc->addMonth($this->val);

        return $dateAlloc->isLater(Zend_Date::now());
    }

    public function approve() {
        $dados = array('approval'=>1);
        self::$_db->update('remedy', $dados, 'id='.$this->id);
    }

    public function __set($name, $value) {
        if (!array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid property "' . $name . '"');
        }
        $this->_data[$name] = $value;
    }

    public static function get($data, $options = null, $filterOwner=null) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }
        $select = '';
        if (count($data) == 1 and $data[0] == '*') {
            $data = self::$_allowedData;
        }
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select)
                ? self::$_dataSource[$name]
                : ',' . self::$_dataSource[$name];
        }
        $from = 'remedy';

        $where = array();
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where[] .= sprintf(
                "%s LIKE %s",
                $options['filterColumn'],
                self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }
        if (! empty($options['filterOwner'])) {
            $where[] = sprintf(
                'id_owner = \'%d\'',
                $options['filterOwner']
                );
        }
        if (! empty($options['approved']) AND $options['approved'] === true) {
            $where[] = 'approval=1';
        }
        if (! empty($options['listOfIds'])) {
            $where[] = sprintf(
                    'id in (%s)',
                    join(', ', $options['listOfIds']));
        }
        if ($filterOwner) {
            $where[] = sprintf(
                'id_owner = \'%s\'',
                $filterOwner
            );
        }
        $where = count($where) ? 'WHERE '.join(' AND ', $where) : '';

        $order = '';
        if (!empty($options['sortColumn'])) {
            $column = self::$_dataSource[$options['sortColumn']];
            $order = 'ORDER BY ' . $column . $options['sortOrder'];
        }

        $limit = '';
        if (!empty($options['rowCount'])) {
            $limit = 'LIMIT ' . (int)$options['rowCount'];
            if ($options['page'] > 1) {
                $limit .= ' OFFSET '
                    . (int)($options['rowCount'] * ($options['page'] - 1));
            }
        }

        $sql = "SELECT $select FROM $from $where $order $limit";
        return self::$_db->fetchAll($sql);
    }

    public static function count(
            $idProfile, $options) {
        $from = 'remedy';
        $where = array();
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where[] .= sprintf(
                "%s LIKE %s",
                $options['filterColumn'],
                self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }
        if (! empty($options['filterOwner'])) {
            $where[] = sprintf(
                'id_owner = \'%d\'',
                $options['filterOwner']
                );
        }
        if (! empty($options['approved']) AND $options['approved'] === true) {
            $where[] = 'approval=1';
        }
        if (count($where)) {
            $where = 'WHERE ' . join(' AND ', $where);
        } else {
            $where = '';
        }

        $sql = "SELECT count(*) FROM $from $where";
        return self::$_db->fetchOne($sql);
    }

    public static function getById($id) {
        $rem = new Remedy();
        $rem->id = $id;
        $rem->read(array('*'));
        return $rem;
    }

    public function read($data) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array or object');
        }

        if (count($data)==1 AND $data[0]=='*') {
            $data = self::$_allowedData;
        }
        $select = array();
        foreach ($data as $name) {
            if (!array_key_exists($name, $this->_data)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select[] = self::$_dataSource[$name];
        }
        $select = join(',',$select);

        if (!empty($select)) {
            $key = 'id';
            $value = $this->id;
            if (empty($this->id)) {
                $key = 'name';
                $value = self::$_db->quote($this->name);
            }
            $results = self::$_db->fetchRow(
                "SELECT $select FROM remedy WHERE $key = $value");
            if (!empty($results)) {
                foreach ($results as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function save() {
        //$date = new Zend_Date();
        //$this->dateModification = $date->get(Zend_Date::TIMESTAMP);
        $data = array(
            'name'      => $this->name,
            'descr'     => $this->descr,
            'qty'       => $this->qty,
            'val'       => $this->val,
            'approval'  => $this->approval ? 1 : 0,
            'id_owner'  => $this->id_owner,
        );
        if (isset($this->id)) {
            self::$_db->update('remedy', $data, 'id='.$this->id.'');
        } else {
            // novo
            if (empty($this->name) || empty($this->qty)
                    || empty($this->id_owner)) {
                throw new Exception(
                'Sem dados suficientes para criar novo remédio');
            }

            self::$_db->insert('remedy', $data);
        }
        return $this;
    }

    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Remédio não identificado para remoção');
        }
        self::$_db->delete('remedy', 'id='.$this->id);
    }
}
