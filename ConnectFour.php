<?php

class ConnectFour {
   
   // internal disc values
   const PLAYER_ONE = 1;
   const PLAYER_TWO = 2;   
   const DISC_EMPTY = NULL;
   
   // display values disc
   static protected $_displayValues = array(self::PLAYER_ONE => 'X',
                                             self::PLAYER_TWO => 'O',
                                             self::DISC_EMPTY => '.');

   protected $grid = array();

   protected $last;
   
   protected $width;
   protected $height;

   /**
    * Create an empty grid and store dimensions
    * 
    * @param type $width
    * @param type $height 
    */
   public function __construct ($width, $height) {
       for ($i = 0; $i < $width; $i++) {
           $this->grid[$i] = array();
           for ($j = 0; $j < $height; $j++) {
               $this->grid[$i][$j] = ConnectFour::DISC_EMPTY;
           }
       }
       $this->width = $width;
       $this->height = $height;
   }
   
   /**
    * Do several checks and insert a disc
    * 
    * @param type $column
    * @param type $color
    * @return type 
    */
   public function insert ($column, $color) {
       
       if ($color !== ConnectFour::PLAYER_ONE && $color !== ConnectFour::PLAYER_TWO)
           throw new InvalidArgumentException ("Invalid disk color: $color");

       if ($color === $this->last)
           throw new LogicException ("Invalid game order, don't add disc for Player $color");

       if ($column > count($this->grid) - 1)
           throw new OutOfBoundsException ("Player $color (". ConnectFour::getDiscDisplayValue($color) .") supplied an incorrect value. Column '$column' is out of bounds for grid.");
       
       if (!isset($this->grid[$column]))
           $this->grid[$column] = array();

       $inserted = false;    
       foreach ($this->grid[$column] as &$value)
           if ($value === ConnectFour::DISC_EMPTY) {
               $value = $color;
               $inserted = true;
               break;
           }
           
       if (!$inserted)
           throw new OutOfBoundsException ("Player $color (". ConnectFour::getDiscDisplayValue($color) .") tried to add a disc to column '$column', which is full.");
           
       if (($winner = $this->check ()) !== false) {
           if ($winner !== $color)
               throw new LogicException ("The winner is not the player making the last move. This should not happen.");
               
           return true;
       }
       $this->last = $color;
       return false;
   }
   
   
   /////////////////////////
   // DO WE HAVE A WINNER //
   /////////////////////////
   
   public function check () {
       // each column
       for ($i = 0; $i < $this->width; $i++) {
           // each row
           for ($j = 0; $j < $this->height; $j++) {
               
               if ($this->grid[$i][$j] === ConnectFour::DISC_EMPTY)
                   continue;

               if ($this->horizontal ($i, $j))
                   return $this->grid[$i][$j];
               
               if ($this->vertical ($i, $j))
                   return $this->grid[$i][$j];
               
               if ($this->diagonal ($i, $j))
                   return $this->grid[$i][$j];
           }
       }
       return false;
   }
   
   protected function horizontal ($column, $row) {

       if (!isset ($this->grid[$column + 3][$row]))
           return false;

       if ($this->grid[$column + 1][$row] === $this->grid[$column][$row] &&
           $this->grid[$column + 2][$row] === $this->grid[$column][$row] &&
           $this->grid[$column + 3][$row] === $this->grid[$column][$row]) {
       
           return true;
       }
       return false;
   }
   
   protected function vertical ($column, $row) {

       if (!isset ($this->grid[$column][$row + 3]))
           return false;

       if ($this->grid[$column][$row + 1] === $this->grid[$column][$row] &&
           $this->grid[$column][$row + 2] === $this->grid[$column][$row] &&
           $this->grid[$column][$row + 3] === $this->grid[$column][$row]) {
       
           return true;
       }
       return false;
   }
   
   protected function diagonal ($column, $row) {
       // ascending
       if (isset ($this->grid[$column + 3][$row + 3])) {
           if ($this->grid[$column + 1][$row + 1] === $this->grid[$column][$row] &&
               $this->grid[$column + 2][$row + 2] === $this->grid[$column][$row] &&
               $this->grid[$column + 3][$row + 3] === $this->grid[$column][$row]) {
       
               return true;
           }
       }

       // descending
       if (isset ($this->grid[$column + 3][$row - 3])) {
           if ($this->grid[$column + 1][$row - 1] === $this->grid[$column][$row] &&
               $this->grid[$column + 2][$row - 2] === $this->grid[$column][$row] &&
               $this->grid[$column + 3][$row - 3] === $this->grid[$column][$row]) {
       
               return true;
           }
       }
       return false;
   }

   public function toJson () {
       $s         = new stdClass();
       $s->width  = $this->width;
       $s->height = $this->height;
       $s->grid   = $this->grid;
   
       return json_encode($s);
   }
   
   public function __sleep () {
       return array('grid', 'last', 'width', 'height');
   }
   
   public function getGrid() {
       return $this->grid;
   }
   
   public function display()
   {
       echo $this->getDisplayGrid();
   }
   
   public function getDisplayGrid()
   {
     for ($i = ($this->height-1); $i >= 0; $i--) {
         $displayGrid .= "\n    ";
         for ($j = 0; $j < $this->width; $j++) {
             $char = $this->grid[$j][$i];
             $displayGrid .= self::getDiscDisplayValue($char). " ";;
         }
     }
     
     return $displayGrid;
   }


   /**
    * Convert the internal disc value to a display value
    * 
    * @param int $discValue
    * @return string the display value 
    */
   public static function getDiscDisplayValue($discValue)
   {
       $displayValue = self::$_displayValues[$discValue];
       
       if (!isset($displayValue)) {
           $displayValue = $discValue;
       }
       
       return $displayValue;
   }
}

echo "use 'php startConnectFour.php'\n";