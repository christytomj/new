<?php
/**
 * Singular - Academic Resource Planning
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
 * @license    http://www.numera.com.br/license/singular     Singular 1.0 License
 * @version    $Id$
 */

class Subscriber_ErrorController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->acl->allow(null);
    }

    public function errorAction()
    {
        $this->_forward('index', 'index', 'default');
        //        $errors = $this->_getParam('error_handler');
        //        switch ($errors->type) {
        //            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
        //            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        //                // 404 error -- controller or action not found
        //                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
        //                $this->view->errorType = 404;
        //                $this->view->exception = $errors->exception;
        //                break;
        //            default:
        //                throw $errors->exception;
        //                break;
        //        }
    }
}