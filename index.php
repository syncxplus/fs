<?php
require_once 'vendor/autoload.php';

call_user_func(function ($f3) {
    if (!$f3->log) {
        $root = $f3->get('ROOT');

        $f3->config($root . '/src/cfg/system.ini');

        $f3->mset([
            'AUTOLOAD' => $root . '/src/',
            'LOGS' => $root . '/src/log/'
        ]);

        if (PHP_SAPI != 'cli') {
            $f3->config($root . '/src/cfg/route.ini');

            $f3->mset([
                'UPLOADS' => $root . '/data/',
                'ONERROR' => function ($f3) {
                    $error = $f3->get('ERROR');

                    if (!$f3->get('DEBUG')) {
                        unset($error['trace']);
                    }

                    if ($f3->get('AJAX')) {
                        echo json_encode(['error' => $error], JSON_UNESCAPED_UNICODE);
                    } else {
                        $f3->set('error', $error);
                        echo Template::instance()->render('error.html');
                    }
                }
            ]);

            $f3->run();
        }
    }
}, Base::instance());
