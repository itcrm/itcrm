<?php

/**
 * Persistentā klase, kas ļauj saglabāt objektus, kuru ID ir piesaistīti citu objektu ID
 * (parastā CPersistent klase mēģinā šādus objektus saglabājot taisīt UPDATE, bet ja ieraksts vēl nav izveidots, tad tur nekas labs nesanāk)
 *
 */

class CLinkedPersistent extends CPersistent {
    function save() {
        global $_IDC_DATABASE;

        if ($this->id > 0) {
            $res = $_IDC_DATABASE->query("BEGIN");
            if ($res === false) throw new Exception('Nav iespējams uzsākt transakciju');

            $query = "SELECT * FROM {$this->tableName} WHERE {$this->idField}=$this->id FOR UPDATE";

            $res = $_IDC_DATABASE->query($query);
            if ($res === false) {
                $_IDC_DATABASE->query('ROLLBACK');
                throw new Exception('Nav iespējams bloķēt ierakstus');
            }
            $exists = ($res->count() > 0);
        } else {
            $exists = false;
        }

        if (($this->id > 0) && ($exists)) {
            $query = "UPDATE {$this->tableName} SET ";

            foreach ($this as $attribute => $value) {
                if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                    $query .= $this->fieldPrefix . $attribute . "='" . sql_escape($value) . "',\n";
                }
            }

            $query = rtrim($query, ",\n") . "\nWHERE " . $this->idField . '=' . $this->id;
            $res = $_IDC_DATABASE->query($query);
            $res = $_IDC_DATABASE->query('COMMIT');
            if ($res === false) {
                $_IDC_DATABASE->query('ROLLBACK');
                throw new Exception('Nav iespējams nobeigt transakciju');
            }
            $this->onUpdate();
        } else {
            $query = "INSERT INTO {$this->tableName} (";

            foreach ($this as $attribute => $value) {
                if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                    $query .= $this->fieldPrefix . $attribute . ', ';
                }
            }

            $query = rtrim($query, ', ') . ') VALUES (';

            foreach ($this as $attribute => $value) {
                if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                    $query .= "'" . sql_escape($value) . "',";
                }
            }

            $query = rtrim($query, ', ') . ')';

            $_IDC_DATABASE->query($query);

            $res = $_IDC_DATABASE->query('COMMIT');
            if ($res === false) {
                $_IDC_DATABASE->query('ROLLBACK');
                throw new Exception('Nav iespējams nobeigt transakciju');
            }

            $this->onCreate();
        }
    }
}
