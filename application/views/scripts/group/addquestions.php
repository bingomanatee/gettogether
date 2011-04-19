<h1>Join Questions</h1>

<p>These questions will be asked of any new members to your group. </p>

<?php
echo $this->view->partial('partials/questions.phtml', array('scope' => 'group',
    'group' => $this->group, 'questions' => $this->questions));

echo $this->view->partial('forms/addquestion.phtml', array('scope' => 'group',
    'group' => $this->group));