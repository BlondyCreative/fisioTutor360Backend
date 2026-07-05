<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. Obtener datos de la app (ID del usuario, plan, etc.)
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$userId = $data['user_id'] ?? '';

// Calcular monto (Ejemplo: $5 USD al cambio oficial de tu backend o tasa fija)
$montoEnBs = 180.50; 

// 2. Configurar los datos para PagoDirecto
$api_url = "https://api.pagodirecto.com/v1/transactions"; // Revisar URL exacta en su documentación
$api_key = "TU_API_KEY_DE_PAGODIRECTO";

$payload = [
    "amount" => $montoEnBs,
    "currency" => "VES",
    "description" => "Suscripción Plan Pro - fisioTutor360",
    "order_id" => "ORD-" . uniqid() . "-USR" . $userId,
    "callback_url" => "https://tu-dominio.com/backend/webhook_pagodirecto.php", // Tu aprobador automático
    "return_url" => "https://tu-dominio.com/backend/pago_exitoso.html" // A donde regresa el WebView al terminar
];

// 3. Realizar la petición HTTP POST a la pasarela
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $api_key
]);

$response = curl_exec($ch);
curl_close($ch);

// 4. Devolver la respuesta de la pasarela a React Native (contiene el link del pasarela)
echo $response;
?>