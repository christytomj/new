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

class SubscriberForm extends HandsOn_Form
{
    public function init()
    {
        $estados = array_merge(
                array(''=>''),
                City::getUFs());
        
        $this->addElement('select', 'uf', array(
            'description'   => 'Estado',
            'label'         => 'Estado',
            'multiOptions'  => $estados,
            'required'      => true,
            'attribs'       => array(
                'class' => 'formElementStateForAutocomplete',
            )
        ));

        $this->addElement('select', 'city', array(
            'description'   => 'Município',
            'label'         => 'Município',
            'multiOptions'  => array(''=>'Selecione um Estado.'),
            'required'      => true,
            'validators' => array(
                'alnum','notEmpty',
            ),
            'attribs'       => array(
                'class' => 'formElementAutocompleteCity',
            )
        ));

        // para não validar pelo valor setado, só pela existência
        $this->getElement('city')->setRegisterInArrayValidator(false);

        $this->addElement('text', 'code', array(
            'description' => 'Código do assinante',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Código',
            'required'    => true,
        ));

        $this->addElement('text', 'name', array(
            'description' => 'Nome do assinante',
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
            'validators' => array(
                'alnum',
                array('StringLength', false, array(6)),
            ),
            'label'      => 'Senha',
        ));

        $this->addSubmit();
    }
}