<style type="text/css">
.Ir {color:green;
      border-color: black;
      text-align: center;
    }
.Nav {color:ButtonText;
      border-color: black;
      text-align: center;
     }
</style>

<h2 align="center">[[:Title:]]</h2>
<form id="AddUsersForm" action="javascript:Save('Users');" method="POST" class="[:NoAdmin:]">
<input type="text" name="ID" class="hide" />
<table class="add" cellpadding="0" cellspacing="0" border="1" align="center">
<tr height="30">
    <td width="50"><input type="button" value="&mdash;" onclick="$(':input','#AddUsersForm').not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');" /></td>
    <td width="150"><input type="text" name="Login" maxlength="32"/><br/>
                    <input type="text" name="Password" maxlength="32"/></td>
    <td width="30"><div style="padding:0 5px"><input type="text" name="Color"  /></div></td>
    <td width="150"><input type="text" name="Name" maxlength="100"/></td>
    <td width="150"><input type="text" name="Phone"  maxlength="100"/></td>
    <td width="80"><select name="Status">
                      <option value="1">[[:Read:]]</option>
                      <option value="4">[[:SuperUser:]]</option>
                      <option value="5">[[:ReadWrite:]]</option>
                      <option value="99">[[:Admin:]]</option>
                      <option value="-1">[[:Deleted:]]</option>
                    </select>
    </td>
    <td width="25"><input type="checkbox" name="RightsAdd" value="1" />+<br/>
                   <input type="checkbox" name="RightsDel" value="1" style="border:1px solid red;"/>-
    </td>

    <td width="95"><input type="submit" value="Saglabāt" /></td>
 </tr>
</table>
<fieldset style="width: 173px; height: 350px; position: absolute; font-size: 11px;">
<legend><b>Lietotāja tiesības</b></legend>
<input type="checkbox" name="RightsUserAdd" value="1" /> Pievienot lietotajus<br />
<input type="checkbox" name="RightsAddAllUser" value="1" /> Pievienot lietotājiem<br />
<input ID="EditOrder" type="checkbox" name="add_order" value="1" /> Pasūtijumu administrēšana<br />
<input ID="Add_r_bilde" type="checkbox" name="add_r_bilde" value="1" /> Pievienot rindas bildi<br />
<input ID="Add_file" type="checkbox" name="add_files" value="1" /> Pievienot failus<br />
<input ID="OneDay" type="checkbox" name="OneDay" value="1" /> Redzēt tikai šodienu<br />
<input ID="noliktava_ap" type="checkbox" name="noliktava" value="1" /> Noliktavas apraksts<br />
<input ID="MultiChange" type="checkbox" name="MultiChange" value="1" /> Multi labošana<br />
<input ID="DelFile" type="checkbox" name="DelFile" value="1" /> Dzēst failus<br />
<input type="checkbox" value="1" name="CopyRights" id="CopyRights"> Kopēt tiesibas No: <input type="text" id="From" maxlength="5" style="width: 42px;" value=""><input  style="display: none;" type="text" id="FromID" name="FromID" value=""><br>
</fieldset>
</form>

<table id="UsersList" cellpadding="0" cellspacing="0"  border="1" align="center">
 <tr class="title">
    <td width="30">[[:ID:]]</td>
    <td width="100">[[:Login:]]</td>
    <td width="30">[[:Color:]]</td>
    <td width="100">[[:Name:]]</td>
    <td width="120">[[:Phone:]]</td>
    <td width="100" class="[:NoAdmin:]">[[:Rights:]]</td>
    <td width="30" >[[:OrderAdmin:]]</td>
    <td width="30" >[[:R_bilde_Admin:]]</td>
    <td width="30" >[[:File_Admin:]]</td>
    <td width="30" >[[:OneDay:]]</td>
    <td width="30" >[[:noliktava:]]</td>
    <td width="30" >[[:MultiChange:]]</td>
    <td width="30" >[[:DelFile:]]</td>
    <td width="95" class="[:NoAdmin:]">[[:Actions:]]</td>
 </tr>
 [:Content:]
</table>

<script type="text/javascript">
$(document).ready(function() {
    $('#AddUsersForm input[name=Color]').colorPicker();

        $("#From").autocomplete({
source: "/lv/Josn/Persons",
minLength: 2,
select: function( event, ui){
        $('#FromID').val(ui.item.ID);
}
});
});
</script>
