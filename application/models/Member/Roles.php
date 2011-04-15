<?php

/**
 * note - roles == member/roles join
 * there is no discrete list of roles - only the role tags
 * that have been assigned to users, and a user can have any number of roles
 * assigned to them. 
 */
class Gettogether_Model_Member_Roles extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'member_roles';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE  `member_roles` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`member` INT NOT NULL ,
`role` VARCHAR( 100 ) NOT NULL
) ENGINE = MYISAM;
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    public function member_roles($id) {
        $roles = $this->find(array('id' => $id));

        $member_roles = array('');
        foreach ($roles as $role) {
            $member_roles[] = $role->role;
        }

        $member_roles = array_unique($member_roles);

        return $member_roles;
    }

    public function member_actions($id) {
        $member_roles = $this->member_roles($id);

        $grants_model = new Gettogether_Model_Grants();

        $where = 'role in ("' . join('","', $member_roles) . '")';

        error_log(__METHOD__ . ':: ' . $where);
        
        $grant_list = $grants_model->table()->fetchAll($where);

        error_log(print_r($grant_list->toArray(), 1));
        $actions = array();

        /**
         * first get all the default grants for all members
         * then register the specific un-grants for specific roles.
         * then register the positive grants ofr specific roles.
         */
        foreach ($grant_list as $grant)
            if ($grant->role == '') {
                $actions[$grant->action] = $grant->can;
            }

        foreach ($grant_list as $grant)
            if (($grant->role != '') && (!$grant->can)) {
                $actions[$grant->action] = $grant->can;
            }

        foreach ($grant_list as $grant)
            if (($grant->role != '') && ($grant->can)) {
                $actions[$grant->action] = $grant->can;
            }

        return $actions;
    }

    public function member_can($id, $action) {
        return $actions = $this->member_actions($id);
        if (array_key_exists($action, $actions)) {
            return $actions[$action];
        } else {
            return false;
        }
    }

    public function active_member_can($action) {
        $ds = Zend_Registry::get('gt_session');
        if ($member = $ds->member) {
            $id = $member->id;
            return $this->member_can($id, $action);
        } else {
            $grants_model = new Gettogether_Model_Grants();
            $actions = $grants_model->role_actions('');
            return array_key_exists($action, $actions) ? $actions[$action] : false;
        }
    }

}