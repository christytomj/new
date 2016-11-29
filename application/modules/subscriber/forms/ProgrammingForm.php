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

class ProgrammingForm extends HandsOn_Form
{
    public function init()
    {
        $this->addElement('textarea', 'description', array(
            'label'       => 'Descrição',
            'description' => 'Descrição da programação',
            'filters'     => array('StringTrim', 'StripTags'),
            'required'    => true
        ));

        $this->addElement('date', 'dt_start', array(
            'description' => self::DESCRIPTION_DATE,
            'label' 	  => 'Início',
            'required' 	  => true
        ));

        $this->addElement('date', 'dt_end', array(
            'description' => self::DESCRIPTION_DATE,
            'label' 	  => 'Término',
        ));

        $this->addElement('text', 'time', array(
            'attribs'     => array('class' => 'formElementMultiValues'),
            'description' => 'Horário que a conta irá receber a programação',
            'label'       => 'Horários',
            'required'    => true,
        ));
        
        $this->addElement('text', 'reminder', array(
            'description' => 'Periodicidade dos lembretes (em minutos)',
            'filters'     => array('StringTrim', 'StripTags'),
            'label'       => 'Lembretes a cada',
        ));

        $this->addElement('radio', 'in_repetition', array(
            'multiOptions' => array(0 => 'Nenhuma',
                                    1 => 'Com freqüencia',
                                    2 => 'Com interrupção'),
            'label'        => 'Tipo de Repetição',
            'required'        => true,
        ));

    //////////////////////////////

        $this->addElement('select', 'frequency', array(
            'attribs'     => array('class' => 'formElementMultiValues'),
            'description'  => 'Opções de freqüencias',
            'label'        => 'Freqüencia com Repetição',
            'multiOptions' => array(1 => 'Diária',
                                    2 => 'Semanal',
                                    3 => 'Mensal',
                                    4 => 'Anual')
        ));

    /////////////////////////////////

        $this->addElement('multiCheckbox', 'week_frequency', array(
            'label'        => 'Selecione o(s) dia(s)',
            'multiOptions' => array(0 => 'Domingo',
                                    1 => 'Segunda',
                                    2 => 'Terça',
                                    3 => 'Quarta',
                                    4 => 'Quinta',
                                    5 => 'Sexta',
                                    6 => 'Sabado'),
            'filters'     => array('StringTrim', 'StripTags'),
        ));

    ////////////////////////////////////

        $this->addElement('select', 'month_frequency', array(
            'description'  => 'Dia do mês',
            'label'        => 'Selecione o dia do mês',
            'multiOptions' => array(1 => '1', 2 => '2', 3 => '3', 4 => '4',
                                    5 => '5', 6 => '6', 7 => '7', 8 => '8',
                                    9 => '9', 10 => '10', 11 => '11', 12 => '12',
                                    13 => '13', 14 => '14', 15 => '15', 16 => '16',
                                    17 => '17', 18 => '18', 19 => '19', 20 => '20',
                                    21 => '21', 22 => '22', 23 => '23', 24 => '24',
                                    25 => '25', 26 => '26', 27 => '27', 28 => '28',
                                    29 => '29', 30 => '30', 31 => '31'),
        ));

    ///////////////////////

        $this->addElement('select', 'year_month_frequency', array(
            'attribs'     => array('class' => 'inline'),
            'multiOptions' => array(1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
                                    4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                                    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
                                    10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'),
        ));

        $this->addElement('select', 'year_day_frequency', array(
            'attribs'     => array('class' => 'inline'),
            'multiOptions' => array(1 => '1', 2 => '2', 3 => '3', 4 => '4',
                                    5 => '5', 6 => '6', 7 => '7', 8 => '8',
                                    9 => '9', 10 => '10', 11 => '11', 12 => '12',
                                    13 => '13', 14 => '14', 15 => '15', 16 => '16',
                                    17 => '17', 18 => '18', 19 => '19', 20 => '20',
                                    21 => '21', 22 => '22', 23 => '23', 24 => '24',
                                    25 => '25', 26 => '26', 27 => '27', 28 => '28',
                                    29 => '29', 30 => '30', 31 => '31'),
        ));

    ///////////////////////////

//        $this->addElement('radio', 'interrupt', array(
//             'multiOptions' => array(0 => 'Envia durante (dd) dias com a interrupção de (id) dias.',
//                                     1 => 'Envia a cada (id) dias.'),
//        ));
//
//        $this->addElement('text', 'during_days', array(
//             'description' => 'Envia durante X dias',
//             'filters'     => array('StringTrim', 'StripTags'),
//        ));
//
//        $this->addElement('text', 'interrupt_days', array(
//             'description' => 'Interrompe o envio por Y dias',
//             'filters'     => array('StringTrim', 'StripTags'),
//        ));
//
//        $this->addElement('text', 'interval_days', array(
//             'description' => 'Envia a cada X dias',
//             'filters'     => array('StringTrim', 'StripTags'),
//        ));

        $this->addSubmit();
    }
}
