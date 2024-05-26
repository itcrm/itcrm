<?php

abstract class DBObject {
    protected $Fields = array();
    static $url = array();

    static public $DB;
}

class Users extends DBObject {
    private $ID;
    private $Login;
    private $Password;
    private $Color;
    private $Name;
    private $Phone;
    private $AddDate;
    private $Status;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function getId() {
        return $this->ID;
    }
}
