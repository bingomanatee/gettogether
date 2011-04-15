<?php

class GroupController extends Zend_Controller_Action {

    /**
     *
     * @var Gettogether_Model_Groups
     */
    private $_group_model;

    public function init() {
        $this->_group_model = new Gettogether_Model_Groups();
    }

    public function indexAction() {
    }

    public function listAction(){ 
        $this->view->groups = $this->_group_model->all(array('sort' => 'name'));
    }

    public function addAction() {
        $this->view->values = $this->_getParam('values', array());
        if ($this->getRequest()->isPost()){

        $errs = array();

        $data = $this->_getParam('group');

        if (!$data['name'])
            $errs[] = array('field' => $name, 'error' => 'Missing name');

        if (count($errs)) {
            return $this->_forward('index', null, null, array('err' => $errs, 'values' => $data));
        }

        $group = $this->_group_model->put($data);

        $this->_forward('show', null, null, array('id' => $group->id));
        }
    }

    public function showAction() {
        $id = $this->_getParam('id');

        if (!$id) {
            return $this->_forward('index', null, null,
                    array('message' => "cannot find group id $id"));
        }

        $this->view->group = $this->_group_model->get($id);

        if ($id = $this->_getParam('id')) {
            $this->view->group = $this->_group_model->get($id);
        } else {
            $this->view->group = false;
        }
    }

}