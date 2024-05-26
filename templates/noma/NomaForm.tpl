<script type="text/javascript">
$("Form#AddDataForm input#Person").autocomplete({
    source: "/lv/Data/AutocompliteJosn",
    select: function( event, ui){
        $("Form#AddDataForm input#PersonID").val(ui.item.ID);
    },
            minLength: 1,
});

$("Form#AddDataForm input#Auto").autocomplete({
    source: "/lv/Josn/AutoAutocomplite",
    select: function( event, ui){
        $("Form#AddDataForm input#AutoID").val(ui.item.ID);
    },
            minLength: 1,
});

$("Form#AddDataForm input#From").timedatepicker();
$("Form#AddDataForm input#To").timedatepicker();

function SaveNomaForm(){
  var Data = $('Form#AddDataForm').serialize();
                    success = function(answ){
                    Loading(0, 0);
                    try {
                    answ = eval("("+answ+")");
                } catch(ex) {
                    answ = new Array(answ);
                }

        $('#DataList tr:first').before(answ[1]);
          document.getElementById('AddDataForm').reset()
        $('#nomasppr').remove();
        };

        Loading(0, 1);
        $.post(URL + '/Noma/SaveNomaForm', Data, success);

 }

</script>

Klients: <span  style="float: left;" onclick="OpenForm('NewSanemejs','DialogForm','scrollDiv','Jauns','430',0,1)" class="addbutton">&nbsp;</span>
         <span   style="float: left;" onclick="OpenForm('EditSanemejs','DialogForm','scrollDiv','Labot','1500',0)" class="editbutton">&nbsp</span>
         <input ID="Person" type="text" name="Person"/>
<input style="display: none;" type="text" ID="PersonID" name="PersonID"/><br>
Automašīna: <span  style="float: left;" onclick="OpenForm('NewAuto','DialogForm','scrollDiv','Jauns','430',0,1)" class="addbutton">&nbsp;</span>
         <span   style="float: left;" onclick="OpenForm('EditAuto','DialogForm','scrollDiv','Labot automašīnu','800',0)" class="editbutton">&nbsp</span>
          <input type="text" ID="Auto" name="Auto"/>
<input style="display: none;" type="text" ID="AutoID" name="AutoID"/><br>
Līguma Nr: <input disabled="disabled" type="text" id="LigumaNr" name="LNr"/><br>
Periods No: <input type="text" ID="From" name="From"/><br>
Periods līdz: <input type="text" ID="To" name="To"/><br>
Summa: <input type="text" name="Price"/><br>
Nodošanas vieta: <input type="text" value='"Purvzīles", Mārupes pag., Rīgas raj."' name="ReturnLocation"/><br>
Pieņemšanas vieta: <input type="text" value='"Purvzīles", Mārupes pag., Rīgas raj."'  name="GetLocation"/><br>
</br>
<input type="submit" onclick="addAutocomplete(); $('#newTplBtn, #editTplBtn').css('color','');" style="width:70px" value="Saglabāt">
