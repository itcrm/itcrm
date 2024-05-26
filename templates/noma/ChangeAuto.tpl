 <script type="text/javascript">
 $(document).ready(function() {
         $("#ChangeAuto [name=Order]").autocomplete({
    source: "/lv/Josn/Orders",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    $("#ChangeAuto [name=OrderID]").val(ui.item.ID);
            },
    minLength: 1,
});

 })
 </script>

<form ID = ChangeAuto>
<table border="0">

    <tr>
<td>Pasūtijums:</td>
<td><input size = "35" type="text" value="[:Order:]" ID="Order" name="Order" />
    <input size = "35" class="hide" type="text" value="[:OrderID:]" ID="OrderID" name="OrderID" /> </td>
</tr>

<tr>
<td>Nosaukums:</td>
<td>
    <input type="text" class="hide" value="[:ID:]" ID="sanid" name="ID" />
    <input size = "35" value='[:Nosaukums:]' type="text" ID="Nosaukums" name="Nosaukums" /></td>
</tr>

<tr>
<td>Reģistrācijas numurs:</td>
<td><input size = "35" type="text" value="[:Reg_nr:]" ID="Reg_nr" name="Reg_nr" /></td>
</tr>

<tr>
<td>Šasijas Nr:</td>
<td><input size = "35" type="text" value="[:Sasija:]" ID="Sasija" name="Sasija" /> </td>
</tr>

<tr>
<td>Reģistrācijas apliecība:</td>
<td><input size = "35" type="text" value="[:Reg_ap:]" ID="Reg_ap" name="Reg_ap" /></td>
</tr>

<tr>
<td>Vērtība:</td>
<td><input size = "35" type="text" value="[:Vertiba:]" ID="Vertiba" name="Vertiba" /></td>
</tr>

</table>
</form>
    