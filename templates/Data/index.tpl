    <div id="SaveFail" title="Saglabāšanas kļūda" class="hide">
    <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                    <p> <span class= "ui-icon ui-icon-alert" style= "float: left; margin-right: .3em;"> </span>
                    <strong>Uzmanību:</strong> Neizdevās saglabāt!!!</p>
                </div>
</div>

<div  ID = "MultiEdit" style= "z-index:10; width: 454px; margin: 0px 20px 0px 0px; border-width: 1px; background-color: rgb(200, 131, 23); border-color: black; border-style: solid; height: 28px; padding-left: -59px;  padding-right: -25px; float: right; padding-top: 3px; top: 30px; bottom: 6px; position: fixed; left: 30%; display: none;">

<form class="SelectChange" action="javascript:ChangeSelected()">
<input onclick="$('div#MultiEdit select option:eq(4)').attr('selected', 'selected');ChangeField();$('div#MultiEdit select').attr('disabled', 'disabled');" type="checkbox" name="copy" value="1" id="copy" title="Kopēt">
<select name="fields" onchange="ChangeField()">
        <option value="0">(Nedefinēts)</option>
        <option value="IDDoc">Dok. ID</option>
        <option value="Date">Dok.datums</option>
        <option value="IDPerson"> Dok. aizp.</option>
        <option value="IDOrder">Pasūtijums</option>
        <option value="TextOrder">Pasutījuma teksts</option>
        <option value="IDType">Tips</option>
        <option value="TextType">Tipa teksts</option>
        <option value="Sum">Summa</option>
        <option value="Hours">Stundas</option>
        <option value="PlaceTaken">Notikumu vieta</option>
        <option value="PlaceDone">Nodošanas vieta</option>
        <option value="Note">Piezīmes</option>
        <option value="BookNote">Piezīmju teksts</option>
        <option value="TotalPrice">kop.</option>
        <option value="PriceNote">.</option>
        <option value="AdminEdit">Labot tikai admin</option>
        <option value="Hidden">Redzēt tikai admin</option>
</select>
<span>:</span> <input type= "text" name= "value" ID= "value">
<input type="checkbox" title="Pievienot sākumā" id = "left" value="left" name="position">
<input type="checkbox" title="Pievienot beigās" id = "right" value="right" name="position">
<input type="checkbox" title="Aizvietot" id = "replace" value="replace" name="position">
<input type="submit" style="float: right;" value="Saglabāt">
</form>

</div>

<div class="clear"><!--  --></div>

