<?php

abstract class Gettogether_Model_Abstract implements Gettogether_Model_IF {

    public function __construct() {
        if (!$this->table_exists()) {
            $this->_create_table();
        }
    }

    protected $table_name = '';
    private static $_tables = array();
    private static $_existing_tables = null;

    private function existing_tables($reload = FALSE) {
        if ((!self::$_existing_tables) || $reload) {
            $a = $this->table()->getAdapter();
            $rows = $a->query('SHOW TABLES')->fetchAll();
            $out = array();
            foreach ($rows as $row) {
                $out[] = array_pop($row);
            }
            self::$_existing_tables = $out;
        }
        return self::$_existing_tables;
    }

    public function table_exists() {
        return in_array($this->table_name, $this->existing_tables());
    }

    protected abstract function _create_table();

    /**
     *
     * @return Zend_DB_Table;
     */
    public static function table_factory($pName) {
        if (!array_key_exists($pName, self::$_tables)) {
            self::$_tables[$pName] = new Zend_Db_Table($pName);
        }
        return self::$_tables[$pName];
    }

    protected function table() {
        if (!$this->table_name) {
            throw new Exeption(__METHOD__ . ':: no table name in class ' . get_class($this));
        }
        return self::table_factory($this->table_name);
    }

    /**
     * @param variant $pIDorCrit
     * @param array $pChanges
     */
    public function change($pIDorCrit, array $pChanges) {
        if (is_scalar($pIDorCrit)) {
            $record = $this->get_item($pIDorCrit);
            $this->_change($record, $pChanges);
        } else {
            $records = $this->find($pIDorCrit);
            foreach ($records as $record) {
                $this->_change($record, $pChanges);
            }
        }
    }

    protected function _change(Zend_Db_Table_Row $row, $pChanges) {
        foreach ($pChanges as $field => $value) {
            $row->$field = $value;
        }
        $row->save();
    }

    public function delete($pIDorCrit) {
        if (is_scalar($pIDorCrit)) {
            $record = $this->get_item($pIDorCrit);
            $this->_change($record, $pChanges);
        } else {
            $records = $this->find($pIDorCrit);
            foreach ($records as $record) {
                $this->_change($record, $pChanges);
            }
        }
    }

    /**
     *
     * @param array $pCrit
     * @return Zend_Db_Table_Rowset
     */
    public function find(array $pCrit) {
        $select = $this->table()->select();

        $this->_add_wheres($select, $pCrit);
        $this->_add_sort($select, $pCrit);
        $this->_add_limit($select, $pCrit);

        return $this->table()->fetchAll($select);
    }

    public function all(array $pCrit = NULL) {
        $select = $this->table()->select();
        
        $this->_add_sort($select, $pCrit);
        $this->_add_limit($select, $pCrit);

        return $this->table()->fetchAll($select);
    }

    private function _add_sort(Zend_Db_Table_Select $select, $pCrit) {
        if (array_key_exists('sort', $pCrit)) {
            $select->order($pCrit['sort']);
        }
    }

    private function _add_limit(Zend_Db_Table_Select $select, $pCrit) {
        if (array_key_exists('limit', $pCrit)) {
            if (array_key_exists('offset', $pCrit)) {
                $select->limit( $pCrit['limit'], $pCrit['offset']);
            } else {
                $select->limit($pCrit['limit']);
            }
        } else if (array_key_exists('offset', $pCrit)) {
            $select->limit(0, $pCrit['offset']);
        }
    }

    private function _add_wheres(Zend_Db_Table_Select $select, $pCrit) {

        if (array_key_exists('where', $pCrit)) {
            $wheres = $pCrit['where'];
        } else {
            $wheres = $pCrit;
        }
        foreach ($wheres as $key => $value) {
            $select->where("$key = ?", $value);
        }
    }

    public function find_one(array $pCrit) {
        $rowset = $this->find($pCrit);
      //  error_log(__METHOD__ . ':: result: ' . print_r($rowset, 1));
        return $rowset->current();
    }

    public function get($pID) {
        $rows = self::table()->find($pID);
        return $rows->current();
    }

    public function put($pData, $pID = NULL) {
        /**
         * @var Zend_Db_Table_Row
         */
        $row = $this->table()->fetchNew();
        foreach ($pData as $field => $value) {
            $row->$field = $value;
        }
        $row->save();

        return $row;
    }

}