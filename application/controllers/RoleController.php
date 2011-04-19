<?php

class RoleController extends Zend_Controller_Action {

    /**
     * @var Gettogether_Model_Member_Roles
     */
    protected $_member_role_model = null;
    /**
     * @var Gettogether_Model_Roles
     */
    protected $_role_model = null;

    protected $_scope = 'site';

    public function init() {
        $this->_member_role_model = new Gettogether_Model_Member_Roles();
        $this->_role_model = new Gettogether_Model_Roles();
        $this->view->scope = $this->_scope = $this->_getParam('scope', 'site');
    }

    public function indexAction() {
        
    }

    public function rolesAction(){
        $this->view->roles = array();
        $this->view->roles['site'] = $this->_role_model->find(array('scope' => 'site', 'scope_id' => 0));
        $this->view->roles['group'] = $this->_role_model->find(array('scope' => 'group', 'scope_id' => 0));
        $this->view->roles['event'] = $this->_role_model->find(array('scope' => 'event', 'scope_id' => 0));
    }

    public function tasksAction(){
        $task_model = new Gettogether_Model_Tasks();
        $this->view->tasks = $task_model->find(array('scope' => 'site'));
    }

    public function grantAction() {
        $grants_model = new Gettogether_Model_Grants();
        $task_model = new Gettogether_Model_Tasks();

        if ($this->getRequest()->isPost()) {
            $data = $this->_getParam('acl');
            error_log(__METHOD__ . ': adding grant '. print_r($data, 1));
            extract($data);
            $grants_model->set_grant($role, $task, $can);
            $this->_forward('acl');
        }
    }

    public function aclAction() {
        $grants_model = new Gettogether_Model_Grants();
        $task_model = new Gettogether_Model_Tasks();
        $this->view->scope = $scope = $this->_getParam('scope', 'site');
        $this->view->scope = $scope_id = $this->_getParam('scope_id', 0);

        $this->view->roles = $roles = $this->_role_model->role_names(TRUE, $scope);
        $this->view->tasks = $tasks = $task_model->task_names($scope);

        $find = array('scope' => $scope, 'scope_id' => $scope_id);

        $grants = $grants_model->find($find);

        $gr = array();

        foreach($roles as $role) foreach($tasks as $task){
            $gr[$role][$task] = NULL;
        }

        foreach($grants as $grant){
            $gr[$grant->role][$grant->task] = $grant->can;
        }

        $this->view->grants = $gr;
    }

    public function pre_Dispatch() {
        if (!$this->_member_role_model->active_member_can('acl_edit')) {
            return $this->_forward('index', 'index', null, array('err' => 'You don\'t have permission to edit grants'));
        }
    }

    public function addAction() {
        if ($this->getRequest()->isPost()) {
            $data = $this->_getParam('role');

            if ($role = $this->_role_model->get($data['role'])) {
                return $this->_forward('index', null, null, array('err' => "role '{$data['role']}' exists already"));
            }

            $this->_role_model->add_role($data['role'], $data['label']);
        }
    }

}