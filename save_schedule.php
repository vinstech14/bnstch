<?php
$servername = "sql304.infinityfree.com";
$username = "if0_35820168";
$password = "DjOlOGxgAcOjDB";
$dbname = "if0_35820168_tutorial";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to send SMS
function send_sms($name, $title, $description, $date, $number) {
    $send_data = [];
    $send_data['sender_id'] = "PhilSMS";
    $send_data['recipient'] = $number;
    $send_data['message'] = "Hi $name! $title, $description on $date";
    $token = "732|AaAxWicJmmYMvgh9wJk8kw0lTcR4KC1lZRvFyy0c";
    
    $parameters = json_encode($send_data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://app.philsms.com/api/v3/sms/send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
        "Content-Type: application/json",
        "Authorization: Bearer $token"
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $get_sms_status = curl_exec($ch);
    curl_close($ch);
    
    return $get_sms_status;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['start_datetime'];

    // Get the client's number
    $stmt = $conn->prepare("SELECT number FROM accounts WHERE name = ? AND usertype = 'client'");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($number);
    $stmt->fetch();
    $stmt->close();

    if ($number) {
        if (empty($id)) {
            // Insert new schedule
            $stmt = $conn->prepare("INSERT INTO schedule_list (name, title, description, start_datetime) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $title, $description, $date);
        } else {
            // Update existing schedule
            $stmt = $conn->prepare("UPDATE schedule_list SET name = ?, title = ?, description = ?, start_datetime = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $title, $description, $date, $id);
        }

        if ($stmt->execute()) {
            // Send SMS
            send_sms($name, $title, $description, $date, $number);
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Client not found or doesn't have a phone number.";
    }

    header("Location: index.php");
    exit();
}

$conn->close();
?>