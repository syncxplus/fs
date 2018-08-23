<?php

namespace app;

class Test
{
    private $logger;

    function beforeRoute($f3)
    {
        $this->logger = new \Log(date('Y-m-d.\l\o\g'));
        $this->logger->write($f3->get('VERB') . ' ' . $f3->get('REALM'));
    }

    function get($f3)
    {
        $f3->set('title', 'test');
        echo \Template::instance()->render('test.html');
    }
}
