# GridStrike – Two-Player Web Battleship (PHP OOP SPA)

## Project Overview
- Title: GridStrike – Modern Battleship
- Concept: Two players secretly position fleets on 10x10 grids, then alternate firing shots via a shared game interface.
- Objective: Sink every ship in the opponent’s fleet before they sink yours.
- Win/Lose: A player wins once all opposing ships are sunk; the opponent loses when their last ship is destroyed.

## Technology Stack
- PHP (OOP controllers, session-backed state, ship hierarchy)
- JavaScript (AJAX/Fetch SPA flow)
- CSS (responsive layout, board styling)
- HTML (single-page shell with persistent audio)

## Team Members & Contributions
- Patrick Briane A. San Jose Jr.: Project Lead, Frontend-Backend Integration, Session Management & Bootstrap Architecture, Ship Placement UI Logic, Core JavaScript Event Handlers, PPT Design, & Collaborated on `game.js`.

- Kenneth S. Cruz: Core Game Engine & Controller (`Game.php`), Win/Lose Detection Algorithms, Game State Validation, Attack Response Handling, Game Flow Control, PPT Design, & Collaborated on `game.js`.

- Aaron Kerbie M. Malaon: Audio System Integration, SPA State Management, Battle Phase Rendering, UI Integration & Dynamic Board Updates, Attack History Tracking, Technical Documentation, & Collaborated on `game.js`.

- Christopher V. Lazatin: Complete CSS/Frontend Architecture, Visual Assets & UI Design, HTML Structure & Layout, Board Rendering Functions, UI/UX Polish, & Collaborated on `game.js`.

- Ezekiel "CJ" G. Estrellado: Player Object Implementation (`Player.php`), Input Validation & Security, Board Composition Logic, Ship Factory Pattern, OOP Architecture, Documentation, & Collaborated on `game.js`.

- Mark Joseph T. Ardenio: Repository Management, Game Lifecycle Management (Restart/Reset), Ship Selection UI Reset Logic, Quality Assurance, Project Documentation, & Collaborated on `game.js`.

## How to Play
- Setup: Each player chooses and places ships on their grid using horizontal/vertical orientation, then confirms placement.
- Turn Order: GridStrike prompts whose turn it is; only that player can attack.
- Attacks: Click a cell on the opponent’s grid. Hits are marked red, misses gray; sunk ships trigger a message.
- Victory: When `Board::allShipsSunk()` detects all ships for a player are down, the attacker wins and the game ends.
- Restart: Use “New Game” to clear the session and return to ship placement.

## How to Run the Program (XAMPP/Localhost)
- Copy the project folder into `htdocs` (e.g., `htdocs/GridStrike`).
- Start Apache (and MySQL if desired) from XAMPP Control Panel.
- Browse to `http://localhost/gridstrike/index.php`.
- Ensure PHP sessions are enabled; assets load from the `assets` and `ships` directories.
- Audio note: Background music (`assets/bgm/GridStrike.mp3`) autoplays muted—toggle with the speaker button.

## Video Demonstration Links
- Patrick Briane A. San Jose Jr.: _link pending_
- Christopher V. Lazatin: _link pending_
- Kenneth S. Cruz: _link pending_
- Ezekiel "CJ" G. Estrellado: _link pending_
- Aaron Kerbie M. Malaon: _link pending_
- Mark Joseph T. Ardenio: _link pending_

