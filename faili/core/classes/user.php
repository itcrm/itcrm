<?php

class CUser {
    var $id;
    var $first_name;
    var $login;                // Logins
    var $password;            // Password - MD5 hash, protams
    var $email;
    var $status;

    function CUser($id = 0) {
        if ($id > 0) {
            $this->load($id);
        } else {
            $this->id = 0;
            $this->first_name = '';
            $this->status = 0;
        }
    }

    function fetch($row) {
        $this->id = $row['id'];
        $this->first_name = $row['name'];
        $this->login = $row['login'];
        $this->password = $row['password'];
        $this->email = $row['phone'];
        $this->status = $row['status'];
    }

    function load($id) {
        global $_IDC_DATABASE;
        if ($id > 0) {
            $query = "SELECT * FROM Users WHERE ID=$id";
            if ($res = $_IDC_DATABASE->query($query)) {
                if (($res !== false) && ($res->count() > 0)) {
                    $row = $res->fetch();
                    $this->fetch($row);
                } else {
                    throw new Exception('Nepareizs lietotāja ID: ' . $id);
                }
            } else {
                throw new Exception('Neveiksme ielasot lietotāja datus!');
            }
        }
    }

    function save() {
    }

    function delete() {
    }
}
