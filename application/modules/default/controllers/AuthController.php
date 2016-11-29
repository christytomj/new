<?php
/**
 * Singular - Academic Resource Planning
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
 * @license    http://www.numera.com.br/license/singular     Singular 1.0 License
 * @version    $Id$
 */


class AuthController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->acl->allow(null);
        Zend_Layout::startMvc(array(
                'layoutPath' => APPLICATION_PATH . '/modules/default/views/layouts',
                'layout' => 'public'
        ));
    }

    public function loginAction() {
        $form = new LoginForm();
        $form->setAction($this->view->baseUrl() . '/');
        $this->view->formResponse = '';

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
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

                if ($auth->authenticate($authAdapter)->isValid()) {
                    $userData = $authAdapter->
                            getResultRowObject(
                            array('id', 'id_profile', 'name', 'email'));
                    $auth->getStorage()->write($userData);

                    $role = Users::getProfileLabel($userData->id_profile);
                    if($role == 'assinante') {
                        $this->_redirect('subscriber/');
                    }
                    $this->_redirect('admin/');
                } else {
                    Zend_Auth::getInstance()->clearIdentity();
                    $this->view->formResponseTitle =
                            'E-mail ou senha incorretos.';
                    $this->view->formResponse =
                            'Tente novamente observando atentamente '
                            . 'todos caracteres, inclusive letras maiúscula '
                            . 'e minúsculas.';
                }
            } else {
                Zend_Auth::getInstance()->clearIdentity();
                $this->view->formResponseTitle =
                        'E-mail ou senha incorretos.';
                $this->view->formResponse =
                        'Tente novamente observando atentamente '
                        . 'todos caracteres, inclusive letras maiúscula '
                        . 'e minúsculas.';
            }
        }
        $this->view->form = $form;
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/');
    }

    public function verifyAction() {
        $this->getRequest()->setParam('format', 'json');
        $this->_helper
                ->contextSwitch()
                ->addActionContext($this->getRequest()->getActionName(), 'json')
                ->initContext();
        $this->view->clearVars();
        if ($this->getRequest()->isPost()) {
            $email = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');
            if (isset($email) && isset($password)) {
                $db = Zend_Db_Table::getDefaultAdapter();
                $email = $db->quote($email);
                $password = $db->quote($password);
                $salt = $db->quote(Zend_Registry::get('config')->auth->salt);
                $sql = sprintf(
                        'SELECT id_user, name FROM users '
                        . ' WHERE email=%s '
                        . ' AND password=SHA1(CONCAT(%s, %s, password_salt)) '
                        . ' AND in_blocked = 0 AND in_excluded = 0',
                        $email, $salt, $password);
                $user = $db->fetchRow($sql);

                $sql = 'SELECT COUNT(*) FROM profiles_users pu, profiles p '
                        . ' WHERE pu.id_profile=p.id_profile '
                        . ' AND pu.id_user=' . $user['id_user']
                        . ' AND p.label="Conteudista do site"';
                if (isset($user['id_user']) && $db->fetchOne($sql)) {
                    $this->view->id = $user['id_user'];
                    $this->view->name = $user['name'];
                }
            }
        }
    }

    public function deactivateAction() {
        $form = new PhoneDeactivationForm();
        $this->view->formResponse = '';

        if ($this->getRequest()->isPost()) {
            $form->isValid($this->getRequest()->getPost());
            $this->view->formProcessed = false;
            $cellPh = $form->getValue('cell_phone');
            $accos = $this->getAccountsSMSFromPhone($cellPh);

            $isSendCode = $form->getValue(
                    PhoneDeactivationForm::BUT_SENDCODE);
            $isSendCode = !empty($isSendCode);
            $isSetCode = $form->getValue(
                    PhoneDeactivationForm::BUT_SETCODE);
            $isSetCode = !empty($isSetCode);
            $formCode = $form->getValue('code');

            if (empty($accos)) {
                $this->view->formResponseTitle = '';
                $this->view->formResponse = '<strong>O número que você forneceu: '
                        . $cellPh
                        . ' não está cadastrado no sistema.</strong>';
            }
            if ($isSendCode) {
                if (!$this->isActiveAccounts($accos)) {
                    $this->view->formResponseTitle =
                            '';
                    $this->view->formResponse =
                            '<strong>O número que você forneceu: '
                            . $cellPh
                            . ' já está programado para não '
                            . 'receber mensagens.</strong>';
                } else if (! empty($accos->activation_code)) {
                    if (empty($formCode)) {
                        $this->view->formResponseTitle = '';
                        $this->view->formResponse =
                                '<strong>O número que você forneceu: '
                                . $cellPh
                                . ' já recebeu o código de bloqueio, por favor '
                                . 'insira-o no campo indicado.</strong>';
                    }
                } else {
                    $this->sendBlockingCode($accos);
                    $this->view->formProcessed = true;
                    $this->view->formResponseTitle = '';
                    $this->view->formResponse =
                            '<strong>Um código de 4 dígitos '
                            . 'foi enviado por SMS para '
                            . $cellPh
                            . ' com as instruções para bloquear o recebimento '
                            . 'de mensagens.</strong>';
                }
            } else if ($isSetCode) {
                if ($this->checkActivationCode($accos, $formCode)) {
                    $this->unblockAndCleanCode($accos);
                    $this->view->formProcessed = true;
                    $this->view->formResponseTitle = '';
                    $this->view->formResponse =
                            '<strong>Seu número: '
                            . $cellPh
                            . ' não irá mais receber SMS do Sistema '
                            . 'Lembrefácil</strong>';
                } else {
                    $this->view->formResponseTitle = '';
                    $this->view->formResponse =
                            '<strong>Você digitou o código: '
                            . $formCode
                            . 'que não corresponde ao código enviado '
                            . 'por SMS para o número '
                            . $cellPh
                            . '. Por favor verifique a digitação.</strong>';
                }
            }
        }

        $this->view->form = $form;

    }

    /**
     * @param array lista de Accounts.
     * @return bool se tem alguma conta com estado is_active do array parametro
     */
    private function isActiveAccounts($accs) {
        foreach ($accs as $acc) {
            if ($acc->is_active) {
                return true;
            }
        }
        return false;
    }

    /**
     * Chama a sendBlockingCode da primeira conta do array parametro
     * @param array lista de Accounts.
     */
    private function sendBlockingCode($accos) {
        foreach ($accos as $acc) {
            $acc->sendBlockingCode();
            return;
        }
    }



    private function getAccountsSMSFromPhone($cellPh) {
        $accos = Accounts::getAccountByCell($cellPh);
        /* tira os java */
        foreach ($accos as $k=>$v) {
            if ($v->isOptJava()) {
                unset($accos[$k]);
            }
        }
        return $accos;
    }

    /**
     * @param array $accos lista de Accounts.
     * @param string $formcode o código fornecido pelo usuário.
     * @return comparação do $formcode com o ->activation_code da
     *      primeira conta do array parametro $accos
     */
    private function checkActivationCode($accos, $formCode) {
        foreach ($accos as $acc) {
            return($acc->activation_code == $formCode);
        }
        return false;
    }

    /**
     * Chama a unblockAndCleanCode da primeira conta do array parametro
     * @param array lista de Accounts.
     */
    private function unblockAndCleanCode($accos) {
        foreach ($accos as $acc) {
            $acc->unblockAndCleanCode();
            return;
        }
    }
}