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
class RemedyForm extends HandsOn_Form {

    public function init() {
        $userIdentity = Zend_Auth::getInstance()->getIdentity();
        $idProfile = (null != $userIdentity) ? $userIdentity->id_profile : null;

        $this->addElement('text', 'name', array(
            'description' => 'Nome do remédio',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Nome',
            'required'    => true,
            'validators'  => array(
                    array('StringLength', false, array(0, 30))),
        ));
        $this->addElement('text', 'descr', array(
            'description' => 'Descrição',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Descrição',
            'required'    => true,
            'validators'  => array(
                    array('StringLength', false, array(0, 150))),
        ));
        $this->addElement('text', 'qty', array(
            'description' => 'Quantidade',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Quantidade',
            'required'    => true,
            'validators'  => array('Digits'),
        ));
        $this->addElement('select', 'val', array(
            'description'  => 'Validade',
            'label'        => 'Validade',
            'multiOptions' => $this->montaOpcoesValidade(),
            'required'    => true,
        ));

        $this->addSubmit();
    }

    private function montaOpcoesValidade() {
        $ret = array();
        for ($im=1; $im <= 48; ++$im) {
            $ret["$im"] = sprintf(
                    '%d %s',
                    $im,
                    $im==1 ? 'mês' : 'meses');
        }
        return $ret;
    }
}
