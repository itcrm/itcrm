
<h2 align="center">[[:Title:]]</h2>

<form id="AddFiltersForm" action="javascript:Save('Filters')" method="POST">
<input type="text" name="ID" class="hide" />
<table class="add" cellpadding="0" cellspacing="0" width="75%" border="1" style="margin-left:3%;">
<tr height="30">
    <td width="60"><input type="text" class="light" name="Name" value="[[:Name:]]"/><br/><input type="button" value="&mdash;" onclick="this.form.reset(); $('#AddFiltersForm input:not(:submit)').addClass('light');" /></td>
    <td width="110"><select name="Date">
                     <option value="99">[[:AllPeriod:]]</option>
      <option value="5">[[:Today:]]</option>
      <option value="6">[[:Yesterday:]]</option>
      <option value="7">[[:Week:]]</option>
      <option value="1" >[[:LastMonth:]]</option>
      <option value="8">[[:Last:]]</option>
      <option value="9">[[:Tomorrow:]]</option>
      <option value="10">[[:FutureWeek:]]</option>
      <option value="11">[[:FutureMonth:]]</option>
      <option value="12">[[:Future:]]</option>
                    </select><br/>
                   <select name="DateType">
                      <option value="0">------</option>
                      <option value="1">[[:AddDate:]]</option>
                      <option value="2">[[:DocDate:]]</option>

                   </select>
                    </td>
    <td width="90"><input type="hidden" name="IDPerson" />
                   <input type="hidden" name="IDOperator" />
                   <input type="text" class="light" name="PersonSelect" value="[[:Operator:]]" /><br/>
                   <input type="text" class="light" name="OperatorSelect" value="[[:User:]]" /></td>
    <td width="70"><input type="hidden" name="IDOrder" />
                   <input type="text" class="light" name="OrderSelect" value="[[:Order:]]" /><br/>
                   <input type="text" class="light" name="TextOrder" value="[[:OrderText:]]"/></td>
    <td width="60"><input type="hidden" name="IDType" />
                   <input type="text" class="light" name="TypeSelect" value="[[:Type:]]" /><br/>
                   <input type="text"  class="light" name="TextType" value="[[:TypeText:]]"/></td>
    <td width="60"><input type="text" class="light" name="Sum" value="[[:Sum:]]"/><br/>
                   <input type="text" class="light" name="Hours" value="[[:Hours:]]"/></td>
    <td width="150"><input type="text" class="light" name="PlaceTaken" value="[[:PlaceTaken:]]" /><br/>
                    <input type="text" class="light" name="PlaceDone"  value="[[:PlaceDone:]]" /></td>
    <td width="150"><input style="background:#BBB;" type="text" name="Search" value="[:Search:]"><br/>
                    <input type="text" class="light" name="Note" value="[[:Note:]]" /><br/>
                    <input type="text" class="light" name="BookNote"  value="[[:BookNote:]]" /></td>
    <td width="100"><input type="submit" value="[[:Save:]]"  /></td>
 </tr>
</table>
</form>
<!-- onclick="addAutocomplete()" -->
<div class="clear"><!--  --></div>
<table id="FiltersList" cellpadding="0" cellspacing="0" width="75%" border="1" style="margin-left:3%; float:left;">
 <tr class="title">
    <td width="60">[[:Name:]]</td>
    <td width="110">[[:Date:]]</td>
    <td width="90">[[:Operator:]]</td>
    <td width="70">[[:Order:]]</td>
    <td width="60">[[:Type:]]</td>
    <td width="60">[[:Value:]]</td>
    <td width="150">[[:Place:]]</td>
    <td width="150">[[:Note:]]</td>
     <td width="150">[[:Search:]]</td>
    <td width="100">[[:Actions:]]</td>
 </tr>
 [:Content:]
</table>

<form id="AddFilterUsersForm" action="javascript:FilterRightsSave()" method="POST">
<input type="hidden" id="IDFilter" name="IDFilter" />
<table id="UsersList" cellpadding="2" cellspacing="0" width="20%" border="1" style="margin-left:1%; float:left;">
 <tr>
   <td colspan="2" align="center" id="CurrentFilter">
    &mdash;
   </td>
   <tr>
   <td colspan="2" align="center">
    <input type="submit" value="[[:Save:]]" />
   </td>
 </tr>
 </tr>
 <tr>
   <td width="50%" valign="top">[:Users1:]</td>
   <td valign="top">[:Users2:]</td>
 </tr>

