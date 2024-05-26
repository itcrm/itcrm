<div id="Supplier[:IDSupplier:]" class="supplier" style="background:[:InfoColor:]">
    <input type="text" class="picker hide" value="[:InfoColor:]" id="Color[:IDSupplier:]" />
    <div class="label" onclick="if($(this).hasClass('supOver')) saveInfo([:IDSupplier:],[:IDData:]); showInfo(this,'supOver');  $('#Info[:IDSupplier:]').focus();" style="background:[:Color:]">
        <b>[:Name:]</b>
        <a href="#" style="float:right;" class="extra edit" onclick="event.returnValue=false; editSupplier(this.parentNode,[:IDSupplier:]); return false;"></a>
        <span>[:Description:]</span>

        <textarea type="text" rows="2" onclick="showInfo($(this).parent().get(0),'supOver');" id="Info[:IDSupplier:]" maxlength="250" onkeyup="if(event.keyCode==13 && event.shiftKey!=1) { saveInfo([:IDSupplier:],[:IDData:]); showInfo($(this).parent().get(0),'supOver'); }">[:Info:]</textarea>
    </div>
    <div class="info" onclick="showInfo(this)" onmouseout="if($(this).hasClass('over')) showInfo(this)">[:Info:]</div>
</div>
