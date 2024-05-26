function numbersonly(myfield, e, comma, numbers, arrows) {
    var key;
    var keychar;

    if (window.event) {
        key = window.event.keyCode;
        eventThingy = window.event;
    } else if (e) {
        eventThingy = e;
        key = e.which;
    } else {
        return true;
    }

    keychar = String.fromCharCode(key);

    //	alert(key);
    if (arrows) {
        if (key == 38) {
            goto_prev_row = true;
            goto_next_row = false;
            edit_sample_submit();
        } else if (key == 40) {
            goto_next_row = true;
            goto_prev_row = false;
            edit_sample_submit();
        }
    }

    if (numbers == 0) return true;

    /**
     *  Atļaujam arī CTRL-C un CTRL-V, HOME, END, F5
     *  Manuprāt šito vajadzēs pārrakstīt savādāk, lai nav katrs atļautais šitā jāčakarē!
     */
    if (key == 116 || key == 36 || key == 35) return true;
    if (key == 67 && eventThingy.ctrlKey) return true;
    if (key == 86 && eventThingy.ctrlKey) return true;

    if (
        key == null ||
        key == 0 ||
        key == 8 ||
        (key == 190 && comma) ||
        (key == 188 && comma) ||
        (key == 110 && comma) ||
        key == 9 ||
        key == 13 ||
        key == 27 ||
        key == 46
    )
        return true;
    else if (key > 95 && key <= 105) return true;
    else if (key > 36 && key < 41) return true;
    else if ("0123456789".indexOf(keychar) > -1) return true;
    else {
        return false;
    }
}

function show_error(id, error_msg) {
    obj = $(id);
    x = curLeft(obj);
    y = curTop(obj) - 25;
    html =
        '<div class="error_balloon" style="position: absolute; top: ' +
        y +
        "px; left: " +
        x +
        'px;"><span><nobr>' +
        error_msg +
        "</nobr></span></div>";
    $("error_messages").innerHTML += html;
}

function show_warning(id, msg) {
    obj = $(id);
    //	x = curLeft(obj)+obj.offsetWidth;
    //	y = curTop(obj);
    //	html = '<div class="warning_balloon" style="position: absolute; top: '+y+'px; left: '+x+'px;"><span><nobr>'+msg+'</nobr></span></div>';
    //	$('warning_messages').innerHTML += html;

    if (obj) {
        if (msg != null) {
            obj.innerHTML =
                '&nbsp;<img src="template/sys/img/asterisk-small.gif" title="' +
                msg +
                '"/>';
        } else {
            obj.innerHTML = "";
        }
    }
}

function show_errors() {
    $("error_messages").innerHTML = "";
    for (var i = 0; i < sample_fields.length; i++) {
        if (sample_errors[i] != null) {
            show_error(
                sample_fields[i] + "_" + sample_opened + "_val",
                sample_errors[i]
            );
        }
    }
}

function show_warnings() {
    //    $('warning_messages').innerHTML = '';
    for (var i = 0; i < sample_fields.length; i++) {
        show_warning(
            sample_fields[i] + "_warning_" + sample_opened,
            sample_warnings[i]
        );
    }
}

function clear_errors(i) {
    if (sample_errors[i] != null) {
        sample_errors[i] = null;
        show_errors();
    }
}

function clear_warnings(i) {
    if (sample_warnings[i] != null) {
        sample_warnings[i] = null;
        show_warnings();
    }
}

function show_box(id) {
    var xid = "box_helper_" + id;
    for (var i = 0; i < sample_fields.length; i++) {
        if (sample_fields[i] != id) {
            hide_box(sample_fields[i]);
        }
    }
    if ($(xid)) {
        $(xid).style.display = "block";
        $("helper").style.display = "block";
    } else {
        $("helper").style.display = "none";
    }
}

function hide_box(id) {
    var xid = "box_helper_" + id;
    if ($(xid)) {
        $(xid).style.display = "none";
    }
}

var expandables = new Array();

function expandable(id) {
    if (expandables[id] == null) {
        expandables[id] = false;
    }
    if (expandables[id] == false) {
        $("exp_content_" + id).style.display = "block";
        $("exp_link_" + id).className = "collapse";
        expandables[id] = true;
    } else {
        $("exp_content_" + id).style.display = "none";
        $("exp_link_" + id).className = "expand";
        expandables[id] = false;
    }
    return false;
}

function toggle(id) {
    if ($("exp_content_" + id).style.display == "") {
        $("exp_content_" + id).style.display = "none";
        $("exp_link_" + id).className = "expand";
    } else {
        $("exp_content_" + id).style.display = "";
        $("exp_link_" + id).className = "collapse";
    }
    return false;
}
