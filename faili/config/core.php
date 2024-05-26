<?php

/***************************************************************

          WEBStorm CMS Engine Configuration file
          A part of WEBStorm Content Management System
          (C) 2003-2005 StorM/LV Creative Group

 ***************************************************************/

// Site Database Backend configuration

// backend DB server address
$_IDC_CONFIG['dbServer'] = '127.0.0.1';
// backend DB server login username
$_IDC_CONFIG['dbUser'] = 'data-prod';
// backend DB server login password
$_IDC_CONFIG['dbPass'] = 'gt09rcL21';
// backend DB name
$_IDC_CONFIG['dbName'] = '090802a1';

// Kodola vispārīgā konfigurācija
$_IDC_CONFIG['base-url'] = '/faili/';

//*******************************************************************
// Site URL handler configuration

// parameter source read order (Get;Post;Cookie)
// (next source in order will overwrite values set by previous sources):
$_IDC_CONFIG['param_order'] = 'GPC';

///////////////////////// Moduļu konfigurācijas ///////////////////////////

$_IDC_CONFIG['filelist.root'] = '/var/webserver/share/rch/R_am';
$_IDC_CONFIG['sorter.root'] = '/var/webserver/share/rch/scan';
$_IDC_CONFIG['filelist.thumbcache.root'] = 'thumbcache';
$_IDC_CONFIG['rowimage.directory'] = 'rindubildes';
$_IDC_CONFIG['image.transparent.background'] = 'FFFFFF';
$_IDC_CONFIG['trash.directory'] = '-atkritne';
