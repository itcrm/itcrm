<div style="float:right; padding-right:10px; font-size:14px;">
  <form name="SearchForm" action="[:URL:]/Data/Search" method="POST" style="display:block; float:left; display:[:NoAdmin:]">
    <input type="text" name="Search" value="[:SearchStr:]" style="width:150px;"/>&nbsp;
    <select name="Period">
      <option value="99" [:SearchP99:]>[[:AllPeriod:]]</option>
      <option value="5" [:SearchP5:]>[[:Today:]]</option>
      <!-- <option value="6" [:SearchP6:]>[[:Yesterday:]]</option> -->
      <option value="7" [:SearchP7:]>[[:Week:]]</option>
      <option value="1" [:SearchP1:]>[[:LastMonth:]]</option>
      <!-- <option value="2" [:SearchP2:]>[[:ThreeMonth:]]</option>
      <option value="3" [:SearchP3:]>[[:HalfYear:]]</option>-->
      <option value="4" [:SearchP4:]>[[:LastYear:]]</option>
      <!-- <option value="8" [:SearchP8:]>[[:Last:]]</option>
      <option value="9" [:SearchP9:]>[[:Tomorrow:]]</option>
      <option value="10" [:SearchP10:]>[[:FutureWeek:]]</option>
      <option value="11" [:SearchP11:]>[[:FutureMonth:]]</option>
      <option value="12" [:SearchP12:]>[[:Future:]]</option> -->

    </select>
    <select name="Sort">
        <option value="2" [:Sort2:]>[[:SortByID:]]</option>
        <option value="1" [:Sort1:]>[[:SortByDate:]]</option>
    </select>
    </select>
    <input type="checkbox" name="FindDeleted" [:FindDeleted:] value="1" style="width:15px; position:relative; top:2px; "/>
    <input type="submit" value="[[:Search:]]">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </form>

  [:Login:]: <a class="menu Logout" href="[:URL:]/Logout">[[:Logout:]]</a>
</div>

<!-- <ul class="DropMenu">
        <li><a class="ui-button-text" href="#">Ievade</a></li>
        <li><a class="ui-button-text" href="#">Noliktava</a></li>
        <li>
            <a class="ui-button-text" href="#">Opcijas</a>
             <ul class="subnav">
                <li ><a class="ui-button-text" href="#">Sub Nav Link</a></li>
                <li><a class="ui-button-text"  href="#">Sub Nav Link</a></li>
            </ul>
        </li>

    </ul> -->

<a class="menu" href="[:URL:]/Data">[[:Data:]]</a>
<a class="menu" href="[:URL:]/Users">[[:Users:]]</a>
<a class="menu" href="[:URL:]/Types">[[:Types:]]</a>
<a class="menu" href="[:URL:]/Orders">[[:Orders:]]</a>
<a class="menu" style="display:[:NoAdmin:]" href="[:URL:]/Filters">[[:Filters:]]</a>
<a class="menu" href="[:URL:]/Rights">[[:Rights:]]</a>
<a class="menu" href="[:URL:]/Task">[[:Tasks:]]</a>
<a class="menu" href="[:URL:]/Warehous">[[:Warehous:]]</a>
