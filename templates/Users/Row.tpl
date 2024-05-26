<tr id="Users[:ID:]" class="Data [:RowClass:]">
    <td>[:ID:]</td>
    <td>[:Login:]</td>
    <td style="background:[:Color:]"></td>
    <td>[:Name:]</td>
    <td>[:Phone:]</td>
    <td class="[:RowClass:][:NoAdmin:]">[:StatusText:]<span id="Status[:ID:]" class="hide">[:Status:]</span></td>
    <td class="[:RowClass:][:add_order:]" >[:add_order:]</td>
    <td class="[:RowClass:][:add_r_bilde:]" >[:add_r_bilde:]</td>
    <td class="[:RowClass:][:add_files:]" >[:add_files:]</td>
    <td class="[:RowClass:][:OneDay:]" >[:OneDay:]</td>
    <td class="[:RowClass:][:noliktava:]" >[:noliktava:]</td>
    <td class="[:RowClass:][:MultiChange:]" >[:MultiChange:]</td>
    <td class="[:RowClass:][:DelFile:]" >[:DelFile:]</td>
    <td class="[:NoAdmin:]">
        <a class="extra edit" href="javascript:EditUser([:ID:])"></a>
        <a class="extra restore [:Deleted:]" id="restore[:ID:]" href="javascript:Restore([:ID:],'Users')"></a>
        <a class="extra delete" href="javascript:Delete([:ID:],'Users')"></a>
    </td>
 </tr>
