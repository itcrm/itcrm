jQuery(document).ready(function () {
  document.documentElement.onmousemove = dataCursor;
});

function rejectEnter(e) {
  var key;

  if (window.event) key = window.event.keyCode;
  //IE
  else key = e.which;
  //firefox

  if (key == 13) return false;
  else return true;
}

function dataCursor(e) {
  e = e ? e : window.event;
  var cursor = new Array();
  cursor["X"] = e.clientX;
  cursor["Y"] = e.clientY;

  cursor["X"] += document.documentElement.scrollLeft
    ? document.documentElement.scrollLeft
    : 0;
  cursor["Y"] += document.documentElement.scrollTop
    ? document.documentElement.scrollTop
    : 0;

  $("#Loading").css("left", cursor["X"] + 20 + "px");
  $("#Loading").css("top", cursor["Y"] + 10 + "px");
}

function readForm(form) {
  var data = "";
  $("input, textarea, select", form).each(function () {
    if (this.name != "")
      if (this.type == "select-multiple") {
        for (x = 0; x < this.options.length; x++) {
          if (this.options[x].selected == true)
            data +=
              "&" +
              this.name +
              "[]=" +
              encodeURIComponent(this.options[x].value);
        }
      } else if (this.type != "checkbox" && this.type != "radio") {
        value = this.value ? encodeURIComponent(this.value) : "";
        data += "&" + this.name + "=" + value;
      } else if (this.checked == true)
        data += "&" + this.name + "=" + encodeURIComponent(this.value);
  });

  return data;
}

function Login() {
  f = $("#LoginForm");
  var data = readForm(f);

  success = function (answ) {
    Loading(f, 0);

    if (answ == 1) {
      window.location.replace(URL);
    } else {
      alert(answ);
    }
  };
  Loading(f, 1);
  $.post(URL + "/Users/Logon", data, success);
}

function Save(Class) {
  if (Class == "Data") {
    editbox(0, this);
    $("input.edit").removeClass("edit");
    $("input.active").removeClass("active");

    ID = $("form#AddDataForm input.hide").val();
    if (Number(ID) > 0) {
      var r = confirm("Vai vēlaties labot šo rindu?");
      if (r == false) {
        return false;
      }
    }
  }

  f = $("#Add" + Class + "Form");
  var data = readForm(f);
  if (Class == "Data" || Class == "Filters") {
    $("input:not(:submit)", f).addClass("light");
  }

  $("input, select", f).removeClass("error");

  success = function (answ) {
    if (Class == "Task") {
      {
        Loading(f, 0);
        a = answ;
      }
    } else {
      Loading(f, 0);
      try {
        answ = JSON.parse(answ);
      } catch (ex) {
        answ = new Array(answ);
      }

      if (answ[0] == 1) {
        ID = $("input[name=ID]", f).attr("value");
        if (answ[1].length)
          if (ID == 0) {
            if (Class == "Data")
              $("#" + Class + "List tr:first").before(answ[1]);
            else $("#" + Class + "List tr:first").after(answ[1]);
            $("#ievadeNoliktava").remove();
          } else {
            //$('#FilterForm').removeClass('hideFilter');
            $("#FilterForm").css("visibility", "visible");
            $("#" + Class + "" + ID).replaceWith(answ[1]);
            $("#ievadeNoliktava").remove();
          }

        f[0].reset();
        $(":input:not(:checkbox)", "#AddUsersForm")
          .not(":button, :submit, :reset, :hidden")
          .val("");
        $(":input", "#AddUsersForm")
          .not(":button, :submit, :reset, :hidden")
          .removeAttr("checked")
          .removeAttr("selected");
      } else if (answ[0] == 0) {
        $("#SaveFail").dialog({
          autoOpen: true,
          show: "blind",
          hide: "explode",
          resizable: false,
          height: 100,
        });

        $.each(answ, function (k, v) {
          $("input[name=" + k + "], select[name=" + k + "]", f).addClass(
            "error"
          );
        });
      } else alert(answ[0]);
    }
  };
  Loading(f, 1);
  $.post(URL + "/" + Class + "/Save", data, success);
}

function Delete(ID, Class) {
  var data = "ID=" + ID;
  success = function (answ) {
    Loading(0, 0);
    if (answ == 1) {
      tr = $("#" + Class + "" + ID);
      if (tr.hasClass("deleted")) tr.remove();
      else {
        tr.addClass("deleted");
        $("td:last a.restore", tr).removeClass("hide");
      }
    } else alert(answ);
  };
  if (confirm(MSG_CONFIRM_DEL) == true) {
    Loading(0, 1);
    $.post(URL + "/" + Class + "/Delete", data, success);
  }
}

function Restore(ID, Class) {
  var data = "ID=" + ID;
  success = function (answ) {
    Loading(0, 0);
    if (answ == 1) {
      tr = $("#" + Class + "" + ID).removeClass("deleted");
      $("td:last a.restore", tr).addClass("hide");
    } else alert(answ);
  };
  Loading(0, 1);
  $.post(URL + "/" + Class + "/Restore", data, success);
}

function addAutocomplete() {
  idPerson = $(".add [name=IDPerson]");
  idPerson.val("");
  idRemindTo = $(".add [name=RemindTo]");
  idOperator = $(".add [name=IDOperator]");
  idOperator.val("");
  idOrder = $(".add [name=IDOrder]");
  idOrder.val("");
  idType = $(".add [name=IDType]");
  idType.val("");

  Person = $(".add [name=PersonSelect]").val();
  RemindTo =
    typeof $("#RemindPerson").val() != "undefined"
      ? $("#RemindPerson").val()
      : idRemindTo.val();
  Operator = $(".add [name=OperatorSelect]").val();
  Order = $(".add [name=OrderSelect]").val();
  Type = $(".add [name=TypeSelect]").val();

  for (k in users) {
    if (users[k].name == Person) idPerson.val(users[k].val);
    if (users[k].name == Operator) idOperator.val(users[k].val);
    if (users[k].name == RemindTo) idRemindTo.val(users[k].val);
  }

  for (k in orders) {
    if (orders[k].name == Order) idOrder.val(orders[k].val);
  }

  for (k in types) {
    if (types[k].name == Type) idType.val(types[k].val);
  }
}

