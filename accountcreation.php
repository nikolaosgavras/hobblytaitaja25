<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();

// We can utilize the isset() function to check if the form has been submitted
if (!isset($_POST['accountemail'], $_POST['accountpassword'], $_POST['accountrole'])) {
	// Could not get the data that should have been sent
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty
if (empty($_POST['accountemail']) || empty($_POST['accountpassword']) || empty($_POST['accountrole'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
}
// Check if the username already exists
if ($stmt = $link->prepare('SELECT paneladmin_id, password_hash FROM admins WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc)
	$stmt->bind_param('s', $_POST['accountemail']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database
	$stmt->store_result();
	// Check if the account exists
	if ($stmt->num_rows > 0) {
		// Username already exists
		echo 'Email is already in use! Please choose another!';
	} else {

        // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in
        $password = password_hash($_POST['accountpassword'], PASSWORD_DEFAULT);
        // Username does not exist, insert new account
        if ($stmt = $link->prepare('INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)')) {
            // Bind POST data to the prepared statement
            $stmt->bind_param('sss', $_POST['accountemail'], $password, $_POST['accountrole']);
            $stmt->execute();
            // Output success message
            echo 'Account creation succesful';
        } else {
            // Something is wrong with the SQL statement, check to make sure the accounts table exists with all 3 fields
            echo 'Could not prepare statement!';
        }

        }
	// Close the statement
	$stmt->close();
} else {
	// Something is wrong with the SQL statement, check to make sure the accounts table exists with all 3 fields.
	echo 'Could not prepare statement!';
}



mysqli_close($link);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account creation</title>
</head>
<body>
    <h2>
        <a href="admin.php">Click here to return to the admin panel</a>
    </h2>
</body>
</html>