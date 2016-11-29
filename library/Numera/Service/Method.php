<?php
/**
 * Númera Framework
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
 * @copyright  Copyright (c) 2009 Númera Soluções e Sistemas Ltda. (http://www.numera.com.br)
 * @license    http://www.numera.com.br/license/framework     Númera Framework 1.0 License
 * @version    $Id$
 */

/**
 * Classe abstrata para criação de controles leves geradores de resposta JSON.
 *
 * A classe funciona de modo semelhante ao Zend_Controller_Action,
 * mas para ser mais rápida dispensa todo processo de roteamento, carregamento
 * de plugins, funções auxiliares pré e pós execução da ação (preDispatch, etc.)
 * Então, por exemplo, os parâmetros não estão no objeto "request", mas diretamente
 * na classe, por meio dos métodos getParam(), getParams() e setParams().
 *
 * A limitação de acesso também é feita diretamente, com os métodos setAcl e
 * isAllowed.
 *
 * Respostas para requisições incorretas podem ser geradas via:
 * - notImplemented (erro HTTP/501)
 * - accessDenied (erro HTTP/401)
 *
 * Tenta-se criar comportamento REST, com requisição no formato:
 * http://hostname/serviço/método/argumento1/valor1/argumento2/valor2/etc.
 * Sendo hostname o caminho até resposta para serviços, definido no bootstrap.
 * Serviço é derivado desta classe (Numera_Service_Method),
 * e método a função membro nomeada métodoMethod.
 * Em seguida as barras definem os argumentos e seus valores a serem passados para
 * o serviço solicitado.
 */
abstract class Numera_Service_Method
{
    /**
     * Parâmetros da requisição
     * @var array
     */
    protected $_params = array();

    /**
     * Se é necessário ou não verificar permissão de acesso ao serviço
     * @var bool
     */
    protected $_acl = false;

    /**
     * Nome da classe de funcionalidades, utilizada para verificar permissão
     * de acesso ao serviço
     * @var string
     */
    protected $_functionalitiesClass = null;

    /**
     * Nome da classe de usuário, utilizada para verificar permissão
     * de acesso ao serviço
     * @var string
     */
    protected $_userClass = null;

    /**
     * Objeto de retorno
     * @var stdClass
     */
    public $output;

    /**
     * Construtor
     *
     * Ajusta a variável de retorno como objeto.
     * Se $requestUri for passado, os parâmetros de requisição serão populados.
     *
     * @param array $requestUri
     * @return void
     */
    public function __construct(array $requestUri = array())
    {
        $this->output = new stdClass;
        if (!empty($requestUri)) {
            $this->setParams($requestUri);
        }
    }

    /**
     * Configura os parâmetros
     *
     * Interpreta a requisição transformada em array no formato:
     * 0: módulo
     * 1: controller
     * 2: service
     * 3: parâmtro 1
     * 4: valor 1
     * 5: parâmtro 2
     * 6: valor 2
     * etc.
     *
     * Retorna o próprio objeto para chamada encadeadas de métodos.
     *
     * @param array $requestUri
     * @return Numera_Controller_Action
     */
    public function setParams(array $requestUri)
    {
        $this->_params['module'] = $requestUri[0];
        $this->_params['service'] = $requestUri[1];
        $this->_params['method'] = $requestUri[2];
        unset($requestUri[0]);
        unset($requestUri[1]);
        unset($requestUri[2]);
        foreach ($requestUri as $key => $value) {
            $nextKey = $key + 1;
            if ($key % 2 != 0 && isset($requestUri[$nextKey])) {
                $this->_params[$value] = $requestUri[$nextKey];
            }
        }

        return $this;
    }

    /**
     * Pega um parâmetro
     *
     * A ordem seguida é:
     * 1) Parâmetros do ambiente do usuário (ver {@link setParam()});
     * 2) $_GET;
     * 3) $_POST.
     * Se não for encontrado a chave solicitada, retorna-se o valor de
     * $default (por padrão, null).
     *
     * @param string $key
     * @param mixed $default Valor padrão a ser utilizado se a chave não for encontrada
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        } elseif (isset($_GET[$key])) {
            return $_GET[$key];
        } elseif (isset($_POST[$key])) {
            return $_POST[$key];
        }

        return $default;
    }

    /**
     * Pega um array de parâmetros
     *
     * Pega um array mesclado de parâmetros, com precedência dos parâmetros do
     * ambiente do usuário (ver {@link setParam()}), $_GET, $POST (i.e., valores
     * no ambiente do usuário terão precedência sobre todas outras).
     *
     * @return array
     */
    public function getParams()
    {
        $params = $this->_params;
        if (isset($_GET) && is_array($_GET)) {
            $params += $_GET;
        }
        if (isset($_POST) && is_array($_POST)) {
            $params += $_POST;
        }
        return $params;
    }

