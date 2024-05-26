var row_ids = Array();

var sample_opened = null;

var sample_errors = Array();
var sample_warnings = Array();
var sample_values = Array();
var errors = 0;
var goto_next_row = false;
var goto_prev_row = false;
var goto_row = null;
var requesting = false;

function on_keypress(event, next_field, enters) {
    if (enters && event.keyCode == 13 && next_field != "" && !event.shiftKey) {
        var field = next_field + "_" + sample_opened + "_val";

        if ($(field).visible()) {
            $(field).focus();
            if ($(field).select) {
                $(field).select();
            }
        } else {
            field = next_field + "_" + sample_opened + "_ctl";
            if ($(field)) {
                $(field).focus();
            }
        }

        return false;
    } else if (enters && event.keyCode == 13) {
        goto_next_row = true;
        goto_prev_row = false;
        edit_sample_submit();
        return false;
    } else if (event.keyCode == 27) {
        edit_sample_cancel();
    }
    return true;
}

function default_buttons(sample_opened) {
    return (
        '<button title="Labot" style="cursor: pointer; padding: 0;" onclick="edit_sample(' +
        sample_opened +
        ');"><img src="' +
        siteroot +
        'template/sys/img/icon-edit.png" width="16" height="16" alt="Labot" title="Labot"/></button>' +
        '<button title="Dzēst" style="cursor: pointer; padding: 0;" onclick="event.cancelBubble=true; delete_sample(' +
        sample_opened +
        ');return false;"><img src="' +
        siteroot +
        'template/sys/img/icon-delete.png" width="16" height="16" alt="Dzēst" title="Dzēst"/></button>'
    );
}

