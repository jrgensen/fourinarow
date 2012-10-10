<?php
    
/**
 * Preprocess arguments and classes
 * Start game if no errors found
 * 
 * Ibuildings v0.1
 */
class startConnectFour
{
    protected $_argv;
    
    protected $_player1FileName;
    protected $_player2FileName;
    
    protected $_player1ClassName;
    protected $_player2ClassName;
    
    protected $_width;
    protected $_height;
    
    protected $_nrOfGames;
    
    protected $_msgArray;
    
    /**
     * Constructor
     * 
     * @param array $argv commandline arguments
     */
    public function __construct($argv)
    {
        system('clear');
        echo "Starting ConnectFour contest..\n";
        echo "==============================\n";
        
        $this->_argv = $argv;
    }

    /**
     * Check commandline arguments
     * 
     * @return boolean $readyToStart true if the game can begin
     */
    public function processArguments()
    {
        $readyToStart = true;
        
        // check player 1 class
        if (!isset($this->_argv[1])) {
            $this->_msgArray[] = "Player 1 filename not set";
            $readyToStart = false;
        }
        else {        
            $player1FileName = $this->_argv[1];
            
            if (!file_exists($player1FileName)){
                $this->_msgArray[] = "Player 1 file '{$player1FileName}' not found";
                $readyToStart = false;
            }
            else {           
                $this->_player1FileName = $player1FileName;
                $this->_player1ClassName = $this->getClassNameFromPlayerFile($player1FileName);
            }
        }
        
        // check player 2 class
        if (!isset($this->_argv[2])) {
            $this->_msgArray[] = "Player 2 filename not set";
            $readyToStart = false;
        }
        else {
            $player2FileName = $this->_argv[2];
            
            if (!file_exists($player2FileName)){
                $this->_msgArray[] = "Player 2 file '{$player2FileName}' not found";
                $readyToStart = false;
            }
            else {           
                $this->_player2FileName = $player2FileName;
                $this->_player2ClassName = $this->getClassNameFromPlayerFile($player2FileName);
            }
        }
        
        // check width (defaults to 7)
        if (!isset($this->_argv[3]) || !is_numeric($this->_argv[3]) || ($this->_argv[3] < 4)) {
            $this->_width = 7;
        }
        else {
            $this->_width = $this->_argv[3];
        }
        
        // check height (defaults to 6)
        if (!isset($this->_argv[4]) || !is_numeric($this->_argv[4]) || ($this->_argv[4] < 4)) {
            $this->_height = 6;
        }
        else {
            $this->_height = $this->_argv[4];
        }
        
        // check number of games
        if (!isset($this->_argv[5]) || !is_numeric($this->_argv[5]) || ($this->_argv[5] < 1)) {
            $this->_nrOfGames = 1;
        }
        else {
            $this->_nrOfGames = $this->_argv[5];
        }
        
        return $readyToStart;
    }
    
    /**
     * Uses the first occurance by default
     * 
     * @param string $playerFile 
     */
    protected function getClassNameFromPlayerFile($playerFile)
    {
        $tokens = token_get_all( file_get_contents($playerFile) );
        $class_token = false;
        
        foreach ($tokens as $token) {
            if ( !is_array($token) ) {
                continue;
            }
            
            if ($token[0] == T_CLASS) {
                $class_token = true;
            } 
            else if ($class_token && $token[0] == T_STRING) {
                echo "Found player class '{$token[1]}' in file '{$playerFile}'.\n";
                return $token[1];
            }
        }
    }
    
    /**
     * Show messages
     * 
     * @return boolean $return false in case of an error
     */
    public function showMessages()
    {
        if (empty($this->_msgArray)) {
            return true;
        }
        
        foreach ($this->_msgArray as $message) {
            echo " * $message \n";
        }
        
        echo "\nGame not started.\n";
        
        echo "\nUsage:\n";
        echo " php startConnectFour.php [Player1.php] [Player2.php] [width] [height] [numberOfGames]\n";
        echo "\n";
        echo "Notes:\n";
        echo " - The first class in the file should be the 'FourInALinePlayer' class (for autodetection).\n";
        echo " - Use 'include_once' instead to include 'FourInALinePlayer.php', not 'include'\n";
        
        return false;
    }
    
