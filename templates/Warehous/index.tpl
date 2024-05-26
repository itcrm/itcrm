<div onclick="javascript:$('.slieder').toggle('slow'); $('.SLO').toggle(); $.post('/lv/Warehous/slieder');" class="SLO" title="Aizvērt izvēli" style="width: 20px; background: none repeat scroll 0% 0% rgb(85, 85, 85); margin-top: -5px; height: 35px; cursor: pointer; float: left; display: [:SLO:];"> <span style= "margin-top: 8px;" class="ui-icon ui-icon-triangle-1-e"> </span> </div>

<div class="slieder" style="background: none repeat scroll 0% 0% rgb(204, 204, 204); margin-top: -5px; border-bottom: 1px solid; height: 30px; padding-top: 5px; padding-left: 21px; display: [:slieder:];">
<button onclick="javascript:NolCeckAllRow()" title="Iezīmēt visus" class="ui-widget"><span class="ui-icon ui-icon-plusthick"> </span></button>
<button onclick="javascript:UnCeckAllRow()" title="Atcelt iezīmētos" class="ui-widget"><span class="ui-icon ui-icon-minusthick"> </span></button>
<button onclick="window.location = '/lv/Warehous/Export'" title="Eksportēt uz Excel" class="ui-widget"><span class="ui-icon ui-icon-script"> </span></button>
<div title="Aizvērt izvēli" style=" width: 20px; float: right; background: none repeat scroll 0% 0% rgb(85, 85, 85); margin-top: -5px; height: 35px; cursor: pointer;" class="ui-widget ui-helper-clearfix" onclick="javascript:$('.slieder').toggle('slow'); $('.SLO').toggle(); $.post('/lv/Warehous/slieder');"> <span style="margin-top: 8px;" class="ui-icon ui-icon-triangle-1-w"> </span> </div>
</div>

<h2 align="center">Noliktava</h2>
<div id="scrollDiv">
<table id="DataList" cellpadding="0" cellspacing="0" border="1" align="center">
<col width="21">
<col width="61">
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
</div>

<div id="rindas_bilde" title="Rindas bilde" align="center" style="display: none;">
    <div class="r_bilde">

        <img class="r_bilde_link" ID="" width="800px" height="600px" src=""/>

    </div>
    <a class="enable-create" onClick="$( 'div.r_bilde' ).photoTagger( 'enableTagDeletion' )" href="#">Atļaut labot</a>
   </div>

<div id="matreals" align="center" style="display: none;">
  <div class="main">
    <a  href="close" onclick="event.returnValue = false;  $('#matreals').hide(); clerDetalas();  return false;">
      <li style="float: right; height: 16px;width: 16px;  list-style-type: none; margin: 2px;" title="Aizvērt" class="ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-circle-close">
        </span>
        <span class="text" style="display: none;">.ui-icon-circle-close
        </span>
      </li></a>
    <a  href="javascript:NoliktavaSave()">
      <li style="float: right; height: 16px;width: 16px;  list-style-type: none; margin: 2px;" title="Saglabāt" class="ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-disk">
        </span>
        <span class="text" style="display: none;">.ui-icon-disk
        </span>
      </li></a>

    <div class="matrealaapraksts">
        <h2>Detaļas apraksts</h2>
      <form id="MatrealsForm">
        <input style="display: none" id="rindasID" name="rindasID" type="text" value=""/>
        <div id="detview">
          <table  style="width: 27%;" border="0">
            <tr><td>
                <span>Pārdošanas cena:
                </span></td><td>
                <input id="daudzums"  name="daudzums" value="" size="10" type="number"/></td><td>
                <span>Minimālais atlikums:
                </span></td><td>
                <input id="detalasID" name="detalasID" value="" size="10" type="number"/></td>
            </tr>
            <tr><td>
                <span>Rezervets:
                </span></td><td>
                <input type="text" readonly="readonly" size="10" value=""  id="Hours"></td><td>
                <span>Atlicis:
                </span></td><td>
                <input type="text" readonly="readonly" size="10" value="" n id="TotalPrice"></td>
            </tr>
            <tr>
              <td COLSPAN="4">
                <span>Atrasanas vieta:
                </span></td>
            </tr>
            <tr>
              <td COLSPAN="4">
                <textarea rows="1" cols="60" readonly="readonly" id="PlaceDone"></textarea></td>
            </tr>
            <tr>
              <td COLSPAN="4">
                <span>Piezimes:
                </span></td>
            </tr>
            <tr>
              <td COLSPAN="4">
                <textarea rows="5" cols="60" readonly="readonly" id="Note"></textarea></td>
            </tr>
            <tr>
              <td COLSPAN="4">
                <span>Pielietojums:
                </span></td>
            </tr>
            <tr>
              <td  COLSPAN="4">
                <textarea rows="1" cols="60" readonly="readonly" id="BookNote"></textarea></td>
            </tr>
          </table>
        </div>
      </form>
      <div  style="height: 20px;">
      </div>
    </div>
  </div>
</div>

