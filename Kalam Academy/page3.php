<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "myapp_db");

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch friend requests
$sqlFriendRequests = "SELECT * FROM friend_requests WHERE receiver_id = ?";
$stmtFriendRequests = $conn->prepare($sqlFriendRequests);
$stmtFriendRequests->bind_param("i", $_SESSION['user_id']);
$stmtFriendRequests->execute();
$resultFriendRequests = $stmtFriendRequests->get_result();
$friendRequests = $resultFriendRequests->fetch_all(MYSQLI_ASSOC);

// Fetch friends
$sqlFriends = "SELECT * FROM friends WHERE user_id = ?";
$stmtFriends = $conn->prepare($sqlFriends);
$stmtFriends->bind_param("i", $_SESSION['user_id']);
$stmtFriends->execute();
$resultFriends = $stmtFriends->get_result();
$friendsList = $resultFriends->fetch_all(MYSQLI_ASSOC);

// Fetch non-friend users
$sqlNonFriends = "SELECT * FROM users WHERE user_id NOT IN (SELECT friend_id FROM friends WHERE user_id = ?) AND user_id != ?";
$stmtNonFriends = $conn->prepare($sqlNonFriends);
$stmtNonFriends->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmtNonFriends->execute();
$resultNonFriends = $stmtNonFriends->get_result();
$nonFriendsList = $resultNonFriends->fetch_all(MYSQLI_ASSOC);

// Handle Accept/Reject requests, Send Friend Request, and Logout functionality
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Logout logic
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        header("Location: registration.html");
        
        exit();
    }

    // Accept or Reject friend requests
    if (isset($_POST['accept']) || isset($_POST['reject'])) {
        // Handle accept/reject functionality
        // Implement the logic to update the friend request status in the database
        if (isset($_POST['accept'])) {
            $requestId = $_POST['request_id']; // Assuming you have a hidden input in the form with request_id
        
            // Logic to update the friend request status to accepted in the database
            $sqlAccept = "UPDATE friend_requests SET status = 'accepted' WHERE request_id = ?";
            $stmtAccept = $conn->prepare($sqlAccept);
            $stmtAccept->bind_param("i", $requestId);
            $stmtAccept->execute();
        
            // Additional logic to add the user as a friend in the friends table
            // INSERT INTO friends table accordingly

            $sqlSender = "SELECT sender_id FROM friend_requests WHERE request_id = ?";
            $stmtSender = $conn->prepare($sqlSender);
            $stmtSender->bind_param("i", $requestId);
            $stmtSender->execute();
            $resultSender = $stmtSender->get_result();
            $senderId = $resultSender->fetch_assoc()['sender_id'];

            // Insert both users as friends in the friends table
            $sqlInsertFriendship = "INSERT INTO friends (user_id, friend_id) VALUES (?, ?), (?, ?)";
            $stmtInsertFriendship = $conn->prepare($sqlInsertFriendship);
            $stmtInsertFriendship->bind_param("iiii", $_SESSION['user_id'], $senderId, $senderId, $_SESSION['user_id']);
            $stmtInsertFriendship->execute();

            
        }
        
        if (isset($_POST['reject'])) {
            $requestId = $_POST['request_id']; // Assuming you have a hidden input in the form with request_id
        
            // Logic to update the friend request status to rejected in the database
            $sqlReject = "UPDATE friend_requests SET status = 'rejected' WHERE request_id = ?";
            $stmtReject = $conn->prepare($sqlReject);
            $stmtReject->bind_param("i", $requestId);
            $stmtReject->execute();
        }
        

    }

    // Send Friend Request
    if (isset($_POST['send_request'])) {
        // Handle sending friend requests
        // Implement the logic to send friend requests and update the database accordingly

        if (isset($_POST['send_request'])) {
            $userId = $_POST['user_id']; // Assuming you have a hidden input in the form with user_id
        
            // Logic to insert a new friend request in the friend_requests table
            $sqlSendRequest = "INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')";
            $stmtSendRequest = $conn->prepare($sqlSendRequest);
            $stmtSendRequest->bind_param("ii", $_SESSION['user_id'], $userId);
            $stmtSendRequest->execute();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Friends Page</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="page3.css"> <!-- Link to your CSS file -->
</head>
<body>
    <nav >
            <div class="top_right">
                <!-- Logout button -->
            <form method="post" action="">
                <input type="submit" class="box" name="logout" value="Logout">
            </form>
            </div>
            
    </nav>
    <h1>Welcome, <?php echo $_SESSION['name']; ?></h1>

    <!-- Friend Requests Section -->
    <h2>Friend Requests</h2>
    <ul>
        <?php foreach ($friendRequests as $request) : ?>
            <li>
                <?php echo $request['sender_name']; ?>
                <form method="post" action="">
                    <input type="submit" name="accept" value="Accept">
                    <input type="submit" name="reject" value="Reject">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Friends Section -->
    <h2>Friends</h2>
    <ul>
        <?php foreach ($friendsList as $friend) : ?>
            <li><?php echo $friend['friend_name']; ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Non-friends Section -->
    <h2>All Users</h2>
    <ul>
        <?php foreach ($nonFriendsList as $user) : ?>
            <li>
                <?php echo $user['username']; ?>
                <form method="post" action="">
                    <input type="submit" name="send_request" value="Send Friend Request">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <!-- 
    //Logout button
    <form method="post" action="">
        <input type="submit" name="logout" value="Logout">
    </form> -->
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
