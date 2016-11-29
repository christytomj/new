<?php

//ini_set('display_errors', '0');
//error_reporting(E_ALL ^ E_STRICT);


include_once "NuSOAP/nusoap.php";

/**
 * Singleton para conversar com o broker Vexx. Implementa o protocolo.
 *
 * @author mauro
 */
class SMSBrokerComunika {
    const SEND_SMS_AS_MAIL = false; //'mauro.martini+lf@gmail.com';

    private $SERVICE_URL =
            'http://webservice.cgi2sms.com.br/axis/services/VolaSDK?WSDL';
    private $AUTH_LOGIN = 'mauromartini';
    private $AUTH_PASSWORD = 'mauromartini';
    private static $instance = null;

    const SOAPCMD_SEND = 'sendMessage';
    const SOAPCMD_STATUS = 'getMessageStatus';

    /**
     *
     * @return SMSBrokerComunika a instancia Singleton.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $smsCfg = Zend_Registry::get('config')->smsbroker;
        $this->AUTH_LOGIN = $smsCfg->username;
        $this->AUTH_PASSWORD = $smsCfg->password;
        $this->SERVICE_URL = $smsCfg->url;
    }

    /**
     * Manda um SMS ara o broker sem esperar resposta.
     *
     * @param string $phone número de destino com 55XXNNNNNNNN
     * @param string $msg a mensagem
     * @param string $smsid o id para verificação do SMS
     * @return array ('result', 'idSMS', 'pending')
     */
    public function sendSingleSMSNoWait($phone, $msg, $smsid) {
        $msg = $this->desacentua($msg);
        error_log('SMSBrokerComunika:SendSms:phone="'
                . $phone . '":msg="' . $msg . '":id='.$smsid);


        $resultado = null;
        try {
            if (self::SEND_SMS_AS_MAIL === false) {
                $resultado = $this->soapSendMessage($phone, $msg, $smsid);
            } else {
                $id = date('mdHisu');
                $resultado = "mail enviado, id=$id";

                $message =
                        'params para "SendSms"'
                        . var_export(
                                array($phone, $msg, $smsid),
                                true);
                Util::sendEmailToAdministrator(
                                'SMS Enviado ' . $id,
                                $message);
            }

            $ret = array(
                'result' => $resultado,
                'idSMS' => $smsid,
                'pending' => true
            );
            error_log(__METHOD__.':retorno:'.var_export($ret, true));
            return $ret;
        } catch (Exception $ex) {
            error_log("Erro ao acessar o WeBSERVICE<br />Erro: ");
            error_log(var_export($resultado, true));
            error_log("Erro ao acessar o WEBSERVICE<br />Erro: <pre>");
            error_log(var_export($ex, true));
            error_log('SoapClient::ERRO=' . var_export($ex, true));
            return false;
        }
    }

    /**
     *
     * @param string $idSMS identificação do SMS
     * @return string o retorno do broker...
     */
    public function getSMSStatus($idSMS) {
        error_log('SMSBrokerComunika:GetSmsStatus:idSMS="'
                . $idSMS . '"');

        try {
            $resultado = '';
            if (self::SEND_SMS_AS_MAIL === false) {
                $resultado = $this->soapCheckMessage($idSMS);
            } else {
                $resultado = "OK $idSMS";
                $message = 'params para "getMessageStatus"'
                        . var_export(array($idSMS), true);
                Util::sendEmailToAdministrator(
                                'SMS Verificado ' . $idSMS,
                                $message);
            }
            error_log('SMSBrokerComunika:getSMSStatus:resultado="'
                    . var_export($resultado, true) . '"');

            return $resultado;
        } catch (Exception $ex) {
            error_log('SMSBrokerComunika:getSMSStatus:exception="'
                    . var_export($ex, true) . '"');
            error_log("Erro ao acessar o WebSERVICE - Erro: " . $ex);
        }
    }

    /**
     * manda a mensagem para SMS por soap.
     *
     * @param string $phone numero do fone
     * @param string $msg a mensagem
     * @param string $smsid o id do SMS para verificação
     */
    private function soapSendMessage($phone, $msg, $smsid) {
        $params = $this->initialSoapData();

        $params['target'] = $phone;
        $params['body'] = $msg;
        $params['ID'] = 'a' . $smsid;

        $resultado = $this->doSoap(
                $this->SERVICE_URL,
                self::SOAPCMD_SEND,
                $params);

        return $resultado;
    }

