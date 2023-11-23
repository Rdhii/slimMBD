<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

$app->get('/kendaraan', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL tampil_kendaraan()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/kendaraan/{id}', function (Request $request, Response $response, array $args) {
        $db = $this->get(PDO::class);
        $id = $args['id']; // Mengambil nilai ID dari URL
    
        $query = $db->prepare('CALL tampil_kendaraan_by_id(:id)'); // Memanggil prosedur dengan parameter ID
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
    
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            // Jika hasil query kosong, berarti ID pelanggan tidak ditemukan
            $response->getBody()->write(json_encode([
                'message' => 'Kendaraan tidak ditemukan.'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode($result));
    
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->post('/kendaraan', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
    
    // Periksa apakah "nama_pelanggan" dan "alamat_pelanggan" ada dalam data yang diterima dan tidak kosong
    if (empty($parsedBody["jenis_kendaraan"]) || 
    empty($parsedBody["merk_kendaraan"]) || 
    empty($parsedBody["biaya_sewa"]) ||
    empty($parsedBody["stok"])) {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menyimpan pelanggan. Data yang diperlukan tidak lengkap atau kosong.'
        ]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
    
        $jenis_kendaraan = $parsedBody["jenis_kendaraan"];
        $merk_kendaraan = $parsedBody["merk_kendaraan"];
        $biaya_sewa = $parsedBody["biaya_sewa"];
        $stok = $parsedBody["stok"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL tambah_kendaraan(NULL, ?, ?, ?, ?)');// Memanggil prosedur denganid_kendaraan = NULL
        $query->execute([$jenis_kendaraan, $merk_kendaraan, $biaya_sewa, $stok]);
    
        if ($query->rowCount() > 0) {
            $response->getBody()->write(json_encode([
                'message' => 'Kendaraan baru berhasil ditambahkan'
            ]));
            return $response->withHeader("Content-Type", "application/json");
        } else {
            $response->getBody()->write(json_encode([
                'message' => 'Gagal menambahkan kendaraan.'
            ]));
            return $response->withStatus(500)->withHeader("Content-Type", "application/json");
        }
    });

    // Update data kendaraan
$app->put('/kendaraan/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $parsedBody = $request->getParsedBody();

    // Periksa apakah elemen yang diperlukan ada dalam data input
    if (empty($parsedBody["jenis_kendaraan"]) | 
        empty($parsedBody["merk_kendaraan"]) | 
        empty($parsedBody["biaya_sewa"]) | 
        empty($parsedBody["stok"])) {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal memperbarui kendaraan. Data yang diperlukan tidak lengkap.'
        ]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    $jenis_kendaraan = $parsedBody["jenis_kendaraan"];
    $merk_kendaraan = $parsedBody["merk_kendaraan"];
    $biaya_sewa = $parsedBody["biaya_sewa"];
    $stok = $parsedBody["stok"];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL ubah_kendaraan(?, ?, ?, ?, ?)');
    $query->execute([$id, $jenis_kendaraan, $merk_kendaraan, $biaya_sewa, $stok]);

    if ($query) {
        if ($query->rowCount() > 0) {
            $response->getBody()->write(json_encode([
                'id_kendaraan' => $id,
                'jenis_kendaraan' => $jenis_kendaraan,
                'merk_kendaraan' => $merk_kendaraan,
                'biaya_sewa' => $biaya_sewa,
                'stok' => $stok,
                'message' => 'Kendaraan berhasil diperbarui'
            ]));
            return $response->withHeader("Content-Type", "application/json");
        } else {
            $response->getBody()->write(json_encode([
                'message' => 'Tidak ada data yang diperbarui. Mungkin ID kendaraan tidak ditemukan.'
            ]));
            return $response->withStatus(404)->withHeader("Content-Type", "application/json");
        }
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal memperbarui kendaraan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

$app->delete('/kendaraan/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL hapus_kendaraan(?)');
    $query->execute([$id]);

    if ($query->rowCount() > 0) {
        $response->getBody()->write(json_encode([
            'id_pelanggan' => $id,
            'message' => 'Kendaraan berhasil dihapus'
        ]));
        return $response->withHeader("Content-Type", "application/json");
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menghapus kendaraan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

};