<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

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
        $hbp = dirname(__FILE__) . join(DS, array('', 'controllers', 'actionhelpers'));
     //   error_log(__METHOD__ . ": finding action helpers in $hbp");
        Zend_Controller_Action_HelperBroker::addPath($hbp, 'ActionHelper');
    }

    public function _initSession() {
        Zend_Registry::set('gt_session', new Zend_Session_Namespace('gt'));
    }

    public function _initCache() {
        $cache = $this->getOption('cache');
        $cm = new Zend_Cache_Manager();
        if ($cache ) foreach ($cache as $name => $setting) {
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

}

