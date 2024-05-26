<?php

class Error extends Exception {
    /**
     * Array of errors
     *
     * @var array
     */
    static public $Errors = array();

    /**
     * Puts an error into errors array
     *
     * @param string $ClassName
     * @param string $Field
     * @param string $err
     */
    function setError($ClassName, $Field, $err = '') {
        self::$Errors[$ClassName][$Field] = $err != '' ? $err : $this->getMessage();
    }

    /**
     * Gets all class errors from array
     *
     * @param string $ClassName
     * @return array
     */
    static function getErrors($ClassName) {
        return self::$Errors[$ClassName];
    }
}
