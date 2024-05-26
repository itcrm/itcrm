<?php
class CSysUser extends CPersistent {
    public $Login;

    function __construct() {
        parent::__construct('Users', 'id', '');
    }
}
