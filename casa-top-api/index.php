<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/casa-top-api/index.php');

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// -------------------------------------------------------------------------
// RUTA DE PRESENTACIÓN (CARÁTULA DEL PARCIAL)
// -------------------------------------------------------------------------
$app->get('[/]', function (Request $request, Response $response){
    $html = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Parcial 3 PDM - UES</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f4f6f9;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                color: #333;
            }
            .card {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                text-align: center;
                border-top: 5px solid #b71c1c; /* Rojo UES */
                max-width: 500px;
                width: 100%;
            }
            h1 { font-size: 24px; margin-bottom: 5px; color: #b71c1c; }
            h2 { font-size: 18px; font-weight: 500; color: #555; margin-top: 0; margin-bottom: 25px; }
            p { font-size: 16px; margin: 8px 0; }
            .badge {
                background-color: #e0e0e0;
                padding: 4px 12px;
                border-radius: 20px;
                font-weight: bold;
                font-size: 14px;
                display: inline-block;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class='card'>
            <h1>Universidad de El Salvador</h1>
            <h2>Parcial 3 - Programación de Dispositivos Móviles</h2>
            <hr style='border: 0; height: 1px; background: #eee; margin-bottom: 20px;'>
            <p><strong>Estudiante:</strong> Carlos Alexander De León</p>
            <p><strong>Carnet:</strong> DG12003</p>
            <span class='badge'>CLAVE 1 - IMPAR</span>
        </div>
    </body>
    </html>
    ";
    
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
});

// -------------------------------------------------------------------------
// FUNCIÓN DE CONEXIÓN A LA BASE DE DATOS (PDO - FILESS.IO)
// -------------------------------------------------------------------------
function getDB() {
    $dbhost = "tub4sx.h.filess.io"; 
    $dbuser = "casa_top_db_paragraph";
    $dbpass = "e0ff463aaab504a3f752b07ae11f99a6a2a17de9";
    $dbname = "casa_top_db_paragraph";
    $dbport = "3306"; 
    
    $mysql_conn_string = "mysql:host=$dbhost;port=$dbport;dbname=$dbname;charset=utf8mb4";
    
    $dbConnection = new PDO($mysql_conn_string, $dbuser, $dbpass);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbConnection;
}

// -------------------------------------------------------------------------
// 1. MÓDULO DE VEHÍCULOS
// -------------------------------------------------------------------------

// POST: Adicionar un nuevo Vehículo
$app->post('/api/vehiculos', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    
    $sql = "INSERT INTO vehiculos (placa, modeloVehiculo, color, anioFabricacion, kilometraje, precioOriginal, idMarca) 
            VALUES (:placa, :modeloVehiculo, :color, :anioFabricacion, :kilometraje, :precioOriginal, :idMarca)";
            
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':placa'           => $data['placa'],
            ':modeloVehiculo'  => $data['modeloVehiculo'],
            ':color'           => $data['color'],
            ':anioFabricacion' => $data['anioFabricacion'],
            ':kilometraje'     => $data['kilometraje'],
            ':precioOriginal'  => $data['precioOriginal'],
            ':idMarca'         => $data['idMarca']
        ]);
        
        $payload = json_encode(["status" => "success", "message" => "Vehículo registrado con éxito"]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch(PDOException $e) {
        $payload = json_encode(["status" => "error", "message" => $e->getMessage()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// GET: Recuperar todos los Vehículos
$app->get('/api/vehiculos', function (Request $request, Response $response) {
    $sql = "SELECT * FROM vehiculos";
    try {
        $db = getDB();
        $stmt = $db->query($sql);
        $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode($vehiculos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch(PDOException $e) {
        $payload = json_encode(["status" => "error", "message" => $e->getMessage()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


// -------------------------------------------------------------------------
// 2. MÓDULO DE MARCAS
// -------------------------------------------------------------------------

// POST: Adicionar una nueva Marca de Vehículo
$app->post('/api/marcas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    
    $sql = "INSERT INTO marcas (idMarca, descripMarca, paisMarca, sitioWeb) 
            VALUES (:idMarca, :descripMarca, :paisMarca, :sitioWeb)";
            
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':idMarca'      => $data['idMarca'],
            ':descripMarca' => $data['descripMarca'],
            ':paisMarca'    => $data['paisMarca'],
            ':sitioWeb'     => $data['sitioWeb'] ?? null
        ]);
        
        $payload = json_encode(["status" => "success", "message" => "Marca registrada con éxito"]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch(PDOException $e) {
        $payload = json_encode(["status" => "error", "message" => $e->getMessage()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// GET: Recuperar todas las Marcas
$app->get('/api/marcas', function (Request $request, Response $response) {
    $sql = "SELECT * FROM marcas";
    try {
        $db = getDB();
        $stmt = $db->query($sql);
        $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode($marcas));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch(PDOException $e) {
        $payload = json_encode(["status" => "error", "message" => $e->getMessage()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// GET: Recuperar una Marca específica por su ID
$app->get('/api/marcas/{id}', function (Request $request, Response $response, array $args) {
    $idMarca = $args['id'];
    $sql = "SELECT * FROM marcas WHERE idMarca = :id";
    
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $idMarca]);
        $marca = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($marca) {
            $response->getBody()->write(json_encode($marca));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $payload = json_encode(["status" => "error", "message" => "Marca no encontrada"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch(PDOException $e) {
        $payload = json_encode(["status" => "error", "message" => $e->getMessage()]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->run();