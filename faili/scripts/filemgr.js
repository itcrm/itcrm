var _currentFileIdx = -1;
//var _clipboard = false;

var _clipboardEx = "";

function urlEncodeFilename(path) {
    var t1 = path.toString();
    var t = Url.encode(t1);
    return t.replace("+", "%2B");
}

function urlDecodeFilename(path) {
    var t = path.toString();
    t = t.replace("+", " ");
    return Url.decode(t);
}

function checkClipboard() {
    el = $("btn-paste");
    if (_clipboard) {
        if (el) {
            el.style.display = "";
        }
    } else {
        if (el) {
            el.style.display = "none";
        }
    }
}

function selectElement(element) {
    if (element) {
        element.addClass("multi");
        if (element.hasClass("image")) {
            element.removeClass("image");
            element.addClass("x-image");
        }
    }
}

function unselectElement(element) {
    if (element) {
        element.removeClass("multi");
        if (element.hasClass("x-image")) {
            element.removeClass("x-image");
            element.addClass("image");
        }
    }
}

function isSelected(element) {
    return element.hasClass("multi");
}

function getSelectedElements() {
    return $$(".multi");
}

function selectAll() {
    $$(".direntry").each(function (item, index) {
        subitems = item.getElements("a");
        subitem = subitems[0];
        selectElement(subitem);
    });

    if (getSelectedElements().length > 0) {
        var el = $("btn-delete");
        if (el) {
            el.style.display = "";
        }
        el = $("btn-rename");
        if (el) {
            el.style.display = "none";
        }
        el = $("btn-copy");
        if (el) {
            el.style.display = "";
        }
        el = $("btn-cut");
        if (el) {
            el.style.display = "";
        }
    }
    cancelRename();
    cancelMkDir();
    checkClipboard();
}

function unselectAll() {
    getSelectedElements().each(function (item, index) {
        unselectElement(item);
    });

    if (_currentFileIdx >= 0) {
        var el = $("btn-delete");
        if (el) {
            el.style.display = "";
        }
        el = $("btn-rename");
        if (el) {
            if (getSelectedElements().length == 0) {
                el.style.display = "";
            } else {
                el.style.display = "none";
            }
        }
        el = $("btn-copy");
        if (el) {
            el.style.display = "";
        }
        el = $("btn-cut");
        if (el) {
            el.style.display = "";
        }
    } else {
        var el = $("btn-delete");
        if (el) {
            el.style.display = "none";
        }
        el = $("btn-rename");
        if (el) {
            el.style.display = "none";
        }
        el = $("btn-copy");
        if (el) {
            el.style.display = "none";
        }
        el = $("btn-cut");
        if (el) {
            el.style.display = "none";
        }
    }

    cancelRename();
    cancelMkDir();
    checkClipboard();
}

function setFile(elementIdx, isShift) {
    var oldElement = $("file" + _currentFileIdx);
    if (oldElement) oldElement.removeClass("active");

    var element = $("file" + elementIdx);
    element.addClass("active");
    var _oldFileIdx = _currentFileIdx;
    _currentFileIdx = elementIdx;

    // multi-selection suports. Pašķidrs pagaidām, bet strādā.
    var element = $("link" + elementIdx);
    if (isShift) {
        var isFirst = false;
        if (getSelectedElements().length == 0) {
            var oldElement = $("link" + _oldFileIdx);
            selectElement(oldElement);
            isFirst = true;
        }
        if (isSelected(element)) {
            if (!isFirst) {
                unselectElement(element);
            }
        } else {
            selectElement(element);
        }
    } else {
        unselectAll();
    }

    //	if ($('commands-rename').style.display==''){
    //		//renameFile();
    //	}
    //	if ($('commands-delete').style.display==''){
    //deleteFile();
    //	}
    if (_currentFileIdx >= 0) {
        var el = $("btn-delete");
        if (el) {
            el.style.display = "";
        }
        el = $("btn-rename");
        if (el) {
            if (getSelectedElements().length == 0) {
                el.style.display = "";
            } else {
                el.style.display = "none";
            }
        }
        el = $("btn-copy");
        if (el) {
            el.style.display = "";
        }
        el = $("btn-cut");
        if (el) {
            el.style.display = "";
        }
    }
    cancelRename();
    cancelMkDir();
    checkClipboard();
}