function filterAutocomplete(t) {
  if (t == 1) {
    idPerson = $("#FilterForm [name=Person]").val();
    idOperator = $("#FilterForm [name=Operator]").val();
    idOrder = $("#FilterForm [name=Order]").val();
    idType = $("#FilterForm [name=Type]").val();

    Person = $("#FilterForm [name=PersonFilterSelect]");
    Person.val("");
    Operator = $("#FilterForm [name=OperatorFilterSelect]");
    Operator.val("");
    Order = $("#FilterForm [name=OrderFilterSelect]");
    Person.val("");
    Type = $("#FilterForm [name=TypeFilterSelect]");
    Type.val("");

    var usersData = users;
    var ordersData = orders;
    var typesData = types;

    if (idOperator) {
      var Soperator = idOperator.split(", ");
    } else {
      var Soperator = "";
    }

    //var Soperator = idOperator.split(', ');
    var OperatorRez = "";
    for (G in Soperator) {
      for (k in usersData) {
        if (usersData[k].val == Soperator[G])
          OperatorRez = OperatorRez + usersData[k].name + ", ";
      }
    }
    Operator.val(OperatorRez);

    if (idPerson) {
      var Sperson = idPerson.split(", ");
    } else {
      var Sperson = "";
    }

    //var Sperson = idPerson.split(', ');
    var PersonRez = "";
    for (G in Sperson) {
      for (k in usersData) {
        if (usersData[k].val == Sperson[G])
          PersonRez = PersonRez + usersData[k].name + ", ";
      }
    }
    Person.val(PersonRez);

    if (idOrder) {
      var Sorder = idOrder.split(", ");
    } else {
      var Sorder = "";
    }

    var OrderRez = "";
    for (G in Sorder) {
      for (k in ordersData) {
        if (ordersData[k].val == Sorder[G])
          OrderRez = OrderRez + ordersData[k].name + ", ";
      }
    }
    Order.val(OrderRez);

    if (idType) {
      var Stype = idType.split(", ");
    } else {
      var Stype = "";
    }

    var TypeRez = "";
    for (G in Stype) {
      for (k in typesData) {
        if (typesData[k].val == Stype[G])
          TypeRez = TypeRez + typesData[k].name + ", ";
      }
    }
    Type.val(TypeRez);
  } else {
    idPerson = $("#FilterForm [name=Person]");
    idPerson.val("");
    idOperator = $("#FilterForm [name=Operator]");
    idOperator.val("");
    idOrder = $("#FilterForm [name=Order]");
    idOrder.val("");
    idType = $("#FilterForm [name=Type]");
    idType.val("");

    Person = $("#FilterForm [name=PersonFilterSelect]").val();
    Operator = $("#FilterForm [name=OperatorFilterSelect]").val();
    Order = $("#FilterForm [name=OrderFilterSelect]").val();
    Type = $("#FilterForm [name=TypeFilterSelect]").val();

    var OperatorRez = "";
    var AOperator = Operator.split(", ");
    for (g in AOperator) {
      for (k in users) {
        if (users[k].name == AOperator[g]) {
          OperatorRez = OperatorRez + users[k].val + ", ";
        }
      }
    }
    var strLen = OperatorRez.length;
    OperatorRez = OperatorRez.slice(0, strLen - 2);
    idOperator.val(OperatorRez);

    var PersonRez = "";
    var APerson = Person.split(", ");
    for (g in APerson) {
      for (k in users) {
        if (users[k].name == APerson[g]) {
          PersonRez = PersonRez + users[k].val + ", ";
        }
      }
    }
    var strLen = PersonRez.length;
    PersonRez = PersonRez.slice(0, strLen - 2);
    idPerson.val(PersonRez);

    var OrderRez = "";
    var AOrder = Order.split(", ");
    for (g in AOrder) {
      for (k in orders) {
        if (orders[k].name == AOrder[g]) {
          OrderRez = OrderRez + orders[k].val + ", ";
        }
      }
    }
    var strLen = OrderRez.length;
    OrderRez = OrderRez.slice(0, strLen - 2);
    idOrder.val(OrderRez);

    var TypeRez = "";
    var AType = Type.split(", ");
    for (g in AType) {
      for (k in types) {
        if (types[k].name == AType[g]) {
          TypeRez = TypeRez + types[k].val + ", ";
        }
      }
    }
    var strLen = TypeRez.length;
    TypeRez = TypeRez.slice(0, strLen - 2);
    idType.val(TypeRez);
  }
}

function showChanges(ID, Class) {
  if (typeof Class == "undefined") Class = "Data";
  if ($("#Changes" + ID).html() != "") {
    $("#Changes" + ID).toggle();
    return;
  }

  var data = "ID=" + ID;
  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
      if (answ[0] == 1) {
        $("#Changes" + ID).html(answ[1]);
        $("#Changes" + ID).toggle();
      } else alert(answ[1]);
    } catch (ex) {
      alert(answ);
    }
  };
  Loading(0, 1);
  $.post(URL + "/" + Class + "/Changes", data, success);
}

function EditUser(ID) {
  $(":input:not(:checkbox)", "#AddUsersForm")
    .not(":button, :submit, :reset, :hidden")
    .val("");
  $(":input", "#AddUsersForm")
    .not(":button, :submit, :reset, :hidden")
    .removeAttr("checked")
    .removeAttr("selected");
  f = $("#AddUsersForm");
  tds = $("#Users" + ID + " td");

  $("input[name=ID]", f).attr("value", tds[0].innerHTML);
  $("input[name=Login]", f).attr("value", tds[1].innerHTML);
  $("input[name=Color]", f).attr("value", tds[2].style.backgroundColor);
  $("div.color_picker", f).css(
    "background-color",
    tds[2].style.backgroundColor
  );

  $("input[name=Name]", f).attr("value", tds[3].innerHTML);
  $("input[name=Phone]", f).attr("value", tds[4].innerHTML);
}

function EditType(ID) {
  f = $("#AddTypesForm");
  tds = $("#Types" + ID + " td");

  $("input[name=ID]", f).attr("value", parseInt(tds[0].innerHTML));
  $("input[name=Code]", f).attr("value", tds[1].innerHTML);
  $("input[name=Description]", f).attr("value", tds[2].innerHTML);
}

function EditOrder(ID) {
  f = $("#AddOrdersForm");
  tds = $("#Orders" + ID + " td");

  $("input[name=ID]", f).attr("value", parseInt(tds[0].innerHTML));
  $("input[name=Code]", f).attr("value", tds[1].innerHTML);
  $("input[name=Color]", f).attr("value", tds[2].style.backgroundColor);
  $("div.color_picker", f).css(
    "background-color",
    tds[2].style.backgroundColor
  );
  $("input[name=Description]", f).attr("value", $("#Desc" + ID).html());

  document.documentElement.scrollTop = 0 + "px";
}

function dropReminder(calendar) {
  f = $("#AddDataForm");
  $("input[name=RemindDate]", f).val("");
  $("input[name=RemindTo]", f).val("");
  $("#RemindPerson").val("");
  $("#calendar" + calendar).hide();

  $(".AddDataForm input#Data").val("2000-00-00");
  $(".AddDataForm input#h").val("00");
  $(".AddDataForm input#m").val("00");
  $("#RemindPerson").val("");
  $(".AddDataForm input[name=RemindTo]").val("0");
}

