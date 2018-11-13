<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартный ZFE Bootstrap.
 */
class ZFE_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Загружаем короткий псевдоним для класса утилит
     */
    protected function _initShortAlias()
    {
        ZFE_Utilites::loadShortAlias();
    }

    /**
     * Настраиваем, что не настроить конфигом
     */
    protected function _initSetting()
    {
        mb_internal_encoding('UTF-8');
    }

    /**
     * Подключаем конфиги.
     */
    protected function _initConfig()
    {
        $config = $this->_getConfig();

        $localConfigPath = APPLICATION_PATH . '/configs/local.ini';
        if (is_readable($localConfigPath)) {
            $config->merge(new Zend_Config_Ini($localConfigPath, APPLICATION_ENV));
        }

        // $config->setReadOnly();

        Zend_Registry::set('config', $config);
    }

    // Список конфигурационных файлов
    protected $_configFiles = [
        APPLICATION_PATH . '/configs/doctrine.ini',
        APPLICATION_PATH . '/configs/acl.ini',
        APPLICATION_PATH . '/configs/menu.ini',
        APPLICATION_PATH . '/configs/forms.ini',
        APPLICATION_PATH . '/configs/ckeditor.ini',
    ];

    /**
     * Собрать генеральную конфигурацию.
     *
     * @return Zend_Config
     */
    protected function _getConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);

        foreach ($this->_configFiles as $file) {
            $config->merge(new Zend_Config_Ini($file, APPLICATION_ENV));
        }

        return $config;
    }

    /**
     * Настраиваем автозагрузку.
     */
    protected function _initLoader()
    {
        $this->bootstrap('Config');

        $loader = new Zend_Loader_Autoloader_Resource([
            'basePath' => APPLICATION_PATH . DIRECTORY_SEPARATOR,
            'namespace' => '',
        ]);

        $config = Zend_Registry::get('config');
        $loader->addResourceTypes($config->autoloaderResourceTypes->toArray());
    }

    /**
     * Подключаем Doctrine.
     *
     * @return Doctrine_Manager
     */
    protected function _initDoctrine()
    {
        $this->bootstrap('Config');

        $dbConfig = Zend_Registry::get('config')->doctrine;

        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
        $manager->setAttribute(Doctrine_Core::ATTR_SEQNAME_FORMAT, $dbConfig->schema . '.%s');
        $manager->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, $dbConfig->schema . '.%s');
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'ZFE_Query');

        spl_autoload_register(['Doctrine_Core', 'modelsAutoload']);

        Doctrine_Core::loadModels($dbConfig->models_path);

        $host = $dbConfig->host;
        $port = $dbConfig->port;
        $schema = $dbConfig->schema;
        $username = $dbConfig->username;
        $password = $dbConfig->password;
        $persistent = $dbConfig->persistent ? 'true' : 'false';
        $driver = isset($dbConfig->driver) ? $dbConfig->driver : 'mysql';

        // У нас не настолько все универсально, что бы можно было конфигом изменять СУБД.
        // Конечно, хорошо бы сделать универсальное подключение на случай перехода
        // на PostgreSQL или Oracle Database, но оставим это переосмысление на будущее.
        $dsn = "{$driver}:host={$host};port={$port};dbname={$schema}";

        if ('mysql' === $driver) {
            $dsn .= ";persistent={$persistent}";
        }

        $conn = $manager->connection([$dsn, $username, $password], 'dbh');
        $conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
        $conn->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
        $conn->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, 'ZFE_Model_Table');
        $conn->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, 'ZFE_Model_Collection');

        if ('mysql' === $driver) {
            $conn->exec('SET NAMES utf8;');
        }

        if ($dbConfig->profile) {
            $conn->setListener(new Doctrine_Connection_Profiler());
        }

        return $manager;
    }

    /**
     * Аутентифицируем пользователя.
     */
    protected function _initAuth()
    {
        $this->bootstrap('session');
        $this->bootstrap('Loader');
        $this->bootstrap('Doctrine');

        $config = Zend_Registry::get('config');
        $auth = Zend_Auth::getInstance();

        $user = null;
        $role = 'guest';
        $canSwitchRoles = false;

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();

            $userModel = $config->userModel;

            if ($obj = $userModel::findForAuth($identity['id'])) {
                $user = $obj;
                $role = $obj->role;

                if ('admin' === $role) {
                    $canSwitchRoles = true;
                }
            }

            if (isset($identity['role']) && $canSwitchRoles) {
                $role = $identity['role'];
            }
        }

        Zend_Registry::set('user', (object) [
            'data' => $user,
            'role' => $role,
            'isAuthorized' => (bool) $user,
            'displayName' => $user ? $user->getShortName() : 'Гость',
            'canSwitchRoles' => $canSwitchRoles,
            'noticeDetails' => $config->noticeDetails,
        ]);
    }

    /**
     * Авторизируем пользователя.
     */
    protected function _initAcl()
    {
        $this->bootstrap('Loader');
        $this->bootstrap('Config');
        $this->bootstrap('FrontController');

        $aclConfig = Zend_Registry::get('config')->acl;
        $acl = new ZFE_Acl($aclConfig);
        $aclPlugin = new ZFE_Plugin_Acl($acl);
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin($aclPlugin);

        Zend_Registry::set('acl', $acl);
    }

    /**
     * Настраиваем представление.
     */
    protected function _initLayout()
    {
        $this->bootstrap('Config');

        $zfeResourcesPath = realpath(ZFE_PATH . '/../../resources');

        $config = Zend_Registry::get('config');
        $layout = Zend_Layout::startMvc();
        $layout->setViewBasePath($zfeResourcesPath . ':/views');

        if ( ! Zend_Auth::getInstance()->hasIdentity()) {
            $layout->setLayout('layout_guest');
        }

        $view = $layout->getView();
        $view->addBasePath($zfeResourcesPath);
        $view
            ->addHelperPath('Twitter/Bootstrap3/View/Helper', 'Twitter_Bootstrap3_View_Helper_')
            ->addHelperPath('ZFE/View/Helper', 'ZFE_View_Helper_')
            ->addHelperPath(APPLICATION_PATH . '/views/helpers', 'Helper_')
        ;

        $view->headTitle(is_string($config->brand) ? $config->brand : $config->brand->short)->setSeparator($config->view->titleSeparator);

        Zend_Controller_Action_HelperBroker::getPluginLoader()->clearPaths();
        Zend_Controller_Action_HelperBroker::addPath('Zend/Controller/Action/Helper', 'Zend_Controller_Action_Helper');
        Zend_Controller_Action_HelperBroker::addPath('ZFE/Controller/Action/Helper', 'ZFE_Controller_Action_Helper');
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers', 'Application_Controller_Helper');
    }

    /**
     * Настраиваем ведение логов.
     */
    protected function _initLogger()
    {
        if ($this->hasPluginResource('log')) {
            $resource = $this->getPluginResource('log');
            $log = $resource->getLog();
            Zend_Registry::set('log', $log);
        }
    }
}
