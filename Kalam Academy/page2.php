<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

if (!isset($_SESSION['name'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "myapp_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fetchFriendUpdates($userId, $conn) {
    $sql = "SELECT * FROM friend_updates WHERE user_id = ? ORDER BY timestamp DESC LIMIT 20";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_status'])) {
    $userId = $_SESSION['user_id'];
    $newStatus = $_POST['new_status'];

    $sql = "INSERT INTO friend_updates (user_id, update_text, timestamp) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $newStatus);
    $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}

$friendUpdates = fetchFriendUpdates($_SESSION['user_id'], $conn);
?>
