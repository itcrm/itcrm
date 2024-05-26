String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, "");
};

String.prototype.ltrim = function () {
    return this.replace(/^\s+/, "");
};

String.prototype.rtrim = function () {
    return this.replace(/\s+$/, "");
};

String.prototype.escapeHTML = function () {
    var div = document.createElement("div");
    var text = document.createTextNode(this);
    div.appendChild(text);
    return div.innerHTML;
};

String.prototype.unescapeHTML = function () {
    var div = document.createElement("div");
    div.innerHTML = this.stripTags();
    return div.childNodes[0]
        ? div.childNodes.length > 1
            ? $A(div.childNodes).inject("", function (memo, node) {
                  return memo + node.nodeValue;
              })
            : div.childNodes[0].nodeValue
        : "";
};

function curTop(obj) {
    var result = 0;
    while (obj) {
        result += obj.offsetTop;
        obj = obj.offsetParent;
    }
    return result;
}

function curLeft(obj) {
    var result = 0;
    while (obj) {
        result += obj.offsetLeft;
        obj = obj.offsetParent;
    }
    return result;
}

var _page_locked = false;

function initLoadingOverlay() {
    var divPageLock = new Element("div", {
        id: "pagelock",
        styles: { display: "none" },
    });
    divPageLock.innerHTML =
        '<img id="pagelock-img" style="position: relative; top: -16px; left: -16px;" src="' +
        siteroot +
        'template/main/img/loading_big.gif" alt="Ielāde.." />';

    $(document.body).grab(divPageLock, "bottom");
}

function lockPage() {
    if (_page_locked) return true;
    var body = document.body;

    var top = curTop(body);
    var left = curLeft(body);
    var width = body.offsetWidth;
    var height = body.offsetHeight;

    if ($("pagelock")) {
        var pagelock = $("pagelock");
        if (pagelock) {
            pagelock.style.cssText =
                "background: url(" +
                siteroot +
                "template/main/img/loading_overlay.gif); position: absolute; top: " +
                top +
                "px; left: " +
                left +
                "px; width: " +
                width +
                "px; height: " +
                height +
                "px;";
        }

        var img = $("pagelock-img");
        if (img) {
            img.style.cssText =
                "position: relative; top: " +
                parseInt(height / 2 - 16) +
                "px; left: " +
                parseInt(width / 2 - 16) +
                "px;";
        }
    }
}

function unlockPage() {
    if ($("pagelock")) {
        $("pagelock").style.display = "none";
    }
}
