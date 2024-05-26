<?php

class Config {
    const ROOT_URL = "";

    const DB_HOST_NAME = "localhost";
    const DB_USER_NAME = "YOUR_MARIADB_USER";
    const DB_PASSWORD = "YOUR_MARIADB_PASSWORD";
    const DB_DATABASE_NAME = "YOUR_MARIADB_DATABASE";

    const DEL_PASS = "rkm69g";
    const EDIT_PASS = "1qwqa";

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
