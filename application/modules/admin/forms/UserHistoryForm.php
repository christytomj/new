<?php
/*
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

class UserHistoryForm extends HandsOn_Form {
    const FIELD_DT_INICIO = 'dt_inicio';
    const FIELD_DT_FIM = 'dt_fim';

    public function init() {
        $this->addElement('date', self::FIELD_DT_INICIO, array(
                'description'   => 'Início',
                'label'         => 'Início',
                'required'      => false,
        ));

        $this->addElement('date', self::FIELD_DT_FIM, array(
                'description'   => 'Final',
                'label'         => 'Final',
                'required'      => false,
        ));

        $this->addSubmit('Relatório');
    }
}