<?php

include_once './FourInALinePlayer.php';

class JrgensenV1 extends FourInALinePlayer 
{
	/** 
	 * @return	string
	 */
	public function getName()
	{
		return 'Jrgensen v1';
	}

	/**
	 * @param	array
	 * @return	integer
	 */
	public function getMove(array $grid)
	{
		$this->opponentPlayer = $this->currentPlayer == self::PLAYER_ONE ? self::PLAYER_TWO : self::PLAYER_ONE;
		// cleanup grid array and calculate scores
		$scores = $this->calcScores(array_map('array_filter', $grid));
		// pick a random column with maximum score
		return array_rand(array_flip(array_keys($scores, max($scores))), 1);
	}

	/**
	 * @param	array
	 * @param	integer
	 * @return	array
	 */
	public function calcScores(array $grid, $level = 0) 
	{
		$scores = array();
		for($x = 0; $x < $this->width; $x++) {
			$y = count($grid[$x]);
			if ($y < $this->height && $level < 6) {
				$grid[$x][$y] = ($level % 2) ? $this->currentPlayer  : $this->opponentPlayer;
				// if victory score is 1, otherwise look one level deeper and sum column scores
				$scores[$x] = $this->isWinner($grid, $x, $y, $debug) ? 1 : -1 * array_sum($this->calcScores($grid, $level + 1)) / $this->width;
				unset($grid[$x][$y]);
			}
		}
		return $scores;
	}

	/**
	 * @param	array
	 * @param	integer
	 * @param	integer
	 * @return	boolean
	 */
	public function isWinner(array $grid, $x, $y) 
	{
		// we know where the last disc was dropped, so no reason to search entire grid for a winner
		// patterns are horizontal, vertical, topleft to bottomright and bottomleft to topright
		foreach (array(array(0, 1), array(1, 0), array(-1, 1), array(1, 1)) as $pattern) {
			list($xpattern, $ypattern) = $pattern;
			$count = 1;
			// look in both directions from (x, y)
			foreach (array(1, -1) as $direction) {
				$i = 1;
				do {
					if ($count > 3) {
						// at least 4 in a row, we have a winner
						return true;
					}
					// if (dx, dy) is set and equals (x, y) increase count and continue
					$dx = $x + $direction * $xpattern * $i;
					$dy = $y + $direction * $ypattern * $i++;
				} while (isset($grid[$dx][$dy]) && $grid[$dx][$dy] == $grid[$x][$y] && $count++);
			}
		}
		return false;
	}
}