function EditData(ID, noEdit) {
  f = $("#AddDataForm");

  $("#DataList .onedit").removeClass("onedit");

  if (noEdit != 1) {
    $("input[name=ID]", f).val(ID);
    if ($("#Data" + ID).hasClass("selected"))
      $($("#Data" + ID))
        .removeClass("selected")
        .addClass("Selected");
    $("#Data" + ID).addClass("onedit");
  } else {
    $("input[name=ID]", f).val(0);
  }

  $("input[name=IDDoc]", f).val($("#Doc" + ID).html());
  $("input[name=Date]", f).val($("#Date" + ID).html());
  $("input[name=PersonSelect]", f).val($("#Person" + ID).html());
  $("input[name=OrderSelect]", f).val($("#Order" + ID).html());
  $("input[name=TextOrder]", f).val($("#TextOrder" + ID).html());
  $("input[name=TypeSelect]", f).val($("#Type" + ID).html());
  $("input[name=TextType]", f).val($("#TextType" + ID).html());
  $("input[name=Sum]", f).val($("#Sum" + ID).html());
  $("input[name=Hours]", f).val($("#Hours" + ID).html());
  $("input[name=PlaceTaken]", f).val($("#PlaceTaken" + ID).html());
  $("input[name=PlaceDone]", f).val($("#PlaceDone" + ID).html());
  $("input[name=Note]", f).val($("#Note" + ID).html());
  $("input[name=BookNote]", f).val($("#BookNote" + ID).html());
  $("input[name=TotalPrice]", f).val($("#TotalPrice" + ID).html());
  $("input[name=PriceNote]", f).val($("#PriceNote" + ID).html());
  $("input[name=RemindDate]", f).val($("#RemindDate" + ID).html());
  $("input[name=RemindDateEnd]", f).val($("#RemindDateEnd" + ID).html());
  $("input[name=RemindTo]", f).val($("#RemindTo" + ID).html());
  if (typeof $("#RemindPerson").val() != "undefined")
    $("#RemindPerson").val($("#RemindTo" + ID).html());

  $("input, select", f).removeClass("light");

  document.documentElement.scrollTop = "0px";
}

function reversEdit() {
  f = $("#AddDataForm");

  tmp = $("input[name=OrderSelect]", f).val();
  $("input[name=OrderSelect]", f).val($("input[name=PersonSelect]", f).val());
  $("input[name=TextOrder]", f).val(
    tmp + " / " + $("input[name=TextOrder]", f).val()
  );
  $("input[name=TypeSelect]", f).val("a");
}

function EditFilter(ID) {
  f = $("#AddFiltersForm");

  $("input[name=ID]", f).val($("#ID" + ID).html());
  $("input[name=Name]", f).val($("#Name" + ID).html());
  $("select[name=Date]", f).val($("#Date" + ID).html());
  $("select[name=DateType]", f).val($("#DateType" + ID).html());
  $("input[name=PersonSelect]", f).val($("#Person" + ID).html());
  $("input[name=OperatorSelect]", f).val($("#Operator" + ID).html());
  $("input[name=OrderSelect]", f).val($("#Order" + ID).html());
  $("input[name=TextOrder]", f).val($("#TextOrder" + ID).html());
  $("input[name=TypeSelect]", f).val($("#Type" + ID).html());
  $("input[name=TextType]", f).val($("#TextType" + ID).html());
  $("input[name=Sum]", f).val($("#Sum" + ID).html());
  $("input[name=Hours]", f).val($("#Hours" + ID).html());
  $("input[name=PlaceTaken]", f).val($("#PlaceTaken" + ID).html());
  $("input[name=PlaceDone]", f).val($("#PlaceDone" + ID).html());
  $("input[name=Note]", f).val($("#Note" + ID).html());
  $("input[name=Search]", f).val($("#Search" + ID).html());
  $("input[name=BookNote]", f).val($("#BookNote" + ID).html());

  $("input, select", f).removeClass("light");
}

function FilterData(form) {
  f = $("#FilterForm");

  var data = readForm(f);
  success = function (answ) {
    Loading(f, 0);
    if (answ == 1) {
      window.location.replace(URL + "/Data");
    } else alert(answ);
  };
  Loading(f, 1);
  $.post(URL + "/Data/Filter", data, success);
}

function FilterOrders() {
  f = $("#AddOrdersForm");

  var data = readForm(f);
  success = function (answ) {
    Loading(f, 0);
    if (answ == 1) {
      window.location.replace(URL + "/Orders");
    } else alert(answ);
  };
  Loading(f, 1);
  $.post(URL + "/Orders/Filter", data, success);
}

function changeSort() {
  data = "1";
  success = function (answ) {
    Loading(0, 0);
    if (answ == 1) {
      window.location.replace(window.location.href);
    } else alert(answ);
  };
  Loading(0, 1);
  $.post(URL + "/Data/Sort", data, success);
}

function changeOrderSort(t) {
  data = 1;
  success = function (answ) {
    Loading(0, 0);
    if (answ == 1) {
      window.location.replace(URL + "/Orders");
    } else alert(answ);
  };
  Loading(0, 1);
  $.post(URL + "/Orders/Sort", data, success);
}

function Loading(f, t) {
  if (t == 1) {
    if (f != 0) $("input:submit", f).attr("disabled", true);
    $("#Loading").show();
  } else {
    if (f != 0) $("input:submit", f).attr("disabled", false);
    $("#Loading").hide();
  }
}

