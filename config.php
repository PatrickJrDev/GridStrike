<?php
/**
 * GridStrike - Configuration File
 */

// Game settings
define('BOARD_SIZE', 10);
define('SHIP_TYPES', [
    'carrier' => 5,
    'battleship' => 4,
    'cruiser' => 3,
    'submarine' => 3,
    'destroyer' => 2
]);

// Game states
define('STATE_SETUP_P1', 'setup_p1');
define('STATE_SETUP_P2', 'setup_p2');
define('STATE_PLAYER1_TURN', 'p1_turn');
define('STATE_PLAYER2_TURN', 'p2_turn');
define('STATE_GAME_OVER', 'game_over');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-load classes
spl_autoload_register(function($className) {
    if (file_exists($className . '.php')) {
        require_once $className . '.php';
    } elseif (file_exists('ships/' . $className . '.php')) {
        require_once 'ships/' . $className . '.php';
    }
});


?>