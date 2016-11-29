<?php

class Accounts extends HandsOn_BasicModel {
    /** 1: Opção de envio JAVA */
    const SEND_OPTION_JAVA = 1;
    /** 2: Opção de envio SMS */
    const SEND_OPTION_SMS = 2;
    /** 3: Opção de envio Laboratorio (nova) */
    const SEND_OPTION_LAB = 3;

    protected $_data = array(
        'id'                    => null,
        'id_user'               => null,
        'name'                  => null,
        'dateModification'      => null,
        'term_of_use_check'     => null,
        'cell_phone'            => null,
        'in_send_option'        => null,
        'dt_register'           => null,
        'in_executed_download'  => null,
        'in_excluded'           => null,
        'credit'                => null,
        'subscriber'            => null,
        'code'                  => null,
        'is_active'             => null,
        'activation_code'       => null,
        'id_account'            => null
    );

    protected static $_allowedData = array(
        'id',
        'id_user',
        'name',
        'cell_phone',
        'in_send_option',
        'dt_register',
        'in_executed_download',
        'in_excluded',
        'credit',
        'subscriber',
        'code',
        'is_active',
        'activation_code',
        'id_account'
    );

    protected static $_dataSource = array(
        'id'                      => 'a.id',
        'id_user'                 => 'a.id_user',
        'name'                    => 'a.name',
        'cell_phone'              => 'a.cell_phone',
        'in_send_option'          => 'ua.in_send_option',
        'dt_register'             => 'a.dt_register',
        'in_executed_download'    => 'a.in_executed_download',
        'in_excluded'             => 'a.in_excluded',
        'credit'                  => 'a.credit',
        'subscriber'              => 'u.name as subscriber',
        'code'                    => 'a.code',
        'is_active'               => 'a.is_active',
        'activation_code'         => 'a.activation_code',
        'id_account'               => 'ua.id_account'
        //        'subscriber'              => 'u.name'
    );

    public function isOptJava() {
        return $this->in_send_option == self::SEND_OPTION_JAVA;
    }
    public function isOptLab() {
        return $this->in_send_option == self::SEND_OPTION_LAB;
    }
    public function isOptSMS() {
        return $this->in_send_option == self::SEND_OPTION_SMS;
    }
    public function __set($name, $value) {
        if (!array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid property "' . $name . '"');
        }
        $this->_data[$name] = $value;
    }