<form id="AddDataForm" action="javascript:Save('Data'), editbox(0, this)" method="POST" class="[:NoAdmin:]"  style="padding-right: 17px; margin-left: 17px;" onkeydown="return rejectEnter(event)">
<input type="text" name="ID" class="hide" />
<table class="add" cellpadding="0" cellspacing="0" width="98%" border="1" align="center">
<tr height="30">
    <td width="63">
    <span style="position:relative; top:0px; left:0px;">
        <a href="javascript:;" id="newTplBtn" onclick="$('#Tpl').toggle();" style="position:absolute; left:-10px; top:1px;">#</a>
    <select id="Tpl" name="Tpl" style="position:absolute; width:125px; height:18px; top:-2px; left:0px; display:none;" onchange="if($(this).val()>0) $('#newTplBtn').css('color','red'); else $('#newTplBtn').css('color',''); if($(this).val()>1) GetTpl($(this).val(),1); $(this).hide()">
            <option value="0">----</option>
            [:TplList:]
            <option value="1" class="[:HidePeriods:]">[[:NewTpl:]]</option>
    </select>
    </span>
        <input style="margin-left: 7px;" type="text" class="light" name="IDDoc" value="[[:IDDoc:]]"/><br/>

    <span style="position:relative; top:0px; left:0px;" class="[:HidePeriods:]">
        <a href="javascript:;" id="editTplBtn" onclick="$('#TplEdit').toggle();" style="position:absolute; left:-10px; top:1px;">E</a>
    <select id="TplEdit" style="position:absolute; width:125px; height:18px; top:-2px; left:0px; display:none;" onchange="if($(this).val()>0) { $('#editTplBtn').css('color','red'); GetTpl($(this).val()); $('select[name=Tpl]',this.form).val(1); } else $('#editTplBtn').css('color','');  $(this).hide()">
        <option value="0">----</option>
        [:TplList:]
    </select>
    </span>

        <input style="margin-left: 7px;" type="text" class="light" name="RemindDate" id="Reminder" value="[[:Reminder:]]" onclick="C.Reminder = new Calendar({target:$(this),el:$(this),showTime:1,shortYear:1}); return false;"/>
        <input type="text" name="RemindTo" class="hide"/>
    </td>
    <td width="110"><input type="text" class="light" name="Date" value="[[:Date:]]" /><br/>
                    <input type="text" id="timedate" class="light" name="Now" readonly="readonly" value="[:NOW:]" />
                    <span style="position:relative; top:0px; left:0px;"><span id="timedate"></span>
                        <a href="javascript:;" onclick="$('#AddDataForm input[name=Date]').val($('#AddDataForm input[name=Now]').val());" style="text-decoration:none; position:absolute; right:3px; top:-2px;">&uarr;</a>
                    </span>
                    </td>
    <td width="50"><input type="hidden" name="IDPerson" />
                   <input type="text" class="light" name="PersonSelect" value="[[:Operator:]]" /><br/>
                   <input type="text" class="light" readonly="readonly" value="[:Login:]" /></td>
    <td width="70"><input type="hidden" name="IDOrder" />
                   <input type="text" class="light" name="OrderSelect" value="[[:Order:]]" /><br/>
                   <input type="text" class="light" name="TextOrder" value="[[:OrderText:]]"/></td>
    <td width="70"><input type="hidden" name="IDType" />
                   <input type="text" class="light" name="TypeSelect" value="[[:Type:]]" /><br/>
                   <input type="text" class="light hide" name="RemindDateEnd" value="" />
                   <input type="text"  class="light" name="TextType" value="[[:TypeText:]]"/></td>
    <td width="60"><input type="text" class="light" name="Sum" value="[[:Sum:]]"/><br/>
                   <input type="text" class="light" name="Hours" value="[[:Hours:]]"/></td>
    <td width="150"><input type="text" class="light" name="PlaceTaken" value="[[:PlaceTaken:]]" /><br/>
                    <input type="text" class="light" name="PlaceDone"  value="[[:PlaceDone:]]" /></td>
    <td><input type="text"  style="width:99%;" name="Note" class="light"  value="[[:Notes:]]"><br/><input  style="width:99%;" class="light" type="text" name="BookNote"  value="[[:BookNotes:]]"></td>
    <td width="50">
        <input type="text" class="light" name="TotalPrice" value="[[:TotalPrice:]]"><br/>
        <input type="text" class="light" name="PriceNote" value="[[:PriceNote:]]">
    </td>
    <td width="124">
     <input type="checkbox"  title="Var labot tikai administrātors vai aizpildītājs."  class="" name="AdminEdit" value="1" style="width:15px; position:relative; top:2px; "/>
    <input type="submit" value="[[:Save:]]"  style="width:70px" onclick="addAutocomplete(); $('#newTplBtn, #editTplBtn').css('color','');" /><li onclick="event.returnValue = false;  MultiEdit(); return false;" style=" display:block; cursor: pointer; float: right; height: 16px;width: 16px;  list-style-type: none; margin: 2px;" title="" class="ui-state-default ui-corner-all">
            <span class="ui-icon ui-icon-check"> </span>
            <span class="text" style="display: none;">.ui-icon-circle-close</span>
        </li>
    <br/><input type="checkbox" title="Slēp! Var redzēt tikai administrātors." name="Hidden" value="1" style="width:15px; position:relative; top:2px; " class="[:HidePeriods:]"/>
    <input style="width:70px;"  type="button" value="&mdash;" onclick="this.form.reset(); $('#newTplBtn, #editTplBtn').css('color',''); $('input:not(:button,:submit)',this.form).addClass('light'); $('#DataList .onedit').removeClass('onedit'); $('#FilterForm').removeClass('hideFilter'); $('input.active').removeClass('edit'); $('input.disabl').removeClass('edit'); editbox(0,this); $('#ievadeNoliktava').remove();  $('tr.Selected').removeClass('Selected').addClass('selected'); " />
  <!--  <span style="position:relative; top:0px; left:0px;">
        <a href="javascript:;" onclick="reversEdit()" style="position:absolute; right:-12px; top:0px;">A</a>
  </span> -->

        <br/>
    </td>
 </tr>
