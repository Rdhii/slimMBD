<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

// Get all customers
$app->get('/pelanggan', function (Request $request, Response $response) {
    $db = $this->get(PDO::class);

    $query = $db->query('CALL tampil_pelanggan()');
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($results));

    return $response->withHeader("Content-Type", "application/json");
});

$app->get('/pelanggan/{id}', function (Request $request, Response $response, array $args) {
    $db = $this->get(PDO::class);
    $id = $args['id']; // Mengambil nilai ID dari URL

    $query = $db->prepare('CALL tampil_pelanggan_by_id(:id)'); // Memanggil prosedur dengan parameter ID
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($result)) {
        // Jika hasil query kosong, berarti ID pelanggan tidak ditemukan
        $response->getBody()->write(json_encode([
            'message' => 'Pelanggan tidak ditemukan.'
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode($result));

    return $response->withHeader('Content-Type', 'application/json');
});

// Create a new customer
$app->post('/pelanggan', function (Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();

    // Periksa apakah "nama_pelanggan" dan "alamat_pelanggan" ada dalam data yang diterima dan tidak kosong
    if (empty($parsedBody["nama_pelanggan"]) || empty($parsedBody["alamat_pelanggan"])) {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menyimpan pelanggan. Data yang diperlukan tidak lengkap atau kosong.'
        ]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }
    

    $nama_pelanggan = $parsedBody["nama_pelanggan"];
    $alamat_pelanggan = $parsedBody["alamat_pelanggan"];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL tambah_pelanggan(NULL, ?, ?)'); // Memanggil prosedur dengan p_id_pelanggan = NULL
    $query->execute([$nama_pelanggan, $alamat_pelanggan]);

    if ($query->rowCount() > 0) {
        $response->getBody()->write(json_encode([
            'message' => 'Pelanggan baru berhasil disimpan'
        ]));
        return $response->withHeader("Content-Type", "application/json");
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menyimpan pelanggan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});




// put data
$app->put('/pelanggan/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $parsedBody = $request->getParsedBody();

    // Periksa apakah elemen yang diperlukan ada dalam data input
    if (empty($parsedBody["nama_pelanggan"]) || empty($parsedBody["alamat_pelanggan"])) {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal memperbarui pelanggan. Data yang diperlukan tidak lengkap.'
        ]));
        return $response->withStatus(400)->withHeader("Content-Type", "application/json");
    }

    $nama_pelanggan = $parsedBody["nama_pelanggan"];
    $alamat_pelanggan = $parsedBody["alamat_pelanggan"];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL ubah_pelanggan(?, ?, ?)');
    $query->execute([$id, $nama_pelanggan, $alamat_pelanggan]);

    if ($query) {
        if ($query->rowCount() > 0) {
            $response->getBody()->write(json_encode([
                'id_pelanggan' => $id,
                'nama_pelanggan' => $nama_pelanggan,
                'alamat_pelanggan' => $alamat_pelanggan,
                'message' => 'Pelanggan berhasil diperbarui'
            ]));
            return $response->withHeader("Content-Type", "application/json");
        } else {
            $response->getBody()->write(json_encode([
                'message' => 'Tidak ada data yang diperbarui. Mungkin ID tidak ditemukan.'
            ]));
            return $response->withStatus(404)->withHeader("Content-Type", "application/json");
        }
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal memperbarui pelanggan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});



// delete data
$app->delete('/pelanggan/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];

    $db = $this->get(PDO::class);

    $query = $db->prepare('CALL hapus_pelanggan(?)');
    $query->execute([$id]);

    if ($query->rowCount() > 0) {
        $response->getBody()->write(json_encode([
            'id_pelanggan' => $id,
            'message' => 'Pelanggan berhasil dihapus'
        ]));
        return $response->withHeader("Content-Type", "application/json");
    } else {
        $response->getBody()->write(json_encode([
            'message' => 'Gagal menghapus pelanggan.'
        ]));
        return $response->withStatus(500)->withHeader("Content-Type", "application/json");
    }
});

};