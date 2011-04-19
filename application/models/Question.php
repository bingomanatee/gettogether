<?php

class Gettogether_Model_Questions extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'questions';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE  `gettogether`.`questions` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`question` TEXT NOT NULL ,
`answer_type` ENUM(  'string',  'int',  'float',  'date',  'json' ) NOT NULL ,
`answer_props` TINYINT NOT NULL ,
`created_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`status` INT NOT NULL ,
`weight` INT NOT NULL ,
`scope` ENUM(  'group',  'event',  'site',  'poll',  'other' ) NOT NULL ,
`scope_id` INT NOT NULL
`required` TINYINT NOT NULL
) ENGINE = MYISAM ;
SQL;
        $this->table()->getAdapter()->query($sql);
    }
}
