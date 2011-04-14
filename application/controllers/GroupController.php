<?php

class GroupController extends Zend_Controller_Action {
/**
 *
 * @var Gettogether_Model_Groups
 */
    private $_group_model;

    public function init() {
        $this->_group_model = new Gettogether_Model_Groups();
        $this->view->message = $this->_getParam('message');
        $this->view->err = $this->_getParam('err');
    }

    public function indexAction(){
        $this->view->groups = $this->_group_model->all(array('sort' => 'name'));
        $this->view->values = $this->_getParam('values', array());
    }

    public function addAction(){

        $errs = array();
        $data = array();

        $data['name']           = $this->_getParam('name');

        if (!$data['name']) $errs[] = array('field' => $name, 'error' => 'Missing name');

        $data['description']    = $this->_getParam('description');
        $data['city']           = $this->_getParam('city');
        $data['state']          = $this->_getParam('state');

        if (count($errs)){
            return $this->_forward('index', null, null, array('err' => $errs, 'values' => $data) );
        }

        $group = $this->_group_model->put($data);

        $this->_forward('show', null, null, array('id' => $group->id));
    }

    public function showAction(){
        $id = $this->_getParam('id');

        if (!$id){
           return $this->_forward('index', null, null,
                   array('message' => "cannot find group id $id"));
        }

        $this->view->group = $this->_group_model->get($id);
    }
}