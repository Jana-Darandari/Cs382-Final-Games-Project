<?php
require_once 'Database.php';

class Register {
    public function registerUser($username, $password, $email) {
        $db = (new Database())->connect();
        
        if (empty($username) || empty($password) || empty($email)) {
            return ["status" => "error", "message" => "Missing required fields."];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["status" => "error", "message" => "Invalid email format."];
        }

        $allowed_domains = ['gmail.com'];
        $email_parts = explode('@', $email);
        $domain = strtolower(end($email_parts)); 
        
        if (!in_array($domain, $allowed_domains)) {
            return ["status" => "error", "message" => "Registration restricted to @gmail.com emails only."];
        }

        if (strlen($password) < 8) {
            return ["status" => "error", "message" => "Password must be at least 8 characters long."];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Updated SQL statement: removed dob
            $stmt = $db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            if ($stmt) {
                // Changed from "ssss" to "sss" because we have 3 strings now
                $stmt->bind_param("sss", $username, $hashedPassword, $email);
                $stmt->execute();
                return ["status" => "success"];
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                return ["status" => "error", "message" => "This username is already taken. Please choose another one."];
            } else {
                return ["status" => "error", "message" => "A database error occurred. Please try again later."];
            }
        } finally {
            if (isset($stmt)) $stmt->close();
            if (isset($db)) $db->close();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'register') {
    header('Content-Type: application/json');
    $reg = new Register();
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    echo json_encode($reg->registerUser($username, $password, $email));
}
?>