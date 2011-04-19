<?php

/**
 * This is a list of tasks that are relative to specific groups.
 */
class Gettogether_Model_Tasks extends Gettogether_Model_Abstract implements Gettogether_Model_IF {

    protected $table_name = 'tasks';

    protected function _create_table() {
        $sql = <<<SQL
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `scope` varchar(20) NOT NULL DEFAULT 'site',
  `scope_id` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SQL;
        $this->table()->getAdapter()->query($sql);

        $this->put_task(array('add member', 'remove member' ),
                NULL, array('site', 'group', 'event'));
        $this->put_task(array('join site', 'edit member', 'edit own member', 'add group', 'edit group', 'edit own group', 'remove group'),
                NULL, 'site');
        $this->put_task(array('join group','see group', 'edit group', 'create event', 'publish event', 'edit event', 'join event', 'edit own event', 'remove event', 'remove own event'),
                NULL, 'group');
    }

    public function put_task($pTask, $pLabel, $pScope = 'site') {
        if (is_array($pTask)) {
            foreach ($pTask as $task_name) {
                $this->put_task($task_name, $pLabel, $pScope);
            }
            return;
        }

        if (is_array($pScope)) {
            foreach ($pScope as $scope) {
                $this->put_task($pTask, $pLabel, $scope);
            }
            return;
        }

        $pTask = strtolower(trim($pTask));
        if (!$pTask)
            return;

        if (!$pLabel) {
            $pLabel = ucwords(str_replace('_', ' ', $pTask));
        }

        if (!$task = $this->get($pTask)) {
            $this->table()->getAdapter()->insert('tasks',
                    array('task' => $pTask, 'label' => $pLabel, 'scope' => $pScope));
            $task = $this->get($pTask);
        }

        return $task;
    }

    public function task_names($scope = 'site') {
        $out = array();

        foreach ($this->find(array('scope' =>$scope)) as $task) {
            $out[] = $task->task;
        }

        return $out;
    }

}
