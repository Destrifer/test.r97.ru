<?php

namespace program\core;

/**
 * v. 1.01
 * 2020-06-16
 */

class File
{

    public $path = '';
    public $folder = '';
    public $name = '';
    public $ext = '';
    public $size = 0;
    public $mime = 0;
    public $error = 0;
    public $log = [];
    protected $tmpName = '';
    protected $deletedFlag = false;
    protected $errors = [
        0 => 'Ошибок нет.',
        1 => 'Файл не найден.',
        2 => 'Не удалось загрузить файл.',
        3 => 'Не удалось изменить адрес файла.',
        4 => 'Не удалось создать директорию.'
    ];

    public function __construct($path = '', $filesName = '', $index = -1)
    {
        if ($filesName === '') {
            $this->parsePath($path);
            return;
        }
        if (($index < 0 && empty($_FILES[$filesName]['name'])) || ($index >= 0 && empty($_FILES[$filesName]['name'][$index]))) {
            return;
        }
        if ($index >= 0) {
            $this->tmpName = $_FILES[$filesName]['tmp_name'][$index];
            $this->size = $_FILES[$filesName]['size'][$index];
            $this->mime = $_FILES[$filesName]['type'][$index];
            $this->error = $_FILES[$filesName]['error'][$index];
            $path = $_FILES[$filesName]['name'][$index];
        } else {
            $this->tmpName = $_FILES[$filesName]['tmp_name'];
            $this->size = $_FILES[$filesName]['size'];
            $this->mime = $_FILES[$filesName]['type'];
            $this->error = $_FILES[$filesName]['error'];
            $path = $_FILES[$filesName]['name'];
        }
        $this->parsePath($path);
    }

    protected function parsePath($path)
    {
        $parts = pathinfo($path);
        $this->ext = $parts['extension'];
        $this->name = $parts['filename'];
        $this->folder = trim($parts['dirname'], '/ ');
        $this->path = ($this->folder) ? '/' . ltrim($path, '/ ') : '';
    }

    public function hasExt()
    {
        if (in_array(strtolower($this->ext), func_get_args())) {
            return true;
        }
        return false;
    }

    public function exists()
    {
        if (!is_file($_SERVER["DOCUMENT_ROOT"] . $this->path) && !$this->tmpName) {
            return false;
        }
        return true;
    }

    public function hasSize($sizeMb)
    {
        if (!$this->size) {
            $this->size = filesize($_SERVER["DOCUMENT_ROOT"] . $this->path);
        }
        if (round($this->size / 2048, 2) <= $sizeMb) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        $this->deletedFlag = true;
    }

    public function setPath($folder = '', $name = '', $copyFlag = false)
    {
        if (!$folder) {
            $folder = $this->folder;
        } else {
            $folder = trim($folder, '/ ');
            if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/' . $folder) && !mkdir($_SERVER["DOCUMENT_ROOT"] . '/' . $folder, 0755, true)) {
                $this->logError(3, $folder);
                throw new \Exception('Failed to create directory: ' . $folder);
            }
        }
        if (!$name) {
            $name = $this->name;
        }
        $newPath = '/' . $folder . '/' . $name . '.' . $this->ext;
        if ($this->tmpName) {
            move_uploaded_file($this->tmpName, $_SERVER['DOCUMENT_ROOT'] . $newPath);
            $this->tmpName = '';
        } else {
            if (!$copyFlag) {
                rename($_SERVER["DOCUMENT_ROOT"] . $this->path, $_SERVER['DOCUMENT_ROOT'] . $newPath);
            } else {
                copy($_SERVER["DOCUMENT_ROOT"] . $this->path, $_SERVER['DOCUMENT_ROOT'] . $newPath);
            }
        }
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . $newPath)) {
            $this->logError(4, $newPath);
            throw new \Exception('Could not move file: ' . $newPath);
        }
        $this->parsePath($newPath);
        return $this;
    }

    protected function logError($errorNum, $data = '')
    {
        $mes = $this->errors[$errorNum];
        if ($data) {
            $mes .= ' # ' . $data;
        }
        $this->log[] = $mes;
    }

    public function getLog($sep = '<br>')
    {
        return implode($sep, $this->log);
    }

    public function __toString()
    {
        return $this->path;
    }

    public function __destruct()
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . $this->path;
        if ($this->deletedFlag && is_file($path)) {
            unlink($path);
        }
    }
}
