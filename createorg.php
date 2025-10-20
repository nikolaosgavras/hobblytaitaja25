<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();


if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] == 'staff') {
    header('Location: admin.php');
    exit;
}


if (!isset($_POST['submitOrg'])) {
    header('Location: userpanel.php');
    exit;
}




// Process form submission
if (isset($_POST['submitOrg'])) {
    
    // Sanitize and assign variables
    $id = intval($_POST['id']);
    $name = trim($_POST['orgName']);
    $email = trim($_POST['orgEmail']);
    $phone = trim($_POST['orgPhone']);
    $photo_url = !empty($_POST['orgImg']) ? trim($_POST['orgImg']) : NULL;
    $description = !empty($_POST['orgDesc']) ? trim($_POST['orgDesc']) : NULL;
    $address = trim($_POST['orgAddress']);
    $city = trim($_POST['orgCity']);
    $postcode = trim($_POST['orgPcode']);
    $businessid = !empty($_POST['orgBid']) ? trim($_POST['orgBid']) : NULL;
    $org_type = trim($_POST['orgType']);
    
    // Prepare and execute the insert statement
    if ($stmt = $link->prepare('INSERT INTO organizations (org_id, name, email, phone, photo_url, description, address, city, postcode, businessid, organization_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
        $stmt->bind_param('issssssssss', $id, $name, $email, $phone, $photo_url, $description, $address, $city, $postcode, $businessid, $org_type);
        
        if ($stmt->execute()) {
            echo 'Organization created successfully!';
            header('Location: userpanel.php');
            exit;
        } else {
            echo 'Error creating organization: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        echo 'Could not prepare statement!';
    }
}

mysqli_close($link);

?>