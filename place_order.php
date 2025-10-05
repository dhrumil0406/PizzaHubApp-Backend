<?php
require_once 'db.php'; // $db: PDO instance

// Step 1: Collect User Inputs
$userId        = isset($_REQUEST['userid'])      ? intval($_REQUEST['userid']) : 0;
$paymentId     = isset($_REQUEST['paymentid'])   ? intval($_REQUEST['paymentid']) : 0;
$addressId     = isset($_REQUEST['addressid'])   ? intval($_REQUEST['addressid']) : 0;
$totalPrice    = isset($_REQUEST['totalprice'])  ? floatval($_REQUEST['totalprice']) : 0;
$finalAmount   = isset($_REQUEST['finalamount']) ? floatval($_REQUEST['finalamount']) : 0;

if ($userId == 0 || $paymentId == 0 || $addressId == 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'userid, paymentid, addressid are required.',
        'data'    => []
    ]);
    exit();
}

try {
    $db->beginTransaction();

    // Step 2: Fetch User Details
    $stmt = $db->prepare("SELECT firstname, lastname, email, phoneno 
                          FROM users_admins 
                          WHERE userid = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) throw new Exception('User not found');

    $email    = $user['email'];
    $phoneno  = $user['phoneno'];

    // Step 3: Fetch Address
    $stmt = $db->prepare("SELECT addressType, name, apartmentNo, buildingName, streetArea, city 
                          FROM addresses 
                          WHERE addressid = ?");
    $stmt->execute([$addressId]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$address) throw new Exception('Address not found');

    $addressText = "{$address['apartmentNo']}, {$address['buildingName']}, {$address['streetArea']}, {$address['city']}";
    $fullname    = $address['name'];

    // Step 4: Map Payment Method
    $paymentMap = [
        1 => 'cash',
        2 => 'card',
        3 => 'upi'
    ];
    $paymentMethodText = $paymentMap[$paymentId] ?? 'unknown';

    // Step 5: Fetch Cart Items
    $stmt = $db->prepare("SELECT pizzaid, catid, quantity 
                          FROM pizza_carts 
                          WHERE userid = ?");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$cartItems) throw new Exception('Cart is empty');

    $orderItemsData = [];

    foreach ($cartItems as $ci) {
        $cartPizzaId = $ci['pizzaid'];
        $cartCatId   = $ci['catid'];
        $quantity    = $ci['quantity'];

        if (!empty($cartCatId)) {
            // ✅ Combo item
            $stmtCat = $db->prepare("SELECT comboprice, discount 
                                     FROM categories 
                                     WHERE catid = ?");
            $stmtCat->execute([$cartCatId]);
            $combo = $stmtCat->fetch(PDO::FETCH_ASSOC);
            if (!$combo) throw new Exception('Combo not found');

            $stmtComboPizzas = $db->prepare("SELECT pizzaid 
                                             FROM pizza_items 
                                             WHERE catid = ?");
            $stmtComboPizzas->execute([$cartCatId]);
            $comboPizzas = $stmtComboPizzas->fetchAll(PDO::FETCH_ASSOC);
            if (!$comboPizzas) throw new Exception('No pizzas found for combo');

            foreach ($comboPizzas as $p) {
                $orderItemsData[] = [
                    'pizzaid'  => $p['pizzaid'],
                    'catid'    => $cartCatId,
                    'quantity' => $quantity,
                    'discount' => $combo['discount']
                ];
            }
        } else {
            // ✅ Normal pizza
            $stmtPizza = $db->prepare("SELECT pizzaprice, discount 
                                       FROM pizza_items 
                                       WHERE pizzaid = ?");
            $stmtPizza->execute([$cartPizzaId]);
            $pizza = $stmtPizza->fetch(PDO::FETCH_ASSOC);
            if (!$pizza) throw new Exception('Pizza not found');

            $orderItemsData[] = [
                'pizzaid'  => $cartPizzaId,
                'catid'    => 0,
                'quantity' => $quantity,
                'discount' => $pizza['discount']
            ];
        }
    }

    // Step 6: Generate Unique Order ID
    function generateOrderId($db)
    {
        do {
            $orderid = "O" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $stmt    = $db->prepare("SELECT COUNT(*) FROM orders WHERE orderid = ?");
            $stmt->execute([$orderid]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);

        return $orderid;
    }

    $orderid = generateOrderId($db);

    // Step 7: Insert Order
    $stmt = $db->prepare("
        INSERT INTO orders 
        (orderid, userid, fullname, email, addressid, address, zip, phoneno, totalfinalprice, discountedtotalprice, paymentid, paymentmethod, orderstatus, orderdate)
        VALUES (?, ?, ?, ?, ?, ?, '000000', ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([
        $orderid,
        $userId,
        $fullname,
        $email,
        $addressId,
        $addressText,
        $phoneno,
        $totalPrice,
        $finalAmount,
        Null, // paymentid
        $paymentId // paymentmethod as integer
    ]);

    // Step 8: Insert Payment
    $stmt = $db->prepare("
        INSERT INTO payments 
        (userid, orderid, payment_method, transaction_id, amount, currency, status, created_at, updated_at)
        VALUES (?, ?, ?, NULL, ?, 'INR', 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$userId, $orderid, $paymentMethodText, $finalAmount]);

    // ✅ Get the last inserted paymentid
    $paymentInsertedId = $db->lastInsertId();

    // ✅ Update orders with this paymentid
    $updateOrder = $db->prepare("UPDATE orders SET paymentid = ? WHERE orderid = ?");
    $updateOrder->execute([$paymentInsertedId, $orderid]);

    // Step 9: Insert Order Items
    foreach ($orderItemsData as $item) {
        $stmt = $db->prepare("
            INSERT INTO order_items (orderid, pizzaid, catid, quantity, discount)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $orderid,
            $item['pizzaid'],
            $item['catid'],
            $item['quantity'],
            $item['discount']
        ]);
    }

    // Step 10: Insert Delivery Details
    $dbid    = rand(101, 105);
    $trackid = "TRACK" . rand(1000, 9999);

    $stmt = $db->prepare("
        INSERT INTO delivery_details (orderid, dbid, deliverytime, trackid, deliverydate)
        VALUES (?, ?, '30', ?, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$orderid, $dbid, $trackid]);

    // ✅ Step 11: Clear Cart Items
    $stmt = $db->prepare("DELETE FROM pizza_carts WHERE userid = ?");
    $stmt->execute([$userId]);

    $db->commit();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Order placed successfully.',
        'data'    => [
            'orderid'        => $orderid,
            'final_amount'   => $finalAmount,
            'trackid'        => $trackid,
            'pdf_url'      => "http://10.101.250.98:8080/order-download/".urlencode($orderid)
        ]
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'status'  => 'error',
        'message' => 'Transaction failed: ' . $e->getMessage(),
        'data'    => []
    ]);
}
