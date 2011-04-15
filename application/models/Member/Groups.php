<?php

/**
 * note - roles == member/roles join
 * there is no discrete list of roles - only the role tags
 * that have been assigned to users, and a user can have any number of roles
 * assigned to them.
 */
class Gettogether_Model_Member_Groups
    extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'member_groups';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `member_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` int(11) NOT NULL,
  `group` int(11) NOT NULL,
  `group_role` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `joined_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SQL;
        $this->table()->getAdapter()->query($sql);
    }

    public function member_groups($user_id){
        $sql = "SELECT g.* FROM groups g LEFT JOIN member_groups mg ON mg.`group` = g.id WHERE mg.member=$user_id";
        $out =  $this->table()->getAdapter()->fetchAll($sql);
        return $out;
    }
    public function members($group_id){
        $sql = "SELECT m.*, mg.joined_on, mg.group_role FROM members m LEFT JOIN member_groups mg ON mg.`member` = m.id WHERE mg.group=$group_id";
        $out =  $this->table()->getAdapter()->fetchAll($sql);
        return $out;
    }
}