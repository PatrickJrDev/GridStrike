<?php
/* Abstract Ship Class
Represents a general blueprint for all ship types.
Demonstrates: Abstraction (common structure), Encapsulation (protected props), and Polymorphism (methods meant to be overridden).
*/
abstract class Ship {

    //Ship name (ENCAPSULATION: protected property)
    protected $name;
    //Ship size/length (ENCAPSULATION)
    protected $size;
    //Number of successful hits (ENCAPSULATION)
    protected $hits = 0;
    
    //Constructor
    public function __construct($name, $size) {
        $this->name = $name;   // Encapsulated property
        $this->size = $size;   // Encapsulated property
    }

    /* Returns ship type
    ABSTRACT METHOD — forces child classes to provide their own implementation.
    Demonstrates ABSTRACTION.
    */
    abstract public function getType();

    //Get ship name (ENCAPSULATION: controlled access)
    public function getName() {
        return $this->name;
    }

    //Get ship size (ENCAPSULATION)
    public function getSize() {
        return $this->size;
    }

    //Get number of hits (ENCAPSULATION)
    public function getHits() {
        return $this->hits;
    }

    //Registers a hit on the ship
    public function hit() {
        $this->hits++;   // Modify encapsulated property safely
    }

    //Checks if ship is sunk
    public function isSunk() {
        return $this->hits >= $this->size;
    }

    /* Returns ship status
    Demonstrates POLYMORPHISM — child classes may override this.
    */
    public function getStatus() {
        return $this->isSunk() ? 'Sunk' : 'Afloat';
    }
}
?>
