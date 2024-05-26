<?php

class Language {
    public static $Main = array(
        'Title' => 'Ievade',
        'Login' => 'Logins',
        'Password' => 'Parole',
        'Logon' => 'Ielogoties',
        'Loading' => 'Ielāde...',
        'Data' => 'Ieraksti',
        'Users' => 'Lietotāji',
        'Types' => 'Tipi',
        'Orders' => 'Pasutījumi',
        'Filters' => 'Filtri',
        'Rights' => 'Tiesības',
        'Logout' => 'Iziet',
        'msgConfirmDel' => 'Tiešām izdzēst?',
        'msgDelPass' => 'Ievadiet dzēšanās paroli',
        'WrongDelPass' => 'Nepareiza dzēšanās parole!',
        'Search' => 'Meklēt',
        'Tasks' => 'Uzdevumi',
        'Warehous' => 'Noliktava',

        'Today' => 'Šodien',
        'Yesterday' => 'Vakar',
        'Week' => 'Nedeļa',
        'LastMonth' => 'Mēnesis',
        'ThreeMonth' => '3 mēneši',
        'HalfYear' => 'Pusgads',
        'LastYear' => 'Gads',
        'AllPeriod' => 'Viss laiks',
        'Future' => 'Nākotne',
        'Tomorrow' => 'Rītdien',
        'FutureWeek' => 'Nākošnedeļ',
        'FutureMonth' => 'Nākošais mēnesis',
        'Last' => 'Pagātne',

        'Months' => ',Janvaris,Februaris,Marts,Aprīlis,Maijs,Jūnijs,Jūlijs,Augusts,Septembris,Oktobris,Novebris,Decembris',
        'Week' => 'Nedeļa',
        'CalendarWeek' => 'P,O,T,C,P,S,Sv',
        'SortByDate' => 'Pēc dat.',
        'SortByID' => 'Pēc ID',

    );

    public static $Suppliers = array(
        'DuplicateName' => 'Tāds piegadātājs jau eksistē!',
        'SetName' => 'Noradiet nosaukumu',
        'NotFound' => 'Piegādātājs nav atrasts',
    );

    public static $Users = array(
        'Title' => 'Lietotāji',
        'ID' => 'ID',
        'Login' => 'Logins',
        'Color' => 'Krāsa',
        'Name' => 'Vārds',
        'Phone' => 'Tālrunis',
        'Rights' => 'Tiesības',
        'Acrions' => 'Darbības',
        'Read' => 'Lasīt',
        'ReadWrite' => 'Lasīt/Rakstīt',
        'Admin' => 'Administrators',
        'Deleted' => 'Nodzēsts',
        'SuperUser' => 'Rakstīt/Labot',

        'OrderAdmin' => 'Pas.Ad',
        'R_bilde_Admin' => 'Pie.RB',
        'File_Admin' => 'Pie.F',
        'OneDay' => 'Re.Š',
        'noliktava' => 'No.A',
        'MultiChange' => 'Mu.L',
        'DelFile' => 'Dz.F',

        'DuplicateLogin' => 'Tāds lietotājvārds jau eksistē!',
        'WrongLoginPassword' => 'Nepareizs logins vai parole',
        'UserDisabled' => 'Lietotājs ir atslēgts',
        'SetLogin' => 'Noradiet lietotājvārdu',
        'SetPassword' => 'Noradiet paroli',
        'UserNotFound' => 'Lietotājs nav atrasts',
    );

    public static $Types = array(
        'Title' => 'Tipu kodi',
        'ID' => 'ID',
        'Code' => 'Kods',
        'Description' => 'Apraksts',
        'Actions' => 'Darbības',
        'SetCode' => 'Noradiet kodu',
        'TypeNotFound' => 'Kodu tips nav atrasts',
        'DuplicateEntry' => 'Tāds kods jau eksistē!'
    );

    public static $Orders = array(
        'Title' => 'Pasūtījumu kodi',
        'ID' => 'ID',
        'Code' => 'Kods',
        'Description' => 'Apraksts',
        'Actions' => 'Darbības',
        'SetCode' => 'Noradiet kodu',
        'OrderNotFound' => 'Pasūtījuma kods nav atrasts',
        'NoRights' => 'Nepietiek tiesību!',
        'DuplicateEntry' => 'Tāds kods jau eksistē!',
        'Close' => 'Aizvert'
    );

