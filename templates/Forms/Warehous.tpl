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
