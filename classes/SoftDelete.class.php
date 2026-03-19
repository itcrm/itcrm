<?php

trait SoftDelete {
    function Delete() {
        $table = static::$tableName;
        $Status = self::$url[2] == 'Restore' ? 1 : -1;

        if ($this->getStatus() == -1 && $Status == -1) {
            $query = 'DELETE FROM `' . $table . '` WHERE `ID`=' . $this->getID();
        } else $query = 'Update `' . $table . '`
                            SET `Status`=' . $Status . ' WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new AppError('Delete error on ' . $table . ' (' . __LINE__ . ')');
        }

        return 1;
    }
}
