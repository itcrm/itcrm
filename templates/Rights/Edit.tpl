<table align="center" width="600" border="0">
 <tr><td>
  <select id="IDUser" style="width:120px;" onchange="getUserRights(this.value)">
    <option value="0">-------</option>
    [:Users:]
  </select>
 </td></tr>
</table>

<table align="center" width="600" border="1" id="tblRights">

<tr class="title">
   <td width="180">[[:Persons:]]</td>
   <td width="180">[[:Orders:]]</td>
   <td width="180">[[:Types:]]</td>
   <td width="180">[[:Folders:]]</td>
</tr>
<tr>
   <td>
       <select id="Persons" style="width:120px;"><option value="0">Pievienot visas</option>[:Users:]<option value="minus">Noņemt visas</option></select>
       <input type="button" value="+" onclick="saveRight('Person')" />
   </td>
   <td>
       <select id="Orders" style="width:120px;"><option value="0">Pievienot visas</option>[:Orders:]<option value="minus">Noņemt visas</option></select>
       <input type="button" value="+" onclick="saveRight('Order')" />
   </td>
   <td>
       <select id="Types" style="width:120px;"><option value="0">Pievienot visas</option>[:Types:]<option value="minus">Noņemt visas</option></select>
       <input type="button" value="+" onclick="saveRight('Type')" />
   </td>

   <td>
       <select id="Folders" style="width:120px;"><option value="0">Pievienot visas</option>[:Orders:]<option value="minus">Noņemt visas</option></select>
       <input type="button" value="+" onclick="saveRight('Folder')" />
   </td>

</tr>
<tr>
   <td id="AllowedPersons" valign="top">&mdash;</td>
   <td id="AllowedOrders" valign="top">&mdash;</td>
   <td id="AllowedTypes" valign="top">&mdash;</td>
   <td id="AllowedFolders" valign="top">&mdash;</td>
</tr>

</table>

<br/><br/>

<table align="center" width="600" border="1" id="tblHideRights">

<tr class="title">
   <td width="180">[[:Persons:]]</td>
   <td width="180">[[:Orders:]]</td>
   <td width="180">[[:Types:]]</td>
   <td width="180">[[:Folders:]]</td>
   <td></td>
</tr>
<tr>
   <td><select id="HidePersons" style="width:120px;"><option value="0">-----</option>[:Users:]</select></td>
   <td><select id="HideOrders" style="width:120px;"><option value="0">-----</option>[:Orders:]</select></td>
   <td><select id="HideTypes" style="width:120px;"><option value="0">-----</option>[:Types:]</select></td>
   <td><select id="HideFolders" style="width:120px;"><option value="0">-----</option>[:Orders:]</select></td>
   <td><input type="button" value="+" onclick="saveHideRights()" /></td>
</tr>

</table>
