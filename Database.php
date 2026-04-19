<?php
class Database {
    public function connect() {
        return new mysqli("localhost", "root", "samref123", "cs382_final_project");
    }
}
?>