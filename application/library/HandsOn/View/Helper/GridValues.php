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

class HandsOn_View_Helper_GridValues extends Zend_View_Helper_Abstract
{
	/**
	 * valores da grid
     *
     * @param array { page, qtype, query, rp, sortname, sortorder, sortColumns, filterColumns }
     * @return array { page, rowCount, sortOrder, sortColumn, filter, filterColumn }
     */
	public function gridValues(array $values)
    {
        $page = (!empty($values['page'])) ? (int)$values['page'] : 1;
        $rowCount = (!empty($values['rp'])) ? (int)$values['rp'] : 15;

        $sortOrder = ($values['sortorder'] == 'desc') ? ' DESC' : null;
        $sortColumn = null;

        // verifica se a coluna é ordenável
        if (isset($values['sortColumns'])
                && array_key_exists($values['sortname'], $values['sortColumns'])) {
        	$sortColumn = $values['sortColumns'][$values['sortname']];
        }

        $filter = $values['query'];
        $filterColumn = null;

        // verifica se a coluna é filtrável
        if (isset($values['filterColumns']) && array_key_exists($values['qtype'], $values['filterColumns'])) {
            $filterColumn = $values['filterColumns'][$values['qtype']];
        }

        return array('page' 			=> $page,
        			 'rowCount' 		=> $rowCount,
        			 'sortOrder' 		=> $sortOrder,
        			 'sortColumn' 		=> $sortColumn,
        			 'filter' 			=> $filter,
        			 'filterColumn' 	=> $filterColumn);
    }
}