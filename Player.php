<?php
/* Player Class
Represents a player in the game.
Uses COMPOSITION (contains a Board).
Uses ENCAPSULATION (private properties and controlled access).
*/
class Player {
    
    private $name;  // Encapsulation: hidden internal data
    private $board; // Composition: Player owns a Board object
    private $shipsPlaced = false;

    // Constructor initializes player name and board.
    public function __construct($name) {
        $this->name = $this->sanitizeName($name);
        $this->board = new Board(); // Composition: Board created inside Player
    }
    
    //Returns player name.
    public function getName() { return $this->name; }
    //Returns player board.
    public function getBoard() { return $this->board; }
    //Checks if player finished placing ships.
    public function hasPlacedShips() { return $this->shipsPlaced; }
    
    //Sets whether the player has placed ships.
    public function setShipsPlaced($placed) {
        $this->shipsPlaced = $placed; // Encapsulation: state updated via method
    }
    
    //Places a ship on the board.
    public function placeShip($shipType, $row, $col, $orientation) {
        $ship = $this->createShip($shipType); // ABSTRACTION: creation handled privately
        if (!$ship) return false;
        
        // POLYMORPHISM: Different ship objects share same interface but behave uniquely
        $success = $this->board->placeShip($ship, $row, $col, $orientation);
        return $success;
    }
    
    /* Creates a ship instance based on type.
    INHERITANCE + POLYMORPHISM: All returned objects extend Ship but differ in implementation.
    */
    private function createShip($shipType) {
        switch ($shipType) {
            case 'carrier': return new Carrier();
            case 'battleship': return new Battleship();
            case 'cruiser': return new Cruiser();
            case 'submarine': return new Submarine();
            case 'destroyer': return new Destroyer();
            default: return null;
        }
    }
    
    //Processes an incoming attack on the player.
    public function receiveAttack($row, $col) {
        return $this->board->receiveAttack($row, $col); // Delegation to Board
    }
    
    //Checks if all the player's ships are sunk.
    public function hasLost() {
        return $this->board->allShipsSunk();
    }

    // Basic input validation for player names.
    private function sanitizeName($name) {
        $clean = strip_tags(trim($name));
        return $clean !== '' ? $clean : 'Player';
    }
}
?>
