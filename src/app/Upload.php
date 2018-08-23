<?php

namespace app;

class Upload extends \Web
{
    private $logger;
    private $fileName;
    private $hashName;

    function beforeRoute($f3)
    {
        $this->logger = new \Log(date('Y-m-d.\l\o\g'));
        $this->logger->write($f3->get('VERB') . ' ' . $f3->get('REALM'));
        $length = strlen($f3->get('URI'));
        $end = strpos($f3->get('URI'), '?');
        $start = strrpos($f3->get('URI'), '/', ($end === false) ? 0 : $end - $length);
        if ($start === false) {
            $start = 0;
        } else {
            ++ $start;
        }
        if ($end === false) {
            $end = $length - $start;
        } else {
            $end -= $start;
        }
        $this->fileName = substr($f3->get('URI'), $start, $end);
        $this->logger->write('BASE ' . $this->fileName);
        $this->hashName = $this->hash();
        $this->logger->write('HASH ' . $this->hashName);
    }

    function upload($f3)
    {
        $receive = $this->receive(null, true, false);
        if ($receive === false || !is_file($receive['tmp_name'])) {
            $f3->error(500);
        } else {
            $target = $f3->get('UPLOADS') . $this->hashName;
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }
            rename($receive['tmp_name'], $target);
            header('HTTP/1.1 201 OK');
        }
    }

    function get($f3)
    {
        //locate file by hash
        $file = $f3->get('UPLOADS') . $this->hashName;
        if (is_file($file)) goto FOUND;
        //locate file by request uri
        $file = $f3->get('UPLOADS') . $this->fileName;
        if (is_file($file)) goto FOUND;
        //locate file in testbird
        $file = $f3->get('UPLOADS') . 'testbird/' . $this->fileName;
        if (is_file($file)) goto FOUND;
        $f3->error(404);
        exit;
        FOUND:
        header('Content-Type:' . $this->mime($file));
        if ($f3->get('VERB') === 'GET') {
            header('X-Sendfile:' . $file);
        } else {
            clearstatcache();
            header('Content-Length:' . filesize($file));
        }
    }

    function delete($f3)
    {
        //locate file by hash
        $file = $f3->get('UPLOADS') . $this->hashName;
        if (is_file($file)) {
            return $this->deleteFile($file);
        }
        //locate file by request uri
        $file = $f3->get('UPLOADS') . $this->fileName;
        if (is_file($file)) {
            return $this->deleteFile($file);
        }
        //locate file in testbird
        $file = $f3->get('UPLOADS') . 'testbird/' . $this->fileName;
        if (is_file($file)) {
            return $this->deleteFile($file);
        }
        header('HTTP/1.1 204 No Content');
    }

    /**
     * 根据文件名将文件散列到不同文件夹下
     */
    private function hash()
    {
        $version = intval(getenv('FSV'));
        /*
         * version 1
         * 1492657346558_077499da.xml          -> /077499da/1492657346558_077499da.xml
         * 1492657394201_077499da_o.jpg        -> /077499da/1492657394201_077499da_o.jpg
         * 077499daadc640c48e7f8b57bf91b0cf.js -> /077499da/077499daadc640c48e7f8b57bf91b0cf.js
         * result.json                         -> /result/result.json
         */
        if ($version === 1) {
            $length = strrpos($this->fileName, '.');
            $baseName = ($length === false) ? $this->fileName : substr($this->fileName, 0, $length);
            $parts = array_diff(explode('_', $baseName), ['o']);
            if (count($parts) > 1) {
                $dir = $parts[1];
            } else {
                $dir = substr($parts[0], 0, 8);
            }
            return ($dir ? '/' . $dir . '/' : '/') . $this->fileName;
        } /*
         * use latest as default
         * version 2
         * explode by '_'
         *     part0: timestamp (5chars) or app(3chars)
         *     part1: id (8chars)
         * otherwise: testbird
         */
        else {
            $tsLength = 5;
            $idLength = 8;
            $length = strrpos($this->fileName, '.');
            $baseName = ($length === false) ? $this->fileName : substr($this->fileName, 0, $length);
            $parts = explode('_', $baseName);
            if (count($parts) > 1) {
                $dir = substr($parts[0], 0, $tsLength) . '/' . substr($parts[1], 0, $idLength);
            } else {
                $dir = 'testbird';
            }
            return '/' . $dir . '/' . $this->fileName;
        }
    }

    private function deleteFile($file)
    {
        $this->logger->write('REMOVE ' . $file);
        if (unlink($file)) {
            header('HTTP/1.1 200 OK');
        } else {
            header('HTTP/1.1 202 Accepted');
        }
    }
}