    /**
     * Create players and start the game
     * This function can also run multiple games and print the statistics
     */
    public function constructPlayersAndStartGame()
    {        
        // include players
        include './' . $this->_player1FileName;
        include './' . $this->_player2FileName;

        $players[ConnectFour::PLAYER_ONE] = new $this->_player1ClassName(ConnectFour::PLAYER_ONE, $this->_width, $this->_height);
        $players[ConnectFour::PLAYER_TWO] = new $this->_player2ClassName(ConnectFour::PLAYER_TWO, $this->_width, $this->_height);

        echo "\nStarting {$this->_nrOfGames} game" . ($this->_nrOfGames>1?"s":"") ." on a {$this->_width} x {$this->_height} grid (official grid is 7x6)\n";
        echo "Between " . $players[ConnectFour::PLAYER_ONE]->getName() . " (".ConnectFour::getDiscDisplayValue(ConnectFour::PLAYER_ONE).") and " . $players[ConnectFour::PLAYER_TWO]->getName() . " (".ConnectFour::getDiscDisplayValue(ConnectFour::PLAYER_TWO).")\n";

        for ($gameNr = 0 ; $gameNr < $this->_nrOfGames ; $gameNr++) {
            $result = $this->playGame($players);
            $gamesArray[] = $result;
        }
        
        
        if ($this->_nrOfGames > 1) {
            $this->printStatistics($players, $gamesArray);
        }
        
        echo "Game over.\n";
    }

    /**
     * Play a game between two FourInALinePlayer's 
     * 
     * @param array $players two FourInALinePlayer's
     * @param array $result winner, number of moves and grid
     */
    protected function playGame($players)
    {
        $showDetails = ($this->_nrOfGames == 1);
        
        if ($showDetails) {
            echo "\nMessages:";
        }

        $grid = new ConnectFour ($this->_width, $this->_height);

        for ($i = 0; $i < ($this->_width * $this->_height); $i++) {

           $p = ($i % 2 == 0) ? ConnectFour::PLAYER_ONE : ConnectFour::PLAYER_TWO;
           $move = $players[$p]->getMove($grid->getGrid());
           
           try {
               $winner = $grid->insert ($move, $p);
           }
           catch (Exception $e) {
               if ($showDetails) {
                   echo " Wrong move by " . $players[$p]->getName() . " (".ConnectFour::getDiscDisplayValue($p)."), ";
               }                   
               
               // we don't want a lockup, so the other player wins when a wrong move is perfomed
               $p = ($p == ConnectFour::PLAYER_ONE ? ConnectFour::PLAYER_TWO : ConnectFour::PLAYER_ONE);
               $winner = true;

               if ($showDetails) {
                   echo $players[$p]->getName() . " (".ConnectFour::getDiscDisplayValue($p).") wins.\n";    
                   echo " - " . $e->getMessage();
               }                   
           }
        
           if ($showDetails) {
               // we only animate if only 1 game is played
               system('clear');
               echo "ConnectFour contest..\n";
               echo "=====================\n";
               echo "Running {$this->_nrOfGames} game" . ($this->_nrOfGames>1?"s":"") ." on a {$this->_width} x {$this->_height} grid (official grid is 7x6)\n";
               echo "Between " . $players[ConnectFour::PLAYER_ONE]->getName() . " (".ConnectFour::getDiscDisplayValue(ConnectFour::PLAYER_ONE).") and " . $players[ConnectFour::PLAYER_TWO]->getName() . " (".ConnectFour::getDiscDisplayValue(ConnectFour::PLAYER_TWO).")\n";
               
               echo "Move: ".($i+1)."\n";
               $grid->display();
               usleep(400000);
           }
           
           if ($winner) {
               $winningGrid = $grid->getDisplayGrid();
               
               if ($showDetails) {
                   $winnerName = $players[$p]->getName();

                   echo "\n\nPlayer {$winnerName} (".ConnectFour::getDiscDisplayValue($p).") won after ".ceil(($i + 1)/2)." turns\n\n";
               }
               break;
           }
           else {
               // NO WINNER
               $p = null;
           }
        }
        
        return array('winner' => $p,
                     'moves'  => ($i+1),
                     'grid'   => $winningGrid);
    }
    
