<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "fisioTutor");
if ($conexion->connect_error) {
    echo json_encode(["status" => "error", "message" => "Error de conexión: " . $conexion->connect_error]);
    exit;
}

// Capturar el cuerpo de la solicitud POST en JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$emailaddress = $data['email'] ?? '';
$password = $data['password'] ?? '';
if (empty($emailaddress) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit; 
}
$verify = $conexion->prepare("SELECT email, password, name FROM users WHERE email = ?");
$verify->bind_param("s", $emailaddress);
$verify->execute();
$result = $verify->get_result();

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    file_put_contents('debug_login.txt', print_r($row, true));

    $emailaddressSave = $row['email'];
    $passwordHash = $row['password'];
    $nameSave = $row['name'];

    if (password_verify($password, $passwordHash)) {

        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user" => [
                "email" => $emailaddressSave,
                "name" => $nameSave // Enviado exitosamente al frontend
            ]
        ]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$verify->close();
$conexion->close();
?>