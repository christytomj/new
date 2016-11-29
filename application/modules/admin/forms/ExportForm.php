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

class ExportForm extends HandsOn_Form
{
    public function init()
    {
          $this->addElement('text', 'multiply', array(
              'description' => 'Multiplica o número de programações',
              'filters'     => array('StringTrim', 'StripTags'),
              'label'       => 'Multiplica por',
              'required'    => true,
          ));

          $this->addElement('text', 'add', array(
              'description' => 'Soma o valor da mensalidade',
              'filters'     => array('StringTrim', 'StripTags'),
              'label'       => 'Somar com',
              'required'    => true,
          ));

        $this->addSubmit();
    }
}