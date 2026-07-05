<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// 1. Database Connection
$conexion = new mysqli("localhost", "root", "", "fisioTutor");

if ($conexion->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conexion->connect_error]);
    exit;
}

// 2. Get JSON Input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 3. Validation
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// 4. Check if email already exists
$checkEmail = $conexion->prepare("SELECT email FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$result = $checkEmail->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    exit;
}

// 5. Hash Password & Save User
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conexion->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "User registered successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to register user"]);
}

$stmt->close();
$conexion->close();
?>