<?php

class FileSessionHandler {
    private $savePath;

    function open($savePath) {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    function close() {
        return true;
    }

    function read($id) {
        return (string)@file_get_contents("$this->savePath/sess_$id");
    }

    function write($id, $data) {
        //return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
    }

    function destroy($id) {
    }

    function gc($maxlifetime) {
    }
}