function changeDateInterval(val) {
  d = new Date();
  from = $("#FilterForm input[name=DateFrom]");
  to = $("#FilterForm input[name=DateTo]");

  // Use UTC methods so filter dates match the server/DB timezone (UTC)
  monthDays = new Array();
  isLeap = d.getUTCFullYear() % 4 == 0;
  for (i = 1; i < 13; i++) {
    if (i == 1 || i == 3 || i == 5 || i == 7 || i == 8 || i == 10 || i == 12) {
      monthDays[i] = 31;
    } else if (i == 4 || i == 6 || i == 9 || i == 11) {
      monthDays[i] = 30;
    } else if (i == 2) {
      monthDays[i] = isLeap ? 29 : 28;
    }
  }

  if (val == 1) {
    month = d.getUTCMonth() + 1;
    dateFrom = d.getUTCDate();
    dateTo = d.getUTCDate() + 1;

    from.val(d.getUTCFullYear() + "-" + month + "-" + dateFrom);
    to.val(d.getUTCFullYear() + "-" + month + "-" + dateTo);
  } else if (val == 2) {
    if (d.getUTCDate() - 7 < 0 && d.getUTCMonth() == 0) {
      year = d.getUTCFullYear() - 1;
      month = 12;
      date = monthDays[month] + (d.getUTCDate() - 7);
    } else if (d.getUTCDate() - 7 < 0) {
      year = d.getUTCFullYear();
      month = d.getUTCMonth();
      date = monthDays[month] + (d.getUTCDate() - 7);
    } else {
      year = d.getUTCFullYear();
      month = d.getUTCMonth() + 1;
      date = d.getUTCDate() - 7;
    }

    monthTo = d.getUTCMonth() + 1;
    monthFrom = month;
    dateTo = d.getUTCDate() + 1;
    dateFrom = date;

    from.val(year + "-" + monthFrom + "-" + dateFrom);
    to.val(d.getUTCFullYear() + "-" + monthTo + "-" + dateTo);
  } else if (val == 3) {
    if (d.getUTCMonth() == 0) {
      year = d.getUTCFullYear() - 1;
      month = 12;
    } else {
      year = d.getUTCFullYear();
      month = d.getUTCMonth();
    }

    month = month;
    monthTo = d.getUTCMonth() + 1;
    dateFrom = d.getUTCDate() + 1;
    dateTo = d.getUTCDate() + 1;

    from.val(year + "-" + month + "-" + dateFrom);
    to.val(d.getUTCFullYear() + "-" + monthTo + "-" + dateTo);
  } else if (val == 4) {
    month = d.getUTCMonth() + 1;
    dateFrom = d.getUTCDate() + 1;
    dateTo = d.getUTCDate() + 1;

    from.val(d.getUTCFullYear() - 1 + "-" + month + "-" + dateFrom);
    to.val(d.getUTCFullYear() + "-" + month + "-" + dateTo);
  } else if (val == 5) {
    month = d.getUTCMonth() + 1;
    dateFrom = d.getUTCDate() + 1;
    year = d.getUTCFullYear();

    from.val("2000-1-1");
    to.val(year + "-" + month + "-" + dateFrom);
  }

  f = from.val().split("-");
  if (f[2] > monthDays[f[1]]) {
    f[2] = 1;
    f[1] = parseInt(f[1]) + 1;
  }
  if (f[1] > 12) {
    f[1] = 1;
    f[0] = parseInt(f[0]) + 1;
  }
  f[2] = f[2] < 10 ? "0" + parseInt(f[2]) : f[2];
  f[1] = f[1] < 10 ? "0" + parseInt(f[1]) : f[1];

  t = to.val().split("-");
  if (t[2] > monthDays[t[1]]) {
    t[2] = 1;
    t[1] = parseInt(t[1]) + 1;
  }
  if (t[1] > 12) {
    t[1] = 1;
    t[0] = parseInt(t[0]) + 1;
  }
  t[2] = t[2] < 10 ? "0" + parseInt(t[2]) : t[2];
  t[1] = t[1] < 10 ? "0" + parseInt(t[1]) : t[1];

  from.val(f[0] + "-" + f[1] + "-" + f[2]);
  to.val(t[0] + "-" + t[1] + "-" + t[2]);
}

function SaveSupplier() {
  f = $("#SupplierForm");

  var data = readForm(f);
  $("input, select", f).removeClass("error");

  success = function (answ) {
    Loading(f, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      ID = $("input[name=ID]", f).attr("value");
      if (answ[1].length)
        if (ID == 0) {
          $("#Suppliers").append(answ[1]);
          $("#Suppliers input.picker:last").colorPicker();
        } else {
          lbl = $("#Supplier" + ID + " .label");
          $("b", lbl).html($("input[name=Name]", f).val());
          $("span", lbl).html($("input[name=Description]", f).val());
          lbl.css("background-color", $("input[name=Color]", f).val());
        }

      f[0].reset();
      $("input[name=Reset]", f).hide();
    } else if (answ[0] == 0) {
      $.each(answ, function (k, v) {
        $("input[name=" + k + "], select[name=" + k + "]", f).addClass("error");
      });
    } else alert(answ[0]);
  };
  Loading(f, 1);
  $.post(URL + "/Suppliers/Save", data, success);
}

function getSupplier(el) {
  ID = el.id.replace(/Data/, "");
  var data = "IDData=" + ID;

  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      $("#Suppliers").html(answ[1]);
      container = $("#scrollDiv");
      $("#info").css(
        "top",
        $(el).offset().top -
          container.offset().top +
          $(el).height() +
          container[0].scrollTop
      );
      $("#Suppliers input.picker").colorPicker();
      $("#SupplierForm input[name=IDData]").val(ID);
      $("#info").show();
    } else alert(answ[0]);
  };
  Loading(0, 1);
  $.post(URL + "/Info/Get", data, success);
}

function getPavadzime(el) {
  ID = el.id.replace(/Data/, "");
  var data = "IDData=" + ID;

  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      $("#PDati").html(answ[1]);
      container = $("#scrollDiv");
      $("#pavadzime").css(
        "top",
        $(el).offset().top -
          container.offset().top +
          $(el).height() +
          container[0].scrollTop
      );

      $("#pavadzime").show();
    } else alert(answ[0]);
  };
  Loading(0, 1);
  $.post(URL + "/Pavadzime/Get", data, success);
}

function DelSan(ID) {
  var data = "ID=" + ID;
  success = function (answ) {
    Loading(0, 0);
    if (answ == 1) {
      var Data = "0";
      success = function (answ) {
        Loading(0, 0);
        answ = decodeURIComponent(answ);
        $("div#EditSanList table.AutoTable tbody").html(answ);
      };
      Loading(0, 1);
      $.post(URL + "/Pavadzime/EditSanList", Data, success);
    } else {
      alert(answ);
    }
  };

  Loading(0, 1);
  $.post(URL + "/Pavadzime/DelSan", data, success);
}

function saveInfo(IDS, IDD) {
  var info = $("#Info" + IDS).val();
  //var color = $('#Color'+IDS).val();

  var data = "IDData=" + IDD + "&IDSupplier=" + IDS + "&Info=" + info;
  //+'&Color='+color;

  success = function (answ) {
    Loading(0, 0);

    if (answ == 1) {
      el = $("#Supplier" + IDS + " .info");
      el.html(info);
      //el.parent().css('background',color);
      //el.bind('click',function() { showInfo(this); });
      //$('#Supplier'+IDS+' div.color_picker').hide();
    } else alert(answ);
  };
  Loading(0, 1);
  $.post(URL + "/Info/Save", data, success);
}

function editSupplier(el, id) {
  f = $("#SupplierForm");
  $("input[name=ID]", f).val(id);
  $("input[name=Color]", f).val(el.style.backgroundColor);
  $("input[name=Name]", f).val($("b", el).html());
  $("input[name=Description]", f).val($("span", el).html());
  $("input[name=Reset]", f).show();
  $("a", f).show();
  //$('a',f).show();
  $("div.color_picker", f).css("background-color", el.style.backgroundColor);
}

function editInfo(IDS, IDD) {
  el = $("#Supplier" + IDS + " .info");
  $("#Supplier" + IDS + " a")
    .removeClass("edit")
    .addClass("restore")
    .attr("href", "javascript:saveInfo(" + IDS + "," + IDD + ");");
  el.html("");
  el.attr("onclick", "");
  el.unbind("click");
  $("#Info" + IDS).focus();
  $("#Supplier" + IDS + " div.color_picker").show();
}

function showInfo(el, cls) {
  if (typeof cls == "undefined") cls = "over";

  if ($(el).hasClass(cls)) {
    $(el).removeClass(cls);
    $(el).parent().css("z-index", "10");
  } else {
    $(el).addClass(cls);
    $(el).parent().css("z-index", "900");
  }
}

