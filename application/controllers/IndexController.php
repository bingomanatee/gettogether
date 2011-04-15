<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
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

