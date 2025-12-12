<?php
class Submarine extends Ship {
    public function __construct() {
        parent::__construct('Submarine', 3);
    }
    
    public function getType() {
        return 'submarine';
    }
}
?>