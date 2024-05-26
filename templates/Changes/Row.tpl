 <tr id="Data[:ID:]" class="Data [:Status:] [:AdminEditClass:] [:HiddenClass:] [:Odd:] [:select:]" ondblclick="[:dblClick:]">
    <td>
       <input type="checkbox" [:checked:] name="Row" value="[:ID:]" onClick="[:Function:]([:ID:])"/>
       <span style="display:none" ID="AdminEdit[:ID:]">[:AdminEdit:]</span>
    </td>
    <td>
       <span class="light" id="Doc[:ID:]">[:IDDoc:]</span>
       <span id="ID[:ID:]">[:ID_Row:]</span><br/>
    </td>
    <td>
        [:DateShow:]
        <span id="Date[:ID:]" class="hide">[:Date:]</span><br/>
        <span class="light">[:AddDate:]</span><br/>
        <span class="light [:reminderColor:]" id="RemindDate[:ID:]">[:RemindDate:]</span>
    </td>
    <td>
        <span id="Person[:ID:]">[:Person:]</span><br/>
        <span class="light">[:User:]</span><br/>
        <span class="light [:reminderColor:]" id="RemindTo[:ID:]">[:RemindTo:]</span>
    </td>
    <td>
        <span id="Order[:ID:]">[:Order:]</span><br/>
        <span id="TextOrder[:ID:]" class="light">[:TextOrder:]</span>
    </td>
    <td>
        <span id="Type[:ID:]">[:Type:]</span><br/>
        <span id="TextType[:ID:]" class="light"  style="font-size:9px;">[:TextType:]</span>
    </td>
    <td>
        <span id="Sum[:ID:]">[:Sum:]</span><br/>
        <span id="Hours[:ID:]" class="light">[:Hours:]</span>
    </td>
    <td>
        <span id="PlaceTaken[:ID:]">[:PlaceTaken:]</span><br/>
        <span class="light" id="PlaceDone[:ID:]">[:PlaceDone:]</span>
    </td>
    <td>
        <span id="Note[:ID:]">[:Note:]</span><br/>
        <span class="light" id="BookNote[:ID:]">[:BookNote:]</span>
    </td>
    <td style="font-size:10px;">
        <span id="TotalPrice[:ID:]">[:TotalPrice:]</span><br/>
        <span id="PriceNote[:ID:]" class="light">[:PriceNote:]</span>
    </td>
    <td>
        <span><a class="extra changes [:Changes:]" href="javascript:showChanges([:ID:]);"  id="changeBtn[:ID:]"></a></span>

         <div class="changes" id="Changes[:ID:]"></div>
    </td>

 </tr>
