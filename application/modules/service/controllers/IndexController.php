<?php

/**
 * HandsOn CMS Framework
 *
 * LICENÇA
 *
 * Este arquivo-fonte é propriedade da Númera Soluções e Sistemas Ltda.,
 * empresa brasileira inscrita no CNPJ/MF sob nº 08.179.010/0001-48.
 * A reprodução parcial ou total do conteúdo é expressamente vedada, conforme
 * descrição detalhada da licença, disponível no documento "docs/license.txt".
 * Se o arquivo estiver ausente, por favor entre em contato pelo email
 * license@numera.com.br para que possamos enviar uma cópia imediatamente.
 *
 * @copyright  Copyright (c) 2008 Númera Soluções e Sistemas Ltda. (http://www.numera.com.br)
 * @license    http://www.numera.com.br/license/handson     HandsOn 1.0 License
 * @version    $Id$
 */
class Service_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->acl->allow(null);
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function getprogrammednotificationAction() {     // SERVER SIDE CODE 
        $this->_helper->viewRenderer->setNoRender(true);
        $username = null;
        $password =null ;
        $form = new LoginForm();

        if ($this->getRequest()->getMethod() == 'GET') {

            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];

// most other servers
            } else {
                
                if (strpos(strtolower($this->getRequest()->getHeader('HTTP_AUTHORIZATION')), 'basic') === 0)
                    list($username, $password) = explode(':', base64_decode(substr($this->getRequest()->getHeader('HTTP_AUTHORIZATION'), 6)));
            }

            $data['email'] = $username;
            $data['password'] = $password;

            if (is_null($username)) {

                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Authentication failed';
            } else {
                if ($form->isValid($data)) {
                    $values = $form->getValues();
                    $authAdapter = new Zend_Auth_Adapter_DbTable(
                            Zend_Db_Table::getDefaultAdapter());

                    //$treatment = 'in_excluded = 0 AND SHA1(?)';
                    $treatment = 'SHA1(?)';
                    $authAdapter->setTableName('users')
                            ->setIdentityColumn('email')
                            ->setCredentialColumn('password')
                            ->setIdentity($values['email'])
                            ->setCredential($values['password'])
                            ->setCredentialTreatment($treatment);

                    $select = $authAdapter->getDbSelect();
                    $select->where('in_excluded = 0');

                    $auth = Zend_Auth::getInstance();

                    if ($auth->authenticate($authAdapter)->isValid()) {         // Checking Authentication
                        
                         $userData = $authAdapter->getResultRowObject(
                                    array('id', 'id_profile', 'name', 'email'));
//                    $auth->getStorage()->write($userData);
//                        var_dump($userData);
                        $acc = new Accounts();
                        $prog = new Programming();
                        $rem = new Remedy();
                        $usr = new Users();
                        if (isset($_GET['userPhoneNumber'])) {
                            $cell_number = $_GET['userPhoneNumber'];

// var_dump($usr);
                            $str = '<loginResult>';
                            $det = $acc->getuseracc($cell_number,$userData->id);
                            $str .= '<Schedule>';
                            $arr_final = array();
                            
                            if (!empty($det)) {
                                foreach ($det as $value) {

                                    $data = $prog->getDataForXml($value->id, array('id', 'id_subscriber', 'description', 'dt_start', 'dt_end', 'dt_register', 'dt_exclusion', 'reminder', 'in_repetition', 'in_frequency', 'in_sunday', 'in_monday', 'in_tuesday', 'in_wednesday', 'in_thurday', 'in_friday', 'in_saturday', 'id_remedy', 'interval_days','time','qtd_new'));
                                    if (!empty($data)) {
                                        $i = 0;
                                        foreach ($data as $val) {
                                            
                                            $str .= '<tSchedule>';
                                            $str .= '<idProgramming>' . $val['id'] . '</idProgramming>';
                                            $str .= '<description>' . $val['description'] . '</description>';
                                            $str .= '<dtStart>' . $val['dt_start'] . '</dtStart>';
                                            $str .= '<dtEnd>' . $val['dt_end'] . '</dtEnd>';
                                            $str .= '<dtRegister>' . $val['dt_register'] . '</dtRegister>';
                                            $str .= '<dtExclusion>' . $val['dt_exclusion'] . '</dtExclusion>';
                                            $str .= '<reminder>' . $val['reminder'] . '</reminder>';
                                            if($val['time'] != '')
                                            $str .= '<intervalHours>'.$val['time'].'</intervalHours>';
                                            
                                            else
                                            $str .= '<intervalHours>0</intervalHours>';
                                            
                                            $str .= '<repetition>' . $val['in_repetition'] . '</repetition>';
                                            $str .= '<frequency>' . $val['in_frequency'] . '</frequency>';
                                            $str .= '<qtdeWarning> </qtdeWarning>';
                                            $str .= '<inMonday>' . $val['in_monday'] . '</inMonday>';
                                            $str .= '<inTuesday>' . $val['in_tuesday'] . '</inTuesday>';
                                            $str .= '<inWednesday>' . $val['in_wednesday'] . '</inWednesday>';
                                            $str .= '<inThursday>' . $val['in_thursday'] . '</inThursday>';
                                            $str .= '<inFriday>' . $val['in_friday'] . '</inFriday>';
                                            $str .= '<inSaturday>' . $val['in_saturday'] . '</inSaturday>';
                                            $str .= '<inSunday>' . $val['in_sunday'] . '</inSunday>';



                                            $remedy_data = $rem->get(array('name', 'descr', 'qty', 'id_owner'), array('filterColumn' => 'id', 'filter' => $val['id_remedy']));
                                            if (!empty($remedy_data)) {
                                                $j = 0;
                                                foreach ($remedy_data as $val1) {
                                                    $str .= '<tRemedy>';
                                                    $str .= '<name>' . $val1['name'] . '</name>';
                                                    $str .= '<description>' . $val1['descr'] . '</description>';
//                                                    $str .= '<qtde>' . $val1['qty'] . '</qtde>';
                                                    $str .= '<qtde>' . $val['qtd_new'] . '</qtde>';
                                                    $str .= '<tOwner>';



                                                    $remedy_data[$j]['tOwner'] = $usr->get(array('name', 'email'), array('filterColumn' => 'id_owner', 'filter' => $val1['id_owner']));
                                                    unset($remedy_data[$j]['id_owner']);

                                                    $str .= '<name>' . $remedy_data[$j]['tOwner'][0]['name'] . '</name>';
                                                    $str .= '<email>' . $remedy_data[$j]['tOwner'][0]['email'] . '</email>';
                                                    $j++;
                                                    $str .= '</tOwner>';
                                                    $str .= '</tRemedy>';
                                                }


                                                $data[$i]['tRemedy'] = $remedy_data;
                                            } else {
                                                $data[$i]['tRemedy'] = array();
                                            }

                                            $i++;

                                            $str .= '</tSchedule>';
                                        }

                                        $arr_final['Schedule'][]['tSchedule'] = $data;
                                    } else {
                                        $arr_final['Schedule'][]['tSchedule'] = array();
                                    }
                                }
                                $str .= '</Schedule>';
                                $str .= '</loginResult>';
                                $final['loginResult'] = $arr_final;
                            } else {
                                $final['loginResult'] = array();
                            }
                            header('Content-type: application/xml');
                            echo $str;
                            die;
                        } else {
                            echo 'Please send userPhoneNumber';
                        }
                    } else {
                        Zend_Auth::getInstance()->clearIdentity();
                        echo 'Email or Password is incorrect';
                    }
                } else {
                    Zend_Auth::getInstance()->clearIdentity();
                    echo 'Please send valid authentication parameters';
                }
            }
        }
    }

}
