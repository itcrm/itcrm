 <tr id="Filters[:ID:]" class="Data [:Status:]">
    <td>
       <span id="Name[:ID:]">[:Name:]</span>
       <span id="ID[:ID:]" class="hide">[:ID:]</span>
    </td>
    <td>
        [:DateShow:]
        <br/>
        <font style="color:#888;">[:DateTypeShow:]</font>
        <span id="Date[:ID:]" class="hide">[:Date:]</span>
        <span id="DateType[:ID:]" class="hide">[:DateType:]</span>
    </td>
    <td>
        <span id="Person[:ID:]">[:Person:]</span><br/>
        <span id="Operator[:ID:]">[:Operator:]</span>
    </td>
    [:include Shared/RowColumns.tpl:]
   <td>
        <span id="Search[:ID:]">[:Search:]</span><br/>
        <span class="light" id="Search[:ID:]">[:Search:]</span>
    </td>
    <td>
        <a class="extra edit" href="javascript:EditFilter([:ID:]);"></a>
        <a class="extra restore [:Deleted:]" href="javascript:Restore([:ID:],'Filters');"></a>
        <a class="extra delete" href="javascript:Delete([:ID:],'Filters');"></a>
    </td>
 </tr>