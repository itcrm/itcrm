<form id="AddDataForm">
  <input type="text" style="display: none;" name="ID" value =[:ID:]>
<table class="add" id="AllDataDialog">

<tr>
<td><span>Dokumenta ID: </span></td>
<td><input type="text" value=[:IDDoc:] name="IDDoc" class="light"></td>
<td><span>Atgādinājums: </span></td>
<td><input type="text" onclick="C.Reminder = new Calendar({target:$(this),el:$(this),showTime:1,shortYear:1}); return false;" value="[:RemindDate:]" id="Reminder" name="RemindDate" class="light"><input type="text" class="hide" name="RemindTo"></td>
<td><span>Dok:</span></td>
<td> <input type="hidden" name="IDPerson">
          <input type="text" value=[:Person:] name="PersonSelect" class="light ac_input" autocomplete="off"></td>
</tr>

<tr>
<td><span>Tips:</span></td>
<td><input type="hidden" name="IDType">
          <input type="text" value="Tips" name="TypeSelect" class="light ac_input" autocomplete="off">
          <br>
          <input type="text" value="" name="RemindDateEnd" class="light hide"> </td>
<td><span>Tipa teksts:</span></td>
<td> <input type="text" value="Tipa teksts" name="TextType" class="light"></td>
<td><span>.:</span></td>
<td><input type="text" value="." name="PriceNote" class="light"></td>
</tr>

<tr>
<td><span>Summa:</span></td>
<td><input type="text" value="Summa" name="Sum" class="light"></td>
<td><span>Stundas: </span></td>
<td><input type="text" value="Stundas" name="Hours" class="light"></td>
<td><span>Kop:</span></td>
<td><input type="text" value="kop." name="TotalPrice" class="light"></td>
</tr>

<tr>
<td><span>Nodošanas vieta:</span></td>
<td colspan="3"><textarea rows="5" cols="80" name="lastname" class="light"></textarea></td>
<td><span>Notikuma vieta:</span></td>
<td><input type="text" value="Notikumu vieta" name="PlaceTaken" class="light"></td>
</tr>

<tr>
<td><span>Pasūtijuma teksts: </span></td>
<td colspan="3"><textarea rows="5" cols="80" name="TextOrder" class="light"></textarea></td>
<td><span>Pasūtijums: </span></td>
<td><input type="hidden" name="IDOrder"><input type="text" value="Pasūtijums" name="OrderSelect" class="light ac_input" autocomplete="off"></td>
</tr>

<tr>
<td><span>Piezīmes: </span></td>
<td colspan="3"><textarea rows="5" cols="80"  class="light" name="Note">[:Note:]</textarea></td>
<td><input type="checkbox" class="" style="width:15px; position:relative; top:2px; " value="1" name="Hidden"></td>

</tr>
<tr>
<td><span>Grāmatveža piezīmes: </span></td>
<td colspan="3"><textarea rows="5" cols="80" name="BookNote" class="light"></textarea></td>
</tr>
</table>

<span align="left"></>Dokumentu izveidoja <input type="text" value="Dok.datums" name="Date" class="light"> un aizpildija <input type="text" name="lastname" />

          <input align="right" type="submit" onclick="addAutocomplete(); $('#newTplBtn, #editTplBtn').css('color','');" style="width:70px" value="Saglabāt">

</form>
