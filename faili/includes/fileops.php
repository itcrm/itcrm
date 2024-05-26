<?php

$_MOVE_ERROR_LOG = '';

// move a directory and all subdirectories and files (recursive)
// void dirmv( str 'source directory', str 'destination directory' [, bool 'overwrite existing files' [, str 'location within the directory (for recurse)']] )
function moveDirectory($source, $dest, $overwrite = false, $funcloc = NULL) {
    global $_MOVE_ERROR_LOG;
    if (is_null($funcloc)) {
        $funcloc = '/';
        $_MOVE_ERROR_LOG = "";
    }
    if (strpos($dest, $source) === 0) {
        $_MOVE_ERROR_LOG .= "Mēģinājums pārvietot direktoriju sevī.\n";
        return;
    }

    if (!is_dir($dest . $funcloc)) {
        mkdir($dest . $funcloc, 0777, true); // make subdirectory before subdirectory is copied
        chmod($dest . $funcloc, 0777);
    }

    set_time_limit(120);

    if ($handle = opendir($source . $funcloc)) { // if the folder exploration is sucsessful, continue
        while (false !== ($file = readdir($handle))) { // as long as storing the next file to $file is successful, continue
            set_time_limit(120);
            if ($file != '.' && $file != '..') {
                $path = $source . $funcloc . $file;
                $path2 = $dest . $funcloc . $file;

                if (is_file($path)) {
                    if (!is_file($path2)) {
                        if (!@rename($path, $path2)) {
                            $_MOVE_ERROR_LOG .= "Failu ({$path}) nevar pārvietot uz ({$path2})\n";
                        } else {
                            @chmod($path2, 0777);
                        }
                    } elseif ($overwrite) {
                        if (!@unlink($path2)) {
                            $_MOVE_ERROR_LOG .= "Iepriekšējo failu ({$path2}) nevar izdzēst pārrakstīšanai\n";
                        } elseif (!@rename($path, $path2)) {
                            $_MOVE_ERROR_LOG .= "Failu ({$path}) nevar pārvietot pārrakstot uz ({$path2})\n";
                        } else {
                            @chmod($path2, 0777);
                        }
                    } elseif ($overwrite) {
                        $_MOVE_ERROR_LOG .= "Fails ({$path}) jau eksistē un to nav atļauts pārrakstīt\n";
                        $info = pathinfo($file);
                        $fileName = $info['filename'];
                        $ext = $info['extension'];
                        $i = 1;
                        do {
                            $date = date('Y-m-d-H-m-i');
                            $newName = $fileName . '-' . $date . '-' . $i . '.' . $ext;
                            $i++;
                        } while (file_exists($dest . $funcloc . $newName));
                        $_MOVE_ERROR_LOG .= "Fails pārsaukts par {$newName}\n";
                        $path2 = $dest . $funcloc . $newName;
                        if (!@rename($path, $path2)) {
                            $_MOVE_ERROR_LOG .= "Failu ({$path}) nevar pārvietot uz ({$path2})\n";
                        } else {
                            @chmod($path2, 0777);
                        }
                    }
                } elseif (is_dir($path)) {
                    moveDirectory($source, $dest, $overwrite, $funcloc . $file . '/'); //recurse!
                    @rmdir($path);
                }
            }
        }
        closedir($handle);
    }
}

function copyFile($source, $dest, $overwrite = false) {
    global $_MOVE_ERROR_LOG;
    if (!is_file($dest)) {
        if (!@copy($source, $dest)) {
            $_MOVE_ERROR_LOG .= "Failu ({$source}) nevar nokopēt uz ({$dest})\n";
        } else {
            @chmod($dest, 0777);
        }
    } elseif ($overwrite) {
        if (!@unlink($dest)) {
            $_MOVE_ERROR_LOG .= "Iepriekšējo failu ({$dest}) nevar izdzēst pārrakstīšanai\n";
        } elseif (!@copy($source, $dest)) {
            $_MOVE_ERROR_LOG .= "Failu ({$source}) nevar nokopēt pārrakstot uz ({$dest})\n";
        } else {
            @chmod($dest, 0777);
        }
    } else {
        $_MOVE_ERROR_LOG .= "Fails ({$source}) jau eksistē un to nav atļauts pārrakstīt\n";
        $info = pathinfo($file);
        $fileName = $info['filename'];
        $ext = $info['extension'];
        $i = 1;
        do {
            $date = date('Y-m-d-H-m-i');
            $newName = $fileName . '-' . $date . '-' . $i . '.' . $ext;
            $i++;
        } while (file_exists($dest . $funcloc . $newName));
        $_MOVE_ERROR_LOG .= "Fails pārsaukts par {$newName}\n";
        $dest = $dest . $funcloc . $newName;
        if (!@copy($source, $dest)) {
            $_MOVE_ERROR_LOG .= "Failu ({$source}) nevar pārvietot uz ({$dest})\n";
        } else {
            @chmod($dest, 0777);
        }
    }
}

