<div id="scrollDiv">
<table id="DataList" cellpadding="0" cellspacing="0" border="1" align="center">
<col width="21">
<col width="61">
<col width="111">
<col width="51">
<col width="71">
<col width="71">
<col width="61">
<col width="151">
<col>
<col width="51">

<tr>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    <td>
    </td>
    </td>
    <td>
    </td>
     <td width="40px">
    </td>
</tr>
 [:Content:]

</table>

</div>

     [:Pages:]

    <style>
    .ui-corner-all{
        border-radius: 0px 0px 0px 0px;
    }

    .ui-autocomplete {
        max-height:  300px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 20px;
    }

    * html .ui-autocomplete {
        height: 300px;
    }
    </style>

 <script type="text/javascript">
var orders = [ [:OrdersList:] ];
var types = [ [:TypesList:] ];
var users = [ [:UsersList:] ];

var usersAllowed = [ [:AllowedUsersList:] ];
var ordersAllowed = [ [:AllowedOrdersList:] ];
var typesAllowed = [ [:AllowedTypesList:] ];

 $(document).ready(function() {

$("form.SelectChange input:checkbox").change(function(){
 if ($('form.SelectChange input:checkbox').is(':checked')) {
     $('form.SelectChange input:checkbox').attr('disabled', true);
     $(this).attr('disabled', false);
    } else{
        $('form.SelectChange input:checkbox').attr('disabled', false);
    }
}
);

 $('#SupplierForm input[name=Color]').colorPicker();
      $('#SupplierForm input[name=Name]').bind('focus',function() {
            if(this.value=='[[:Name:]]') {
                this.value='';
                $(this).removeClass('light');
            }
       }).bind('blur',function() {
            if(this.value=='') {
                this.value='[[:Name:]]';
                $(this).addClass('light');
            }
       });

      $('#SupplierForm input[name=Description]').bind('focus',function() {
            if(this.value=='[[:MoreData:]]') {
                this.value='';
                $(this).removeClass('light');
            }
       }).bind('blur',function() {
            if(this.value=='') {
                this.value='[[:MoreData:]]';
                $(this).addClass('light');
            }
       });

// Add Autocomplite
$(".add [name=PersonSelect]").autocomplete({
    source: "/lv/Josn/Persons",
    minLength: 2,
});

$(".add [name=TypeSelect]").autocomplete({
    source: "/lv/Josn/Types",
    select: function( event, ui){
        AddNoliktavaForm(ui.item.ID);
    },
    minLength: 1,
});

$(".add [name=OrderSelect]").autocomplete({
    source: "/lv/Josn/Orders",
    minLength: 1,
});

// Filter Autocomplite
  $("#FilterForm [name=OrderFilterSelect]").bind( "keydown", function( event ) {
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
                    return false;
            },
    minLength: 2,
});

 $("#FilterForm [name=PersonFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterPersons",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 2,
});

$("#FilterForm [name=TypeFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterTypes",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 1,
});

$("#FilterForm [name=OperatorFilterSelect]").bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            }).autocomplete({
    source: "/lv/Josn/FilterPersons",
    focus: function() {
                    return false;},
    select: function( event, ui ) {
                    var terms = split( this.value );
                    terms.pop();
                    terms.push( ui.item.value );
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
            },
    minLength: 2,
});

 Names =  { IDDoc:'[[:IDDoc:]]', Date:'[[:Date:]]', PersonSelect:'[[:Operator:]]',
            OrderSelect:'[[:Order:]]', TextOrder:'[[:OrderText:]]',
            TypeSelect:'[[:Type:]]', TextType:'[[:TypeText:]]',
            Sum:'[[:Sum:]]', Hours:'[[:Hours:]]', PlaceTaken:'[[:PlaceTaken:]]', PlaceDone:'[[:PlaceDone:]]',
            Note:'[[:Notes:]]', BookNote:'[[:BookNotes:]]', TotalPrice:'[[:TotalPrice:]]',  PriceNote:'[[:PriceNote:]]',RemindDate:'[[:Reminder:]]' } ;

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
          if(el.hasClass('edit')) {
             /*el.css('width','');

             el.removeClass('big');
            el.removeClass('edit')
            editbox(0,this)*/

          }
      }).bind('dblclick',function() {
            if(el.hasClass('edit')) {
             editbox(0,this)
            el.removeClass('edit')
            } else {
             el.addClass('edit')
            editbox(1,this)
            }
       });

      $('#DataList input, #DataList select').bind('focus',function() {
          $(this).addClass('active');
      }).bind('blur',function() {
          $(this).removeClass('active');
      });

        filterAutocomplete(1);

            // Hook up the enable create links.
            $( "a.enable-create" ).click(
                function( event ){
                    // Prevent relocation.
                    event.preventDefault();

                    // Get the container and enable the tag
                    // creation on it.
                    $( this ).prevAll( "div.r_bilde" )
                        .photoTagger( "enableTagCreation" )
                    ;
                }
            );
    });

function split( val ) {
            return val.split( /,\s*/ );
        }

 function MultiEdit(){
      $('#MultiEdit').toggle("blind");
  }
 </script>
 <style>
 div.minimize{margin-bottom: 10px;}
 </style>
