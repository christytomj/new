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

class CellPhoneForm extends HandsOn_Form {
    public function init() {
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('text', 'manufacturer', array(
            'description' => 'Fabricante do aparelho',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Fabricante',
            'required'    => true,
        ));

        $this->addElement('text', 'model', array(
            'description' => 'Modelo do aparelho',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Modelo',
            'required'    => true,
        ));


        $maxFileSize = 0.5;
        $maxFileSizeBits = $maxFileSize * 1024 * 1024;

        $this->addElement('upload', 'image', array(
            'count'       => 1,
            'description' => self::descriptionUpload(array('JPG', 'PNG', 'GIF'), $maxFileSize .'MB'),
            'label'       => 'Imagem',
            'mimetypes'   => array('image/gif', 'image/jpeg', 'image/png'),
            'size'        => $maxFileSizeBits
        ));

        $this->addElement('hidden', 'currentImage', array(
            'decorators' => $this->_noElementDecorator,
            'name'       => 'MAX_FILE_SIZE',
            'value'      => $maxFileSizeBits
        ));

        $this->addSubmit();
    }
}