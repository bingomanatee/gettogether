<?php

class GroupController extends Zend_Controller_Action {

    /**
     *
     * @var Gettogether_Model_Groups
     */
    private $_group_model;
    private $_group;

    public function init() {
        $this->_group_model = new Gettogether_Model_Groups();
        $id = $this->_getParam('id');

        $this->view->member_group = FALSE;

        if ($id) {
            $this->view->group = $this->_group = $this->_group_model->get($id);

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

    public function exposureAction(){
        $exp_model = new Gettogether_Model_Exposure();

        if ($name = $this->_getParam('name')){
            $exp = $exp_model->find_one(array('name' => $name));
            $grants = Zend_Json::decode($exp->grants);
            $group_id = $this->_group->id;

            $grants_model = new Gettogether_Model_Grants();

            foreach($grants as $grant){
                extract($grant);
                $grants_model->set_grant($role, $task, $can, 'group', $group_id);
            }

            $params = array(
                'id' => $group_id,
                'message' => "Group Settings {$exp->name} applied");

            return $this->_forward('show', NULL, NULL, $params);
        }

        $this->view->settings = $exp_model->all();
    }

    public function myAction(){
        $join_model = new Gettogether_Model_Member_Groups();
        $this->view->groups = $groups = $join_model->member_groups($this->view->user->id);
    }

    public function membersAction(){
        $join_model = new Gettogether_Model_Member_Groups();
        $this->view->members  = $join_model->members($this->_group->id);
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
        if (!$this->_group) {
            return $this->_forward('index', null, null,
                    array('message' => "cannot find group id "  . $this->_getParam('id')));
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
                return $this->_forward('show', null, null, array('err' =>$errs, 'id' => $data['group']));
            } else {
                unset($data['accept_mail']);

                $join_model = new Gettogether_Model_Member_Groups();
                $join_model->put($data);
                $user_role_model = new Gettogether_Model_Member_Roles();
                $ur = array('member' => $data['member'],
                    'role' => 'group member',
                    'scope' => 'group',
                    'scope_id' => $data['group']);
                $user_role_model->put($ur);

                $this->_forward('show', null, null, array('message' => 'You have joined this group',
                    'id' => $data['group']));
            }
        }
    }
    
    public function grantAction() {
        $grants_model = new Gettogether_Model_Grants();
        $task_model = new Gettogether_Model_Tasks();
        $role_model = new Gettogether_Model_Roles();

        if ($this->getRequest()->isPost()) {
            $data = $this->_getParam('acl');
            extract($data);
    //        error_log(__METHOD__ . ': data: ' . print_r($data, 1));
            if ($role == '*') {
                if ($task) {
                    foreach ($role_model->role_names(TRUE, 'group') as $role) {
                        $grants_model->set_grant($role, $task, $can, 'group', $data['scope_id']);
                    }
                }
            } else if ($task == '*') {
                foreach ($task_model->task_names('group') as $task) {
                    $grants_model->set_grant($role, $task, $can, 'group', $data['scope_id']);
                }
            } else {
                $grants_model->set_grant($role, $task, $can, 'group', $data['scope_id']);
            }
            $this->_forward('acl', NULL, NULL, array('id' => $data['scope_id'], ));
        }
    }

    public function aclAction() {
        $grants_model = new Gettogether_Model_Grants();
        $task_model = new Gettogether_Model_Tasks();
        $role_model = new Gettogether_Model_Roles();
        
        $this->view->scope = $scope = 'group';

        $find = array('scope' => 'group', 'scope_id' => $this->_getParam('id'));
     //   error_log(__METHOD__ . ':: finding ' . print_r($find, 1));
        
        $grants = $grants_model->find($find);

        $find_default = $find;
        $find_default['scope_id'] = 0;

        $grants_default = ($grants_model->find($find_default));


        $this->view->roles = $roles = $role_model->role_names(TRUE, 'group', $this->_getParam('id'));
     //   error_log(__METHOD__ . ':: Roles: ' . print_r($roles, 1));
        $this->view->tasks = $tasks = $task_model->task_names('group');

        $gr = $grants_model->merge_grants($roles, $tasks, $grants, $grants_default);

        ksort($gr);

        $this->view->grants = $gr;
    }


    public function defaultgrantAction() {
        $grants_model = new Gettogether_Model_Grants();
        $task_model = new Gettogether_Model_Tasks();
        $role_model = new Gettogether_Model_Roles();

        if ($this->getRequest()->isPost()) {
            $data = $this->_getParam('acl');
            extract($data);
        //    error_log(__METHOD__ . ': data: ' . print_r($data, 1));
            if ($role == '*') {
                if ($task) {
                    foreach ($role_model->role_names(TRUE, 'group') as $role) {
                        $grants_model->set_grant($role, $task, $can, 'group', 0);
                    }
                }
            } else if ($task == '*') {
                foreach ($task_model->task_names('group') as $task) {
                    $grants_model->set_grant($role, $task, $can, 'group', 0);
                }
            } else {
                $grants_model->set_grant($role, $task, $can, 'group', 0);
            }
            $this->_forward('defaultacl', NULL, NULL, array('id' => 0, ));
        }
    }

    public function addquestion(){
        $q_model = new Gettogether_Model_Questions();
        
        $this->view->questions = $this->questions = $q_model->find($find);
    }

    public function settingsAction(){

    }

    public function addquestionsAction(){
        $this->view->questions = array();
    }

    public function defaultaclAction() {
        $grants_model = new Gettogether_Model_Grants();
        $task_model = new Gettogether_Model_Tasks();
        $role_model = new Gettogether_Model_Roles();

        $this->view->scope = $scope = 'group';

        $find = array('scope' => 'group', 'scope_id' => 0);
    //    error_log(__METHOD__ . ':: finding ' . print_r($find, 1));

        $grants = $grants_model->find($find);
        $this->view->roles = $roles = $role_model->role_names(TRUE, 'group', 0);
      //  error_log(__METHOD__ . ':: Roles: ' . print_r($roles, 1));
        $this->view->tasks = $tasks = $task_model->task_names('group');

        $gr = array();
        foreach ($roles as $role) {
            $gr[$role] = array();
            foreach ($tasks as $task){
                $gr[$role][$task] = NULL;
            }
        }

        foreach ($grants as $grant) {
            error_log("grant: " . print_r($grant->toArray(), 1));
            $gr[$grant->role][$grant->task] = $grant->can;
        }

        ksort($gr);

        $this->view->grants = $gr;
    }
}