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
`role` VARCHAR( 100 ) NOT NULL,
  `scope` varchar(20) NOT NULL DEFAULT 'site',
  `scope_id` int NOT NULL DEFAULT 0
) ENGINE = MYISAM;
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    private static $_member_roles_cache = array();

    private function _cache_member_roles($pMember_id, $pForce = FALSE) {
        if ($pForce || !array_key_exists($pMember_id, self::$_member_roles_cache)) {
            unset(self::$_member_roles_cache[$pMember_id]);

            $roles = $this->find(array('member' => $pMember_id));

            foreach(array('site', 'group') as $scope){
                self::$_member_roles_cache[$pMember_id][$scope][0] = '';
            }
            foreach ($roles as $role) {
                error_log(__METHOD__ . ":: roles: " . print_r($role->toArray(), 1));
                
                self::$_member_roles_cache[$pMember_id][$role->scope][$role->scope_id] = $role->role;
            }
        }
    }

    public function add_role($pMember, $pRole, $pScope = 'site', $pScope_id = 0){
        $find = array('member' => $pMember, 'scope' => $pScope, 'scope_id' => $pScope_id, 'role' => $pRole);
        error_log(__METHOD__ . ': find = ' . print_r($find, 1));

        if ($old = $this->find_one($find)){
            error_log('...exists');
            return;
        }
        $this->put($find);
    }

    public function member_roles($pMember_id, $pScope = 'site', $pScope_id = 0) {
        if (!array_key_exists($pMember_id, self::$_member_roles_cache)) {
            $this->_cache_member_roles($pMember_id);
        }

        if (array_key_exists($pScope, self::$_member_roles_cache) && array_key_exists($pScope_id, self::$_member_roles_cache[$pScope])) {
            return self::$_member_roles_cache[$pMember_id][$pScope][$pScope_id];
        } else {
            return array();
        }
    }

    public function member_roles_cache($pMember_id) {
        if (!array_key_exists($pMember_id, self::$_member_roles_cache)) {
            $this->_cache_member_roles($pMember_id);
        }
        return self::$_member_roles_cache[$pMember_id];
    }


}