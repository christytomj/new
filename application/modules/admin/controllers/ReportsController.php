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

class Admin_ReportsController extends HandsOn_Controller_Action {

    public function init() {
        $this->_helper->acl->allow('administrativo');
        $this->_helper->acl->allow('financeiro2');
    }

    public function indexAction() {
        $columns = array(
            'name' => array('Nome', 230),
            'email' => array('E-mail', 230),
            'action' => array('Ações', 50, false)
        );
        $buttons = array(
            array('export', 'reports/export'),
            array('exportall', 'reports/exportall'),
            array('viewselect', 'reports/viewselect'),
                //   array('multiview', 'subscribers/multiview', 'Ver')
        );


        $this->view->title = 'Relatório de Envios';
        $this->view->type = 'list';
        $grid = $this->view->gridConfig($columns, array(), $buttons,
                                        'reports/list');
        $grid['advancedSearch'] = true;
        $this->view->config = $grid;
    }

    public function listAction() {
        if ($this->getRequest()->isPost()) {
            $postValues = $this->getRequest()->getPost();
            if (!empty($postValues['query'])) {
                $filter = Zend_Json::decode($postValues['query']);
                /** @var Zend_Date */
                $start = $end = null;
                if (!empty($filter['start'])) {
                    $start = new Zend_Date($filter['start'], 'pt_BR');
                    $filter['start'] = $start->get(Zend_Date::TIMESTAMP);
                }
                if (!empty($filter['end'])) {
                    $end = new Zend_Date($filter['end'], 'pt_BR');
                    $filter['end'] = $end->get(Zend_Date::TIMESTAMP);
                }

                $postValues['sortColumns'] = array(
                    'name' => 'name', 'email' => 'email');
                //$postValues['filterColumns'] = Zend_Json::decode($postValues['query']);
                //$postValues['filterColumns'] = array('name' => 'name', 'dt_start' => 'dt_start', 'dt_end' => 'dt_end');
                $values = $this->view->gridValues($postValues);

                //$category = $this->getRequest()->getParam('category');
                $subscribers = new Subscribers();
                $subscriberList = $subscribers->get(
                                array('id', 'name', 'email'), $values, 'report',
                                $filter);

                $rows = array();
                //$date = new Zend_Date();
                foreach ($subscriberList as $subscriber) {
                    //$date->set($subscriber['dateModification'], Zend_Date::TIMESTAMP);
                    $rows[] = array(
                        'id' => $subscriber['id'],
                        'cell' => array(
                            $subscriber['name'],
                            //$subscriber['code'],
                            $subscriber['email'],
                            //$date->get("dd'/'MM'/'YYYY"),
                            $this->view->listAction(
                                    array(
                                'reports', 'viewselect',
                                'start', $start ? $start->get('dd_MM_yyyy') : '',
                                'end', $start ? $end->get('dd_MM_yyyy') : '',
                                'ids', $subscriber['id'],
                                    ), 'view')
                        )
                    );
                }
                $this->view->page = $values['page'];
                $this->view->total = $subscribers->count($values['filterColumn'],
                                                         $values['filter'],
                                                         'report', $filter);
                $this->view->rows = $rows;
            } else {
                //print_r('oi');
                //exit();
                $postValues['sortColumns'] = array('name' => 'name', 'email' => 'email');
                $values = $this->view->gridValues($postValues);
                $rows = array();
                $this->view->page = $values['page'];
                $this->view->total = 0;
                $this->view->rows = $rows;
            }
        }
    }

    /**
      //    public function searchAction() {
      //        $form = new ReportSearchForm();
      //        $form->setAction($this->view->baseUrl() . '/admin/reports/');
      //
      //        $this->view->type = 'form';
      //        $this->view->config = array('form' => $form->render());
      //        $this->_formatFileFormResponseView();
      //    }
     *
     */
    public function exportAction() {
        $add = $this->getRequest()->getParam('add');
        $multiplyj = $this->getRequest()->getParam('multiplyj');
        $multiplys = $this->getRequest()->getParam('multiplys');
        $start = $this->dateGetFromParam('start');
        $end = $this->dateGetFromParam('end');
        $ids = $this->getRequest()->getParam('ids');
        $maturity = $this->getRequest()->getParam('maturity');

        $maturity = str_replace("_", "/", $maturity);

        $dateDay = new Zend_Date();

        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="programa'
                . $dateDay->getTimestamp() . '.csv"');

