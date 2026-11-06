<?php
	// Reads all the orders from the file. Returns null if the file cannot be found or opened
	function readOrders($filename) {
		if (!file_exists($filename) || !is_readable($filename)) {
			return null;
		}
		$orders = json_decode(file_get_contents($filename), true);
		if (!is_array($orders)) {
			return null;
		}
		return $orders;
	}

	// Looks for an order by its code. Returns the order or null if it does not exist
	function findOrder($orders, $code) {
		foreach ($orders as $order) {
			if ($order['orderCode'] === $code) {
				return $order;
			}
		}
		return null;
	}

	// Builds the text of the products of an order (one line per product)
	function formatProducts($products) {
		$text = '';
		foreach ($products as $product) {
			$text = $text . htmlspecialchars($product['quantity'] . ' x ' . $product['name'] . ' (€' . number_format($product['price'], 2) . ' each)') . '<br>';
		}
		return $text;
	}

	// Main program
	$orders = readOrders("../../orders/orders.json");
	$searching = isset($_GET['orderCode']);
	$order = null;
	$errorMessage = '';

	if ($orders === null) {
		$errorMessage = "Error: the orders file could not be found or opened.";
	} else if ($searching) {
		$order = findOrder($orders, (string)$_GET['orderCode']);
		if ($order === null) {
			$errorMessage = "Error: no order was found with the code \"" . $_GET['orderCode'] . "\".";
		}
	}

	if ($errorMessage !== '') {
		header("HTTP/1.1 404 Not Found");
	} else {
		header("HTTP/1.1 200 OK");
	}

	$title = $searching ? "Order details" : "All orders";
	$ordersToShow = [];
	if ($errorMessage === '') {
		$ordersToShow = $searching ? [$order] : $orders;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sweet Bakes Orders - <?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="../client/styles.css">
</head>
<body>
  <h1><?= htmlspecialchars($title) ?></h1>

  <?php if ($errorMessage !== ''): ?>
    <p><?= htmlspecialchars($errorMessage) ?></p>
  <?php else: ?>
    <table border="1">
      <tr>
        <th>Order code</th>
        <th>Customer name</th>
        <th>Address</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Products</th>
        <th>Total price with 21% VAT</th>
      </tr>
      <?php foreach ($ordersToShow as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['orderCode']) ?></td>
        <td><?= htmlspecialchars($row['customerName']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= formatProducts($row['products']) ?></td>
        <td>€<?= number_format($row['totalWithVat'], 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <div>
    <button onclick="goTo('../client/view_orders_menu.html')">Back to view orders menu</button>
  </div>
  <div>
    <button onclick="goTo('../client/operations_menu.html')">Back to operations menu</button>
  </div>
  <div>
    <button onclick="goTo('../client/index.html')">Back to home</button>
  </div>

  <script>
    function goTo(page) {
      window.location = page;
    }
  </script>
</body>
</html>