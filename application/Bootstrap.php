<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initConstants() {
        define('DS', DIRECTORY_SEPARATOR);
    }

    public function _initRoutes() {
        $this->bootstrap('constants');
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();
        $route = new Zend_Controller_Router_Route_Static('chart.png',
                        array('controller' => 'proj',
                            'action' => 'chart')
        );

        $router->addRoute('chart', $route);
    }

    public function _initActionHelpers() {
        $this->bootstrap('constants');
        $this->bootstrap('frontController');

        foreach ($this->getOption('actionhelpers') as $helper) {
            Zend_Controller_Action_HelperBroker::getStaticHelper($helper);
        }
    }

    public function _initSession() {
        $gt = new Zend_Session_Namespace('gt');
        Zend_Registry::set('gt_session', $gt);

        if ($gt->member) {
            Zend_Registry::set('user', $gt->member);
        } else {
            Zend_Registry::set('user', FALSE);
        }
    }

    public function _initCache() {
        $cache = $this->getOption('cache');
        $cm = new Zend_Cache_Manager();
        if ($cache)
            foreach ($cache as $name => $setting) {
                $cm->setCacheTemplate($name, $setting);
            }

        Zend_Registry::set('cm', $cm);
        $db_cache = $cm->getCache('db');
        Zend_Db_Table_Abstract::setDefaultMetadataCache($db_cache);
    }

    public function _initLog() {
        global $logger;
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'app.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);
        return $logger;
    }

    public function _initRest() {
        $front = Zend_Controller_Front::getInstance();
        $restRoute = new Zend_Rest_Route($front, array(), array('service'));
        $front->getRouter()->addRoute('rest', $restRoute);
    }

}