</table>
</form>
<div onclick="javascript:$('.slieder').toggle('slow'); $('.SLO').toggle(); $.post('/lv/Warehous/slieder');" class="SLO" title="Aizvērt izvēli" style="width: 20px; background: none repeat scroll 0% 0% rgb(85, 85, 85); margin-top: -5px; position: fixed; height: 20px; cursor: pointer; float: left; display: [:SLO:];"> <span style="margin-top: 2px;" class="ui-icon ui-icon-triangle-1-e"></span> </div>
<div class="slieder" style="background: none repeat scroll 0% 0% rgb(204, 204, 204); border-bottom: 1px solid; padding-left: 21px; padding-top: 1px; height: 20px; border-top: 1px solid; display: [:slieder:]; margin-top: 2px; margin-bottom: 2px;">

<!-- <div class="TaskUsers" style="float:left; position:relative; top:-18px;"> -->

{:Reminder:}
<a href="[:URL:]/Data/Reminder/[:RemindTo:]" class="reminder" style="color:[:Alert:]; background:[:Color:];">[:Login:]</a>
{:/Reminder:}

<!-- </div> -->
<div title="Aizvērt izvēli" style="width: 20px; float: right; background: none repeat scroll 0% 0% rgb(85, 85, 85); cursor: pointer; height: 21px; margin-top: -1px;" class="ui-widget ui-helper-clearfix" onclick="javascript:$('.slieder').toggle('slow'); $('.SLO').toggle(); $.post('/lv/Warehous/slieder');"> <span style="margin-top: 2px;" class="ui-icon ui-icon-triangle-1-w"></span> </div>
</div>
<!-- onkeydown="return rejectEnter(event)" -->
<form action="javascript:FilterData()"  id="FilterForm"  style="padding-right:17px;"  class="[:showFilter:]">
<table cellpadding="0" cellspacing="0" width="98%" border="1" align="center">
 <tr class="title">
      <td width="21">
        <input type="button" value="+"  OnClick="CeckAllRow()">
        <input type="button" value="--" OnClick="UnCeckAllRow()">

    </td>
    <td width="81">
        <a href="/lv/Data/Export" title="Exports uz ms excel"><img style="float: left; margin-left: 5px; width: 25px; border: none" src="/images/Export.png"></a>
