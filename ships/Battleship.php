<?php
class Battleship extends Ship {
    public function __construct() {
        parent::__construct('Battleship', 4);
    }
    
    public function getType() {
        return 'battleship';
    }
}
?>