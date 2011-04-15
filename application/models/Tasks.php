<?php

/**
 * This is a list of tasks that are relative to specific groups.
 */
class Gettogether_Model_tasks extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'tasks';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `tasks` (
  `task` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `verb` varchar(100) NOT NULL,
  PRIMARY KEY (`task`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SQL;
        $this->table()->getAdapter()->query($sql);

        foreach(array('member', 'grant', 'group', 'event', 'message') as $subject){
            foreach(array('edit', 'create', 'delete', 'edit_own', 'delete_own') as $verb){

                $task = $verb . '_' . $subject;
                $label = ucwords(str_replace('_', ' ', $task));
                $this->put_task($task, $label, $subject, $verb);
            }


        }
    }

    public function put_task($pTask, $pLabel, $subject, $verb) {
        $pTask = strtolower(trim($pTask));
        if (!$pTask) return;

        if (!$pLabel){
            $pLabel = ucwords(str_replace('_', ' ', $pTask));
        }

        if (!$task = $this->get($pTask)) {
            $this->table()->getAdapter()->insert('tasks',
                    array('task' => $pTask, 'label' => $pLabel, 'subject' => $subject, 'verb' => $verb));
            $task= $this->get($pTask);
        }

        return $task;
    }

    public function task_names(){
        $out = array();

        foreach($this->all() as $task){
            $out[] = $task->task;
        }

        return $out;
    }
}
