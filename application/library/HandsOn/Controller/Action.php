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

 
abstract class HandsOn_Controller_Action extends Zend_Controller_Action
{
    //public $ajaxable = array();
    
    /**
     * Class constructor
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        
        $this->_request->setParam('format', 'json');
        $this->_helper
             ->contextSwitch()
             ->addActionContext($request->getActionName(), 'json')
             ->initContext();
    }
    
    protected function _formatFileFormResponseView()
    {
        $xhr = $this->_request->isXmlHttpRequest();
        if (!$xhr) {
            $this->_helper->viewRenderer->setNoRender();
            echo '<textarea>';
            echo Zend_Json::encode(get_object_vars($this->view));
            echo '</textarea>';
            exit();
        }
    }


    /**
     * Pega o usuário atualmente logado
     * @return Users o usuário logado.
     */
    protected function getLoggedUser() {
        $userIdentity = Zend_Auth::getInstance()->getIdentity();
        $idUser = (null != $userIdentity) ? $userIdentity->id : null;
        $user = Users::getUserById($idUser);
        return $user;
    }

    /**
     * Converte um parametro mandado no request para uma Zend_Date
     * @param string $paramNm o nome do parametro (request)
     * @return Zend_Date
     */
    public function dateGetFromParam($paramNm) {
        /** @var $vl Zend_Date */
        $vl = null;
        $parm = $this->getRequest()->getParam($paramNm);
        if (! empty($parm)) {
            $parm = str_replace("_", "/", $parm);
            $vl = new Zend_Date($parm, 'pt_BR');
        }

        return $vl;
    }
}