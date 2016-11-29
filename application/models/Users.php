<?php

class Users extends HandsOn_BasicModel {
    const SEND_OPTION_JAVA = 1;
    const SEND_OPTION_SMS = 2;
    const PROFILE_SUBSCRIBER = 'assinante';
    const PROFILE_ADMIN = 'administrativo';
    const PROFILE_FUNC = 'funcionario';
    const PROFILE_FIN1 = 'financeiro1';
    const PROFILE_FIN2 = 'financeiro2';
    const PROFILE_GUEST = 'guest';
    const PROFILE_LAB = 'laboratorio';
    const PROFILE_DIST = 'distribuidor';
    const PROFILE_SELLER = 'vendedor';
    const PROFILE_REDE = 'rede';

    protected $_data = array(
        'id'                => null,
        'id_profile'        => null,
        'code'              => null,
        'dateModification'  => null,
        'name'              => null,
        'email'             => null,
        'password'          => null,
        'password_link'     => null,
        'dt_register'       => null,
        'in_excluded'       => null,
        'title'             => null,
        'label'             => null,
        'id_owner'          => null,
        'labcredit'         => null,
        'id_city'           => null,
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
        'title',
        'label',
        'id_owner',
        'labcredit',
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
        'title'            => 'p.title',
        'label'            => 'p.label',
        'id_owner'         => 'u.id_owner',
        'labcredit'        => 'u.labcredit',
        'id_city'          => 'u.id_city',
    );

    public function getCity() {
        $city = City::getById($this->id_city);
        return $city;
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
                ."WHERE u.$key = $value AND u.in_excluded=0");
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
        //$prevRevision = self::$_db->fetchOne('SELECT revision FROM blog_revisions WHERE id_blog=' . $this->id . ' ORDER BY revision DESC LIMIT 1');
        //$this->revision = $prevRevision + 1;

            $data = array(
                'id'              => $this->id,
                'id_profile'      => $this->id_profile,
                'code'            => '',
                'name'            => $this->name,
                'email'           => $this->email,
                //'password'        => SHA1($this->password),
                'password_link'   => '',
                'dt_register'     => $this->dateModification,
                'in_excluded'     => '0'
            );

            if (!empty($this->password)) {
                $data['password'] = SHA1($this->password);
            }

