<div align="center" >
        <div class="main" style="background: none repeat scroll 0% 0% rgb(252, 251, 196);">

        <a class="right button" href="javascript:NoliktavaDialogSave()"><span class="ui-icon ui-icon-disk"> </span>Saglabāt</a>

        <div class="matrealaapraksts">
                <h2><span>[:PlaceTaken:]</span> apraksts</h2>
                <form id="MatrealsDialogForm">

                    <input style="display: none" id="rindasID" name="rindasID" type="text" value="[:rindasID:]"/>
                    <input style="display: none" id="SuperID" name="SuperID" type="text" value="[:SuperID:]"/>
                    <input style="display: none" id="AdminEdit" name="AdminEdit" type="text" value="[:AdminEdit:]"/>

<table  style="width: 27%;" border="0">
<tr>
<td><span>Pārdošanas cena:</span></td>
<td><input id="daudzums"  name="daudzums" value="[:daudzums:]" size="10" type="number"/></td>
<td><span>Minimālais atlikums:</span></td>
<td><input id="detalasID" name="detalasID" value="[:detalasID:]" size="10" type="number"/></td>
</tr>
<tr>
<td><span>Rezervēts:</span></td>
<td><input type="text" readonly="readonly" size="10" value="[:Hours:]"  id="Hours"></td>
<td><span>Atlicis:</span></td>
<td><input type="text" readonly="readonly" size="10" value="[:TotalPrice:]" n id="TotalPrice"></td>
</tr>

<tr>
<td><span>Artikuls:</span></td>
<td><input type="text"  size="20" value="[:PlaceTaken:]"  id="PlaceTaken" name="PlaceTaken"></td>
<td><span>Mērvienība:</span></td>
<td><input type="text"  size="10" value="[:PriceNote:]"  id="PriceNote" name="PriceNote"></td>
</tr>

<tr>
    <td COLSPAN="4"><span>Atrašanās vieta:</span></td>
</tr>
<tr>
    <td COLSPAN="4"><textarea rows="1" cols="60"  id="PlaceDone" name="PlaceDone">[:PlaceDone:]</textarea></td>
</tr>
<tr>
    <td COLSPAN="4"><span>Piezīmes:</span></td></tr><tr>
    <td COLSPAN="4"><textarea rows="5" cols="60" name="Note" id="Note">[:Note:]</textarea></td>
</tr>
<tr>
    <td COLSPAN="4"><span>Pielietojums:</span></td></tr><tr>
    <td  COLSPAN="4"><textarea rows="1" cols="60" name="BookNote" id="BookNote">[:BookNote:]</textarea></td>
</tr>
</tr>
<tr>
    <td COLSPAN="4"><span>Veikals:</span></td></tr><tr>
    <td COLSPAN="4"><input type="checkbox" [:Shop:] value="1" ID="Shop" name="Shop"></td>
</tr>
</table>
<table>
<table border="1"  style="width: 32%;">
<tr style="background-color: yellow;">
<td><span>Nosaukums: <input type="text" value="[:ShopTitle:]" size="32" ID="ShopTitle" name="ShopTitle"></span></span></td>
<td><span>Orģinālkods: <input type="text" value="[:OrginalCode:]" size="42" ID="OrginalCode" name="OrginalCode"></span></td>
</tr>
<tr style="background-color: yellow;">
<td><span>Cenas %: <input type="text" size="5" value="100" ID="addition" name="addition"></span>&nbsp;&nbsp; <span><input type="checkbox" [:offer:] ID="offer" name="offer"value="1">:Akcija</span></td>
<td><span> Lietota:<input type="checkbox" value="1" [:used:] name="used" id="used"></span> <span> Pieejamība:<select name="state" id="state">[:piejams:]</select></span></td>
</tr>
<tr style="background-color: yellow;">
<td><span>Kategorija: <select style="width: 150px;" id="ShopCategoryID" name="ShopCategoryID"><option value="0">nav</option>[:Kategorijas:]</select></td>
<td><span ID="models">[:ShopModel:]</span><span><input style="display: none" type="text" value="[:ShopModelID:]" size="35" id="ShopModelID" name="ShopModelID"></span></td>
</tr>
<tr style="background-color: yellow;" >
    <td COLSPAN="2"><span>Apraksts:</span></td></tr><tr>
    <td style="background-color: yellow;" COLSPAN="4"><textarea rows="5" cols="70" name="ShopDescription" id="ShopDescription">[:ShopDescription:]</textarea></td>
</tr>
</table>

                </form>
            <div  style="height: 20px;"></div>
        </div>
    </div>
</div>
<div ID="GrupasMenu[:SuperID:]" title="Grupas">
<div>
 <script type="text/javascript">

 $(document).ready(function() {

$("#MatrealsDialogForm #ShopModel").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/Groups",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );

                    var ID = $("#MatrealsDialogForm input#ShopModelID");
                    var termsID = split( ID.val());
                    termsID.pop();
                    termsID.push( ui.item.ID );
                    termsID.push( "" );
                    ID.val(termsID.join( ", " ));

                    return false;
            },
    minLength: 1,
});

})
 function SaveGrupas(el){
                    var data = $('Form#Grupas').serialize();
                    data = data.replace(/=1&/gi, ',');
                    data = data.replace('=1', ',');
                    PostData = '&ID=' + data + '&form='+[:SuperID:];
                    $('#MatrealsDialogForm #ShopModelID').val(data);
                    success = function(answ){
                    Loading(0, 0);
                    $('#MatrealsDialogForm span#models').html(answ);
                    $('div#GrupasMenu[:SuperID:]').remove();

                    $('div#DialogForm').append('<div id="GrupasMenu[:SuperID:]"></div>');
                    //$('div#GrupasMenu[:SuperID:]').dialog( "destroy" );
                    //el.dialog('close');
                    };

                Loading(0, 1);
                    $.post(URL + '/Data/HTMLGrupas', PostData, success);

}

function CechGrupas(){
    $("div#GrupasMenu[:SuperID:].ui-dialog-content form#Grupas.GrupasDialogs fieldset.visible div input:checkbox").each(function() {
            $(this).attr('checked','checked');
        });
}
function UnCechGrupas(){
    $("div#GrupasMenu[:SuperID:].ui-dialog-content form#Grupas.GrupasDialogs fieldset.visible div input:checkbox").each(function() {
            $(this).removeAttr('checked');
        });
}

 </script>
