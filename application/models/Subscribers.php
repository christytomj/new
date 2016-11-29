<?php

require_once 'Zend/Mail.php';

class Subscribers extends HandsOn_BasicModel {

    protected $_data = array(
        'id'              => null,
        'id_profile'      => null,
        'code'            => null,
        'dateModification'=> null,
        'name'            => null,
        'email'           => null,
        'password'        => null,
        'password_link'   => null,
        'dt_register'     => null,
        'in_excluded'     => null,
        'id_city'         => null,
    );

    protected static $_allowedData = array(
        'id',
        'id_profile',
        'code',
        'name',
        'email',
        'password',
        'password_link',
        'dt_register',
        'in_excluded',
        'id_city',
    );

    protected static $_dataSource = array(
        'id'               => 'u.id',
        'id_profile'       => 'u.id_profile',
        'code'             => 'u.code',
        'name'             => 'u.name',
        'email'            => 'u.email',
        'password'         => 'u.password',
        'password_link'    => 'u.password_link',
        'dt_register'      => 'u.dt_register',
        'in_excluded'      => 'u.in_excluded',
        'id_city'          => 'u.id_city',
    );

    public function get($data, $options = null, $report = null, $filter = null) {
    //print_r($filter);
    //exit();
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }
        $select = $from = $where = $order = $limit = '';
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select) 
                    ? self::$_dataSource[$name]
                    : ',' . self::$_dataSource[$name];
        }

        if(!empty($report)) {
            $select = explode(",", $select);
            $select = "DISTINCT ($select[0]), $select[1], $select[2]";
            //            print_r($select);
            //            exit();
            $from = 'users u, profiles pfl, programming p';
            $where = 'u.in_excluded = 0 AND u.id_profile = pfl.id AND pfl.label= "assinante"';
            if (!empty($filter)) {
                if(!empty($filter['name'])) {
                    $where .= sprintf(
                            " AND u.name LIKE %s",
                            self::$_db->quote('%%' . $filter['name'] . '%%')
                    );
                }
            }
        } else {
            $from = 'users u, profiles p';
            $where = sprintf(
                'u.in_excluded = 0 AND u.id_profile = p.id '
                . 'AND p.label = "%s"',
                Users::PROFILE_SUBSCRIBER
            );
            if (!empty($options['filterColumn']) && !empty($options['filter'])) {
                $where .= sprintf(
                    " AND %s LIKE %s",
                    $options['filterColumn'],
                    self::$_db->quote('%%' . $options['filter'] . '%%')
                );
            }
        }

        if (!empty($options['sortColumn'])) {
            $column = ($options['sortColumn'] == 'id')
                    ? 'u.id' : self::$_dataSource[$options['sortColumn']];
            $order = 'ORDER BY ' . $column . $options['sortOrder'];
        }

        if (!empty($options['rowCount'])) {
            $limit = 'LIMIT ' . (int)$options['rowCount'];
            if ($options['page'] > 1) {
                $limit .= ' OFFSET '
                        . (int)($options['rowCount'] * ($options['page'] - 1));
            }
        }

        return self::$_db->fetchAll(
                "SELECT $select FROM $from WHERE $where $order $limit");
    }

    public function count(
            $filterColumn = null, $filter = null,
            $report = null, $filter = null) {
        if(!empty($report)) {
            $from = 'users u, profiles pfl, programming p';
            $where = 'u.in_excluded=0 AND u.id_profile=pfl.id '
                    . 'AND pfl.label="assinante"';
            if (!empty($filter)) {
                if(!empty($filter['name'])) {
                    $where .= sprintf(
                            " AND u.name LIKE ",
                            self::$_db->quote('%%' . $filter['name'] . '%%')
                    );
                }
            }
        } else {
            $from = 'users u, profiles p';
            $where = sprintf(
                    'u.in_excluded=0 AND u.id_profile=p.id AND p.label="%s"',
                    Users::PROFILE_SUBSCRIBER
            );
            if (!empty($options['filterColumn']) && !empty($options['filter'])) {
                $where .= sprintf(
                        " AND %s LIKE %s",
                        $options['filterColumn'],
                        self::$_db->quote('%%' . $options['filter'] . '%%')
                );
            }
        }
        return self::$_db->fetchOne(
                "SELECT count(DISTINCT u.id) FROM $from WHERE $where");
    }

    public function read($data) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array or object');
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
                    "SELECT $select FROM users u "
                    . "WHERE u.$key = $value AND u.in_excluded=0");
            if (!empty($results)) {
                foreach ($results as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function save() {
        $date = new Zend_Date();
        $this->dateModification = $date->get(Zend_Date::TIMESTAMP);
        if (isset($this->id)) {
        // update
            $data = array(
                'id'              => $this->id,
                'id_profile'      => '0',
                'code'            => $this->code,
                'name'            => $this->name,
                'email'           => $this->email,
                //'password'        => $this->password,
                //'password_link'   => '',
                //perguntar Deucelia//'dt_register'     => $this->dateModification,
                'in_excluded'     => '0',
            );
            if ($this->id_city) {
                $data['id_city'] = $this->id_city;
            }
            if (!empty($this->password)) {
                $data['password'] = SHA1($this->password);
            }

            self::$_db->update('users', $data, 'id='.$this->id.'');
        } else {
        // novo
            if (empty($this->code) || empty($this->name) || empty($this->email)) {
                throw new Exception(
                        'Sem dados suficientes para criar novo assinante');
            }
            $password = SHA1($this->password);
            if (empty($this->password)) {
                $this->password = substr((uniqid(rand(), true)), 0, 10);
                $password = SHA1($this->password);
            }

            $data = array(
                'id_profile'      => '0',
                'code'            => $this->code,
                'name'            => $this->name,
                'email'           => $this->email,
                'password'        => $password,
                'password_link'   => '',
                'dt_register'     => $this->dateModification,
                'in_excluded'     => '0',
            );
            if ($this->id_city) {
                $data['id_city'] = $this->id_city;
            }
            self::$_db->insert('users', $data);
        }

        return $this;
    }

    public function send($new_pass = null) {
        $message_part = 'Por favor encontre abaixo sua senha para acessar '
                . 'nossa solução através do website www.lembrefacil.com.br</p> '
                . '<p>Senha: <strong>'.$this->password.'</strong></p><p>'
                . 'Recomendamos que você troque sua senha em seu primeiro '
                . 'acesso ao sistema clicando na opção "Trocar senha". '
                . 'Recomendamos ainda que, antes de usar o sistema pela '
                . 'primeira vez, você leia o manual que se encontram na opção '
                . '"Ajuda". Desejamos uma ótima experiência com o Lembre Fácil '
                . 'e ficamos à disposição para eventuais dúvidas.</p>'
                . '<p>&nbsp;</p><p>&nbsp;</p>'
                . '<p>Mensagem gerada automaticamente.<br/>Qualquer dúvida '
                . 'entre em contato com suporte@lembrefacil.com.br';
        if ($new_pass == 'new_pass') {
            $subject = 'Sua Senha foi Alterada na Plataforma Lembre Fácil! '
                    . date("H:i\h, d/m/Y");
            $message = 'Prezado(a) <strong>'.$this->name.'</strong>,'
                    . '<p>Sua senha de acesso ao Lembre Fácil foi alterada. '
                    . $message_part;
        } else {
            $subject = 'Você foi Cadastrado na Plataforma Lembre Fácil! '
                    . date("H:i\h, d/m/Y");
            $message = 'Prezado(a) <strong>'.$this->name.'</strong>,<p>'
                    . 'Bem-vindo ao Lembre Fácil. '
                    . $message_part;
        }

        $mail = Util::getMailObject();
        $mail->setBodyHtml($message)
            ->setFrom('suporte@lembrefacil.com.br', 'Suporte Lembre Fácil')
            ->addTo($this->email, $this->name)
            ->setSubject($subject)
            ->addHeader('Reply-To', 'suporte@lembrefacil.com.br')
            ->send();

        return $this;
    }

    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Assinante não identificado para remoção');
        }
        self::$_db->update('users', array('in_excluded'=> 1), 'id='.$this->id);
    }
}
