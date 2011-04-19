<?php

class Service_IndexController
extends Zend_Rest_Controller{

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    public function postAction() {
    }

    public function deleteAction() {

    }

    public function getAction() {

    }

    public function indexAction() {
        ?>
{
    "controller": "index",
    "module: "service",
    "action": "index",
    "data": []
}
<?
    }

    public function putAction() {

    }

}