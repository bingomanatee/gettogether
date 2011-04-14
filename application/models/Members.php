<?php

class Gettogether_Model_Members extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'members';

    protected function _create_table() {
        $sql = <<<SQL

CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(100) NOT NULL,
  `password` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `description` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(20) NOT NULL,
  `gender` char(1) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`,`password`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

SQL;
        $this->table()->getAdapter()->query($sql);
    }

    private $_m_auth;

    /**
     *
     * @return Zend_Auth_Adapter_DbTable
     */
    public function auth() {
        if (!$this->_m_auth) {
            $this->_m_auth = new Zend_Auth_Adapter_DbTable(
                $this->table()->getAdapter(),
                'members',
                'alias',
                'password'
            );
        }
        
        return $this->_m_auth;
    }

}