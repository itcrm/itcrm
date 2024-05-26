<?php
class COrder extends CPersistent {
    public $IDUser;
    public $Code;
    public $Description;
    public $Status;
    public $AddDate;

    function __construct() {
        parent::__construct('Orders', 'id', '');
        $this->AddDate = '2001-01-01 01:01:01';
    }
}
