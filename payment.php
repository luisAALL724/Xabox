<?php
require 'vendor/autoload.php'; // Asegúrate de que Composer esté configurado
$config = require 'config.php';

use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;

// Configurar las credenciales
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $config['client_id'],
        $config['secret']
    )
);
$apiContext->setConfig($config['settings']);

// Crear el pagador
$payer = new Payer();
$payer->setPaymentMethod("paypal");

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
$suma = 0;
// Validar y procesar productos
foreach ($data as $product) {
    if (empty($product['name']) || !is_numeric($product['price']) || $product['price'] <= 0) {
        $errorMessage = 'Producto inválido: ' . json_encode($product);
        file_put_contents('debug_error.txt', $errorMessage);
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        exit;
    }

  
    $suma =$suma+ $product['price'];
   
}

// Configurar el monto
$amount = new Amount();
$amount->setCurrency("MXN")
       ->setTotal($suma); // Cambiar por el monto deseado

// Crear la transacción
$transaction = new Transaction();
$transaction->setAmount($amount)
            ->setDescription("Pago de ejemplo con PayPal");

// Configurar las URLs de retorno y cancelación
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("http://localhost/demoMP/xabo/vadenuevo/success.php") // Cambiar por tu URL
             ->setCancelUrl("http://localhost/demoMP/xabo/vadenuevo/cancel.php");

// Crear el pago
$payment = new Payment();
$payment->setIntent("sale")
        ->setPayer($payer)
        ->setTransactions([$transaction])
        ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($apiContext);
            echo json_encode([
                'status' => 'success',
                'redirect_url' => $payment->getApprovalLink() // URL de aprobación de PayPal
            ]);
            exit();
        } catch (Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'message' => $ex->getMessage()
            ]);
            exit();
        }
        
?>