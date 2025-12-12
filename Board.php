<?php

/* Class Board
  
Represents the game board in GridStrike.
This class hides the internal grid and ship placement logic.
It abstracts away low-level operations like validating positions, placing ships, and processing attacks.
 */
class Board {
    
    //Encapsulation: kept private so outside classes cannot modify the grid directly.
    private $grid = [];
    
    //Encapsulation: hidden from outside access. Only board manages ship data.
    private $ships = [];
    
    /* Board constructor.
    Initializes the grid with Cell objects.
    Abstraction: The constructor hides how a grid is prepared internally.
    */
    public function __construct() {
        $this->initializeGrid();
    }
    
    /* Initializes the game grid with Cell objects.
    Abstraction: The details of how cells are created are hidden inside this private method.
     */
    private function initializeGrid() {
        for ($row = 0; $row < BOARD_SIZE; $row++) {
            for ($col = 0; $col < BOARD_SIZE; $col++) {
                
                // Each board position is a Cell object (object composition)
                $this->grid[$row][$col] = new Cell($row, $col);
            }
        }
    }
    
    /* Returns a specific cell from the grid.
    Encapsulation: The grid is private, but is accessed safely via this method.
    */
    public function getCell($row, $col) {
        if (isset($this->grid[$row][$col])) {
            return $this->grid[$row][$col];
        }
        return null;
    }
    
    /* Attempts to place a ship on the board.
    Encapsulation: Only this method controls how ships are placed.
    Abstraction: Outside code doesn't need to understand placement rules.
    */
    public function placeShip($ship, $startRow, $startCol, $orientation) {
        $positions = [];
        
        // Loop through ship size to check placement availability
        for ($i = 0; $i < $ship->getSize(); $i++) {
            
            // Polymorphism: getSize() is inherited from Ship and behaves the same across subclasses
            if ($orientation === 'horizontal') {
                $row = $startRow;
                $col = $startCol + $i;
            } else {
                $row = $startRow + $i;
                $col = $startCol;
            }
            
            // Validate position
            if (!$this->isValidPosition($row, $col) || 
                $this->grid[$row][$col]->hasShip()) {
                return false;
            }
            
            $positions[] = [$row, $col];
        }
        
        // Place ship once all positions are validated
        foreach ($positions as $pos) {
            $this->grid[$pos[0]][$pos[1]]->placeShip($ship);
        }
        
        $this->ships[] = $ship; // Add ship to board list
        return true;
    }
    
    /* Processes an attack on a given cell.
    Polymorphism: Cell::attack() behaves differently depending on whether the cell contains a ship or not.
     */
    public function receiveAttack($row, $col) {
        $cell = $this->getCell($row, $col);
        
        if (!$cell) {
            return ['hit' => false, 'valid' => false, 'message' => 'Invalid position'];
        }
        
        // Polymorphic behavior: attack() on a Cell can trigger Ship::hit()
        return $cell->attack();
    }
    
    /* Checks if all ships on the board have been sunk.
    Polymorphism: isSunk() may behave differently for different ship subclasses.
    */
    public function allShipsSunk() {
        foreach ($this->ships as $ship) {
            if (!$ship->isSunk()) {
                return false;
            }
        }
        
        // Board is considered active only if at least one ship existed
        return !empty($this->ships);
    }
    
    /* Checks if a position is within board boundaries.
    Abstraction: Validity logic is hidden and reused.
     */
    private function isValidPosition($row, $col) {
        return $row >= 0 && $row < BOARD_SIZE && $col >= 0 && $col < BOARD_SIZE;
    }
    
    /* Returns a text-based board rendering.
    Used for debugging in console.
    Abstraction: Converts the grid into text output.
    */
    public function display($showShips = false) {
        $output = "  ";
        
        // Column numbers
        for ($i = 0; $i < BOARD_SIZE; $i++) {
            $output .= $i . " ";
        }
        $output .= "\n";
        
        // Each row
        for ($row = 0; $row < BOARD_SIZE; $row++) {
            $output .= $row . " "; // row header
            
            for ($col = 0; $col < BOARD_SIZE; $col++) {
                
                // Polymorphism: getDisplay() behaves differently if cell contains ship / hit / miss
                $output .= $this->grid[$row][$col]->getDisplay($showShips) . " ";
            }
            $output .= "\n";
        }
        return $output;
    }
}

?>