// move a directory and all subdirectories and files (recursive)
// void dirmv( str 'source directory', str 'destination directory' [, bool 'overwrite existing files' [, str 'location within the directory (for recurse)']] )
function copyDirectory($source, $dest, $overwrite = false, $funcloc = NULL) {
    global $_MOVE_ERROR_LOG;

    if (is_null($funcloc)) {
        $funcloc = '/';
        $_MOVE_ERROR_LOG = "";
    }

    if (strpos($dest, $source) === 0) {
        $_MOVE_ERROR_LOG .= "Mēģinājums kopēt direktoriju sevī.\n";
        return;
    }

    if (!is_dir($dest . $funcloc)) {
        mkdir($dest . $funcloc, 0777, true); // make subdirectory before subdirectory is copied
        chmod($dest . $funcloc, 0777);
    }

    set_time_limit(120);

    if ($handle = opendir($source . $funcloc)) { // if the folder exploration is sucsessful, continue
        while (false !== ($file = readdir($handle))) { // as long as storing the next file to $file is successful, continue
            set_time_limit(120);
            if ($file != '.' && $file != '..') {
                $path = $source . $funcloc . $file;
                $path2 = $dest . $funcloc . $file;

                if (is_file($path)) {
                    copyFile($path, $path2, $overwrite);
                } elseif (is_dir($path)) {
                    copyDirectory($source, $dest, $overwrite, $funcloc . $file . '/'); //recurse!
                }
            }
        }
        closedir($handle);
    }
}

function moveItem($src, $dest, $targetDir) {
    set_time_limit(120);

    global $_MOVE_ERROR_LOG;
    $_MOVE_ERROR_LOG = "";

    if (is_dir($src)) {
        // source ir direktorija, ar tā būs sarežģītāk
        @mkdir($targetDir, 0777, true);
        @chmod($targetDir, 0777);
        moveDirectory($src, $dest);
        @rmdir($src);
        return true;
    } else {
        // source ir (laikam) fails
        @mkdir($targetDir, 0777, true);
        @chmod($targetDir, 0777);

        if (file_exists($dest)) {
            $_MOVE_ERROR_LOG .= "Fails ({$src}) jau eksistē un to nav atļauts pārrakstīt\n";
            $info = pathinfo($src);
            $fileName = $info['filename'];
            $ext = $info['extension'];

            $info2 = pathinfo($dest);
            $path = $info2['dirname'];

            $i = 1;
            do {
                $date = date('Y-m-d-H-m-i');
                $newName = $path . '/' . $fileName . '-' . $date . '-' . $i . '.' . $ext;
                $i++;
            } while (file_exists($newName));
            $_MOVE_ERROR_LOG .= "Fails pārsaukts par {$newName}\n";
            $dest = $newName;
        }

        if (!@rename($src, $dest)) {
            $_MOVE_ERROR_LOG .= "Failu ({$src}) nevar pārvietot uz ({$dest})\n";
        }
        @chmod($dest, 0777);
    }
}

function copyItem($src, $dest, $targetDir) {
    set_time_limit(120);

    global $_MOVE_ERROR_LOG;
    $_MOVE_ERROR_LOG = "";

    if (is_dir($src)) {
        // source ir direktorija, ar tām būs sarežģītāk
        @mkdir($targetDir, 0777, true);
        @chmod($targetDir, 0777);
        copyDirectory($src, $dest);
        return true;
    } else {
        // source ir (laikam) fails
        @mkdir($targetDir, 0777, true);
        @chmod($targetDir, 0777);
        copyFile($src, $dest);
        return true;
    }
}
