<?php

class MemberController extends Zend_Controller_Action {

    /**
     * @var Gettogether_Model_Members
     */
    private $_member_model;
    /**
     *
     * @var Zend_Session
     */
    private $_session;

    private $_role_model;

    public function init() {
        $this->_member_model = new Gettogether_Model_Members();
        $this->_role_model   = new Gettogether_Model_Roles();
        
        $this->_session = Zend_Registry::get('gt_session');

        $this->view->section_nav = array();
        if ($this->_role_model->active_member_can('member_grant_change')){
            $this->view->section_nav['Grants'] = '/member/acl';
        }

        $this->view->message = $this->_getParam('message');
        $this->view->err = $this->_getParam('err');
    }

    public function indexAction() {
        $this->view->members = $this->_member_model->all(array('sort' => 'alias'));
    }

    public function joinAction() {
        if ($this->getRequest()->isPost()) {

            $errs = array();
            $data = $this->_getParam('member');

            if ($data['password'][0] !== $data['password'][1]) {
                $errs[] = array('field' => 'password', 'message' => 'Passwords do not match');
            } else {
                $data['password'] = $data['password'][0];
            }

            if ($data['email'][0] !== $data['email'][1]) {
                $errs[] = array('field' => 'email', 'message' => 'Emails do not match');
            } else {
                $data['email'] = $data['email'][0];
            }

            if (count($errs)) {
                return $this->_forward('index', null, null, array('errs' => $errs, 'values' => (Object) $data));
            }

            $member = $this->_member_model->put($data);

            $this->_forward('show', null, null, array('id' => $member->id));
        }
    }

    public function signinAction() {
        if ($this->getRequest()->isPost()) {
            $errs = array();
            $data = $this->_getParam('member');

            $auth = $this->_member_model->auth();

            $auth->setIdentity($data['alias']);
            $auth->setCredential($data['password']);
            $test = $auth->authenticate();
            if ($test->isValid()) {
                $this->_session->member = (Object) $auth->getResultRowObject();

                $this->_forward('index', 'index', null, array('message' => 'Logged in'));
            } else {
                $this->view->values = $data;
                $this->view->err = array('message' => 'Bad Signin');
            }
        }
    }

    public function editAction() {

        if (!($member = $this->_member_model->get($id = $this->_getParam('id')))) {
            return $this->_forward('index', null, null, array('err' => array(array('message' => "Cannot find member $id"))));
        }

        if ($this->getRequest()->isPost()) {
            $member_data = $this->_getParam('member');
            $errs = array();

            foreach ($member_data as $field => $value) {

                switch ($field) {
                    case 'password':
                        if ($value[0]) {
                            if ($value[0] == $value[1]) {
                                $member->$field = $value[0];
                            } else {
                                $errs[] = array('field' => $field, 'message' => 'Password Mismatch');
                            }
                        }
                        break;
                    case 'roles':
                        // todo - save roles;
                        break;

                    case 'email':
                        if ($value[0]) {
                            if ($value[0] == $value[1]) {
                                $member->$field = $value[0];
                            } else {
                                $errs[] = array('field' => $field, 'message' => 'Email Mismatch');
                            }
                        }
                        break;

                    default:
                        $member->$field = $value;
                }
            }

            if (!count($errs)) {
                $member->save();
                $this->_forward('show', null, null, array('message' => 'Member Saved'));
            }
        }


        $role_model = new Gettogether_Model_Roles();

        $this->view->member = $member;
        error_log(__METHOD__ . ':: member = ' . print_r($member, 1));
        $data = $member->toArray();
        $data['actions'] = $role_model->member_actions($member->id);
        $data['roles'] = $role_model->member_roles($member->id);

        $this->view->data = $data;
    }

    public function signoutAction() {
        unset($this->_session->member);

        $this->_forward('index', null, null, array('message' => 'Logged Out'));
    }

    public function showAction() {
        $this->view->member = $this->_member_model->get($this->_getParam('id'));
    }

    public function aclAction() {
        if (!$this->_role_model->active_member_can('member_grant_change')){
           return $this->_forward('index', 'index', null, array('err' => 'You don\'t have permission to edit grants'));
        }

        $grants_model = new Gettogether_Model_Grants();

        if ($this->getRequest()->isPost()) {
            $data = $this->_getParam('acl');
            $can = $data['can'];
            $action = $data['action'];

            if (!empty($data['all_roles'])) {
                if ($action) {
                    foreach ($grants_model->roles() as $role) {
                        $grants_model->set_grant($role, $action, $can);
                    }
                }
            } else if (!empty($data['all_actions'])) {
                foreach ($grants_Model->actions() as $action) {
                    $grants_model->set_grant($role, $action, $can);
                }
            } else {
                $grants_model->set_grant($role, $action, $can);
            }
        }

        $grants = $grants_model->all(array('sort' => array('role', 'action')));

        $this->view->roles = $roles = $grants_model->roles();
        $this->view->actions = $actions = $grants_model->actions();
        $gr = array();
        foreach ($roles as $role) {
            $gr[$role] = array();
            foreach ($actions as $action

                )$gr[$role][$action] = NULL;
        }

        foreach ($grants as $grant) {
            $gr[$grant->role][$grant->action] = $grant->can;
        }

        ksort($gr);

        $this->view->grants = $gr;

        $this->view->update = $update = $this->_getParam('update');
        if ($update) {
            $this->_helper->layout->disableLayout();
        }
    }

}