function check_changes() {
    var changes = 0;
    for (var i = 0; i < sample_fields.length; i++) {
        if (
            $(sample_fields[i] + "_" + sample_opened + "_val")
                .value.escapeHTML()
                .replace(/\"/g, "&quot;") != sample_values[i]
        ) {
            changes++;
        }
    }
    return changes;
}

function edit_sample(id) {
    goto_next_row = goto_prev_row = false;
    if (sample_opened == id) {
        return false;
    } else if (sample_opened != null) {
        changes = check_changes();

        if (changes == 0) {
            if (sample_opened == 0) {
                edit_sample_cancel();
            } else {
                edit_sample_done();
            }
        } else {
            errors = 0;
            for (var i = 0; i < sample_errors.length; i++) {
                if (sample_errors[i] != null) errors++;
            }
            if (errors > 0) {
                return false;
            }
            if (edit_sample_submit() == false) {
                return false;
            } else if (requesting == true) {
                goto_row = id;
                return false;
            }
        }
    }
    var sample_next_field = "";
    sample_values["rowclass"] = $("row_" + id).className;
    var field = "";
    for (var i = 0; i < sample_fields.length; i++) {
        field = sample_fields[i];

        if ($(field + "_" + id)) {
            val = $(field + "_" + id).innerHTML;
        } else {
            val = "";
        }

        if (i < sample_fields.length - 1) {
            sample_next_field = sample_fields[i + 1];
        } else {
            sample_next_field = "";
        }

        val = val.replace(/\"/g, "&quot;");

        html =
            '<input id="' +
            sample_fields[i] +
            "_" +
            id +
            '_val" value="' +
            val +
            '" style="width: 95%"' +
            " onkeypress=\"return on_keypress(event,'" +
            sample_next_field +
            '\',1);" onkeydown="clear_errors(' +
            i +
            "); clear_warnings(" +
            i +
            '); return numbersonly(this,event,0,0,1)" />';

        sample_values[i] = val;
        $(sample_fields[i] + "_" + id).innerHTML = html;
    }
    $("row_" + id).className = "editable";
    $("act_" + id).innerHTML =
        '<button title="Saglabāt" style="cursor: pointer" onclick="event.cancelBubble=true;edit_sample_submit();return false;"><img src="' +
        siteroot +
        'template/sys/img/icon-save.png" width="16" height="16" alt="Saglabāt" title="Saglabāt" /></button>';
    $("act_" + id).innerHTML +=
        '<button title="Atcelt" style="cursor: pointer" onclick="event.cancelBubble=true;edit_sample_cancel();return false;"><img src="' +
        siteroot +
        'template/sys/img/icon-cancel.png" width="16" height="16" alt="Atcelt" title="Atcelt" /></button>';

    $("sample_add").style.color = "#888";
    sample_opened = id;
    $(focus_field + "_" + id + "_val").focus();
    if ($(focus_field + "_" + id + "_val").select) {
        $(focus_field + "_" + id + "_val").select();
    }
}

function edit_sample_cancel() {
    if (sample_opened == 0) {
        for (var i = 0; i < sample_fields.length; i++) {
            sample_errors[i] = null;
        }
        show_errors();
        row = $("row_0");
        row.parentNode.removeChild(row);
        sample_opened = null;
        $("sample_add").style.color = "#447B1C";
    } else {
        for (var i = 0; i < sample_fields.length; i++) {
            sample_errors[i] = null;
            val = sample_values[i];
            $(sample_fields[i] + "_" + sample_opened + "_val").value =
                val.unescapeHTML();
        }
        show_errors();
        show_warnings();
        edit_sample_done();
    }
}

function edit_sample_done() {
    if (sample_opened == null) {
        return false;
    }
    for (var i = 0; i < sample_fields.length; i++) {
        val = $(sample_fields[i] + "_" + sample_opened + "_val")
            .value.escapeHTML()
            .replace(/\"/g, "&quot;");
        if ($(sample_fields[i] + "_" + sample_opened) && val != null) {
            $(sample_fields[i] + "_" + sample_opened).innerHTML = val;
        }
    }
    $("row_" + sample_opened).className = sample_values["rowclass"];
    $("act_" + sample_opened).innerHTML = default_buttons(sample_opened);

    $("sample_add").style.color = "#447B1C";
    sample_opened = null;
    return true;
}

function edit_sample_submit() {
    sample_id = sample_opened;
    changes = check_changes();

    if (changes == 0) {
        if (sample_opened != 0) {
            edit_sample_done();
            sample_change_row();
            return true;
        }
    }

    errors = 0;
    for (var i = 0; i < sample_fields.length; i++) {
        sample_errors[i] = null;
    }

    show_errors();

    $("act_" + sample_id).innerHTML =
        '<img src="' +
        siteroot +
        'template/sys/img/loading.gif" width="16" height="16" alt="" />';
    req = "contour_id=" + sample_id;
    for (var i = 0; i < sample_fields.length; i++) {
        val = Url.encode(
            $(sample_fields[i] + "_" + sample_opened + "_val").value
        );
        req += "&f_" + sample_fields[i] + "=" + val;
    }

    var opt = {
        url: siteroot + "xml/sysrpc.php?cmd=setparam&id=" + sample_id,
        method: "post",
        data: req,
        onSuccess: function (t) {
            requesting = false;
            if (t == "" || t.indexOf("\t") >= 0) {
                //*	Vienkārši saglabāts
                // Ja kāds atribūts ir mainīts, tas tiek padots atpakaļ formā
                var new_id = 0;

                if (t.indexOf("\t") >= 0) {
                    lines = t.split("\t");
                    for (var j = 0; j < lines.length; j++) {
                        line = lines[j].split("=");

                        fieldCode = line[0];
                        line.splice(0, 1);
                        errorLine = line.join("=");

                        if (fieldCode == "id") {
                            new_id = parseInt(errorLine);
                        } else {
                            for (var k = 0; k < sample_fields.length; k++) {
                                if (fieldCode == sample_fields[k]) {
                                    if (
                                        $(
                                            sample_fields[k] +
                                                "_" +
                                                sample_opened +
                                                "_val"
                                        )
                                    ) {
                                        $(
                                            sample_fields[k] +
                                                "_" +
                                                sample_opened +
                                                "_val"
                                        ).value = errorLine;
                                    }
                                    sample_warnings[k] = "Koriģēts";
                                } else {
                                    if (
                                        fieldCode.substr(1) == sample_fields[k]
                                    ) {
                                        $(
                                            sample_fields[k] +
                                                "_" +
                                                sample_opened
                                        ).innerHTML = errorLine;
                                    }
                                }
                            }
                        }
                    }
                    show_warnings();
                }

                edit_sample_done();

                /**
                 * Ir pievienota jauna rowa
                 */
                if (new_id != 0 && sample_id == 0) {
                    $("row_0").setAttribute(
                        "onclick",
                        "edit_sample('" + new_id + "')"
                    );

                    /**
                     * Fix IE6 glitch
                     */
                    $("row_0").onclick = function () {
                        var temp = new Function(
                            "edit_sample('" + new_id + "')"
                        );
                        temp();
                    };

                    $("row_0").setAttribute("id", "row_" + new_id);
                    for (var i = 0; i < sample_fields.length; i++) {
                        if ($(sample_fields[i] + "_0")) {
                            $(sample_fields[i] + "_0").setAttribute(
                                "id",
                                sample_fields[i] + "_" + new_id
                            );
                        }

                        if ($(sample_fields[i] + "_0_val")) {
                            $(sample_fields[i] + "_0_val").setAttribute(
                                "id",
                                sample_fields[i] + "_" + new_id + "_val"
                            );
                        }

                        if ($(sample_fields[i] + "_warning_0")) {
                            $(sample_fields[i] + "_warning_0").setAttribute(
                                "id",
                                sample_fields[i] + "_warning_" + new_id
                            );
                        }
                        if ($(sample_fields[i] + "_code_0")) {
                            $(sample_fields[i] + "_code_0").setAttribute(
                                "id",
                                sample_fields[i] + "_code_" + new_id
                            );
                        }
                    }
                    $("act_0").setAttribute("id", "act_" + new_id);

                    row_ids[row_ids.length] = new_id;
                    sample_id = new_id;
                }

                $("act_" + sample_id).innerHTML = default_buttons(sample_id);
                sample_change_row();
                if (goto_row != null) {
                    id = goto_row;
                    goto_row = null;
                    edit_sample(id);
                }
                return true;
            } else {
                // error rādīšanas kods
                $("act_" + sample_id).innerHTML =
                    '<button title="Saglabāt" style="cursor: pointer" onclick="event.cancelBubble=true;edit_sample_submit();return false;"><img src="' +
                    siteroot +
                    'template/sys/img/icon-save.png" width="16" height="16" alt="Saglabāt" title="Saglabāt"/></button><button title="Atcelt" style="cursor: pointer" onclick="event.cancelBubble=true;edit_sample_cancel();return false;"><img src="' +
                    siteroot +
                    'template/sys/img/icon-cancel.png" width="16" height="16" alt="Atcelt" title="Atcelt" /></button>';
                lines = t.split("\n");
                for (var j = 0; j < lines.length; j++) {
                    line = lines[j].split(":");
                    for (var k = 0; k < sample_fields.length; k++) {
                        if (line[0] == sample_fields[k]) {
                            sample_errors[k] = line[1];
                        }
                    }
                }
                goto_next_row = false;
                show_errors();
            }
        },
        onFailure: function (t) {
            requesting = false;
            $("act_" + sample_id).innerHTML =
                '<button title="Saglabāt" style="cursor: pointer" onclick="event.cancelBubble=true;edit_sample_submit();return false;"><img src="' +
                siteroot +
                'template/sys/img/icon-save.png" width="16" height="16" alt="Saglabāt" title="Saglabāt"/></button><button title="Atcelt" style="cursor: pointer" onclick="event.cancelBubble=true;edit_sample_cancel();return false;"><img src="' +
                siteroot +
                'template/sys/img/icon-cancel.png" width="16" height="16" alt="Atcelt" title="Atcelt" /></button>';
            goto_next_row = false;
            alert("Kļūda saglabājot datus");
        },
    };
    requesting = true;
    new Request(opt).send();
}

function sample_change_row() {
    if (goto_next_row == true) {
        // ejam uz nākamo rindu
        for (j = 0; j < row_ids.length; j++) {
            if (row_ids[j] == sample_id && row_ids[j + 1] != null) {
                edit_sample(row_ids[j + 1]);
                return true;
            }
        }
        // ja jau nav nākamās rindas, laipni piedāvājamies to pievienot
        return add_sample();
    } else if (goto_prev_row == true) {
        // ejam uz iepriekšējo rindu
        for (j = 0; j < row_ids.length; j++) {
            if (row_ids[j] == sample_id && row_ids[j - 1] != null) {
                edit_sample(row_ids[j - 1]);
                return true;
            }
        }
    }
    return false;
}

function add_sample() {
    if (sample_opened != null) {
        return false;
    }
    var oldRow = $("row_new");
    newRow = document.createElement("tr");
    newRow.setAttribute("id", "row_0");
    for (i = 0; i < sample_fields.length; i++) {
        newCell = document.createElement("td");
        newCell.setAttribute("nowrap", "nowrap");

        if (sample_fields[i] == "d3") {
            newNode = document.createTextNode("R\u00A0");
            newCell.appendChild(newNode);
            newSpan = document.createElement("span");
            newSpan.setAttribute("id", sample_fields[i] + "_0");
            newSpan.className = "text";
            newCell.appendChild(newSpan);
        } else {
            newSpan = document.createElement("span");
            newSpan.setAttribute("id", sample_fields[i] + "_0");
            newSpan.className = "text";
            newCell.appendChild(newSpan);
        }

        newRow.appendChild(newCell);
    }
    newCell = document.createElement("td");
    newCell.setAttribute("id", "act_0");
    newCell.setAttribute("align", "center");
    newCell.setAttribute("style", "padding:0;");
    newRow.appendChild(newCell);
    oldRow.parentNode.appendChild(newRow);
    edit_sample(0);
    return false;
}

function delete_sample(id) {
    if (confirm("Vai tiešām dzēst? Šo operāciju nebūs iespējams atsaukt")) {
        sample_id = id;
        $("act_" + sample_id).innerHTML =
            '<img src="' +
            siteroot +
            'template/sys/img/loading.gif" width="16" height="16" alt="" />';

        req = "contour_id=" + sample_id;

        var opt = {
            url: siteroot + "xml/sysrpc.php?cmd=deleteparam&id=" + sample_id,
            method: "post",
            data: req,
            onSuccess: function (t) {
                requesting = false;

                if (sample_id != 0) {
                    if (t == "" || t.indexOf(";") >= 0) {
                        var i = $("row_" + sample_id).rowIndex;
                        var table = $("row_" + sample_id).parentNode;
                        table.deleteRow(i);
                        for (j = 0; j < row_ids.length; j++) {
                            if (row_ids[j] == sample_id) {
                                row_ids.splice(j, 1);
                                break;
                            }
                        }
                    } else {
                        $("act_" + sample_id).innerHTML =
                            default_buttons(sample_id);
                        alert(t);
                    }
                    sample_id = 0;
                    sample_opened = null;
                }
            },
            onFailure: function (t) {
                requesting = false;
                $("act_" + sample_id).innerHTML = default_buttons(sample_id);
                sample_id = 0;
                alert("Kļūda dzēšot datus");
            },
        };
        requesting = true;
        new Request(opt).send();
        return false;
    }
}
