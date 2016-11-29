<?php

class Schedule extends HandsOn_BasicModel {
    const UPDATE_LOOKAHEAD_MINS = 15;
    const UPDATE_LOOKBEHIND_MINS = 80;
    const SEND_LOOKBEHIND_MINS = 30;

    public static function updateSchedule(Zend_Date $now) {
        $dtNow = Zend_Date::now();
        $monthDay = $dtNow->get("dd");
        $month = $dtNow->get("MM");

        $dtPlus = Util::cloneZendDate($now)->addMinute(self::UPDATE_LOOKAHEAD_MINS);
        $timePlus = $dtPlus->isToday() ? $dtPlus->get(Util::DB_TIME_FORMAT) : '23:59:00';
        $dtMinus = Util::cloneZendDate($now)->subMinute(self::UPDATE_LOOKBEHIND_MINS);
        $timeMinus = $dtMinus->isToday() ? $dtMinus->get(Util::DB_TIME_FORMAT) : '00:00:00';

        $todayDate = $dtNow->get(Util::DB_DATE_FORMAT);
        $weekDay = self::getDbFieldForWeekday($dtNow);

        $commonSelect = sprintf(
                        "SELECT d.id, t.time,"
                        // atenção para o espaço depois do %s
                        . " cast(concat('%s ', t.time) as datetime) as ctime ",
                        $todayDate
        );
        $commonFrom =
                "FROM descriptions d, times t, programming p, user_account ua ";
        $commonWhere = sprintf(
                        'WHERE d.id_programming = p.id '
                        . ' AND p.id_subscriber = ua.id_subscriber '
                        . ' AND p.id_account = ua.id_account '
                        . " AND d.id = t.id_description "
                        . ' AND ua.in_send_option IN (2,3) ' // sms ou lab
                        . ' AND p.in_excluded = 0 '
                        . ' AND p.in_concluded_send = \'N\' '
                        . ' AND t.time BETWEEN \'%s\' AND \'%s\' '
                        . ' AND d.dt_start <= \'%s\' '
                        . ' AND (d.dt_end IS NULL OR d.dt_end >= \'%s\') '
                        , $timeMinus
                        , $timePlus
                        , $todayDate
                        , $todayDate
        );

        //IN_REPETITION = 0
        $sql =
                $commonSelect . $commonFrom . $commonWhere
                . ' AND d.in_repetition = 0 '
                . sprintf(' AND d.dt_start = \'%s\' ', $todayDate);
        $case1 = self::$_db->fetchAll($sql);

        //IN_REPETITION = 1 AND IN_FREQUENCY = 1
        $sql =
                $commonSelect . $commonFrom . $commonWhere
                . " AND d.in_repetition = 1 "
                . " AND d.in_frequency = 1 ";
        $case2 = self::$_db->fetchAll($sql);

        //IN_REPETITION = 1 AND IN_FREQUENCY = 2
        $sql =
                $commonSelect . $commonFrom . $commonWhere
                . " AND d.in_repetition = 1 "
                . " AND d.in_frequency = 2 "
                . " AND $weekDay = 'Y' ";
        $case3 = self::$_db->fetchAll($sql);

        //IN_REPETITION = 1 AND IN_FREQUENCY = 3
        $sql =
                $commonSelect
                . $commonFrom
                . "  , days dy "
                . $commonWhere
                . " AND d.in_repetition = 1 "
                . " AND d.in_frequency = 3 "
                . " AND dy.day = '$monthDay' "
                . " AND dy.id_description = d.id";
        $case4 = self::$_db->fetchAll($sql);

        //IN_REPETITION = 1 AND IN_FREQUENCY = 4
        $sql =
                $commonSelect
                . $commonFrom
                . "  , months m "
                . $commonWhere
                . " AND d.in_repetition = 1 "
                . " AND d.in_frequency = 4 "
                . " AND m.day = '$monthDay' "
                . " AND m.month = '$month' "
                . " AND m.id_description = d.id";
        $case5 = self::$_db->fetchAll($sql);

        //IN_REPETITION = 2 AND INTERVAL_DAYS
        $sql =
                $commonSelect . $commonFrom . $commonWhere
                . " AND d.in_repetition = 2 "
                . " AND MOD(
                    DATEDIFF('$todayDate', d.dt_start), d.interval_days) = 0 ";
        $case6 = self::$_db->fetchAll($sql);

