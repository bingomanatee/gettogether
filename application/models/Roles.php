<?php

/**
 * This is a list of roles that are relative to specific groups.
 */
class Gettogether_Model_Roles extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'roles';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `roles` (
`role` VARCHAR( 100 ) PRIMARY KEY,
`label` VARCHAR(100) NOT NULL
) ENGINE = MYISAM ;
SQL;
        $this->table()->getAdapter()->query($sql);

        $this->put_role('admin', 'Site Administrator');
        $this->put_role('editor', 'Site Editor');
    }

    public function put_role($pRole, $pLabel = NULL) {
        $pRole = strtolower(trim($pRole));
        if (!$pRole) return;

        if (!$pLabel){
            $pLabel = ucwords(str_replace('_', ' ', $pRole));
        }

        if (!$role = $this->get($pRole)) {
            $this->table()->getAdapter()->insert('roles',
                    array('role' => $pRole, 'label' => $pLabel));
            $role= $this->get($pRole);
        }

        return $role;
    }

    public function role_names($pEmpty = FALSE){
        $out = array();
        if ($pEmpty) $out[] = '';
        
        foreach($this->all() as $role){
            $out[] = $role->role;
        }

        return $out;
    }
}
