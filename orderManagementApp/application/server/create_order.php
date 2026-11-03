<?php
	// Returns the products and their prices (associative array of associative arrays)
	function getProducts() {
		return [
			'quantity1' => ['name' => 'Chocolate napolitana', 'price' => 1.80],
			'quantity2' => ['name' => 'Custard cream napolitana', 'price' => 1.90],
			'quantity3' => ['name' => 'Butter croissant', 'price' => 1.50],
			'quantity4' => ['name' => 'Chocolate chip cookie', 'price' => 1.20]
		];
	}

	// Rounds a price to 2 decimals
	function roundPrice($price) {
		return (float)number_format($price, 2, '.', '');
	}

	// Calculates the price of the order without VAT
	function calculateTotal($products, $data) {
		$total = 0;
		foreach ($products as $field => $product) {
			if (isset($data[$field]) && $data[$field] !== '') {
				$total = $total + $product['price'] * (float)$data[$field];
			}
		}
		return $total;
	}

	// Adds 21% VAT to a price
	function addVat($total) {
		$vatRate = 0.21;
		return roundPrice($total + $total * $vatRate);
	}

	// Builds the order as an associative array (with the ordered products inside)
	function buildOrder($products, $data, $totalWithVat) {
		$orderedProducts = [];
		foreach ($products as $field => $product) {
			if (isset($data[$field]) && $data[$field] !== '') {
				$orderedProducts[] = [
					'name' => $product['name'],
					'price' => $product['price'],
					'quantity' => (int)$data[$field]
				];
			}
		}
		return [
			'orderCode' => isset($data['orderCode']) ? $data['orderCode'] : '',
			'customerName' => isset($data['customerName']) ? $data['customerName'] : '',
			'address' => isset($data['address']) ? $data['address'] : '',
			'email' => isset($data['email']) ? $data['email'] : '',
			'phone' => isset($data['phone']) ? $data['phone'] : '',
			'products' => $orderedProducts,
			'totalWithVat' => $totalWithVat
		];
	}

	// Reads all the orders from the file (empty list if the file does not exist yet)
	function readOrders($filename) {
		if (!file_exists($filename)) {
			return [];
		}
		$orders = json_decode(file_get_contents($filename), true);
		if (!is_array($orders)) {
			return [];
		}
		return $orders;
	}

	// Writes all the orders to the file. Returns true if it was saved
	function saveOrders($filename, $orders) {
		$json = json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		return file_put_contents($filename, $json, LOCK_EX) !== false;
	}

	// Sends only the result to the client, in JSON format
	function sendResult($totalWithVat, $saved) {
		header('Content-Type: application/json');
		echo json_encode([
			'totalWithVat' => number_format($totalWithVat, 2),
			'saved' => $saved
		]);
	}

	// Main program
	$ordersFile = "../../orders/orders.json";

	$products = getProducts();
	$total = calculateTotal($products, $_POST);
	$totalWithVat = addVat($total);
	$order = buildOrder($products, $_POST, $totalWithVat);

	$orders = readOrders($ordersFile);
	$orders[] = $order;
	$saved = saveOrders($ordersFile, $orders);

	sendResult($totalWithVat, $saved);
?>