function filterSuppliers(str) {
  sups = $("#Suppliers .supplier");

  $.each(sups, function () {
    data = $("b,span", this);
    if (
      data.get(0).innerHTML.toLowerCase().indexOf(str.toLowerCase()) > -1 ||
      data.get(1).innerHTML.toLowerCase().indexOf(str.toLowerCase()) > -1
    ) {
      $(this).show();
    } else $(this).hide();
  });
}

function GetTpl(ID, t) {
  //must all forms edit overwrite through this
  f = $("#AddDataForm");

  $("input, select", f).removeClass("error");

  success = function (answ) {
    Loading(f, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      $.each(answ, function (k, v) {
        if (k != 0) {
          if (t == 1 && (k == "ID" || k == "BookNote")) return;
          if (typeof v == "object") {
            $("select[name=" + k + "]", f).html(v["html"]);
            $("select[name=" + k + "]", f).val(v["val"]);
          } else {
            $(
              "input[name=" +
                k +
                "], select[name=" +
                k +
                "], textarea[name=" +
                k +
                "]",
              f
            ).val(v);
          }

          $("input[name=" + k + "], select[name=" + k + "]", f).removeClass(
            "light"
          );
        }
      });
    } else alert(answ[0]);
  };
  Loading(f, 1);
  $.post(URL + "/Data/GetTpl", "ID=" + ID, success);
}

function getFilterData(id) {
  $("form#FilterForm [name=Search]").focus();

  if (id == 0) return;

  f = $("#FilterForm");
  $("input:not(:submit,:button)", f).val("");
  success = function (answ) {
    Loading(f, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      $.each(answ, function (k, v) {
        if (k == "Date") {
          v.From = v.From.split(" ");
          v.To = v.To.split(" ");
          $("input[name=DateFrom]", f).val(v.From[0]);
          $("input[name=DateTo]", f).val(v.To[0]);
        } else if (k != 0) {
          $("input[name=" + k + "]", f)
            .val(v)
            .removeClass("light");
        }
      });
    } else alert(answ[0]);
  };
  Loading(f, 1);
  $.post(URL + "/Filters/Get", "ID=" + id, success);
}

function savetable() {
  var rinda = $("#Preces tr:last.bordersolidadd").attr("id");
  var i = 1;
  for (i = 1; i <= rinda; i++) {
    var entry = $("#Preces tr:last.bordersolidadd").attr("name");

    var data =
      "summa=" +
      $("td:last.Summa").text() +
      "&entryid=" +
      entry +
      "&id=" +
      $("input#pavadid").val() +
      "&nosaukums=" +
      $("tr:last.bordersolidadd>td>input.Precu_nosaukums").val() +
      "&artikuls=" +
      $("tr:last.bordersolidadd>td>input.Artikuls").val() +
      "&daudzums=" +
      $("tr:last.bordersolidadd>td>input.Daudz").val() +
      "&mervieniba=" +
      $("tr:last.bordersolidadd>td>input.Merv").val() +
      "&cena=" +
      $("tr:last.bordersolidadd>td>input.Cena").val();
    success = function (answ) {
      Loading(0, 0);
    };

    Loading(0, 1);
    $.post(URL + "/Pavadzime/LineSave", data, success);
    $("#Preces tr:last.bordersolidadd").remove();
    if (i == rinda) {
      return 1;
    }
  }
}

function bildsave(print) {
  a = savetable();
  if (a == 1) {
    if (!$("input.atlaidenr").val()) {
      a = 0;
    } else {
      a = $("input.atlaidenr").val();
    }
    var data =
      "ID=" +
      $("input#Saveid").val() +
      "&samaksaskartiba=" +
      $("input.SamKart").val() +
      "&Sanemejs=" +
      encodeURIComponent($("input#Sanemejs").val()) +
      "&pavadid=" +
      $("input#pavadid").val() +
      "&Atlaide=" +
      a +
      "&izsniedza=" +
      $("input.izsniedz").val() +
      "&Kopa=" +
      $("td#Kop").text() +
      "&atlaidessumma=" +
      $("td#atlaide").text() +
      "&PirmsNodokliem=" +
      $("td#sumaatlaide").text() +
      "&PVN=" +
      $("td#PVN").text() +
      "&Samaksai=" +
      $("td#PavisamSamaksai").text() +
      "&SanemejaID=" +
      $("input#SanemejsID").val();
    success = function (answ) {
      Loading(0, 0);
      if (answ == 1) {
        history.go(0);
        return 1;
      }
    };

    Loading(0, 1);
    $.post(URL + "/Pavadzime/BildSave", data, success);
  }

  if (print == 1) {
    return 1;
  }
}

function print() {
  // a = bildsave(1);
  //if (a == 1) {
  var ID = $("input#pavadid").val();
  window.open(URL + "/PavadzimePrints&ID=" + ID);
  //}
}

function NextPage(rindask) {
  var data = "lapa=" + rindask;
  success = function (answ) {
    Loading(0, 0);
    location.reload(true);
  };

  Loading(0, 1);
  $.post(URL + "/Data/Page", data, success);
}

function InPage(sk) {
  var data = "sk=" + sk;
  success = function (answ) {
    Loading(0, 0);
    location.reload(true);
  };

  Loading(0, 1);
  $.post(URL + "/Data/Pagesk", data, success);
}

function summ(a) {
  var celuzDaudz = "#Preces tbody>tr#" + a + ".bordersolidadd>td>input.Daudz";
  var celuzCena = "#Preces tbody>tr#" + a + ".bordersolidadd>td>input.Cena";
  var celuzSumma = "#Preces tbody>tr#" + a + ".bordersolidadd>td.Summa";
  var Daudz = $(celuzDaudz).val();
  var Cena = $(celuzCena).val();
  var summa = r4(Cena * Daudz);
  var atlaide1 = $("#Preces tbody>tr>td>input.atlaidenr").val();
  var atlaide = "0." + atlaide1 + "";
  $(celuzSumma).html(summa);
  $("#Preces tbody>tr.bordersolid>td#Kop").html(
    r4(sumOfColumns("Preces", 6, true))
  );

  $("#Preces tbody>tr>td#atlaide.bordersolid").html(
    r2(sumOfColumns("Preces", 6, true) * atlaide)
  );

  $("#Preces tbody>tr>td#sumaatlaide.bordersolid").html(
    r2(
      sumOfColumns("Preces", 6, true) -
        sumOfColumns("Preces", 6, true) * atlaide
    )
  );
  $("#Preces tbody>tr>td#PVN.bordersolid").html(
    r2(sumOfColumns("Preces", 6, true) * 0.0)
  );
  $("#Preces tbody>tr>td#PavisamSamaksai.bordersolid").html(
    r2(
      sumOfColumns("Preces", 6, true) -
        sumOfColumns("Preces", 6, true) * atlaide +
        sumOfColumns("Preces", 6, true) * 0.0
    )
  );
}

function sumOfColumns(tableID, columnIndex, hasHeader) {
  var tot = parseFloat(0.0);
  $("#" + tableID + " tr" + (hasHeader ? ":gt(0)" : ""))
    .children("td:nth-child(" + columnIndex + ")")
    .each(function () {
      tot += parseFloat($(this).html());
    });
  return tot;
}

