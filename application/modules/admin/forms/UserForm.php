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

class UserForm extends HandsOn_Form {

    public function init() {
        $cUser = Users::getLoggedUser();

        $profiles = Users::getAllowedCreateProfiles($cUser->label);
        $profiles = UserProfile::convertObjectToIdTitle($profiles);

        $this->addElement('select', 'id_profile', array(
            'description'  => 'Perfil do usuário',
            'label'        => 'Perfil',
            'multiOptions' => $profiles,
            'required'    => true,
        ));
        $this->addElement('text', 'name', array(
            'description' => 'Nome do usuário',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Nome',
            'required'    => true,
        ));

        $this->addElement('email', 'email', array(
            'label'       => 'E-mail',
            'required'    => true,
            'description' => self::DESCRIPTION_MAIL
        ));

        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'label'      => 'Senha',
            'validators' => array(
            'alnum',
            array('StringLength', false, array(6)),
            //array('Confirm', false, array('passwordConfirm')),
            //verifica se a senha possui letras e numeros
            //array('regex', true, array('/(\d+\w*[a-zA-Z]+|[a-zA-Z]+\w*\d+)\w*/'))
            ),
        ));

        $this->addSubmit();
    }
}