function copyFile() {
    var multiElements = getSelectedElements();
    if (multiElements.length > 0) {
        // multi-select mode
        var filestring = "paths=";
        var clbEx = "";
        multiElements.each(function (item, index) {
            var clipboard = item.rel;
            filestring += urlEncodeFilename(clipboard) + "\n";
            clbEx += clipboard + "\n";
        });

        cancelRename();
        cancelMkDir();

        lockPage();
        new Request({
            url: siteroot + "xml/filemgr.php?cmd=clipop&op=copy-multi",
            method: "post",
            data: filestring,
            onSuccess: function (t) {
                if (t != "") {
                    alert(t);
                } else {
                    _clipboard = true;
                    _clipboardEx = clbEx;
                    alert("Kopējamie faili:\n" + clbEx);
                    checkClipboard();
                }
                unlockPage();
            },
            onFailure: function (t) {
                //alert('Neveiksme kopējot failus:\n'+clbEx);
                unlockPage();
            },
        }).send();
    } else {
        // single-select mode
        var link = $("link" + _currentFileIdx);
        var clipboard = link.rel;
        //_clipboardOp = 'cut';
        cancelRename();
        cancelMkDir();

        if (clipboard) {
            lockPage();
            new Request({
                url:
                    siteroot +
                    "xml/filemgr.php?cmd=clipop&op=copy&path=" +
                    urlEncodeFilename(clipboard),
                method: "get",
                onSuccess: function (t) {
                    if (t != "") {
                        alert(t);
                    } else {
                        _clipboard = true;
                        _clipboardEx = "";
                        alert("Kopējamais fails:\n" + clipboard);
                        checkClipboard();
                    }
                    unlockPage();
                },
                onFailure: function (t) {
                    unlockPage();
                },
            }).send();
        }
    }
}

function cutFile() {
    var multiElements = getSelectedElements();
    if (multiElements.length > 0) {
        // multi-select mode
        var filestring = "paths=";
        var clbEx = "";
        multiElements.each(function (item, index) {
            var clipboard = item.rel;
            //alert(clipboard);
            filestring += urlEncodeFilename(clipboard) + "\n";
            clbEx += clipboard + "\n";
        });
        //		alert(filestring);

        cancelRename();
        cancelMkDir();

        lockPage();
        new Request({
            url: siteroot + "xml/filemgr.php?cmd=clipop&op=cut-multi",
            method: "post",
            data: filestring,
            onSuccess: function (t) {
                if (t != "") {
                    alert(t);
                } else {
                    _clipboard = true;
                    _clipboardEx = clbEx;
                    alert("Izgriežamie faili:\n" + clbEx);
                    checkClipboard();
                }
                unlockPage();
            },
            onFailure: function (t) {
                unlockPage();
            },
        }).send();
    } else {
        var link = $("link" + _currentFileIdx);
        var clipboard = link.rel;
        //_clipboardOp = 'cut';
        cancelRename();
        cancelMkDir();

        if (clipboard) {
            lockPage();
            new Request({
                url:
                    siteroot +
                    "xml/filemgr.php?cmd=clipop&op=cut&path=" +
                    urlEncodeFilename(clipboard),
                method: "get",
                onSuccess: function (t) {
                    if (t != "") {
                        alert(t);
                    } else {
                        _clipboard = true;
                        _clipboardEx = "";
                        alert("Izgriežamais fails:\n" + clipboard);
                        checkClipboard();
                    }
                    unlockPage();
                },
                onFailure: function (t) {
                    unlockPage();
                },
            }).send();
        }
    }
}

