<?php

class Engine extends DBObject {
    public static function Run($Class) {
        if ($Class && $cls = new $Class) {
            return $cls->Load();
        }
    }
}
