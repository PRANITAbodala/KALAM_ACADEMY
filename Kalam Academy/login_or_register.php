<?php

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "myapp_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Registration and login logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Registration logic
    if (isset($_POST['signup'])) {
        $signup_name = $_POST['signup_name'];
        $signup_password = $_POST['signup_password'];

        // Basic form validation
        if (empty($signup_name) || empty($signup_password)) {
            echo "Please fill in all fields for signup.";
            exit();
        }

        // Hash the password
        $hashed_password = password_hash($signup_password, PASSWORD_DEFAULT);

        // SQL injection prevention using prepared statements
        $stmt = $conn->prepare("INSERT INTO signup (name, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $signup_name, $hashed_password);

        if ($stmt->execute()) {
            echo "Registration successful!";
            header("location:page2.html");
        } else {
            echo "Registration failed. Please try again.";
        }

        $stmt->close();
    }

    // Login logic
    if (isset($_POST['login'])) {
        $name = $_POST['name'];
        $password = $_POST['password'];

        // Basic form validation
        if (empty($name) || empty($password)) {
            echo "Please fill in all fields for login.";
            exit();
        }

        // SQL injection prevention using prepared statements
        $stmt = $conn->prepare("SELECT * FROM signup WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['name'] = $name; // Setting the session variable upon successful login
                
                
                header("Location: page2.html"); // Redirect to the dashboard or homepage
                exit();
            } else {
                echo "Invalid name or password!";
            }
        } else {
            echo "Invalid name or password!";
        }

        $stmt->close();
    }
}

$conn->close();
?>