    /**
     * Faz a chamada Soap e joga exceção se der erro
     *
     * @param string $url a url do serviço
     * @param string $cmd o comando
     * @param array() $prm os parametros da chamada
     * @return o resultado
     */
    private function doSoap($url, $cmd, $prm) {
        // cria conexao
        $clienteSoap = new nusoap_client($url);

        $this->checkClientSoapError($clienteSoap);

        // chama o método
        $resultado = $clienteSoap->call($cmd, $prm);

        if ($clienteSoap->fault) {
            $mm = 'NuSoap fault' . print_r($resultado, true);
            error_log($mm);
            throw new Exception($mm);
        } else {
            $this->checkClientSoapError($clienteSoap);

            error_log(__METHOD__ . 'Result=' . print_r($resultado, true));
        }

        return $resultado;
    }

    /**
     * Checa por um erro de Soap, mostra no error_log e joga exceção
     *
     * @param <type> $clienteNuSoap
     */
    private function checkClientSoapError($clienteNuSoap) {
        $err = $clienteNuSoap->getError();
        if ($err) {
            $mm = 'NuSoap Constructor error:' . $err;
            error_log($mm);
            throw new Exception('NuSoap Constructor error:' . $err);
        }


    }

    /**
     * verifica o status de um sms enviado.
     *
     * @param string $idSMS identificação do SMS
     */
    private function soapCheckMessage($idSMS) {
        $params = $this->initialSoapData();
        $params = array(
            'username' => 'mauromartini',
            'password' => 'mauromartini',
        );

        $params['ids'] = array('a' . $idSMS);

        $clienteSoap = new nusoap_client($this->SERVICE_URL);

        $resultado = '';
        try {
            $resultado = $clienteSoap->call(
                            self::SOAPCMD_STATUS,
                            $params
            );
            if ($clienteSoap->fault) {
                error_log(print_r($clienteSoap, true));
            }
            $err = $clienteSoap->getError();
            if ($err) {
                error_log('soap error' . $err);
            }
        } catch (Exception $ex) {
            error_log(print_r($ex, true));
        }

        error_log('SMSBrokerComunika:GetSmsStatus:vv="'
                . var_export($resultado, true) . '"');

        return $resultado;
    }

    /**
     * Cria os dados comuns do array de parãmetros do serviço SOAP
     *
     * @return array com os parametros basicos do Soap
     */
    private function initialSoapData() {
        $params = array(
            'username' => $this->AUTH_LOGIN,
            'password' => $this->AUTH_PASSWORD,
            'testMode' => false,
            'sender' => '',
                //'schedule' => Zend_Date::now()->get(Util::DB_DATETIME_FORMAT),
        );

        return $params;
    }

    private function parseInt($string) {
        //	return intval($string);
        if (preg_match('/(\d+)/', $string, $array)) {
            return $array[1];
        } else {
            return 0;
        }
    }

    private function sendSMS_batch($phone, $msg) {
        throw new Exception('Not implemented');
    }

    private function desacentua($text) {
        $text = str_replace(
                        array('á', 'â', 'ã', 'à'), 'a', $text);
        $text = str_replace(
                        array('Á', 'Â', 'Ã', 'À'), 'A', $text);
        $text = str_replace(
                        array('é', 'ê'), 'e', $text);
        $text = str_replace(
                        array('É', 'Ê'), 'E', $text);
        $text = str_replace(
                        array('í', 'ì'), 'i', $text);
        $text = str_replace(
                        array('Í', 'Ì'), 'I', $text);
        $text = str_replace(
                        array('ó', 'ô'), 'o', $text);
        $text = str_replace(
                        array('Ó', 'Ô'), 'O', $text);
        $text = str_replace(
                        array('ú', 'ü'), 'u', $text);
        $text = str_replace(
                        array('Ú', 'Ü'), 'U', $text);
        $text = str_replace(
                        array('ñ'), 'n', $text);
        $text = str_replace(
                        array('Ñ'), 'N', $text);
        return $text;
    }

}

?>
