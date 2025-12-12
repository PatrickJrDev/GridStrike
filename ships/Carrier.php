<?php
/**
 * Carrier Ship
 * OOP PILLAR: INHERITANCE - Extends Ship class
 */
class Carrier extends Ship {
    public function __construct() {
        parent::__construct('Carrier', 5);
    }
    
    // POLYMORPHISM: Implements abstract method
    public function getType() {
        return 'carrier';
    }
}
?>