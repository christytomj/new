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
 * @copyright   Copyright (c) 2008 Númera Soluções e Sistemas Ltda. 
 *              (http://www.numera.com.br)
 * @license     http://www.numera.com.br/license/handson     
 *              HandsOn 1.0 License
 * @version    $Id$
 */


class ScheduleController extends Zend_Controller_Action {
    public function init() {
        $this->_helper->acl->allow(null);
    }


    // pega as programações e cria os sms pendentes.
    public function updateAction() {
        $now = Zend_Date::now();
        Schedule::updateSchedule($now);
        $now->addMinute(30);
        if ($now->isTomorrow()) {
            // novo $now com 10 minutos a mais.
            $now->setTime('00:00:00');

            Schedule::updateSchedule($now);
        }
        // se der tempo, executa tarefas de manutenção
        $this->overTime();
    }

    private function overTime() {
        // se ainda der, revisa os remédios vencidos
        Schedule::returnExpiredCredit();
        
        // se der tempo, manda relatórios
        // este report termina com um exit, deve ser o último.
        Schedule::monthReport();
    }

    //envia os sms pendentes (necessário que estejam na tabela antes, ver metodo updateAction()
    public function sendAction() {
        echo("Enviando: ");
        Schedule::flushScheduledSMS();
        echo(". Enviou!");
        // se der tempo, executa tarefas de manutenção
        $this->overTime();
    }

    public function wsappAction() {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            $app = new Schedule();
            $app->wsapp($data);
        }
    }

    public function monthreportAction() {
        Schedule::monthReport();

        // se der tempo, executa tarefas de manutenção
        $this->overTime();
    }
}
