<?php

require_once 'Session.php';
require_once 'Database.php';

class Score {
    
    public function addWin($name, $gameName = null) {
      
        $db = (new Database())->connect();

        try {

            $stmt = $db->prepare("INSERT INTO players (username, wins) VALUES (?, 1) ON DUPLICATE KEY UPDATE wins = wins + 1");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            

            // If we know game they played
            if ($gameName) {
                $stmt2 = $db->prepare("INSERT INTO player_game_wins (username, game_name, wins) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE wins = wins + 1");

                if ($stmt2) {
                    $stmt2->bind_param("ss", $name, $gameName);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }


            // Tell js everything worked 
            echo json_encode(["status" => "success", "message" => "Win recorded!"]);
            

       
        } finally {
            if (isset($stmt)) $stmt->close();
            $db->close();
        }
    }










    // PIE CHART
    public function incrementGameStat($gameName) {
 
        $db = (new Database())->connect();
        
        try {
            $stmt = $db->prepare("INSERT INTO game_stats (game_name, play_count) VALUES (?, 1) ON DUPLICATE KEY UPDATE play_count = play_count + 1");
            $stmt->bind_param("s", $gameName);
            $stmt->execute();
            
            // Send success to js
            echo json_encode(["status" => "success", "message" => "Play count updated!"]);
            
        } finally {
            if (isset($stmt)) $stmt->close();
            $db->close();
        }
    }












    // FETCH DATA FOR THE PIE CHART
    public function getGameStats() {

        $db = (new Database())->connect();
        $res = $db->query("SELECT game_name, play_count FROM game_stats");

        $data = ["Tic-Tac-Toe" => 0, "Bubble Territory" => 0, "Bubble Drop" => 0];
        
        // If db have some data
        if ($res) {
            while($row = $res->fetch_assoc()) { 
                // Overwrite  with  play count number from db
                $data[$row['game_name']] = (int)$row['play_count']; 
            }
        }

        $db->close();
        
        // Send the data array to frontend so Chart.js can draw it
        echo json_encode($data);
    }








   
    public function resetGameStats() {
        
        $db = (new Database())->connect();
        $db->query("TRUNCATE TABLE game_stats");
        $db->close();
        
        echo json_encode(["status" => "success", "message" => "Game stats have been reset to 0."]);
    }










    // BUILD THE LEADERBOARD
    public function getBoard() {
        
        $db = (new Database())->connect();
        
        //  Top 5 sorted by highest wins 
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
                        // ex "Tic-Tac-Toe" => 3
                        $gameWins[$gameRow['game_name']] = (int)$gameRow['wins'];
                    }
                    $stmt->close();
                }
                $row['gameWins'] = $gameWins;
                $data[] = $row; 
            }
        }

        $db->close();
        
        // Send leaderboard list back to the frontend
        echo json_encode($data);
    }
}









$score = new Score();









//  API ROUTER 
header('Content-Type: application/json');


$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';


// standard security
if (in_array($action, ['save_win', 'increment_stat'])) {
    Session::requireAuth(); 
}

// strict security
elseif ($action == 'reset_stats') {
    Session::requireAuth(true);
}




// ROUTE THE TRAFFIC
switch ($action) {
    
    case 'save_win':
        $game = isset($_POST['game']) ? $_POST['game'] : null;
        $score->addWin($_POST['winner'], $game);
        break;

    case 'increment_stat':
        $score->incrementGameStat($_POST['game']);
        break;

    case 'get_leaderboard':
        $score->getBoard();
        break;

    case 'get_game_stats':
        $score->getGameStats();
        break;

    case 'reset_stats':
        $score->resetGameStats();
        break;
        
    default:
        // If JavaScript asks for an action that doesn't exist, throw an error
        echo json_encode(["status" => "error", "message" => "Unknown action requested."]);
        break;
}
?>