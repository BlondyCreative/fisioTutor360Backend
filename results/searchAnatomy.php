<?php
// 1. Configurar cabeceras obligatorias para APIs Móviles (Evita problemas de CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Responder inmediatamente si la petición es de tipo OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "127.0.0.1";
$db_name = "fisioTutor";
$username = "root"; 
$password = ""; // 💡 Si usas MAMP en Mac y sigue fallando, cambia esto por "root"

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    http_response_code(200);
    echo json_encode([
        "status" => "error",
        "message" => "Fallo de conexión a Base de Datos: " . $exception->getMessage()
    ]);
    exit();
}
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

if (empty($query)) {
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "count" => 0,
        "data" => []
    ]);
    exit();
}

try {
    $sql = "SELECT id, name, description AS shortDescription, category, image_url 
            FROM bones 
            WHERE name LIKE :query 
               OR description LIKE :query 
               OR category LIKE :query";

    $stmt = $pdo->prepare($sql);
    $searchTerm = "%{$query}%";
    $stmt->execute(['query' => $searchTerm]);

    $bones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "count" => count($bones),
        "query" => $query,
        "data" => $bones
    ]);

} catch (PDOException $e) {
    // 🔍 SI LA CONSULTA SQL FALLA (Por ejemplo si la tabla 'bones' no existe o las columnas se llaman distinto)
    http_response_code(200); // Forzamos 200 para que pinte el error en tu app
    echo json_encode([
        "status" => "error",
        "message" => "Error en la consulta SQL: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        "status" => "error",
        "message" => "Otros errores: " . $e->getMessage()
    ]);
}