    /**
     * Print the multigame statistics
     * 
     * @param array $players FourInALinePlayers'
     * @param array $gamesArray 
     */
    protected function printStatistics($players, $gamesArray)
    {
        echo "\n";
        
        ////////////////////////
        // PLAYER ONE RESULTS //
        ////////////////////////
        $player1Stats = $this->calculateStatistics($gamesArray, ConnectFour::PLAYER_ONE);
        
        $playerInfo = $players[ConnectFour::PLAYER_ONE]->getName() . " (".ConnectFour::getDiscDisplayValue(ConnectFour::PLAYER_ONE).")";
        echo $playerInfo . "\n";
        echo str_repeat("-", strlen($playerInfo)) . "\n";        
        
        $this->printStatisticsForPlayer($player1Stats);
        
        ////////////////////////
        // PLAYER TWO RESULTS //
        ////////////////////////        
        $player2Stats = $this->calculateStatistics($gamesArray, ConnectFour::PLAYER_TWO);
        $playerInfo = $players[ConnectFour::PLAYER_TWO]->getName() . " (".ConnectFour::getDiscDisplayValue(ConnectFour::PLAYER_TWO).")";
        echo $playerInfo . "\n";
        echo str_repeat("-", strlen($playerInfo)) . "\n";

        $this->printStatisticsForPlayer($player2Stats);

        ///////////
        // DRAWS //
        ///////////
        $nrDraws = $this->_nrOfGames - $player1Stats['nrWon'] - $player2Stats['nrWon'];
        $percDraws = number_format((100*$nrDraws)/$this->_nrOfGames, 2);
        echo "Draws: $nrDraws ({$percDraws}%)\n\n";
    }
    
    /**
     * Print statistics for player
     * 
     * @param array $stats 
     */
    protected function printStatisticsForPlayer($stats)
    {
        echo " - won: {$stats['nrWon']}/{$this->_nrOfGames} times ({$stats['percWon']}%)\n";
        echo " - fastest win: ".ceil($stats['min']/2)." turns"; 
        if ($stats['minGrid'] != null) {
            echo $stats['minGrid']; 
        }
        echo "\n - slowest win: ".ceil($stats['max']/2)." turns"; 
        if ($stats['maxGrid'] != null) {
            echo $stats['maxGrid']; 
        }
        echo "\n - average number of turns to win: ".$stats['avg']."\n\n";         
    }
    
    /**
     * Calculate statistics for this game for the specified player
     * 
     * @param array $gamesArray
     * @param int $playerConst
     * @return array $stats 
     */
    protected function calculateStatistics($gamesArray, $playerConst)
    {
        $nrWon = 0;

        foreach ($gamesArray as $game) {
            if ($game['winner'] != $playerConst) {
                // we only process won games for the current player
                continue;
            }
            
            $nrWon++;
            $totalWinTurns += ceil($game['moves']/2);

            if (!isset($minMoves) || ($game['moves'] < $minMoves)) {
                $minMoves = $game['moves'];
                $minGrid = $game['grid'];
            }
            
            if (!isset($maxMoves) || ($game['moves'] > $maxMoves)) {
                $maxMoves = $game['moves'];
                $maxGrid = $game['grid'];
            }
        }
        
        $moves = $gamesArray[$playerConst];
        $stats['nrWon']   = $nrWon;
        $stats['percWon'] = number_format((100*$nrWon)/$this->_nrOfGames, 2);
        $stats['min']     = (!isset($minMoves) ? '-' : $minMoves);
        $stats['minGrid'] = (!isset($minMoves) ? null : $minGrid);
        $stats['max']     = (!isset($maxMoves) ? '-' : $maxMoves);
        $stats['maxGrid'] = (!isset($maxMoves) ? null : $maxGrid);
        $stats['avg']     = ($nrWon == 0 ? '-' : number_format($totalWinTurns/ $nrWon, 2));
        
        return $stats;
    }
}

////////////////////////////////////
// PROCESS ARGUMENTS AND RUN GAME //
////////////////////////////////////

error_reporting(E_ALL^E_NOTICE);

include_once './ConnectFour.php';

$processClass = new startConnectFour($argv);
$readyToStart = $processClass->processArguments();
$processClass->showMessages();

if ($readyToStart) {
    $processClass->constructPlayersAndStartGame();
}