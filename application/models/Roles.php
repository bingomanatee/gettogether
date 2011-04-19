<?php

/**
 * This is a list of roles that are relative to specific groups.
 */
class Gettogether_Model_Roles extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'roles';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `scope` varchar(20) NOT NULL DEFAULT 'site',
  `scope_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SQL;
        $this->table()->getAdapter()->query($sql);

        $this->put_role('admin', 'Administrator', 'site');
        $this->put_role('member', 'Registered', 'site');
        $this->put_role('group member', NULL, array('site'));
        $this->put_role('editor', NULL, array('site', 'group'));
        $this->put_role('organizer', NULL, array('group', 'event'));
        $this->put_role('assistant organizer', NULL, array('group', 'event'));
    }

    /**
     *
     * @param string $pRole
     * @param string $pLabel
     * @param string $pScope
     * @param int $pScope_id
     * @return null
     */
    public function put_role($pRole, $pLabel = NULL, $pScope = 'site', $pScope_id = 0) {
        if (is_array($pRole)){
            foreach($pRole as $role_name){
                $this->put_role($role_name, $pLabel, $pScope, $pScope_id);
            }
            return;
        }

        if (is_array($pScope)){
            foreach($pScope as $scope){
                $this->put_role($pRole, $pLabel, $scope, $pScope_id);
            }
            return;
        }

        if (is_array($pScope_id)){
            foreach($pScope_id as $scope_id){
                $this->put_role($pRole, $pLabel, $pScope, $scope_id);
            }
            return;
        }

        $pRole = strtolower(trim($pRole));
        if (!$pRole) return;

        if (!$pLabel){
            $pLabel = ucwords(str_replace('_', ' ', $pRole));
        }

        $pScope = strtolower($pScope);
        $uscope = ucwords($pScope);

        if (!preg_match("~^$uscope~", $pLabel)){
            $pLabel = "$uscope $pLabel";
        }
        
        $data = array('role' => $pRole,
                        'label' => $pLabel,
                        'scope' => $pScope,
                        'scope_id' => $pScope_id);
            error_log(__METHOD__ . ":: looking for " . print_r($data, 1));

        if (!$role = $this->find_one($data)) {
            error_log(__METHOD__ . ":: cannot find " . print_r($data, 1));
            $role = $this->put($data);
            error_log(__METHOD__ . '.. made ' . print_r($role->toArray(), 1));
        }

        return $role;
    }

    /**
     *
     * @param boolean $pEmpty
     * @param string|string[] $pScope
     * @param int $pScope_id
     * @return string[]
     */
    public function role_names($pEmpty = FALSE, $pScope = 'site', $pScope_id = 0){
        $out = array();
        if ($pEmpty) $out[] = '';
        
        foreach($this->find(array(
            'scope' => $pScope,
            'scope_id' => $pScope_id)) as $role){
            $out[] = $role->role;
        }

        return $out;
    }

    /**
     *
     * @param string $pScope
     * @param int $pID
     */
    public function clone_roles($pScope, $pID = 0){
  
        $role_names = $this->role_names(FALSE, $pScope);

        $this->put_role($role_names, NULL, $pScope, $pID);
    }
}
