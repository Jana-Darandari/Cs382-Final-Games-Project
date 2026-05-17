<?php
// Import our new Session class and Database
require_once 'Session.php';
require_once 'Database.php';

class Login {
    public function startPlayers($p1, $p2) {
        $db = (new Database())->connect();
        try {
            $stmt = $db->prepare("SELECT username FROM users WHERE username = ?");
            $stmt->bind_param("s", $p2);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                return ["status" => "error", "message" => "Opponent is not registered in the database, you cannot start the game."];
            }

            $stmt->bind_param("s", $p1);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                return ["status" => "error", "message" => "Player 1 is not registered in the database."];
            }

            // CLEAN OOP WAY: Start the game session!
            Session::startGame($p1, $p2);
            
            return ["status" => "success"];
        } finally {
            if (isset($stmt)) $stmt->close();
            $db->close();
        }
    }

    public function authenticateUser($username, $password) {
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT password, email FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                
                // CLEAN OOP WAY: Log the user in!
                Session::loginUser($username);
                
                return [
                    "status" => "success",
                    "email" => $row['email'] 
                ];
            }
        }
        $db->close();
        return ["status" => "error", "message" => "Invalid username or password!"];
    }

    public function getRegisteredPlayers() {
        $db = (new Database())->connect();
        $res = $db->query("SELECT username FROM users");
        $players = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) { 
                $players[] = $row['username']; 
            }
        }
        $db->close();
        return ["status" => "success", "players" => $players];
    }
}

// API Router
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    header('Content-Type: application/json');
    $auth = new Login();
    echo json_encode($auth->startPlayers($_POST['p1'], $_POST['p2']));
}

if (isset($_POST['action']) && $_POST['action'] == 'user_login') {
    header('Content-Type: application/json');
    $auth = new Login();
    echo json_encode($auth->authenticateUser($_POST['username'], $_POST['password']));
}

if (isset($_GET['action']) && $_GET['action'] == 'get_players') {
    header('Content-Type: application/json');
    $auth = new Login();
    echo json_encode($auth->getRegisteredPlayers());
}
?>