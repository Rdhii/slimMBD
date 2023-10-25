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

        $query = $db->query('CALL tampil_pelanggan()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    // Create a new customer
    $app->post('/pelanggan', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
    
        // Periksa apakah "nama_pelanggan" dan "alamat_pelanggan" ada dalam data yang diterima
        if (!isset($parsedBody["nama_pelanggan"]) || !isset($parsedBody["alamat_pelanggan"])) {
            $response->getBody()->write(json_encode([
                'message' => 'Gagal menyimpan pelanggan. Data yang diperlukan tidak lengkap.'
            ]));
            return $response->withStatus(400)->withHeader("Content-Type", "application/json");
        }
    
        $nama_pelanggan = $parsedBody["nama_pelanggan"];
        $alamat_pelanggan = $parsedBody["alamat_pelanggan"];
    
        // Lakukan validasi data jika diperlukan
        if (empty($nama_pelanggan) || empty($alamat_pelanggan)) {
            $response->getBody()->write(json_encode([
                'message' => 'Gagal menyimpan pelanggan. Data yang diperlukan tidak boleh kosong.'
            ]));
            return $response->withStatus(400)->withHeader("Content-Type", "application/json");
        }
        
        $db = $this->get(PDO::class);
        
        $query = $db->prepare('INSERT INTO pelanggan (nama_pelanggan, alamat_pelanggan) VALUES (?, ?)');
        $query->execute([$nama_pelanggan, $alamat_pelanggan]);
    
        if ($query->rowCount() > 0) {
            $newId = $db->lastInsertId();
            $response->getBody()->write(json_encode([
                'id_pelanggan' => $newId,
                'nama_pelanggan' => $nama_pelanggan,
                'alamat_pelanggan' => $alamat_pelanggan,
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
        if (!isset($parsedBody["nama_pelanggan"]) || !isset($parsedBody["alamat_pelanggan"])) {
            $response->getBody()->write(json_encode([
                'message' => 'Gagal memperbarui pelanggan. Data yang diperlukan tidak lengkap.'
            ]));
            return $response->withStatus(400)->withHeader("Content-Type", "application/json");
        }
    
        $nama_pelanggan = $parsedBody["nama_pelanggan"];
        $alamat_pelanggan = $parsedBody["alamat_pelanggan"];
    
        // Lakukan validasi data jika diperlukan
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('UPDATE pelanggan SET nama_pelanggan = ?, alamat_pelanggan = ? WHERE id_pelanggan = ?');
        $query->execute([$nama_pelanggan, $alamat_pelanggan, $id]);
    
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
    
        $query = $db->prepare('DELETE FROM pelanggan WHERE id_pelanggan = ?');
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
