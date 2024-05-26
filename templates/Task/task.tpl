<script type='text/javascript'>
$(document).ready(function() {
    var ad = [:allDay:];
    if ( ad == 1){
        $("#AllDays").prop("checked", true);
        ///alert("I Am Ok");
    }

    $("form#AddTaskForm input#AllDays").button();

    $("#EndDate").timedatepicker();

$(".ed").dblclick(function(){

if ($(".ed").hasClass('OnEditBox')) {

CloseEditBox();

} else {
var text = $(this).val();
var strSingleLineText = text.replace(new RegExp( " ` ", "g" ),"\n");
$("#edittext").val(strSingleLineText);
$(".editbox").show();
$("#edittext").focus();
$("input.light").attr("disabled", true);
$(this).removeAttr("disabled");
$(this).addClass("OnEditBox");
}});

$(".add [name=PersonSelect]").autocomplete({
    source: "/lv/Josn/Persons",
    minLength: 2,
});

$(".add [name=TypeSelect]").autocomplete({
    source: "/lv/Josn/Types",
    minLength: 1,
});

$(".add [name=OrderSelect]").autocomplete({
    source: "/lv/Josn/Orders",
    minLength: 1,
});

  $("#Reminder").click(function(){ImplementDelete()})
});

function ImplementDelete(){
$(".delete").click(function(){
    f = $('#AddTaskForm');
    $('input[name=RemindDate]',f).val('');
    $('input[name=RemindDateEnd]',f).val('');
    $('input[name=RemindTo]',f).val('');
    $('#RemindPerson').val('');
    $('#calendar'+calendar).hide();

    $('.AddTaskForm input#Data').val('2000-00-00');
    $('.AddTaskForm input#h').val('00');
    $('.AddTaskForm input#m').val('00');
    $('#RemindPerson').val('');
    $('.AddTaskForm input[name=RemindTo]').val('0');

})

}

</script>

<form id="AddTaskForm" action="javascript:CloseEditBox(), Save('Data')" method="POST" class="" style="padding-right:17px;" onkeydown="return rejectEnter(event)">
<input type="text" value="[:ID:]" name="ID" class="hide">
<table  width="100%" cellspacing="0" cellpadding="0" border="1" align="center" class="add">
<tbody><tr height="30">
    <td width="115">

        <input class="light" style="width:99%;" type="text"  name="IDDoc" value="[:IDDoc:]"><br>

        <input class="light" style="width:99%;" type="text"  name="RemindDate" id="Reminder" value="[:RemindDate:]" onclick="C.Reminder = new Calendar({target:$(this),el:$(this),showTime:1,shortYear:1}); return false;">
        <input type="text" name="RemindTo" value="[:RemindTo:]" class="hide">
    </td>
    <td width="125"><input class="light" type="text"  name="Date" value="[:Date:]"><br>
                    <input class="light" type="text"  name="Now" readonly="" value="11.09.19 07:52">
                    <span style="position:relative; top:0px; left:0px;">
                        <a href="javascript:;" onclick="$('#AddTaskForm input[name=Date]').val($('#AddTaskForm input[name=Now]').val());" style="text-decoration:none; position:absolute; right:3px; top:-2px;">?</a>
                    </span>

                    </td>
    <td width="50"> <input type="hidden" name="IDPerson" value="19">
                    <input class="light "type="text" autocomplete="off" class="ac_input" name="PersonSelect" value="[:Person:]"><br>
                    <input class="light" type="text" name="Writer"  readonly="" value="rm"></td>
    <td width="70"> <input type="hidden" name="IDOrder"  value="[:IDOrder:]">
                    <input class="light" type="text" autocomplete="off" class="ac_input" name="OrderSelect" value="[:Order:]"><br>
                    <input class="light ed" type="text"  name="TextOrder" value="[:TextOrder:]"></td>
    <td width="70"> <input type="hidden" name="IDType" value="[:IDType:]">

                    <input class="light" type="text" autocomplete="off" class="ac_input" name="TypeSelect" value="[:Type:]"><br>

                    <input class="light ed" type="text"  name="TextType" value="[:TextType:]"></td>
    <td width="60"> <input class="light" type="text"  name="Sum" value="[:TotalPrice:]"><br>
                    <input class="light" type="text"  name="Hours" value="[:Hours:]"></td>
    <td width="150"><input class="light ed" type="text" style="width:99%;" name="PlaceTaken" value="[:PlaceDone:]"><br>
                    <input class="light ed" type="text" style="width:99%;" name="PlaceDone" value="[:PlaceTaken:]"></td>
    <td><input type="text" class="light ed" name="Note" style="width:99%;" value="[:Note:]"><br>
        <input type="text" class="light ed" name="BookNote" style="width:99%;" value="[:BookNote:]"></td>
    <td width="50">
        <input type="text"  class="light" name="TotalPrice" value="[:TotalPrice:]"><br>
        <input type="text"  class="light ed" name="PriceNote" value="[:PriceNote:]">
    </td>
   <!--  <td width="25">
    <input type="checkbox" title="Sl�p! Var redz�t tikai administr�tors." name="Hidden" value="0" style="width:15px; position:relative; top:2px; " class="">
    <input type="submit" onclick="addAutocomplete(); $('#newTplBtn, #editTplBtn').css('color','');" style="width:70px" value="Saglab�t"> -->
    <!-- <br> <input type="checkbox" title="Var labot tikai administr�tors." class="" name="AdminEdit" value="0" style="width:15px; position:relative; top:2px; ">
     <input type="button" onclick="this.form.reset(); $('#newTplBtn, #editTplBtn').css('color',''); $('input:not(:button,:submit)',this.form).addClass('light'); $('#DataList .onedit').removeClass('onedit'); $('#FilterForm').removeClass('hideFilter'); $('input.active').removeClass('edit'); $('input.disabl').removeClass('edit'); editbox(0,this);  $('tr.Selected').removeClass('Selected').addClass('selected'); " value="&mdash;" style="width:70px;"> -->
    <!-- <span style="position:relative; top:0px; left:0px;">
        <a style="position:absolute; right:-12px; top:0px;" onclick="reversEdit()" href="javascript:;">A</a>
    </span>
    </td> -->
 </tr>
</tbody></table><input type="text" id="EndDate" name="RemindDateEnd" value="[:RemindDateEnd:]" class="light" style="width:115px"><input ID="AllDays" type="checkbox" name="AllDays" value="1"><label for="AllDays">Dienas uzdevums</label>
</form><br>
<a id="changeBtn"  class="changes extra " href="javascript:showChanges([:ID:]);"></a>
<div onmousemove=" $('div.Main div input').click(function() {$('div.Main').remove();});" id="Changes[:ID:]" class="changes" style="position: inherit;"></div>

<a target="_blank" href="/faili/[:Order:]" title="Failu p�rl�ks" class="extra image" id="Bildes"></a>
<a href="javascript:RowEdit([:ID:]);" title="Labot blakus log�" class="extra RowEdit" id="Labot"></a>
<div class="editbox" style="display: none;">
<textarea ondblclick="CloseEditBox();" id="edittext" rows="10" cols="30" style="  z-index: 1005; width: 934px; height: 62px;"></textarea>
</div>
