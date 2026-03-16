<?php

class CVSysParams {
    function render() {
        global $_core;

        $q = new CQuery("SELECT * FROM parameters ORDER BY param_name");

        $table = new CTableEdit();

        $out = '
        <h4>Sistēmas parametri</h4>';

        $table->fields = array(
            new CTableEditField('name', 'Nosaukums', 124),
            new CTableEditField('value', 'Vērtība', 280),
        );

        while ($row = $q->fetch()) {
            $id = $row['param_id'];
            $table->rows[] = new CTableEditRow($id, array(
                new CTableEditFieldValue(H($row['param_name'])),
                new CTableEditFieldValue(nl2br(H($row['param_value']))),
            ));
        }
        $out .= $table->insert();

        return $out;
    }
}
