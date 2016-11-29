<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SMSSender
 *
 * @author mauro
 */
class SMSSender extends HandsOn_BasicModel {
    const SMS_STATUS_PENDING = 0;
    const SMS_STATUS_SENT = 1;
    const SMS_STATUS_ERROR = 2;

    /**
     * Manda um SMS e salva na tabela 'sms'
     *
     * @param <type> $opts
     * @return <type>
     */
    public static function sendSMS($opts) {
        error_log(__METHOD__.json_encode($opts));
        if (self::isSMSAlreadySent($opts)) {
            return;
        }
        if (isset($opts['provided_time']) AND ! self::checkDateRange($opts['provided_time'])) {
            return array('out of time range');
        }
        $phone = self::acertaNumeroPhone($opts['phone']);
        $message = $opts['message'];

        $idSMS = self::saveSMSSent($opts);

        /** @var SMSBrokerComunika $broker */
        $broker = SMSBrokerComunika::getInstance();

        $response = $broker->sendSingleSMSNoWait($phone, $message, $idSMS);
        error_log(__METHOD__.':retornou:'.json_encode($response));
        self::updateSMSresponse($idSMS, $response['result']);

        return $response;
    }

    private static function checkDateRange($datetime) {
        $RANGE = 60 * 20; // 20 min máx atraso ou adianto.
        $dt = new Zend_Date($datetime, Zend_Date::ISO_8601);
        $now = Zend_Date::now();

        $diff = $dt->getTimestamp() - $now->getTimestamp();
        error_log(__METHOD__
                . '::dado:' . $dt->get(Util::DB_DATETIME_FORMAT) . "(veio de $datetime)"
                . '; now:' . $now->get(Util::DB_DATETIME_FORMAT)
                . '; difa:' . abs($diff)
        );
        if (abs($diff) > $RANGE) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * Remove tudo que não for númérico e prependa um '55' se não tiver.
     * @param string $phone o numero do telefone
     * @return string o numero do telefone normatizado
     */
    private static function acertaNumeroPhone(&$phone) {
        $phone = preg_replace('/[^\d]/', '', $phone);
        if (substr("$phone", 0, 2) != '55') {
            $phone = '55' . $phone;
        }
        return $phone;
    }

    /**
     * salva o sms enviado na tabela 'sms'
     *
     * @param <type> $opts
     * @param string $response resposta do envio SMS
     * @return o id do sms salvo (pk da tabela)
     */
    private static function saveSMSSent($opts, $response=null) {
        $provided_time = isset($opts['provided_time'])
            ? $opts['provided_time']
            : null;
        $id_programming = isset($opts['id_programming'])
            ? $opts['id_programming']
            : null;

        $smst = new Sms();

        $dados = array(
            'destination_number' => $opts['phone'],
            'description' => $opts['message'],
            'send_time' => Zend_Date::now()->toString(Util::DB_DATETIME_FORMAT),
            'answer' => empty($response) ? '-sem resposta do broker-' : $response,
            'provided_time' => $provided_time,
            'id_programming' => $id_programming,
            'status' => self::SMS_STATUS_PENDING,
            'idSMS' => ''
        );
        $pk = $smst->insert($dados);

        $where = $smst->getAdapter()->quoteInto('id = ?', $pk);
        $smst->update(
                array('idSMS' => $pk),
                $where);

        return $pk;
    }

    /**
     * atualiza uma resposta de SMS do broker (salva a resposta na base)
     *
     * @param <type> $opts
     * @param string $response resposta do envio SMS
     * @return quantas colunas atualizadas (deve ser 1 sempre)
     */
    private static function updateSMSresponse($idsms, $response) {
        $resp = is_array($response)
            ? implode(', ', $response)
            : $response;
        $smst = new Sms();

        $where = $smst->getAdapter()->quoteInto('id = ?', $idsms);
        $ret = $smst->update(
                array('answer' => $resp),
                $where);

        return $ret;
    }

    /**
     * checa o status do sms junto ao broker, salva na tabla 'sms' o status novo
     */
    public static function checkSMSStatus() {
        error_log(__METHOD__);
        $list = self::listPendingSMSStatus();
        $broker = SMSBrokerComunika::getInstance();
        foreach ($list as $easms) {
            if ($easms['idSMS']) {
                error_log('SMS:checkSMSStatus:id='.$easms['idSMS']);
                $newstatus = $broker->getSMSStatus($easms['idSMS']);
                if (self::isStatusOk($newstatus)) {
                    self::updateSMSStatus($easms['id'], 1);
                }
            }
        }
    }

    /**
     * Verifica se um status de sms (retorno do broker para check de envio)
     * pode ser considerada ok.
     * Existem mais de uma mensagem de OK.
     *
     * @param string $status
     * $return boolean
     */
    public static function isStatusOk($status) {
        return true;
            //$status == 'Entregue pela Operadora'
            //|| $status == 'Mensagem Entregue';
    }

    /**
     * salva o novo status na tabela sms
     * @param int $idSMS
     * @param int $st
     */
    private static function updateSMSStatus($idSMS, $st) {
        self::$_db->update(
            'sms',
            array('status'=>$st),
            'id='.$idSMS
        );
    }

    /**
     * 
     * @param array $sms
     * @return boolean se ja tem, retorna true.
     */
    public static function isSMSAlreadySent($sms) {
        if (!isset($sms['cell_phone']) || !isset($sms['provided_time']) || !isset($sms['description'])) {
            return false;
        }
        $c = $sms['cell_phone'];
        $pt = $sms['provided_time'];
        $desc = $sms['description'];
        $sql = sprintf("SELECT id FROM sms "
                    . "WHERE provided_time=%s AND destination_number=%s "
                    . "AND description=%s",
                    self::$_db->quote($pt),
                    self::$_db->quote($c),
                    self::$_db->quote($desc)
        );

        $sended = self::$_db->fetchAll($sql);

        return (! empty($sended));
    }

    public static function listPendingSMSStatus() {
        $sql = "SELECT * FROM sms  WHERE status = 0";

        $pendingList = self::$_db->fetchAll($sql);

        return ($pendingList);
    }

}
