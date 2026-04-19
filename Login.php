<?php
session_start();
class Login {
    public function startPlayers($p1, $p2) {
        $_SESSION['p1'] = $p1;
        $_SESSION['p2'] = $p2;
        return ["status" => "success"];
    }
}
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    header('Content-Type: application/json');
    $auth = new Login();
    echo json_encode($auth->startPlayers($_POST['p1'], $_POST['p2']));
}
?>