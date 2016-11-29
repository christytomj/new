<?php

class SmsBuy extends HandsOn_BasicModel {
    protected $_data = array(
        'id'            => null,
        'id_user'       => null,
        'id_account'    => null,
        'dt_credit'     => null,
        'qty'           => null,
    );

    protected static $_allowedData = array(
        'id',
        'id_user',
        'id_account',
        'dt_credit',
        'qty',
    );

    protected static $_dataSource = array(
        'id'		=> 'id',
        'id_user'	=> 'id_user',
        'id_account'	=> 'id_account',
        'qty'		=> 'qty',
        'dt_credit'     => 'dt_credit',
    );

    protected static $_fromTable = 'sms_buy';

    public function getUserBuyer() {
        return Users::getUserById($this->id_user);
    }

    public function getAccountBuyer() {
        return Accounts::getAccountById($this->id_account);
    }

    /**
     * Lista os SmsBuy por usuário. (quem comprou o crtédito)
     * @param int $idAcc o id de quem comprou credito
     * @param Zend_Date $dti inicia a pesquisa nesta data (inclusive)
     * @param Zend_Date $dtf termina a pesquisa antes deste dia (exclusive)
     */
    public static function listByBuyer($idAcc, $dti=null, $dtf=null) {
        $sql = sprintf(
                'SELECT * FROM %s WHERE id_user = %s',
                self::$_fromTable,
                self::$_db->quote($idAcc));

        if ($dti) {
            $sql .= sprintf(
                ' AND dt_credit >= \'%s\'',
                $dti->get(Util::DB_DATE_FORMAT)
            );
        }
        if ($dtf) {
            $sql .= sprintf(
                ' AND dt_credit < \'%s\'',
                $dtf->get(Util::DB_DATE_FORMAT)
            );
        }
        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }

    /**
     * Lista os SmsBuy por account. (quem comprou o crtédito)
     * @param int $idAcc o id de quem comprou credito
     * @param Zend_Date $dti inicia a pesquisa nesta data (inclusive)
     * @param Zend_Date $dtf termina a pesquisa antes deste dia (exclusive)
     */
    public static function listByAccount(
            $idAcc, Zend_Date $dti=null, Zend_Date $dtf=null) {
        $sql = sprintf(
                'SELECT * FROM %s WHERE id_account = %s',
                self::$_fromTable,
                self::$_db->quote($idAcc));
        if ($dti) {
            $sql .= sprintf(
                ' AND dt_credit >= \'%s\'',
                $dti->get(Util::DB_DATE_FORMAT)
            );
        }
        if ($dtf) {
            $newend = new Zend_Date($dtf->getTimestamp());
            $sql .= sprintf(
                ' AND dt_credit < \'%s\'',
                $newend->get(Util::DB_DATE_FORMAT)
            );
        }

        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }

    private static function resultSetToList($rs) {
        $list = array();
        foreach ($rs as $rec) {
            $list[] = new SmsBuy($rec);
        }
        return $list;
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
            if (! defined($data['dt_credit'])) {
                $data['dt_credit'] = 
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
