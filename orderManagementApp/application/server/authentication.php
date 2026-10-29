<?php
	// Reads all the users from users.json and returns them as an associative array
	function readUsers($filename) {
		return json_decode(file_get_contents($filename), true);
	}

	// Returns true if the user and password match one of the users in the array
	function checkCredentials($users, $user, $password) {
		if (!is_array($users)) {
			return false;
		}
		foreach ($users as $savedUser) {
			if ($savedUser['user'] === $user && password_verify($password, $savedUser['password'])) {
				return true;
			}
		}
		return false;
	}

	// Sends the result to the client in JSON format
	function sendResult($authenticated) {
		header('Content-Type: application/json');
		echo json_encode(['authenticated' => $authenticated]);
	}

	// Main program
	$user = isset($_POST['user']) ? $_POST['user'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';

	$users = readUsers("../../users/users.json");
	sendResult(checkCredentials($users, $user, $password));
?>