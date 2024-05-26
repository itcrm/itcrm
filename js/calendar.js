var C = new Object();

function Calendar(opts) {
  this.parseNum = function (num) {
    num = new Array(num.substring(0, 1), num.substring(1, 2));
    if (num[0] == 0) num = parseInt(num[1]);
    else num = parseInt(num[0]) * 10 + parseInt(num[1]);

    return num;
  };

  this.date = new Date();
  this.months = Months; //this.months.unshift('');
  this.week = Week;
  this.timeInterval = 5;
  this.selectYears = 0;
  this.target = "";
  this.el = "";
  this.name = "";

  for (k in opts) this[k] = opts[k];

  if (this.target.val() != "") {
    tmp = this.target.val().split(" ");
    tmp[0] = tmp[0].split(".");
    tmp[1] = tmp[1].split(":");

    h = this.parseNum(tmp[1][0]);
    m = this.parseNum(tmp[1][1]);
    d = this.parseNum(tmp[0][2]);
    mon = this.parseNum(tmp[0][1]);

    this.current = {
      month: mon,
      year:
        tmp[0][0].length == 2
          ? parseInt(tmp[0][0]) + 2000
          : parseInt(tmp[0][0]),
      date: d,
      hour: h, //this.date.getHours(),
      minute: m, //this.date.getMinutes() + (this.timeInterval-this.date.getMinutes()%this.timeInterval)
    };
  } else
    this.current = {
      month: this.date.getMonth() + 1,
      year: this.date.getFullYear(),
      date: this.date.getDate(),
      hour: 0, //this.date.getHours(),
      minute: 0, //this.date.getMinutes() + (this.timeInterval-this.date.getMinutes()%this.timeInterval)
    };

  this.selected = {};
  this.selected.date =
    this.current.date < 10 ? "0" + this.current.date : this.current.date;
  this.selected.month =
    this.current.month < 10 ? "0" + this.current.month : this.current.month;
  this.selected.year = this.current.year;

  this.name = this.target.attr("id");

  this.target.after(
    '<span class="calendarContainer calendar-over" id="calendar' +
      this.name +
      '"><div class="calendar"><div class="header"><input type="button" class="btnNext" onclick="C.' +
      this.name +
      '.showDays(\'next\');" value="&gt;" /><input type="button" class="btnPrev" onclick="C.' +
      this.name +
      '.showDays(\'prev\');" value="&lt;" /><div class="title"><span id="month' +
      this.name +
      '"></span>, <span id="year' +
      this.name +
      '"></span><a class="extra delete" href="javascript:dropReminder(\'' +
      this.name +
      "');\"></a></div></div></div></span>"
  );
  var weeks = "";
  for (i = 0; i < 7; i++)
    weeks += '<div class="week">' + this.week[i] + "</div>";
  var calendar = $("#calendar" + this.name + " .calendar");

  calendar
    .bind("mouseover", function () {
      $(this).parent().addClass("calendar-over");
    })
    .bind("mouseout", function () {
      $(this).parent().removeClass("calendar-over");
    });
  //  $(document).bind('click',function() { var c = $('span.calendarContainer'); if(!c.hasClass('calendar-over')) c.hide(); });

  calendar.append(weeks);
  calendar.append(
    '<div class="clr"><!--  --></div><div class="days"></div><div class="clr"><!--  --></div>'
  );
  if (this.showTime) {
    (this.h = ""), (this.m = "");
    for (i = 0; i < 24; i++)
      this.h +=
        '<option value="' +
        i +
        '" ' +
        (i == this.current.hour ? "selected" : "") +
        ">" +
        (i < 10 ? "0" + i : i) +
        "</option>";
    for (i = 0; i < 60; i += this.timeInterval)
      this.m +=
        '<option value="' +
        i +
        '" ' +
        (i == this.current.minute ? "selected" : "") +
        ">" +
        (i < 10 ? "0" + i : i) +
        "</option>";

    calendar.append(
      '<div class="Time"><input type="text""style="float:left; width:70px; height:14px; margin-top:5px;" id="RemindPerson"  /><input class="btnNext" type="button" onclick=" C.' +
        this.name +
        '.setDate();" value="Ok" /><div><select class="hours time" onclick="$(\'#calendar' +
        this.name +
        "').addClass('calendar-over');\">" +
        this.h +
        '</select> : <select class="minutes time" onclick="$(\'#calendar' +
        this.name +
        "').addClass('calendar-over');\">" +
        this.m +
        '</select></div><div><select class="hours time" onclick="$(\'#calendar' +
        this.name +
        "').addClass('calendar-over');\">" +
        this.h +
        '</select> : <select class="minutes time" onclick="$(\'#calendar' +
        this.name +
        "').addClass('calendar-over');\">" +
        this.m +
        "</select></div></div>"
    );
  }

  if (this.selectYears) {
    tmp = "";
    for (i = this.current.year; i > this.current.year - 100; i--)
      tmp += '<option value="' + i + '">' + i + "</option>";
    $("#year" + this.name).replaceWith(
      '<select id="year' +
        this.name +
        '" class="selectYear" onchange="C.' +
        this.name +
        '.showDays(0,$(this).val());">' +
        tmp +
        "</select>"
    );
  }

  this.el.attr("onclick", "").bind("click", { obj: this }, function (e) {
    e.data.obj.Show(this);
    return false;
  });

  this.Show = function (el) {
    el = $(el);
    if (el.val() != "") {
      tmp = el.val().split(" ");
      tmp[0] = tmp[0].split(".");
      tmp[1] = tmp[1].split(":");

      h = this.parseNum(tmp[1][0]);
      m = this.parseNum(tmp[1][1]);
      d = this.parseNum(tmp[0][2]);
      mon = this.parseNum(tmp[0][1]);

      this.current = {
        month: mon,
        year:
          tmp[0][0].length == 2
            ? parseInt(tmp[0][0]) + 2000
            : parseInt(tmp[0][0]),
        date: d,
        hour: h, //this.date.getHours(),
        minute: m, //this.date.getMinutes() + (this.timeInterval-this.date.getMinutes()%this.timeInterval)
      };
    }
    this.selected = {};
    this.selected.date =
      this.current.date < 10 ? "0" + this.current.date : this.current.date;
    this.selected.month =
      this.current.month < 10 ? "0" + this.current.month : this.current.month;
    this.selected.year = this.current.year;

    $("#calendar" + this.name + " select.hours").val(this.current.hour);
    $("#calendar" + this.name + " select.minutes").val(this.current.minute);

    $("#RemindPerson").val($(".add input[name=RemindTo]").val());

    this.showDays(this.current.month, this.current.year);
    $("#calendar" + this.name).css("display", "inline");
  };

  this.showDays = function (month, year) {
    if (month == "prev") {
      var month =
        this.current.month == 1 ? 12 : parseInt(this.current.month) - 1;
      var year =
        month == 12 ? parseInt(this.current.year) - 1 : this.current.year;
    } else if (month == "next") {
      var month =
        this.current.month == 12 ? 1 : parseInt(this.current.month) + 1;
      var year =
        month == 1 ? parseInt(this.current.year) + 1 : this.current.year;
    } else {
      month = this.current.month;
    }

    $("#month" + this.name).html(this.months[month]);
    if (this.selectYears) {
      maxYear = $("#year" + this.name + " option:first").attr("value");
      if (maxYear < year) year = maxYear;
      $("#year" + this.name).val(year);
    } else $("#year" + this.name).html(year);

    this.current.month = month;
    this.current.year = year;

    var date = new Date();
    var today = new Date();
    date.setYear(year);
    date.setMonth(month - 1);
    date.setDate(1);
    firstDay = date.getDay() == 0 ? 7 : date.getDay();

    var days = "";
    var totalDays = 0,
      lastTotalDays = 0;
    var leapYear = year % 4 == 0 ? true : false;

    if (
      month == 1 ||
      month == 3 ||
      month == 5 ||
      month == 7 ||
      month == 8 ||
      month == 10 ||
      month == 12
    ) {
      totalDays = 31;
      lastTotalDays =
        month == 3
          ? leapYear
            ? 29
            : 28
          : month == 1
          ? (lastTotalDays = 31)
          : 30;
    } else if (month == 4 || month == 6 || month == 9 || month == 11) {
      totalDays = 30;
      lastTotalDays = 31;
    } else if (month == 2) {
      totalDays = leapYear ? 29 : 28;
      lastTotalDays = 31;
    }

    week = 0;

    prevMonth =
      month == 1 ? 12 : month - 1 < 10 ? "0" + (month - 1) : month - 1;
    prevYear = prevMonth == 12 ? year - 1 : year;

    for (i = lastTotalDays - (firstDay - 2); i <= lastTotalDays; i++) {
      day = i < 10 ? "0" + i : i;
      days +=
        '<a href="' +
        day +
        "." +
        prevMonth +
        "." +
        prevYear +
        '" onclick="event.returnValue=false; return C.' +
        this.name +
        '.setDate(this);" class="day prev">' +
        i +
        "</a>";
      week++;
    }

    m = month < 10 ? "0" + month : month;
    for (i = 1; i <= totalDays; i++) {
      day = i < 10 ? "0" + i : i;
      todayCls =
        month == today.getMonth() + 1 &&
        i == today.getDate() &&
        year == today.getFullYear()
          ? "today"
          : "";
      selectedCls =
        month == this.selected.month &&
        i == this.selected.date &&
        year == this.selected.year
          ? "selected"
          : "";
      days +=
        '<a href="' +
        day +
        "." +
        m +
        "." +
        year +
        '" onclick="event.returnValue=false; return C.' +
        this.name +
        '.setDate(this);" class="day ' +
        todayCls +
        " " +
        selectedCls +
        '">' +
        i +
        "</a>";
      week++;

      if (week == 7) {
        days += '<div class="clr"><!-- --></div>';
        week = 0;
      }
    }

    if (week > 0) {
      month = month == 12 ? 1 : month + 1;
      year = month == 1 ? year + 1 : year;
      m = month < 10 ? "0" + month : month;
      for (i = 1; i <= 7 - week; i++) {
        day = i < 10 ? "0" + i : i;
        days +=
          '<a href="' +
          day +
          "." +
          m +
          "." +
          year +
          '" onclick="event.returnValue=false; return C.' +
          this.name +
          '.setDate(this); " class="day next">' +
          i +
          "</a>";
      }
    }

    $("#calendar" + this.name + " .days").html(days);
    $("#calendar" + this.name).addClass("calendar-over");

    $("#RemindPerson").val($(".add input[name=RemindTo]").val());
  };

  $("#RemindPerson").autocomplete({
    source: "/lv/Josn/Persons",
    select: function () {
      $("#RemindPerson").focus();
    },
    minLength: 2,
  });

  this.setDate = function (el) {
    if (typeof el != "undefined") {
      $("#calendar" + this.name + " a.selected").removeClass("selected");
      $(el).addClass("selected");

      var date = el.href.substr(el.href.lastIndexOf(".") - 5, 10).split(".");
      $("#" + this.name).val(
        date[2].substring(2, 4) + "." + date[1] + "." + date[0]
      );
      if (!this.showTime) $("#calendar" + this.name).hide();

      this.selected.date = date[0];
      this.selected.month = date[1];
      this.selected.year = date[2];
    } else {
      v = $("#" + this.name);
      h = $("#calendar" + this.name + " select.hours").val();
      h = h < 10 ? "0" + h : h;
      m = $("#calendar" + this.name + " select.minutes").val();
      m = m < 10 ? "0" + m : m;
      v.val(
        this.selected.year.toString().substring(2, 4) +
          "." +
          this.selected.month +
          "." +
          this.selected.date +
          " " +
          h +
          ":" +
          m
      );
      $("#calendar" + this.name).hide();
    }

    $(".add input[name=RemindTo]").val($("#RemindPerson").val());

    return false;
  };
  this.showDays(this.current.month, this.current.year);
}