<input type="text" name="IDDoc" value="[:IDDoc:]" />
<input type="text" name="ID" value="[:ID:]"/><br/>

    </td>
    <td width="110">
      <a href="javascript:changeSort()" style="color:#000; font-size:10px">[:DateSort:]</a><br/>
                <select onchange="changeDateInterval($(this).val())">
                        <option value="0">------</option>
                        <option value="5">[[:AllTime:]]</option>
                        <option value="1">[[:Today:]]</option>
                        <option value="2">[[:Week:]]</option>
                        <option value="3">[[:Month:]]</option>
                        <option value="4">[[:Year:]]</option>
                   </select>
                   <div><input type="text" name="DateFrom" value="[:DateFrom:]" [:ReadOnly:] /></div>
                   <div><input type="text" name="DateTo" value="[:DateTo:]" [:ReadOnly:] /></div>

    </td>
    <td width="50">[[:Operator:]]<br/>
                   <input type="hidden" name="Person" value="[:Person:]" />
                   <input type="hidden" name="Operator" value="[:Operator:]" />
                   <input type="text" name="PersonFilterSelect" value="[:PersonFilterSelect:]"/><br/>
                   <input type="text" name="OperatorFilterSelect" />
    </td>
    <td width="70"><br/>[[:Order:]]<br/>
                   <input type="hidden" name="Order" value="[:Order:]" />
                   <input type="text" name="OrderFilterSelect" value="[:OrderFilterSelect:]" /><br/>
                   <input type="text" name="TextOrder" value="[:TextOrder:]" />
    </td>
    <td width="70"><br/>[[:Type:]]<br/>
                   <input type="hidden" name="Type" value="[:Type:]" />
                   <input type="text" name="TypeFilterSelect" value="[:TypeFilterSelect:]" /><br/>
                   <input type="text" name="TextType" value="[:TextType:]" />
    </td>
    <td width="60">[:Total:]<br/>[:TotalHours:]<br/>
                   <input type="text" name="Sum" value="[:Sum:]" /><br/>
                   <input type="text" name="Hours" value="[:Hours:]" />
    </td>
    <td width="150"><br/>[[:Place:]]<br/>
                    <input type="text" name="PlaceTaken" value="[:PlaceTaken:]"/><br/>
                    <input type="text" name="PlaceDone" value="[:PlaceDone:]" />
    </td>
    <td><!-- [[:Notes:]] --><br/>
        <input style="width:99%; background:#BBB;" type="text" name="Search" value="[:Search:]"><br/>
        <input style="width:99%;" type="text" name="Note" value="[:Note:]"><br/>
        <input style="width:99%;" type="text" name="BookNote" value="[:BookNote:]">
    </td>
    <td width="50">[:PriceTotal:]<br/>[[:TotalPrice:]]<br/>
        <input type="text" name="TotalPrice" value="[:TotelPrice:]"><br/>
        <input type="text" name="PriceNote" value="[:PriceNote:]">
    </td>
    <td width="124">[[:Actions:]]<br/>
    <select name="IDFilter" onchange="getFilterData($(this).val());"><option value="0">[[:NoFilter:]]</option>[:Filters:]</select><br/>
    <input type="checkbox" name="FindDeleted" [:FindDeleted:]  value="1" style="width:15px; position:relative; top:2px; "/>
    <input style="width:70px" type="submit" onclick="filterAutocomplete()" value="[[:Filter:]]" /><br/>
    <input type="submit" onclick="$('input:not(:submit,:button), select',this.form).val('');" value="&mdash;" />
    </td>
 </tr>
</table>
</form>

<div id="scrollDiv">
<table id="DataList" cellpadding="0" cellspacing="0" border="1" align="center">
<col width="21">
<col width="81">
<col width="111">
<col width="51">
<col width="71">
<col width="71">
<col width="61">
<col width="151">
<col>
<col width="51">
<col width="124">

<tr>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    </td>
    <td>
    </td>
</tr>
 [:Content:]

</table>
<div style="width:100%; height:40px;"></div>
   <div id="info" align="center">
    <div class="main">
      <a class="extra close" style="float:right;" href="close" onclick="event.returnValue = false;  $('#info').hide(); return false;"></a>
      <div style="float:right; width:20%; padding-right:10px;">
            <input type="text" style="width:100%;" onkeyup="filterSuppliers(this.value)" id="filterSups"/>
      </div>
      <form id="SupplierForm" action="javascript:SaveSupplier()" class="[:NoAdmin:]" onkeydown="return rejectEnter(event)" style="width:70%;">
         <input type="text" class="hide" name="ID" />
         <input type="hidden" name="IDData" />

         <div style="float:left; width:85%;">
            <input type="text" class="light" name="Name" maxlength="30" value="[[:Name:]]" style="width:99%;"/>
            <input type="text" class="light" name="Description" maxlength="200" value="[[:MoreData:]]" style="width:99%;"/>
         </div>
         <input type="text" name="Color"  />
         <input type="submit" value="[[:Save:]]" />
         <input type="button" name="Reset" onclick="this.form.reset(); $('a',this.form).hide(); $(this).hide()" value="&mdash;" style="margin-left:7px; display:none;" />
         <span class="[:NoAdmin:]">
            <a class="extra delete hide" href="javascript:Delete($('#SupplierForm input[name=ID]').val(),'Suppliers');"></a>
         </span>
      </form>
      <div class="clear"><!--  --></div>
      <div id="Suppliers">
        [:Suppliers:]
      </div>
      <div class="clear"><!--  --></div>
    </div>
   </div>

      <div id="pavadzime" align="center" style="display: none;">
    <div class="main">
     <a class="right button" onclick="event.returnValue = false;  $('#pavadzime').hide(); return false;"> <span class= "ui-icon ui-icon-circle-close"> </span>[[:Close:]]</a>
     <a class="right button" href="javascript:bildsave()"><span class="ui-icon ui-icon-disk"> </span>[[:Save:]]</a>
     <a class="right button" href="javascript:print()"><span class="ui-icon ui-icon-print"> </span>[[:Print:]]</a>

      <div class="clear"><!--  --></div>
      <div id="PDati">
        [:Suppliers:]
      </div>
      <div class="clear"><!--  --></div>
    </div>
   </div>

         <div id="Noma" align="center" style="height: auto; left: 16px; position: absolute; top: 35px; width: 98%; display: none;">
             <a class="extra close" style="float:right; position: absolute; right: 11px; top: 11px;" href="#" onclick="event.returnValue = false;  tinymce.execCommand('mceRemoveControl',true,'elm1'); $('#elm1').val(''); $( '#Noma' ).tabs( 'destroy' ); $('#Noma').hide(); return false;"></a>
                 <div class="main">

      <div class="clear"><!--  --></div>
      <div id="Tabs">
       <ul>
        <li><a href="/lv/Josn/Pavadzime">Pavadzīme</a></li>
        <li><a href="/lv/Josn/NomaLigums">Līgums</a></li>
        <li><a href="/lv/Josn/NomaAkts">Akts</a></li>
        <li><a href="/lv/Josn/NomaPielikums">Pielikums</a></li>
        <li><a href="/lv/Josn/NomaOptions">Opcijas</a></li>
    </ul>
      </div>
      <div class="clear"><!--  --></div>
    </div>
   </div>

    <div id="noliktava" align="center" style="display: none;">
        <div class="main">

                 <a  href="close" onclick="event.returnValue = false;  $('#noliktava').hide();  clerNoliktava(); return false;">
     <li style="float: right; height: 16px;width: 16px;  list-style-type: none; margin: 2px;" title="Aizvērt" class="ui-state-default ui-corner-all">
            <span class= "ui-icon ui-icon-circle-close"> </span>
            <span class="text" style="display: none;">.ui-icon-circle-close</span>
        </li></a>

        <div class="DetDat">
                <h2>Detaļas apraksts</h2>
                <form id="DetalasForm">
