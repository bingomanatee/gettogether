<?php

class Gettogether_Model_Grants extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'role_grants';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE  `role_grants` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`scope` varchar (20) NOT NULL DEFAULT 'site',
`scope_id` INT NOT NULL DEFAULT 0,
`role` VARCHAR( 100 ) NOT NULL ,
`task` VARCHAR( 100 ) NOT NULL ,
`can` TINYINT NOT NULL DEFAULT  '1'
) ENGINE = MYISAM ;
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    public function role_tasks($role) {
        if ($role) {
            $out = $this->role_tasks('');
            $select = $this->table()->select(TRUE)
                            ->where('role LIKE ?', $role)
                            ->where('can = 1')
                            ->columns(array('task', 'can'))
                            ->distinct();
            $rows = $this->table()->fetchAll($select);

            foreach ($rows as $row) {
                $out[$row->task] = $row->can;
            }
        } else {
            $select = $this->table()->select(TRUE)
                            ->where('role LIKE ?', '')
                            ->where('can = 1')
                            ->columns(array('task', 'can'))
                            ->distinct();
            $rows = $this->table()->fetchAll($select);
            $out = array();

            foreach ($rows as $row) {
                $out[$row->task] = $row->can;
            }
        }
        return $out;
    }

    public function set_grant($role, $task, $can, $pScope='site', $pScope_id = 0) {
        if (!$task)
            throw new Exception(__METHOD__ . ":: no task for role $role/$can");
        error_log(__METHOD__ . ":: role = $role, task = $task, can = $can, scope=$pScope, scope_id = $pScope_id ");

        if ($task == '*') {
            $task_model = new Gettogether_Model_Tasks();
            $task = $task_model->task_names($pScope);
        }

        if (is_array($task)) {
            foreach ($task as $t) {
                $this->set_grant($role, $t, $can, $pScope, $pScope_id);
            }
            return;
        }

        if (is_array($role)) {
            foreach ($role as $r) {
                $this->set_grant($r, $task, $can, $pScope, $pScope_id);
            }
            return;
        }
        $find = array('role' => $role, 'task' => $task,
            'scope' => $pScope, 'scope_id' => (int) $pScope_id);

        $old_grant = $this->find_one($find);
        if ($old_grant) {
            error_log(__METHOD__ . 'found ' . print_r($old_grant->toArray(), 1));
        } else {
            error_log(__METHOD__ . ' no existing record for ' . print_r($find, 1));
        }

        switch ($can) {
            case 'delete':
                error_log(__METHOD__ . ':: adding null');
                if ($old_grant) {
                    $old_grant->delete();
                }
                break;

            case 'yes':
            case 1:
                error_log(__METHOD__ . ':: adding yes');
                if ($old_grant) {
                    $old_grant->can = 1;
                    $old_grant->save();
                } else {
                    $find['can'] = 1;
                    $this->put($find);
                }
                break;

            case 'no':
            case 0:
                error_log(__METHOD__ . ':: adding no');
                if ($old_grant) {
                    $old_grant->can = 0;
                    $old_grant->save();
                } else {
                    $find['can'] = 0;
                    $this->put($find);
                }
                break;
        }
    }

    /**
     *
     * @param int[][] $g1
     * @param int[][] $g2
     * @return array
     */
    public function merge_grants($roles, $tasks, $g1, $g2) {
        $gr = array();
        foreach ($roles as $role) {
            $gr[$role] = array();
            foreach ($tasks as $task) {
                $gr[$role][$task] = array(NULL, NULL);
            }
        }
        foreach ($g1 as $grant) {
            $gr[$grant->role][$grant->task][0] = $grant->can;
        }
        foreach ($g2 as $grant) {
            $gr[$grant->role][$grant->task][1] = $grant->can;
        }

        return $gr;
    }

    private static $_member_can_cache = array();

    private function _add_to_cache($pMember_id, $pTask, $pScope, $pScope_id, $pCan) {
        self::$_member_can_cache[$pMember_id][$pTask][$pScope][$pScope_id] = $pCan;
    }

    // cachec as [$pMember_id][$pTask][$pScope][$pScope_id];

    public function cache_member_cans($pMember_id) {

        $default_grants = $this->find(array('role' => ''));

        unset(self::$_member_can_cache[$pMember_id]);
        $this->_add_to_cache($pMember_id, '-- placeholder --', 'site', 0, 0);
        $this->_add_to_cache($pMember_id, '-- placeholder --', 'group', 0, 0);

        foreach ($default_grants as $grant) {
            $this->_add_to_cache($pMember_id, $grant->task, $grant->scope, $grant->scope_id, $grant->can);
        }
        // to do - cache default grants

        $sql = 'select * from role_grants g INNER JOIN member_roles r on (g.role = r.role) AND (g.scope = r.scope) AND (g.scope_id = r.scope_id) WHERE r.member = ' . $pMember_id;

        foreach ($this->table()->getAdapter()->fetchAll($sql) as $grant) {
            error_log(__METHOD__ . ': grant: ' . print_r($grant, 1));
            $this->_add_to_cache($pMember_id, $grant['task'], $grant['scope'], $grant['scope_id'], $grant['can']);
        }
    }

    public static function active_member_can( $pTask, $pScope = 'site', $pScope_id = 0){
        if ($user = Zend_Registry::get('user')){
            $member = $user->id;
        } else {
            $member = 0;
        }

        return self::member_can($member, $pTask, $pScope, $pScope_id);
    }

    public static function member_can($pMember_id = NULL, $pTask, $pScope = 'site', $pScope_id = 0) {

        if (!array_key_exists($pMember_id, self::$_member_can_cache)) {
            $model = new self();
            $model->cache_member_cans($pMember_id);
        }

        $cache = self::$_member_can_cache[$pMember_id];

        if (array_key_exists($pTask, $cache)
                && array_key_exists($pScope, $cache[$pTask])
                && array_key_exists($pScope_id, $cache[$pTask][$pScope])) {
            return $cache[$pTask][$pScope][$pScope_id];
        } else {
            return FALSE;
        }
    }

    public function member_cans_cache($pMember_id = NULL) {
        if (!array_key_exists($pMember_id, self::$_member_can_cache)) {
            $this->cache_member_cans($pMember_id);
        }

        return self::$_member_can_cache[$pMember_id];
    }

}