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


class Subscriber_CellphonelistController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->acl->allow(null);
    }

    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();

        $cellphones = new Cellphone();

        $options['sortColumn'] = 'manufacturer';
        $options['sortOrder'] = '';
        $values = $cellphones->getCellPhones(
            array(
                'manufacturer',
                'model',
                'filepath',
                'filepath_thumb'),
            $options);

        $page = $this->view->baseUrl();
        $content = '<table cellspacing="1px">';

        $rowCont = 0;
        foreach($values as $cellphone) {
            if (($rowCont % 2) == 0) {
                $content .= '<tr>';
            }
            $onclick = sprintf(
                'var v=window.open(\'%s\',\'%s\',\'%s\');'
                .'v.onclick=function(){v.close();};',
                $page . $cellphone['filepath'],
                'cellImages',
                'height=300,width=300,'
                . 'location=no,resizable=no,scrolbars=no,'
                . 'menubar=no,titlebar=no'
            );
            $content .= sprintf(
                '<td>'
                . '<a href="#" onclick="%s">'
                . '<img src="%s"></img>'
                . '</a>'
                . '<div id="description"><p id="label">Fabricante|</p>'
                . '<p>%s</p><p id="label">Modelo|</p>'
                . '<p>%s</p></div></td>',
                $onclick,
                $page . $cellphone['filepath_thumb'],
                $cellphone['manufacturer'],
                $cellphone['model']
            );

            if (($rowCont % 2) == 1) {
                $content .= '</tr>';
            }
            ++$rowCont;
        }

        $content .= '</table>';

        $source = '<html><head>'
            . '<link type="text/css" rel="stylesheet" media="all" '
            . 'href="http://www.lembrefacil.com.br/panel/css/celllist.css"/>'
            . '</link></head><body><div id="phoneList">'
            . $content . '</div></body></html>';

        echo $source;
        exit();
    }
}
