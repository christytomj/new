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

class EditContentForm extends HandsOn_Form
{
    public function init()
    {
        $this->addElement('text', 'title', array(
              'description' => 'Título:',
              'filters'     => array('StringTrim', 'StripTags'),
              'label'       => 'Título',
              'required'    => true,
              'value'       => 'Título',
            ));

        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('textarea', 'text', array(
            'attribs'     => array('class' => 'formElementRichText'),
            'filters'     => array('HtmlBody'),
            'label'       => 'Texto',
            'required'    => false
            ));

        $this->addSubmit();
    }
}