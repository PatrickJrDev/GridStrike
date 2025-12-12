<?php
class Destroyer extends Ship {
    public function __construct() {
        parent::__construct('Destroyer', 2);
    }
    
    public function getType() {
        return 'destroyer';
    }
}
?>