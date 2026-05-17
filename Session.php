<?php
// We put session_start() here so any file that includes Session.php 
// automatically starts the session. No more repeating it!
session_start();

class Session {
    
    // --------------------------------------------------
    // SETTING SESSIONS (Used in Login.php)
    // --------------------------------------------------

    // Starts a main system session (when someone logs into the dashboard)
    public static function loginUser($username) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;
    }

    // Starts a game session (when two players connect to play)
    public static function startGame($p1, $p2) {
        $_SESSION['p1'] = $p1;
        $_SESSION['p2'] = $p2;
    }

    // --------------------------------------------------
    // CHECKING SESSIONS
    // --------------------------------------------------

    // Checks if ANY authorized session exists (System OR Game)
    public static function isAuthorized() {
        return isset($_SESSION['user_logged_in']) || isset($_SESSION['p1']);
    }

    // Checks specifically for main system login
    public static function isSystemLoggedIn() {
        return isset($_SESSION['user_logged_in']);
    }

    // --------------------------------------------------
    // SECURITY CHECKPOINTS (Used in Score.php)
    // --------------------------------------------------

    // Kicks out unauthorized users. 
    // If $strictSystem is true, they MUST be fully logged in (not just in a game).
    public static function requireAuth($strictSystem = false) {
        if ($strictSystem && !self::isSystemLoggedIn()) {
            echo json_encode(["status" => "error", "message" => "Unauthorized: System login required."]);
            exit;
        } elseif (!$strictSystem && !self::isAuthorized()) {
            echo json_encode(["status" => "error", "message" => "Unauthorized: You must be logged in."]);
            exit;
        }
    }

    // --------------------------------------------------
    // DESTROY SESSION (Used in Logout.php)
    // --------------------------------------------------
    public static function destroy() {
        session_destroy();
    }
}
?>