<label for="artikuls" title="Detaļas artikuls">Artikuls:</label><input style="display: none" id="rindasID" name="rindasID" type="text" value=""/><input style="display: none" id="detalasID" name="detalasID" type="text" value=""/><input id="artikuls" type="text" value=""/> <label for="daudzums" name="daudzums" title="Daudzums">Daudzums:</label><input id="daudzums" name="daudzums" value="" size="5" type="number"/> :<input  style="border:none;"  ID="mervieniba" type="text" value="" readonly="readonly" />
                    <hr>
                        <span id="atlikums"></span>
                            <div  style="height: 20px;"></div>
                </form>
            </div>
        </div>
    </div>

<div style="display:none" id="AddNol" title="Pievienot jaunu preci">
    <form id="NewDetForm"style="width: 275px;">

        <table border="0">
        <tr>
        <td><label for="OrderSelect" title="Īpašnieks">Īpašnieks:</label></td>
        <td><input id="OrderSelect" type="text" name="OrderSelect">
            <input style="display: none;" id="IDOrder" type="text" name="IDOrder">
            <input style="display: none;" id="IDType" type="text" name="IDType" >
            <input style="display: none;" id="IDPerson" type="text" name="IDPerson" value="[:UserID:]">
        </td>
        </tr>
        <tr>
        <td><label for="PlaceTaken" title="Artikuls">Artikuls:</label></td>
        <td><input id="PlaceTaken" type="text" name="PlaceTaken"></td>
        </tr>
        <tr>
        <td><label for="Note" title="Nosaukums">Nosaukums:</label></td>
        <td><input id="Note" type="text" name="Note"></td>
        </tr>
        <tr>
        <td><label for="PriceNote" title="Mērvienība">Mērvienība:</label></td>
        <td><input id="PriceNote" type="text" name="PriceNote"></td>
        </tr>
        <tr>
        <td><label for="daudzums" title="Vien.pārd.cena">Vien.pārd.cena:</label></td>
        <td><input id="daudzums" type="text" name="daudzums"></td>
        </tr>
        <tr>
        <td><label style="display: none;" for="TotalPrice" title="Daudzums">Daudzums:</label></td>
        <td><input style="display: none;" id="TotalPrice" type="text" name="TotalPrice"></td>
        </tr>
        <tr>
        <td><label for="PlaceDone" title="Novietojums">Novietojums:</label></td>
        <td><input id="PlaceDone" type="text" name="PlaceDone"></td>
        </tr>
        <tr>
        <td><label for="detalasID" title="Min atlikums">Min atlikums:</label></td>
        <td><input id="detalasID" type="text" name="detalasID"></td>
        </tr>
        <tr>
        <td><label for="BookNote" title="Pielietojums">Pielietojums:</label></td>
        <td><input id="BookNote" type="text" name="BookNote"></td>
        </tr>
        </table>
    <input style="display: none;" type="text" name="RemindDate" value="00.00.00 00:00:00" />
    <input style="display: none;" type="text" name="Date" value="[:NOW:]" />
