<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

// 1. Crear la conexión (esto faltaba)
$conexion = new mysqli($host, $user, $pass, $dbname);

if ($conexion->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conexion->connect_error]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 2. Validación
if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// 3. Buscar usuario
$verify = $conexion->prepare("SELECT email, password, name FROM users WHERE email = ?");
$verify->bind_param("s", $email);
$verify->execute();
$result = $verify->get_result();

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();

    $emailSave = $row['email'];
    $passwordHash = $row['password'];
    $nameSave = $row['name'];

    if (password_verify($password, $passwordHash)) {
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user" => [
                "email" => $emailSave,
                "name" => $nameSave
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$verify->close();
$conexion->close();
?>