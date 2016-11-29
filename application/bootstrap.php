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

define('APPLICATION_PATH', realpath(dirname(__FILE__)));
define('UPLOAD_PATH', realpath(dirname(dirname(__FILE__))) . '/data/uploads/');
define('HTMLPURIFIER_PREFIX', realpath(dirname(dirname(__FILE__))) . '/library');

/**
 * Realiza a execução da aplicação.
 */
class Bootstrap {

    /**
     * Instância de Zend_Controller_Front
     * @var Zend_Controller_Front
     */
    private $_frontController = null;

    /**
     * Instância de Zend_Config_Ini
     * @var Zend_Config_Ini
     */
    private $_config = null;

    /**
     * Tipo de execução (produção, desenvolvimento, teste).
     * @var string
     */
    private $_type = 'development';

    /**
     * Construtor
     *
     * Prepara o ambiente: ajusta apresentação dos erros, seta diretórios para
     * inclusão, carrega arquivo de configuração.
     *
     * @return void
     */
    public function __construct($configSection = 'development') {
        // Reporta todos os erros diretamente na tela
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_startup_errors', 1);
        ini_set('display_errors', 1);

        $paths = array(
            APPLICATION_PATH . '/../library',
            APPLICATION_PATH . '/library',
            APPLICATION_PATH . '/models',
        );
        set_include_path(implode(PATH_SEPARATOR, $paths));

        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);

        // Carrega configuração
        $str = APPLICATION_PATH . '/../data/configuration/config.ini';
        $this->_config = new Zend_Config_Ini(
                $str, $configSection);
        Zend_Registry::set('config', $this->_config);

        // Seta fuso horário conforme requerido pelo PHP5
        date_default_timezone_set($this->_config->date_default_timezone);

        // Habilita cache de plugin
        $pluginLoaderCache =
                APPLICATION_PATH . '/../data/cache/pluginLoader.php';
        if (file_exists($pluginLoaderCache)) {
            include_once $pluginLoaderCache;
        }
        if ($configSection == 'production') {
            Zend_Loader_PluginLoader::setIncludeFileCache($pluginLoaderCache);
        }

        $this->_type = $configSection;
    }

    /**
     * Prepara e processa a requisição.
     *
     * @return void
     */
    public function run() {
        error_log('CMD:' . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"]);
        error_log('PRM:' . json_encode($_POST));
        $this->prepare();
        try {
            $this->_frontController->dispatch();
        } catch (Exception $exception) {
            // Ocorreu exceção depois da execução de postdispatch()
            // do ErrorController
            error_log(__METHOD__ . 'EXCEPTION NO CONTROLLER');
            error_log($exception->__toString());
            if ($this->_config->debug == 1) {
                $message = $exception->getMessage();
                $trace = $exception->getTraceAsString();
                echo "<div>Erro: $message<p><pre>$trace</pre></p></div>";
            } else {
                try {
                    $logFile = $this->_config->logFile->error;
                    $log = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
                    $log->debug($exception->getMessage() . "\n"
                            . $exception->getTraceAsString()
                            . "\n-----------------------------");
                } catch (Exception $e) {
                    // Não foi possível fazer o log do erro
                    die("<p>Não foi possível fazer log de um erro!");
                }
            }
        }
    }

    /**
     * Prepara mas não processa a requisição.
     *
     * @return void
     */
    public function prepare() {
        $this->_setupFrontController();
        $this->_setupRoutes();
        $this->_setupView();
        $this->_setupDatabase();
        $this->_setupAcl();
        $this->_setupDebug();
    }

    /**
     * Configura front controller.
     *
     * @return void
     */
    private function _setupFrontController() {
        $this->_frontController = Zend_Controller_Front::getInstance();
        $this->_frontController->throwExceptions(true);
        $this->_frontController->addModuleDirectory(
                APPLICATION_PATH . '/modules');
        $this->_frontController->registerPlugin(
                new HandsOn_Controller_Plugin_LoadModels());
        //$this->frontController->setParam('noErrorHandler', TRUE);
        //$this->_frontController->setControllerDirectory($this->_rootDir
        //      . '/application/controllers');
    }

    private function _setupRoutes() {

        $router = new Zend_Controller_Router_Route('getProgrammedNotification', array(
            'module' => 'service',
            'controller' => 'index',
            'action' => 'getprogrammednotification'));
        $this->_frontController->getRouter()->addRoute(
                'getProgrammedNotification', $router);
        
        $route = new Zend_Controller_Router_Route(
            '',
            array('module' => 'landing','controller' => 'index','action' => 'index',)
        );
         $this->_frontController->getRouter()->addRoute('', $route);
        
        if (isset($this->_config->routes)) {
            foreach ($this->_config->routes as $name => $config) {
                switch ($config->type) {
                    case 'static':
                        $destination = explode('/', $config->redirect);
                        $defaults = array('controller' => $destination[0]);
                        if (isset($destination[1])) {
                            $defaults['action'] = $destination[1];
                        }
                        if (isset($destination[2]) && isset($destination[3])) {
                            $defaults[$destination[2]] = $destination[3];
                        }
                        $route = new Zend_Controller_Router_Route_Static(
                                $name, $defaults);
                        $this->_frontController->getRouter()->addRoute(
                                $name, $route);
                        break;
                    case 'route':
                        $destination = explode('/', $config->redirect);
                        $defaults = array(
                            'controller' => $destination[0],
                            'action' => $destination[1],
                            'route' => $name
                        );
                        $variables = explode('/', $config->pattern);
                        foreach ($variables as $variable) {
                            if (strpos($variable, ':') !== false) {
                                $defaults[str_replace(array(':'), array(''), $variable)] = null;
                            }
                        }
                        $route = new Zend_Controller_Router_Route(
                                $config->pattern, $defaults);
                        $this->_frontController->getRouter()
                                ->addRoute($name, $route);
                        break;
                }
            }
        }
    }

    /**
     * Ajusta configurações relacionadas a view, inclusive layout.
     *
     * @return void
     */
    private function _setupView() {
//        $this->_frontController->registerPlugin(new HandsOn_Controller_Plugin_ActionSetup());

        $this->_frontController->registerPlugin(new HandsOn_Controller_Plugin_ViewSetup(), 98);

        // Configura layout
//        Zend_Layout::startMvc(array(
//            'layoutPath' => APPLICATION_PATH . '/modules/default/views/layouts',
//            'layout' => 'public'
//        ));        
    }

    /**
     * Configura base de dados e guarda no registro.
     *
     * @return void
     */
    private function _setupDatabase() {
        // Configura base de dados e guarda no registro
        $db = Zend_Db::factory($this->_config->db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }

    /**
     * Prepara autorização de acesso as páginas.
     *
     * @return void
     */
    private function _setupAcl() {
        $acl = new HandsOn_Acl();
        $aclHelper = new HandsOn_Controller_Action_Helper_Acl(
                null, array('acl' => $acl));
        Zend_Controller_Action_HelperBroker::addHelper($aclHelper);

        //$this->_frontController->registerPlugin(new Lembrefacil_Controller_Action_Helper_Acl());
    }

    /**
     * Configura logger
     *
     * @return void
     */
    private function _setupDebug() {
        $writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);

        if ($this->_type == 'development') {
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled(true);
            Zend_Registry::get('db')->setProfiler($profiler);
        } else {
            $writer->setEnabled(false);
        }
    }

}