</form></div>

<div id="matreals" align="center" style="display: none;">
        <div class="main" style="background: none repeat scroll 0% 0% rgb(252, 251, 196);">

                 <a  href="close" onclick="event.returnValue = false;  $('#matreals').hide(); clerDetalas();   return false;">
     <li style="float: right;   list-style-type: none; margin: 2px;" title="Aizvērt" class="ui-state-default ui-corner-all">
            <button><span class="ui-icon ui-icon-circle-close"></span></button>
            <span class="text" style="display: none;">.ui-icon-circle-close</span>
        </li></a>

        <a  href="javascript:NoliktavaSave()">
        <li style="float: right;   list-style-type: none; margin: 2px;" title="Saglabāt" class="ui-state-default ui-corner-all">
            <button><span class="ui-icon ui-icon-disk"></span></button>
            <span class="text" style="display: none;">.ui-icon-disk</span>
        </li></a>

        <div class="matrealaapraksts">
                <h2>Detaļas <span id="Nosaukums"></span> apraksts</h2>
                <form id="MatrealsForm">

                    <input style="display: none" id="rindasID" name="rindasID" type="text" value=""/>
                    <input style="display: none" id="SuperID" name="SuperID" type="text" value="0"/>

                            <!-- <div  style="height: 20px;"></div> -->

<div id="detview">
<table  style="width: 27%;" border="0">
<tr>
<td><span>Pārdošanas cena:</span></td>
<td><input id="daudzums"  name="daudzums" value="" size="10" type="number"/></td>
<td><span>Minimālais atlikums:</span></td>
<td><input id="detalasID" name="detalasID" value="" size="10" type="number"/></td>
</tr>
<tr>
<td><span>Rezervēts:</span></td>
<td><input type="text" readonly="readonly" size="10" value=""  id="Hours"></td>
<td><span>Atlicis:</span></td>
<td><input type="text" readonly="readonly" size="10" value="" n id="TotalPrice"></td>
</tr>

<tr>
<td><span>Artikuls:</span></td>
<td><input type="text"  size="20" value=""  id="PlaceTaken" name="PlaceTaken"></td>
<td><span>Mērvienība:</span></td>
<td><input type="text"  size="10" value=""  id="PriceNote" name="PriceNote"></td>
</tr>

<tr>
    <td COLSPAN="4"><span>Atrašanās vieta:</span></td>
</tr>
<tr>
    <td COLSPAN="4"><textarea rows="1" cols="60"  id="PlaceDone" name="PlaceDone"></textarea></td>
</tr>
<tr>
    <td COLSPAN="4"><span>Piezīmes:</span></td></tr><tr>
    <td COLSPAN="4"><textarea rows="5" cols="60" name="Note" id="Note"></textarea></td>
</tr>
<tr>
    <td COLSPAN="4"><span>Pielietojums:</span></td></tr><tr>
    <td  COLSPAN="4"><textarea rows="1" cols="60" name="BookNote" id="BookNote"></textarea></td>
</tr>
</tr>
<tr>
    <td COLSPAN="4"><span>Veikals:</span></td></tr><tr>
    <td  COLSPAN="4"><input type="checkbox" value="1" ID="Shop" name="Shop"></textarea></td>
