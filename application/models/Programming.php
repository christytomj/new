<?php

class Programming extends HandsOn_BasicModel
{

    protected $_data = array(
        'id_account'        => null,
        'id_subscriber'     => null,
        'dt_register'       => null,
        'id'                => null,
        'id_programming'    => null,
        'description'       => null,
        'dt_start'          => null,
        'dt_end'            => null,
        'dt_exclusion'       => null, 
        'time'              => null,
        'times'             => null,
        'reminder'          => null,
        'in_repetition'     => null,
        'in_frequency'      => null,
        'week_days'         => null,
        'month_days'        => null,
        'day'               => null,
        'month'             => null,
        'during_days'       => null,
        'interrupt_days'    => null,
        'interval_days'     => null,
        'id_description'    => null,
        'rcredit'           => null,
        'id_remedy'         => null,
        'qtd_new'           => null

    );
    protected static $_allowedData = array(
        'id',
        'id_subscriber',
        'description',
        'cell_phone',
        'dt_start',
        'dt_end',
        'time',
        'times',
        'reminder',
        'in_repetition',
        'in_frequency',
        'dt_register',
        'dt_exclusion',
        'name',
        'nr_descriptions',
        'in_send_option',
        'in_sunday',
        'in_monday',
        'in_tuesday',
        'in_wednesday',
        'in_thurday',
        'in_friday',
        'in_saturday',
        'week_days',
        'month_days',
        'day',
        'month',
        'during_days',
        'interrupt_days',
        'interval_days',
        'id_description',
        'rcredit',
        'id_remedy',
        'qtd_new'
    );

    protected static $_dataSource = array(
        'id'                => 'p.id',
        'dt_register'       => 'p.dt_register',
        'id_subscriber'     => 'p.id_subscriber',
        'name'              => 'a.name',
        'user_name'         => 'u.name',
        'cell_phone'        => 'a.cell_phone',
        'nr_descriptions'   => 'nd.nr_descriptions',
        'in_send_option'    => 'ua.in_send_option',
        'description'       => 'd.description',
        'dt_start'          => 'd.dt_start',
        'dt_end'            => 'd.dt_end',
        'time'              => 'group_concat(time_format(times.time, "%H:%i") SEPARATOR ", ") as time',
        'times'              => 'group_concat(time_format(t.time, "%H:%i") SEPARATOR ", ") as time',
        'reminder'          => 'd.reminder',
        'in_repetition'     => 'd.in_repetition',
        'in_frequency'      => 'd.in_frequency',
        'in_sunday'         => 'd.in_sunday',
        'in_monday'         => 'd.in_monday',
        'in_tuesday'        => 'd.in_tuesday',
        'in_wednesday'      => 'd.in_wednesday',
        'in_thurday'        => 'd.in_thursday',
        'in_friday'         => 'd.in_friday',
        'in_saturday'       => 'd.in_saturday',
        'interval_days'     => '(24 * d.interval_days) as interval_hours',
        'id_description'    => 'd.id',
        'rcredit'           => 'd.rcredit',
        'id_remedy'         => 'd.id_remedy',
        'dt_exclusion'      => 'd.dt_exclusion',
        'qtd_new'           => 'd.qtd_new'
    );

