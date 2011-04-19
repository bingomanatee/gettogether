<?php

class MemberController extends Zend_Controller_Action {

    /**
     * @var Gettogether_Model_Members
     */
    private $_member_model;

    /**
     *
     * @var Zend_Db_Table_Row
     */
    private $_member;
    /**
     *
     * @var Gettogether_Model_Member_Roles
     */
    private $_member_role_model;
    /**
     *
     * @var Zend_Session
     */
    private $_session;

    public function init() {
        $this->_member_model = new Gettogether_Model_Members();
        $this->_member_role_model = new Gettogether_Model_Member_Roles();

        $this->_session = Zend_Registry::get('gt_session');

        $this->view->section_nav = array();

        $this->view->section_nav['Grants'] = '/role';

        if ($id = $this->_getParam('id')) {
            $this->view->member = $this->_member = $this->_member_model->get($id);
        }
    }

    public function indexAction() {
        if (Gettogether_Model_Grants::active_member_can('view_members')) {
            $this->view->members = $this->_member_model->all(array('sort' => 'alias'));
        } else {
            $this->view->members = FALSE;
        }
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
                $data['email'] = $email = $data['email'][0];

                $old_email = $this->_member_model->find_one(array('email' => $email));
                if ($old_email) {
                    $errs[] = array('field' => 'email', 'message' => "Email already in use");
                }
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
                $this->_session->member = $this->_session->user = (Object) $auth->getResultRowObject();

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
        //  error_log(__METHOD__ . ':: member = ' . print_r($member, 1));
        $data = $member->toArray();
        $data['actions'] = $role_model->member_actions($member->id);
        $data['roles'] = $role_model->member_roles($member->id);

        $this->view->data = $data;
    }

    public function signoutAction() {
        unset($this->_session->member);
        unset($this->_session->user);

        $this->_forward('index', null, null, array('message' => 'Logged Out'));
    }

    public function showAction() {

    }

    public function addroleAction() {

        if ($this->getRequest()->isPost()) {
            $put = $this->_getParam('addrole');
            error_log(__METHOD__ . ': add = ' . print_r($put, 1));
            $member_role_model = new Gettogether_Model_Member_Roles();
            $member_role_model->add_role($put['member'], $put['role'], $put['scope'], $put['scope_id']);
            $params = array('id' => $put['member'], 'message' => 'Role Added');
            $this->_forward('show', null, null, $params);
        }
        $this->view->scope      = $scope    = $this->_getParam('scope', 'site');
        $this->view->scope_id   = $scope_id = $this->_getParam('scope_id', 0);

    }

}