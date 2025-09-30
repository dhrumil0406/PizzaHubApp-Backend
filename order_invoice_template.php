<div class="main">
    <div class="header">
        <h2>Pizza Hub - Order Invoice</h2>
    </div>
    <div class="order-info">
        <h4>Order Details:</h4>
        <table>
            <tr>
                <td><strong>Order ID:</strong></td>
                <td><?php echo $orderDetails['orderid'] ?? 0; ?></td>
            </tr>
            <tr>
                <td><strong>Order Date:</strong></td>
                <td>
                    <?php
                    if (!empty($orderDetails['orderdate'])) {
                        echo date("d-m-Y h:i A", strtotime($orderDetails['orderdate']));
                    } else {
                        echo "N/A";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td>
                    <?php
                    if (!empty($orderDetails['paymentmethod']) && $orderDetails['paymentmethod'] == 1) {
                        echo "Cash On Delivery";
                    } else if (!empty($orderDetails['paymentmethod']) && $orderDetails['paymentmethod'] == 2) {
                        echo "Online Payment with Card";
                    } else {
                        echo "Online Payment with UPI";
                    }
                    ?>
                </td>
            </tr>
            <?php if (!empty($paymentDetails) && $orderDetails['paymentmethod'] == 2 || $orderDetails['paymentmethod'] == 3): ?>
                <tr>
                    <td><strong>Payment ID:</strong></td>
                    <td><?php echo $orderDetails['paymentid'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Transaction ID:</strong></td>
                    <td><?php echo $paymentDetails['transactionid'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Payment Status:</strong></td>
                    <td><?php echo $paymentDetails['status'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>IP Address:</strong></td>
                    <td><?php echo $paymentDetails['ip'] ?? 'N/A'; ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td><strong>Payment ID:</strong></td>
                    <td><?php echo $orderDetails['paymentid'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Payment Status:</strong></td>
                    <td><?php echo 'Pending (Case On Delivery)' ?? 'N/A'; ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="user-info">
        <h4>Customer Details:</h4>
        <table>
            <tr>
                <td><strong>Name:</strong></td>
                <td><?php echo $orderDetails['fullname'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo $orderDetails['email'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td><strong>Phone No:</strong></td>
                <td><?php echo $orderDetails['phoneno'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td><?php echo $orderDetails['address'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td><strong>Zip Code:</strong></td>
                <td><?php echo $orderDetails['zip'] ?? 'N/A'; ?></td>
            </tr>
        </table>
    </div>
    <h4>Order Items:</h4>
    <table>
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Pizza / Combo Name</th>
                <th>Quantity</th>
                <th>Price (Rs.)</th>
                <th>Total (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $comboGroups = [];
            $index = 1; // will count only pizzas and combo main row

            // Group items by catid
            foreach ($orderItems as $item) {
                if ($item['catid'] != 0) {
                    $comboGroups[$item['catid']][] = $item;
                } else {
                    // Normal pizza
                    $stmt = $db->prepare("SELECT * FROM pizza_items WHERE pizzaid = ?");
                    $stmt->execute([$item['pizzaid']]);
                    $pizzaItem = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
                    <tr>
                        <td><?php echo $index; ?></td>
                        <td><?php echo $pizzaItem['pizzaname'] ?? 'N/A'; ?></td>
                        <td><?php echo $item['quantity'] ?? 'N/A'; ?></td>
                        <td><?php echo $pizzaItem['pizzaprice'] ?? 'N/A'; ?></td>
                        <td><?php echo ($item['quantity'] * $pizzaItem['pizzaprice']) ?? 'N/A'; ?></td>
                    </tr>
                <?php
                    $index++; // increment only for normal pizza
                }
            }

            // Handle combos
            foreach ($comboGroups as $catId => $comboItems) {
                $stmt = $db->prepare("SELECT * FROM categories WHERE catid = ?");
                $stmt->execute([$catId]);
                $categoryItem = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <!-- Combo main row -->
                <tr>
                    <td><?php echo $index; ?></td>
                    <td><strong><?php echo $categoryItem['catname'] ?? 'Combo'; ?></strong></td>
                    <td><?php echo $comboItems[0]['quantity'] ?? 1; ?></td>
                    <td><?php echo $categoryItem['comboprice'] ?? 'N/A'; ?></td>
                    <td><strong><?php echo $comboItems[0]['quantity'] * $categoryItem['comboprice'] ?? 'N/A'; ?></strong></td>
                </tr>
                <?php
                $index++; // increment for combo name only

                // Print pizzas inside combo (NO index)
                foreach ($comboItems as $comboItem) {
                    $stmt2 = $db->prepare("SELECT * FROM pizza_items WHERE pizzaid = ?");
                    $stmt2->execute([$comboItem['pizzaid']]);
                    $pizzaItem = $stmt2->fetch(PDO::FETCH_ASSOC);
                ?>
                    <tr>
                        <td></td> <!-- blank index -->
                        <td><?php echo "* " . ($pizzaItem['pizzaname'] ?? 'N/A'); ?></td>
                        <td><?php echo $comboItem['quantity'] ?? 'N/A'; ?></td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>

    <div class="total">
        <p><strong>Total Final Price:</strong> <?php echo number_format($orderDetails['totalfinalprice'], 2); ?>/-Rs.</p>
        <?php
        $discountPrice = $orderDetails['totalfinalprice'] - $orderDetails['discountedtotalprice'];
        ?>
        <p><strong>Total Discount:</strong> <?php echo number_format($discountPrice, 2); ?>/-Rs.</p>
        <p><strong>Discounted Price:</strong> <?php echo number_format($orderDetails['discountedtotalprice'] ?? 0, 2); ?>/-Rs.</p>
    </div>

    <div class="footer">
        <p>Thank you for ordering from Pizza Hub!</p>
    </div>
</div>