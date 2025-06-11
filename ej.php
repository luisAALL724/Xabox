<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");

require __DIR__ . '/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

// Configurar SDK de MercadoPago
SDK::setAccessToken('TEST-160798400791756-102512-f4256a020f699624ffaf9e3e09842b38-2045425702');

// Leer los datos recibidos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Guardar datos para depuración
file_put_contents('debug_input.txt', $input);

// Validar errores de JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    $errorMessage = 'Error en la decodificación del JSON: ' . json_last_error_msg();
    file_put_contents('debug_error.txt', $errorMessage);
    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    exit;
}

// Inicializar array de ítems
$items = [];

// Validar y procesar productos
foreach ($data as $product) {
    if (empty($product['name']) || !is_numeric($product['price']) || $product['price'] <= 0) {
        $errorMessage = 'Producto inválido: ' . json_encode($product);
        file_put_contents('debug_error.txt', $errorMessage);
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        exit;
    }

    $item = new Item();
    $item->title = $product['name'];
    $item->quantity = 1;
    $item->unit_price = $product['price'];
    $item->currency_id = 'MXN';
    $items[] = $item;
}

// Crear y guardar preferencia
try {
    $preference = new Preference();
    $preference->items = $items;
    $preference->save();

    echo json_encode(['status' => 'success', 'preference_id' => $preference->id]);
} catch (Exception $e) {
    $errorMessage = 'Error al guardar la preferencia: ' . $e->getMessage();
    file_put_contents('debug_error.txt', $errorMessage);
    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    exit;
}
?>
