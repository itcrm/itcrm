 <script type="text/javascript">
 $(document).ready(function() {
         $("#ChangeAuto [name=Order]").autocomplete({
    source: "/lv/Josn/Orders",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    $("#ChangeAuto [name=OrderID]").val(ui.item.ID)
            },
    minLength: 1,
});

 })
 </script>
<form ID = ChangeAuto>
<table border="0">

        <tr>
<td>Pasūtijums:</td>
<td><input size = "35" type="text" value="" ID="Order" name="Order" />
    <input size = "35" class="hide" type="text" value="" ID="OrderID" name="OrderID" /> </td>
</tr>

<tr>
<td>Nosaukums:</td>
<td>
    <input type="text" class="hide" value="0" ID="sanid" name="ID" />
    <input size = "35" value='' type="text" ID="Nosaukums" name="Nosaukums" /></td>
</tr>

<tr>
<td>Reģistrācijas numurs:</td>
<td><input size = "35" type="text" value="" ID="Reg_nr" name="Reg_nr" /></td>
</tr>

<tr>
<td>Šasijas Nr:</td>
<td><input size = "35" type="text" value="" ID="Sasija" name="Sasija" /> </td>
</tr>

<tr>
<td>Reģistrācijas apliecība:</td>
<td><input size = "35" type="text" value="" ID="Reg_ap" name="Reg_ap" /></td>
</tr>

<tr>
<td>Vērtība:</td>
<td><input size = "35" type="text" value="" ID="Vertiba" name="Vertiba" /></td>
</tr>

</table>
</form>
    