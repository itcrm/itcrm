<?php

class CPersistent {
    /**
     * Objekta unikālais identifikators
     *
     * @var int
     */
    public $id;

    /**
     * Tabulas nosaukums, kurā tiks glabāti objekta atribūti
     *
     * @var string
     */
    public $tableName;

    /**
     * Lauka nosaukums tabulā, kuram atbilst objekta id
     *
     * @var int
     */
    public $idField;

    /**
     * Lauka nosaukuma prefikss.
     * Tiek pielikts objekta atribūta nosaukuma sākumā, veidojot SQL vaicājumus
     * Piemēram, ja objektam ir atribūts "name", prefikss ir "tmp_", tad tabulā tiks meklēts lauks "tmp_name"
     *
     * @var string
     */
    public $fieldPrefix;

    /**
     * Mazākā ID vērtība, kura var būt.
     * Visiem objektiem, kam id ir zem šīs vertības, saglabājot tiks taisīts jauns ieraksts un piešķirts jauns id
     *
     * @var int
     */
    public $_minId;

    function __construct($table, $idField, $fieldPrefix, $minId = 1) {
        $this->tableName = $table;
        $this->idField = $idField;
        $this->fieldPrefix = $fieldPrefix;
        $this->_minId = $minId;
        $this->id = false;
    }

    function fetch($row) {
        $this->id = $row[$this->idField];
        $prefix = strtolower($this->fieldPrefix);
        foreach ($this as $attribute => $value) {
            if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                if (substr($attribute, 0, 3) == 'ne_') {
                    $t_attribute = substr($attribute, 3);
                } else {
                    $t_attribute = $attribute;
                }

                $t_attribute = strtolower($t_attribute);

                if (array_key_exists($prefix . $t_attribute, $row)) {
                    $this->$attribute = $row[$prefix . $t_attribute];
                } elseif (array_key_exists($t_attribute, $row)) {
                    $this->$attribute = $row[$t_attribute];
                }
            }
        }
    }

    function load($id) {
        $query = "SELECT * FROM {$this->tableName} WHERE {$this->idField}=$id";
        $res = new CQuery($query, DB_RESULT_TYPE_SINGLE);
        if ($res->error != false) throw new Exception('Neizdevās ielasīt klases "' . get_class($this) . '" objekta ' . $id . ' datus');

        $row = $res->fetchAssoc();
        if ($row === false) throw new Exception('Klases "' . get_class($this) . '" objekta ID "' . $id . '" nav derīgs');

        $this->id = $id;
        $this->fetch($row);
    }

    function save() {
        if ($this->id >= $this->_minId) {
            $query = "UPDATE {$this->tableName} SET ";

            foreach ($this as $attribute => $value) {
                if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                    if (strpos($attribute, 'ne_') === 0) {
                        $t_value = sql_escape($value);
                        $attribute = substr($attribute, 3);
                    } else {
                        $t_value = sql_escape($value);
                    }

                    $query .= $this->fieldPrefix . $attribute . "='" . $t_value . "',\n";
                }
            }

            $query = rtrim($query, ",\n") . "\nWHERE " . $this->idField . '=' . $this->id;
            new CQuery($query, DB_RESULT_TYPE_NONE);

            $this->onUpdate();
        } else {
            $query = "INSERT INTO {$this->tableName} (";

            foreach ($this as $attribute => $value) {
                if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                    if (strpos($attribute, 'ne_') === 0) {
                        $attribute = substr($attribute, 3);
                    }
                    $query .= $this->fieldPrefix . $attribute . ', ';
                }
            }

            $query = rtrim($query, ', ') . ') VALUES (';

            foreach ($this as $attribute => $value) {
                if (($attribute != 'id') && ($attribute != 'tableName') && ($attribute != 'idField') && ($attribute != 'fieldPrefix') && (substr($attribute, 0, 1) != '_')) {
                    $t_value = sql_escape($value);
                    $query .= "'" . $t_value . "',";
                }
            }
            $query = rtrim($query, ', ') . ')';

            $res = new CQuery($query);

            $this->id = $res->lastInsertId();
            $this->onCreate();
        }
    }

    function delete() {
        $this->load($this->id);
        $query = "DELETE FROM {$this->tableName} WHERE {$this->idField}=$this->id";
        new CQuery($query);
        $this->onDelete();
    }

    function onCreate() {
    }

    function onUpdate() {
    }

    function onDelete() {
    }
}
