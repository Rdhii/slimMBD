<?php

declare(strict_types=1);

use Slim\App;

return function (App $app) {
    $pelangganRouter = require __DIR__ .'/pelangganRoutes.php';
    $pelangganRouter($app);

    $kendaraanRouter = require __DIR__ .'/kendaraanRoutes.php';
    $kendaraanRouter($app);

    $penyewaanRoutes = require __DIR__ .'/penyewaanRoutes.php';
    $penyewaanRoutes($app);
};