function editbox(status, el) {
  if (status == 1) {
    $("input.active").removeClass("disabl");
    $("input.light:not(:button,:submit)").attr("disabled", true);
    $("input.disabl:not(:button,:submit)").attr("disabled", true);
    var text = $("input.edit").val();
    var strSingleLineText = text.replace(new RegExp(" ` ", "g"), "\n");
    $("#edittext").val(strSingleLineText);
    //$("#edittext").val($("input.edit").val());
    $("div.editbox").show();
    $("#edittext").focus();
    $("p").css("background-color", "yellow");
  } else {
    $("input.light").removeAttr("disabled");
    $("input.disabl").removeAttr("disabled");
    $("div.editbox").hide();

    var text = $("#edittext").val();
    var strSingleLineText = text.replace(new RegExp("\n", "g"), " ` ");
    //alert( strSingleLineText );
    $("input.edit").val(strSingleLineText);
    //$("input.edit").val($("#edittext").val());
    $("input.active:not(:button,:submit)").addClass("disabl");
  }
}

function CeckRow(ID) {
  var data = "row=" + ID;

  success = function (answ) {
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }
    $("#Data" + ID).fadeOut("slow", function () {
      $("#Data" + ID).remove();
      $("#DataList tr:first").before(answ[0]);
    });

    Loading(0, 0);
  };

  Loading(0, 1);
  $.post(URL + "/Data/CeckRow", data, success);
}

function UnCheckRow(ID) {
  var data = "row=" + ID;

  success = function (answ) {
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }
    $("#Data" + ID).fadeOut("slow", function () {
      $("#Data" + ID).remove();
      $("#DataList tr:last").before(answ[0]);
    });

    Loading(0, 0);
    //location.reload(true);
  };

  Loading(0, 1);
  $.post(URL + "/Data/UnCeckRow", data, success);
}

function clearForm(form) {
  $(":input", form).each(function () {
    var type = this.type;
    var tag = this.tagName.toLowerCase();
    if (type == "text") {
      this.value = "";
    }
  });
}

function extend(ID) {
  $("#slider" + ID).toggleClass("exp_hide");
  $("#exp_but" + ID).toggleClass("exp_but_up");
}

function RowEdit(ID) {
  var today = new Date();
  var now = new Date(today.getTime() + 24 * 60 * 60 * 1000);
  DateTo = now.getFullYear() + "-" + (now.getMonth() + 1) + "-" + now.getDate();
  var data =
    "&IDDoc=&ID=" +
    ID +
    "&DateFrom=2000-01-01%2000%3A00%3A00&DateTo=" +
    DateTo +
    "&Person=&Operator=&PersonFilterSelect=&OperatorFilterSelect=&Order=&OrderFilterSelect=&TextOrder=&Type=&TypeFilterSelect=&TextType=&Sum=&Hours=&PlaceTaken=&PlaceDone=&Search=&Note=&BookNote=&TotalPrice=&PriceNote=&IDFilter=0";
  success = function (answ) {
    Loading(0, 0);

    window.open(URL + "/Data");
    $("#task").dialog("destroy");
  };

  Loading(0, 1);
  $.post(URL + "/Data/Filter", data, success);
}

function getNoliktava(el, type) {
  ID = el.id.replace(/Data/, "");
  var data = "DetalasID=" + ID;
  container = $("#scrollDiv");
  $("#noliktava").css(
    "top",
    $(el).offset().top -
      container.offset().top +
      $(el).height() +
      container[0].scrollTop
  );
  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ == 0) {
      $("#DetalasForm #rindasID").val(ID);
      $("#noliktava").show();
    } else {
      $.each(answ, function (i, object) {
        $("#DetalasForm #" + i).val(object);
      });
      var text =
        '<a href="javascript:RowEdit(' +
        answ["detalasID"] +
        ');" >' +
        answ["nosaukums"] +
        "</a> uz doto brīdi noliktavā atlicis " +
        answ["atlikums"] +
        " " +
        answ["mervieniba"];
      $("#DetalasForm #atlikums").html(text);
      $("#noliktava").show();
    }
  };
  Loading(0, 1);
  $.post(URL + "/Data/noliktava", data, success);
}

function getNolMatreals(el) {
  ID = el.id.replace(/Data/, "");
  $("#MatrealsForm #rindasID").val(ID);
  $(el).removeClass("Odd");
  $(el).toggleClass("formback");

  var data = "DetalasID=" + ID;
  container = $("#scrollDiv");
  $("#matreals").css(
    "top",
    $(el).offset().top -
      container.offset().top +
      $(el).height() +
      container[0].scrollTop
  );
  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }
    $("div.matrealaapraksts span#Nosaukums").html(answ["PlaceTaken"]);
    if (answ == 0) {
      $("#MatrealsForm #rindasID").val(ID);
      $("#matreals").show();
    } else {
      $.each(answ, function (i, object) {
        if (i == "Shop" && object == 1) {
          $("#MatrealsForm #Shop").attr("checked", "checked");
        } else {
          $("#MatrealsForm #Shop").val(1);
        }
        $("#MatrealsForm #" + i).val(object);
      });
      var text =
        '<a href="javascript:RowEdit(' +
        answ["detalasID"] +
        ');" >' +
        answ["nosaukums"] +
        "</a> uz doto brīdi noliktavā atlicis " +
        answ["atlikums"] +
        " " +
        answ["mervieniba"] +
        " un rezervēti " +
        answ["rezervets"] +
        " " +
        answ["mervieniba"];
      $("#MatrealsForm #atlikums").html(text);

      $("#MatrealsForm #ShopModel")
        .bind("keydown", function (event) {
          if (
            event.keyCode === $.ui.keyCode.TAB &&
            $(this).data("autocomplete").menu.active
          ) {
            event.preventDefault();
          }
        })
        .autocomplete({
          source: "/lv/Josn/FilterTypes",
          focus: function () {
            return false;
          },
          select: function (event, ui) {
            var terms = split(this.value);
            terms.pop();
            terms.push(ui.item.value);
            terms.push("");
            this.value = terms.join(", ");

            var ID = $("#MatrealsForm input#ShopModelID");
            var termsID = split(ID.val());
            termsID.pop();
            termsID.push(ui.item.ID);
            termsID.push("");
            ID.val(termsID.join(", "));

            return false;
          },
          minLength: 1,
        });

      $("#MatrealsForm #ShopCategory").autocomplete({
        source: "/lv/Josn/Orders",
        select: function (event, ui) {
          var ID = $("#MatrealsForm input#ShopCategoryID");
          ID.val(ui.item.ID);
        },
        minLength: 1,
      });
      $("#matreals").show();
    }
  };
  Loading(0, 1);
  $.post(URL + "/Data/noliktava", data, success);
}

