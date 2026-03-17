<?php

class AppError extends Exception {
    /**
     * Array of errors
     *
     * @var array
     */
    public static $errors = [];

    static function setError($className, $field, $err = '') {
        self::$errors[$className][$field] = $err ?: 'Error';
    }

    static function getErrors($className) {
        return self::$errors[$className] ?? [];
    }
}
