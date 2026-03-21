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
    const WarehouseTypeID = 2362;
    const AddToWarehouseTypeID = 2366;
    const RemoveFromWarehouseTypeID = 2367;
    const ReserveFromWarehouseTypeID = 2369;
    const ReturnToWarehouseTypeID = 2370;
}
