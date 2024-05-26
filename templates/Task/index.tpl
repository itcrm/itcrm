<div class="kalendars">
    <link rel="stylesheet" type="text/css" href="../css/task.css" />
    <link rel='stylesheet' type='text/css' href='../css/fullcalendar.css' />
    <script type='text/javascript' src='../js/fullcalendar.js'></script>
    <script type='text/javascript' src='../js/jquery.ui.datepicker-lv.js'></script>
    <script type='text/javascript' src='../js/date.format.js'></script>

    <script type="text/javascript">

        var orders = [ [:OrdersList:] ];
        var types = [ [:TypesList:] ];
        var users = [ [:UsersList:] ];

        var usersAllowed = [ [:AllowedUsersList:] ];
        var ordersAllowed = [ [:AllowedOrdersList:] ];
        var typesAllowed = [ [:AllowedTypesList:] ];

        var TaskUsers =  [:TaskUsers:] ;
        var User = [:SelectUser:];
        $(document).ready(function() {
        $('#calendar').fullCalendar({
        user: [:SelectUser:],
        userlists: TaskUsers,
        theme: true,
        header: {
        left: 'prev,next today print',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
        },
        editable: true,
        selectable: false,
        selectHelper: true,
        events: 'Task/Josn',

        select: function(start, end) {
        var pasutijums = "aa";
        var paspiez = "aaa";
        var title = prompt('Nosaukums:');

        if (title) {
        $('#calendar').fullCalendar('renderEvent',
        {
        title: title,
        start: start,
        end: end,
        paspiez: paspiez,
        pasutijums: pasutijums
        //allDay: allDay
        },
        false // make the event "stick"
        );
        }
        $('#calendar').fullCalendar('unselect');
        },

        eventResizeStop: function(event, delta) {
        success = function(answ) {
        Remaind = $.fullCalendar.formatDate( event.start, 'yyyy-MM-dd HH:mm:ss' )
        RemaindEnd = $.fullCalendar.formatDate( event.end, 'yyyy-MM-dd HH:mm:ss' )
        Data = '&RemindDate=' + Remaind + '&RemindDateEnd=' + RemaindEnd + answ;
        $.post(URL+'/Task/Save',Data);
        }
        id = '&id='+event.id;
        $.post(URL+'/Task/Move',id,success);
        },

        eventDrop: function(event, delta) {
        $("#line4").removeAttr("style")
        $("#drag").dialog({
        resizable: false,
        modal: true,
        buttons: {
        Saglabāt: function() {
        success = function(answ) {
        Remaind = $.fullCalendar.formatDate( event.start, 'yyyy-MM-dd HH:mm:ss' );
        RemaindEnd = $.fullCalendar.formatDate( event.end, 'yyyy-MM-dd HH:mm:ss' );

        Data = '&RemindDate=' + Remaind + '&RemindDateEnd=' + RemaindEnd + '&AllDays=' + event.allDay + answ;
        $.post(URL+'/Task/Save',Data);
        }
        id = '&id='+event.id;
        $.post(URL+'/Task/Move',id,success);
        $(this).dialog('close');
        },
        Labot: function() {
        labot(event);
        $(this).dialog('close');
        }
        }
        });
        $("#drag").html('Uzdevums: "' + event.title + '" tika pārvietots par ' + delta + ' dienu' + ' ( Vai vēlaies labot datus?)')
        $(".ui-dialog").css({'position': 'absolute', 'top':'40%', 'left':'40%', 'width':'300px'});
        },

        eventClick: function (event) {
        var scr = "Task/Josn?Type=Data&ID=" + event.id;

        var data = 'Type=Data&ID=' + event.id;
        success = function(answ){
        Loading(0, 0);

        $('#task').html(answ);

        };

        Loading(0, 1);
        $.get(URL + '/Task/Josn', data, success);

        Dialog(event);

        },

        loading: function(bool) {
        if (bool) $('#loading').show();
        else $('#loading').hide();
        }

        });

        $('.fc-header').click(function() {
        var view = $('#calendar').fullCalendar('getView');

        var skats = view.name;
        var gads = $.fullCalendar.formatDate(view.visStart, 'yyyy');
        var menesis = $.fullCalendar.formatDate(view.visStart, 'M');
        var dina = $.fullCalendar.formatDate(view.visStart, 'd');

        var data = '&skats='+skats+'&gads='+gads+'&menesis='+menesis+'&dina='+dina;
        $.post(URL+'/Task/SaveTaskPlace',data);
        });

        var cech
        cech = '[:TaskSkats:]';
        if (cech == ''){cech = '';}else{
        $('#calendar').fullCalendar( 'gotoDate', '[:TaskGads:]','[:TaskMenesis:]','[:TaskDina:]');
        $('#calendar').fullCalendar( 'changeView', '[:TaskSkats:]' );
        }
        });

        function CloseEditBox() {
        var text = $("#edittext").val();
        var strSingleLineText = text.replace(new RegExp( "\n", "g" )," ` ");
        $("input.OnEditBox").val(strSingleLineText);
        $("input.light").removeAttr("disabled");
        $("input.OnEditBox").removeClass("OnEditBox");
        $(".editbox").hide();
        }

        function Dialog(event){
        $("#task").dialog({
        resizable: true,
        modal: true,
        buttons: {
        Aizvērt: function() {
        $(".ac_results").remove()
        $(this).dialog('close');
        }[:save:]
        }
        });
        $(".ui-dialog").css({'position': 'absolute', 'top':'40%', 'left':'17%', 'width':'1030px'});

        }

        function checkhtime(o,n,min,max) {
        if ( o.val() > max || o.val() < min ) {
        o.addClass('ui-state-error');
        updateTips("Length of " + n + " must be between "+min+" and "+max+".");
        return false;
        } else {
        return true;
        }

        }

        function updateTips(t) {
        tips
        .text(t)
        .addClass('ui-state-highlight');
        setTimeout(function() {
        tips.removeClass('ui-state-highlight', 1500);
        }, 500);
        }

        function izmainas() {
        $("#dialog").dialog({
        resizable: false,
        modal: true,
        buttons: {
        Aizvērt: function() {
        $(this).dialog('close');
        }
        }
        });
        $(".ui-dialog").css({'position': 'absolute', 'top':'40%', 'left':'40%', 'width':'300px'});
        $("#dialog").removeAttr("style");
        };

        function data(id,SelectUser){
        data = SelectUser

        //success = function(answ) {
        //     Loading(1,0);

        window.location.replace("/lv/Data/Reminder/"+data);
        EditData(76992);
        //  }
        //Loading(0,1);

        }

        function SaveAutocomplite(){
        //$('#AddTaskForm input[name=IDPerson]').val(User);

        var ID = $('#AddTaskForm input[name=PersonSelect]').val();
        var db = users;
        $.each(db, function(i, object) {
        if (object.name == ID){$('#AddTaskForm input[name=IDPerson]').val(object.val);}
        });

        var ID = $('#AddTaskForm #RemindPerson').val();
        var db = users;
        $.each(db, function(i, object) {
        if (object.name == ID){$('#AddTaskForm input[name=RemindTo]').val(object.val);}
        });

        var ID = $('#AddTaskForm input[name=OrderSelect]').val();
        var Text = $('#AddTaskForm input[name=IDOrder]');
        var db = orders;
        $.each(db, function(i, object) {
        if (object.name == ID){Text.val(object.val);}
        });

        var ID = $('#AddTaskForm input[name=TypeSelect]').val();
        var Text =$('#AddTaskForm input[name=IDType]');
        var db = types;
        $.each(db, function(i, object) {
        if (object.name == ID){Text.val(object.val);}
        });

        }

        function MakeDate(date){
        if (date == ''){return '';}
        date = date.split(' ')
        var dat = date[0].split('.');
        var time = date[1].split(':');
        return datums =  new Date("20"+dat[0], dat[1]-1, dat[2], time[0], time[1]);
        }

        function daydiff(first, second) {
        return (second-first)/(1000*60*60)
        }

        function Saglabat(event){
        CloseEditBox();
        //addAutocomplete();

        //Nepabeigts funkcija pareiza, bet laikam atšķiras formati vai nav pierakstits beigu laiks. funkcija update nav zinama kam radita :D

        //update(Formatets,Note,pasutijums);
       // var allDay = $('').val();
        var allDay = 0;
       if ($("form#AddTaskForm input#AllDays").attr('checked')){
        allDay = 1;
    }
        var date = MakeDate($('#AddTaskForm input[name=RemindDate]').val());
        var edatums = MakeDate($('#AddTaskForm input[name=RemindDateEnd]').val());
        //var dat = date[0].split('.');
        //var time = date[1].split(':');

       /* if (allDay == 0){
        var dif =(daydiff(date,edatums));

        if(dif > 10 ){
            event.allDay = true;
            }
            else{
                event.allDay = false;
                }
        }
        else{
        event.allDay = true;
        }*/
       if (allDay == 0){
         event.allDay = false;
       }else{
          event.allDay = true;
       }

        //var datums =  new Date("20"+dat[0], dat[1]-1, dat[2], time[0], time[1]);
        event.start = date;
        event.end = edatums;
        event.title= $('#AddTaskForm input[name=BookNote]').val();
        event.pasutijums = $('#AddTaskForm input[name=OrderSelect]').val();
        event.paspiez = $('#AddTaskForm input[name=Note]').val();
        $('#calendar').fullCalendar('updateEvent', event);

        var ID = $('#AddTaskForm input[name=RemindTo]').val();
        var db = users;
        $.each(db, function(i, object) {
        if (object.name == ID){$('#AddTaskForm input[name=RemindTo]').val(object.val);}
        });

        SaveAutocomplite();

        Save('Task');
        $(".ac_results").remove()
        }

    </script>

    <style type='text/css'>
        .calendar {
            background: none repeat scroll 0 0 #CCCCCC;
            border: 1px solid #CCCCCC;
            float: left;
            left: 17%;
            padding: 2px;
            position: fixed;
            top: 18%;
            width: 220px;
            z-index: 1004;
        }

        #loading {
            position: absolute;
            top: 5px;
            right: 5px;
        }

        #calendar {
            width: 900px;
            margin: 0 auto;
        }

        .selected {
            background: url("../images/ui-bg_glass_85_dfeffc_1x400.png") repeat-x scroll 50% 50% #DFEFFC;
            border: 1px solid #C5DBEC;
            color: #2E6E9E;
            font-size: medium;
        }

        .type {
            width: 51px;
        }

        #datepicker {
            width: 75px;
        }

        #Pasutijums {
            width: 51px;
        }

        span.calendarContainer {
            position: static;
        }
    </style>

    </head>
    <body>
        <h2 id="nos" align="center">Uzdevumi</h2>
        <p style="display: none;" id="drag" title="Uzmanību"></p>
        </form>
</div>
</p>
<p ID="atbilde" title="Uzdevums"></p>

<p style="display: none; font-size:12px;" id="dialog" title="Labojumi"></p>

<div id='calendar'></div>

</div>

<div style ="display: none;" id="task" title="Uzdevums"  >

</div>
