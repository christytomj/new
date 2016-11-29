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

class ReportSearchForm extends HandsOn_Form
{
    public function init()
    {

        $this->addElement('date', 'dt_start', array(
            //'attribs' 		=> array('date:yearRange' => ('-100:+0')),
            'description' 	=> self::DESCRIPTION_DATE,
            'label' 		=> 'Período de',
            'required' 		=> false
        ));

        $this->addElement('date', 'dt_end', array(
            //'attribs' 		=> array('date:yearRange' => ('-100:+0')),
            'description' 	=> self::DESCRIPTION_DATE,
            //'label' 		=> 'Data Fim',
            //'required' 		=> true
        ));

        $this->addElement('text', 'name', array(
          'description' => 'Nome',
          'filters'     => array('StringTrim', 'StripTags'),
          'label'       => 'Nome',
          'required'    => false,
        ));

        $this->addElement('submit', 'query', array(
            'decorators'    => $this->_buttonElementDecorators,
            'label'         => 'Buscar'));
        /// $this->addSubmit('Buscar');
    }
}