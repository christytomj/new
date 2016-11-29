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


class LoginForm extends HandsOn_Form
{
    public function init()
    {
        $this->addElement('email', 'email', array(
            'label' => 'E-mail',
            'required' => true
        ));

        $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'label' => 'Senha',
            'required' => true,
            'validators' => array(array('StringLength', false, array(6)))
        ));

        $this->addElement('submit', 'submit', array(
            'decorators' => $this->_buttonElementDecorators,
            'label' => 'Acesso',
        ));
    }
}
