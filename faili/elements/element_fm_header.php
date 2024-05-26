<?php
function element_fm_header() {
    global $_core;

    $currentPath = (string) get_variable('currentPath');

    $pathItems = explode('/', $currentPath);
    $fullPath = '';
    $formattedPath = '<a href="' . url() . '">Faili</a>';
    foreach ($pathItems as $idx => $pathItem) {
        if ($pathItem) {
            $fullPath .= '/' . $pathItem;
            $formattedPath .= ' &raquo; <a href="' . url(trim($fullPath, '/')) . '">' . H(trim($pathItem, '/')) . '</a>';
        }
    }

    $out = '<div id="header"><table style="border:0;width:100%" width="100%">
        <tr>';

    $out .= '<td align="left"><h3>' . $formattedPath . '</h3></td>';
    $project = get_variable('currentProject');
    $out .= '<td align="right">';
    $out .= 'Lietotājs: ' . $_core->user->first_name . ' (' . $_core->user->id . ')';
    if ($project !== false) {
        $out .= '<br/>Pasūtījums: ' . H($project->Code) . ' (' . H($project->Description) . ')';
    }
    $out .= '</td>';

    $out .= '</tr>';

    $out .= '</table>';

    $out .= '<div id="commands">{{E:element_fm_commands}}</div>';

    $out .= '</div>';

    $out .= '<script type="text/javascript">var currentPath = \'' . str_replace('\'', '\\\'', $currentPath) . '\'</script>';

    return $out;
}