        $checkAdd = $add == null ? 0 : str_replace(".", "", $add);
        if (!is_numeric($checkAdd)) {
            echo 'Valor de soma inválido!';
            error_log(__METHOD__. ':saindo.'.'Valor de soma inválido!');
            exit();
        }

        $checkMultiply =
                $multiplyj == null ? 0 : str_replace(".", "", $multiplyj);
        if (!is_numeric($checkMultiply)) {
            echo 'Valor de multiplicação Java inválido!';
            error_log(__METHOD__. ':saindo.'.'Valor de multiplicação Java inválido!');
            exit();
        }
        $checkMultiply =
                $multiplys == null ? 0 : str_replace(".", "", $multiplys);
        if (!is_numeric($checkMultiply)) {
            echo 'Valor de multiplicação SMS inválido!';
            error_log(__METHOD__. ':saindo.'.'Valor de multiplicação SMS inválido!');
            exit();
        }

        $checkMaturity =
                $maturity == null ? 0 : str_replace("/", "", $maturity);
        if (!is_numeric($checkMaturity)) {
            echo 'Data de Vencimento inválida';
            error_log(__METHOD__. ':saindo.'.'Data de Vencimento inválida');
            exit();
        }

        try {
            $reports = new Programming();
            $reports = $reports->reportCSV(
                            $ids, $start, $end, $add, $multiplyj, $multiplys);
        } catch (Exception $ex) {
            error_log(print_r($ex, true));
        }

        //print("ALALAO ");
        //print_r($reports);
        //exit();

        $out = '';
        $out .= "Cliente|Valor|Vencimento|" . "\n";
        foreach ($reports as $key => $valor) {
            $valor['valor'] = explode(".", $valor['valor']);
            if (isset($valor['valor'][1])) {
                $valor['valor'] = $valor['valor'][0] . ',' . str_pad($valor['valor'][1],
                                                                     2, 0,
                                                                     STR_PAD_RIGHT);
            } else {
                $valor['valor'] = $valor['valor'][0] . ',00';
            }
            $out .= $valor['code'] . '|' . $valor['valor'] . '|' . $maturity . '|';

            $out .= "\n";
        }
        echo $out;
        exit();
    }

    public function exportallAction() {
        $add = $this->getRequest()->getParam('add');
        $multiplyj = $this->getRequest()->getParam('multiplyj');
        $multiplys = $this->getRequest()->getParam('multiplys');
        /** @var Zend_Date $start */
        $zstart = $this->dateGetFromParam('start');
        /** @var Zend_Date $end */
        $end = $this->dateGetFromParam('end');
        $name = $this->getRequest()->getParam('name');
        $maturity = $this->getRequest()->getParam('maturity');

        $maturity = str_replace("_", "/", $maturity);

        $dateDay = Zend_Date::now('pt_BR')->toString('Y_M_d');

        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="programa'
                . $dateDay . '.csv"');

        $checkAdd = $add == null ? 0 : str_replace(".", "", $add);
        if (!is_numeric($checkAdd)) {
            echo 'Valor de soma inválido!';
            exit();
        }

        $checkMultiply =
                $multiplyj == null ? 0 : str_replace(".", "", $multiplyj);
        if (!is_numeric($checkMultiply)) {
            echo 'Valor de multiplicação (Aplicativo Java) inválido!';
            exit();
        }

        $checkMultiply =
                $multiplys == null ? 0 : str_replace(".", "", $multiplys);
        if (!is_numeric($checkMultiply)) {
            echo 'Valor de multiplicação (SMS) inválido!';
            exit();
        }

        $checkMaturity =
                $maturity == null ? 0 : str_replace("/", "", $maturity);
        if (!is_numeric($checkMaturity)) {
            echo 'Data de Vencimento inválida';
            exit();
        }

        $reports = new Programming();
        $reports = $reports->reportAllCSV(
                        $name, $zstart, $end, $add, $multiplyj, $multiplys);

