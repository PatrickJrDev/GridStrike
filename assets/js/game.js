class GridStrikeGame {
    constructor() {
        this.currentPlayer = 1;
        this.selectedShip = null;
        this.orientation = 'horizontal';
        this.boardSize = 10;
        // Change attackHistory to store hit/miss results: {player: {coord: 'hit' or 'miss'}}
        this.attackHistory = { 1: {}, 2: {} };
        this.init();
    }

    init() {
        this.setupIntroControls();
        this.setupAudioControls();
        this.setupEventListeners();
        this.renderSetupBoard();
        this.showPhase('setup');
    }

    setupIntroControls() {
        const startBtn = document.getElementById('startGameBtn');
        if (startBtn) {
            startBtn.addEventListener('click', () => this.enterGame());
        }
    }

    async enterGame() {
        const intro = document.getElementById('introScreen');
        const app = document.getElementById('gameApp');
        if (intro) intro.classList.add('hidden');
        if (app) app.classList.remove('hidden');
        await this.syncAndRender();
    }

    setupAudioControls() {
        const audio = document.getElementById('bgm');
        const toggleBtn = document.getElementById('muteBtn');
        const icon = document.getElementById('toggleIcon');

        if (!audio || !toggleBtn || !icon) return;

        toggleBtn.addEventListener('click', () => {
            if (audio.muted) {
                audio.muted = false;
                audio.play();
                audio.volume = 1.0;
                icon.src = "assets/svg/Unmute.svg";
            } else {
                audio.muted = true;
                icon.src = "assets/svg/Mute.svg";
                audio.volume = 1.0;
            }
        });
    }

    showPhase(target) {
        const setup = document.getElementById('setupPhase');
        const battle = document.getElementById('battlePhase');
        const over = document.getElementById('gameOverPhase');

        if (setup) setup.style.display = target === 'setup' ? 'flex' : 'none';
        if (battle) battle.style.display = target === 'battle' ? 'block' : 'none';
        if (over) over.style.display = target === 'over' ? 'flex' : 'none';
    }

    setupEventListeners() {
        // Ship selection
        document.querySelectorAll('.ship-item').forEach(item => {
            item.addEventListener('click', (e) => this.selectShip(e));
        });

        // Orientation
        document.querySelectorAll('input[name="orientation"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.orientation = e.target.value;
            });
        });

        // Confirm placement
        document.getElementById('confirmBtn').addEventListener('click', () => {
            this.confirmPlacement();
        });

        // Reset game
        document.getElementById('resetBtn').addEventListener('click', () => {
            this.resetGame();
        });

        // Play again
        document.getElementById('playAgainBtn').addEventListener('click', () => {
            this.resetGame();
        });
    }

    selectShip(e) {
        const shipItem = e.currentTarget;
        if (shipItem.classList.contains('placed')) return;

        document.querySelectorAll('.ship-item').forEach(item => {
            item.classList.remove('selected');
        });

        shipItem.classList.add('selected');
        this.selectedShip = {
            type: shipItem.dataset.ship,
            size: parseInt(shipItem.dataset.size)
        };
    }

    renderSetupBoard() {
        const board = document.getElementById('setupBoard');
        board.innerHTML = '';

        for (let row = 0; row < this.boardSize; row++) {
            for (let col = 0; col < this.boardSize; col++) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.dataset.row = row;
                cell.dataset.col = col;

                cell.addEventListener('mouseenter', () => this.previewShip(row, col));
                cell.addEventListener('mouseleave', () => this.clearPreview());
                cell.addEventListener('click', () => this.placeShip(row, col));

                board.appendChild(cell);
            }
        }
    }

    previewShip(row, col) {
        if (!this.selectedShip) return;
        this.clearPreview();

        const cells = this.getShipCells(row, col, this.selectedShip.size);
        const valid = this.canPlaceShip(cells);

        cells.forEach(([r, c]) => {
            const cell = document.querySelector(`#setupBoard .cell[data-row="${r}"][data-col="${c}"]`);
            if (cell) cell.style.background = valid ? 'rgba(46, 204, 113, 0.5)' : 'rgba(231, 76, 60, 0.5)';
        });
    }

    clearPreview() {
        document.querySelectorAll('#setupBoard .cell').forEach(cell => {
            cell.style.background = '';
        });
    }

    getShipCells(startRow, startCol) {
        const cells = [];
        for (let i = 0; i < this.selectedShip.size; i++) {
            if (this.orientation === 'horizontal') {
                cells.push([startRow, startCol + i]);
            } else {
                cells.push([startRow + i, startCol]);
            }
        }
        return cells;
    }

    canPlaceShip(cells) {
        return cells.every(([row, col]) => {
            if (row < 0 || row >= this.boardSize || col < 0 || col >= this.boardSize) return false;
            const cell = document.querySelector(`#setupBoard .cell[data-row="${row}"][data-col="${col}"]`);
            return cell && !cell.classList.contains('ship');
        });
    }

    async placeShip(row, col) {
        if (!this.selectedShip) {
            alert('Please select a ship first!');
            return;
        }

        const cells = this.getShipCells(row, col);
        if (!this.canPlaceShip(cells)) {
            alert('Cannot place ship here!');
            return;
        }

        const response = await this.sendRequest('placeShip', {
            player: this.currentPlayer,
            shipType: this.selectedShip.type,
            row: row,
            col: col,
            orientation: this.orientation
        });

        if (response.success) {
            cells.forEach(([r, c]) => {
                const cell = document.querySelector(`#setupBoard .cell[data-row="${r}"][data-col="${c}"]`);
                cell.classList.add('ship');
            });

            document.querySelector(`.ship-item[data-ship="${this.selectedShip.type}"]`).classList.add('placed');
            this.selectedShip = null;

            // Check if all ships placed
            const placedShips = document.querySelectorAll('.ship-item.placed').length;
            document.getElementById('confirmBtn').disabled = placedShips < 5;

            this.updateMessage(response.message);
        } else {
            alert(response.message);
        }
    }

    async confirmPlacement() {
        const response = await this.sendRequest('confirmPlacement', {
            player: this.currentPlayer
        });

        if (response.success) {
            if (response.state === 'setup_p2') {
                this.currentPlayer = 2;

                // Ensure we stay in compact mode for Player 2 setup
                document.querySelector('.container').classList.remove('pvp-mode');
                
                // Update the tactical grid title
                const tacticalGridTitle = document.querySelector('.board-container h3');
                if (tacticalGridTitle) {
                    tacticalGridTitle.textContent = 'Tactical Grid - Player 2';
                }
                
                // Update the main game message
                this.updateMessage('Player 2: Place your ships');
                
                // Reset ship selection UI
                this.resetShipUI();
                this.renderSetupBoard();
                this.showPhase('setup');
                this.currentPlayer = 2;
                this.updateTurnIndicator();
            } else {
                this.startBattle();
            }
        }
    }

    async startBattle() {
        const state = await this.sendRequest('getState', {});
        this.currentPlayer = state.currentPlayer;

        this.showPhase('battle');
        document.querySelector('.container').classList.add('pvp-mode');

        this.renderBattleBoards();
        this.updateMessage(state.message);
        this.updateTurnIndicator();
    }

    renderBattleBoards() {
        // Clear both boards first
        document.getElementById('player1Board').innerHTML = '';
        document.getElementById('player2Board').innerHTML = '';

        // Render both boards with current attack history
        this.renderBoard('player1Board', 1);
        this.renderBoard('player2Board', 2);
        
        // Setup attack functionality for current player
        this.setupAttackBoard();
        
        // Update turn indicator correctly
        this.updateTurnIndicator();
    }

    renderBoard(boardId, playerNum) {
        const board = document.getElementById(boardId);
        if (!board) return;

        for (let row = 0; row < this.boardSize; row++) {
            for (let col = 0; col < this.boardSize; col++) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.dataset.row = row;
                cell.dataset.col = col;
                cell.dataset.player = playerNum;
                
                // Check if this cell has been attacked before and show proper mark
                const attackKey = `${row},${col}`;
                if (this.attackHistory[playerNum][attackKey]) {
                    // This cell was already attacked - show hit or miss based on stored result
                    const result = this.attackHistory[playerNum][attackKey];
                    if (result === 'hit') {
                        cell.classList.add('hit');
                        // cell.textContent = 'X';
                    } else {
                        cell.classList.add('miss');
                        // cell.textContent = 'O';
                    }
                    cell.style.cursor = 'default';
                }
                
                board.appendChild(cell);
            }
        }
    }

    setupAttackBoard() {
        const opponentPlayer = this.currentPlayer === 1 ? 2 : 1;
        const opponentBoardId = this.currentPlayer === 1 ? 'player2Board' : 'player1Board';
        const opponentBoard = document.getElementById(opponentBoardId);

        if (!opponentBoard) return;

        opponentBoard.querySelectorAll('.cell').forEach(cell => {
            const row = parseInt(cell.dataset.row);
            const col = parseInt(cell.dataset.col);
            const attackKey = `${row},${col}`;
            
            // Only make cells attackable if they haven't been attacked before
            if (!this.attackHistory[opponentPlayer][attackKey]) {
                cell.classList.add('attackable');
                cell.style.cursor = 'pointer';
                
                // Remove any existing click events and add new one
                cell.replaceWith(cell.cloneNode(true));
                const newCell = opponentBoard.querySelector(`.cell[data-row="${row}"][data-col="${col}"]`);
                
                newCell.addEventListener('click', () => {
                    console.log(`Attacking ${row},${col}`);
                    this.attack(row, col);
                });
            } else {
                // Cell already attacked - make it non-clickable
                cell.style.cursor = 'default';
                cell.classList.remove('attackable');
            }
        });
    }

    async attack(row, col) {
    console.log(`Player ${this.currentPlayer} attacking ${row},${col}`);
    
    const response = await this.sendRequest('attack', {
        player: this.currentPlayer,
        row: row,
        col: col
    });

    console.log('Attack response:', response);

    if (!response.valid) {
        alert(response.message);
        return;
    }

    // Record this attack in history immediately with hit/miss result
    const opponentPlayer = this.currentPlayer === 1 ? 2 : 1;
    const attackKey = `${row},${col}`;
    
    // Store whether it was a hit or miss
    this.attackHistory[opponentPlayer][attackKey] = response.hit ? 'hit' : 'miss';

    // Update the attacked cell immediately with proper hit/miss styling
    const opponentBoardId = this.currentPlayer === 1 ? 'player2Board' : 'player1Board';
    const attackedCell = document.querySelector(`#${opponentBoardId} .cell[data-row="${row}"][data-col="${col}"]`);
    
    if (attackedCell) {
        // Remove attackable styling
        attackedCell.classList.remove('attackable');
        
        // Apply proper hit or miss styling
        if (response.hit) {
            attackedCell.classList.add('hit');
            attackedCell.classList.remove('miss');
            attackedCell.title = 'Hit!';
        } else {
            attackedCell.classList.add('miss');
            attackedCell.classList.remove('hit');
            attackedCell.title = 'Miss';
        }
        
        // Remove click event to prevent re-attacking
        attackedCell.style.cursor = 'default';
        attackedCell.onclick = null;
    }

    this.updateMessage(response.message);

    if (response.gameOver) {
        setTimeout(() => {
            // Hide battle phase
            this.showPhase('over');
            
            // Set winner message
            document.getElementById('winnerMessage').textContent = `Player ${response.winner} Wins!`;
            
            // Clear the regular game message to avoid overlap
            this.updateMessage('Game Over!');
        }, 1000);
    } else {
        // Switch turns after attack
        setTimeout(() => {
            this.currentPlayer = response.nextPlayer;
            this.renderBattleBoards();
            this.updateMessage(`Player ${this.currentPlayer}'s turn`);
        }, 1000);
    }
}

    async resetGame() {
        if (confirm('Start a new game?')) {
            // Clear attack history
            this.attackHistory = { 1: {}, 2: {} };
            this.selectedShip = null;
            this.orientation = 'horizontal';
            this.resetShipUI();
            
            const response = await this.sendRequest('reset', {});
            
            this.showPhase('setup');
            this.currentPlayer = 1;
            
            const tacticalGridTitle = document.querySelector('.board-container h3');
            if (tacticalGridTitle) {
                tacticalGridTitle.textContent = 'Tactical Grid - Player 1';
            }
            
            this.renderSetupBoard();
        
            this.updateMessage('Player 1: Place your ships');
            
            document.querySelector('.container').classList.remove('pvp-mode');
            this.resetShipUI();
        }
    }
    resetShipUI() {
        document.querySelectorAll('.ship-item').forEach(item => {
            item.classList.remove('placed', 'selected');
        });
        const orientationInput = document.querySelector('input[name="orientation"][value="horizontal"]');
        if (orientationInput) {
            orientationInput.checked = true;
        }
        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) confirmBtn.disabled = true;
    }

    updateMessage(message) {
        document.getElementById('gameMessage').textContent = message;
    }

    async syncAndRender() {
        const response = await this.sendRequest('getState', {});
        if (!response.success) return;

        this.currentPlayer = response.currentPlayer;
        this.updateMessage(response.message);

        const state = response.state;
        if (state === 'setup_p1' || state === 'setup_p2') {
            this.showPhase('setup');
            const tacticalGridTitle = document.querySelector('.board-container h3');
            if (tacticalGridTitle) {
                const playerLabel = state === 'setup_p1' ? 'Player 1' : 'Player 2';
                tacticalGridTitle.textContent = `Tactical Grid - ${playerLabel}`;
            }
            this.currentPlayer = state === 'setup_p1' ? 1 : 2;
            this.updateTurnIndicator();
        } else if (state === 'p1_turn' || state === 'p2_turn') {
            this.currentPlayer = response.currentPlayer;
            this.showPhase('battle');
            this.renderBattleBoards();
            this.updateTurnIndicator();
        } else if (state === 'game_over' || response.gameOver) {
            this.showPhase('over');
            const winnerMessage = document.getElementById('winnerMessage');
            if (winnerMessage) {
                winnerMessage.textContent = response.message || 'Mission Complete!';
            }
        }
    }

    async sendRequest(action, data) {
        const formData = new FormData();
        formData.append('action', action);
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }

        try {
            const response = await fetch('index.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Request failed:', error);
            return { success: false, message: 'Network error' };
        }
    }
}

// Start game when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.game = new GridStrikeGame();
});