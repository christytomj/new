<?php

/**
 * Loga o refund de créditos de remédios das contas que não usaram ou expiraram.
 */
class CredRefund extends HandsOn_BasicModel {
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
        'id'            => 'id',
        'id_user'       => 'id_user',
        'id_account'	=> 'id_account',
        'qty'           => 'qty',
        'dt_credit'     => 'dt_credit',
    );

    protected static $_fromTable = 'cred_ret';

    public function getLab() {
        return Users::getUserById($this->id_user);
    }

    public function getAccount() {
        return Accounts::getAccountById($this->id_account);
    }

    /**
     * Lista os SmsBuy por usuário. (quem comprou o crtédito)
     * @param <int> $idAcc o id de quem comprou credito
     * @param $dti a data de inicio (inclusive)
     * @param $dtf a data de fim (exclusive)
     */
    public static function listByUser($idAcc, $dti=null, $dtf=null) {
        $sql = sprintf(
                'SELECT * FROM %s WHERE id_user = %s',
                self::$_fromTable,
                self::$_db->quote($idAcc));
        if ($dti !== null) {
            $sql .= sprintf(
                ' AND dt_credit >= \'%s\'',
                $dti->get(Util::DB_DATE_FORMAT)
            );
        }
        if ($dtf !== null) {
            $sql .= sprintf(
                ' AND dt_credit < \'%s\'',
                $dtf->get(Util::DB_DATE_FORMAT)
            );
        }

        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }

    /**
     * Soma os campos qty do resultado duma chamada de listByUser;
     */
    public static function sumQtyByUser($idAcc, $dti=null, $dtf=null) {
        // @todo usar um SUM(qty) com GROUP BY...
        $val = 0;
        $lista = self::listByUser($idAcc, $dti, $dtf);
        foreach ($lista as $cada) {
            $val += $cada->qty;
        }
        return $val;
    }

    /**
     * Lista os CredRefund por account. (quem comprou o crtédito)
     * @param int $idAcc o id de quem comprou credito
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
                $newend->addDay(1)->get(Util::DB_DATE_FORMAT)
            );
        }

        $rs = self::$_db->fetchAll($sql);
        return self::resultSetToList($rs);
    }

    private static function resultSetToList($rs) {
        $list = array();
        foreach ($rs as $rec) {
            $list[] = new CredRefund($rec);
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
        $select = '*';
        
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
