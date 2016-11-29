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
class Subscriber_ProgrammingController extends HandsOn_Controller_Action {

    public function init() {
        $this->_helper->acl->allow('assinante');
    }

    public function indexAction() {
        $columns = array(
            'dt_register' => array('Data - Hora', 120),
            //'dt_register' => array('Hora', 80),
            'name' => array('Nome', 150),
            //'cell_phone' => array('Celular', 70),
            'nr_descriptions' => array('Descrições', 70),
            'in_send_option' => array('Envio via', 70),
            'action' => array('Ações', 60, false)
        );
        $searchItems = array(
            'name' => 'Nome',
            'cell_phone' => 'Celular'
        );
        $buttons = array(
            //array('add', 'accounts/add/', 'Nova conta'),
            array('remove',
                'subscriber/programming/modify',
                'Remover',
                'Deseja remover as programações selecionadas? As programações com descrições já enviadas serão cobradas mesmo após a exclusão.',
                'Nenhuma página foi selecionada.'
            )
        );

        $this->view->title = 'Programações';
        $this->view->type = 'list';
        $this->view->config = $this->view->gridConfig($columns, $searchItems, $buttons, 'programming/list');
    }

    public function listAction() {
        if ($this->getRequest()->isPost()) {

            $postValues = $this->getRequest()->getPost();
            $postValues['sortColumns'] = array(
                'dt_register' => 'dt_register',
                'name' => 'name',
                'cell_phone' => 'cell_phone',
                'nr_descriptions' => 'nr_descriptions',
                'in_send_option' => 'in_send_option');
            $postValues['filterColumns'] = array(
                'dt_register' => 'dt_register',
                'name' => 'name',
                'cell_phone' => 'cell_phone',
                'nr_descriptions' => 'nr_descriptions',
                'in_send_option' => 'in_send_option');
            $values = $this->view->gridValues($postValues);

            $userIdentity = Zend_Auth::getInstance()->getIdentity();
            $idSubscriber = (null != $userIdentity) ? $userIdentity->id : null;

            $programming = new Programming();
            $programmingList = $programming->get(
                            $idSubscriber,
                            array('id', 'dt_register', 'cell_phone', 'name',
                                'nr_descriptions', 'in_send_option'),
                            $values);

            $rows = array();
            foreach ($programmingList as $pl) {
                $rows[] = array(
                    'id' => $pl['id'],
                    'cell' => array(
                        $pl['dt_register'],
                        $pl['name'],
                        //$pl['cell_phone'],
                        $pl['nr_descriptions'],
                        Accounts::getSendOptionName($pl['in_send_option']),
                        $this->view->listAction(
                                array('programming', 'view', 'id', $pl['id']),
                                'view')
                    )
                );
            }
            $this->view->page = $values['page'];
            $this->view->total = $programming->count(
                            $idSubscriber,
                            $values['filterColumn'],
                            $values['filter']);
            $this->view->rows = $rows;
        }
    }

    private function newPdfPage() {

        //Incluir uma Página
        $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        $pageHeight = $page->getHeight();
        $pageWidth = $page->getWidth();
        //        echo "$pageHeight - ";
        //        echo "$pageWidth";
        //exit();
        //Estilo da Página
        $style = new Zend_Pdf_Style();
        $style->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $style->setLineWidth(2);
        $page->setStyle($style);

        //Incluir uma Imagem
        $imagem = Zend_Pdf_Image::imageWithPath(APPLICATION_PATH . '/../public/images/public/logo.png');
        $imageHeight = 35;
        $imageWidth = 157;
        $topPos = $pageHeight - 36;
        $leftPos = 36;
        $bottomPos = $topPos - $imageHeight;
        $rightPos = $leftPos + $imageWidth;
        $page->drawImage($imagem, $leftPos, $bottomPos, $rightPos, $topPos);

        $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
        $page->setLineColor($color1);
        $page->setLineWidth(0.5);
        //$page->drawLine(30, 625, 570, 625);
        //$page->drawLine(30, 675, 570, 675);

        return $page;
    }

