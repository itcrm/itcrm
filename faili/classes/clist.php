<?php
class CList {
    public $objects;
    /**
     * @desc Objektu klases nosaukums, kuru lasa saraksts
     */
    protected $className;

    /**
     * @desc
     */
    public $indexAttributes;

    public $orderedIndex;
    public $attributeIndex;

    function __construct($className) {
        $this->className = $className;
        $this->indexAttributes = array();
        $this->attributeIndex = array();
        $this->orderedIndex = array();

        $this->objects = array();
    }

    function getQuery($attributeName, $attributeValue, $where = '') {
        global $_core;
        $instance = new $this->className;

        if ($where == '') {
            if ($attributeName) {
                $where = "WHERE $attributeName = '" . sql_escape($attributeValue) . "'";
            } else {
                $where = "";
            }
        }

        $query = "SELECT {$instance->tableName}.*
                FROM {$instance->tableName}
                $where";

        return $query;
    }

    function loadByField($attributeName, $attributeValue) {
        $query = $this->getQuery($attributeName, $attributeValue);

        $res = new CQuery($query, DB_RESULT_TYPE_SINGLE);
        if ($res->error != false) throw new Exception('Neizdevās ielasīt "' . $this->className . '" objektu sarakstu');

        $this->objects = array();
        $this->orderedIndex = array();
        $this->attributeIndex = array();
        while ($row = $res->fetch()) {
            $obj = new $this->className;
            $obj->fetch($row);
            $this->objects[$obj->id] = $obj;

            $this->orderedIndex[] = $obj->id;

            foreach ($this->indexAttributes as $attribute) {
                $this->attributeIndex[$attribute][$obj->$attribute] = $obj->id;
            }
        }
    }

    function loadByCustomWhere($where) {
        $query = $this->getQuery('', '', $where);

        $res = new CQuery($query, DB_RESULT_TYPE_SINGLE);

        if ($res->error != false) throw new Exception('Neizdevās ielasīt "' . $this->className . '" objektu sarakstu');

        $this->objects = array();
        $this->orderedIndex = array();
        $this->attributeIndex = array();
        while ($row = $res->fetch()) {
            $obj = new $this->className;
            $obj->fetch($row);
            $this->objects[$obj->id] = $obj;

            $this->orderedIndex[] = $obj->id;

            foreach ($this->indexAttributes as $attribute) {
                $this->attributeIndex[$attribute][$obj->$attribute] = $obj->id;
            }
        }
    }
}
