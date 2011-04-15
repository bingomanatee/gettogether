<?php

/**
 * This is a list of roles that are relative to specific groups.
 */
class Gettogether_Model_Member_Grouproles extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'group_member_roles';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE  `group_member_roles` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`member` INT NOT NULL ,
`group_role` VARCHAR( 100 ) NOT NULL
) ENGINE = MYISAM ;
SQL;
    }

}
