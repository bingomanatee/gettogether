<?php

class Service_GrantsController
extends Zend_Rest_Controller{
    /**
     *
     * @var Gettogether_Model_Grants
     */
    private $_grants_model;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_grants_model = new Gettogether_Model_Grants();
    }

    public function postAction() {
    }

    public function deleteAction() {

    }

    public function getAction() {
        $scope = $this->_getParam('scope', 'site');
        $scope_id = $this->_getParam('scope_id', 0);

        $find = array(
            'where' => array('scope' => $scope, 'scope_id' => $scope_id),
            'columns' => array('task', 'can', 'role'),
            'sort'  => 'task'
        );

        $out = $this->_grants_model->find($find);
        $rows = array();
        foreach($out as $key => $value){
            if (is_object($value)){
                $rows[] = $value->toArray();
            } else {
                $rows[] = $value;
            }
        }

        echo Zend_Json::encode($rows);
    }

    public function indexAction() {
        ?>
{
    "controller": "index",
    "module: "service",
    "action": "index",
    "data": []
}
<?
    }

    public function putAction() {

    }

}