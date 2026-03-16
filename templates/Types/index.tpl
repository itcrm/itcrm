<h2 align="center">[[:Title:]]</h2>
<form onkeydown="return rejectEnter(event);" id="AddTypesForm" action="javascript:Save('Types')" method="POST">
<input type="text" name="ID" class="hide" />
<table class="add" cellpadding="0" cellspacing="0" width="545" border="1" align="center">
<tr height="30">
    <td width="50"><input type="button" value="&mdash;" onclick="this.form.reset()" /></td>
    <td width="150"><input type="text" name="Code" maxlength="20"/></td>
    <td width="230"><input type="text" name="Description" maxlength="200"/></td>
    <td width="95"><input type="submit" value="Saglabāt" /></td>
 </tr>
</table>
</form>

<table id="TypesList" cellpadding="0" cellspacing="0" width="545" border="1" align="center">
 <tr class="title">
    <td width="50">[[:ID:]]</td>
    <td width="150">[[:Code:]]</td>
    <td width="250">[[:Description:]]</td>
    <td width="95">[[:Actions:]]</td>
 </tr>
 [:Content:]
</table>
