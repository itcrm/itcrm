<?php

class CTableEditField {
    public $name;
    public $type;

    public $title;
    public $width;

    function __construct($name, $title = '', $width = 0) {
        $this->name = $name;
        $this->type = 'text';
        $this->title = $title;
        $this->width = $width;
        return $this;
    }

    function getTitle() {
        return $this->title;
    }

    function getHTMLWidth() {
        return $this->width ? ' width="' . $this->width . '"' : '';
    }
}

class CTableEditFieldValue {
    public $value;

    function __construct($value) {
        $this->value = $value;
    }
}

class CTableEditRow {
    public $id;
    public $values;

    function __construct($id, $values = false) {
        $this->id = $id;
        if (is_array($values)) {
            $this->values = $values;
        }
    }
}

class CTableEdit {
    public $fields;

    public $focusField;

    public $rows;

    function __construct() {
        $this->fields = array();

        $this->focusField = '';

        $this->rows = array();
    }

    function insert() {
        register_script('scripts/tableedit.js');

        $init = '
        var sample_fields = new Array(';

        $fields = array();
        foreach ($this->fields as $field) {
            $fields[] = "'" . $field->name . "'";
        }
        $init .= implode(',', $fields);

        $init .= ');
        var fields = sample_fields;';

        /**
         * Sagatavojam pirmā lauka nosaukumu - ja tas nav norādīts, tad vienkārši ieliekam pirmo no saraksta
         */
        if ($this->focusField == '') {
            $this->focusField = $this->fields[0]->name;
        }

        $init .= '
        var focus_field = \'' . $this->focusField . '\';
        ';

        /**
         * Salasam rindu IDus
         */
        $row_ids = array();
        foreach ($this->rows as $row) {
            $row_ids[] = $row->id;
        }

        $init .= '
                row_ids = Array(' . implode(',', $row_ids) . ');
        ';

        $out = '<table id="datatable" class="datatable" cellspacing="0" width="100%" style="table-layout:fixed;width:100%;">
                <tr>';

        foreach ($this->fields as $field) {
            $out .= '<th' . $field->getHTMLWidth() . '>' . $field->getTitle() . '</th>';
        }

        $out .= '<th class="last" style="width:40px">&nbsp;</th>
        </tr>';

        $c = 1;
        foreach ($this->rows as $row) {
            $id = $row->id;
            $c = ($c == 1) ? 0 : 1;

            $out .= '<tr ' . (($c == 1) ? 'class="stripe"' : '') . ' id="row_' . $id . '" onclick="edit_sample(' . $id . ')">';

            foreach ($row->values as $idx => $value) {
                $field = $this->fields[$idx];

                $out .= '<td><span class="text" id="' . $field->name . '_' . $id . '">' . $value->value . '</span></td>';
            }

            $out .= '<td align="center" nowrap="nowrap" id="act_' . $id . '"><button title="Labot" style="cursor: pointer; padding: 0;" onclick="edit_sample(' . $id . ');"><img src="' . C('base-url') . 'template/sys/img/icon-edit.png" width="16" height="16" alt="Labot" title="Labot" /></button>';

            $out .= '<button title="Dzēst" style="cursor: pointer; padding: 0;" onclick="event.cancelBubble=true; delete_sample(' . $id . ');"><img src="' . C('base-url') . 'template/sys/img/icon-delete.png" width="16" height="16" alt="Dzēst" title="Dzēst" /></button>';

            $out .= '</td>';
        }

        $out .= '<tr id="row_new" style="display: none"></tr>';
        $out .= '</table>';

        $out .= '<div id="debug"></div><div id="error_messages"></div><div id="warning_messages"></div>';
        $out .= '<a href="#" onclick="return add_sample();" class="datatable_add" id="sample_add">Pievienot</a>';

        register_startup($init);

        return $out;
    }
}
