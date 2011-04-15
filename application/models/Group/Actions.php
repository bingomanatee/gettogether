<?php

/**
 * This is a list of roles that are relative to specific groups.
 */
class Gettogether_Model_Group_Actions extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'group_roles';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `group_actions` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`action` VARCHAR( 100 ) NOT NULL ,
`group` INT NOT NULL DEFAULT  '0'
) ENGINE = MYISAM ;

SQL;
        $this->table()->getAdapter()->query($sql);

        $this->put(array('group' => 0, 'action' => 'see_group'));
        $this->put(array('group' => 0, 'action' => 'join_group'));
        $this->put(array('group' => 0, 'action' => 'evict_member'));
        $this->put(array('group' => 0, 'action' => 'start_event'));
        $this->put(array('group' => 0, 'action' => 'announce_event'));
        $this->put(array('group' => 0, 'action' => 'edit_event'));
        $this->put(array('group' => 0, 'action' => 'join_event'));
    }

}