            self::$_db->update('users', $data, 'id='.$this->id.'');
        } else {
        // novo
            if (empty($this->id_profile) || empty($this->name)
                || empty($this->email)) {
                throw new Exception(
                'Sem dados suficientes para criar nova página');
            }

            $password = SHA1($this->password);
            if (empty($this->password)) {
                $this->password = substr((uniqid(rand(), true)), 0, 10);
                $password = SHA1($this->password);
            }
            $data = array(
                //'id'              => '3',
                'id_profile'    => $this->id_profile,
                'code'          => '',
                'name'          => $this->name,
                'email'         => $this->email,
                'password'      => $password,
                'password_link' => '',
                'dt_register'   => $this->dateModification,
                'in_excluded'   => '0',
                'id_owner'      => $this->id_owner,
            );

            self::$_db->insert('users', $data);
        }
        return $this;
    }

    public function send($new_pass = null) {
        $message_part
            = 'Por favor encontre abaixo sua senha para acessar '
            . 'nossa solução através do website www.lembrefacil.com.br</p> '
            . '<p>Senha: <strong>'.$this->password.'</strong></p><p>'
            . 'Recomendamos que você troque sua senha em seu primeiro '
            . 'acesso ao sistema clicando na opção "Trocar senha". '
            . 'Recomendamos ainda que, antes de usar o sistema pela '
            . 'primeira vez, você leia os manuais que se encontram na '
            . 'opção "Ajuda". Desejamos uma ótima experiência com o Lembre '
            . 'Fácil e ficamos à disposição para eventuais dúvidas.</p>'
            . '<p>&nbsp;</p><p>&nbsp;</p>'
            . '<p>Mensagem gerada automaticamente.<br/>'
            . 'Qualquer dúvida entre em contato com '
            . 'suporte@lembrefacil.com.br';

        if ($new_pass == 'new_pass') {
            $subject = 'Sua Senha foi Alterada na Plataforma Lembre Fácil! '
                . date("H:i\h, d/m/Y");
            $message = 'Prezado(a) <strong>'.$this->name.'</strong>,'
                . '<p>Sua senha de acesso ao Lembre Fácil foi alterada. '
                . $message_part;
        } else {
            $subject = 'Você foi Cadastrado na Plataforma Lembre Fácil! '
                . date("H:i\h, d/m/Y");
            $message = 'Prezado(a) <strong>'
                . $this->name . '</strong>,<p>Bem-vindo ao Lembre Fácil. '
                . $message_part;
        }

        $mail = Util::getMailObject();
        $mail->setBodyHtml($message)
            ->setFrom('lembrefacil@floripa.br', 'Suporte Lembre Fácil')
            ->addTo($this->email, $this->name)
            ->setSubject($subject)
            ->addHeader('Reply-To', 'suporte@lembrefacil.com.br')
            ->send();

        return $this;
    }

    public function saveLabCredit() {
        if (!empty($this->id)) {
            $data = array(
                'id'        => $this->id,
                'labcredit' => $this->labcredit,
            );

            self::$_db->update('users', $data, 'id='.$this->id.'');
        }
        return $this;

    }

    public function saveNewPassword() {
        if (!empty($this->id)) {
            $data = array(
                'id'              => $this->id,
                'password'        => SHA1($this->password),
            );

            self::$_db->update('users', $data, 'id='.$this->id.'');
        }
        return $this;
    }

    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Usuário não identificado para remoção');
        }
        self::$_db->update('users', array('in_excluded'=> 1), 'id='.$this->id);
    }

    public function isProfile($testProfile) {
        return($this->label == $testProfile);
    }

    public function isAdmin() {
        return $this->isProfile(Users::PROFILE_ADMIN);
    }

    public function isSeller() {
        return $this->isProfile(Users::PROFILE_SELLER);
    }

    public function isSubscriber() {
        return $this->isProfile(Users::PROFILE_SUBSCRIBER);
    }

    public function isLab() {
        return $this->isProfile(Users::PROFILE_LAB);
    }

    public function isDist() {
        return $this->isProfile(Users::PROFILE_DIST);
    }

    public function isRede() {
        return $this->isProfile(Users::PROFILE_REDE);
    }

    public function isFin1() {
        return $this->isProfile(Users::PROFILE_FIN1);
    }

    public function isFin2() {
        return $this->isProfile(Users::PROFILE_FIN2);
    }

    public function isFunc() {
        return $this->isProfile(Users::PROFILE_FUNC);
    }

    public static function get($data, $options = null) {
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
        $from = 'users u, profiles p';

        $where = 'u.id_profile=p.id AND u.in_excluded=0 ';

        if (!empty($options['hiddenProfiles'])) {
            $where .= sprintf(
                    'AND p.label NOT IN ("%s") ',
                    join('","', $options['hiddenProfiles'])
                    );
        }

        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                " AND %s LIKE %s ",
                $options['filterColumn'],
                self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }

        $order = '';
        if (!empty($options['sortColumn'])) {
            $column = self::$_dataSource[$options['sortColumn']];
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

        // error_log(__METHOD__ . " - Query: 'SELECT $select FROM $from WHERE $where $order $limit'");
        return self::$_db->fetchAll(
                "SELECT $select FROM $from WHERE $where $order $limit");
    }

    public static function count(
            $idProfile, $filterColumn = null, $filter = null,
            $filterProfiles=array()) {

        $from = 'users u, profiles p';

        $profile = self::getProfileLabel($idProfile);

        $where = 'u.id_profile=p.id AND u.in_excluded=0 ';
        if (! empty($filterProfiles)) {
            $where .= sprintf(
                    ' AND p.label not in (\'%s\') ',
                    join('\', \'', $filterProfiles));
        }

        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                ' AND %s LIKE %s',
                $options['filterColumn'],
                self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }
        return self::$_db->fetchOne("SELECT count(*) FROM $from WHERE $where");
    }

    /**
     * Retorna os perfis que o $idProfile pode criar.
     * Usada para construir o <select> do form de usuarios
     * @param <int ou string> $idProfile o id ou o label do profile
     * @return <array> lista UserProfile.
     */
    public static function getAllowedCreateProfiles($idProfile) {
        if (is_numeric($idProfile)) {
            $cprofile = self::getProfileLabel($idProfile);
        } else {
            $cprofile = $idProfile;
        }

        $profiles = UserProfileDAO::getUserProfilesFiltered(
                self::listDisalowedViewProfilesFor($cprofile));
        return $profiles;
    }

    /**
     * Lista os perfis que o determinado perfil NAO pode ver ou criar.
     *
     * @param <array> lista de labels de profiles proibidos
     */
    public static function listDisalowedCreateProfilesFor($labelProfile) {

        // ninguem vê assinantes aqui
        $ret = array(uSERS::PROFILE_SUBSCRIBER);
        // Só admin vê admins
        if ($labelProfile != Users::PROFILE_ADMIN) {
            $ret[] = Users::PROFILE_ADMIN;
        } else {
            // admins não criam vendedores
            $ret[] = Users::PROFILE_SELLER;
        }
        if ($labelProfile == Users::PROFILE_LAB) {
            $ret[] = Users::PROFILE_FUNC;
            $ret[] = Users::PROFILE_FIN1;
            $ret[] = Users::PROFILE_FIN2;
            $ret[] = Users::PROFILE_GUEST;
            $ret[] = Users::PROFILE_LAB;
            //$ret[] = Users::PROFILE_DIST;
            //$ret[] = Users::PROFILE_SELLER;
        } else if ($labelProfile == Users::PROFILE_SELLER) {
            $ret[] = Users::PROFILE_FUNC;
            $ret[] = Users::PROFILE_FIN1;
            $ret[] = Users::PROFILE_FIN2;
            $ret[] = Users::PROFILE_GUEST;
            $ret[] = Users::PROFILE_LAB;
            //$ret[] = Users::PROFILE_DIST;
            $ret[] = Users::PROFILE_SELLER;
        }
        return $ret;
    }

    /**
     * Retorna os perfis que o $idProfile pode eidtar/criar.
     * Usada para construir o <select> do form de usuarios
     * @param <int ou string> $idProfile o id ou o label do profile
     * @return <array> lista UserProfile.
     */
    public static function getAllowedEditProfiles($idProfile) {
        if (is_numeric($idProfile)) {
            $cprofile = self::getProfileLabel($idProfile);
        } else {
            $cprofile = $idProfile;
        }

        $profiles = UserProfileDAO::getUserProfilesFiltered(
                self::listDisalowedViewProfilesFor($cprofile));
        return $profiles;
    }

    /**
     * Lista os perfis que o determinado perfil NAO pode ver ou criar.
     *
     * @param <array> lista de labels de profiles proibidos
     */
    public static function listDisalowedViewProfilesFor($labelProfile) {

        // ninguem vê assinantes aqui
        $ret = array(Users::PROFILE_SUBSCRIBER);
        // Só admin vê admins
        if ($labelProfile != Users::PROFILE_ADMIN) {
            $ret[] = Users::PROFILE_ADMIN;
        }
        if ($labelProfile == Users::PROFILE_LAB) {
            $ret[] = Users::PROFILE_FUNC;
            $ret[] = Users::PROFILE_FIN1;
            $ret[] = Users::PROFILE_FIN2;
            $ret[] = Users::PROFILE_GUEST;
            $ret[] = Users::PROFILE_LAB;
            //$ret[] = Users::PROFILE_DIST;
            //$ret[] = Users::PROFILE_REDE;
            //$ret[] = Users::PROFILE_SELLER;
        } else if ($labelProfile == Users::PROFILE_SELLER) {
            $ret[] = Users::PROFILE_FUNC;
            $ret[] = Users::PROFILE_FIN1;
            $ret[] = Users::PROFILE_FIN2;
            $ret[] = Users::PROFILE_GUEST;
            $ret[] = Users::PROFILE_LAB;
            //$ret[] = Users::PROFILE_DIST;
            //$ret[] = Users::PROFILE_REDE;
            $ret[] = Users::PROFILE_SELLER;
        } else if ($labelProfile == Users::PROFILE_DIST) {
            $ret[] = Users::PROFILE_FUNC;
            $ret[] = Users::PROFILE_FIN1;
            $ret[] = Users::PROFILE_FIN2;
            $ret[] = Users::PROFILE_GUEST;
            $ret[] = Users::PROFILE_LAB;
            $ret[] = Users::PROFILE_DIST;
            //$ret[] = Users::PROFILE_REDE;
            $ret[] = Users::PROFILE_SELLER;
        }
        return $ret;
    }

    public static function getProfileLabel($id) {
        //return 'administrativo';
        return UserProfileDAO::getLabelFromId($id);
    }

    /**
     * Pega dados (id, name, email) de um usuário dado o email.
     *
     * @param <string> $email o email pra procurar na base
     * @return <HashResultSet> os dados do usuário
     */
    public static function getUserByEmail($email) {
        $busca = self::$_db->fetchRow(
            "SELECT id,name,email FROM users "
            . "WHERE in_excluded=0 and email='$email'");
        return $busca;
    }

    /**
     * Pega um usuario dado o codigo
     *
     * @param <string> $code o código do assinante
     * @return <Users> usuario do codigo ou null (?)
     */
    public static function getUserByCode($code) {
        $busca = self::$_db->fetchRow(
            'SELECT * FROM users u, profiles p '
            . 'WHERE u.id_profile=p.id AND code=\''.$code.'\'');
        return $busca;
    }

    /**
     * Pega um usuário dado o id.
     *
     * @param <type> $id_user o ID do usuário
     * @return <Users> usuario do id ou null (?)
     */
    public static function getUserById($id_user) {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE u.id_profile=p.id AND u.id=%s',
            join(', ', array_values(self::$_dataSource)),
            'users u, profiles p',
            self::$_db->quote($id_user)
        );
        $busca = self::$_db->fetchRow($sql);
        $user = null;
        if (empty($busca)) {
            error_log(__METHOD__ . " - User com id ($id_user) não achado.");
        }  else {
            $user = new Users($busca);
        }
        return $user;
    }

    /**
     * Lista os usuários (vendedores) que são do $owner
     * @param Users $owner um laboratorio dono de vendeores
     * @return array(Users) lista com os possuídos do $owner
     */
    public static function listUsersByOwner(Users $owner) {
        $select = "SELECT * FROM users where id_owner = "
                . $owner->id;
        $rs = self::$_db->fetchAll($select);
        $ret = array();
        foreach ($rs as $row) {
            $ret[] = new Users($row);
        }
        return $ret;
    }

    /**
     * Pega dados de recuperação de senha: id_user, dt_register
     *
     * @param <type> $code o código de troca de senha
     * @return <HashResultSet>
     */
    public static function getLinkCodeData($code) {
        $code = self::$_db->quote($code);
        $busca = self::$_db->fetchRow(
            'SELECT id,id_user,code,dt_register FROM password_links '
            . 'WHERE code='.$code);
        return $busca;
    }

    public static function checkResetPassData($data) {
        $checker = true;
        $id_user = $data['linkCodeData']['id_user'];
        $user = self::getUserById($id_user);
        $email = $user->email;
        $emailCheck = $email == $data['email'];
        if(!$emailCheck) {
            $checker = false;
        }
        elseif(!($data['password']==$data['passwordConfirm'])) {
            $checker = false;
        }
        return $checker;
    }

    public static function savePasswordLink($user) {
        $code = substr(SHA1(uniqid(rand(), true)), 0, 16);
        $data = array(
            'id_user' => $user['id'],
            'code' => $code,
            'dt_register' => Zend_Date::now()->get(Util::DB_DATE_FORMAT),
        );
        $checker = self::$_db->fetchOne(
            'SELECT id FROM password_links WHERE id_user='
            . $data['id_user']);
        if($checker == null) {
            self::$_db->insert('password_links', $data);
        }else {
            self::$_db->delete('password_links', 'id_user ='.$user['id']);
            self::$_db->insert('password_links', $data);
        }
    }

    public static function getPasswordLink($user) {
        $passwordCode = self::$_db->fetchOne(
            'SELECT code FROM password_links WHERE id_user='
            . $user['id']);
        return $passwordCode;
    }

    public static function sendEmail($user, $link, $page) {
        $message = 'Prezado(a) <strong>'.$user['name'].'</strong>,'
            . '<p>Para alterar sua senha de acesso a área restrita do '
            . 'sistema Lembre Fácil clique no link abaixo:<br/><br/>'
            . '<a href=' . $page . '/password/reset/code/' . $link . '>'
            . 'clique aqui</a><br/><br/>'
            . 'Este link estará disponível por 5 dias e poderá ser '
            . 'acessado somente uma vez.<br/>'
            . 'Att,<br/><br/>Lembre Fácil<br/>www.lembrefacil.com.br';

        $mail = Util::getMailObject();
        $mail->setBodyHtml($message)
            ->setFrom('Lembre Fácil')
            ->addTo($user['email'], $user['name'])
            ->setSubject('Nova senha')
            ->addHeader('Reply-To', 'suporte@lembrefacil.com.br')
            ->send();
    }

    public static function checkPasswordLinkCode($code) {
        $check = false;
        $code = self::$_db->quote($code);
        $currentTime = time() - (60*60*5*24);
        $passwordCode = self::$_db->fetchOne(
            'SELECT id_user FROM password_links WHERE code='.$code);

        if (!empty($passwordCode)) {
            $timeCheck = self::$_db->fetchOne(
                'SELECT id FROM password_links WHERE dt_register > '
                . $currentTime);

            if(!empty($timeCheck)) {
                $check = true;
            } else {
                self::$_db->delete('password_links', 'code ='."$code");
                $check = false;
            }

        }
        return $check;
    }

    public static function deleteLinkCode($linkId) {
        self::$_db->delete('password_links', "id ='$linkId'");
    }

    /**
     * Pega o usuário logado do Zend_Auth
     * @return Users o usuário logado.
     */
    public static function getLoggedUser() {
        return Users::getUserById(
            Zend_Auth::getInstance()->getIdentity()->id
        );
    }
}
