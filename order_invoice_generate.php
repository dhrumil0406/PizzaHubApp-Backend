<?php
require 'vendor/autoload.php';
require 'db.php'; // your database connection

use Dompdf\Dompdf;

// Example: Fetch order details (replace with actual query)
$orderId = $_GET['orderid'] ?? 0;

// ---- Fetch Data from DB ----
$stmt = $db->prepare("SELECT * FROM orders WHERE orderid = ?");
$stmt->execute([$orderId]);
$orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM order_items WHERE orderid = ?");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Example: fetch payment details if needed
$paymentDetails = [];
if (!empty($orderDetails['paymentid'])) {
    $stmt = $db->prepare("SELECT * FROM payments WHERE paymentid = ?");
    $stmt->execute([$orderDetails['paymentid']]);
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ---- Load invoice template ----
ob_start();
include "order_invoice_template.php";
$html = ob_get_clean();

// ---- Generate PDF ----
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ---- OPTION A: Force download to user's mobile/PC ----
$dompdf->stream("order_$orderId.pdf", ["Attachment" => true]);

// ---- OPTION B: Save to backend folder (server side) ----
$output = $dompdf->output();
file_put_contents("invoices/order_$orderId.pdf", $output);
