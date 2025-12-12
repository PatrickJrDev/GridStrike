<?php
/* GameManager Class - Main game controller
Handles game flow, player turns, and state management.
Uses COMPOSITION because it contains Player objects.
Uses ENCAPSULATION by keeping properties private and controlling access via methods.
*/
class GameManager {
    private $player1;   // Encapsulation: private property (Player 1)
    private $player2;   // Encapsulation: private property (Player 2)
    private $currentState;
    private $currentPlayer;
    private $message;
    
    //Constructor – Initializes the game session.
    public function __construct() {
        $this->initializeGame();
    }
    
    /* Initializes game state and players.
    ABSTRACTION: Hides setup details from outside code.
    */
    private function initializeGame() {
        if (!isset($_SESSION['game_state'])) {
            $_SESSION['game_state'] = STATE_SETUP_P1;
            $_SESSION['current_player'] = 1;

            // Composition: GameManager creates and stores Player objects
            $_SESSION['player1'] = serialize(new Player('Player 1'));
            $_SESSION['player2'] = serialize(new Player('Player 2'));
        }
        
        // Encapsulation: Internal state loaded privately
        $this->currentState = $_SESSION['game_state'];
        $this->currentPlayer = $_SESSION['current_player'];
        $this->player1 = unserialize($_SESSION['player1']);
        $this->player2 = unserialize($_SESSION['player2']);
    }
    
    /* Saves game state back into the session.
    ABSTRACTION: Hides how saving works.
    */
    private function saveGameState() {
        $_SESSION['game_state'] = $this->currentState;
        $_SESSION['current_player'] = $this->currentPlayer;
        $_SESSION['player1'] = serialize($this->player1);
        $_SESSION['player2'] = serialize($this->player2);
    }
    
    //Places a ship for a player.
    public function placeShip($playerNum, $shipType, $row, $col, $orientation) {
        // POLYMORPHISM: Different ship types behave differently (via Ship subclasses)
        $player = $playerNum == 1 ? $this->player1 : $this->player2;
        
        if (!$player->placeShip($shipType, $row, $col, $orientation)) {
            return ['success' => false, 'message' => 'Cannot place ship here'];
        }
        
        $this->saveGameState();
        return ['success' => true, 'message' => 'Ship placed successfully'];
    }
    
    //Confirms that a player has completed ship placement.
    public function confirmPlacement($playerNum) {
        $player = $playerNum == 1 ? $this->player1 : $this->player2;

        // Encapsulation: Mutating internal state through class method
        $player->setShipsPlaced(true);
        
        if ($this->currentState === STATE_SETUP_P1) {
            $this->currentState = STATE_SETUP_P2;
            $this->currentPlayer = 2;
            $this->message = "Player 2: Place your ships";
        } else {
            $this->currentState = STATE_PLAYER1_TURN;
            $this->currentPlayer = 1;
            $this->message = "Player 1's turn to attack!";
        }
        
        $this->saveGameState();
        return ['success' => true, 'state' => $this->currentState];
    }
    
    //Handles an attack from one player to another.
    public function attack($attackingPlayer, $row, $col) {
        if ($attackingPlayer != $this->currentPlayer) {
            return ['valid' => false, 'message' => "Not your turn!"];
        }
        
        // Composition: GameManager delegates attack logic to Player object
        $defender = $attackingPlayer == 1 ? $this->player2 : $this->player1;
        $result = $defender->receiveAttack($row, $col);  // POLYMORPHISM: ship hit behavior varies by ship type
        
        if (!$result['hit']) {
            // Switch turns on miss
            $this->currentPlayer = $attackingPlayer == 1 ? 2 : 1;
            $this->currentState = $this->currentPlayer == 1 ? STATE_PLAYER1_TURN : STATE_PLAYER2_TURN;
            $this->message = "Player {$this->currentPlayer}'s turn";
        } else {
            $this->message = $result['message'];
        }
        
        // Check for winner
        if ($defender->hasLost()) {
            $this->currentState = STATE_GAME_OVER;
            $this->message = "Player $attackingPlayer wins!";
            $result['gameOver'] = true;
            $result['winner'] = $attackingPlayer;
        }
        
        $this->saveGameState();
        return array_merge($result, [
            'valid' => true,
            'nextPlayer' => $this->currentPlayer,
            'gameOver' => $this->currentState === STATE_GAME_OVER
        ]);
    }
    
    //GETTERS
    public function getCurrentState() { return $this->currentState; } // Encapsulation: controlled access
    public function getCurrentPlayer() { return $this->currentPlayer; }
    public function getMessage() { return $this->message; }
    
    //Returns player object.
    public function getPlayer($num) { return $num == 1 ? $this->player1 : $this->player2; }
    //Checks if the game is over.
    public function isGameOver() {
        return $this->currentState === STATE_GAME_OVER;
    }
    
    //Resets the game by destroying session data.
    public function reset() {
        session_destroy();
        $this->initializeGame();
    }
}
?>
