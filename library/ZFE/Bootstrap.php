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
     * Загружаем короткий псевдоним для класса утилит.
     *
     * @deprecated
     */
    protected function _initShortAlias()
    {
        ZFE_Utilities::loadShortAlias();
    }

    /**
     * Настраиваем, что не настроить в конфигурации.
     */
    protected function _initSetting()
    {
        mb_internal_encoding('UTF-8');
    }

    /**
     * Подключаем конфигурацию.
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
        APPLICATION_PATH . '/configs/sphinx.ini',
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
            if (is_readable($file)) {
                $config->merge(new Zend_Config_Ini($file, APPLICATION_ENV));
            }
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

        $loader->addResourceTypes(config('autoloaderResourceTypes')->toArray());
    }

    /**
     * Подключаем Doctrine.
     *
     * @return Doctrine_Manager
     */
    protected function _initDoctrine()
    {
        $this->bootstrap('Config');

        $host = config('doctrine.host', '127.0.0.1');
        $port = config('doctrine.port', 3306);
        $schema = config('doctrine.schema');
        $username = config('doctrine.username');
        $password = config('doctrine.password');
        $charset = config('doctrine.charset', 'utf8');
        $persistent = config('doctrine.persistent') ? 'true' : 'false';
        $driver = config('doctrine.driver', 'mysql');
        $modelsDir = config('doctrine.models_path', APPLICATION_PATH . '/models');

        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
        // $manager->setAttribute(Doctrine_Core::ATTR_SEQNAME_FORMAT, ('pgsql' === $driver) ? '%s' : $schema . '.%s');  // В миграциях нужно что бы не было указано схемы
        // $manager->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, ('pgsql' === $driver) ? '%s' : $schema . '.%s');
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, ZFE_Query::class);

        spl_autoload_register(['Doctrine_Core', 'modelsAutoload']);
        Doctrine_Core::loadModels($modelsDir);

        $dsn = "{$driver}:host={$host};port={$port};dbname={$schema}";
        if ('mysql' === $driver) {
            $dsn .= ";persistent={$persistent}";
        }

        $conn = $manager->connection([$dsn, $username, $password], 'dbh');
        $conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
        $conn->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
        $conn->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, ZFE_Model_Table::class);
        $conn->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, ZFE_Model_Collection::class);

        if ('mysql' === $driver) {
            try {
                $conn->exec("SET NAMES {$charset};");
            } catch (Doctrine_Connection_Exception $ex) {
                if (config('noticeDetails', false)) {
                    ZFE_Debug::dump([
                        'host' => $host,
                        'port' => $port,
                        'user' => $username,
                        'schema' => $schema,
                    ]);
                }
                throw $ex;
            }

            // отключить режим ONLY_FULL_GROUP_BY, включенный по-умолчанию в MySQL 5.7.5 и старше
            $conn->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        }


        if (config('doctrine.profile', false)) {
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

        Zend_Registry::set('user', (object) $this->_makeAuthData());
    }

    /**
     * Определить авторизационные данные.
     *
     * @return array
     */
    protected function _makeAuthData()
    {
        $auth = Zend_Auth::getInstance();

        $user = null;
        $role = 'guest';
        $canSwitchRoles = false;

        if (PHP_SAPI === 'cli') {
            $cliUserId = config('cli.userId');
            $cliUserLogin = config('cli.userLogin', 'cli');

            // Специальный режим для работы до создания таблицы editors
            if ($cliUserId == -1) {
                return [
                    'role' => 'guest',
                    'isAuthorized' => false,
                ];
            }

            if ($cliUserId) {
                $user = Editors::find($cliUserId);
                if (!$user) {
                    die("<error>Пользователь с ID = {$cliUserId} не найден</error>\n");
                }
            } elseif ($cliUserLogin) {
                $users = Editors::findBy('login', $cliUserLogin);
                switch ($users->count()) {
                    case 0:
                        die("<error>Пользователь с логином «{$cliUserLogin}» не найден</error>\n");
                    break;
                    case 1:
                        $user = $users->getFirst();
                    break;
                    default:
                        die("<error>Найдено более одного пользователя с логином «{$cliUserLogin}»</error>\n");
                }
            } else {
                die("<error>Не указан пользователь для CLI. Необходимо указать в конфигурации параметр `cli.userLogin` или `cli.userId`</error>\n");
            }

            $role = $user->role;
        } elseif ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();

            $userModel = config('userModel', 'Editors');

            if ($obj = $userModel::findForAuth($identity['id'])) {
                $user = $obj;
                $role = $obj->role;
                $canSwitchRoles = $this->_canSwitchRoles($user);
            }

            if (isset($identity['role']) && $canSwitchRoles) {
                $role = $identity['role'];
            }
        }

        return [
            'data' => $user,
            'role' => $role,
            'isAuthorized' => (bool) $user,
            'displayName' => $user ? $user->getShortName() : 'Гость',
            'canSwitchRoles' => $canSwitchRoles,
            'noticeDetails' => config('noticeDetails', false),
        ];
    }

    /**
     * Пользователь может менять свою роль?
     *
     * @param Editors $user
     *
     * @return bool
     */
    protected function _canSwitchRoles(Editors $user)
    {
        return 'admin' === $user->role;
    }

    /**
     * Авторизуем пользователя.
     */
    protected function _initAcl()
    {
        $this->bootstrap('Loader');
        $this->bootstrap('Config');
        $this->bootstrap('FrontController');

        $acl = new ZFE_Acl(config('acl'));
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

        $layout = Zend_Layout::startMvc();
        $layout->setViewBasePath($zfeResourcesPath . ':/views');

        /** @var ZFE_View $view */
        $view = $layout->getView();
        $view->addBasePath($zfeResourcesPath);
        $view
            ->addHelperPath('Twitter/Bootstrap3/View/Helper', 'Twitter_Bootstrap3_View_Helper_')
            ->addHelperPath('ZFE/View/Helper', 'ZFE_View_Helper_')
            ->addHelperPath(ZfeFiles_Helpers::getRoot() . '/View/Helper', 'ZfeFiles_View_Helper_')
            ->addHelperPath(APPLICATION_PATH . '/views/helpers', 'Helper_')
        ;

        $brand = config('brand');
        $view->headTitle(is_string($brand) ? $brand : $brand->short)
            ->setSeparator(config('view.titleSeparator', '/'))
            ->setDefaultAttachOrder(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND)
        ;

        Zend_Controller_Action_HelperBroker::getPluginLoader()->clearPaths();
        Zend_Controller_Action_HelperBroker::addPath('Zend/Controller/Action/Helper', 'Zend_Controller_Action_Helper');
        Zend_Controller_Action_HelperBroker::addPath('ZFE/Controller/Action/Helper', 'ZFE_Controller_Action_Helper');
        Zend_Controller_Action_HelperBroker::addPath(ZfeFiles_Helpers::getRoot() . '/Controller/Action/Helper', 'ZfeFiles_Controller_Action_Helper');
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
