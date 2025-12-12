<?php
/* Game Class - Main game controller
OOP:
Encapsulation: All properties are private.
Abstraction: Provides simple methods to control the game (attack, place ships).
Polymorphism: Calls Player/Ship methods that behave differently depending on subclasses.
Inheritance: Not directly used here, but interacts with classes (Player, Ship) that use it.
*/
class Game {
    private $player1;        // Encapsulation: private game state
    private $player2;
    private $currentState;
    private $currentPlayer;
    private $message;

    //Constructor initializes or loads the game
    public function __construct() {
        $this->initializeGame(); // Abstraction: hides setup complexity
    }

    //Initializes game from session or creates a new one
    private function initializeGame() {
        if (!isset($_SESSION['gridstrike_game'])) {
            $this->resetGame();
        } else {
            $this->loadFromSession();
        }
    }

    //Loads all game data from session
    private function loadFromSession() {
        $gameData = $_SESSION['gridstrike_game'];
        $this->currentState = $gameData['state'];
        $this->currentPlayer = $gameData['currentPlayer'];
        $this->message = $gameData['message'];

        // Polymorphism: unserialize may return Player object with different ship subclasses
        $this->player1 = unserialize($gameData['player1']);
        $this->player2 = unserialize($gameData['player2']);
    }

    //Saves the entire game state to session
    private function saveToSession() {
        $_SESSION['gridstrike_game'] = [
            'state' => $this->currentState,
            'currentPlayer' => $this->currentPlayer,
            'message' => $this->message,
            'player1' => serialize($this->player1),
            'player2' => serialize($this->player2)
        ];
    }

    /* Places a ship on a player's board
    Polymorphism: $player->placeShip() interacts with various ship subclasses
    */
    public function placeShip($playerNum, $shipType, $row, $col, $orientation) {
        $player = $playerNum == 1 ? $this->player1 : $this->player2;

        if (!$this->canPlaceShips($playerNum)) {
            return ['success' => false, 'message' => 'Cannot place ships now'];
        }

        $success = $player->placeShip($shipType, $row, $col, $orientation);

        if ($success) {
            $this->saveToSession();
            return [
                'success' => true,
                'message' => ucfirst($shipType) . ' placed successfully!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Cannot place ship at this position'
            ];
        }
    }

    //Checks if ship placement is allowed
    private function canPlaceShips($playerNum) {
        return ($playerNum == 1 && $this->currentState == STATE_SETUP_P1) ||
               ($playerNum == 2 && $this->currentState == STATE_SETUP_P2);
    }

    //Confirms a player's ship placement and moves game to next state
    public function confirmPlacement($playerNum) {
        $player = $playerNum == 1 ? $this->player1 : $this->player2;
        $player->setShipsPlaced(true); // Encapsulation: modify player state safely

        if ($this->currentState == STATE_SETUP_P1) {
            $this->currentState = STATE_SETUP_P2;
            $this->currentPlayer = 2;
            $this->message = "Player 2: Place your ships";
        } elseif ($this->currentState == STATE_SETUP_P2) {
            $this->currentState = STATE_PLAYER1_TURN;
            $this->currentPlayer = 1;
            $this->message = "Player 1's turn to attack!";
        }

        $this->saveToSession();
        return ['success' => true, 'state' => $this->currentState];
    }

    /* Handles an attack from a player
    Polymorphism: $defender->receiveAttack() and ship->hit() vary by ship subclass
    */
    public function attack($attackingPlayer, $row, $col) {

        // Encapsulation: Only Game controls turn order logic
        if ($attackingPlayer != $this->currentPlayer) {
            return [
                'valid' => false,
                'message' => "Not your turn!",
                'hit' => false
            ];
        }

        // Determine defending player
        $defender = $attackingPlayer == 1 ? $this->player2 : $this->player1;

        $result = $defender->receiveAttack($row, $col); // Polymorphism in ship hit logic

        if (!$result['valid']) {
            return $result;
        }

        $this->message = $result['message'];

        // Game over logic
        if ($defender->hasLost()) {
            $this->currentState = STATE_GAME_OVER;
            $result['gameOver'] = true;
            $result['winner'] = $attackingPlayer;
            $this->message = "Player $attackingPlayer wins the game!";
        } else {
            // Switch turns (abstraction: simplified switching logic)
            $this->currentPlayer = $attackingPlayer == 1 ? 2 : 1;
            $this->currentState = $this->currentPlayer == 1 ? STATE_PLAYER1_TURN : STATE_PLAYER2_TURN;
            $this->message = "Player {$this->currentPlayer}'s turn to attack!";
            $result['nextPlayer'] = $this->currentPlayer;
        }

        $this->saveToSession();

        return array_merge($result, [
            'valid' => true,
            'gameOver' => $this->currentState == STATE_GAME_OVER
        ]);
    }
    public function getCurrentState() {
        return $this->currentState;
    }
    public function getCurrentPlayer() {
        return $this->currentPlayer;
    }
    public function getMessage() {
        return $this->message;
    }

    //Returns player object    
    public function getPlayer($playerNum) {
        return $playerNum == 1 ? $this->player1 : $this->player2;
    }
    public function isGameOver() {
        return $this->currentState == STATE_GAME_OVER;
    }

    //Resets entire game
    public function reset() {
        $this->resetGame();
        return ['success' => true, 'message' => 'Game reset successfully'];
    }
    /* Internal method to reset game state
    Uses Encapsulation to control how game resets
     */
    private function resetGame() {
        $this->player1 = new Player('Player 1'); // Inheritance: Player uses ships (Ship subclasses)
        $this->player2 = new Player('Player 2');
        $this->currentState = STATE_SETUP_P1;
        $this->currentPlayer = 1;
        $this->message = "Player 1: Place your ships";
        $this->saveToSession();
    }
}
?>
