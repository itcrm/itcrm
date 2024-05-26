
 <script type="text/javascript">

    tinyMCE.init({
    [:Rights:]

        // Replace values for the template plugin
        template_replace_values : {
            LigumaNumurs : "[:IDDoc:]",
            Sanemejs : '[:Name:]',
            Kods : "[:Kods:]",
            Adrese : '[:Adrese:]',
            Auto : '[:Auto:]',
            RegistracijasNr : '[:Reg_nr:]',
            SasijasNr : '[:Sasija:]',
            RegAp : '[:Reg_ap:]',
            DienasNauda : "[:DayMony:]",
            DienasNaudaTexts : "[:DayMonyText:]",
            No : "[:From:]",
            SanemsanasVieta : '[:GetLocation:]',
            Uz : "[:To:]",
            NodosanasVieta : '[:ReturnLocation:]',
            Summa : "[:Summ:]",
            SummaTekst : "[:SummText:]",
            Drosibasnauda : "[:CautionMoney:]",
            DrosibasnaudaTeksts: "[:CautionMoneyText:]",
            Banka : '[:Banka:]',
            Konts : "[:Konts:]",
            Vertiba : "[:Vertiba:]",
            VertibaTeksts : "[:VertibaTeksts:]",
            Telefons : "[:Telefons:]",
            Datums : "[:Now:]",
            Dienas : "[:Days:]",
            Epasts : "[:Epasts:]",
            DienasTeksts : "[:DaysText:]"
                    }

    });

</script>

<form method="post" action="javascript:SavePielikums()"><input type="image" style="position: absolute; z-index: 200; border: medium none; width: 25px; left: 22px; height: 25px;" value="" name="image" title="Save" src="">
<input name="ID" ID="IDS" type="text" value="[:ID:]" style="display: none"/>
<textarea id="Pielikums" name="Pielikums" rows="15" cols="80" style="width: 100%">[:Pielikums:]</textarea>
</form>

<script type="text/javascript">
function SavePielikums(){
    var data = $('#Pielikums').val();
    var IDS =  $('#IDS').val();

    $.ajax({
    type: "POST",
    cache: false,
    url: "/lv/Noma/savePielikums",
    data: { Data: data, ID: IDS},
    success: function(data){
       alert("Pielikums saglabāts veiksmīgi!");
    }
});
}
</script>
