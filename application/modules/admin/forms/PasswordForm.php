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

class PasswordForm extends HandsOn_Form
{
    public function init()
    {
        
        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'label'      => 'Senha',
            'validators' => array(
                'alnum',
                array('StringLength', false, array(6)),
                array('Confirm', false, array('passwordConfirm')),
                //verifica se a senha possui letras e numeros
                //array('regex', true, array('/(\d+\w*[a-zA-Z]+|[a-zA-Z]+\w*\d+)\w*/'))
            ),
        ));
        switch ($this->_mode) {
        	case self::MODE_ADD:
        		$this->getElement('password')
        			 ->setRequired()
        			 ->setDescription(self::DESCRIPTION_PASSWORD_ADD);
        		break;
        	case self::MODE_EDIT:
        		$this->getElement('password')
        			 ->setDescription(self::DESCRIPTION_PASSWORD_EDIT);
        		break;
        }

        $this->addElement('password', 'passwordConfirm', array(
            'filters'    => array('StringTrim'),
            'label'      => 'Confirmar senha',
            'validators' => array('alnum', array('StringLength', false, array(6))),
        ));
        if ($this->_mode == self::MODE_ADD) {
        	$this->getElement('passwordConfirm')->setRequired();
        }

//        $this->addDisplayGroup(array('in_send_option', 'term_of_use', 'term_of_use_check'), 'send_options', array(
//             'legend' => 'Opções de Envio'));

        $this->addSubmit();
    }
}