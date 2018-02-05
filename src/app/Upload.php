<?php

namespace app;

class Upload extends \Web
{
    private $uri;
    private $file;
    private $logger;

    function beforeRoute($f3) {
        $this->logger = new \Logger();
        $this->logger->info($f3->VERB, $f3->REALM);
        $this->uri = ($_SERVER['QUERY_STRING']) ? substr($f3->URI, 0, strlen($f3->URI) - strlen($_SERVER['QUERY_STRING']) - 1) : $f3->URI;
        $this->file = $this->hash();
    }

    function upload($f3)
    {
        $receive = $this->receive(null, true, false);

        if ($receive === false || !is_file($receive['tmp_name'])) {
            header('HTTP/1.1 500 Upload Failure');
        } else {
            $target = $f3->get('UPLOADS') . $this->file;
            if (!is_dir(dirname($target)))  {
                mkdir(dirname($target), 0755, true);
            }
            rename($receive['tmp_name'], $target);
            header('HTTP/1.1 201 OK');
        }
    }

    function get($f3)
    {
        $file = $f3->get('UPLOADS') . $this->file;

        if (is_file($file)) {
            header('Content-Length:' . filesize($file));
            header('Content-Type:' . $this->mime($file));
            readfile($file);
        } else {
            $file = $f3->get('UPLOADS') . basename($this->uri);
            if (is_file($file)) {
                header('Content-Length:' . filesize($file));
                header('Content-Type: ' . $this->mime($file));
                readfile($file);
            } else {
                $f3->error(404);
            }
        }
    }

    /*
     * 根据文件名将文件散列到不同文件夹下
     *
     * 1492657346558_077499da.xml          -> /077499da/1492657346558_077499da.xml
     * 1492657394201_077499da_o.jpg        -> /077499da/1492657394201_077499da_o.jpg
     * 077499daadc640c48e7f8b57bf91b0cf.js -> /077499da/077499daadc640c48e7f8b57bf91b0cf.js
     * result.json                         -> /result/result.json
     */
    private function hash()
    {
        $pathInfo = pathinfo($this->uri);

        if ($pathInfo['extension']) {
            $name = substr($pathInfo['basename'], 0, strlen($pathInfo['basename']) - strlen($pathInfo['extension']) - 1);
        } else {
            $name = $pathInfo['basename'];
        }

        $parts = array_diff(explode('_', $name), ['o']);
        $count = count($parts);

        if ($count > 1) {
             $dir = $parts[1];
        } else {
            $dir = substr($parts[0], 0, min(strlen($parts[0]), 8));
        }

        return ($dir ? '/' . $dir . '/' : '/') . $pathInfo['basename'];
    }
}