</tr>
</table>
<table>
<table border="1"  style="width: 32%;">
<tr style="background-color: yellow;">
<td><span>Nosaukums: <input type="text" size="32" ID="ShopTitle" name="ShopTitle"></span></span></td>
<td><span>Orģinālkods: <input type="text" size="42" ID="OrginalCode" name="OrginalCode"></span></td>
</tr>
<tr style="background-color: yellow;">
<td><span>Cenas %: <input type="text" size="5" value="100" ID="addition" name="addition"></span>&nbsp;&nbsp; <span><input type="checkbox" ID="offer" name="offer"value="1">:Akcija</span></td>
<td><span>Redzams:<input type="checkbox" ID="visible" name="visible" value="1"></span> <span> Lietota:<input type="checkbox" value="1" name="used" id="used"></span> <span> Pieejamība:<select name="state" id="state"><option selected="selected" value="0">Nav pieejams</option><option value="1">Pieejams</option><option value="2">Pasūtāms</option><option value="3">Izgatavojams</option></select></span></td>
</tr>
<tr style="background-color: yellow;">
<td><span>Kategorija: <input type="text"  size="34" value=""  id="ShopCategory"> <input style="display: none" type="text"  size="35" id="ShopCategoryID" name="ShopCategoryID"></span></td>
<td><span>Modeļi: <input type="text"  size="35" value=""  id="ShopModel"><input style="display: none" type="text"  size="35" id="ShopModelID" name="ShopModelID"></span></td>
</tr>
<tr style="background-color: yellow;" >
    <td COLSPAN="2"><span>Apraksts:</span></td></tr><tr>
    <td style="background-color: yellow;" COLSPAN="4"><textarea rows="5" cols="70" name="ShopDescription" id="ShopDescription"></textarea></td>
</tr>
</table>

                </div></form>
            <div  style="height: 20px;"></div>
        </div>
    </div>
</div>

<div ID="DialogForm"></div>

<div id="upload_dialog" title="Pievienot failus" align="center" style="display: none;">
    <div class="upload">
     <form ID="Upload"  action="/faili/xml/rowimage.php?rowid=" method="post" enctype="multipart/form-data">
      <label for="file">Pievienot failu / failus</label>
      <input type="file" name="r_bilde" id="r_bilde"/>
      <br />
       <br />
      <input type="submit" name="submit" value="Pievienot" />
</form>
    </div>
   </div>

<div class="editbox" style="display:none">
<textarea ondblclick= "editbox(0,this); el.removeClass('edit'); el.removeClass('active')" id ="edittext" rows="10" cols="30" style="top: 30px; position: absolute; left: 400px;"></textarea>
</div>

<div id="rindas_bilde" title="Rindas bilde" align="center" style="display: none;">
    <div class="r_bilde">

        <img class="r_bilde_link" ID="" width="800px" height="600px" src=""/>

    </div>
    <a class="enable-create" onClick="$( 'div.r_bilde' ).photoTagger( 'enableTagDeletion' )" href="#">Atļaut labot</a>
   </div>

    <div class="changes" id="Changes" style="position:absolute;"></div>
</div>

     [:Pages:]

    <style>
    .ui-corner-all{
        border-radius: 0px 0px 0px 0px;
    }

    .ui-autocomplete {
        max-height:  300px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 20px;
    }

    * html .ui-autocomplete {
        height: 300px;
    }
    </style>

 <script type="text/javascript">
var orders = [ [:OrdersList:] ];
var types = [ [:TypesList:] ];
var users = [ [:UsersList:] ];

var usersAllowed = [ [:AllowedUsersList:] ];
var ordersAllowed = [ [:AllowedOrdersList:] ];
var typesAllowed = [ [:AllowedTypesList:] ];

 $(document).ready(function() {

$("form.SelectChange input:checkbox").change(function(){
 if ($('form.SelectChange input:checkbox').is(':checked')) {
     $('form.SelectChange input:checkbox').attr('disabled', true);
     $(this).attr('disabled', false);
    } else{
        $('form.SelectChange input:checkbox').attr('disabled', false);
    }
}
);

 $('#SupplierForm input[name=Color]').colorPicker();
      $('#SupplierForm input[name=Name]').bind('focus',function() {
            if(this.value=='[[:Name:]]') {
                this.value='';
                $(this).removeClass('light');
            }
       }).bind('blur',function() {
            if(this.value=='') {
                this.value='[[:Name:]]';
                $(this).addClass('light');
            }
       });

      $('#SupplierForm input[name=Description]').bind('focus',function() {
            if(this.value=='[[:MoreData:]]') {
                this.value='';
                $(this).removeClass('light');
            }
       }).bind('blur',function() {
            if(this.value=='') {
                this.value='[[:MoreData:]]';
                $(this).addClass('light');
            }
       });

// Add Autocomplite

$(".add [name=PersonSelect]").autocomplete({
    source: "/lv/Josn/Persons",
    minLength: 2,
});

$(".add [name=TypeSelect]").autocomplete({
    source: "/lv/Josn/Types",
    select: function( event, ui){
        AddNoliktavaForm(ui.item.ID);
    },
    minLength: 1,
});

$(".add [name=OrderSelect]").autocomplete({
    source: "/lv/Josn/Orders",
    minLength: 1,
});

// Filter Autocomplite
  $("#FilterForm [name=OrderFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterOrders",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 2,
});

 $("#FilterForm [name=PersonFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterPersons",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 2,
});

