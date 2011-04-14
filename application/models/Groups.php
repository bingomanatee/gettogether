<?php

class Gettogether_Model_Groups extends Gettogether_Model_Abstract
implements Gettogether_Model_IF
{

    protected $table_name = 'groups';

    protected function _create_table() {
        $sql = <<<SQL
        CREATE TABLE  `gettogether`.`groups` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 200 ) NOT NULL ,
`city` VARCHAR( 100 ) NOT NULL ,
`state` VARCHAR( 20 ) NOT NULL ,
`description` TEXT NOT NULL ,
`status` INT NOT NULL
) ENGINE = MYISAM COMMENT =  'An organization, club, etc. ';
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    

}