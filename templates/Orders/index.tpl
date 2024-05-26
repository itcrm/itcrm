<h2 align="center">[[:Title:]]</h2>
<form onkeydown="return rejectEnter(event);" id="AddOrdersForm" action="javascript:Save('Orders')" method="POST" class="[:NoAdmin:]">
<input type="text" name="ID" class="hide" />
<table class="add" cellpadding="0" cellspacing="0" width="545" border="1" align="center">
<tr height="30">
    <td width="50"><input type="button" value="&mdash;" onclick="this.form.reset()" /></td>
    <td width="150"><input type="text" name="Code" maxlength="20"/></td>
    <td width="30"><div style="padding:0 5px"><input type="text" name="Color"  /></div></td>
    <td width="200"><input type="text" name="Description" maxlength="200"/></td>
    <td width="20">
       <input type="checkbox" name="RightsAdd" value="1" /><br/>
       <input type="checkbox" name="RightsDel" value="1" style="border:1px solid red;"/>
    </td>
    <td width="95">
        <input type="submit" value="Saglabāt" /><br/>
        <input type="button" onclick="FilterOrders();" value="Meklēt" />
    </td>
 </tr>
</table>
</form>

<table id="OrdersList" cellpadding="0" cellspacing="0" width="545" border="1" align="center">
 <tr class="title">
    <td width="50"><a href="javascript:changeOrderSort('ID')">[[:ID:]]</a> </td>
    <td width="150"><a href="javascript:changeOrderSort('Code')">[[:Code:]]</a>[:Sort:]</td>
    <td width="30">Krāsa</div></td>
    <td width="220">[[:Description:]]</td>
    <td width="95" class="[:NoAdmin:]">[[:Actions:]]</td>
 </tr>
 [:Content:]

</table>
[:Pages:]

<script type="text/javascript">
$(document).ready(function() {
    $('#AddOrdersForm input[name=Color]').colorPicker();
});
</script>
