<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>[[:Title:]]</title>

        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="stylesheet" type="text/css" href="/css/colorPicker.css" />
        <link rel="stylesheet" type="text/css" href="/css/redmond/jquery-ui-1.8.5.custom.css" />

        <script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>

        <script type="text/javascript" src="/js/jquery-ui-1.8.21.custom.min.js"></script>
        <script type="text/javascript" src="/js/jquery.colorPicker.js"></script>
        <script type="text/javascript" src="/js/phototagger.jquery.js" charset="utf-8"></script>
        <script type="text/javascript" src="/js/calendar.js"></script>

        <script type="text/javascript">
            [:JSVariables:]
        </script>
        <script type="text/javascript" src="/js/jquery.ui.datepicker.js"></script>

        <script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="/js/main.js"></script>
        <script type="text/javascript">
            function errorHandler(message, url, line) {
             var xmlHttpRequest;
             var bHookTheEventHandler = true;

                 if (window.XMLHttpRequest) {
                    xmlHttpRequest = new XMLHttpRequest();
                } else {
                    xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                }

    var xmlDoc= '&line='+line+'&url='+url+'&message='+message;

    xmlHttpRequest.open("post", "/lv/Josn/ErrorLogger", bHookTheEventHandler);
    xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xmlHttpRequest.send( xmlDoc );

                return true;
            }

            window.onerror = errorHandler;
        </script>
    </head>

    <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <div id="Loading"><img src="/images/loader.gif" align="left"/>&nbsp;&nbsp;[[:Loading:]]
        </div>
        [:Header:]

        [:Content:]

        [:Footer:]

    </body>
</html>