$("#FilterForm [name=TypeFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterTypes",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 1,
});

$("#FilterForm [name=OperatorFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterPersons",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 2,
});

 Names =  { IDDoc:'[[:IDDoc:]]', Date:'[[:Date:]]', PersonSelect:'[[:Operator:]]',
            OrderSelect:'[[:Order:]]', TextOrder:'[[:OrderText:]]',
            TypeSelect:'[[:Type:]]', TextType:'[[:TypeText:]]',
            Sum:'[[:Sum:]]', Hours:'[[:Hours:]]', PlaceTaken:'[[:PlaceTaken:]]', PlaceDone:'[[:PlaceDone:]]',
            Note:'[[:Notes:]]', BookNote:'[[:BookNotes:]]', TotalPrice:'[[:TotalPrice:]]',  PriceNote:'[[:PriceNote:]]',RemindDate:'[[:Reminder:]]' } ;

      $('.add input, .add select').bind('focus',function() {
          el = $(this);
          el.addClass('active');
            if(this.value==Names[this.name]) {
                this.value='';
                el.removeClass('light');
            }
      }).bind('blur',function() {
          el = $(this);
          el.removeClass('active');
            if(this.value=='') {
                this.value=Names[this.name];
                el.addClass('light');
          }
      }).bind('dblclick',function() {
            if(el.hasClass('edit')) {
             editbox(0,this)
            el.removeClass('edit')
            } else {
             el.addClass('edit')
            editbox(1,this)
            }
       });

      $('#DataList input, #DataList select').bind('focus',function() {
          $(this).addClass('active');
      }).bind('blur',function() {
          $(this).removeClass('active');
      });

        filterAutocomplete(1);

            // Hook up the enable create links.
            $( "a.enable-create" ).click(
                function( event ){
                    // Prevent relocation.
                    event.preventDefault();

                    // Get the container and enable the tag
                    // creation on it.
                    $( this ).prevAll( "div.r_bilde" )
                        .photoTagger( "enableTagCreation" )
                    ;
                }
            );
    });

function split( val ) {
            return val.split( /,\s*/ );
        }

 function MultiEdit(){
      $('#MultiEdit').toggle("blind");
  }
 </script>
  <script>
const pad = num => ("0" + num).slice(-2);
const timedate = () => {
  const currentTime = new Date(new Date().getTime() + diff);
  let hours = currentTime.getHours();
  const minutes = pad(currentTime.getMinutes());
  const seconds = pad(currentTime.getSeconds());

  const d = currentTime.getDate();
  const day = pad(d);
  const month = pad(currentTime.getMonth() + 1);
  const yyyy = new Intl.DateTimeFormat("en", {year: "2-digit"}).format(new Date());

  hours = pad(hours);
  timeOutput.value = "" +
    yyyy + "." + month + "." + day +
    " " +
    hours + ":" +
    minutes + ":" +
    seconds// + dn;
}
let timeOutput;
let serverTime;
let diff;
window.addEventListener("load", function() {
  timeOutput = document.getElementById("timedate");
  serverTime = new Date;// change to new Date("[[:Date:]]"); for example
  diff = new Date().getTime() - serverTime.getTime();
  setInterval(timedate, 1000);
});
</script>
 <style>
 div.minimize{margin-bottom: 10px;}
 </style>
