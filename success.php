<?php
require 'vendor/autoload.php';
$config = require 'config.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
 echo "librerias";
// Configurar las credenciales
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $config['client_id'],
        $config['secret']
    )
);
$apiContext->setConfig($config['settings']);
echo "credenciales";
// Obtener los parámetros de la URL
$paymentId = $_GET['paymentId'];
$payerId = $_GET['PayerID'];

$payment = Payment::get($paymentId, $apiContext);
$execution = new PaymentExecution();
$execution->setPayerId($payerId);
echo "create id";
try {
    $result = $payment->execute($execution, $apiContext);
    echo "Pago completado con éxito. Detalles:";
    print_r($result);
} catch (Exception $ex) {
    echo "Error procesando el pago: " . $ex->getMessage();
}
echo "despues try";
?>