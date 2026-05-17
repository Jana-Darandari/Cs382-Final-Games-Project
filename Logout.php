<?php
// Import our new Session class
require_once 'Session.php';

class Logout {
    public function exit() { 
        
        // CLEAN OOP WAY: Destroy the session!
        Session::destroy(); 
        
        return ["status" => "success"]; 
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'logout') {
    header('Content-Type: application/json');
    $out = new Logout();
    echo json_encode($out->exit());
}
?>