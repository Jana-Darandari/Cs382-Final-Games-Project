<?php
require_once 'Database.php';

class Score {
    // This saves the score to MySQL forever
    public function addWin($name) {
        $db = (new Database())->connect();
        $safeName = $db->real_escape_string($name);
        
        // This adds a win to the specific player name in the database
        $sql = "INSERT INTO players (username, wins) VALUES ('$safeName', 1) 
                ON DUPLICATE KEY UPDATE wins = wins + 1";
        
        $db->query($sql);
        $db->close();
    }

    // This gets the top 5 players of all time
    public function getBoard() {
        $db = (new Database())->connect();
        $res = $db->query("SELECT username, wins FROM players ORDER BY wins DESC LIMIT 5");
        $data = [];
        while($row = $res->fetch_assoc()) { 
            $data[] = $row; 
        }
        $db->close();
        echo json_encode($data);
    }
}

$score = new Score();

if (isset($_POST['action']) && $_POST['action'] == 'save_win') {
    $score->addWin($_POST['winner']);
}

if (isset($_GET['action']) && $_GET['action'] == 'get_leaderboard') {
    header('Content-Type: application/json');
    $score->getBoard();
}
?>