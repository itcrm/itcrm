<?php

class CSession {
    public $id = '';
    public $user_id;

    function __construct() {
        session_start();

        if (isset($_SESSION['User'])) {
            $this->user_id = $_SESSION['User']->getId();
            $this->id = get_param('PHPSESSID', 'GPC');
        } else {
            $this->user_id = -1;
            $this->id = 0;
        }
    }
}
