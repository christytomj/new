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


class HandsOn_View_Helper_GridConfig extends Zend_View_Helper_Abstract {
    public function gridConfig($columns, $searchItems, $buttons, $url, $sortName = null, $sortOrder = 'asc') {
        $gridConfig = array();
        if (null === $sortName) {
            $sortName = key($columns);
        }

        $gridConfig['sortOrder'] = $sortOrder;
        $gridConfig['sortName'] = $sortName;
        $gridConfig['url'] = $url;

        foreach ($columns as $name => $column) {
            $width = isset($column[1]) ? $column[1] : 150;
            $sortable = isset($column[2]) ? $column[2] : true;
            $gridConfig['columns'][] = array(
                'name'      => $name,
                'display'   => $column[0],
                'width'     => $width,
                'sortable'  => $sortable);
        }

        if (!empty($searchItems)) {
            foreach ($searchItems as $searchItemName => $searchItemText) {
                $gridConfig['searchItems'][] = array(
                    'display' 	=> $searchItemText,
                    'name' 	=> $searchItemName);
            }
        }

        if (!empty($buttons)) {
            foreach ($buttons as $key => $button) {
                if ($button[0] != 'add' && $button[0] != 'mail') {
                    $url = $this->view->baseUrl() . '/' . $button[1];
                } else {
                    $url = '#' . $button[1];
                }

                $gridConfig['buttons'][$key] =
                    array('type' => $button[0], 'url' => $url);

                if (isset($button[2])) {
                    $gridConfig['buttons'][$key]['label'] = $button[2];
                }

                if (isset($button[3])) {
                    $gridConfig['buttons'][$key]['message'] = $button[3];
                }

                if (isset($button[4])) {
                    $gridConfig['buttons'][$key]['emptyMessage'] = $button[4];
                }
            }
        }

        return $gridConfig;
    }
}