<?php

/**
 * Funções úteis estáticas
 *
 * @author mauro
 */
class Util {
    const DB_DATE_FORMAT = 'yyyy-MM-dd';
    const DB_TIME_FORMAT = 'HH:mm:ss';
    const DB_DATETIME_FORMAT = 'yyyy-MM-dd HH:mm:ss';
    const DATE_FORMAT = 'dd/MM/yyyy';
    const TIME_FORMAT = 'HH:mm';

    public static function sendEmailToAdministrator($subj, $msg) {
        $configMail = Zend_Registry::get('config')->mail;

        $mail = Util::getMailObject();
        $mail->setBodyText($msg)
            ->setFrom('lembrefacil@floripa.br', 'Suporte Lembre Fácil')
            ->addTo($configMail->receive)
            ->setSubject($subj)
            ->addHeader('Reply-To', 'suporte@lembrefacil.com.br')
            ->send();
    }

    public static function getMailObject() {
        $contactMail = Zend_Registry::get('config')->mail;
        $config = array(
            'auth' => 'login',
            'username' => $contactMail->send->username,
            'password' => $contactMail->send->password,
            'ssl' => 'ssl',
            'port' => 465
        );

        $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);
        Zend_Mail::setDefaultTransport($transport);
                
        return new Zend_Mail('UTF-8');
    }

    /**
     * Acha objetos numa lista pela propriedade.
     * 
     * @param <array> $arr a lista de objetos
     * @param <string> $prop o nome da propriedade
     * @param <mixed> $val o valor buscado
     * @return <object> um array com os objs achados (array vazio se nao
     *      encontrou nada)
     */
    public static function findObjectByProperty($arr, $prop, $val) {
        $ret = array();
        foreach ($arr as $each) {
            if ($each->$prop == $val) {
                $ret[] = $each;
            }
        }
        return $ret;
    }

    public static function inverteData($data, $sep='/') {
        $arr = split($sep, $data);
        if (count($arr) != 3) {
            throw new Exception('invalid date to invert');
        }
        $arr = array_reverse($arr);
        return join('-', $arr);
    }

    public static function cleanPhoneNumber($num) {
        return str_replace(array(')', '('), '', $num);
    }

    public static function formatPhoneNumber($num) {
        $res = '';
        if (strlen($num) > 8) {
            $res = '('
                . substr($num, 0, 2)
                . ')'
                . substr($num, 2);
        } else {
            $res = $num;
        }
        return $res;
    }

    /**
     * @param string|int $numMes o número do mes (1..12)
     * @return string o nome do mes em portugues sem acento
     * (janeiro, fevereiro, marco)
     */
    public static function getNomeMes($numMes) {
        switch ($numMes) {
            case 1:
                return 'janeiro';
            case 2:
                return 'fevereiro';
            case 3:
                return 'marco';
            case 4:
                return 'abril';
            case 5:
                return 'maio';
            case 6:
                return 'junho';
            case 7:
                return 'julho';
            case 8:
                return 'agosto';
            case 9:
                return 'setembro';
            case 10:
                return 'outubro';
            case 11:
                return 'novembro';
            case 12:
                return 'dezembro';
            default:
                return '';
        }
    }

    /**
     *
     * @param Zend_Date $dt
     * @return Zend_Date outra instância com o mesmo valor de $dt
     */
    public static function cloneZendDate(Zend_Date $dt) {
        return clone $dt;
        //return new Zend_Date($dt->getTimestamp());
    }

    /**
     * Pica um texto em linhas tentando respeitar os espaços.
     *
     * @param string $texto o texto a ser picado
     * @param int $maxlen tamanho máximo da linha em caracteres
     */
    public static function quebraLinhas($texto, $maxlen) {
        $linhas = array();
        while (strlen($texto) > $maxlen) {
            $posEspaco = strrpos($texto, ' ', $maxlen - strlen($texto));
            $posCorte = $posEspaco ? min($posEspaco, $maxlen) : $maxlen;
            $parte = substr($texto, 0, $posCorte);
            $comespaco = 0;
            if (substr($texto, $posCorte, 1) == ' ') $comespaco = 1;
            $texto = substr($texto, $posCorte+$comespaco);
            $linhas[] = $parte;
        }
        if (strlen($texto) > 0) {
            $linhas[] = $texto;
        }

        return $linhas;
    }
    
    
    /**
     * copiado de http://php.net/manual/en/function.strtr.php
     * 
     * @param type $string
     * @return type 
     */
    public static function tiraAcentos ($string) {
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝàáâãäåæçèéêëìíîïñòóôõöøùúûýýÿ'; 
        $b = 'AAAAAAACEEEEIIIIDNOOOOOOUUUUYaaaaaaaceeeeiiiinoooooouuuyyy'; 
        $string = utf8_decode($string);
        $string = strtr($string, utf8_decode($a), $b);
        $string = strtolower($string);
        return utf8_encode($string);
    } 

    public static function errorLog($arg, $msg='') {
        $stack = debug_backtrace();
        $pre = 
            $stack[1]['class']
            . $stack[1]['type']
            . $stack[1]['function']
            . '#' . $stack[0]['line']
            . ' >'
            . $msg
            . ': ';

        error_log($pre.var_export($arg, true));
    }
}
