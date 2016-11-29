<?php

//ini_set('display_errors', '0');
//error_reporting(E_ALL ^ E_STRICT);


include_once "NuSOAP/nusoap.php";
/**
 * Singleton para conversar com o broker Vexx. Implementa o protocolo.
 *
 * @author mauro
 */
class SMSBrokerVexx {
    const SEND_SMS_AS_MAIL = 'mauro.martini+lf@gmail.com';
    private static $SERVICE_URL =
        'http://www.vexxmobile.com.br/servicos/servicos.php?wsdl';
        //'http://www.vexxmobile.com.br/servicos/servicos.php?WSDL';
    private static $AUTH_LOGIN = 'vendas@lembrefacil.com.br';
    private static $AUTH_PASSWORD = '937752935';

    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
    }

    public function sendSingleSMSNoWait($phone, $msg) {
        error_log('SMSBrokerVexx:sendSingleSMSNoWait:phone="'
                .$phone.'":msg="'.$msg.'"');

        $params = array(
            'usuario' => self::$AUTH_LOGIN,
            'senha' => self::$AUTH_PASSWORD,
            'celular' => $phone,
            'mensagem' => $msg
        );



        $resultado = null;
        try {
            if (self::SEND_SMS_AS_MAIL === false) {
                $clienteSoap = new nusoap_client(self::$SERVICE_URL, true);
                $resultado = $clienteSoap->call('enviarTorpedoVexx', $params);

                error_log('SMSBrokerVexx:sendSingleSMSNoWait:resultado='.$resultado);

            } else {
                $id = date('mdHisu');
                $resultado = "mail enviado, id=$id";

                $message = 'params para "enviarTorpedoVexx"'.var_export($params, true);
                Util::sendEmailToAdministrator(
                        'SMS Enviado '.$id,
                        $message);
}
            return (array(
                'result' => $resultado,
                'idSMS' => $this->parseInt($resultado),
                'pending' => true
            ));
        } catch (Exception $ex) {
            echo "Erro ao acessar o WeBSERVICE<br />Erro: ";
            echo var_export($resultado, true);
            echo "Erro ao acessar o WEBSERVICE<br />Erro: <pre>";
            echo var_export($ex, true);
            error_log('SoapClient::ERRO=' . var_export($ex, true));
            return false;
        }
    }

    public function getSMSStatus($idSMS) {
        error_log('SMSBrokerVexx:getSMSStatus:idSMS="'
                .$idSMS.'"');

        $params = array(
                //'usuario' => self::$AUTH_LOGIN,
                //'senha' => self::$AUTH_PASSWORD,
                //'idmensagem' => $idSMS
                self::$AUTH_LOGIN,
                self::$AUTH_PASSWORD,
                $idSMS
        );

        try {
            $resultado = '';
            if (self::SEND_SMS_AS_MAIL === false) {
                $clienteSoap = new nusoap_client(self::$SERVICE_URL, true);
                $resultado = $clienteSoap->call(
                    'consultarStatusTorpedoVexx',
                    $params
                );
            } else {
                $resultado = "OK $idSMS";
                $message = 'params para "consultarStatusTorpedoVexx"'.var_export($params, true);
                Util::sendEmailToAdministrator(
                        'SMS Verificado '.$idSMS,
                        $message);
            }
            error_log('SMSBrokerVexx:getSMSStatus:resultado="'
                    .$resultado.'"');

            return $resultado;
        } catch (Exception $ex) {
            error_log('SMSBrokerVexx:getSMSStatus:exception="'
                    .var_export($ex, true).'"');
            echo "Erro ao acessar o WebSERVICE - Erro: ".$ex;
        }
    }

    private function parseInt($string) {
    //	return intval($string);
        if(preg_match('/(\d+)/', $string, $array)) {
            return $array[1];
        } else {
            return 0;
        }
    }

//    private function sendSMS_batch($phone, $msg) {
//        /*
//         * Exemplo de utilização com o webservice Vexx para o método
//         * enviarTorpedosBatchVexx.
//         */
//        $clienteSoap = new soapclient(self::$SERVICE_URL);
//
//        var_dump('1 Erro', $clienteSoap->getError());
//
//        $resultado = $clienteSoap->call(
//            'enviarTorpedosBatchVexx',
//            array(
//                'usuario' => self::$AUTH_LOGIN,
//                'senha' => self::$AUTH_PASSWORD,
//                'arquivoxml' => $this->makeXML($phone, $msg)
//            ));
//        if($clienteSoap->fault) {
//          echo "Erro ao acessar o WEBSERVICE<br />Erro: ".$clienteSoap->faultstring;
//          var_dump('2 fault', $clienteSoap);
//        } else {
//          echo "Retorno: ".$resultado;
//        }
//    }

    private function makeXML($phone, $msg) {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $vexx = $doc->createElement('vexx');
        $torp = $doc->createElement('torpedo');
        $cell = $doc->createElement('celular');
        $cell->appendChild($doc->createTextNode($phone));
        $mesg = $doc->createElement('mensagem');
        $mesg->appendChild($doc->createTextNode($msg));
        $vexx->appendChild($torp);
        $torp->appendChild($cell);
        $torp->appendChild($mesg);

        $ret = $doc->saveXML();
        print "\n\n--------------\n$ret\n--------------\n\n";
        return $ret;
    }
}
?>
