<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->message = $this->_getParam('message');
        $this->view->err = $this->_getParam('err');
    }

    public function indexAction()
    {
        // action body
    }

    public function tablesAction(){
        $t = new Zend_Db_Table('foo');

        $a = $t->getAdapter();
        $rows = $a->query('SHOW TABLES')->fetchAll();
        $out = array();
        foreach ($rows as $row) $out[] = array_pop($row);
        $this->view->tables = $out;
    }

}

