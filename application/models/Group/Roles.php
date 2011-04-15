<?php

/**
 * This is a list of roles that are relative to specific groups.
 */
class Gettogether_Model_Group_Roles extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'group_roles';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `group_roles` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`group` INT NOT NULL ,
`role` VARCHAR( 100 ) NOT NULL
) ENGINE = MYISAM ;
SQL;
        $this->table()->getAdapter()->query($sql);

        $this->put(array('group' => 0, 'role' => 'organizer'));
        $this->put(array('group' => 0, 'role' => 'assistant_oragnizer'));
        $this->put(array('group' => 0, 'role' => 'member'));
        $this->put(array('group' => 0, 'role' => 'nonmember'));
        $this->put(array('group' => 0, 'role' => 'anonymous'));
    }

}
