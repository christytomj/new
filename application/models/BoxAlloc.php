<?php

class BoxAlloc extends HandsOn_BasicModel {
    protected $_data = array(
        'id'            => null,
        'id_remedy'     => null,
        'id_allocated'  => null,
        'qty'           => null,
        'used'          => 0,
        'id_origin'     => null,
        'dt_alloc'      => null,
    );

    protected static $_allowedData = array(
        'id',
        'id_remedy',
        'id_allocated',
        'qty',
        'used',
        'id_origin',
        'dt_alloc',
    );

    protected static $_dataSource = array(
        'id'            => 'id',
        'id_remedy'     => 'id_remedy',
        'id_allocated'  => 'id_allocated',
        'qty'           => 'qty',
        'used'          => 'used',
        'id_origin'     => 'id_origin',
        'dt_alloc'      => 'dt_alloc',
    );

    protected static $_fromTable = 'remedy_alloc';

    /**
     * Lista os BoxAlloc por dono da alocação do remedio
     * @param <int> $idAlloc o id do dono a listar
     * @return <array> lista de BoxAlloc-adas pro idAlloc
     */
    public static function listByAllocated($idAlloc) {
        $sql = sprintf(
                'SELECT * FROM %s WHERE id_allocated = %s',
                self::$_fromTable,
                self::$_db->quote($idAlloc));
        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }

    /**
     * Lista os BoxAlloc por dono da alocação do remedio
     * @param int $idAlloc o id do dono a listar
     * @param int $idRem o id do dono a listar
     * @return array lista de BoxAlloc-adas pro idAlloc
     */
    public static function listByAllocatedRem($idAlloc, $idRem) {
        $sql = sprintf(
                'SELECT * FROM %s WHERE id_allocated = %s AND id_remedy = %s',
                self::$_fromTable,
                self::$_db->quote($idAlloc), self::$_db->quote($idRem));
        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }

    /**
     * Lista os BoxAlloc por usuário de origem do crédito.
     * @param int $idAlloc o id do dono a listar
     * @param Zend_Date $dti data para inicio da pesquisa (inclusive)
     * @param Zend_Date $dtf data para fim da pesquisa (exclusive)
     * 
     * @return array lista de BoxAlloc-adas do idAlloc
     */
    public static function listByOrigin(
            $idAlloc, Zend_Date $dti=null, Zend_Date $dtf=null) {
        $select = self::$_db->select()->from(self::$_fromTable);
        $select->where('id_origin = ?', $idAlloc);

        if ($dti != null) {
            $select->where('dt_alloc >= ?', $dti->get(Util::DB_DATE_FORMAT));
        }
        if ($dtf != null) {
            $select->where('dt_alloc < ?', $dtf->get(Util::DB_DATE_FORMAT));
        }

        $rs = self::$_db->fetchAll($select);

        return self::resultSetToList($rs);
    }

    private static function resultSetToList($rs) {
        $list = array();
        foreach ($rs as $rec) {
            $list[] = new BoxAlloc($rec);
        }
        return $list;
    }

    /**
     * Lista as alocações que ainda têm crédito.
     */
    public static function listUsable() {
        $sql = sprintf(
                'SELECT * FROM %s WHERE qty <> used',
                self::$_fromTable);
        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }
    
    /**
     * Retorna os créditos restantes.
     */
    public function getCredit() {
        return ($this->qty - $this->used);
    }

    /**
     * @return Remedy o Remedy desta alocacao.
     */
    public function getRemedy() {
        return Remedy::getById($this->id_remedy);
    }
    
    /**
     * pega a data em que o remedio foi alocado.
     */
    public function getAllocTime() {
        return new Zend_Date($this->dt_alloc);
    }

    /**
     * Relê este objeto do banco de dados, tem que ter ID.
     * @return $this.
     */
    public function read($data) {
        if (empty($this->id)) {
            throw new Exception('No index found to read data.');
        }
        $select = array();
        if (sizeof($data) == 1 && $data[0] == '*') {
            $select = '*';
        } else {
            foreach ($data as $name) {
                if (!array_key_exists($name, $this->_data)) {
                    throw new Exception('Invalid property "' . $name . '"');
                }
                $select[] = self::$_dataSource[$name];
            }
            $select = join(', ', $select);
        }

        $key = 'id';
        $value = $this->id;
        $result = self::$_db->fetchRow(sprintf(
                "SELECT %s FROM %s WHERE %s = %s",
                $select, self::$_fromTable, $key, $value
                ));
        if ($result) {
            $this->populate($result);
        }
        return $this;
    }
    
    public static function getById($id) {
        $result = self::$_db->fetchRow(sprintf(
                "SELECT * FROM %s WHERE id = %d",
                self::$_fromTable, $id
                ));
        $ret = null;
        if ($result) {
            $ret = new BoxAlloc();
            $ret->populate($result);
        }
        return $ret;
    }

    /**
     * Salva este objeto no BD, update se tem id, ou insert.
     * @return $this.
     */
    public function save() {
        $data = $this->toArray();

        if (isset($this->id)) {
            self::$_db->update(self::$_fromTable, $data, 'id='.$this->id);
        } else {
            // novo
            if (! defined($data['dt_alloc'])) {
                $data['dt_alloc'] =  
                        Zend_Date::now()->get(Util::DB_DATETIME_FORMAT);
            }
            self::$_db->insert(self::$_fromTable, $data);
            $this->id = self::$_db->lastInsertId();
        }
        return $this;
    }

    /**
     * Apaga este obj do BD, e limpa os campos desta instância.
     * @return $this.
     */
    public function delete() {
        if (empty($this->id)) {
            throw new Exception(
                    'Alocação de remédio não identificada para remoção');
        }
        self::$_db->delete(self::$_fromTable, 'id='.$this->id);
        $this->clear();
        return $this;
    }

}