    public function viewAction() {
        $id = (int) $this->getRequest()->getParam('id');

        $programmingList = Programming::getProgramming(
                        array('id', 'name', 'cell_phone', 'description',
                            'dt_start', 'dt_end', 'time', 'in_repetition',
                            'in_frequency', 'reminder'),
                        $id);

        $account = new Accounts(array('id' => $id));
        $account->read(array('name', 'cell_phone'));

        $viewConfig = array();

        if (!empty($programmingList)) {
            $rows = array();

            foreach ($programmingList as $eaProgr) {
                switch ($eaProgr['in_repetition']) {
                    case 0:
                        $eaProgr['in_repetition'] = 'Sem frequência';
                        break;
                    case 1:
                        $eaProgr['in_repetition'] = 'Com frequência';
                        break;
                    case 2:
                        $eaProgr['in_repetition'] = 'Com interrupção';
                        break;
                }
                if ($eaProgr['in_repetition'] == 'Com frequência') {
                    switch ($eaProgr['in_frequency']) {
                        case 1:
                            $eaProgr['in_frequency'] = 'Diária';
                            break;
                        case 2:
                            $eaProgr['in_frequency'] = 'Semanal';
                            break;
                        case 3:
                            $eaProgr['in_frequency'] = 'Mensal';
                            break;
                        case 4:
                            $eaProgr['in_repetition'] = 'Anual';
                            break;
                    }
                } else {
                    $eaProgr['in_frequency'] = null;
                }
                $eaProgr['reminder'] =
                        $eaProgr['reminder'] == 0 
                        ? 'Sem Lembrete'
                        : $eaProgr['reminder'];
                $dtSta = new Zend_Date($eaProgr['dt_start'], 'pt_BR');
                $dtEnd =
                        empty($eaProgr['dt_end']) 
                        ? null
                        : new Zend_Date($eaProgr['dt_end'], 'pt_BR');
                $rows[] = array(
                    0 => $eaProgr['description'],
                    1 => $dtSta->toString(Util::DATE_FORMAT),
                    2 => $dtEnd ? $dtEnd->toString(Util::DATE_FORMAT) : '',
                    3 => $eaProgr['time'],
                    4 => $eaProgr['reminder'],
                    5 => $eaProgr['in_repetition'] . ' '
                        . $eaProgr['in_frequency']);
            }
            $label = 'Conta: ' . $eaProgr['name']
                    . ' - ' . $eaProgr['cell_phone'];

            $viewConfig['group'][0]['label'] = $label;
            $viewConfig['group'][0]['data'][] = array(
                'type' => 'table',
                'header' => array(
                    'Descrição', 'Data Início', 'Data Término',
                    'Horários', 'Lembrete', 'Repetição'),
                'rows' => $rows
            );
        } else {
            $viewConfig['data'] = array(
                0 => array(
                    'type' => 'message',
                    'message' =>
                    '<div id="reportInfo">Não existem programações.</div>'
                )
            );
        }
        $this->view->title = 'Visualização de Programação';
        $this->view->type = 'report';
        $this->view->config = $viewConfig;
    }

    public function viewpdfAction() {
        $id = (int) $this->getRequest()->getParam('id');

        $valores = Programming::getProgramming(
                        array(
                            'id', 'name', 'cell_phone', 'description', 'dt_start',
                            'dt_end', 'time', 'reminder', 'in_repetition',
                            'in_frequency'),
                        $id);

        require_once 'Zend/Pdf.php';

        $pdf = new Zend_Pdf();

        //Incluir uma Página
        $page = $this->newPdfPage();


        $page->drawLine(30, 625, 570, 625);
        $page->drawLine(30, 675, 570, 675);

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setFont($font, 12);
        $page->drawText('Conta:', 31, 700);

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 11);
        $page->drawText($valores[0]['name'] . ' - ' . $valores[0]['cell_phone'], 75, 700);

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setFont($font, 12);
        $page->drawText('Descrição', 31, 650, 'UTF-8');
        $page->drawText('Data Início', 170, 650, 'UTF-8');
        $page->drawText('Data Término', 250, 650, 'UTF-8');
        $page->drawText('Horários', 335, 650, 'UTF-8');
        $page->drawText('Lembrete', 415, 650);
        $page->drawText('Repetição', 500, 650, 'UTF-8');

