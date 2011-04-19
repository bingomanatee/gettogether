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
`zip` VARCHAR( 20 ) NOT NULL ,
`description` TEXT NOT NULL ,
`status` INT NOT NULL
) ENGINE = MYISAM COMMENT =  'An organization, club, etc. ';
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    public function put($pData, $pID = NULL) {
        $group = parent::put($pData, $pID);
        $role_model = new Gettogether_Model_Roles();
        $role_model->clone_roles('group', $group->id);
        $grant_model = new Gettogether_Model_Grants();

        // to do - replace with exposure setting
        
        $grant_model->set_grant('member',
                array('join','join event', 'join group'), 1, 'group', $group->id);
        $grant_model->set_grant('organizer', '*', 1, 'group', $group->id);
        $grant_model->set_grant('assistant_organizer',
                array('create event', 'publish event', 'edit event', 'remove event' ), 1, 'group', $group->id);
        return $group;
    }

}