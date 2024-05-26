<?php
function create_thumb($source, $dest, $maxwidth, $maxheight, $padColor = false, $trimmed = false) {
    $size = @getimagesize($source);
    if ($size !== false) {
        @list($width, $height, $type) = @$size;

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = @imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_GIF:
                $src = @imagecreatefromgif($source);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($source);
                break;
            default:
                return false;
        }
    } else {
        return false;
    }

    if ($src === false) return false;

    $autoSizeWidth = false;
    $autoSizeHeight = false;
    if ($maxheight == 0) {
        $autoSizeHeight = true;
        $maxheight = $height;
    }
    if ($maxwidth == 0) {
        $autoSizeWidth = true;
        $maxwidth = $width;
    }

    $zoom_h = $maxheight / $height;
    $zoom_w = $maxwidth / $width;

    if ($autoSizeHeight) {
        $zoom_h = $zoom_w;
    } elseif ($autoSizeWidth) {
        $zoom_w = $zoom_h;
    }

    if ($trimmed) {
        $zoom = max($zoom_h, $zoom_w);
    } else {
        $zoom = min($zoom_h, $zoom_w);
    }

    if ($zoom > 1) {
        $zoom = 1;
    }

    $dst_h = $height * $zoom;
    $dst_w = $width * $zoom;

    if ($autoSizeWidth) {
        $maxwidth = $dst_w;
    }
    if ($autoSizeHeight) {
        $maxheight = $dst_h;
    }

    if (($padColor !== false) || ($trimmed)) {
        $def = imagecreatetruecolor($maxwidth, $maxheight);

        if ($padColor !== false) {
            $r = hexdec(substr($padColor, 0, 2));
            $g = hexdec(substr($padColor, 2, 2));
            $b = hexdec(substr($padColor, 4, 2));

            $background = imagecolorallocate($def, $r, $g, $b);
            imagefilledrectangle($def, 0, 0, $maxwidth, $maxheight, $background);
        } else {
            if (($type == IMAGETYPE_GIF) || ($type == IMAGETYPE_PNG)) {
                // bišku korekcijas, ja ir iespējams caurspīdīgs GIFs vai PNG
                $backgroundColor = C('image.transparent.background');

                $r = hexdec(substr($backgroundColor, 0, 2));
                $g = hexdec(substr($backgroundColor, 2, 2));
                $b = hexdec(substr($backgroundColor, 4, 2));

                $background = imagecolorallocate($def, $r, $g, $b);
                imagefilledrectangle($def, 0, 0, $maxwidth, $maxheight, $background);
            }
        }

        ImageCopyResampled($def, $src, round(($maxwidth / 2) - ($dst_w / 2)), round(($maxheight / 2) - ($dst_h / 2)), 0, 0, $dst_w, $dst_h, $width, $height);

        imagejpeg($def, $dest, 95);
        ImageDestroy($def);
    } else {
        $def = imagecreatetruecolor($dst_w, $dst_h);

        if (($type == IMAGETYPE_GIF) || ($type == IMAGETYPE_PNG)) {
            // bišku korekcijas, ja ir iespējams caurspīdīgs GIFs vai PNG
            $backgroundColor = C('image.transparent.background');

            $r = hexdec(substr($backgroundColor, 0, 2));
            $g = hexdec(substr($backgroundColor, 2, 2));
            $b = hexdec(substr($backgroundColor, 4, 2));

            $background = imagecolorallocate($def, $r, $g, $b);
            imagefilledrectangle($def, 0, 0, $dst_w, $dst_h, $background);
        }

        ImageCopyResampled($def, $src, 0, 0, 0, 0, $dst_w, $dst_h, $width, $height);

        imagejpeg($def, $dest, 95);
        ImageDestroy($def);
    }

    return true;
}

function convertToJPEG($source, $dest) {
    $size = @getimagesize($source);
    if ($size !== false) {
        @list($width, $height, $type) = @$size;

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = @imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_GIF:
                $src = @imagecreatefromgif($source);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($source);
                break;
            default:
                return false;
        }
    }
    if ($src === false) return false;

    $def = imagecreatetruecolor($width, $height);
    imagecopy($def, $src, 0, 0, 0, 0, $width, $height);

    imagejpeg($def, $dest, 95);
    ImageDestroy($def);
    return true;
}

function getImageWidth($src) {
    $size = @getimagesize($src);
    if ($size !== false) {
        @list($width, $height, $type) = @$size;
        return $width;
    }
}

function getImageHeight($src) {
    $size = @getimagesize($src);
    if ($size !== false) {
        @list($width, $height, $type) = @$size;
        return $height;
    }
}