        $line = 600;
        $tableLine = 575;
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 11);

        foreach ($valores as $key => $valor) {
            $valor['reminder'] = $valor['reminder'] == 0 ? 'Sem Lembrete' : $valor['reminder'];
            switch ($valor['in_repetition']) {
                case 0:
                    $valor['in_repetition'] = 'Sem frequência';
                    break;
                case 1:
                    $valor['in_repetition'] = 'Com frequência';
                    break;
                case 2:
                    $valor['in_repetition'] = 'Com interrupção';
                    break;
            }

            $contDescricao = 1;
            $contHorario = 1;

            if (strlen($valor['description']) > 25) {
                $x = 10;
                do {

                    $desc = substr($valor['description'], 0, 24);
                    $valor['description'] = substr($valor['description'], 24);
                    $page->drawText($desc, 31, $line + $x, "UTF-8");
                    $x -= 20;
                    $contDescricao++;
                } while (strlen($valor['description']) > 24);
                $page->drawText($valor['description'], 31, $line + $x, "UTF-8");
            } else {
                $page->drawText($valor['description'], 31, $line + 10, "UTF-8");
            }

            if (strlen($valor['time']) > 12) {
                $x = 10;
                do {

                    $hora = substr($valor['time'], 0, 12);
                    $valor['time'] = substr($valor['time'], 12);
                    $page->drawText($hora, 335, $line + $x, "UTF-8");
                    $x -= 20;
                    $contHorario++;
                } while (strlen($valor['time']) > 12);
                $page->drawText($valor['time'], 335, $line + $x, "UTF-8");
            } else {
                $page->drawText($valor['time'], 335, $line + 10, "UTF-8");
            }


            $dt_start = new Zend_Date($valor['dt_start'], 'pt_BR');
            $dt_end = new Zend_Date($valor['dt_end'], 'pt_BR');

            if (isset($x)) {

                if ($contDescricao > $contHorario) {
                    $contTotal = $contDescricao;
                } else {
                    $contTotal = $contHorario;
                }

                $array[$key] = array(
                    //$page->drawText($valor['description'], 31, $line+10, "UTF-8"),
                    $page->drawText($dt_start->get(Util::DATE_FORMAT), 170, $line + 10, "UTF-8"),
                    $page->drawText($dt_end->get(Util::DATE_FORMAT), 250, $line + 10, "UTF-8"),
                    //$page->drawText($valor['time'], 335, $line+10, "UTF-8"),
                    $page->drawText($valor['reminder'], 415, $line + 10, "UTF-8"),
                    $page->drawText($valor['in_repetition'], 500, $line + 10, "UTF-8"),
                );
                $contTotal = ($contTotal) * 25;
                $line = ($line - $contTotal) + 30; //700
            } else {
                $line -= 20; //700
                $array[$key] = array(
                    //$page->drawText($valor['description'], 31, $line+50, "UTF-8"),
                    $page->drawText($dt_start->get(Util::DATE_FORMAT), 170, $$line + 30, "UTF-8"),
                    $page->drawText($dt_end->get(Util::DATE_FORMAT), 250, $line + 30, "UTF-8"),
                    //$page->drawText($valor['time'], 335, $line+50, "UTF-8"),
                    $page->drawText($valor['reminder'], 415, $line + 30, "UTF-8"),
                    $page->drawText($valor['in_repetition'], 500, $$line + 30, "UTF-8"),
                );
            }




            $line -= 35;
            $tableLine -= 25;
            if ($tableLine < 72) {
                //Criar novo pdf
                array_push($pdf->pages, $page);
                $page = $this->newPdfPage();
                $page->drawLine(30, 725, 570, 725);
                $page->drawLine(30, 675, 570, 675);
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $page->setFont($font, 12);
                $page->drawText('Descrição', 31, 700, 'UTF-8');
                $page->drawText('Data Início', 170, 700, 'UTF-8');
                $page->drawText('Data Término', 250, 700, 'UTF-8');
                $page->drawText('Horários', 335, 700, 'UTF-8');
                $page->drawText('Lembrete', 415, 700);
                $page->drawText('Repetição', 500, 700, 'UTF-8');
                $line = 600;
                $tableLine = 575;
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $page->setFont($font, 11);
            }
        }
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 11);

        array_push($pdf->pages, $page);

        //Salvar o PDF
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="Arquivo.pdf"');

        //Mostrar o PDF na tela
        echo $pdf->render();

        exit();
    }

    public function historyAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $account = Accounts::getAccountById($id);

        $historyAll = Programming::getProgrammingByAccount(
                        array('id', 'dt_register', 'in_send_option', 'description',
                            'dt_start', 'time', 'reminder', 'in_frequency',
                            'in_repetition', 'in_sunday', 'in_monday', 'in_tuesday',
                            'in_wednesday', 'in_thurday', 'in_friday', 'in_saturday',
                            'id_description'),
                        $id);
