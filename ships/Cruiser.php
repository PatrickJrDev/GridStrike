<?php
class Cruiser extends Ship {
    public function __construct() {
        parent::__construct('Cruiser', 3);
    }
    
    public function getType() {
        return 'cruiser';
    }
}
?>