    /**
     * Define controle de acesso aos serviços
     *
     * Se este método for chamado, será verificado a permissão de acesso
     * antes da execução do serviço requisitado.
     * Essa verificação é feita a partir de objetos de funcionalidades e de usuário.
     * Assim, é necessário fornecer as classes desses objetos, sendo que elas
     * devem conter os seguintes métodos:
     * - $functionalities::TYPE_PUBLIC
     * - $functionalities::TYPE_RESTRICTED
     * - $functionalities::TYPE_PROTECTED
     * - $functionalities->getResourceType($resource, $privilege)
     * - $user->__construct($userID)
     * - $user->hasFunctionality($resource, $privilege)
     *
     * @param string $functionalitiesClass
     * @param string $userClass
     * @return void
     */
    public function setAcl($functionalitiesClass, $userClass)
    {
        $this->_acl = true;
        $this->_functionalitiesClass = $functionalitiesClass;
        $this->_userClass = $userClass;
    }

    /**
     * Processa a verificação da permissão de acesso ao serviço
     *
     * Retorna verdadeiro se o usuário tiver permissão, ou falso caso contrário.
     *
     * @return boolean
     */
    public function isAllowed()
    {
        if (true == $this->_acl) {
            $resource = $this->getParam('service');
            $privilege = $this->getParam('method');
            $functionalities = new $this->_functionalitiesClass();
            $resourceType = $functionalities->getResourceType($resource, $privilege);

            // Se o recurso não for do tipo público (ou seja, protegido ou restrito),
            // o usuário tem que estar autenticado.
            // Se for restrito, o acesso só é possível se o perfil do usuário der permissão.
            if (Functionalities::TYPE_PUBLIC != $resourceType)
            {
                $userIdentity = Zend_Auth::getInstance()->getIdentity();
                if (null == $userIdentity) {
                    return false;
                }
                if (Functionalities::TYPE_RESTRITED == $resourceType) {
                    $user = new $this->_userClass($userIdentity->id);
                    if (!$user->hasFunctionality($resource, $privilege)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }


    /**
     * Executa a ação
     *
     * Realiza a chamada do controller/service soliticado pela requisição,
     * e imprime o objeto de retorno como JSON.
     *
     * @return void
     */
    public function dispatch() {
        if (false == $this->isAllowed()) {
            self::accessDenied($this->getParam('service'), $this->getParam('method'));
        }
        $method = $this->getParam('method') . 'Method';
        if (!method_exists($this, $method)) {
            self::notImplemented($this->getParam('service'), $this->getParam('method'));
        }
        $this->$method();

        header('Content-Type: application/json');
        echo Zend_Json::encode($this->output);
    }

    /**
     * Dispara resposta de não-implementado
     *
     * Cria uma página tipo 501, avisando que o controller/service fornecido
     * não está implementado.
     *
     * Termina o script atual.
     *
     * @param string $controller
     * @param string $service
     * @return void
     */
    static public function notImplemented($service, $method)
    {
        header('HTTP/1.0 501 Not Implemented');
        echo "<h1>501 - Not Implemented</h1>";
        echo "<p>Page requested: " . htmlentities($service . '/' . $method) . "</p>";
        exit();
    }

     /**
     * Dispara resposta de acesso negado
     *
     * Cria uma página tipo 401, avisando que não há permissão para o
     * recurso/privilégio solicitado.
     *
     * Termina o script atual.
     *
     * @param string $resource
     * @param string $privilege
     * @return void
     */
    static public function accessDenied($resource, $privilege)
    {
        header('HTTP/1.0 401 Unauthorized');
        echo "<h1>401 - Unauthorized</h1>";
        echo "<p>Page requested: " . htmlentities($resource . '/' . $privilege) . "</p>";
        exit();
    }
}
