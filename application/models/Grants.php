<?php

class Gettogether_Model_Grants extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'role_grants';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE  `role_grants` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`role` VARCHAR( 100 ) NOT NULL ,
`action` VARCHAR( 100 ) NOT NULL ,
`can` TINYINT NOT NULL DEFAULT  '1'
) ENGINE = MYISAM ;
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    public function roles() {
        $select = $this->table()->select(TRUE)->columns('role')->distinct();
        $rows = $this->table()->fetchAll($select);
        $out = array();

        foreach ($rows as $row) {
            $out[] = $row->role;
        }

        //      error_log(__METHOD__ . ':: roles = ' . print_r($out, 1));

        return array_unique($out);
    }

    public function actions() {
        $select = $this->table()->select(TRUE)->columns('action')->distinct();
        $rows = $this->table()->fetchAll($select);
        $out = array();

        foreach ($rows as $row) {
            $out[] = $row->action;
        }

        //    error_log(__METHOD__ . ':: roles = ' . print_r($out, 1));

        return array_unique($out);
    }

    public function role_actions($role) {
        if ($role) {
            $out = $this->role_actions('');
            $select = $this->table()->select(TRUE)
                            ->where('role LIKE ?', $role)
                            ->where('can = 1')
                            ->columns(array('action', 'can'))
                            ->distinct();
            $rows = $this->table()->fetchAll($select);

            foreach ($rows as $row) {
                $out[$row->action] = $row->can;
            }
        } else {
            $select = $this->table()->select(TRUE)
                            ->where('role LIKE ?', '')
                            ->where('can = 1')
                            ->columns(array('action', 'can'))
                            ->distinct();
            $rows = $this->table()->fetchAll($select);
            $out = array();

            foreach ($rows as $row) {
                $out[$row->action] = $row->can;
            }
        }
        return $out;
    }

    public function set_grant($role, $action, $can) {
        $old_grant = $this->find_one(array('role' => $role, 'action' => $action));
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

}