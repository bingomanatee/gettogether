<?php

class Gettogether_Model_Group_Events extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'events';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE  `events` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 200 ) NOT NULL ,
`description` TEXT NOT NULL ,
`status` INT NOT NULL ,
`creator` INT NOT NULL ,
`address` VARCHAR( 200 ) NOT NULL ,
`city` VARCHAR( 100 ) NOT NULL ,
`state` VARCHAR( 20 ) NOT NULL ,
`zip` VARCHAR( 20 ) NOT NULL ,
`cutoff` INT NOT NULL DEFAULT  '10',
`start_date` DATETIME,
`end_date` DATETIME
)
SQL;
        $this->table()->getAdapter()->query($sql);

    }

}



