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

class CreditForm extends HandsOn_Form
{
    public function init() {
        $this->addElement(
            'text',
            'actual',
            array(
                'description' => 'Indica os créditos que a conta possui',
                'filters'     => array('StringTrim', 'StripTags'),
                'label'       => 'Créditos na conta',
                'validators'  => array(
                    'Digits',
                    array('StringLength', false, array(0, 3)),
            ),
            'disabled' => true
        ));
        $this->addElement(
            'hidden',
            'showCostMessage',
            array(
                'description' => 'Edite o valor de créditos da Conta',
            )
        );
        $this->addElement(
            'text',
            'credit',
            array(
                'description' => 'Edite o valor de créditos da Conta',
                'filters'     => array('StringTrim', 'StripTags'),
                'label'       => 'Acrescentar Crédito de Mensagens',
                'validators'  => array(
                    'Digits',
                    array('StringLength', false, array(0, 3)),
                ),
                'required' => true,
            )
        );

        $this->addSubmit();
    }
}