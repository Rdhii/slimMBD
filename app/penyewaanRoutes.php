<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

$app->get('/penyewaan', function (Request $request, Response $response) {
    $db = $this->get(PDO::class);

    $query = $db->query('CALL tampil_penyewaan()');
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($results));

    return $response->withHeader("Content-Type", "application/json");
});

$app->get('/penyewaan/{id}', function (Request $request, Response $response, array $args) {
    $db = $this->get(PDO::class);
    $id = $args['id']; // Mengambil nilai ID dari URL

    $query = $db->prepare('CALL tampil_penyewaan_by_id(:id)'); // Memanggil prosedur dengan parameter ID
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($result)) {
        // Jika hasil query kosong, berarti ID pelanggan tidak ditemukan
        $response->getBody()->write(json_encode([
            'message' => 'Penyewaan tidak ditemukan.'
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode($result));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/penyewaan', function (Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();

    // Periksa apakah semua elemen yang diperlukan ada dalam data yang diterima
    if (
        empty($parsedBody["id_pelanggan"]) ||
        empty($parsedBody["id_kendaraan"]) ||
        empty($parsedBody["tanggal_penyewaan"]) ||
        empty($parsedBody["tanggal_pengembalian"]) ||
        empty($parsedBody["durasi"])
    ) {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menambahkan penyewaan. Data yang diperlukan tidak lengkap.'
        ]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    $id_pelanggan = $parsedBody["id_pelanggan"];
    $id_kendaraan = $parsedBody["id_kendaraan"];
    $tanggal_penyewaan = $parsedBody["tanggal_penyewaan"];
    $tanggal_pengembalian = $parsedBody["tanggal_pengembalian"];
    $durasi = $parsedBody["durasi"];

    $db = $this->get(PDO::class);

        // Periksa apakah ID pelanggan yang diberikan ada dalam basis data
        $checkQuery = $db->prepare('CALL tampil_kendaraan_by_id(:id)');
        $checkQuery->bindParam(':id', $id_kendaraan, PDO::PARAM_INT);
        $checkQuery->execute();
        $result = $checkQuery->fetchAll(PDO::FETCH_ASSOC);
    
        if (empty($result)) {
            $response->getBody()->write(json_encode([
                'message' => 'Gagal menambahkan penyewaan. ID kendaraan tidak ditemukan.'
            ]));
            $checkQuery->closeCursor(); 
            return $response->withStatus(404)->withHeader("Content-Type", "application/json");
        }
        $checkQuery->closeCursor();

        

    $query = $db->prepare('CALL tambah_penyewaan(NULL, ?, ?, ?, ?, ?)'); // Memanggil prosedur dengan p_id_penyewaan = NULL
    $query->execute([$id_pelanggan, $id_kendaraan, $tanggal_penyewaan, $tanggal_pengembalian, $durasi]);

    if ($query->rowCount() > 0) {
        $response->getBody()->write(json_encode([
            'message' => 'Penyewaan baru berhasil ditambahkan'
        ]));
        return $response->withHeader("Content-Type", "application/json");
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menambahkan penyewaan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

$app->delete('/penyewaan/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL hapus_penyewaan(?)');
    $query->execute([$id]);

    if ($query->rowCount() > 0) { 
        $response->getBody()->write(json_encode([
            'id_pelanggan' => $id,
            'message' => 'Penyewaan berhasil dihapus'
        ]));
        return $response->withHeader("Content-Type", "application/json");
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menghapus penyewaan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

$app->put('/penyewaan/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $parsedBody = $request->getParsedBody();

    // Periksa apakah elemen yang diperlukan ada dalam data input
    if (
        empty($parsedBody["id_pelanggan"]) ||
        empty($parsedBody["id_kendaraan"]) ||
        empty($parsedBody["tanggal_penyewaan"]) ||
        empty($parsedBody["tanggal_pengembalian"]) ||
        empty($parsedBody["durasi"])
    ){
        $response->getBody()->write(json_encode([
            'message' => 'Gagal memperbarui penyewaan. Data yang diperlukan tidak lengkap.'
        ]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    $id_pelanggan = $parsedBody["id_pelanggan"];
    $id_kendaraan = $parsedBody["id_kendaraan"];
    $tanggal_penyewaan = $parsedBody["tanggal_penyewaan"];
    $tanggal_pengembalian = $parsedBody["tanggal_pengembalian"];
    $durasi = $parsedBody["durasi"];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL ubah_penyewaan(?, ?, ?, ?, ?, ?)');
    $query->execute([$id, $id_pelanggan, $id_kendaraan, $tanggal_penyewaan, $tanggal_pengembalian, $durasi]);

    if ($query) {
        if ($query->rowCount() > 0) {
            $response->getBody()->write(json_encode([
                'id_pelanggan' => $id_pelanggan,
                'id_kendaraan' => $id_kendaraan,
                'tanggal_penyewaan' => $tanggal_penyewaan,
                'tanggal_pengembalian' => $tanggal_pengembalian,
                'durasi' => $durasi,
                'message' => 'Penyewaan berhasil diperbarui'
            ]));
            return $response->withHeader("Content-Type", "application/json");
        } else {
            $response->getBody()->write(json_encode([
                'message' => 'Tidak ada data yang diperbarui. Mungkin ID penyewaan tidak ditemukan.'
            ]));
            return $response->withStatus(404)->withHeader("Content-Type", "application/json");
        }
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal memperbarui penyewaan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

};