        //IN_REPETITION = 2 AND INTERRUPT_DAYS
        $sql =
                $commonSelect . $commonFrom . $commonWhere
                . " AND d.in_repetition = 2 "
                . " AND ( "
                . "   MOD( DATEDIFF('$todayDate', d.dt_start), "
                . "     d.during_days + d.interrupt_days "
                . "   ) < during_days "
                . " )";
        $case7 = self::$_db->fetchAll($sql);

        $result = array_merge($case1, $case2, $case3, $case4, $case5, $case6, $case7);
        error_log('Schedule::RESULT #' . count($result));
        foreach ($result as $grid) {
            $id_description = $grid['id'];
            $time_ss = $grid['time'];

            $id_ss = self::$_db->fetchOne(
                    "SELECT ss.id FROM schedule_send ss "
                    . "WHERE id_description = $id_description "
                    . " AND time = '$time_ss' "
                    . " AND dt_provided = '$todayDate'");
            if (empty($id_ss)) {
                error_log(__METHOD__
                    ."schedule_send:descr$id_description:tm$time_ss:$todayDate");
                self::$_db->insert(
                    'schedule_send',
                    array(
                        'id_description' => $id_description,
                        'time' => $time_ss,
                        'dt_provided' => $todayDate,
                        'dt_register' => $dtNow->get(Util::DB_DATETIME_FORMAT)));
            } else {
                error_log(__METHOD__
                    .":schedule pulado:descr$id_description:tm$time_ss:$todayDate");
            }
        }

