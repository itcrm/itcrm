<?php

class Config {
    const ROOT_URL = "";

    const DB_HOST_NAME = _DB_HOST;
    const DB_USER_NAME = _DB_USER;
    const DB_PASSWORD = _DB_PASS;
    const DB_DATABASE_NAME = _DB_NAME;

    // PARAMS
    const SHOW_PERIOD = -2; // days
    const PAGE_LENGTH = 10000;

    // TYPES
    const Noliktava = 2362; // Noliktavas ID
    const AddNoliktava = 2366; // Pievienot noliktavā ID
    const DelNoliktava = 2367; // Noņemt no noliktavas ID
    const ReservNoliktava = 2369; // Rezervēts no noliktavas ID
    const ReturnNoliktava = 2370; // Atgriezts noliktavā ID
}