function pasteFile() {
    //lockPage();
    //	var link = $('link'+_currentFileIdx);

    var confirmed = true;
    if (_clipboardEx) {
        confirmed = confirm("Ievietot vairākus failus? \n\n" + _clipboardEx);
    }

    if (_clipboard && confirmed) {
        lockPage();
        new Request({
            url:
                siteroot +
                "xml/filemgr.php?cmd=clipop&op=paste&target=" +
                urlEncodeFilename(currentPath),
            method: "get",
            onSuccess: function (t) {
                if (t != "") {
                    // error handling
                    if (t == "E_CHARS") {
                        alert(
                            'Neatļauti simboli nosaukumā (/,\\,*,:,?,>,<,|,")'
                        );
                    } else if (t == "E_EXISTS") {
                        alert("Tāds fails vai direktorija jau eksistē");
                    } else {
                        alert(t);
                    }
                } else {
                    _clipboard = false;
                    _clipboardOp = "";
                    _clipboardEx = "";
                    checkClipboard();
                    reloadList();
                }

                unlockPage();
            },
            onFailure: function (t) {
                //cancelRename();
                unlockPage();
            },
        }).send();
    }
}

function openCurrentFile(asImage) {
    var link = $("link" + _currentFileIdx);
    if (link) {
        var path = link.href;
        if (asImage) {
            var size = window.getSize();
            path = link.rel;

            var images = new Array();
            var imageIdx = 0;
            $$("a.image")
                .filter(function (el) {
                    return true;
                })
                .each(function (el) {
                    if (path == el.rel) imageIdx = images.length;
                    var idx = el.id.replace("link", "");
                    images[images.length] = [
                        siteroot +
                            "xml/filemgr.php?cmd=getthumb&path=" +
                            urlEncodeFilename(el.rel) +
                            "&width=" +
                            (size.x * 95) / 100 +
                            "&height=" +
                            (size.y * 95) / 100,
                        "",
                        idx,
                    ];
                });

            //Slimbox.open(siteroot+'xml/filemgr.php?cmd=getthumb&path='+Url.encode(path)+'&width='+(size.x * 95 / 100), '');
            Slimbox.open(images, imageIdx, {
                loop: false, // Allows to navigate between first and last images
                overlayOpacity: 0.8, // 1 is opaque, 0 is completely transparent (change the color in the CSS file)
                overlayFadeDuration: 0, // Duration of the overlay fade-in and fade-out animations (in milliseconds)
                resizeDuration: 0, // Duration of each of the box resize animations (in milliseconds)
                resizeTransition: false, // false uses the mootools default transition
                initialWidth: 250, // Initial width of the box (in pixels)
                initialHeight: 250, // Initial height of the box (in pixels)
                imageFadeDuration: 0, // Duration of the image fade-in animation (in milliseconds)
                captionAnimationDuration: 0, // Duration of the caption animation (in milliseconds)
                counterText: "Attēls {x} no {y}", // Translate or change as you wish, or set it to false to disable counter text for image groups
                closeKeys: [27, 88, 67], // Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
                previousKeys: [37, 80], // Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
                nextKeys: [39, 78], // Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
                allowEdit:
                    typeof window._allowSlimboxEdits !== "undefined"
                        ? window._allowSlimboxEdits
                        : false,
            });
        } else {
            //path = link.rel;
            //alert(path);
            window.location = path;
        }
    }
}

function openFile(elementIdx, asImage) {
    setFile(elementIdx);
    openCurrentFile(asImage);
}

function renameFile() {
    var link = $("link" + _currentFileIdx);

    if (link) {
        var path = link.rel;

        var pathparts = path.split("/");
        if (pathparts.length >= 1) {
            var lastpart = pathparts[pathparts.length - 1];
        }
        //$('f_filename').value = urlDecodeFilename(lastpart);
        $("f_filename").value = lastpart;
        $("commands-rename").style.display = "";
    }
}

function cancelRename() {
    if ($("commands-rename")) {
        $("commands-rename").style.display = "none";
    }
    if ($("f_filename")) {
        $("f_filename").value = "";
    }
}

function reloadList() {
    window.location.reload();
}

