<script type="text/javascript">
    function LoginAs(login) {
        Loading(0, 1);
        $.post(URL + "/Users/Logon", { Login: login }, function (answ) {
            Loading(0, 0);
            if (answ == 1) {
                window.location.replace(URL);
            } else {
                alert(answ);
            }
        });
    }
</script>
<div id="LoginCards" style="display:flex; flex-wrap:wrap; justify-content:center; gap:20px; padding:80px 20px 40px; max-width:900px; margin:0 auto;">
    [:Cards:]
</div>
