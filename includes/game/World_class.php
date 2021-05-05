<?php

/*
 * Klasa obsługująca informacje o świecie gry
 */

class world
{

    // KLAS
    protected $init;
    protected $db;
    protected $session;
	protected $helper;
    
    public $name;
    public $numBattle;
    public $outbreakWar;
    public $WarDateY;
    public $WarDateM;
    public $WarTurn;
    public $WarDifficulty;
    public $WarMode;
    public $WarWeather;
    public $endWar;
    public $hardGame;
	public $warMap;
	public $warMapNum;
	public $timing;
	public $timeToUp;
	public $timeClock;
    public $startHex = array(1 => array('x' => 122, 'y' => -117), 2 => array('x' => 10, 'y' => -61));
    
   function __construct($init, $db, $session, $helper)
   {
       $this -> init = $init;
       $this -> db = $db -> db;
       $this -> session = $session;
       $this -> helper = $helper;

       return $this;
   }

    public function getData($intWorldID = 0)
    {

        $stats = $this -> db -> query('SELECT * FROM `worlds` WHERE `id`=' . $intWorldID . ' LIMIT 1') -> fetch();

        if ( ! isset($stats['id'])) exit( comp_index14 .'.');

        $ilosctur            = $stats['WarTurn'];
        $rok                 = 1939 + floor(($ilosctur+32)/48);
        $numer_mc            =  floor(($ilosctur%48)/4);
        $miesiac_przesuniety = ($numer_mc + 8)%12;

        $this -> name = $stats['name'];
        $this -> numBattle = $stats['numBattle'];
        $this -> outbreakWar = $stats['outbreakWar'];
        $this -> WarDateY = $rok;
        $this -> WarDateM = $this->helper->changeMonth( $miesiac_przesuniety+1 );
        $this -> WarTurn = $stats['WarTurn'];
        $this -> WarDifficulty = $stats['WarDifficulty'];
        $this -> WarMode = $stats['WarMode'];
        $this -> WarWeather = $stats['WarWeather'];
        $this -> hardGame = $stats['hardGame'];
        $this -> endWar = $stats['endWar'];
		$this -> timing = $stats['timing'];
		$this -> timeToUp = (int)$stats['timeToUp'];
			switch($stats['warMap']){
				case 1;
					$this -> warMap = 'mapav1.jpg';
				break;
				case 2:
					$this -> warMap = 'mapav2.jpg';
				break;
				default:
					$this -> warMap = 'mapav1.jpg';
			}
		$this -> warMapNum = (int)$stats['warMap'];	
		$this -> id = $intWorldID;
		$txt = '';
		$s = round($stats['timeToUp']);
		$this -> timeClock = '';
		if( $s!= 0 ){ 
			$min = $s / 60;				// minuty
			$h = $min / 60;				// godziny
			$sLeft = floor($s  % 60);	// pozostało sekund		
			$minLeft =floor($min % 60);	// pozostało minut
			$hLeft = floor($h);		// pozostało godzin	
			if($hLeft == 0 ){
				$hLeft ='';
			}
			if($minLeft == 0 ){
				$minLeft ='';
			}else if($minLeft < 10 AND $minLeft != 0){
			  $minLeft = "0".$minLeft.':';
			}
			if($sLeft == 0 ){
				$sLeft ='';
			}else if ($sLeft < 10 AND $sLeft != 0 ){
			  $sLeft = "0".$sLeft;
			}
			if( $sLeft > 0 ) {
				$txt ='sek.';
			}
			
			if( $minLeft > 0 ) {
				$txt ='min.';
			}
			
			if( $hLeft > 0 ) {
				$txt ='godz.';
			}
			
			$this -> timeClock = $hLeft."".$minLeft."".$sLeft." ".$txt; //tekst wyswietlony uzytkownikowi
		}
		$stats = null;

        return $this;
    }
	
	public function loginCount($intPlayerID)
	{
		//zapis numeru logowania na dany świat
		$this -> db -> exec('UPDATE `players` SET `logIntoWorld`=( `logIntoWorld`+1 ), `deleteAccount`=0 WHERE `id`='.$intPlayerID.' LIMIT 1');
	}
}