    public function get($idSubscriber, $data, $options = null) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }
        $select = '';
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select) ? self::$_dataSource[$name] : ',' . self::$_dataSource[$name];
        }

        $from = 'programming p, user_account ua, descriptions d, accounts a, '
                . '(SELECT id_programming, COUNT(DISTINCT id) nr_descriptions '
                . 'FROM descriptions GROUP BY id_programming) nd';
        $where = 'p.in_excluded=0 and ua.in_excluded=0 and d.in_excluded=0 '
                . 'and ua.id_account=p.id_account and p.id=d.id_programming '
                . 'and ua.id_account=a.id and nd.id_programming=p.id '
                . 'and p.id_subscriber='.$idSubscriber;
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                    " AND %s LIKE %s",
                    $options['filterColumn'],
                    self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }

        $order = '';
        if (!empty($options['sortColumn'])) {
            switch($options['sortColumn']) {
                case 'dt_register':
                    $column = 'p.dt_register';
                    break;
                case 'id':
                    $column = 'p.id';
                    break;
                case 'name':
                    $column = 'a.name';
                    break;
                case 'cell_phone':
                    $column = 'a.cell_phone';
                    break;
                case 'nr_descriptions':
                    $column = 'nd.nr_descriptions';
                    break;
                case 'in_send_option':
                    $column = 'ua.in_send_option';
                    break;
                default:
                    $column = self::$_dataSource[$options['sortColumn']];
                    break;
            }

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
        return self::$_db->fetchAll(
                "SELECT DISTINCT $select FROM $from WHERE $where $order $limit");
    }
    public function getDataForXml($idSubscriber, $data, $options = null) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }
        $select = '';
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select) ? self::$_dataSource[$name] : ',' . self::$_dataSource[$name];
        }

