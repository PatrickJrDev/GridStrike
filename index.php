<?php
require_once 'config.php';
$game = new Game();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => false];
    try {
        switch ($_POST['action']) {
            case 'placeShip':
                $response = $game->placeShip(
                    intval($_POST['player']),
                    $_POST['shipType'],
                    intval($_POST['row']),
                    intval($_POST['col']),
                    $_POST['orientation']
                );
                break;
                
            case 'confirmPlacement':
                $response = $game->confirmPlacement(intval($_POST['player']));
                break;
                
            case 'attack':
                $response = $game->attack(
                    intval($_POST['player']),
                    intval($_POST['row']),
                    intval($_POST['col'])
                );
                break;
                
            case 'getState':
                $response = [
                    'success' => true,
                    'state' => $game->getCurrentState(),
                    'currentPlayer' => $game->getCurrentPlayer(),
                    'message' => $game->getMessage(),
                    'gameOver' => $game->isGameOver()
                ];
                break;
                
            case 'reset':
                $game->reset();
                $response = ['success' => true, 'message' => 'Game reset'];
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GridStrike - Modern Battleship Game</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Persistent audio container -->
    <div class="audio-shell">
        <audio id="bgm" autoplay loop muted>
            <source src="assets/bgm/GridStrike.mp3" type="audio/mpeg">
        </audio>
        <button id="muteBtn" class="mute-button" type="button" aria-label="Toggle background music">
            <img id="toggleIcon" src="assets/svg/Mute.svg" alt="Toggle audio">
        </button>
    </div>

    <!-- Intro view (SPA entry) -->
    <section id="introScreen" class="intro-screen">
        <div class="intro-content">
            <img src="assets/svg/darkblue (1).png" class="intro-logo" alt="GridStrike Logo">
            <button id="startGameBtn" class="playButton" type="button" aria-label="Start GridStrike">
                <img src="assets/svg/play.png" alt="Start Game">
            </button>
            <p class="quitCommand">Press ESC to Quit.</p>
        </div>
    </section>

    <!-- Game view -->
    <section id="gameApp" class="hidden">
        <div class="container">
            <header>
                <h1>🚢 GridStrike</h1>
                <div class="game-info">
                    <span id="gameMessage"><?php echo $game->getMessage(); ?></span>
                    <button id="resetBtn" class="btn" type="button">New Game</button>
                </div>
            </header>

            <div class="main-content">
                <!-- Setup Phase -->
                <div id="setupPhase" class="interface-main">
                    
                    <div class="setup-content">
                        <!-- Interface Panel 1: Ship Selection -->
                        <div class="interface-panel ship-selector">
                            <h3>Fleet Command</h3>
                            <div class="ship-list">
                                <?php foreach (SHIP_TYPES as $type => $size): ?>
                                    <div class="ship-item" data-ship="<?php echo $type; ?>" data-size="<?php echo $size; ?>">
                                        <span><?php echo ucfirst($type); ?> (<?php echo $size; ?> units)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="orientation">
                                <h4>Placement Direction</h4>
                                <label><input type="radio" name="orientation" value="horizontal" checked> Horizontal</label>
                                <label><input type="radio" name="orientation" value="vertical"> Vertical</label>
                            </div>
                            
                            <button id="confirmBtn" class="btn" type="button" disabled>Confirm Fleet Placement</button>
                        </div>

                        <!-- Interface Panel 2: Board - FIXED THIS LINE -->
                        <div class="interface-panel board-container">
                            <h3 id="tacticalGridTitle">Tactical Grid - Player <?php echo $game->getCurrentPlayer(); ?></h3>
                            <div id="setupBoard" class="board"></div>
                        </div>
                    </div>
                </div>

                <!-- Battle Phase -->
                <div id="battlePhase" class="interface-main">
                    
                    <div class="battle-container">
                        <!-- Turn Indicator on Top -->
                        <div class="turn-indicator">
                            <div class="vs-banner">
                                <img src="assets/svg/p1.png" alt="P1" class="banner-img p1-img">
                                <img src="assets/svg/vs.png" alt="VS" class="banner-img vs-img">
                                <img src="assets/svg/p2.png" alt="P2" class="banner-img p2-img">
                            </div>

                            <div class="turn-text-wrapper">
                                <p></p>
                            </div>
                        </div>

                        <!-- Player Grids at Bottom -->
                        <div class="player-grids">
                            <!-- Player 1 Area -->
                            <div class="player-area <?php echo $game->getCurrentPlayer() == 1 ? 'active' : ''; ?>">
                                <h3>Player 1 - Command Center</h3>
                                <div id="player1Board" class="board"></div>
                            </div>
                            
                            <!-- Player 2 Area -->
                            <div class="player-area <?php echo $game->getCurrentPlayer() == 2 ? 'active' : ''; ?>">
                                <h3>Player 2 - Command Center</h3>
                                <div id="player2Board" class="board"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game Over Phase -->
            <div id="gameOverPhase" class="phase">
                <div class="game-over">
                    <h1>Mission Complete!</h1>
                    <h2 id="winnerMessage">Player <?php echo $game->getCurrentPlayer() == 1 ? '2' : '1'; ?> Wins!</h2>
                    <button id="playAgainBtn" class="btn" type="button">Launch New Mission</button>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/game.js"></script>
</body>
</html>