<?php

interface Gettogether_Model_IF {

    public function get($pID);

    public function put($pData, $pID = NULL);

    public function find_one(Array $pCrit);

    public function find(Array $pCrit);

    public function all(Array $pCrit);

    public function delete($pIDorCrit);

    public function change($pIDorCrit, Array $pChanges);
}