    public function get($idUser, $data, $options = null, $isAdmin = false) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }

        $select = '';
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select)
                ? self::$_dataSource[$name]
                : ',' . self::$_dataSource[$name];
        }

        if($isAdmin) {
            $from = 'accounts a, user_account ua, users u';
            $where = 'a.in_excluded=0 and a.id=ua.id_account '
                    . 'and ua.id_subscriber=u.id and ua.in_send_option in (2,3)';
        } else {
            $from = 'accounts a, user_account ua';
            $where = 'a.in_excluded=0 and a.id=ua.id_account and id_user='
                    . $idUser;
        }
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                " AND %s LIKE %s",
                $options['filterColumn'],
                self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }

        $order = '';
        if (!empty($options['sortColumn'])) {
            $column = ($options['sortColumn'] == 'id')
                ? 'a.id' : self::$_dataSource[$options['sortColumn']];
            if($column == 'u.name as subscriber') {
                $column = 'u.name';
            }
            $order = 'ORDER BY ' . $column . $options['sortOrder'];
        }

        $limit = '';
        if (!empty($options['rowCount'])) {
            $limit = 'LIMIT ' . (int)$options['rowCount'];
            if ($options['page'] > 1) {
                $limit .= ' OFFSET ' . (int)($options['rowCount'] * ($options['page'] - 1));
            }
        }

        return self::$_db->fetchAll(
                "SELECT $select FROM $from WHERE $where $order $limit");
    }

    public function count(
        $idUser, $filterColumn = null,
        $filter = null, $admin = false) {
        $from = 'accounts a,  user_account ua';
        //$where = 'a.in_excluded=0 and a.id_user='.$idUser;
        if($admin == false) {
            $where = 'a.in_excluded=0 and a.id=ua.id_account and id_user='
                . $idUser;
        } elseif($admin == true) {
            $where = 'a.in_excluded=0 and a.id=ua.id_account';
        }
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                " AND %s LIKE %s",
                $options['filterColumn'],
                self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }
        return self::$_db->fetchOne("SELECT count(*) FROM $from WHERE $where");
    }

    public function read($data) {
        if (!is_array($data)) {
            $data = array_keys(self::$_dataSource);
        }
        $select = '';
        foreach ($data as $name) {
            if (!array_key_exists($name, $this->_data)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select)
                ? self::$_dataSource[$name]
                : ',' . self::$_dataSource[$name];
        }

        if (!empty($select)) {
            if (empty($this->id)) {
                $key = 'name';
                $value = self::$_db->quote($this->name);
            } else {
                $key = 'id';
                $value = $this->id;
            }
            $results = self::$_db->fetchRow(
                "SELECT $select FROM accounts a, user_account ua "
                . "WHERE a.$key = $value AND "
                . "a.in_excluded=0 AND a.id = ua.id_account");
            if (!empty($results)) {
                foreach ($results as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function save() {
        $this->dateModification = Zend_Date::now()->get(Util::DB_DATE_FORMAT);
//        if ($this->term_of_use_check[0] == 0) {
//            throw new Exception('O termo de uso deve ser aceito!');
//        }
        $cellPhone = '';
        if (isset($this->cell_phone)) {
            $cellPhone = Util::cleanPhoneNumber($this->cell_phone);
        }

        if (isset($this->id)) {
            // update
            $data = array(
                'id'              => $this->id,
                'name'            => $this->name,
                'cell_phone'      => $cellPhone,
                //'in_send_option'  => $this->in_send_option,
                //'dt_register'     => $this->dateModification,
            );
            if ($this->is_active !== NULL) {
                $data['is_active'] = $this->is_active;
            }
            $dataUserAccount = array(
                'id_account'             => $this->id,
                'in_send_option'         => $this->in_send_option,
            );
            self::$_db->update('accounts', $data, 'id='.$this->id.'');
            self::$_db->update('user_account', $dataUserAccount,
                'id_account='.$this->id.'');

            return self::$_db->fetchOne(
                "SELECT code FROM accounts WHERE cell_phone=".$cellPhone);

        } else {
           
            // novo
            if (empty($this->cell_phone)
                    || empty($this->name) || empty($this->in_send_option)) {
                throw new Exception(
                'Sem dados suficientes para criar nova conta');
            }

            // SEMOPTIN - este código era gerado para o optin inicial
            // $actCode = substr((uniqid(rand(), true)), 0, 5);

            // Se exite o celular na base pega o mesmo código para download
            // de programações no aplicativo
            $existCell = self::getCell($this->cell_phone);

            if(!empty($existCell)) {
                $code = $existCell['code'];
            } else {
                do {
                    $code = substr((uniqid(rand(), true)), 0, 10);
                    $existCode = self::$_db->fetchRow(
                        "SELECT code FROM accounts a WHERE a.code = '$code'");
                } while(!empty($existCode));
            }


            $data = array(
                'id_user'               => $this->id_user,
                'name'                  => $this->name,
                'cell_phone'            => $cellPhone,
                'code'                  => $code,
                'dt_register'           => $this->dateModification,
                'in_excluded'           => '0',
                'activation_code'       => null, // SEMOPTIN - conta já nasce ativa
                'is_active'             => 1, // SEMOPTIN - conta já nasce ativa
            );
            if ($this->is_active !== NULL) {
                $data['is_active'] = $this->is_active;
            }

            self::$_db->insert('accounts', $data);
            $id_account = self::$_db->lastInsertId();

            $this->populate($data);
            $this->id = $id_account;

            $dataUserAccount = array(
                /*
                 * @todo - deve-se trocar o id_subscriber
                 */
                'id_subscriber'          => $this->id_user,
                'id_account'             => $id_account,
                'in_send_option'         => $this->in_send_option,
                'dt_register'            => $this->dateModification,
                'in_excluded'            => '0'
            );

            self::$_db->insert('user_account', $dataUserAccount);
            $this->populate($dataUserAccount);
        }
        return $this;
    }

    public function setActive($val) {
        $data = array(
            'is_active' => $val ? 1 : 0
        );
        self::$_db->update(
                'accounts',
                $data,
                'id='.$this->id.''
        );
    }

    public function saveCredit() {
        $data = array(
            'id'              => $this->id,
            'credit'          => $this->credit,
        );
        self::$_db->update('accounts', $data, 'id='.$this->id.'');
    }

    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Conta não identificada para remoção');
        }
        self::$_db->update('accounts', array('in_excluded'=> 1), 'id='.$this->id);
    }

    public static function getCell($cell) {
        $cell = self::explodeCell($cell);
        $maxDate = self::$_db->fetchOne(
                "SELECT max(dt_register) FROM accounts WHERE cell_phone="
                . $cell);
        if(!empty($maxDate)) {
            $account = self::$_db->fetchRow(
                'SELECT * '
                . 'FROM accounts '
                . 'WHERE cell_phone=\''. $cell.'\''
                . ' ORDER BY dt_register DESC '
                . 'LIMIT 1');
            return $account;
        }
    }

    /**
     * retorna o código (code) para uma conta dado o celular.
     */
    public static function getCodeForCell($cell) {
        $cell = self::explodeCell($cell);
        $code = self::$_db->fetchOne(
            'SELECT code '
            . 'FROM accounts '
            . 'WHERE cell_phone=\''. $cell.'\''
            . ' ORDER BY dt_register DESC '
            . 'LIMIT 1');
        return $code;
    }

    /**
     *  Pega uma Accounts pelo número do celular.
     * @param <String> $cell o numero do celular
     * @return <Accounts> o Accounts ou Boolean false
     */
    public static function getAccountByCell($cell) {
        $cell = self::explodeCell($cell);
        $ret = array();
        $accounts = self::$_db->fetchAll(sprintf(
            'SELECT * FROM accounts a, user_account ua '
            . 'WHERE a.id = ua.id_account AND a.cell_phone=%s '
            . 'ORDER BY a.id DESC ',
            self::$_db->quote($cell)
        ));
        foreach ($accounts as $cadaac) {
            $acco = new Accounts();
            $acco->populate($cadaac);
            $ret[] = $acco;
        }
        return $ret;
    }

    /**
     *  Pega uma Accounts pelo id.
     * @param int $id o id da account
     * @return Accounts o Accounts ou Boolean false
     */
    public static function getAccountById($id) {
        $sql = sprintf(
            'SELECT * FROM accounts a, user_account ua '
            . 'WHERE a.id = ua.id_account AND a.id=%s ',
            self::$_db->quote($id)
        );

        $accountData = self::$_db->fetchRow($sql);

        $account = null;
        if ($accountData) {
            $account = new Accounts();
            $account->populate($accountData);
        }
        return $account;
    }

    /**
     *  Pega uma lista Accounts pelo id do assinante.
     * @param int $id o id do subscriber
     * @return array lista de accounts
     */
    public static function getAccountBySubscriber($id) {
        if (is_object($id)) {
            $id = $id->id;
        }
        $sql = sprintf(
            'SELECT * FROM accounts a, user_account ua '
            . 'WHERE a.id = ua.id_account AND a.id_user=%s ',
            self::$_db->quote($id)
        );

        $accountData = self::$_db->fetchAll($sql);

        $accs = array();

        foreach ($accountData as $eadata) {
            $account = new Accounts();
            $account->populate($eadata);
            $accs[] = $account;
        }
        return $accs;
    }

    /**
     * @return array
     */
    public static function getCellFromSubscriber($cell, $idUser) {
        $cell = self::explodeCell($cell);
        $account = self::$_db->fetchRow(
                'SELECT * '
                .' FROM accounts '
                .'WHERE id_user = \''.$idUser.'\' '
                .'AND cell_phone=\''.$cell.'\' '
                .'AND in_excluded = 0 '
                .'ORDER BY dt_register DESC '
                .'LIMIT 1');
        return $account;
        return null;
    }

    public static function explodeCell($cell) {
        return Util::cleanPhoneNumber($cell);
    }

    /**
     *  Gera o código para bloquear SMS; envia o SMS com o código; e
     * guarda na base
     * @return <type> o código gerado, enviado e guardado.
     */
    public function sendBlockingCode() {
        $cod = self::makeBlockingCode();
        $data = array(
            'activation_code' => $cod
        );
        self::$_db->update(
            'accounts',
            $data,
            'cell_phone='.$this->cell_phone
        );
        $msg = 
            'Seu código para bloqueio de mensagens é: '
            . $cod;
        SMSSender::sendSMS(array(
            'phone' => $this->cell_phone,
            'message' => $msg
        ));
        return $cod;
    }

    private static function makeBlockingCode() {
        $code = array();
        $code[] = rand('0', '9');
        $code[] = rand('0', '9');
        $code[] = rand('0', '9');
        $code[] = rand('0', '9');
        $code = join('', $code);
        return $code;
    }

    /**
     * Desbloqueia a conta e limpa o campo de código de desbloqueio
     */
    public function unblockAndCleanCode() {
        self::$_db->update(
            'accounts',
            array(
                'activation_code' => null,
                'is_active' => 0
            ),
            'cell_phone='.$this->cell_phone
        );
    }

    /**
     * Marca o código de que o download do aplicativo java já foi feito.
     */
    public static function markDownloadExecutedByCode($code) {
        self::$_db->update(
            'accounts',
            array('in_executed_download' => 'Y'),
            'code='.$code
        );
    }

    public static function getSendOptionName($senOpt) {
        static $opns = array(
            Accounts::SEND_OPTION_JAVA => 'Aplicativo Java',
            Accounts::SEND_OPTION_LAB => 'Laboratório',
            Accounts::SEND_OPTION_SMS => 'SMS'
        );
        return $opns[$senOpt];
    }

    /**
     * Testa se tem alguma conta SMS ou LAB ativa para este $num de telefone.
     *
     * @param string número de telefone (55NNNNNNNN)
     * @return bool
     */
    public static function isActiveSMSForNumber($num) {
        /** @var Zend_Db_Table_Select */
        $select = 'SELECT count(*) FROM accounts a '
                . 'INNER JOIN user_account u ON (a.id=u.id_account) '
                . 'WHERE a.cell_phone = \''.$num.'\' '
                . 'AND u.in_excluded=0 '
                . 'AND a.is_active=1 '
                . 'AND u.in_send_option in (2,3)'; // SMS ou LAB
        $conta = self::$_db->fetchOne($select);
        return $conta > 0;
    }

    /**
     * Testa se tem alguma conta JAVA ativa para este $num de telefone.
     *
     * @param string número de telefone (55NNNNNNNN)
     * @return bool
     */
    public static function isActiveJAVAForNumber($num) {
        /** @var string */
        $select = 'SELECT count(*) FROM accounts a '
                . 'INNER JOIN user_account u ON (a.id=u.id_account) '
                . 'WHERE a.cell_phone = \''.$num.'\' '
                . 'AND u.in_excluded<>\'N\' '
                . 'AND a.in_executed_download=1 '
                . 'AND u.in_send_option = 1'; // Java
        $conta = self::$_db->fetchOne($select);
        return ($conta > 0);
    }
    
    
     public function getuseracc($cell,$id){
        $cell = self::explodeCell($cell);
//        $id = self::explodeCell($id);
        $ret = array();
        $accounts = self::$_db->fetchAll(sprintf(
            'SELECT * FROM accounts a, user_account ua,programming p '
            . 'WHERE a.id = ua.id_account AND a.cell_phone=%s AND a.id = p.id_account '
            . 'ORDER BY a.id DESC ',
            self::$_db->quote($cell)
//            self::$_db->quote($id)
        ));
        foreach ($accounts as $cadaac) {
            $acco = new Accounts();
            $acco->populate($cadaac);
            $ret[] = $acco;
        }
        return $ret;
    }
}