function NoliktavaAtlikums(ID) {
  var data = "ID=" + ID;

  success = function (answ) {
    Loading(0, 0);
    //alert(answ);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    var text =
      '<a href="javascript:RowEdit(' +
      answ["detalasID"] +
      ');" >' +
      answ["nosaukums"] +
      "</a> uz doto brīdi noliktavā atlicis " +
      answ["atlikums"] +
      " " +
      answ["mervieniba"] +
      " un rezervēti " +
      answ["rezervets"] +
      " " +
      answ["mervieniba"];
    $("#atlikums").html(text);
    $("#mervieniba").val(answ["mervieniba"]);
    $("#detalasID").val(answ["detalasID"]);
  };
  Loading(0, 1);
  $.post(URL + "/Data/NoliktavaAtlikums", data, success);
}

function addNoliktavaAutoComp() {
  $("#artikuls").autocomplete({
    source: "/lv/Josn/Noliktava",
    select: function (event, ui) {
      NoliktavaAtlikums(ui.item.ID);
    },
    minLength: 2,
  });
}

function NoliktavaSave() {
  var dz = $("#MatrealsForm #daudzums").val();

  //if(isNaN(dz) == true)
  //return alert('Daudzumam jābūt skaitlim.');

  var data = $("#MatrealsForm").serialize();
  var ID = $("#MatrealsForm #rindasID").val();
  success = function (answ) {
    Loading(0, 0);

    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      //$('#Data'+ID).replaceWith(answ[1]);
      SaveForm();
      $("#matreals").hide();
      clerDetalas();
    } else {
      alert(answ);
    }
  };

  Loading(0, 1);
  $.post(URL + "/Data/SaveDetala", data, success);
}

function NoliktavaDialogSave() {
  var dz = $("#MatrealsDialogForm #daudzums").val();

  //if(isNaN(dz) == true)
  //return alert('Daudzumam jābūt skaitlim.');

  var data = $("#MatrealsDialogForm").serialize();
  var ID = $("#MatrealsDialogForm #rindasID").val();

  success = function (answ) {
    Loading(0, 0);

    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ[0] == 1) {
      //$('#Data'+ID).replaceWith(answ[1]);
      SaveForm();
      $("#DialogForm").dialog("close");
      $("#DialogForm").remove();
      $("div#scrollDiv").append('<div id="DialogForm"></div>');
    } else {
      alert(answ);
    }
  };

  Loading(0, 1);
  $.post(URL + "/Data/SaveDetala", data, success);
}

function clerNoliktava() {
  document.getElementById("DetalasForm").reset();
  $("#noliktava #atlikums").html("");
}

function AddNoliktavaForm(ID) {
  var RowID = $("form#AddDataForm input.hide").val();

  if (
    ID == AddNolTyp ||
    ID == DelNolTyp ||
    ID == AtgNolTyp ||
    ID == RezNolTyp
  ) {
    $("#ievadeNoliktava").remove();
    $("#AddDataForm").append(
      '<div class="noliktava" id="ievadeNoliktava"> </div>'
    );
    $("#ievadeNoliktava").append("<span> Artikuls:</span>");
    $("#ievadeNoliktava").append('<input id="artikuls" type="text" />');
    $("#ievadeNoliktava").append(
      '<input id="detalasID" type="text" value="" name="detalasID" style="display: none">'
    );
    $("#ievadeNoliktava").append("<span> daudzums:</span>");
    $("#ievadeNoliktava").append(
      '<input name="daudzums" size="5" id="daudzums" type="text" />'
    );
    $("#ievadeNoliktava").append(
      '<input style="border:none; width: 30px; background-color: silver;" id="mervieniba"  readonly="readonly" type="text" />'
    );
    $("#ievadeNoliktava").append(
      '<a title="Pievienot jaunu preci" class="noliktavaadd" href="javascript:NewPrec();"><span class="ui-icon ui-icon-document"></span></a>'
    );
    $("#ievadeNoliktava").append("<hr>");
    $("#ievadeNoliktava").append('<span id="atlikums"></span>');

    addNoliktavaAutoComp();
  }
  if (ID == 72) {
    $("#pprForm").remove();
    $("#AddDataForm").append('<div class="noliktava" id="pprForm"> </div>');
    $("#pprForm").append("<span> Pavadzimes Nr:</span>");
    $("#pprForm").append(
      '<input id="pprNr" type="text" name="Nr" onblur="CechNrExist(this)"/>'
    );
    $("#pprForm").append("<hr>");
    $("#AddDataForm #pprNr").focus();
  }
}

function CechNrExist(Object) {
  var value = Object.value;
  if (value == "") {
    alert("Līguma numurs nav ievadīts!");
    $("#AddDataForm #pprNr").val("");
    $("#AddDataForm #pprNr").css("background-color", "red");
    $("#AddDataForm #pprNr").focus();
    return false;
  }
  $.ajax({
    url: "/lv/Josn/NrExist",
    data: {
      value: value,
    },
    success: function (data) {
      if (data == 1) {
        $("#AddDataForm #pprNr").css("background-color", "white");

        $("#AddDataForm [name=IDDoc]").val($("#AddDataForm #pprNr").val());
        $("#AddDataForm #LigumaNr").val($("#AddDataForm #pprNr").val());
      } else {
        alert("Šāds Numurs jau eksistē");
        $("#AddDataForm #pprNr").val("");
        $("#AddDataForm #pprNr").css("background-color", "red");
        $("#AddDataForm #pprNr").focus();
      }
    },
  });
}

function clerDetalas() {
  var ID = $("#MatrealsForm #rindasID").val();
  var Obj = $("tr#Data" + ID + ".Data");
  $(Obj).removeClass("formback");
  document.getElementById("MatrealsForm").reset();
  $("#MatrealsForm #Shop").attr("checked", false);
}

function ChangeField() {
  //funkcija paredzeta lauku mainai
  val = $("form.SelectChange select").val();
  val2 = val;
  val = val.substring(2, val.length);

  if (val == "Order" || val == "Person" || val == "Type") {
    $(".SelectChange #value").remove();
    $("form.SelectChange span").after(
      '<input type="text" id="value" name="value">'
    );
    $(".SelectChange #ID").remove();
    $(".SelectChange #value").val("");
    $(".SelectChange #value").attr("name", "values");
    $("form.SelectChange").append(
      '<input id="ID" type="text" value="" name="value" style="display: none">'
    );
    $("form.SelectChange #replace").attr("checked", true);
    $("form.SelectChange input:checkbox").attr("disabled", true);

    $(".SelectChange [name=values]").autocomplete({
      source: "/lv/Josn/" + val + "s",
      select: function (event, ui) {
        $(".SelectChange #ID").val(ui.item.ID);
      },
      minLength: 1,
    });
  } else {
    if ($(".SelectChange input#ID").length) {
      $(".SelectChange [name=values]").autocomplete("destroy");
      $(".SelectChange #ID").remove();
    }
    $(".SelectChange #value").remove();
    $("form.SelectChange span").after(
      '<input type="text" id="value" name="value">'
    );
    $(".SelectChange #value").attr("name", "value");
    $("form.SelectChange #replace").attr("checked", false);
    $("form.SelectChange input:checkbox").attr("disabled", false);
    //$(".SelectChange #value").attr("name",val)
  }
}