error_log(var_export($historyAll, true));

        $smsBought = null;
        if ($account->isOptSMS()) {
            $smsBought = SmsBuy::listByAccount($id);
        }

        $viewConfig = array();
        if (!empty($historyAll) || !empty($smsBought)) {
            $label = array();
            $rowsHist = array();
            // prepara os dados
            foreach ($historyAll as $historyItem) {
                $this->translateHistoryData($historyItem);

                $hiID = $historyItem['id'];

                $rowsHist[$hiID][0] =
                        $this->setHistoryRows($historyItem);


                $dtReg = new Zend_Date($historyItem['dt_register'], 'pt_BR');
                $label[$hiID][0] = $dtReg->toString(Util::DATE_FORMAT);
                $label[$hiID][1] = $dtReg->toString(Util::TIME_FORMAT);
                $label[$hiID][2] = $historyItem['in_send_option'];
            }

            $label = array_values($label);
            $i = 0;
            foreach ($rowsHist as $eaHist) {
                $sendOption = $label[$i][2];
                $sendOptionLabel = Accounts::getSendOptionName($sendOption);

                $viewConfig['group'][$i]['label'] =
                        'Data: ' . $label[$i][0]
                        . ' - Hora: ' . $label[$i][1]
                        . ' - Opção de envio: ' . $sendOptionLabel;


                $viewConfig['group'][$i]['data'][] =
                        $this->setTableDataByOption($sendOption, $eaHist);

                $i++;
            }

            if ($account->isOptSMS()) {
                ///
                $rowsCred = array();
                $totCred = 0;
                foreach ($smsBought as $eaBuy) {
                    $dtcr = new Zend_Date($eaBuy->dt_credit, 'pt_BR');
                    $dt = $dtcr->toString(Util::DATE_FORMAT);
                    $tm = $dtcr->toString(Util::TIME_FORMAT);
                    // list($d, $t) = explode(' ', $eaBuy->dt_credit);
                    // $aBuy = $eaBuy->getAccountBuyer();
                    /* muito acesso ler um por um? de fato,
                     * mas resolvemos isso depois na persistência mesmo,
                     * fazendo um cache ou puxando mais dados
                     */
                    $rowsCred[] = array(
                        $dt,
                        $tm,
                        $eaBuy->qty,
                    );
                    $totCred += $eaBuy->qty;
                }

                $viewConfig['group'][$i]['label'] = 'Compra de SMSs';
                $viewConfig['group'][$i]['data'][] = array(
                    'type' => 'table',
                    'header' => array('Data', 'Hora', 'Quantidade'),
                    'rows' => $rowsCred,
                );
            }
        } else {
            $viewConfig['data'] = array(
                0 => array(
                    'type' => 'message',
                    'message' => '<div id="reportInfo">'
                    . 'Não existem programações.</div>'
                )
            );
        }
        $this->view->title =
                'Histórico: ' . $account->name
                . ' - ' . $account->cell_phone;
        $this->view->type = 'report';
        $this->view->config = $viewConfig;
    }

    private function setTableDataByOption($sendOption, $r) {
        $ret = array();
        if ($sendOption == Accounts::SEND_OPTION_JAVA) {
            $ret = array(
                'type' => 'table',
                'header' => array(
                    'Descrição',
                    'Data Inicio',
                    'Horários',
                    'Lembrete',
                    'Frequência',
                    'Dia(s)'),
                'rows' => $r
            );
        } else if ($sendOption == Accounts::SEND_OPTION_SMS) {
            $ret = array(
                'type' => 'table',
                'header' => array(
                    'Descrição',
                    'Data Início',
                    'Horários',
                    'Frequência',
                    'Dia(s)'),
                'rows' => $r
            );
        } else { // Accounts::SEND_OPTION_LAB
            $ret = array(
                'type' => 'table',
                'header' => array(
                    'Descrição',
                    'Data Início',
                    'Horários',
                    'Frequência',
                    'Dia(s)'),
                'rows' => $r
            );
        }
        return $ret;
    }

    /**
     *
     * @param array $historyItem array de hashes vindos do
     *      banco de dados.
     * @return array $rows array para retornar os dados de cada coluna
     */
    private function setHistoryRows($historyItem) {
        $compl = '-';
        switch ($historyItem['in_frequency']) {
            case 2: // semanal
                $compl = Programming::weekDaysToString($historyItem);
                break;
            case 3: // mensal
                $compl = Programming::getDaysForDescription($historyItem['id']);
                //$compl = 'm:'.$historyItem['id'];
                break;
            case 4: // anual
                $compl = Programming::getMonthsForDescription($historyItem['id']);
                //$compl = 'a:'.$historyItem['id'];
                break;
        }

        $ret = array();
        $dtStart = new Zend_Date($historyItem['dt_start'], 'pt_BR');
        if ($historyItem['in_send_option'] == Accounts::SEND_OPTION_JAVA) {
            $ret = array(
                0 => $historyItem['description'],
                1 => $dtStart->toString(Util::DATE_FORMAT),
                2 => $historyItem['time'],
                3 => $historyItem['in_repetition'],
                4 => $compl);
        } else {
            // Accounts::SEND_OPTION_SMS) ou Accounts::SEND_OPTION_LAB
            $ret = array(
                0 => $historyItem['description'],
                1 => $dtStart->toString(Util::DATE_FORMAT),
                2 => $historyItem['time'],
                3 => $historyItem['in_repetition'],
                4 => $compl);
        }
        return $ret;
    }

    /**
     *
     * @param <type> $historyItem Converte alguns valores numéricos em string
     * para apresentação
     */
    private function translateHistoryData(&$historyItem) {
        switch ($historyItem['in_repetition']) {
            case 0:
                $historyItem['in_repetition'] = 'Nenhuma';
                break;
            case 1:
                $historyItem['in_repetition'] = 'Com frequência';
                break;
            case 2:
                $historyItem['in_repetition'] = 'Com interrupção';
                break;
        }
        if ($historyItem['in_repetition'] == 'Com frequência') {
            switch ($historyItem['in_frequency']) {
                case 1:
                    $historyItem['in_repetition'] = 'Diária';
                    break;
                case 2:
                    $historyItem['in_repetition'] = 'Semanal';
                    break;
                case 3:
                    $historyItem['in_repetition'] = 'Mensal';
                    break;
                case 4:
                    $historyItem['in_repetition'] = 'Anual';
                    break;
            }
        } else {
            $historyItem['in_frequency'] = null;
        }

        $historyItem['reminder'] =
                ($historyItem['reminder'] == null) ? 'Sem lembrete' : 'a cada ' . $historyItem['reminder'] . ' min';
    }

    public function historypdfAction() {
        $id = (int) $this->getRequest()->getParam('id');

        $accoutProgrammings = Programming::getProgrammingByAccount(
                array(
                    'id', 'dt_register', 'in_send_option', 'description',
                    'dt_start', 'time', 'reminder', 'in_frequency',
                    'in_repetition', 'in_sunday', 'in_monday', 'in_tuesday',
                    'in_wednesday', 'in_thurday', 'in_friday', 'in_saturday',
                    'id_description'),
                $id);

        $account = new Accounts(array('id' => $id));
        $account->read(array('name', 'cell_phone'));
        foreach ($accoutProgrammings as $v) {

            switch ($v['in_repetition']) {
                case 0:
                    $v['in_repetition'] = 'Nenhuma';
                    break;
                case 1:
                    $v['in_repetition'] = 'Com frequência';
                    break;
                case 2:
                    $v['in_repetition'] = 'Com interrupção';
                    break;
            }
            if ($v['in_repetition'] == 'Com frequência') {
                switch ($v['in_frequency']) {
                    case 1:
                        $v['in_repetition'] = 'Diária';
                        break;
                    case 2:
                        $v['in_repetition'] = 'Semanal';
                        break;
                    case 3:
                        $v['in_repetition'] = 'Mensal';
                        break;
                    case 4:
                        $v['in_repetition'] = 'Anual';
                        break;
                }
            } else {
                $v['in_frequency'] = null;
            }

            $compl = '-';
            switch ($v['in_frequency']) {
                case 2: // semanal
                    $compl = Programming::weekDaysToString($v);
                    break;
                case 3: // mensal
                    $compl = Programming::getDaysForDescription($v['id']);
                    break;
                case 4: // anual
                    $compl = Programming::getMonthsForDescription($v['id']);
                    break;
            }

            $label[$v['id']] = array($v['dt_register'], $v['in_send_option']);
            $tm = new Zend_Date($v['dt_start'], 'pt_BR');
            $tabelas[$v['id']][] = array(
                0 => $v['description'],
                1 => $tm->get(Util::DATE_FORMAT),
                2 => $v['time'],
                3 => $v['in_repetition'],
                4 => $compl);
        }
        $tabelas = array_values($tabelas);
        $label = array_values($label);

        require_once 'Zend/Pdf.php';
        $pdf = new Zend_Pdf();

        //Incluir uma Página
        $page = $this->newPdfPage();

        $line = 600;
        $line2 = 700;
        $line3 = 650;
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 11);
        $pageNumber = 1;

        foreach ($tabelas as $key => $valores) {

            if (($line3 < 72) || ($line2 < 72)) {
                array_push($pdf->pages, $page);
                $page = $this->newPdfPage();
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $line = 600;
                $line2 = 700;
                $line3 = 650;
            }

            $sendOption = $label[$key][1] == 1 ? 'Aplicativo Java' : 'SMS';

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $page->setFont($font, 12);
            $page->drawText('Data:', 31, $line2);

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $page->setFont($font, 11);
            $tm = new Zend_Date($label[$key][0], 'pt_BR');
            $page->drawText($tm->get(Util::DATE_FORMAT), 65, $line2);

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $page->setFont($font, 12);
            $page->drawText('- Hora:', 125, $line2);

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $page->setFont($font, 11);
            $page->drawText($tm->get(Util::TIME_FORMAT), 169, $line2);

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $page->setFont($font, 12);
            $page->drawText('- Opção de envio:', 217, $line2, 'UTF-8');

            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $page->setFont($font, 11);
            $page->drawText($sendOption, 325, $line2);


            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $page->setFont($font, 12);
            $page->drawLine(30, $line2 - 15, 570, $line2 - 15);
            $page->drawText('Descrição', 31, $line3 + 20, 'UTF-8');
            $page->drawText('Data Início', 195, $line3 + 20, 'UTF-8');
            $page->drawText('Horários', 265, $line3 + 20, 'UTF-8');
            $page->drawText('Frequência', 400, $line3 + 20, 'UTF-8');
            $page->drawText('Dias', 495, $line3 + 20, 'UTF-8');
            $page->drawLine(30, $line3 + 10, 570, $line3 + 10);


            foreach ($valores as $valor) {

                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $page->setFont($font, 11);
                $maxLinhasDesta = 0;

                // Descrição
                $linhasDescr = Util::quebraLinhas($valor[0], 25);
                $x = 40;
                $maxLinhasDesta = max($maxLinhasDesta, count($linhasDescr));
                foreach ($linhasDescr as $cadalinha) {
                    $page->drawText($cadalinha, 31, $line + $x, "UTF-8");
                    $x -= 20;
                }

                // Data
                $page->drawText($valor[1], 195, $line + 40, "UTF-8");

                // Horários
                $linhashorarios = Util::quebraLinhas($valor[2], 20);
                $x = 40;
                $maxLinhasDesta = max($maxLinhasDesta, count($linhashorarios));
                foreach ($linhashorarios as $cadalinha) {
                    $page->drawText($cadalinha, 265, $line + $x, "UTF-8");
                    $x -= 20;
                }

                // Frequencia
                $page->drawText($valor[3], 400, $line + 40, "UTF-8");

                // dias
                $linhasCompl = Util::quebraLinhas($valor[4], 15);
                $x = 40;
                $maxLinhasDesta = max($maxLinhasDesta, count($linhasCompl));
                foreach($linhasCompl as $cadalinha) {
                    $page->drawText($cadalinha, 495, $line + $x, "UTF-8");
                    $x -= 20;
                }

                $contTotal = ($maxLinhasDesta) * 25;
                $line = ($line - $contTotal) - 20;

                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $page->setFont($font, 12);
                $line += 25;

                //Paginação
                if ($line < 72) {
                    array_push($pdf->pages, $page);
                    $page = $this->newPdfPage();
                    $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                    $page->setLineColor($color1);
                    $page->setLineWidth(0.5);
                    $line = 650;
                    $line2 = 750;
                    $line3 = 700;
                }
            }
            $line2 = $line + 20; //700
            $line3 = $line2 - 50; //650
            $line = $line3 - 50;
        }

        array_push($pdf->pages, $page);

        //Salvar o PDF
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="Arquivo.pdf"');

        //Mostrar o PDF na tela
        echo $pdf->render();

        exit();
    }

    public function modifyAction() {
        if ($this->getRequest()->isPost()) {
            switch ($this->getRequest()->getPost('command')) {
                case 'delete':
                    $id = (int) $this->getRequest()->getPost('id');
                    $programming = new Programming(
                                    array('id' => $id));
                    $programming->delete();
                    Schedule::delete($id);
                    break;
            }
        }
    }

}
