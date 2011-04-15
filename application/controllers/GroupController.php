<?php

class GroupController extends Zend_Controller_Action {

    /**
     *
     * @var Gettogether_Model_Groups
     */
    private $_group_model;

    public function init() {
        $this->_group_model = new Gettogether_Model_Groups();
        $id = $this->_getParam('id');

        $this->view->member_group = FALSE;

        if ($id) {
            $this->view->group = $group = $this->_group_model->get($id);

            if ($user = Zend_Registry::get('user')){
                $join_model = new Gettogether_Model_Member_Groups();
                $find = array(
                    'member' => $user->id,
                    '`group`' => $id
                );
                $this->view->member_group = $join_model->find_one($find);
            }
        }
    }

    public function indexAction() {

    }

    public function myAction(){
        $join_model = new Gettogether_Model_Member_Groups();
        $this->view->groups = $groups = $join_model->member_groups($this->view->user->id);

    }
    public function membersAction(){
        $join_model = new Gettogether_Model_Member_Groups();
        $this->view->members  = $join_model->members($this->view->group->id);
    }

    public function listAction() {
        $this->view->groups = $this->_group_model->all(array('sort' => 'name'));
    }

    public function addAction() {
        $this->view->values = $this->_getParam('values', array());
        if ($this->getRequest()->isPost()) {

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

    public function joinAction() {

        $errs = array();

        if ($this->getRequest()->isPost()) {
            $data = $this->_getParam('join');
            if (empty($data['accept_mail'])){
                $errs[] = array('field' => 'accept_mail', 'message' => 'You must accept mail from this group to join');
            }

            if (count($errs)){
                $this->_forward('show', null, null, array('err' =>$errs, 'id' => $data['group']));
            } else {
                unset($data['accept_mail']);

                $join_model = new Gettogether_Model_Member_Groups();
                $join_model->put($data);
                $this->_forward('show', null, null, array('message' => 'You have joined this group',
                    'id' => $data['group']));
            }
        }
    }

}