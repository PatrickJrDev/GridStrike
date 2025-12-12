<?php
/* Cell Class
Represents one square on the game board.
Encapsulation: Properties are private.
Abstraction: Provides simple methods to interact with a cell.
 */
class Cell {
    // Encapsulation: hidden internal data
    private $row;       
    private $col;
    private $hasShip = false;
    private $isHit = false;
    private $ship = null;

    //Constructor
    public function __construct($row, $col) {
        $this->row = $row;
        $this->col = $col;
    }

    public function getRow() { return $this->row; }
    public function getCol() { return $this->col; }
    public function hasShip() { return $this->hasShip; }
    public function isHit() { return $this->isHit; }
    public function getShip() { return $this->ship; }
    
    /* Places a ship on this cell
    Polymorphism: accepts any Ship subclass
     */
    public function placeShip($ship) {
        $this->hasShip = true;
        $this->ship = $ship;
    }

    /* Handles an attack on this cell
    Uses Polymorphism: $ship->hit() may behave differently depending on ship type
     */
    public function attack() {
        if ($this->isHit) {
            return ['hit' => false, 'valid' => false, 'message' => 'Already attacked this position'];
        }

        $this->isHit = true;

        if ($this->hasShip) {
            $this->ship->hit(); // Polymorphism: different Ship types share this behavior
            $sunk = $this->ship->isSunk();
            $message = $sunk ? "Hit! You sunk the {$this->ship->getName()}!" : "Hit!";
            return ['hit' => true, 'valid' => true, 'message' => $message, 'sunk' => $sunk];
        }

        return ['hit' => false, 'valid' => true, 'message' => 'Miss!', 'sunk' => false];
    }

    /* Returns symbol for displaying the cell
    Abstraction: Hides display logic behind one method.
    */
    public function getDisplay($showShips = false) {
        if ($this->isHit) {
            return $this->hasShip ? 'X' : 'O'; // X = hit, O = miss
        }
        return $showShips && $this->hasShip ? 'S' : '~'; // S = ship, ~ = water
    }
}
?>
