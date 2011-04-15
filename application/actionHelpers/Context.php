<?php

class ActionHelper_Context extends Zend_Controller_Action_Helper_Abstract {

    public function init() {

        $ds = Zend_Registry::get('gt_session');

        $view = $this->getActionController()->view;

        $layout = Zend_Layout::getMvcInstance();
        $layout_view = $layout->getView();

        $view->user = $layout_view->user = $ds->member;

        $layout_view->message = $message = $this->getRequest()->getParam('message');
        $layout_view->err = $this->getRequest()->getParam('err');
        $layout_view->ticket = $ticket = $this->getRequest()->getParam('ticket');

        if ($ticket) {
            $layout->disableLayout();
        }
    }

}