        /*
         * checa o status no fim pra não atrapalhar a operação.
         */
        SMSSender::checkSMSStatus();
    }

    private static function getDbFieldForWeekday($dtNow) {
        $weekDay = $dtNow->get(Zend_Date::WEEKDAY_DIGIT);

        switch ($weekDay) {
            case 0: // 'domingo':
                $weekDay = 'd.in_sunday';
                break;
            case 1: // 'segunda-feira':
                $weekDay = 'd.in_monday';
                break;
            case 2: // 'terça-feira':
                $weekDay = 'd.in_tuesday';
                break;
            case 3: // 'quarta-feira':
                $weekDay = 'd.in_wednesday';
                break;
            case 4: // 'quinta-feira':
                $weekDay = 'd.in_thursday';
                break;
            case 5: // 'sexta-feira':
                $weekDay = 'd.in_friday';
                break;
            case 6: // 'sábado':
                $weekDay = 'd.in_saturday';
                break;
        }
        return $weekDay;
    }

    public static function flushScheduledSMS() {
        $dateNow = new Zend_Date('pt_BR');

        $smsList = self::getListSMSToSend();

        $count = count($smsList);
        echo($count);

        error_log(__METHOD__ . ':smsToSend=#' . $count);
        foreach ($smsList as $sms) {
            error_log(__METHOD__ . ':sms=' . json_encode($sms));
            if (SMSSender::isSMSAlreadySent($sms)) {
                error_log(__METHOD__ . "id{$sms['id']} already sent.");
                continue;
            }

            $sndOptSMS = 
                ($sms['in_send_option'] == Accounts::SEND_OPTION_SMS) 
                ? true : false;
            $sndOptLab = 
                ($sms['in_send_option'] == Accounts::SEND_OPTION_LAB)
                ? true : false;

            $hasCredit =
                    ($sndOptSMS && ($sms['credit'] > 0))
                    ||
                    ($sndOptLab && self::hasCreditFromLab($sms));

            if ($hasCredit) {
                $sentResponse = 0;

                // Manda o SMS
                $smsData = array(
                        'phone' => '55' . $sms['cell_phone'],
                        'message' => $sms['description'],
                        'provided_time' => $sms['provided_time'],
                        'id_programming' => $sms['programming_id'],
                );
                $sentResponse = SMSSender::sendSMS($smsData);
error_log(__METHOD__.':SMS sent:'.  json_encode($sentResponse));
                $answerOK = isset($sentResponse['idSMS']);
error_log(__METHOD__.':$answerOK='.$answerOK.':sndSMS='.$sndOptSMS.':sndLAB='.$sndOptLab);

                $sms['attempts_number'] = $sms['attempts_number'] + 1;
                self::$_db->update(
                        'schedule_send',
                        array('attempts_number' => $sms['attempts_number']),
                        'id=' . $sms['id']);


                if ($answerOK) {
                    self::updateScheduleSend($sms, $sentResponse,
                                    $dateNow);

                    if ($sndOptSMS) {
                        self::decrementAccountCredit($sms);
                    } else if ($sndOptLab) {
                        self::decrementDescriptionCredit($sms);
                    }

                    // self::updateProgrammingToSent($sms);
                } else {
                    self::updateScheduleSend($sms, $sentResponse);
                }
            } else {
                error_log(__METHOD__
                    .":No credit:sms id=".$sms['id']);
                self::updateScheduleSend($sms, 'no credit');
                // se não tem crédito, cancela a Programação
                // self::updateProgrammingToSent($sms);
            }
        }
    }

    /**
     * Checa se o usuário da conta tem pelo menos um crédito de remédio.
     *
     * @param $sms
     * @return bool
     */
    private static function hasCreditFromLab($sms) {
        if (($sms['rcredit']) > 0) {
            return true;
        }
        return false;
    }

    private static function updateProgrammingToSent($sms) {
        self::$_db->update(
                'programming',
                array('in_concluded_send' => 'Y'),
                'id=' . $sms['programming_id']);
    }

    private static function updateProgrammingToExcluded($sms) {
        self::$_db->update(
                'programming',
                array(
                    'in_excluded' => '1'),
                'id=' . $sms['programming_id']);
    }

    private static function decrementAccountCredit(&$sms) {
        $sms['credit'] = $sms['credit'] - 1;

        $acc = Accounts::getAccountById($sms['id_account']);
        $acc->credit -= 1;
        $acc->saveCredit();
        error_log(__METHOD__
            . 'Descontado crédito da conta '.$sms['id_account']);
    }

    private static function decrementDescriptionCredit(&$sms) {
        error_log(__METHOD__
            . ':Desconta crédito da descricao '.$sms['id_description']);
        $sms['rcredit'] = $sms['rcredit'] - 1;

        try {
            Programming::descontaRcredit($sms['id_description'], $sms['rcredit']);
        } catch (Exception $ex) {
            error_log(__METHOD__.'::::EXCEPTION!');
            error_log($ex->__toString());
        }
        error_log(__METHOD__
            . ':Descontado crédito da descricao '.$sms['id_description']);
    }

    private static function updateScheduleSend($sms, $answer, $timeSent=null) {
        if (is_array($answer)) {
            $answer = implode('::', $answer);
        }

        $dados = array(
            'attempts_number' => $sms['attempts_number'],
            'answer' => $answer);

        if ($timeSent !== null) { // significa que não é de programação
            $dados['dt_send'] = $timeSent->get(Util::DB_DATETIME_FORMAT);
            $dados['in_concluded_send'] = 'Y'; // schedule_send
        }

        self::$_db->update(
                'schedule_send',
                $dados,
                'id=' . $sms['id']);
        error_log(__METHOD__.json_encode($dados));
    }

    /**
     * Pega um lista de SMS a enviar da tabela schedule_send e relações
     * que:
     * - opção de envio seja SMS ou LAB
     * - envio (no schedule_send) não esteja concluido
     * - conta esteja ativa
     * - provided_time seja entre agora e self::
     *
     *
     * @param string $nowTimeS data-hora de agora no formato myssql :p
     * @return <type>
     */
    private static function getListSMSToSend() {
        $nowTime = Zend_Date::now();
        $nowTimeS =
                $nowTime->get(Util::DB_DATETIME_FORMAT);

        $inicioTime = Util::cloneZendDate($nowTime)
                        ->subMinute(self::SEND_LOOKBEHIND_MINS);
        $inicioTimeS = '00:00:00';
        if ($inicioTime->isToday()) {
            $inicioTimeS = $inicioTime->get(Util::DB_DATETIME_FORMAT);
        }

        $sql = 'SELECT ss.id, ss.id_description, ss.attempts_number, '
                . ' (ss.dt_provided+ss.time) as provided_time, d.description, '
                . ' a.cell_phone, a.credit, ua.id_account, ua.in_send_option, '
                . ' a.id as account_id, p.id as programming_id, d.rcredit as rcredit '
                . 'FROM schedule_send ss, descriptions d, accounts a, '
                . ' programming p, user_account ua '
                . 'WHERE ss.id_description = d.id '
                . ' AND d.id_programming = p.id '
                . ' AND p.id_subscriber = ua.id_subscriber '
                . ' AND ua.id_account = a.id '
                . ' AND p.id_account = a.id '
                . ' AND (ss.dt_provided+ss.time) '
                . "    >= CAST('$inicioTimeS' AS DATETIME) "
                . ' AND (ss.dt_provided+ss.time) '
                . "    <= CAST('$nowTimeS' AS DATETIME) "
                . ' AND ss.in_concluded_send <> \'Y\' '
                . ' AND ua.in_send_option IN (2,3) ' // sms ou lab
                . ' AND a.is_active = 1';
        //error_log(__METHOD__.':SQL='.$sql);
        $result = self::$_db->fetchAll($sql);
        return $result;
    }

    function wsapp($data) {
        /*
         * Para debug.
          $mail = Util::getMailObject();
          $mail->setBodyHtml(var_export(
          $data,
          true))
          ->setFrom('suporte@lembrefacil.com.br', 'Suporte Lembre Fácil')
          ->addTo('mauro.martini@gmail.com', 'Mauro')
          ->setSubject('sem assunto')
          ->addHeader('Reply-To', 'suporte@lembrefacil.com.br')
          ->send();
         *
         */

        $return = '';
        $excluded = '';
        $descriptions = null;
        $excludedProgrammings = null;


        if (is_array($data)) {
            $userapp = Zend_Registry::get('config')->wsapp;
            $code = $data['code'];
            //$code = 1006009348;
            //print('oi'); exit();

            Accounts::markDownloadExecutedByCode($code);

            $isValidCode = self::$_db->fetchOne(
                            "SELECT a.id FROM accounts a "
                            . "WHERE a.code = '$code' AND a.in_excluded = 0");
            if (!empty($isValidCode)) {
                if ($data['username'] == $userapp->username
                        && $data['password'] == $userapp->password) {

                    $excludedProgrammings = self::$_db->fetchAll(
                                    "SELECT p.id FROM programming p, accounts a "
                                    . "WHERE p.in_excluded = 1 AND a.id = p.id_account "
                                    . "   AND a.code = $code");

                    $bigsql =
                            "SELECT a.name, a.code, p.id as id_programming, "
                            . "  d.id as id_description, d.description, d.dt_start, "
                            . "  d.dt_end, d.reminder, d.in_repetition, "
                            . "  d.in_frequency, d.in_monday, d.in_tuesday, "
                            . "  d.in_wednesday, d.in_thursday, d.in_friday, "
                            . "  d.in_saturday, d.in_sunday, d.during_days, "
                            . "  d.interrupt_days, d.interval_days, t.time, "
                            . "  m.day as month_day, y.month, y.day "
                            . " FROM accounts a, user_account ua, programming p, "
                            . "   descriptions d, times t, "
                            . "   ( SELECT descriptions.id, days.day "
                            . "     FROM descriptions LEFT JOIN days "
                            . "       ON descriptions.id=days.id_description "
                            . "   ) m, "
                            . "   ( SELECT descriptions.id, months.month, months.day "
                            . "     FROM descriptions LEFT JOIN months "
                            . "       ON descriptions.id=months.id_description "
                            . "    ) y "
                            . " WHERE a.code = '$code' AND p.in_excluded = 0 "
                            . "   AND ua.in_send_option = 1 "
                            . "   AND p.in_concluded_send <> 'Y' "
                            . "   AND a.id = ua.id_account AND a.id = p.id_account "
                            . "   AND p.id = d.id_programming "
                            . "   AND t.id_description = d.id "
                            . "   AND m.id = d.id AND y.id = d.id";

                    $descriptions = self::$_db->fetchAll($bigsql);
                    if (!empty($excludedProgrammings) || !empty($descriptions)) {
                        if (!empty($excludedProgrammings)) {
                            foreach ($excludedProgrammings as $excludedProgramming) {
                                $excluded .= $excludedProgramming['id'] . "#";
                            }
                            $return = $excluded;
                        }

                        if (!empty($descriptions)) {
                            $d = '';

                            foreach ($descriptions as $description) {
                                $description['day'] =
                                        !empty($description['month_day']) ? $description['month_day']
                                            : $description['day'];
                                $row = $this->makeARow($description);

                                $d .= $row;
                            }

                            $return .= $d;
                            //$return = Zend_XmlRpc_Value::getXmlRpcValue($descriptions);
                        }
                    } else {
                        $return = '@no_data@';
                    }
                } else {
                    $return = '@not_auth@';
                }
            } else {
                //Assinante inválido
                $return = '@not_auth@';
            }
        } else {
            $return = '';
        }

        if (!empty($descriptions)) {
            foreach ($descriptions as $description) {
                self::$_db->update(
                        'programming',
                        array('in_concluded_send' => 'Y'),
                        'id=' . $description['id_programming']);
            }
        }

        if (!empty($excludedProgrammings)) {
            foreach ($excludedProgrammings as $excludedProgramming) {
                self::$_db->update(
                        'programming',
                        array('in_excluded' => '2'),
                        'id=' . $excludedProgramming['id']);
            }
        }
        echo $return;
        exit();
    }

    private function makeARow($description) {
        /** @var Zend_Date */
        $dtStart = '';
        $sdtStart = '';
        if (!empty($description['dt_start'])) {
            $dtStart = new Zend_Date($description['dt_start'], 'pt_BR');
            $sdtStart = $dtStart->toString(Util::DATE_FORMAT);
        }
        /** @var Zend_Date */
        $dtEnd = '';
        $sdtEnd = '';
        if (empty($description['dt_end'])) {
            $dtEnd = $dtStart;
            $sdtEnd = $sdtStart;
        } else {
            $dtEnd = new Zend_Date($description['dt_end'], 'pt_BR');
            $sdtEnd = $dtEnd->toString(Util::DATE_FORMAT);
        }

        $time = $description['time'];
        $time = explode(':', $time);
        $time = $time[0] * 3600 + $time[1] * 60 + $time[2];

        $ret =
                $description['name'] . ";" .
                $description['code'] . ";" .
                $description['id_programming'] . ";" .
                $description['id_description'] . ";" .
                $description['description'] . ";" .
                $dtStart->getTimestamp() . ";" .
                $dtEnd->getTimestamp() . ";" .
                $description['reminder'] . ";" .
                $description['in_repetition'] . ";" .
                $description['in_frequency'] . ";" .
                $description['in_monday'] . ";" .
                $description['in_tuesday'] . ";" .
                $description['in_wednesday'] . ";" .
                $description['in_thursday'] . ";" .
                $description['in_friday'] . ";" .
                $description['in_saturday'] . ";" .
                $description['in_sunday'] . ";" .
                $description['during_days'] . ";" .
                $description['interrupt_days'] . ";" .
                $description['interval_days'] . ";" .
                $time . ";" .
                $description['day'] . ";" .
                $description['month'] . ";" .
                $dtStart . ";" .
                $dtEnd . ";" . "#";
        return $ret;
    }

    public static function monthReport() {
        /*
         * pelo que deu pra entender, ele espera o seguinte nas variáveis:
         * $monthly_report_date: a data de hoje
         * $dt_end: a data de ontem
         * $dt_start: a data de um mês atras
         */
        $monthly_report_date = Zend_Date::now();

        $start = Zend_Date::now();
        $start->setDay(1);
        $start = $start->subMonth(1);
        $month = Util::getNomeMes($start->get(Zend_Date::MONTH));

        $end = Zend_Date::now();
        $end->setDay(1);
        //$end->subMonth(1);
        // @todo este true deixa emitir relatorio em qualquer dia
        if ($monthly_report_date->get(Zend_Date::DAY_SHORT) == 1) {
            $sql = 'SELECT u.id, u.email, u.name FROM users u '
                    . 'WHERE u.id NOT IN '
                    . '(SELECT id_subscriber '
                    . 'FROM monthly_report WHERE date = \''
                    . $monthly_report_date . '\') '
                    . ' AND u.id_profile = '
                    . '(SELECT id FROM profiles WHERE label = \'assinante\') '
                    . ' AND u.in_excluded = 0 LIMIT 0, 10';
error_log(__METHOD__."SQL==$sql");
            $ids = self::$_db->fetchAll($sql);
error_log(__METHOD__.var_export($ids, true));

            foreach ($ids as $id) {
                $id_subscriber = $id['id'];
                $sql = 'SELECT id FROM monthly_report '
                        . 'WHERE date = \''
                        . $monthly_report_date->get(Util::DB_DATE_FORMAT)
                        . '\' '
                        . " AND id_subscriber = $id_subscriber";

                $sended_report = self::$_db->fetchOne($sql);

                // @todo esta próxima linha permite infinitos relatórios de
                // assinante
                // $sended_report = null;

                if (empty($sended_report)) {
                    // self::createPDF($id['id'], $start, $end);

                    $message = 'Prezado(a) ' . $id['name'] . ', <p>&nbsp;</p>'
                            . 'Segue anexo o relatório referente as suas '
                            . 'programações realizadas no sistema Lembre Fácil, '
                            . 'referentes ao mês de ' . $month . "<br/>\n
                        . 'Ficamos à disposição para eventuais dúvidas.";

                    $mail = Util::getMailObject();
                    $mail->setBodyHtml($message)
                            ->setFrom('suporte@lembrefacil.com.br',
                                    'Suporte Lembre Fácil')
                            ->addTo($id['email'], $id['email'])
                            ->addBcc('mauro+lfm@numera.com.br', 'mauro+lfm@numera.com.br')
                            ->setSubject('Programações de ' . $month)
                            ->addHeader('Reply-To', 'suporte@numera.com.br')
                            ->createAttachment(
                                    self::createPDF($id['id'], $start, $end),
                                    'application/pdf')
                                ->filename = 'programacoes_de_' . $month;

                    $mail->send();

                    self::$_db->insert(
                            'monthly_report',
                            array(
                                'date' => $monthly_report_date
                                        ->toString(Util::DB_DATE_FORMAT),
                                'id_subscriber' => $id['id']));
                }
            }
        }
        exit();
    }

    private static function createPDF($id, $start, $end) {
        $sendReports = new Programming();
        $valores = $sendReports->sendprogrammingReport(
                array('id', 'dt_register', 'in_send_option', 'name', 'cell_phone'),
                $id,
                $start,
                $end);

        $subscriber = new Users(array('id' => $id));
        $subscriber->read(array('name', 'email'));

        //Relátorio de Envio Individual

        require_once 'Zend/Pdf.php';
        $pdf = new Zend_Pdf();

        //Incluir uma Página
        $page = self::newPdfPage();

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setFont($fontb, 14);
        $page->drawText('Assinante:', 41, 700);

        $page->setFont($font, 12);
        $label = $subscriber->name . ' - ' . $subscriber->email;
        $page->drawText($label, 122, 700, 'UTF-8');

        $page->drawLine(30, 625, 570, 625);
        $page->drawLine(30, 675, 570, 675);
        $page->setFont($fontb, 14);
        $page->drawText('Data', 41, 650);
        $page->drawText('Hora', 110, 650);
        $page->drawText('Nome', 180, 650);
        $page->drawText('Celular', 406, 650);
        $page->drawText('Envio', 506, 650, 'UTF-8');

        $line = 600;
        $tableLine = 575;
        $page->setFont($font, 12);

        $key = '';

        foreach ($valores as $key => $valor) {
            $ddd = substr($valor['cell_phone'], 0, 2);
            $celular = substr($valor['cell_phone'], 2);
            $sendOption = $valor['in_send_option'] == 1 ? 'Aplicativo Java' : 'SMS';
            $dtRegister = new Zend_Date($valor['dt_register'], 'pt_BR');
            $sDtReg = $dtRegister->get(Util::DATE_FORMAT);
            $sTmReg = $dtRegister->get(Util::TIME_FORMAT);
            $array[$key] = array(
                $page->drawText($sDtReg, 41, $line),
                $page->drawText($sTmReg, 110, $line),
                $page->drawText(mb_substr($valor['name'], 0, 36), 180, $line, 'UTF-8'),
                $page->drawText('(' . $ddd . ')' . $celular, 406, $line),
                $page->drawText($sendOption, 506, $line),
                    //$page->drawLine(30, $tableLine, 570, $tableLine),
            );
            $line -= 25;
            $tableLine -= 25;
            if ($tableLine < 72) {
                //Criar novo pdf

                array_push($pdf->pages, $page);
                $page = self::newPdfPage();
                $page->drawLine(30, 725, 570, 725);
                $page->drawLine(30, 675, 570, 675);
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $page->setFont($fontb, 14);
                $page->drawText('Data', 41, 650);
                $page->drawText('Hora', 110, 650);
                $page->drawText('Nome', 180, 650);
                $page->drawText('Celular', 406, 650);
                $page->drawText('Envio', 506, 650, 'UTF-8');
                $line = 600;
                $tableLine = 575;
                $page->setFont($font, 12);
            }
        }

        $key = (!empty($key) || $key === 0) ? $key + 1 : 0;

        $page->setFont($fontb, 14);
        $page->drawText('Total de Programações: ', 41, $tableLine, 'UTF-8');
        $page->setFont($font, 13);
        $page->drawText($key, 210, $tableLine);
        array_push($pdf->pages, $page);

        
        
        $page = self::fazPaginaPdfCreditosComprados(
                array($id), array($label), $start, $end);

        array_push($pdf->pages, $page);
        

        //Salvar o PDF
        //        header('Content-type: application/pdf');
        //        header('Content-Disposition: attachment; filename="Arquivo.pdf"');

        return $pdf->render();
    }

    public static function newPdfPage() {

        //Incluir uma Página
        $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        $pageHeight = $page->getHeight();
        $pageWidth = $page->getWidth();

        //Estilo da Página
        $style = new Zend_Pdf_Style();
        $style->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $style->setLineWidth(2);
        $page->setStyle($style);

        //Incluir uma Imagem
        $imagem = Zend_Pdf_Image::imageWithPath(APPLICATION_PATH . '/../public/images/public/logo.png');
        $imageHeight = 35;
        $imageWidth = 157;
        $topPos = $pageHeight - 36;
        $leftPos = 36;
        $bottomPos = $topPos - $imageHeight;
        $rightPos = $leftPos + $imageWidth;
        $page->drawImage($imagem, $leftPos, $bottomPos, $rightPos, $topPos);

        $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
        $page->setLineColor($color1);
        $page->setLineWidth(0.5);

        return $page;
    }

    public static function delete($id) {
        if (empty($id)) {
            throw new Exception('Programação não identificada para remoção');
        }
        $idDescriptions = self::$_db->fetchAll(
                    "SELECT d.id FROM descriptions d WHERE id_programming = $id");
        foreach ($idDescriptions as $idDescription) {
            self::$_db->delete(
                    'schedule_send',
                    'in_concluded_send <> "Y" AND id_description = '
                        . $idDescription['id']);
        }
    }

    public static function fazPaginaPdfCreditosComprados($ids, $label, $dti=null, $dtf=null) {
        /////////// CREDITOS
        $page = self::newPdfPage();
        $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setLineColor($color1);
        $page->setLineWidth(0.5);
        $line = 600;
        $line2 = 700;
        $line3 = 665;

        $page->setFont($fontb, 11);
        $page->drawText('Assinante:', 41, $line2 + 20, 'UTF-8');

        $page->setFont($font, 11);
//error_log(__METHOD__.var_export($label, true));
        $page->drawText($label[0], 100,
                        $line2 + 20, 'UTF-8');

        $page->setFont($fontb, 12);
        $page->drawLine(30, $line2 - 15, 570, $line2 - 15);
        $page->drawText('Data', 41, $line3, 'UTF-8');
        $page->drawText('Hora', 120, $line3, 'UTF-8');
        $page->drawText('Nome', 210, $line3, 'UTF-8');
        $page->drawText('Celular', 360, $line3, 'UTF-8');
        $page->drawText('Quantidade', 465, $line3, 'UTF-8');
        $page->drawLine(30, $line3 - 10, 570, $line3 - 10);


        $totQty = 0;
        $contTotal = $cont1 = 0;
        foreach ($ids as $id) {
            // $subscr = Users::getUserById($id);
            $accs = Accounts::getAccountBySubscriber($id);
            foreach ($accs as $acc) {
                if ($acc->isOptJava()) {
                    continue;
                }
                $buys = SmsBuy::listByAccount($acc->id, $dti, $dtf);
                foreach ($buys as $buy) {
                    $dt_register = new Zend_Date($buy->dt_credit, 'pt_BR');

                    $page->setFont($font, 11);
                    $page->drawText(
                            $dt_register->get(Util::DATE_FORMAT), 41,
                                              $line + 25, "UTF-8");
                    $page->drawText(
                            $dt_register->get(Util::TIME_FORMAT), 120,
                                              $line + 25, "UTF-8");
                    $page->drawText(
                            mb_substr($acc->name, 0, 22), 210, $line + 25,
                                      'UTF-8');
                    $page->drawText(
                            Util::formatPhoneNumber($acc->cell_phone), 360,
                                                    $line + 25, "UTF-8");
                    $page->drawText(
                            $buy->qty, 465, $line + 25, "UTF-8");
                    $line -= 25;
                    $cont1 += 1;
                    $contTotal += 1;
                    $totQty += $buy->qty;
                }
            }
        }

        $page->setFont($fontb, 11);
        $page->drawText('Total de Créditos comprados: ' . $totQty, 41, $line2,
                        'UTF-8');
        $page->setFont($fontb, 11);
        $page->drawText($contTotal, 175, $line2 - 25, 'UTF-8');
        
        return $page;
    }

    public static function returnExpiredCredit() {
        $allocsCom = BoxAlloc::listUsable();
        /** @var BoxAlloc $cadaAlloc */
        foreach ($allocsCom as $cadaAlloc) {
            error_log(__METHOD__.  var_export($cadaAlloc, true));
            $remedy = $cadaAlloc->getRemedy();
            $dtAlloc = $cadaAlloc->getAllocTime();
            if (! $remedy->isValidSince($dtAlloc)) {
                $difa = $cadaAlloc->getCredit();
                $cadaAlloc->used = $cadaAlloc->qty;
                $cadaAlloc->save();
                
                $retCred = new CredRefund();
                $retCred->id_user = $remedy->id_owner;
                $retCred->qty = $difa*$remedy->qty;
                $retCred->save();
            }
        }
        
        
    }
}
