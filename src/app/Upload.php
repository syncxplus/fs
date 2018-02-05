<?php

namespace app;

class Upload extends \Web
{
    private $logger;
    private $fileName;
    private $hashName;

    function beforeRoute($f3) {
        $this->logger = new \Logger();
        $this->logger->info($f3->VERB, $f3->REALM);
        $this->fileName = preg_replace(['/^.+[\\\\\\/]/', '/\?.*/'], '', $f3->URI);
        $this->hashName = $this->hash();
    }

    function upload($f3)
    {
        $receive = $this->receive(null, true, false);

        if ($receive === false || !is_file($receive['tmp_name'])) {
            $f3->error(500);
        } else {
            $target = $f3->get('UPLOADS') . $this->hashName;
            if (!is_dir(dirname($target)))  {
                mkdir(dirname($target), 0755, true);
            }
            rename($receive['tmp_name'], $target);
            header('HTTP/1.1 201 OK');
        }
    }

    function get($f3)
    {
        $file = $f3->get('UPLOADS') . $this->fileName;
        if (is_file($file)) {
            header('Content-Length:' . filesize($file));
            header('Content-Type:' . $this->mime($file));
            readfile($file);
        } else {
            $file = $f3->get('UPLOADS') . $this->hashName;
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
        $baseName = preg_replace('/\..*/', '', $this->fileName);
        $parts = array_diff(explode('_', $baseName), ['o']);
        if (count($parts) > 1) {
            $dir = $parts[1];
        } else {
            $dir = substr($parts[0], 0, min(strlen($baseName), 8));
        }
        return ($dir ? '/' . $dir . '/' : '/') . $this->fileName;
    }
}
