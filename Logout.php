<?php
session_start();
class Logout {
    public function exit() { session_destroy(); return ["status" => "success"]; }
}
if (isset($_POST['action']) && $_POST['action'] == 'logout') {
    header('Content-Type: application/json');
    $out = new Logout();
    echo json_encode($out->exit());
}
?>