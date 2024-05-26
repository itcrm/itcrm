<div id="PavadzimesD[:IDSupplier:]" class="Pavadzime" >
    <h2>PREČU PAVADZĪME- RĒĶINS NR. A1-[:IDDoc:] </h2>
<table border="0">
<tr>
<td width="560px">[:Date:]</td>
<td></td>
</tr>
<tr>
<td><b></>Nosūtītājs:</b></td>
<td>SIA"Auto Nr.1"</td>
</tr>

<tr>
<td>Nodokļu maksātāja Kods:</td>
<td>LV40003597704</td>
</tr>

<tr>
<td>Juridiskā adrese:</td>
<td>Rīga, Brīvības iela 197b</td>
</tr>

<tr>
<td>Kredītiestādes nosaukums</td>
<td>AS Swedbank</td>
</tr>

<tr>
<td>Swift kods:</td>
<td>HABALV22</td>
</tr>

<tr>
<td>Norēķinu konta Nr.:</td>
<td>LV84HABA0551004925038</td>
</tr>

<tr>
<td>Izsniegšanas vieta</td>
<td>[:PlaceTaken:]</td>
</tr>

</table>

</b><hr></b>

<table border="0">

<tr>
<td width="560px"><b></>Saņēmējs:</b><span onclick="OpenForm('NewSanemejs','DialogForm','scrollDiv','Jauns','430',0,1)" class="addbutton">&nbsp;</span>
                                     <span onclick="OpenForm('EditSanemejs','DialogForm','scrollDiv','Labot','1500',0)" class="editbutton">&nbsp</span></td>
<td>
<input type="text" class="hide" value="[:SaveID:]" ID="Saveid" />
<input type="text" class="hide" value="[:ID:]" ID="pavadid" />
<input type="text" class="hide" value="[:SanemejsID:]" ID="SanemejsID" />
<input size="35" value='' ID="Sanemejs" />
</td>
</tr>

<tr>
<td>Nodokļu maksātāja Kods:</td>
<td id="MaksKods"></td>
</tr>

<tr>
<td>Juridiskā adrese:</td>
<td id="Adrese"></td>
</tr>

<tr>
<td>Kredītiestādes nosaukums</td>
<td id="Banka"></td>
</tr>

<tr>
<td>Norēķinu konta Nr.:</td>
<td id="KontaNr"></td>
</tr>

<tr>
<td>Saņemšanas vieta:</td>
<td>[:PlaceDone:]</td>
</tr>

</table>

<b><hr></b>

<table border="0">

<tr>
<td width="560px">Speciālas piez.</td>
<td>[:Note:]</td>
</tr>

<tr>
<td>Samaksas veids un kārtība:</td>
<td><input value = "[:Samaksa:]" size = "35" type="text" class="SamKart" /></td>
</tr>

<tr>
<td>Pakalpojuma sniegšanas laiks:</td>
<td><input class="izsniedz" size = "35" type="text" name="IzsnDat" value="[:Izsniedza:]"  /></td> <!--[:IzDate:]-->
</tr>
</tr>
</table>
<br>
<span  onclick="addprece()" class="addbutton">&nbsp;</span>
<table ID="Preces">

<tr>
<th>Preču nosaukums:</td>
<th>Artikuls</td>
<th>Daudz</td>
<th>Mērv</td>
<th>Cena(Eur)</td>
<th>Summa(Eur)</td>
</tr>

[:tabula:]

<tr class="bordersolid">
<td COLSPAN="5">Kopā izsniegts</td>
<td ID="Kop"></td>
</tr>
<tr>
<td COLSPAN=5>Atlaide <input onblur="summ(this.id)" size = "5" type="text" class="atlaidenr" id=1  value="[:Atlaide:]" />%</td>
<td class="bordersolid" id="atlaide">0.00</td>
</tr>
<tr>
<td COLSPAN="5">Summa pirms nodokļiem</td>
<td class="bordersolid" ID="sumaatlaide"></td>
</tr>
<tr>
<td COLSPAN="5">Pievienotās vērtības nodoklis 0%</td>
<td class="bordersolid" ID ="PVN"></td>
</tr>
<tr>
<td COLSPAN="5">Pavisam samaksai</td>
<td class="bordersolid" ID="PavisamSamaksai"></td>
</tr>

