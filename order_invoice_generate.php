<?php
require 'vendor/autoload.php';
require 'db.php';

use Dompdf\Dompdf;

$orderId = $_GET['orderid'] ?? 0;

// Fetch order details
$stmt = $db->prepare("SELECT * FROM orders WHERE orderid = ?");
$stmt->execute([$orderId]);
$orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch order items
$stmt = $db->prepare("SELECT * FROM order_items WHERE orderid = ?");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch payment details if exists
$paymentDetails = [];
if (!empty($orderDetails['paymentid'])) {
    $stmt = $db->prepare("SELECT * FROM payments WHERE paymentid = ?");
    $stmt->execute([$orderDetails['paymentid']]);
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Render template into HTML
ob_start();
include "order_invoice_template.php";
$html = ob_get_clean();

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream to browser
$dompdf->stream("order_$orderId.pdf", ["Attachment" => true]);

// Optional: Save copy to server
$output = $dompdf->output();
file_put_contents("invoices/order_$orderId.pdf", $output);