//        $from = 'programming p, user_account ua, descriptions d, accounts a,times t,'
//                . '(SELECT id_programming, COUNT(DISTINCT id) nr_descriptions '
//                . 'FROM descriptions GROUP BY id_programming) nd';
//        $where = 'p.in_excluded=0 and ua.in_excluded=0 and d.in_excluded=0 '
//                . 'and ua.id_account=p.id_account and p.id=d.id_programming '
//                . 'and ua.id_account=a.id and nd.id_programming=p.id '
//                .'and t.id_description = d.id '
//                . 'and p.id ='.$idSubscriber;
        
       $from = 'programming p, user_account ua, descriptions d, accounts a, '
                . '(SELECT  d.id, t.time as time '
                . 'from descriptions d, times t '
                . 'where t.id_description=d.id) times';
        $where = ''
                . 'ua.in_excluded=0 and ua.id_account=p.id_account '
                . 'and p.id=d.id_programming and ua.id_account=a.id '
                . 'and d.id=times.id and p.id='.$idSubscriber.' group by d.id';
        $sql = "SELECT $select FROM $from WHERE $where";   
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                    " AND %s LIKE %s",
                    $options['filterColumn'],
                    self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }

        $order = '';
        if (!empty($options['sortColumn'])) {
            switch($options['sortColumn']) {
                case 'dt_register':
                    $column = 'p.dt_register';
                    break;
                case 'id':
                    $column = 'p.id';
                    break;
                case 'name':
                    $column = 'a.name';
                    break;
                case 'cell_phone':
                    $column = 'a.cell_phone';
                    break;
                case 'nr_descriptions':
                    $column = 'nd.nr_descriptions';
                    break;
                case 'in_send_option':
                    $column = 'ua.in_send_option';
                    break;
                default:
                    $column = self::$_dataSource[$options['sortColumn']];
                    break;
            }

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
        return self::$_db->fetchAll(
                "SELECT DISTINCT $select FROM $from WHERE $where $order $limit");
    }

    public function count($idSubscriber, $filterColumn = null, $filter = null) {
        $from = 'programming p, user_account ua, accounts a';
        $where = 'p.in_excluded=0 and ua.in_excluded=0 '
                . 'and ua.id_account=p.id_account and ua.id_account=a.id '
                . 'and p.id_subscriber='.$idSubscriber;
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                    " AND %s LIKE %s",
                    $options['filterColumn'],
                    self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }
        return self::$_db->fetchOne("SELECT count(*) FROM $from WHERE $where");
    }

    public static function getProgramming($data, $id) {
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

        $from = 'programming p, user_account ua, descriptions d, accounts a, '
                . '(SELECT  d.id, t.time as time '
                . 'from descriptions d, times t '
                . 'where t.id_description=d.id) times';
        $where = ''
                . 'ua.in_excluded=0 and ua.id_account=p.id_account '
                . 'and p.id=d.id_programming and ua.id_account=a.id '
                . 'and d.id=times.id and p.id='.$id.' group by d.id';
        $sql = "SELECT $select FROM $from WHERE $where";

        return self::$_db->fetchAll($sql);
    }

    public static function getProgrammingByAccount($data, $id) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }

        $select = array();
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select[] = self::$_dataSource[$name];
        }
        $select = join(', ', $select);

        $from = 'programming p, user_account ua, descriptions d, accounts a, '
                . '(SELECT  d.id, t.time as time '
                . 'from descriptions d, times t '
                . 'where t.id_description=d.id) times';
        $where =
                //'p.in_concluded_send = \'Y\' AND '
                'ua.in_excluded=0 and ua.id_account=p.id_account '
                . 'and p.id=d.id_programming and ua.id_account=a.id '
                . 'and d.id=times.id and a.id='.$id.' group by d.id';
        $sql = "SELECT $select FROM $from WHERE $where";
        return self::$_db->fetchAll($sql);
    }

    /**
     * Pega os dados $data das programações do assinante $id para o período
     * entre $start e $end.
     * Para contas com opção JAVA!
     *
     * @param array $data quais campos retornar no array de hashes
     * @param int $id ID do assinante para achar as programações
     * @param Zend_Date $start início do periodo, null == desde sempre.
     * @param Zend_Date $end fim do periodo, null == desde sempre.
     * @return array array de hashes com cada linha (RowSet).
     */
    public function sendprogrammingReport(
            $data, $id, $start = null, $end = null) {

        if ($data != null && !is_array($data)) {
            throw new Exception('Initial data must be an array');
        }
        $select = '';
        if ($data == null) {
            $select = 'p.id, p.dt_register, p.id_subscriber, a.name,'
                . 'a.cell_phone,'
                . 'ua.in_send_option';
        } else {
            foreach ($data as $name) {
                if (!in_array($name, self::$_allowedData)) {
                    throw new Exception('Invalid property "' . $name . '"');
                }
                $select .= empty($select)
                        ? self::$_dataSource[$name]
                        : ',' . self::$_dataSource[$name];
            }
        }

        $from = 'programming p, user_account ua, accounts a';
        $where = 'ua.in_excluded=0 '
                . 'AND ua.id_account=p.id_account '
                . 'AND ua.id_account=a.id '
                . 'AND in_concluded_send="Y" '
                . 'AND ua.id_subscriber='.$id.' '
                . 'AND ua.in_send_option='.Accounts::SEND_OPTION_JAVA.' ';
        if(!empty($start)) {
            $where .= ' AND p.dt_register >= \''
                    . $start->toString(Util::DB_DATE_FORMAT).'\'';
        }
        if(!empty($end)) {
            $newend = new Zend_Date($end->getTimestamp());
            $where .= ' AND p.dt_register < \''
                    . $newend->addDay(1)->toString(Util::DB_DATETIME_FORMAT).'\'';
        }
        $sql = "SELECT $select FROM $from WHERE $where";
        error_log("SQL_SEND_PROGR::$sql");
        $ret = self::$_db->fetchAll($sql);
        error_log(__METHOD__.json_encode($ret));
        return $ret;
    }

    /**
     * @param string $ids lista de ids de assinantes separada por ','
     * @param Zend_Date $start data inicial ou null
     * @param Zend_Date $end data final ou null
     * @param float $add
     * @param float $multiplyj
     * @param float $multiplys
     */
    public function reportCSV(
            $ids,
            $start, $end,
            $add, $multiplyj, $multiplys) {
        if(empty($add)) {
            $add = 0;
        }
        if(empty($multiplyj)) {
            $multiplyj = 1;
        }
        if(empty($multiplys)) {
            $multiplys = 1;
        }

        $multiplyj = (float)$multiplyj;
        $multiplys = (float)$multiplys;
        $add = (float)$add;

        // pega as subqueries
        /** @var string $countj subquery contadora de javas */
        $countj = $this->_sqlReportSubqueryCountJavaProgrFromUid($start, $end);
        /** @var string $countj subquery contadora de SMSs */
        $counts = $this->_sqlReportSubqueryCountSmsFromUid($start, $end);
        // monta a query
        $selectFrom = $this->_sqlReportSelectAndFrom(
                $countj, $multiplyj, $counts, $multiplys, $add);
        $where = $this->_sqlReportWhereIds($ids);
        $sql = "$selectFrom $where";
        // roda.
        error_log(__METHOD__.':select do CSV:'.$sql);
        $result = self::$_db->fetchAll($sql);
        
        return $result;
    }

    /**
     *
     * @param string $name
     * @param Zend_Date|string $start
     * @param Zend_Date|string $end
     * @param float $add
     * @param float $multiplyj
     * @param float $multiplys
     * @return array
     */
    public function reportAllCSV(
            $name,
            $start, $end,
            $add, $multiplyj, $multiplys) {

        if(empty($add)) {
            $add = 0;
        }
        if(empty($multiplyj)) {
            $multiplyj = 1;
        }
        if(empty($multiplys)) {
            $multiplys = 1;
        }
        $multiplyj = (float)$multiplyj;
        $multiplys = (float)$multiplys;
        $add = (float)$add;

        // pega as subqueries
        /** @var string $countj subquery contadora de javas */
        $countj = $this->_sqlReportSubqueryCountJavaProgrFromUid($start, $end);
        /** @var string $countj subquery contadora de SMSs */
        $counts = $this->_sqlReportSubqueryCountSmsFromUid($start, $end);
        // monta a query
        $selectFrom = $this->_sqlReportSelectAndFrom(
                $countj, $multiplyj, $counts, $multiplys, $add);
        $where = $this->_sqlReportWhereNameSearch($name);
        $sql = "$selectFrom $where";
        // roda.
        
        $result = self::$_db->fetchAll($sql);

        return $result;
    }

    /**
     * Cria a parte de SELECT e FROM da query comum de Reports.
     * Neste select é que faz o cálculo dos créditos e somas e multiplicações.
     * @param string $countj subsql para contar créditos Java
     * @param float $multiplyj valor de cada crédito Java
     * @param string $counts subsql para contar créditos SMS
     * @param float $multiplys valor de cada crédito SMS
     * @param float $add valor a adicionar ao total.
     * @return string a query.
     */
    private function _sqlReportSelectAndFrom(
            $countj, $multiplyj, $counts, $multiplys, $add) {
        $sql = "SELECT DISTINCT(u.code),"
                . "((($countj) * $multiplyj) "
                . "+ (($counts) * $multiplys) "
                . "+ $add) as valor ";
        $sql .= 'FROM programming p, users u, profiles ';
        return $sql;
    }


    /**
     * Cria a parte básica do WHERE das queies de report.
     */
    private function _sqlReportWhereCommon() {
        $sql = 'WHERE p.in_excluded=0 AND u.in_excluded=0 '
            . 'AND profiles.label = "assinante" '
            . 'AND profiles.id = u.id_profile ';
        return $sql;
    }

    /**
     * extende a _sqlReportWhereCommon filtrando o nome do usuário assinante
     * @param string
     * @return string
     */
    private function _sqlReportWhereNameSearch($name) {
        $sql = $this->_sqlReportWhereCommon();
        if(!empty($name)) {
            $sql .= ' AND u.name LIKE '
                . self::$_db->quote('%%' . $name . '%%');
        }
        return $sql;
    }

    /**
     * extende a _sqlReportWhereCommon filtrando uma lista de IDs
     * @param string
     * @return string
     */
    private function _sqlReportWhereIds($ids) {
        $sql = $this->_sqlReportWhereCommon();
        if(!empty($ids)) {
            $sql .= ' AND u.id in ('.$ids.')';
        }
        return $sql;
    }

    /**
     * Conta os SMS comprados
     * Indexa pelo 'u.id' da query super.
     *
     * @param Zend_Date $start
     * @param Zend_Date $end
     * @return string o sql, subquery
     */
    private function _sqlReportSubqueryCountSmsFromUid(
            Zend_Date $start = null, Zend_Date $end = null) {
        $sql = 'SELECT ifnull(sum( b.qty ), 0)'
            . 'FROM sms_buy b, accounts a '
            . 'WHERE b.id_account = a.id '
            . "AND a.id_user = u.id";
        if(!empty($start)) {
            $dbdt = $start->get(Util::DB_DATE_FORMAT);
            $sql .= " AND b.dt_credit >= '$dbdt'";
        }
        if(!empty($end)) {
            $dbdt = $end->addDay(1)->get(Util::DB_DATE_FORMAT);
            $sql .= " AND b.dt_credit < '$dbdt'";
        }
        return $sql;
    }

    /**
     * Conta as programações Java realizadas.
     * Indexa pelo 'u.id' da query super.
     *
     * @param Zend_Date $start
     * @param Zend_Date $end
     * @return string o sql, subquery
     */
    private function _sqlReportSubqueryCountJavaProgrFromUid(
            Zend_Date $start = null, Zend_Date $end = null) {
        $sql = "SELECT ifnull(count( p.id ), 0) AS count "
            . 'FROM programming p JOIN user_account ua '
            . 'ON (p.id_subscriber=ua.id_subscriber '
            . 'AND p.id_account=ua.id_account) '
            . 'WHERE p.id_subscriber = u.id '
            . ' AND p.in_concluded_send=\'Y\''
            . ' AND ua.in_send_option=\''.Accounts::SEND_OPTION_JAVA.'\'';
        if(!empty($start)) {
            $dbdt = $start->get(Util::DB_DATE_FORMAT);
            $sql .= " AND p.dt_register >= '$dbdt'";
        }
        if(!empty($end)) {
            $dbdt = $end->addDay(1)->get(Util::DB_DATE_FORMAT);
            $sql .= " AND p.dt_register < '$dbdt'";
        }
        return $sql;
    }


    /**
     * Salva um novo Programming com id_account e id_subscriber na tabela
     * programming e, óbvio, seta o id_programming recém criado.
     * id_programming
     * @return Programmig $this
     */
    public function save() {

        $data = array(
            'id_subscriber' => $this->id_subscriber,
            'id_account'    => $this->id_account,
            'dt_register'   => Zend_Date::now()->get(Util::DB_DATETIME_FORMAT),
            'in_excluded'   => '0'
        );

        self::$_db->insert('programming', $data);
        $id_programming = self::$_db->lastInsertId();
        $this->id_programming = $id_programming;

        return $this;
    }

    public function saveDescription() {

        $date = new Zend_Date();
        //@todo testar o codigo abaixo comentado
        //$this->dt_start = $date->get($this->dt_start, 'pt_BR');
        //$this->dt_start = new Zend_Date($this->dt_start, 'pt_BR');
        //$this->dt_start = $this->dt_start->get(Zend_Date::TIMESTAMP);
        //if(!empty($this->dt_end)) {
        //$this->dt_end = $date->get($this->dt_end, 'pt_BR');
            //$this->dt_end = new Zend_Date($this->dt_end, 'pt_BR');
            //$this->dt_end = $this->dt_end->get(Zend_Date::TIMESTAMP);
        //}
        $data_description = array(
            //'id'              => $this->id,
            'id_programming'  => $this->id_programming,
            'description'     => $this->description,
            'dt_start'        => $this->dt_start->get(Util::DB_DATE_FORMAT),
            'dt_end'          => $this->dt_end 
                                ? $this->dt_end->get(Util::DB_DATE_FORMAT)
                                : null,
            'reminder'        => $this->reminder,
            'in_repetition'   => $this->in_repetition,
            'dt_register'     => Zend_Date::now()->get(Util::DB_DATE_FORMAT),
            'in_frequency'    => $this->in_frequency,
            'rcredit'         => $this->rcredit,
            'id_remedy'       => $this->id_remedy,
            'qtd_new'       => $this->qtd_new
        );

        if ($this->in_repetition == 1 && $this->in_frequency == 2) {
            $days = array_fill(1, 7, 'N');
            foreach($this->week_days as $wd) {
                $days[$wd] = 'Y';
            }
            //print_r($days);
            //exit();
            $data_description['in_sunday']      = $days[7];
            $data_description['in_monday']      = $days[1];
            $data_description['in_tuesday']     = $days[2];
            $data_description['in_wednesday']   = $days[3];
            $data_description['in_thursday']    = $days[4];
            $data_description['in_friday']      = $days[5];
            $data_description['in_saturday']    = $days[6];
        }
        if (!empty($this->during_days) || !empty($this->interrupt_days)) {
            $data_description['during_days']    = (int)$this->during_days;
            $data_description['interrupt_days'] = (int)$this->interrupt_days;
        }

        if (!empty($this->interval_days)) {
            $data_description['interval_days']  = (int)$this->interval_days;
        }

        self::$_db->insert('descriptions', $data_description);

        $this->id = self::$_db->lastInsertId();

        if (!empty($this->times)) {
            $this->times = array_unique($this->times);
            foreach($this->times as $time) {
                self::$_db->insert('times', array(
                        'id_description' => $this->id,
                        'time' => $time));
            }
        }

        if (!empty($this->month_days)) {
            $this->month_days = array_unique($this->month_days);
            foreach($this->month_days as $day) {
                self::$_db->insert('days', array(
                        'id_description' => $this->id,
                        'day' => $day));
            }
        }

        if(!empty($this->month) && !empty($this->day)) {
            self::$_db->insert('months', array(
                    'id_description' => $this->id,
                    'day' => $this->day,
                    'month' => $this->month));
        }

        return $this;
    }
    
    public static function descontaRcredit($id_description, $rcredit) {
        $numrows = self::$_db->update(
                'descriptions',
                array('rcredit'=> 0+$rcredit),
                'id='.$id_description);
        error_log(__METHOD__."Numrows=$numrows");
        
    }

    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Conta não identificada para remoção');
        }

        $dtExclusion = Zend_Date::now()->get(Util::DB_DATETIME_FORMAT);

        self::$_db->update(
                'programming',
                array('in_excluded'=> 1),
                'id='.$this->id);
        self::$_db->update(
                'descriptions',
                array(
                    'in_excluded'=> 1,
                    'dt_exclusion' => $dtExclusion),
                'id_programming='.$this->id);
    }

    /**
     * @param int $idd id de uma descrição
     * @return string a lista de <dia>/<mes> para esta descrition.
     */
    public static function getDaysForDescription($idd) {
        $select = self::$_db->select()
                ->from('days', array('day'))
                ->where('id_description=?', $idd);
        $rs = self::$_db->fetchAll($select);

        $days = array();
        foreach ($rs as $row) {
            $days[] = $row['day'];
        }

        return implode(',', $days);
    }

    /**
     *
     * @param int $idd id de uma descrição
     * @return string a lista de <dia>/<mes> para esta descrition.
     */
    public static function getMonthsForDescription($idd) {
        $select = self::$_db->select()
                ->from('months', array('day', 'month'))
                ->where('id_description=?', $idd);
        $rs = self::$_db->fetchAll($select);

        $months = array();
        foreach ($rs as $row) {
            $months[] = $row['day'].'/'.Util::getNomeMes($row['month']);

        }

        return implode(',', $months);
    }

    /**
     * COnverte um array contendo (ou não) weekdays do banco: in_sundai, etc
     * para uma string com os nomes da semana listados.
     *
     * @param array $weekdaysArray
     * @param string $sep separador dos dias da semana (opt)
     * @return string com a lista de dias da semana
     */
    public static function weekDaysToString($weekdaysArray, $sep=', ') {
        $weeknames = implode($sep,
            array_keys(
                // só cria os que não tem valor undefined
                array(
                    'segunda' => $weekdaysArray['in_monday'],
                    'terça' => $weekdaysArray['in_tuesday'],
                    'quarta' => $weekdaysArray['in_wednesday'],
                    'quinta' => $weekdaysArray['in_thursday'],
                    'sexta' => $weekdaysArray['in_friday'],
                    'sábado' => $weekdaysArray['in_saturday'],
                    'domingo' => $weekdaysArray['in_sunday']
                )
            )
        );

        return $weeknames;

    }
    
}
