<div class="upload" align="center">
    <form ID= "Upload" action= "/faili/xml/rowattach.php?rowid=[:ID:]&order=[:Order:]" method="post" enctype="multipart/form-data">
        <label for="file">Pievienot failu / failus</label>
        <input type="file" name="files[]" multiple="true" id="MultiFiles" onchange="makeFileList();"/>
        <br />
        <br />
        <input type="submit" name="submit" value="Pievienot" />
    </form>
    <ul id="fileList" style="list-style: none outside none; text-align: left; padding-left: 0px;">
        <li class="ui-state-highlight">
            Neviens fails nav izvēlēts
        </li>
    </ul>
</div>
<script type="text/javascript">
    function makeFileList() {
        var input = document.getElementById("MultiFiles");
        var ul = document.getElementById("fileList");
        while (ul.hasChildNodes()) {
            ul.removeChild(ul.firstChild);
        }
        for (var i = 0; i < input.files.length; i++) {
            var li = document.createElement("li");
            li.setAttribute("class", "ui-state-highlight");
            li.innerHTML = input.files[i].name;
            ul.appendChild(li);
        }
        if (!ul.hasChildNodes()) {
            var li = document.createElement("li");
            li.setAttribute("class", "ui-state-highlight");
            li.innerHTML = 'Neviens fails nav izvēlēts';
            ul.appendChild(li);
        }
    }
</script>
