<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    // Get all customers
    $app->get('/pelanggan', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL tampil_kendaraan()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    // Create a new customer
    $app->post('/pelanggan', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $id_pelanggan = $parsedBody["id_pelanggan"];
        $nama_pelanggan = $parsedBody["nama_pelanggan"];
        $alamat_pelanggan = $parsedBody["alamat_pelanggan"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('INSERT INTO pelanggan (id_pelanggan, nama_pelanggan, alamat_pelanggan) VALUES (?, ?, ?)');
        $query->execute([$id_pelanggan, $nama_pelanggan, $alamat_pelanggan]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode([
            'message' => 'Pelanggan disimpan di ID ' . $lastId
        ]));

        return $response->withHeader("Content-Type", "application/json");
    });
};
