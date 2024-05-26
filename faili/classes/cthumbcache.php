<?php
class CThumbCache extends CPersistent {
    public $path;
    public $width;
    public $height;
    public $filesize;
    public $filedate;

    function __construct() {
        parent::__construct('thumbcache', 'thumb_id', '');
    }

    function createFromPath() {
        global $_IDC_ENGINE_ROOT;
        if ((($this->width > 0) && ($this->width < 128)) || (($this->height > 0) && ($this->height < 128))) {
            $pad = C('image.transparent.background');
        } else {
            $pad = false;
        }
        $result = create_thumb($this->path, $_IDC_ENGINE_ROOT . C('filelist.thumbcache.root') . '/' . $this->id . '.jpg', $this->width, $this->height, $pad);
        if ($result) {
            $this->filesize = filesize($this->path);
            $this->filedate = filemtime($this->path);
        }
        return $result;
    }

    function isValid() {
        global $_IDC_ENGINE_ROOT;
        if (!file_exists($_IDC_ENGINE_ROOT . C('filelist.thumbcache.root') . '/' . $this->id . '.jpg')) return false;

        $currentFilesize = filesize($this->path);
        $currentFiledate = filemtime($this->path);
        return $currentFiledate == $this->filedate && $currentFilesize == $this->filesize;
    }

    private static function _getThumb($path, $width, $height) {
        $where = "WHERE path='" . sql_escape($path) . "' AND width={$width} AND height={$height}";
        $list = new CList('CThumbCache');
        $list->loadByCustomWhere($where);
        if (count($list->objects) > 0) {
            return $list->objects[$list->orderedIndex[0]];
        } else {
            return false;
        }
    }

    static function getThumb($path, $width, $height, $force = false) {
        $instance = CThumbCache::_getThumb($path, $width, $height);
        if ($instance === false) {
            // nekas nav atradies, jātaisa jauns
            $instance = new CThumbCache();
            $instance->path = $path;
            $instance->width = $width;
            $instance->height = $height;
            $instance->save();
            if (!$instance->createFromPath()) {
                $instance->delete();
                return false;
            }
            $instance->save();
            return $instance;
        } else {
            // ir atrasts, pārbaudam arī vai ir derīgs
            if ((!$instance->isValid()) || ($force)) {
                if ($instance->createFromPath()) {
                    $instance->save();
                    return $instance;
                } else {
                    return false;
                }
            } else {
                return $instance;
            }
        }
    }

    function onCreate() {
    }

    function onUpdate() {
    }

    function onDelete() {
    }
}
