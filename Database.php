<?php
class Database {
    public function connect() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = new mysqli("localhost", "root", "samref123", "cs382_final_project");
            $conn->set_charset("utf8mb4");
            return $conn;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage()); 
            die(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
        }
    }
}
?>