</table>
</form>

<br/><br/>
 <script type="text/javascript">
var orders = [ [:OrdersList:] ];
var types = [ [:TypesList:] ];
var users = [ [:UsersList:] ];

var usersAllowed = [ [:AllowedUsersList:] ];
var ordersAllowed = [ [:AllowedOrdersList:] ];
var typesAllowed = [ [:AllowedTypesList:] ];

 $(document).ready(function() {
     function split( val ) {
            return val.split( /,\s*/ );
        }

    /*  $(".add [name=OrderSelect]").autocomplete(
        orders,
        {   minChars: 0,
            max: 100,
            formatItem: function(row, i, max) {
                return row.name;
            },
            formatResult: function(row) {
                return row.name;
            }
        }
      );

$(".add [name=OrderSelect]").autocomplete({
    source: "/lv/Josn/Orders",
    minLength: 1,
});

       */

$(".add [name=OrderSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterOrders",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );

                    var ID = $(".add [name=IDOrder]");
                    var termsID = split( ID.val());
                    termsID.pop();
                    termsID.push( ui.item.ID );
                    termsID.push( "" );
                    ID.val(termsID.join( ", " ));

                    return false;
            },
    minLength: 2,
});

     /* $(".add [name=PersonSelect]").autocomplete(
        users,
        {   minChars: 0,
            max: 100,
            formatItem: function(row, i, max) {
                return row.name;
            },
            formatResult: function(row) {
                return row.name;
            }
        }
      );

          $(".add [name=PersonSelect]").autocomplete({
    source: "/lv/Josn/Persons",
    minLength: 2,
});*/

$(".add [name=PersonSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/Persons",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );

                    var ID = $(".add [name=IDPerson]");
                    var termsID = split( ID.val());
                    termsID.pop();
                    termsID.push( ui.item.ID );
                    termsID.push( "" );
                    ID.val(termsID.join( ", " ));

                    return false;
            },
    minLength: 2,
});

      /* $(".add [name=OperatorSelect]").autocomplete(
        users,
        {   minChars: 0,
            max: 100,
            formatItem: function(row, i, max) {
                return row.name;
            },
            formatResult: function(row) {
                return row.name;
            }
        }
      );

    $(".add [name=OperatorSelect]").autocomplete({
    source: "/lv/Josn/Persons",
    minLength: 2,
})*/

$(".add [name=OperatorSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/Persons",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );

                    var ID = $(".add [name=IDOperator]");
                    var termsID = split( ID.val());
                    termsID.pop();
                    termsID.push( ui.item.ID );
                    termsID.push( "" );
                    ID.val(termsID.join( ", " ));

                    return false;
            },
    minLength: 2,
});

      /* $(".add [name=TypeSelect]").autocomplete(
        types,
        {   minChars: 0,
            max: 100,
            formatItem: function(row, i, max) {
                return row.name;
            },
            formatResult: function(row) {
                return row.name;
            }
        }
      );

     $(".add [name=TypeSelect]").autocomplete({
    source: "/lv/Josn/Types",
    minLength: 1,
});*/

$(".add [name=TypeSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/Types",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );

                    var ID = $(".add [name=IDType]");
                    var termsID = split( ID.val());
                    termsID.pop();
                    termsID.push( ui.item.ID );
                    termsID.push( "" );
                    ID.val(termsID.join( ", " ));

                    return false;
            },
    minLength: 2,
});

 Names =  { Name:'[[:Name:]]', Date:'[[:Date:]]', PersonSelect:'[[:Operator:]]', OperatorSelect:'[[:User:]]',
            OrderSelect:'[[:Order:]]', TextOrder:'[[:OrderText:]]',
            TypeSelect:'[[:Type:]]', TextType:'[[:TypeText:]]',
            Sum:'[[:Sum:]]', Hours:'[[:Hours:]]', PlaceTaken:'[[:PlaceTaken:]]', PlaceDone:'[[:PlaceDone:]]',
            Note:'[[:Note:]]', BookNote:'[[:BookNote:]]'    } ;

      $('.add input, .add select').bind('focus',function() {
          el = $(this);
          el.addClass('active');
            if(this.value==Names[this.name]) {
                this.value='';
                el.removeClass('light');
            }
      }).bind('blur',function() {
          el = $(this);
          el.removeClass('active');
            if(this.value=='') {
                this.value=Names[this.name];
                el.addClass('light');
            }
      });

        filterAutocomplete(1);
    });

 </script>