</table>
   <br>
   <br>
</div>

<script type="text/javascript" src="/js/jquery-ui-1.8.5.custom.min.js"></script>

 <script type="text/javascript">
     $(document).ready(function(){
    replaceentry($("input#SanemejsID").val());
    var stop =[:ierakstusk:];
    var i=1;
    for (i = 1; i <= stop; i++) {
        summ(i);
    };

    });

$("#Sanemejs").autocomplete({
    source: "/lv/Data/AutocompliteJosn",
    select: function( event, ui){
        AutoUiReplace(ui.item.ID);
    },
            minLength: 2,
});

function replaceentry(){
    var val = $("input#SanemejsID").val();
    if (!val) {
    }else{
        var data = 'ID=' + val;
        success = function(answ){
            Loading(0, 0);

                text  = answ.split('|');
                $("input#Sanemejs").val(text[1]);
                $("td#MaksKods").text(text[2]);
                $("td#Adrese").text(text[3]);
                $("td#Banka").text(text[4]);
                $("td#KontaNr").text(text[5]);
                $("td#Veids").text(text[6]);
        };

        Loading(0, 1);
        $.post(URL + '/Pavadzime/Sanemejs', data, success);
    }
};

function AutoUiReplace(val){
    if (!val) {
    }else{
        val = val.replace("''",'"');
        val = val.replace("''",'"');

        var data = 'ID=' + val;
        success = function(answ){
            Loading(0, 0);

                text  = answ.split('|');
                $("input#SanemejsID").val(text[0]);
                $("input#Sanemejs").val(text[1]);
                $("td#MaksKods").text(text[2]);
                $("td#Adrese").text(text[3]);
                $("td#Banka").text(text[4]);
                $("td#KontaNr").text(text[5]);
                $("td#Veids").text(text[6]);
        };

        Loading(0, 1);
        $.post(URL + '/Pavadzime/AutoUiReplace', data, success);
    }
};

function addprece(){
    var id = $('#Preces tbody>tr:last.bordersolidadd').attr("ID");
    id++;

$('#Preces tr:last.bordersolidadd').after("<tr class='bordersolidadd' id='"+id+"' name='0'> <td width='40%'> <input type='text' class='Precu_nosaukums' size='106'></td> <td width='20%'> <input type='text' class='Artikuls' size='50'></td> <td width='5%'> <input type='text' id='"+id+"' class='Daudz' size='16' onblur='summ(this.id)'></td> <td width='5%'> <input type='text' class='Merv' size='15'></td> <td width='10%'> <input type='text' id='"+id+"' class='Cena' size='15'  onblur='summ(this.id)'><a style='float:right' href='javascript:Delete("+id+",0);' class='extra delete'></a></td></td> <td width='10%' id='"+id+"' class='Summa'> </td></tr>")

}

function Delete(ID,entry){
    var ID,entry;

    if (entry == 0){
        $('#Preces tr#'+ID+'.bordersolidadd').remove()
    }else{
            var data = 'id='+entry;
    success = function(answ){
        Loading(0, 0);
        if (answ == 1){$('#Preces tr#'+ID+'.bordersolidadd').remove();    var stop =[:ierakstusk:];
    var i=1;
    for (i = 1; i <= stop; i++) {
        summ(i);
    };}

    };

    Loading(0, 1);
    $.post(URL + '/Pavadzime/DeleteEntry', data, success);

    }
}

function r4(n) {
  ans = n * 10000;
  ans = Math.round(ans /10) + "";
  while (ans.length < 4) {ans = "0" + ans};
  len = ans.length;
  ans = ans.substring(0,len-3) + "." + ans.substring(len-3,len);
  return ans;
} ;

function r2(n) {
  ans = n * 1000;
  ans = Math.round(ans /10) + "";
  while (ans.length < 3) {ans = "0" + ans};
  len = ans.length;
  ans = ans.substring(0,len-2) + "." + ans.substring(len-2,len);
  return ans;
} ;

</script>
