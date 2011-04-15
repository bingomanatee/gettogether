<?php


class EventController extends Zend_Controller_Action {

    /**
     *
     * @var Gettogether_Model_Group_Events
     */
    private $_event_model;

    public function init() {

        error_log(__METHOD__ . ":: message = {$this->view->message}");

        $this->_event_model = new Gettogether_Model_Group_Events();

        if ($id = $this->_getParam('group_id')) {
        $this->view->group = $this->_group_model()->get($id);
        } else {
            $this->view->group = false;
        }

        if ($id = $this->_getParam('id')){
            $this->view->event = $event = $this->_event_model->get($id);
            if ($event){
                $this->view->group = $this->_group_model()->get($event->group);
            }
        } else {
            $this->view->event = false;
        }
    }

    private $_group_model;

    /**
     *
     * @return Gettogether_Model_Groups
     */
    private function _group_model(){
        if (!$this->_group_model){
            $this->_group_model = new Gettogether_Model_Groups();
        }

        return $this->_group_model;
    }

    public function addAction(){
         if ($this->getRequest()->isPost()) {

            $event_data = $this->_getParam('event');
            $date = strtotime($event_data['start_date']);
            $event_data['start_date'] = $date = date('Y-m-d', $date) . ' ' . str_replace(' ', '',  $event_data['start_time']);
            unset($event_data['start_time']);

            error_log(__METHOD__ . ":: start date = $date ");

            $event = $this->_event_model->put($event_data);

            $this->_forward('show', null, null, array('message' => 'Event Created', 'id' => $event->id));
        }
    }

    public function showAction(){
        
    }

    public function listAction(){
        $this->view->events = $this->_event_model->find(array('sort' => 'start_date',  'where' => array('`group`' => $this->_getParam('group_id'))));
    }

    public function indexAction(){
        
    }
}