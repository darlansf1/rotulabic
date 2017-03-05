<?php
include_once 'db_connect.php';
include_once 'psl-config.php';
include_once 'functions.php';

sec_session_start();

//Checking if form was submitted  
if (isset($_POST['username'], $_POST['email'], $_POST['p'], $_POST['user_role'])) {
    // Sanitize and validate the data passed in
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$user_role = filter_input(INPUT_POST, 'user_role', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Not a valid email
		setAlert("O email inserido não é válido.");
    }
 
    $password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
    if (strlen($password) != 128) {
        // The hashed pwd should be 128 characters long.
        // If it's not, something really odd has happened
		setAlert("Configuração de senha inválida.");
    }
 
    // Username validity and password validity have been checked client side.
    // This should should be adequate as nobody gains any advantage from
    // breaking these rules.
    //
 
    $prep_stmt = "SELECT user_id FROM tbl_user WHERE user_email = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);
 
   // check existing email  
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows == 1) {
            // A user with this email address already exists
			setAlert("Este email já foi cadastrado");
        }
        $stmt->close();
    } else {
		setAlert("Database error");
        $stmt->close();
    }
 
    // check existing username
    $prep_stmt = "SELECT user_id FROM tbl_user WHERE user_name = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);
 
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
 
		if ($stmt->num_rows == 1) {
			// A user with this username already exists
			setAlert("Este nome de usuário já foi cadastrado.");
		}
		$stmt->close();
    } else {
		setAlert("Database error");
		$stmt->close();
    }
 
    // TODO: 
    // We'll also have to account for the situation where the user doesn't have
    // rights to do registration, by checking what type of user is attempting to
    // perform the operation.
 
    if (isAlertEmpty()) {
        // Create a random salt
        //$random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE)); // Did not work
        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
 
        // Create salted password 
        $password = hash('sha512', $password . $random_salt);
 
        // Insert the new user into the database 
        $insert_stmt = $mysqli->prepare("INSERT INTO tbl_user (user_name, user_email, user_password, user_salt, user_role) VALUES (?, ?, ?, ?, ?)");
		phpAlert(""+$insert_stmt);
        $insert_stmt->bind_param('sssss', $username, $email, $password, $random_salt, $user_role);
        // Execute the prepared query.
        if (! $insert_stmt->execute()) {
			phpAlert($mysqli->error);
            header('Location: ../error.php?err=Registration failure: INSERT');
        }
		$insert_stmt->close();
        header('Location: ./index.php?signup=ok');
    }
}