<tr id="Orders[:ID:]" class="Data [:Status:]">
    <td>[:ID:] [:Restricted:]</td>
    <td>[:Code:]</td>
    <td style="background:[:Color:]"></td>
    <td><span id="Desc[:ID:]">[:Description:]</span> <span class="light">([:User:])</span></td>
    <td class="[:NoAdmin:]">
        <a class="extra edit" href="javascript:EditOrder([:ID:])"></a>
        <a class="extra restore [:Deleted:]" id="restore[:ID:]" href="javascript:Restore([:ID:],'Orders')"></a>
        <a class="extra delete" href="javascript:Delete([:ID:],'Orders')"></a>
        <a class="extra changes [:Changes:]" href="javascript:showChanges([:ID:],'Orders');  " id="changeBtn[:ID:]"></a>
         <div class="changes" id="Changes[:ID:]"></div>
    </td>
 </tr>