//                        print_r($reports);
//                        exit();

        echo "Cliente|Valor|Vencimento|" . "\n";
        foreach ($reports as $key => $valor) {

            //$valor['valor'] = str_replace(".", ",", $valor['valor']);
            $valor['valor'] = explode(".", $valor['valor']);
            if (isset($valor['valor'][1])) {
                $valor['valor'] = (0+$valor['valor'][0])
                        . ',' . str_pad($valor['valor'][1], 2, 0, STR_PAD_RIGHT);
            } else {
                $valor['valor'] = (0+$valor['valor'][0]) . ',00';
            }
            echo $valor['code'] . '|' . $valor['valor'] . '|' . $maturity . '|';

            echo "\n";
        }
        //print_r($valor); exit();
        exit();
    }

    public function viewAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $start = $this->dateGetFromParam('start');
        $end = $this->dateGetFromParam('end');

        $subscriber = new Users(array('id' => $id));
        $subscriber->read(array('name', 'email'));

        $sendReports = new Programming();
        $sendReports = $sendReports->sendprogrammingReport(
                        array('id', 'dt_register', 'in_send_option', 'name', 'cell_phone'),
                        $id, $start, $end);
        //print_r($sendReports);
        //exit();
        $totJava = 0;
        $totSms = 0;
        $rowsJava = array();
        $rowsSMS = array();
        if (!empty($sendReports)) {
            foreach ($sendReports as $t) {
                $ddd = substr($t['cell_phone'], 0, 2);
                $celular = substr($t['cell_phone'], 2);
                $dtm_reg = new Zend_Date($t['dt_register'], 'pt_BR');
                if ($t['in_send_option'] == Accounts::SEND_OPTION_JAVA) {
                    $rowsJava[] = array(
                        0 => $dtm_reg->toString(Util::DATE_FORMAT),
                        1 => $dtm_reg->toString(Util::TIME_FORMAT),
                        2 => $t['name'],
                        3 => '(' . $ddd . ')' . $celular);
                    $totJava++;
                } else {
                    $rowsSMS[] = array(
                        0 => $dtm_reg->toString(Util::DATE_FORMAT),
                        1 => $dtm_reg->toString(Util::TIME_FORMAT),
                        2 => $t['name'],
                        3 => '(' . $ddd . ')' . $celular,
                        4 => 1
                    );
                    $totSms++;
                }
            }
            //print_r($rows);
            //exit();

            $config['group'][0]['label'] = 'Assinante: ' . $subscriber->name
                    . ' - ' . $subscriber->email;

            $config['group'][1]['label'] = 'Opção de envio: Aplicativo Java';
            $config['group'][1]['data'][] = array(
                'type' => 'table',
                'header' => array('Data', 'Hora', 'Nome', 'Celular'),
                'rows' => $rowsJava,
            );
            $config['group'][1]['data'][] = array(
                'type' => 'list',
                'items' => array(
                    array('label' => 'Total de programações',
                        'value' => $totJava)),
            );

            $config['group'][2]['label'] = 'Opção de envio: SMS';
            $config['group'][2]['data'][] = array(
                'type' => 'table',
                'header' =>
                array('Data', 'Hora', 'Nome', 'Celular', 'Quantidade'),
                'rows' => $rowsSMS,
            );
            $config['group'][2]['data'][] = array(
                'type' => 'list',
                'items' => array(
                    array('label' => 'Total de Programações',
                        'value' => $totSms)),
            );

            $smsBought = SmsBuy::listByBuyer($id);
            $rowsCred = array();
            $totCred = 0;
            foreach ($smsBought as $eaBuy) {
                $dtc = new Zend_Date($eaBuy->dt_credit, 'pt_BR');
                $d = $dtc->toString(Util::DATE_FORMAT);
                $t = $dtc->toString(Util::TIME_FORMAT);
                $aBuy = $eaBuy->getAccountBuyer();
                /* muito acesso ler um por um? de fato, mas resolvemos isso
                 * depois na persistência mesmo, fazendo um cache ou puxando
                 * mais dados
                 */
                $rowsCred[] = array(
                    $d,
                    $t,
                    $aBuy->name,
                    $aBuy->cell_phone,
                    $eaBuy->qty,
                );
                $totCred += $eaBuy->qty;
            }
            $config['group'][3]['label'] = 'Créditos Comprados';
            $config['group'][3]['data'][] = array(
                'type' => 'table',
                'header' =>
                array('Data', 'Hora', 'Nome', 'Celular', 'Quantidade'),
                'rows' => $rowsSMS,
            );
            /*
              $config['group'][3]['data'][] = array(
              'type' => 'list',
              'items' => array(
              array('label' => 'Total de Programações',
              'value' => $totCred)),
              );
             */

            $this->view->title = 'Programações e Créditos Comprados: ';
            $this->view->type = 'report';
            $this->view->config = $config;
        } else {
            $config['data'] = array(
                0 => array(
                    'type' => 'message',
                    'message' => '<div id="reportInfo">Não existem programações neste período.</div>'
                )
            );

            $this->view->title = 'Programações Enviadas: ';
            $this->view->type = 'report';
            $this->view->config = $config;
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
        $imagem = Zend_Pdf_Image::imageWithPath(APPLICATION_PATH
                        . '/../public/images/public/logo.png');
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

        return $page;
    }

    public function viewpdfAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $start = $this->dateGetFromParam('start');
        $end = $this->dateGetFromParam('end');

        $sendReports = new Programming();
        $valores = $sendReports->sendprogrammingReport(
                        array('id', 'dt_register', 'in_send_option', 'name', 'cell_phone'),
                        $id, $start, $end);

        //print_r($valores);
        //exit();
        $subscriber = new Users(array('id' => $id));
        $subscriber->read(array('name', 'email'));

        //Relátorio de Envio Individual

        require_once 'Zend/Pdf.php';
        $pdf = new Zend_Pdf();

        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

        //Incluir uma Página
        $page = $this->newPdfPage();

        $page->setFont($fontb, 14);
        $page->drawText('Assinante:', 41, 700);

        $page->setFont($font, 12);
        $page->drawText($subscriber->name . ' - ' . $subscriber->email, 122, 700);

        $page->drawLine(30, 625, 570, 625);
        $page->drawLine(30, 675, 570, 675);
        $page->setFont($fontb, 14);
        $page->drawText('Data', 41, 650);
        $page->drawText('Hora', 110, 650);
        $page->drawText('Nome', 180, 650);
        $page->drawText('Celular', 406, 650);
        $page->drawText('Envio', 506, 650, 'UTF-8');

        $line = 600;
        $tableLine = 575;
        $page->setFont($font, 12);

        foreach ($valores as $key => $valor) {
            $ddd = substr($valor['cell_phone'], 0, 2);
            $celular = substr($valor['cell_phone'], 2);
            $sendOption =
                    $valor['in_send_option'] == Accounts::SEND_OPTION_JAVA ? 'Aplicativo Java'
                        : 'SMS';
            $array[$key] = array(
                $page->drawText(
                        date("d/m/Y", $valor['dt_register']), 41, $line),
                $page->drawText(
                        date("G:i:s", $valor['dt_register']), 110, $line),
                $page->drawText(
                        mb_substr($valor['name'], 0, 36), 180, $line),
                $page->drawText('(' . $ddd . ')' . $celular, 406, $line),
                $page->drawText($sendOption, 506, $line),
                    //$page->drawLine(30, $tableLine, 570, $tableLine),
            );
            $line -= 25;
            $tableLine -= 25;
            if ($tableLine < 72) {
                //Criar novo pdf
                // $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                // $page->setFont($font, 14);
                // $page->drawText('Pagina:'." ".$pageNumber, 41, $line);
                array_push($pdf->pages, $page);
                $page = $this->newPdfPage();
                $page->drawLine(30, 725, 570, 725);
                $page->drawLine(30, 675, 570, 675);
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $page->setFont($fontb, 14);
                $page->drawText('Data', 41, 650);
                $page->drawText('Hora', 110, 650);
                $page->drawText('Nome', 180, 650);
                $page->drawText('Celular', 406, 650);
                $page->drawText('Envio', 506, 650, 'UTF-8');
                $line = 600;
                $tableLine = 575;
                $page->setFont($font, 12);
            }
        }
        $page->setFont($fontb, 14);
        $page->drawText('Total de Programações: ', 41, $tableLine, 'UTF-8');
        $page->setFont($font, 13);
        $page->drawText($key + 1, 210, $tableLine);
        array_push($pdf->pages, $page);

        //Salvar o PDF
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="Arquivo.pdf"');

        //Mostrar o PDF na tela
        echo $pdf->render();

        exit();
    }

    public function viewselectAction() {
        $ids = $this->getRequest()->getParam('ids');
        $start = $this->dateGetFromParam('start');
        $end = $this->dateGetFromParam('end');

        $start = empty($start) ? null : new Zend_Date($start, 'pt_BR');

        $end = empty($end) ? null : new Zend_Date($end, 'pt_BR');
        /* @var $countTotal int total de programações */
        $countTotal = 0;
        $config = array();

        $oVal = $this->getAndFormatProgrammings($ids, $start, $end, $countTotal);

        if ($countTotal) {
            $config['group'][] = $oVal;
            $config['group'][] = array(
                'label' => '',
                'data' => array(array(
                    'type' => 'list',
                    'items' => array(array(
                        'label' => 'Total de programações',
                        'value' => $countTotal
                    )),
                ))
            );
        }

        //// Créditos
        $countTotal = 0;
        $oVal = $this->getAndFormatCredits($ids, $start, $end, $countTotal);

        if (!empty($oVal)) {
            $config['group'][] = $oVal;
        }

        if (empty($config)) {
            $config['data'] = array(array(
                    'type' => 'message',
                    'message' =>
                    '<div id="reportInfo">Não existem programações neste '
                    . 'período.</div>'
                    ));
        }

        $this->view->title = 'Programações Enviadas: ';
        $this->view->type = 'report';
        $this->view->config = $config;
    }

    private function getAndFormatCredits(
    $ids, $start, $end, &$countTotal) {
        $sendReports = $label = array();
        $ids = explode(",", $ids);
        $i = 0;
        $oVal = array();
        $rows = array();
        $oVal['label'] =
                'Créditos SMS comprados';

        foreach ($ids as $id) {
            $subscr = Users::getUserById($id);
            $accs = Accounts::getAccountBySubscriber($id);
            $totQty = 0;
            foreach ($accs as $acc) {
                if ($acc->isOptJava()) {
                    continue;
                }
                $buys = SmsBuy::listByAccount($acc->id, $start, $end);
                foreach ($buys as $buy) {
                    $dt_credit = new Zend_Date($buy->dt_credit, 'pt_BR');
                    $rows[] = array(
                        $dt_credit->get(Util::DATE_FORMAT),
                        $dt_credit->get(Util::TIME_FORMAT),
                        $acc->name,
                        $acc->cell_phone,
                        $buy->qty
                    );
                    $totQty += $buy->qty;
                }
            }
            $oVal['data'][] = array(
                'type' => 'table',
                'header' => array(
                    'Data',
                    'Hora',
                    'Nome',
                    'Celular',
                    'Quantidade'),
                'rows' => $rows
            );
            $oVal['data'][] = array(
                'type' => 'list',
                'items' => array(array(
                        'label' => 'Total de créditos comprados por '
                        . $subscr->name,
                        'value' => $totQty)),
            );
        }


        return($oVal);
    }

    private function getAndFormatProgrammings(
            $ids, $start, $end, &$countTotal) {
        $sendReports = $label = array();
        $ids = explode(",", $ids);
        $i = 0;
        $oVal = array();
        foreach ($ids as $id) {
            $sendR = new Programming();
            $sendReports[$i] = $sendR->sendprogrammingReport(
                            null, $id, $start, $end);

            $subscriber = new Users(array('id' => $id));
            $subscriber->read(array('id', 'name', 'email'));
            $label[$i] = $subscriber;
            ++$i;
        }
        if (!empty($sendReports)) {
            $oVal = $this->makeSubscriberReportGroup(
                            $sendReports, $label, $countTotal);
        }

        return($oVal);
    }

    private function makeSubscriberReportGroup(
            $sendReports, $label, &$countTotal) {
        $oVal = array();
        foreach ($sendReports as $key => $subscriberProgramming) {
            if (!empty($subscriberProgramming)) {
                $rows = $this->makeProgrammingReportRow(
                                $subscriberProgramming);
                $count = count($rows);


                $oVal['label'] =
                        'Assinante: ' . $label[$key]->name
                        . ' - ' . $label[$key]->email;

                $oVal['data'][] = array(
                    'type' => 'table',
                    'header' => array(
                        'Data',
                        'Hora',
                        'Nome',
                        'Celular',
                        'Opção de envio'),
                    'rows' => $rows
                );
                $oVal['data'][] = array(
                    'type' => 'list',
                    'items' => array(array(
                            'label' => 'Total de programações de '
                            . $label[$key]->name,
                            'value' => $count)),
                );
                $countTotal = $countTotal + $count;
            }
            $rows = array();
        }
        return $oVal;
    }

    private function makeProgrammingReportRow($subscriberProgramming) {
        $rows = array();
        foreach ($subscriberProgramming as $k => $v) {
            $dtRegister = new Zend_Date($v['dt_register'], 'pt_BR');
            $ddd = substr($v['cell_phone'], 0, 2);
            $celular = substr($v['cell_phone'], 2);
            $sendOption =
                    $v['in_send_option'] == Accounts::SEND_OPTION_JAVA ? 'Aplicativo Java'
                        : 'SMS';
            $rows[] = array(
                0 => $dtRegister->toString(Util::DATE_FORMAT),
                1 => $dtRegister->toString(Util::TIME_FORMAT),
                2 => $v['name'],
                3 => '(' . $ddd . ')' . $celular,
                4 => $sendOption);
        }
        return $rows;
    }

    public function viewselectpdfAction() {
        //        //RELATÓRIO DE VÁRIOS ASSINANTES 99% PRONTO
        $ids = $this->getRequest()->getParam('ids');
        $start = $this->dateGetFromParam('start');
        $end = $this->dateGetFromParam('end');

        $cont1 = 0;
        $contTotal = 0;
        $sendReports = array();
        $ids = explode(",", $ids);
        $allProgrReports = array();
        $label = array();
        $i = 0;
        foreach ($ids as $id) {
            $sendR = new Programming();
            $allProgrReports[$i] = $sendR->sendprogrammingReport(
                            array('id', 'dt_register', 'in_send_option', 'name',
                        'cell_phone'), $id, $start, $end);

            $subscriber = Users::getUserById($id);
            $label[$i] = $subscriber->name.' - '.$subscriber->email;
            $i++;
        }

        require_once 'Zend/Pdf.php';
        $pdf = new Zend_Pdf();

        //Incluir uma Página
        $page = $this->newPdfPage();

        $font = Zend_Pdf_Font::fontWithName(
                        Zend_Pdf_Font::FONT_HELVETICA);
        $fontb = Zend_Pdf_Font::fontWithName(
                        Zend_Pdf_Font::FONT_HELVETICA_BOLD);

        $line = 600;
        $line2 = 700;
        $line3 = 665;
        //$contTotal = 0;
        //$cont2 = 1;

        foreach ($allProgrReports as $key => $userProgrReport) {

            if (($line3 < 72) || ($line2 < 72)) {
                array_push($pdf->pages, $page);
                $page = $this->newPdfPage();
                $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                $page->setLineColor($color1);
                $page->setLineWidth(0.5);
                $line = 600;
                $line2 = 700;
                $line3 = 665;
            }

            $page->setFont($fontb, 11);
            $page->drawText('Assinante:', 41, $line2 + 20, 'UTF-8');

            $page->setFont($fontb, 11);
            $page->drawText('Total de Programações: ', 41, $line2, 'UTF-8');

            $page->setFont($font, 11);
            $page->drawText($label[$key], 100,
                            $line2 + 20);

            //            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            //            $page->setFont($font, 11);
            //            $page->drawText($cont2,180,$line2);
            //$cont2 = 0;

            $page->setFont($fontb, 12);
            $page->drawLine(30, $line2 - 15, 570, $line2 - 15);
            $page->drawText('Data', 41, $line3, 'UTF-8');
            $page->drawText('Hora', 120, $line3, 'UTF-8');
            $page->drawText('Nome', 210, $line3, 'UTF-8');
            $page->drawText('Celular', 360, $line3, 'UTF-8');
            $page->drawText('Opção de Envio', 465, $line3, 'UTF-8');
            $page->drawLine(30, $line3 - 10, 570, $line3 - 10);


            foreach ($userProgrReport as $valor) {
                $dt_register = new Zend_Date($valor['dt_register'], 'pt_BR');
                $ddd = substr($valor['cell_phone'], 0, 2);
                $celular = substr($valor['cell_phone'], 2);
                $sendOption = $valor['in_send_option'] == 1 ? 'Aplicativo Java' : 'SMS';

                $page->setFont($font, 11);
                $page->drawText(
                        $dt_register->get(Util::DATE_FORMAT), 41, $line + 25,
                                          "UTF-8");
                $page->drawText(
                        $dt_register->get(Util::TIME_FORMAT), 120, $line + 25,
                                          "UTF-8");
                $page->drawText(
                        mb_substr($valor['name'], 0, 22), 210, $line + 25,
                                  'UTF-8');
                $page->drawText(
                        '(' . $ddd . ')' . $celular, 360, $line + 25, "UTF-8");
                $page->drawText(
                        $sendOption, 465, $line + 25, "UTF-8");
                $line -= 25;
                $cont1 += 1;
                $contTotal += 1;

                //Paginação
                if ($line < 72) {
                    array_push($pdf->pages, $page);
                    $page = $this->newPdfPage();
                    $color1 = new Zend_Pdf_Color_Html('#4B4B4B');
                    $page->setLineColor($color1);
                    $page->setLineWidth(0.5);
                    $line = 700;
                    $line2 = 800;
                    $line3 = 750;
                }
                $cont2 = $cont1;
            }
            $cont1 = 0;
            $page->setFont($font, 11);
            $cont2 = 0;
            $page->drawText($cont2, 180, $line2);
            $line2 = $line - 25; //700
            $line3 = $line2 - 30; //650
            $line = $line3 - 50;
        }
        //$font = Zend_Pdf_Font::fontWithName(
        //        Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        //$page->setFont($font, 11);
        //$page->drawText('Total de Programações: ',41 ,$line2-25, 'UTF-8');
        //$font = Zend_Pdf_Font::fontWithName(
        //        Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        //$page->setFont($font, 11);
        //$page->drawText($contTotal ,175 ,$line2-25, 'UTF-8');

        array_push($pdf->pages, $page);


        $page = Schedule::fazPaginaPdfCreditosComprados($ids, $label, $start, $end);

        array_push($pdf->pages, $page);
        //Salvar o PDF
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="Arquivo.pdf"');

        //Mostrar o PDF na tela
        echo $pdf->render();

        exit();
    }

}
