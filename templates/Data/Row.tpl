<tr id="Data[:ID:]" class="Data [:Status:][:AdminEditClass:] [:HiddenClass:] [:Odd:] [:select:]" ondblclick="[:dblClick:]">
    <td>
    <input type="checkbox" [:checked:] name="Row" value="[:ID:]" onClick="[:Function:]([:ID:])"/>
    <span style="display:none" ID="AdminEdit[:ID:]">[:AdminEdit:]</span></td>
    <td><span style="font-size: 10px;" id="Doc[:ID:]">[:IDDoc:] </span><span class="light" id="ID[:ID:]"> [:ID:]</span>
    <br/>
    </td>
    <td> [:DateShow:] <span id="Date[:ID:]" class="hide">[:Date:]</span>
    <br/>
    <span class="light">[:AddDate:]</span>
    <br/>
    <span class="light [:reminderColor:]" id="RemindDate[:ID:]">[:RemindDate:]</span></td>
    <td><span id="Person[:ID:]">[:Person:]</span>
    <br/>
    <span class="light">[:User:]</span>
    <br/>
    <span class="light [:reminderColor:]" id="RemindTo[:ID:]">[:RemindTo:]</span></td>
    <td><span id="Order[:ID:]">[:Order:]</span>
    <br/>
    <span id="TextOrder[:ID:]" class="light">[:TextOrder:]</span></td>
    <td><span id="Type[:ID:]">[:Type:]</span>
    <br/>
    <span id="TextType[:ID:]" class="light"  style="font-size:9px;">[:TextType:]</span></td>
    <td><span id="Sum[:ID:]">[:Sum:]</span>
    <br/>
    <span id="Hours[:ID:]" class="light">[:Hours:]</span></td>
    <td><span id="PlaceTaken[:ID:]">[:PlaceTaken:]</span>
    <br/>
    <span class="light" id="PlaceDone[:ID:]">[:PlaceDone:]</span></td>
    <td><span id="Note[:ID:]">[:Note:]</span>
    <br/>
    <span class="light" id="BookNote[:ID:]">[:BookNote:]</span></td>
    <td style="font-size:10px;"><span id="TotalPrice[:ID:]">[:TotalPrice:]</span>
    <br/>
    <span id="PriceNote[:ID:]" class="light">[:PriceNote:]</span></td>
    <td class="action"> <div ID="exp_but[:ID:]" onClick="extend([:ID:])" class="exp_but_up exp_but "> </div><span class="hide" id="Hidden[:ID:]">[:Hidden:]</span><span class="[:CanCopy:]"> <a class="extra template" href="javascript:EditData([:ID:],1);"></a> </span><span class="[:CanEdit:]"> <a class="extra edit" href="javascript:EditData([:ID:]);"></a> </span><span class="[:NoAdmin:]"> <a id="restore[:ID:]" class="extra restore [:Deleted:]" href="javascript:Restore([:ID:],'Data');"></a> <a class="extra delete" href="javascript:Delete([:ID:],'Data');"></a> </span><a class="extra changes [:Changes:]" href="/lv/Changes/[:ID:]"  target="_blank" id="changeBtn[:ID:]"></a><!-- href="javascript:showChanges([:ID:]);-->
    <div class="exp_hide" id="slider[:ID:]">
        <span class=""> <a class="extra r_bilde" title="Rindas bilde"  onClick=" $('.r_bilde_link').attr('src', '[:link:]'); $('.r_bilde_link').attr('id', '[:ID:]');  $( '#rindas_bilde' ).dialog({resizable: false, width: 831}); $('.ui-dialog').css({'position': 'absolute', 'top':'15%', 'left':'15%'}); $( '#rindas_bilde' ).dialog({   beforeClose: function(event, ui) {$( 'div.r_bilde' ).photoTagger('destroy');}}); $( 'div.r_bilde' ).photoTagger({loadURL: './Data/photoTagger',saveURL: './Data/SavephotoTagger',deleteURL: './Data/DeletephotoTagger'});" href='#'></a> </span>

        <span class="[:add_files:]"> <a class="extra file_upload" title="Pievienot failus" onClick= "OpenForm( 'AddFiles', 'DialogForm', 'scrollDiv', 'Pievienot failus', '300', [:ID:] );"> </a> </span>
        <span class="[:add_r_bilde:]"> <a class="extra r_bile_upload" title="Pievienot rindas bildi" href="#" onClick="OpenForm( 'AddPicture', 'DialogForm', 'scrollDiv', 'Pievienot rindas bildi', '300', [:ID:] )"> </a> </span>
        <span class=""> <a class="extra image"  title="Failu pārlūks"href="/faili/[:Order:]" target="_blank"> </a> </span>

    </div><div class="changes" id="Changes[:ID:]"></div></td>
</tr>