    public static $Data = array(
        'NewTpl' => 'Jauns šablons',
        'Title' => 'Ieraksti',

        'Total' => 'Kopā',

        'ID' => 'ID',
        'Date' => 'Dok.datums',
        'AddDate' => 'Piev.datums',
        'Operator' => ' Dok. aizp.',
        'Order' => 'Pasūtijums',
        'Type' => 'Tips',
        'Value' => 'Vērtiba',
        'Place' => 'Notikuma vieta',
        'Notes' => 'Piezīmes',
        'Actions' => 'Darbības',
        'Save' => 'Saglabāt',
        'Filter' => 'Atlasīt',
        'Close' => 'Aizvērt',
        'Print' => 'Drukāt',
        'Export' => 'Ex',
        'All' => 'Visi',
        'Reminder' => 'Atgadinājumi',

        'Today' => 'Šodien',
        'Week' => 'Nedeļa',
        'Month' => 'Mēnesis',
        'Year' => 'Gads',
        'AllTime' => 'Pa visu laiku',

        'IDDoc' => 'Dok. ID',
        'DateAdd' => 'Ierakstīšanas datums',
        'User' => 'Pievienoja',
        'OrderText' => 'Pasutījuma teksts',
        'TypeText' => 'Tipa teksts',
        'Sum' => 'Summa',
        'Hours' => 'Stundas',
        'PlaceTaken' => 'Notikumu vieta',
        'PlaceDone' => 'Nodošanas vieta',
        'Notes' => 'Piezīmes',
        'BookNotes' => 'Piezīmju teksts',
        'TotalPrice' => 'kop.',
        'PriceNote' => '.',
        'Reminder' => 'Atgad.',

        'NoFilter' => 'Nav Filtra',

        'SetIDDoc' => 'Noradiet dokumenta numuru',
        'WrongDateFormat' => 'Nepareizs datuma formats',
        'SetIDPerson' => 'Norādiet ievādītāju',
        'SetIDOrder' => 'Norādiet pasūtījumu',
        'SetIDType' => 'Norādiet tipu',
        'NoChanges' => 'Izmaiņu nav!',
        'NoDataToExport' => '<div align="center">Nav datu eksportēšanai!</div>',

        'Name' => 'Nosaukums',
        'Note' => 'Piezīmes',
        'MoreData' => 'Papild. info',

    );

    public static $Rights = array(
        'Title' => 'Tiesības',
        'Persons' => 'Ievadītāji',
        'Orders' => 'Pasutījumi',
        'Types' => 'Tipi',
        'Folders' => 'Faili',
        'Delete' => '[del]',
        'SetUser' => 'Lūdzu, izvēliet lietotāju!',

    );

    public static $Filters = array(

        'Name' => 'Nosaukums',
        'Date' => 'Datums',
        'Operator' => 'Ievadītājs',
        'User' => 'Lietotājs',
        'Order' => 'Pasūtijums',
        'Type' => 'Tips',
        'OrderText' => 'Pasutījuma teksts',
        'TypeText' => 'Tipa teksts',
        'Sum' => 'Summa',
        'Hours' => 'Stundas',
        'Value' => 'Vērtiba',
        'PlaceTaken' => 'Notikumu vieta',
        'PlaceDone' => 'Nodošanas vieta',
        'Note' => 'Piezīme',
        'BookNote' => 'Grāmatveža piezīme',
        'Place' => 'Notikuma vieta',
        'Actions' => 'Darbības',
        'Save' => 'Saglabāt',
        'Search' => 'Meklēt',

        'Today' => 'Šodien',
        'Yesterday' => 'Vakar',
        'Week' => 'Nedeļa',
        'LastMonth' => 'Pēdejais mēnesis',
        'ThreeMonth' => '3 mēneši',
        'HalfYear' => 'Pusgads',
        'LastYear' => 'Gads',
        'AllPeriod' => 'Pa visu laiku',
        'Future' => 'Nākotne',
        'Tomorrow' => 'Rītdien',
        'FutureWeek' => 'Nākošnedeļ',
        'FutureMonth' => 'Nākošais mēnesis',
        'Last' => 'Pagātne',

        'AddDate' => 'Pievien. datums',
        'DocDate' => 'Dok. datums',

        'SetName' => 'Norādiet nosaukumu!',
        'DataNotFound' => 'Filtrs nav atrasts',
        'NoUsers' => 'Nav lietotāju',
        'Saved' => 'Saglabāts'
    );
}
