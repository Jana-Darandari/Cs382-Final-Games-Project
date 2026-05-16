<?php
session_start();
require_once 'Database.php';

class Score {
    // This saves the score to MySQL forever
    public function addWin($name, $gameName = null) {
        $db = (new Database())->connect();
        try {
            $stmt = $db->prepare("INSERT INTO players (username, wins) VALUES (?, 1) ON DUPLICATE KEY UPDATE wins = wins + 1");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            
            if ($gameName) {
                $stmt2 = $db->prepare("INSERT INTO player_game_wins (username, game_name, wins) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE wins = wins + 1");
                if ($stmt2) {
                    $stmt2->bind_param("ss", $name, $gameName);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }
            echo json_encode(["status" => "success", "message" => "Win recorded!"]);
        } finally {
            if (isset($stmt)) $stmt->close();
            $db->close();
        }
    }

    // This increments the play count for a specific game
    public function incrementGameStat($gameName) {
        $db = (new Database())->connect();
        try {
            $stmt = $db->prepare("INSERT INTO game_stats (game_name, play_count) VALUES (?, 1) ON DUPLICATE KEY UPDATE play_count = play_count + 1");
            $stmt->bind_param("s", $gameName);
            $stmt->execute();
            echo json_encode(["status" => "success", "message" => "Play count updated!"]);
        } finally {
            if (isset($stmt)) $stmt->close();
            $db->close();
        }
    }

    // This fetches the play counts for the pie chart
    public function getGameStats() {
        $db = (new Database())->connect();
        $res = $db->query("SELECT game_name, play_count FROM game_stats");
        $data = ["Tic-Tac-Toe" => 0, "Bubble Territory" => 0, "Bubble Drop" => 0];
        if ($res) {
            while($row = $res->fetch_assoc()) { 
                $data[$row['game_name']] = (int)$row['play_count']; 
            }
        }
        $db->close();
        echo json_encode($data);
    }

    // This resets the play counts for all games back to 0
    public function resetGameStats() {
        $db = (new Database())->connect();
        $db->query("TRUNCATE TABLE game_stats");
        $db->close();
        echo json_encode(["status" => "success", "message" => "Game stats have been reset to 0."]);
    }

    // This gets the top 5 players of all time
    public function getBoard() {
        $db = (new Database())->connect();
        $res = $db->query("SELECT username, wins FROM players ORDER BY wins DESC LIMIT 5");
        $data = [];
        if ($res) {
            while($row = $res->fetch_assoc()) { 
                $username = $row['username'];
                $gameWins = [];
                $stmt = $db->prepare("SELECT game_name, wins FROM player_game_wins WHERE username = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $gameRes = $stmt->get_result();
                    while($gameRow = $gameRes->fetch_assoc()) {
                        $gameWins[$gameRow['game_name']] = (int)$gameRow['wins'];
                    }
                    $stmt->close();
                }
                $row['gameWins'] = $gameWins;
                $data[] = $row; 
            }
        }
        $db->close();
        echo json_encode($data);
    }
}

$score = new Score();

if (isset($_POST['action']) && $_POST['action'] == 'save_win') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['p1']) && !isset($_SESSION['user_logged_in'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized: You must be logged in to save scores."]);
        exit;
    }
    $game = isset($_POST['game']) ? $_POST['game'] : null;
    $score->addWin($_POST['winner'], $game);
}

if (isset($_POST['action']) && $_POST['action'] == 'increment_stat') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['p1']) && !isset($_SESSION['user_logged_in'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized: You must be logged in to update stats."]);
        exit;
    }
    $score->incrementGameStat($_POST['game']);
}

if (isset($_GET['action']) && $_GET['action'] == 'get_leaderboard') {
    header('Content-Type: application/json');
    $score->getBoard();
}

if (isset($_GET['action']) && $_GET['action'] == 'get_game_stats') {
    header('Content-Type: application/json');
    $score->getGameStats();
}

if (isset($_POST['action']) && $_POST['action'] == 'reset_stats') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_logged_in'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized: Only logged-in users can reset stats."]);
        exit;
    }
    $score->resetGameStats();
}
?>