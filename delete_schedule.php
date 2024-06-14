<?php
require_once('db_connect.php');

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM schedule_list WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error: ' . $stmt->error;
    }
    $stmt->close();
} else {
    echo 'error: missing id';
}
$conn->close();
?>