function ChangeSelected() {
  if ($(".SelectChange [name=fields]").val() == 0) {
    alert("Izvēlieties labošanas lauku!");
    return;
  }
  var checked = $("form.SelectChange input:checkbox").is(":checked");
  if (checked == false) {
    alert("Izvēlieties pievienošanas virzienu!");
    return;
  }
  if ($(".SelectChange #value").val() == "") {
    if (confirm("Tiktiešām vēlaties dzēst šo lauku?")) {
      var OK = "1";
    } else {
      return;
    }
  }
  var data = readForm($(".SelectChange"));

  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    if (answ == 1) {
      window.location.reload();
    } else {
      alert(answ);
    }
  };
  Loading(0, 1);
  $.post(URL + "/Data/ChangeSelected", data, success);
}

function nolAddDet() {
  var data = $("#NewDetForm").serialize();

  success = function (answ) {
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }
    var rindasID = answ[2];
    var daudzums = $("form#NewDetForm #daudzums").val();
    var detalasID = $("form#NewDetForm #detalasID").val();

    data =
      "rindasID=" +
      rindasID +
      "&daudzums=" +
      daudzums +
      "&detalasID=" +
      detalasID;

    $.post(URL + "/Data/SaveDetala", data);

    Loading(0, 0);
  };

  Loading(0, 1);
  $.post(URL + "/Data/Save", data, success);
}

function NewPrec() {
  clearNewDet();
  $("form#NewDetForm #IDType").val(noliktava);
  $("form#NewDetForm #OrderSelect").autocomplete({
    source: "/lv/Josn/Orders",
    select: function (event, ui) {
      $("form#NewDetForm #IDOrder").val(ui.item.ID);
    },
    minLength: 1,
  });

  $("#AddNol").dialog({
    buttons: {
      Saglabāt: function () {
        var novietojums = $("form#NewDetForm #PlaceDone").val();
        var mervieniba = $("form#NewDetForm #PriceNote").val();
        var minAtlik = $("form#NewDetForm #detalasID").val();
        $("form#NewDetForm #PlaceDone").val(
          novietojums + " min=" + minAtlik + mervieniba
        );
        nolAddDet();

        $(this).dialog("close");
      },
      Aizvēt: function () {
        $(this).dialog("close");
      },
    },
  });
}

function clearNewDet() {
  $("form#NewDetForm #OrderSelect").val("");
  $("form#NewDetForm #PlaceTaken").val("");
  $("form#NewDetForm #IDOrder").val("");
  $("form#NewDetForm #Note").val("");
  $("form#NewDetForm #PriceNote").val("");
  $("form#NewDetForm #daudzums").val("");
  $("form#NewDetForm #PlaceDone").val("");
  $("form#NewDetForm #detalasID").val("");
  $("form#NewDetForm #BookNote").val("");
  $("form#NewDetForm #TotalPrice").val("");
}

function CeckAllRow() {
  $.get("/lv/Data/AddAllSelected", "", function () {
    location.reload();
  });
  //location.reload();
  //$("div#scrollDiv table#DataList tbody tr td:first-child input:checkbox").each(function() {
  //CeckRow(this.value);
  // });
}

function NolCeckAllRow() {
  $("div#scrollDiv table#DataList tbody tr td:first-child input:checkbox").each(
    function () {
      CeckRow(this.value);
    }
  );
}

function UnCeckAllRow() {
  $("div#scrollDiv table#DataList tbody tr td:first-child input:checkbox").each(
    function () {
      UnCheckRow(this.value);
    }
  );
}

function SaveForm() {
  var ID = $("form#MatrealsDialogForm input#rindasID").val();
  var Sum = $("form#MatrealsDialogForm input#daudzums").val();
  var Data = $("form#MatrealsDialogForm").serialize();
  Data = Data + "&ID=" + ID + "&Sum=" + Sum;
  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }
    $("#Data" + ID).replaceWith(answ[1]);
  };
  Loading(0, 1);
  $.post("/lv/Data/FormSave", Data, success);
}

function editfil(ID) {
  //ID = '140';
  var data = "ID=" + ID;

  success = function (answ) {
    Loading(0, 0);
    try {
      answ = JSON.parse(answ);
    } catch (ex) {
      answ = new Array(answ);
    }

    $.each(answ, function (i, object) {
      $(".add [name=" + i + "]").val(object);
    });
  };
  Loading(0, 1);
  $.post(URL + "/Filters/editRow", data, success);
}

function OpenForm(Name, Blok, ParentBlok, nosaukums, Platums, ID, Save) {
  //Save = 1

  Loading(0, 1);
  $.ajax({
    type: "POST",
    cache: false,
    url: "/lv/Josn/" + Name,
    data: {
      ID: ID,
    },
    success: function (data) {
      $("#" + Blok + "")
        .html(data)
        .delay(300);
    },
  });

  var myButtons = {};

  if (Save > 0) {
    myButtons["Save"] = function () {
      DialogSave(Name, ID);
      $(this).dialog("close");
      $("#" + Blok + "").html(data);
    };
  }
  if (Save >= 0) {
    myButtons["Cancel"] = function () {
      $(this).dialog("close");
      $("#" + Blok + "").html("");
    };
  }

  $("#" + Blok + "").dialog({
    autoOpen: true,
    modal: true,
    width: Platums,
    buttons: myButtons,
    title: nosaukums,
    close: function (event, ui) {
      $("div#" + Blok + "").remove();
      $("div#" + ParentBlok + "").append('<div ID="' + Blok + '"></div>');
    },
  });
  Loading(0, 0);
}

function DialogSave(Name, ID) {
  if (Name == "AddSanemejs") {
    EditSan();
  }
  if (Name == "NewSanemejs") {
    AddSan();
  }
}

function EditSan() {
  var data = $("Form#ChangeSanemejs").serialize();
  success = function (answ) {
    Loading(0, 0);
    $("DIV#AddDialog").remove();
    $.ajax({
      type: "POST",
      cache: false,
      url: "/lv/Josn/EditSanemejs",
      success: function (data) {
        $("#DialogForm").html(data);
      },
    });
  };

  Loading(0, 1);
  $.post(URL + "/Pavadzime/Sanemejsedit", data, success);
}

function AddSan() {
  var data = $("Form#ChangeSanemejs").serialize();
  success = function (answ) {
    Loading(0, 0);
  };
  Loading(0, 1);
  $.post(URL + "/Pavadzime/SanemejsSave", data, success);
}

function HTMLFilter(selector, query) {
  name = $(".ui-dialog-content #Filter").val();
  query = $.trim(name);
  query = query.replace(/ /gi, "(.*?)");
  $(selector).each(function () {
    if ($(this).text().search(new RegExp(query, "i")) < 0) {
      $(this).hide().removeClass("visible");
    } else {
      $(this).show().addClass("visible");
    }
  });
}