function submitRename() {
    //lockPage();
    var link = $("link" + _currentFileIdx);

    if (link) {
        var newFilename = $("f_filename").value.trim();

        if (newFilename) {
            var oldName = link.rel;

            lockPage();
            new Request({
                url:
                    siteroot +
                    "xml/filemgr.php?cmd=rename&path=" +
                    urlEncodeFilename(oldName) +
                    "&newname=" +
                    urlEncodeFilename(newFilename),
                method: "get",
                onSuccess: function (t) {
                    if (t != "") {
                        // error handling
                        if (t == "E_CHARS") {
                            alert(
                                'Neatļauti simboli nosaukumā (/,\\,*,:,?,>,<,|,")'
                            );
                        } else if (t == "E_EXISTS") {
                            alert("Tāds fails vai direktorija jau eksistē");
                        } else {
                            alert(t);
                        }
                    } else {
                        var link = $("link" + _currentFileIdx);
                        link.innerHTML = newFilename;

                        var oldPath = link.rel;
                        var pathparts = oldPath.split("/");
                        if (pathparts.length > 1) {
                            //pathparts[pathparts.length-1] = '';
                            //pathparts = pathparts.splice(-1,1);
                            pathparts.splice(-1, 1);
                            var parts = pathparts.join("/") + "/" + newFilename;
                            link.rel = parts;
                        } else {
                            link.rel = newFilename;
                        }
                        cancelRename();
                    }

                    unlockPage();
                },
                onFailure: function (t) {
                    cancelRename();
                    unlockPage();
                },
            }).send();
        }
    }
}

function mkDir() {
    $("f_dirname").value = "";
    $("commands-mkdir").style.display = "";
}

function cancelMkDir() {
    if ($("commands-mkdir")) {
        $("commands-mkdir").style.display = "none";
    }
    if ($("f_dirname")) {
        $("f_dirname").value = "";
    }
}

function submitMkDir() {
    var newFilename = $("f_dirname").value.trim();

    if (newFilename) {
        lockPage();
        new Request({
            url:
                siteroot +
                "xml/filemgr.php?cmd=mkdir&path=" +
                urlEncodeFilename(currentPath) +
                "&newname=" +
                urlEncodeFilename(newFilename),
            method: "get",
            onSuccess: function (t) {
                if (t != "") {
                    // error handling
                    if (t == "E_CHARS") {
                        alert(
                            'Neatļauti simboli nosaukumā (/,\\,*,:,?,>,<,|,")'
                        );
                    } else if (t == "E_EXISTS") {
                        alert("Tāds fails vai direktorija jau eksistē");
                    } else {
                        alert(t);
                    }
                } else {
                    cancelMkDir();
                    reloadList();
                }

                unlockPage();
            },
            onFailure: function (t) {
                cancelMkDir();
                unlockPage();
            },
        }).send();
    }
}

function deleteFile() {
    var multiElements = getSelectedElements();
    if (multiElements.length > 0) {
        // multi-select mode
        var filestring = "paths=";
        var clbEx = "";
        multiElements.each(function (item, index) {
            var path = item.rel;
            //alert(clipboard);
            filestring += urlEncodeFilename(path) + "\n";
            clbEx += path + "\n";
        });
        //		alert(filestring);

        cancelRename();
        cancelMkDir();

        lockPage();
        if (confirm("Tiešām dzēst vairākus failus? \n\n" + clbEx)) {
            new Request({
                url: siteroot + "xml/filemgr.php?cmd=delete-multi",
                method: "post",
                data: filestring,
                onSuccess: function (t) {
                    reloadList();
                    unlockPage();
                },
                onFailure: function (t) {
                    unlockPage();
                },
            }).send();
        }
    } else {
        var link = $("link" + _currentFileIdx);

        if (link) {
            var path = link.rel;

            if (
                confirm(
                    "Tiešām dzēst? Fails tiks pārvietots uz atkritni un vēl būs atjaunojams"
                )
            ) {
                lockPage();
                new Request({
                    url: siteroot + "xml/filemgr.php?cmd=delete",
                    data: "path=" + urlEncodeFilename(path),
                    method: "post",
                    onSuccess: function (t) {
                        reloadList();
                        //unlockPage();
                    },
                    onFailure: function (t) {
                        unlockPage();
                    },
                }).send();
            }
        }
    }
}

function uploadFile() {
    $("f_uploadfile").value = "";
    $("commands-upload").style.display = "";
}

function cancelUpload() {
    if ($("commands-upload")) {
        $("commands-upload").style.display = "none";
    }
    if ($("f_uploadfile")) {
        $("f_uploadfile").value = "";
    }
}
