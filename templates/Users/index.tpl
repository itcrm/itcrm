<h2 align="center">[[:Title:]]</h2>
<form id="AddUsersForm" action="javascript:Save('Users');" method="POST">
<input type="text" name="ID" class="hide" />
<table class="add" cellpadding="0" cellspacing="0" border="1" align="center">
<tr height="30">
    <td width="50"><input type="button" value="&mdash;" onclick="$(':input','#AddUsersForm').not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');" /></td>
    <td width="150"><input type="text" name="Login" maxlength="32"/><br/>
                    <input type="text" name="Password" maxlength="32"/></td>
    <td width="30"><div style="padding:0 5px"><input type="text" name="Color"  /></div></td>
    <td width="150"><input type="text" name="Name" maxlength="100"/></td>
    <td width="150"><input type="text" name="Phone"  maxlength="100"/></td>
    <td width="95"><input type="submit" value="Saglabāt" /></td>
 </tr>
</table>
</form>

<table id="UsersList" cellpadding="0" cellspacing="0"  border="1" align="center">
 <tr class="title">
    <td width="30">[[:ID:]]</td>
    <td width="100">[[:Login:]]</td>
    <td width="30">[[:Color:]]</td>
    <td width="100">[[:Name:]]</td>
    <td width="120">[[:Phone:]]</td>
    <td width="95">[[:Actions:]]</td>
 </tr>
 [:Content:]
</table>

<script type="text/javascript">
$(document).ready(function() {
    $('#AddUsersForm input[name=Color]').colorPicker();
});
</script>
