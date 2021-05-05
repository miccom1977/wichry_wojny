<?php

/*
 * Klasa obsługująca zdarzenia związane z jednostkami
 */

class unit
{

    // KLAS
    protected $init;
    protected $db;
    protected $session;
	protected $account;
	protected $world;
	protected $error;

	function __construct($init, $db, $session, $account, $world, $error)
	{
		$this -> init = $init;
		$this -> db = $db -> db;
		$this -> session = $session;
		$this -> account = $account;
		$this -> world = $world;
		$this -> error = $error;
	
            $this -> landFieldsTypesWeteran = array(
                //podłoża: 1:woda płytka; 2:teren piaszczysty; 3: skały; 5:łąka; 6: teren podłokły; 7:wyzyna; 8: woda głęboka; 9:rzeka; 10: las; 11: nabrzeże

                1 => array(//koszta przejścia dla piechoty
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 2, 1 => 1, 12 => 2, 13 => 1000, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => array( 0 => 3, 1 => 1, 12 => 3, 13 => 1000, 14 => 3, 15 => 3 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    5 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 3, 1 => 3, 12 => 3, 13 => 1000, 14 => 3, 15 => 3 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    7 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => 1000,
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 2, 1 => 1, 12 => 2, 13 => 1000, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                ),
                2 => array(//koszta przejścia dla jendostek pancernych
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 2, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => 1000,
                    5 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => 1000,
                    7 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 2, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => array( 0 => 1000, 18 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 2, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                ),
                3 => array(//koszta przejścia dla jendostek przeciwlotniczych
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 3, 1 => 1, 12 => 1000, 13 => 1000, 14 => 3, 15 => 3 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => 1000,
                    5 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 3, 1 => 1, 12 => 1000, 13 => 1000, 14 => 3, 15 => 3 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    7 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 1000, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => 1000, //0-normalna wartość, 1-most
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 1000, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                ),
                4 => array(//koszta przejścia dla lotnictwa
                    1 => 1,
                    2 => 1,
                    3 => 1,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                    9 => 1,
                    10 => 1,
                    11 => 1,
                ),
                5 => array(//koszta przejścia dla floty
                    1 => 2,//0-normalna wartość, 17- mina pływająca, 18- mina głębinowa
                    2 => 1000,
                    3 => 1000,
                    5 => 1000,
                    6 => 1000,
                    7 => 1000,
                    8 => 1,//0-normalna wartość, 17- mina pływająca, 18- mina głębinowa
                    9 => 1000,//0-normalna wartość, 17- mina pływająca, 18- mina głębinowa
                    10 => 1000,
                    11 => 1000
                ),
                6 => array(//koszta przejścia dla artylerii polowej ( identyczne z jendostkami przeciwlotniczymi
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 3, 1 => 1, 12 => 1000, 13 => 1000, 14 => 3, 15 => 3 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => 1000,
                    5 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 3, 1 => 1, 12 => 1000, 13 => 1000, 14 => 3, 15 => 3 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    7 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 1000, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 2, 1 => 1, 12 => 1000, 13 => 1000, 14 => 2, 15 => 2 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                )
            );

            $this -> landFieldsTypes = array(
                //podłoża: 1:woda płytka; 2:teren piaszczysty; 3: skały; 5:łąka; 6: teren podłokły; 7:wyzyna; 8: woda głęboka; 9:rzeka; 10: las; 11: nabrzeże

                1 => array(//koszta przejścia dla piechoty
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    5 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech,
                    7 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => 1000,
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                ),
                2 => array(//koszta przejścia dla jendostek pancernych
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    5 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    7 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => array( 0 => 1000, 18 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                ),
                3 => array(//koszta przejścia dla jendostek przeciwlotniczych
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    5 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    7 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => 1000, //0-normalna wartość, 1-most
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                ),
                4 => array(//koszta przejścia dla lotnictwa
                    1 => 1,
                    2 => 1,
                    3 => 1,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                    9 => 1,
                    10 => 1,
                    11 => 1,
                ),
                5 => array(//koszta przejścia dla floty
                    1 => 1,//0-normalna wartość, 17- mina pływająca, 18- mina głębinowa
                    2 => 1000,
                    3 => 1000,
                    5 => 1000,
                    6 => 1000,
                    7 => 1000,
                    8 => 1,//0-normalna wartość, 17- mina pływająca, 18- mina głębinowa
                    9 => 1000,//0-normalna wartość, 17- mina pływająca, 18- mina głębinowa
                    10 => 1000,
                    11 => 1000
                ),
                6 => array(//koszta przejścia dla artylerii polowej ( identyczne z jendostkami przeciwlotniczymi
                    1 => array( 0 => 1000, 1 => 1, 17 => 1000 ),//0-normalna wartość, 1- most, 17- mina pływająca
                    2 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    3 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    5 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    6 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    7 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona, 12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    8 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    9 => array( 0 => 1000, 1 => 1 ), //0-normalna wartość, 1-most
                    10 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                    11 => array( 0 => 1, 1 => 1, 12 => 1000, 13 => 1000, 14 => 1, 15 => 1 ), //0-normalna wartość, 1- droga utwardzona,12-smocze zeby, 13-zasieki, 14- mina ppanc,15- minappiech
                )
            );
		return $this;
	}
	
	public function loadUnits($page)
    {
		$per_page=5;
        $sponsorUnitsList= '
		<table style="margin: 0 auto;text-align:center;">
		<tr>';
		$podziel=explode("_",$page);
		$rodzaj_jednostki=$podziel[0];
		$strona = $podziel[1];
		$start = ($strona-1)*$per_page;
		if( $this -> account -> SponsorAccount  >  $this ->init-> time ){
			$getUnits = $this -> db -> query('SELECT `u`.`tacticalDataID`,`u`.`unitType`, `u`.`Specialty`,`u`.`id`,`u`.`unitTurn`,`td`.`nazwa'.$this -> account -> lang.'` as `nazwa`, `u`.`x`, `u`.`y`,`u`.`timing1`, `u`.`timing2` from `units` AS `u` '
			.' LEFT JOIN `TacticalData` AS `td` ON ( `u`.`tacticalDataID`=`td`.`id` ) '
		    .'WHERE `u`.`playerID`=' . $this -> account -> playerID . ' AND `u`.`unitType`='.$rodzaj_jednostki.' AND `u`.`x`!=0 AND `u`.`y`!=0 limit '.$start.', '.$per_page.' ');
				$i=0;
				foreach ($getUnits as $prepareUnitsList)
				{
					$unitsNumber = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$prepareUnitsList['id'].'')->fetch();
					$nowa_nazwa = $this -> ustalNazwe( (int)$unitsNumber[0], $prepareUnitsList['unitType'], $prepareUnitsList['Specialty'], $prepareUnitsList['nazwa']);

					// min - klasa, która umożliwia reakcje na kliknięcice w miniature
					if( $this -> world -> timing == 1 ){
						$intTury = 0;
						if( $prepareUnitsList['timing1'] > $this -> init -> time ){
							if( $prepareUnitsList['timing2'] <= $this -> init -> time ){
								$intTury++;//dodajemy drugą dostepną turę
							}
						}else{
							if( $prepareUnitsList['timing2'] <= $this -> init -> time ){
								$intTury++;//dodajemy drugą dostepną turę
							}
							$intTury++;// dodajemy drugą dostepna turę
							//$this -> error -> printError('można działać.'.$this -> init -> time.'', 'mapa');
						}
						$turki = $intTury;
					}else{
						$turki = $prepareUnitsList['unitTurn'];
					}
					$sponsorUnitsList.= '<td id = "'.$prepareUnitsList['id'].'" class = "minPanel units unit-'.$prepareUnitsList['tacticalDataID'].'" coord="'.$prepareUnitsList['x'].'_'.$prepareUnitsList['y'].'" title="'.$nowa_nazwa.'"><p class="ilosc_tur">'.$turki.'</p></td>';
					$i++;
					if ($i % $per_page == 0){
						$sponsorUnitsList.= '</tr><tr>';
					}
				}
				if($i==0){
					switch($rodzaj_jednostki){
						case '1':
						$sponsorUnitsList.= '<tr><td>'. incl_game_unit1 .'</td></tr>';
						break;
						case '2':
						$sponsorUnitsList.= '<tr><td>'. incl_game_unit2 .'</td></tr>';
						break;
						case '3':
						$sponsorUnitsList.= '<tr><td>'. incl_game_unit3 .'</td></tr>';
						break;
						case '4':
						$sponsorUnitsList.= '<tr><td>'. incl_game_unit4 .'</td></tr>';
						break;
						case '5':
						$sponsorUnitsList.= '<tr><td>'. incl_game_unit5 .'</td></tr>';
						break;
						case '6':
						$sponsorUnitsList.= '<tr><td>'. incl_game_unit6 .'</td></tr>';
						break;
					}
				}
				$sponsorUnitsList.= '</table>';
				$getUnitsCD = $this -> db -> query('SELECT count(*) from `units` WHERE `playerID`=' . $this  -> account -> playerID . ' AND `unitType`='.$rodzaj_jednostki.' AND `x`!=0 AND `y`!=0 ')->fetch();
				if( (int)$getUnitsCD[0] > $per_page ){
				$liczba_stron  = ceil( (int)$getUnitsCD[0]/$per_page);
					if($liczba_stron>$strona){
							$sponsorUnitsList.= '<div id = "'.$rodzaj_jednostki.'_'.($strona+1).'" class="next_page" title="'. incl_game_unit7 .' "></div>';
					}
					if($liczba_stron<=$strona AND $strona>1){
							$sponsorUnitsList.= '<div id = "'.$rodzaj_jednostki.'_'.($strona-1).'" class="rev_page" title="'. incl_game_unit8 .'"></div>';
					}
				}	
		}else{
			$sponsorUnitsList = '<div class="sponsor">'. incl_game_unit9 .'</div>';
		}	
		
		return 	$sponsorUnitsList;
    }
	public function checkStuff($unitID)
	{
		$objHelper = init::getFactory() -> getService('helper');
		$dataStuff = array();
        $dataStuff['txt'] = '';
		$stuffCode='';
        $stuffDATA = $this -> db -> query('SELECT `un`.*, `hq`.*,`un2`.`x` as `xHQ`,`un`.`belongHQ`, `un2`.`y` as `yHQ`,`un`.`x` as `xU`,`un`.`y` as `yU`, `acc`.`login` FROM `HQData` as `hq`
		LEFT JOIN `units` as `un` ON `hq`.`unitsID`=`un`.`belongHQ`
		LEFT JOIN `units` as `un2` ON `hq`.`unitsID`=`un2`.`id`
		LEFT JOIN `players` as `pl` ON `un`.`playerID`=`pl`.`id`
		LEFT JOIN `accounts` as `acc` ON `pl`.`accountID`=`acc`.`id` AND `pl`.`nation`= '.$this  -> account -> nation.'
		WHERE `un`.`id`=' .$unitID. ' AND `pl`.`worldID`=' .$this -> world -> id . ' ')->fetch();
        if( $stuffDATA['belongHQ'] != 0 AND $stuffDATA['belongHQ'] != $unitID )
		{
			$haslo =  SYSTEM_PASSWORD;
			$t["nr_jednostki"]   = $unitID;
			$t["id_sztabu"]      = $stuffDATA['belongHQ'];
			$t["funkcja"]        = 4;//usunięcie jendostki ze sztabu
			$dane = base64_encode(serialize($t));
			$jel = md5($haslo.$dane);
            $odlegloscHQDeffense = $this -> hex_distance(array('x' => $stuffDATA['xHQ'], 'y' => $stuffDATA['yHQ']),array('x' => $stuffDATA['xU'], 'y' => $stuffDATA['yU'] ) );

            //$odlegloscHQDeffense = count($objHelper -> bresenham($stuffDATA['xHQ'], $stuffDATA['yHQ'], $stuffDATA['xU'], $stuffDATA['yU'], false) );
			$title = '';
			if( $stuffDATA['range'] * 10 >= $odlegloscHQDeffense )
			{
				switch($stuffDATA['range'])
				{
					case '1':
					//sztab batalionu
					$title = sztaby5;
					break;
					case '2':
					$title = sztaby6;
					break;
					case '3':
					$title = sztaby7;
					break;
				}
				$stuffCode = '<div id="sztaby"><div class="sztaby_activ" title="'.$title.' '.$stuffDATA['nameStuff'].'"></div><div id="'.$jel.'" var="'.$dane.'" class="sztaby_u sztaby" title="'.sztaby11.'"></div></div>';
			}
			else
			{
				switch($stuffDATA['range'])
				{
					case '1':
					//sztab batalionu
					$title = sztaby8;
					break;
					case '2':
					$title = sztaby9;
					break;
					case '3':
					$title = sztaby10;
					break;
				}
				$stuffCode = '<div id="sztaby"><div class="sztaby_no_activ" title="'.$title.' '.$stuffDATA['nameStuff'].'"></div><div id="'.$jel.'" var="'.$dane.'" class="sztaby_u sztaby" title="'.sztaby11.'"></div></div>';
			}
		}
		else
		{
			
			if( $stuffDATA['belongHQ'] == $unitID )//jednostka jest sztabem, ustalamy punkty bonusu dla poszczególnych korpusów
			{
				//sprawdzam, czy sztab może być rozbudowany
				$suma = $stuffDATA['artillery'] + $stuffDATA['tanks'] + $stuffDATA['infantry'] + $stuffDATA['aircraft'] + $stuffDATA['antiAircraft'] + $stuffDATA['underWater'] + $stuffDATA['waterArtillery'];
				$stuffUp = '';
				switch( $stuffDATA['range'] )
				{				
					case '1':
						if( $suma >= 100 )
						{
							$stuffUp = '<div class="sztabUp">'. incl_game_unit10 .'</div>';	
						}
						else
						{
							$stuffUp = incl_game_unit11 .' '.( 100 - $suma ).' PDS';
						}
					break;
					case '2':
						if( $suma >= 1000 )
						{
							$stuffUp = '<div class="sztabUp">'. incl_game_unit12 .'</div>';	
						}
						else
						{
							$stuffUp = incl_game_unit13 .' '.( 1000 - $suma ).' PDS';
						}
					break;
				}
				
				
				$stuffCode=array(
					'artillery' => $stuffDATA['artillery'],
					'tanks'  => $stuffDATA['tanks'],
					'infantry' => $stuffDATA['infantry'],
					'aircraft'  => $stuffDATA['aircraft'],
					'antiAircraft' => $stuffDATA['antiAircraft'],
					'underWater'  => $stuffDATA['underWater'],
					'waterArtillery'  => $stuffDATA['waterArtillery'],
					'stuffPoints' => ( $stuffDATA['artillery'] + $stuffDATA['tanks'] + $stuffDATA['infantry'] + $stuffDATA['aircraft'] + $stuffDATA['antiAircraft'] + $stuffDATA['underWater'] + $stuffDATA['waterArtillery'] ),
					'stuffUp' => $stuffUp,
                    'nameStuff' => $stuffDATA['nameStuff']
				);
			}
			else
			{
				//sprawdzamy, czy jendostka jest już zgłoszona do jakiegoś sztabu
				$hqWait = $this -> db -> query('SELECT * FROM `HQwaiting` WHERE `unitsID`='.$unitID.' LIMIT 1')->fetch();
				if( $hqWait['ID'] )
				{
					$haslo =  SYSTEM_PASSWORD;
					$t["nr_jednostki"]   = $unitID;
					$t["id_sztabu"]      = $stuffDATA['HQID'];
					$t["funkcja"]        = 2;//anulowanie żadania przyłączenia do sztabu
					$dane = base64_encode(serialize($t));
					$jel = md5($haslo.$dane);
					$stuffCode ='<div id="sztaby"><div class="sztaby_ocz sztaby" title="'.sztaby12.''.$stuffDATA['HQID'].'"></div>
					<div id="'.$jel.'" var="'.$dane.'" class="sztaby_del sztaby" title="'.sztaby13.'"></div></div>';
				}
				else
				{
					//$stuffCode = 'jednostka nie jest w sztabie, wyszukujemy sztaby któe mogą przyjąć nową jendostkę ';
					$dane = $this -> db -> query('SELECT `un2`.`x` as `xU`,`un2`.`y` as `yU`,`un`.`x` as `xHQ`, `un`.`y` as `yHQ`,`hq`.`range`,`hq`.`nameStuff`,`hq`.`unitsID` FROM `HQData` as `hq`
					LEFT JOIN `units` as `un` ON `hq`.`unitsID`=`un`.`id`
					LEFT JOIN `units` as `un2` ON `un2`.`id`='.$unitID.'
					LEFT JOIN `players` as `pl` ON `un`.`playerID`=`pl`.`id`
					WHERE `un`.`x`!=0 AND  `un`.`y`!=0 AND `pl`.`worldID`=' .$this -> world -> id . ' ' );
					foreach ( $dane as $stuffDATA)
					{
						$i = 0;
                        $odlegloscHQDeffense = $this -> hex_distance(array('x' => $stuffDATA['xHQ'], 'y' => $stuffDATA['yHQ']),array('x' => $stuffDATA['xU'], 'y' => $stuffDATA['yU'] ) );
                        //$odlegloscHQDeffense = count($objHelper -> bresenham($stuffDATA['xHQ'], $stuffDATA['yHQ'], $stuffDATA['xU'], $stuffDATA['yU'], false) );
						if( $stuffDATA['range'] * 10 >= $odlegloscHQDeffense )
						{
							$dane = $this -> db -> query('SELECT count(*) FROM `units` WHERE `belongHQ`='.$stuffDATA['unitsID'].' ')->fetch();
							if( $stuffDATA['range'] * 10 > (int)$dane[0] - 1 )
							{
								$haslo =  SYSTEM_PASSWORD;
								$t["nr_jednostki"]   = $unitID;
								$t["id_sztabu"]      = $stuffDATA['unitsID'];
								$t["funkcja"]        = 1;//wysłanie żadania przyłączenia do sztabu
								$dane = base64_encode(serialize($t));
								$jel = md5($haslo.$dane);
								$stuffCode .='<div id="'.$jel.'" var="'.$dane.'" class="sztaby" title="'.sztaby14.' '.$stuffDATA['nameStuff'].'"></div>';
								$i++;
							}
						}
						if( $i == 6 ){
							break;
						}
					}
				}
			}
		}
        return $stuffCode;
	}

	/*
	 * Metody potrzebne przy przekształceniach na hexach
	*/
	private function hex_to_cube($x, $y)
	{
    	$z = ($x+$y);
    	return array('x' => $x, 'y' => $y, 'z' => $z);
	}
	private function cube_distance($from, $to)
	{
		return max(abs($from['x'] - $to['x']), abs($from['y'] - $to['y']), abs($from['z'] - $to['z']));
	}
	public function hex_distance($a, $b)
	{
		$ac = $this -> hex_to_cube($a['x'], $a['y']);
    	$bc = $this -> hex_to_cube($b['x'], $b['y']);
    	return $this -> cube_distance($ac, $bc);
	}

	/*
	 * funkcja sprawdza zasięg ruchu jednostki
	 * tożsama z funkcją w JS
	 */

	private function checkUnitsRange($params, $arrUnitData, $arrVisionMapData)
	{
		//$i = 1;
		if( $params['hardGame'] == 3){
			$landFields = $this -> landFieldsTypesWeteran;
		}else{
			$landFields = $this -> landFieldsTypes;
		}
		
		
		
		$arrPoints = array(array('x' => $params['moveFromX'], 'y' => $params['moveFromY'], 'distance' => 0));
		$arrFields = array();
		while (count($arrPoints) > 0) {
			$startRange = 1;
			$round = -1;
			$countX = 1;

			// coords
			$x = $arrPoints[0]['x'];
			$y = $arrPoints[0]['y'];
			$distance = $arrPoints[0]['distance'];

			while ($round <= $startRange) {
				//  y dla 0
				if ($round == 0) $coordY = $startRange * ($countX % 2 == 0 ? -1 : 1);
				// y dla maxów
				if ($round == ($startRange * -1) || $round == $startRange) $coordY = ($round < 0 ? ($countX - 1) * -1 : ($countX - 1));
				elseif ($round != 0 && $round != ($startRange * -1) && $round != $startRange) {
					if ($round < 0) $coordY = ($countX % 2 == 0 ? $round - ($startRange + $round) : ($round + $startRange));
					else $coordY = ($countX % 2 == 0 ? $round + ($startRange - $round) : ($round - $startRange));
				}

				$moveForward = 1;
				if (isset($arrVisionMapData[($x + $round) . ':' . ($y + ($coordY * -1))])) {
					if (is_array($landFields[$arrUnitData['CorpsId']][$arrVisionMapData[($x + $round) . ':' . ($y + ($coordY * -1))]['fieldType']])) {
						$moveForward = $landFields[$arrUnitData['CorpsId']][$arrVisionMapData[($x + $round) . ':' . ($y + ($coordY * -1))]['fieldType']][$arrVisionMapData[($x + $round) . ':' . ($y + ($coordY * -1))]['fieldCustom']];
					} else {
						$moveForward = $landFields[$arrUnitData['CorpsId']][$arrVisionMapData[($x + $round) . ':' . ($y + ($coordY * -1))]['fieldType']];
					}
					// Przez jednostki nie można przechodzić
					if (isset($arrVisionMapData[($x + $round) . ':' . ($y + ($coordY * -1))]['unitID'])) $moveForward = 1000;
				}

				$newDistance = $distance + $moveForward;

				if ($newDistance <= $arrUnitData['unitRange']) {
					if (empty($arrFields[($x + $round) . ':' . ($y + ($coordY * -1))])) {
						$arrFields[($x + $round) . ':' . ($y + ($coordY * -1))] = $newDistance;  // list of valid fields
						$arrPoints[] = array('x' => $x + $round, 'y' => $y + ($coordY * -1), 'distance' => $newDistance); // cache valid fields
						// check that field is finded
						if (isset($arrFields[$params['moveToX'] . ':' . $params['moveToY']]))
						{
							//echo $params['moveFromX'].':'.$params['moveFromY'].' ['.$newDistance.'] '.($x + $round) . ':' . ($y + ($coordY * -1));
							return true;
						}
					}
					elseif ($arrFields[($x + $round) . ':' . ($y + ($coordY * -1))] > $newDistance)
					{
						$arrFields[($x + $round) . ':' . ($y + ($coordY * -1))] = $newDistance;  // list of valid fields
						$arrPoints[] = array('x' => $x + $round, 'y' => $y + ($coordY * -1), 'distance' => $newDistance); // cache valid fields
						// check that field is finded
						if (isset($arrFields[$params['moveToX'] . ':' . $params['moveToY']]))
						{
							//echo $params['moveFromX'].':'.$params['moveFromY'].' ['.$newDistance.'] '.($x + $round) . ':' . ($y + ($coordY * -1));
							return true;
						}
					}
				}

				if (($countX == ($startRange + 1) && ($round == ($startRange * -1) || $round == $startRange)) || ($countX == 2 && $round != ($startRange * -1) && $round != $startRange)) {
					$round++;
					$countX = 0;
				}
				$countX++;
			}
			array_shift($arrPoints);
			//$i = $i + 1;
			//if ($i > 10000) break;
		}
		return false;
	}

	/*
	 * Funkcja sprawdza które jednostki znikają z pola widzenia jednostki a które są w zasięgu sojuszników nadal
	 * $unitID = ID jednostki która się przesuwa lub znika
	 * $x = Pole x na które jednostka się przesuwa lub z którego znika
	 * $y = Pole y na które jednostka się przesuwa lub z którego znika
	 */
	public function	checkNotSeenUnits($unitID, $x, $y, $removeUnit = false)
	{
		$arrNotSeenUnits = [];
		$theySeeMeBefore = false; // wróg widział mnie przed ruchem
		$theySeeMeStil = false; // Wróg mnie nie widzi

		// Jednostki, które widziała przesunięta jednostka ale już może ich nie widzieć a one jej
		$arrCheckMySeen = $this -> db -> query('SELECT `r`.`id`, `r`.`unitID`, `r`.`unitRange`, `r`.`seenUnitID`, `r`.`seenUnitRange`, `u`.`x`, `u`.`y`, `u2`.`x` AS `x2`, `u2`.`y` AS `y2` FROM `units_range` AS `r`
                LEFT JOIN `units` AS `u` ON `u`.`id`=`r`.`seenUnitID`
                LEFT JOIN `units` AS `u2` ON `u2`.`id`=`r`.`unitID`
                WHERE `r`.`unitID`='.$unitID.' || `r`.`seenUnitID`='.$unitID.' AND `u`.`worldID`='. $this -> world -> id .' AND `u2`.`worldID`='. $this -> world -> id .'  ORDER BY `r`.`id` ASC');
		if ($removeUnit === false)
		{
			foreach ($arrCheckMySeen as $row)
			{
				if ($row['unitID'] == $unitID) // Moja jednostka
				{
					if ($this -> hex_distance(array('x' => $x, 'y' => $y), array('x' => $row['x'], 'y' => $row['y'])) > $row['unitRange']) // Nie widzimy juz tej jednostki usuwamy wpis
					{
						$this -> db -> exec('DELETE FROM `units_range` WHERE `id`='.$row['id'].' LIMIT 1');
						$arrNotSeenUnits[$row['seenUnitID']] =  array('removeUnit' => true, 'id' => $row['seenUnitID'], 'x' => $row['x'], 'y' => $row['y']);
					}
				}
				else // Przeciwnik widzi mnie
				{
					if ($this -> hex_distance(array('x' => $x, 'y' => $y), array('x' => $row['x2'], 'y' => $row['y2'])) > $row['unitRange']) // Ta jednostka już nas nie widzi
					{
						$this->db->exec('DELETE FROM `units_range` WHERE `id`=' . $row['id'] . ' LIMIT 1');
						$theySeeMeBefore = true;
					}
					else // Te jednostki nas widzą o ile są
					{
						$theySeeMeBefore = true;
						$theySeeMeStil = true;
					}
				}
			}
		}
		else
		{
			foreach ($arrCheckMySeen as $row) {
				if ($row['unitID'] == $unitID) // Moja jednostka
				{
					$this->db->exec('DELETE FROM `units_range` WHERE `id`=' . $row['id'] . ' LIMIT 1');
					$arrNotSeenUnits[$row['seenUnitID']] = array('removeUnit' => true, 'id' => $row['seenUnitID'], 'x' => $row['x'], 'y' => $row['y']);
				} else // Przeciwnik nie widzi już mnie bo jestem trup
				{
					$this->db->exec('DELETE FROM `units_range` WHERE `id`=' . $row['id'] . ' LIMIT 1');
					$theySeeMeBefore = true;
				}
			}
		}
		$arrCheckMySeen -> closeCursor();

		// Sprawdzam czy jednostki wroga, których już nie widzę są widoczne dla kogoś z sojuszników
        $arrAllySeenEnemy = [];
        if (count($arrNotSeenUnits) > 0)
		{
            //$arrAllySeenEnemy = array();//jeśli nie został spełniony powyższy warunek wywalałow  logu błąd że nie ma tablicy $arrAllySeenEnemy
			$arrCheckNotSeenUnits = $this -> db -> query('SELECT `r`.`unitID`, `r`.`seenUnitID`, `u`.`id` FROM `units_range` AS `r`
 					INNER JOIN `units` AS `u` ON `u`.`id`=`r`.`unitID`
					WHERE `r`.`seenUnitID` IN ('.implode(',', array_keys($arrNotSeenUnits)).')');
			foreach ($arrCheckNotSeenUnits as $row)
			{
				$arrAllySeenEnemy[$row['seenUnitID']] = array($row['seenUnitID'], $row['unitID']);
			}
			$arrCheckNotSeenUnits -> closeCursor();
			$arrNotSeenUnits = array_diff_key ( $arrNotSeenUnits, $arrAllySeenEnemy  ); // te jednostki powinny zniknąć z pola widzenia sojuszników
		}

		return ['arrNotSeenUnits' => $arrNotSeenUnits, 'theySeeMeBefore' => $theySeeMeBefore, 'theySeeMeStil' => $theySeeMeStil];
	}


	/*
     * Metoda sprawdza jakie hexy widzi jednostka
     */

	public function checkUnitsVision($x, $y, $range, $arrViewedArea, $arrVisionMapData = array())
	{
		$startRange = 1;
		while ($startRange <= $range) {
			$round = ($startRange * -1);
			$countX = 1;
			while ($round <= $startRange) {
				//  y dla 0
				if ($round == 0) $coordY = $startRange * ($countX % 2 == 0 ? -1 : 1);
				// y dla maxów
				if ($round == ($startRange * -1) || $round == $startRange) $coordY = ($round < 0 ? ($countX - 1) * -1 : ($countX - 1));
				elseif ($round != 0 && $round != ($startRange * -1) && $round != $startRange)
				{
					if ($round < 0) $coordY = ($countX % 2 == 0 ? $round - ($startRange + $round) : ($round + $startRange));
					else $coordY = ($countX % 2 == 0 ? $round + ($startRange - $round) : ($round - $startRange));
				}
				if (empty($arrViewedArea[($x + $round) . ':' . ($y + ($coordY * -1))])) $arrViewedArea[($x + $round) . ':' . ($y + ($coordY * -1))] = array('x' => $x + $round, 'y' => $y + ($coordY * -1));

				if (($countX == ($startRange + 1) && ($round == ($startRange * -1) || $round == $startRange)) || ($countX == 2 && $round != ($startRange * -1) && $round != $startRange))
				{
					$round ++;
					$countX = 0;
				}
				$countX ++;
			}
			$startRange ++;
		}
		return $arrViewedArea;
	}

	/*
	 * Funkcja przesuwa jednostkę, pobiera dane o sojusznikach
	 *
	 *@ params
	 * array(
          'unitID' => int, // ID jednostki
          'moveToX' => int, // X docelowy
          'moveToY' => int,  // Y docelowy
          'fromCity' => true|false,  // jednostka wystawiana z miasta
          'moveFromX' => int, // Koordynaty miasta
          'moveFromY' => int, // Koordynaty miasta
          'rounds' => 1-5 // Liczba tur ruchu
       )
	 */
	public function getMovedUnitData( $params = array() )
	{
		$objHelper = init::getFactory() -> getService('helper');
		// Pobieram dane jednostki
		
		$arrUnitData = $this -> db -> query('SELECT `u`.*, `acc`.`login` AS `nickName`, `t`.`NationId`, `t`.`widocznosc`, `t`.`ruch`, `t`.`CorpsId`,`t`.`nazwa'.$this -> account -> lang .'` as `nazwa` FROM `units` AS `u` '
            . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
            . 'LEFT JOIN `players` AS `pl` ON (`u`.`playerID`=`pl`.`id`) '
            . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
            . 'WHERE `u`.`id`=' . $params['unitID'] . ' AND `u`.`playerID`=' . $this -> account -> playerID . ' LIMIT 1') -> fetch();
			
        if ( ! isset($arrUnitData['id'])) $this -> error -> printError( incl_game_unit14 .'.', 'mapa');
		
		if( $this -> world -> timing == 1 ){
			// sprawedzam, czy licnziki upłynęły
			$intTury = 0;
			if( $arrUnitData['timing1'] > $this -> init -> time ){
				if( $arrUnitData['timing2'] <= $this -> init -> time ){
					$intTury++;//dodajemy drugą dostepną turę
				}
			}else{
				if( $arrUnitData['timing2'] <= $this -> init -> time ){
					$intTury++;//dodajemy drugą dostepną turę
				}
				$intTury++;// dodajemy drugą dostepna turę
				//$this -> error -> printError('można działać.'.$this -> init -> time.'', 'mapa');
			}
			//$this -> error -> printError('można działać.'.$intTury, 'mapa');
			if ($intTury < $params['rounds']) $this -> error -> printError( incl_game_unit15 .'.', 'mapa');
		}else{
			if ($arrUnitData['unitTurn'] < $params['rounds']) $this -> error -> printError( incl_game_unit15 .'.', 'mapa');
		}
		
		// Przesuwam z miasta, aktualizuje x i y jednostki o koordynaty miasta
		if ($params['fromCity'] === true)
		{
			$arrUnitData['x'] = $params['moveFromX'];
			$arrUnitData['y'] = $params['moveFromY'];
		}
		
		//pobiramy wartość zasiegu ruchu plutonu
		$attackTacticData = $this->db->query('select min(`td`.`ruch`) as `ruch` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`unitsID` =' . $params['unitID'] . ' ')->fetch();
		$ruch = $attackTacticData['ruch'];
		
		if( $params['desant'] == 1) { // skacze desantowiec, przez to ruch ma 100 heksów
			$ruch = 100;
		}
	
		//obniżamy wartość nominalną ruchu jednostki
		if( $this -> world -> hardGame > 1 AND $params['desant'] == 0 )//jeśli trudnosć swiata jest różna od KADET, obliczamy wpływ pogody na ruch
        {
            if ($arrUnitData['Specialty'] == 18)//zanurzony okręt podwodny traci 25% wartości ruchu
            {
                $ruch = round($ruch * 0.75);
            }
            if ($arrUnitData['CorpsId'] == 1 AND in_array($this->world->WarWeather, array(1, 3, 6)) == true)//piechota, woda w kamaszach to nic dobrego
            {
                $ruch = round($ruch * 0.75);
            }
            if ($arrUnitData['CorpsId'] == 5 AND $arrUnitData['Specialty'] != 18 AND in_array($this->world->WarWeather, array(2, 7)) == true)//wynurzony okręt podwodny lub jednostka nawodna
            {
                $ruch = round($ruch * 0.50);
            }
            if ($arrUnitData['CorpsId'] == 4 AND in_array($this->world->WarWeather, array(1, 2, 8)) == true)// lotnictwo, zła pogoda- ruch = 1
            {
                $ruch = round($ruch * 0.10);
            }
        }

		$attackTacticData = null;

		// Liczba pól do przeskoczenia podczas ruchu
		$intTurnFields = $params['rounds'] * $ruch;
		
		if( $arrUnitData['idLC'] != 0 ){
			$intTurnFields = 1000;
		}
		
		// Sprawdzam czy jednostka ma w zasięgu pole na które chce się ruszyć
		
		$arrVisionMapData = $this -> db -> query('SELECT `m`.*, `u`.`id` AS `unitID` FROM `mapData` AS `m`
                LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`)
				LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id` )
                WHERE `m`.`x`<' . ($arrUnitData['x'] + $intTurnFields) . ' AND `m`.`y`>' . ($arrUnitData['y'] - $intTurnFields) . ' AND `m`.`x`>'.($arrUnitData['x'] - $intTurnFields).' AND `m`.`y`<'.($arrUnitData['y'] + $intTurnFields).' AND `u`.`worldID`='. $this -> world -> id .'  AND `pl`.`nation` != '. $this -> account -> nation .' ');
		$arrPartMapData = [];
		
		foreach ($arrVisionMapData as $row)
		{
			$arrPartMapData[$row['x'].':'.$row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'unit' => $row['unitID'], 'fieldType' => $row['fieldType'], 'fieldCustom' => $row['fieldCustom']);
		}
		$arrVisionMapData -> closeCursor();
		if ($this -> checkUnitsRange(array('moveFromX' => $arrUnitData['x'], 'moveFromY' => $arrUnitData['y'], 'moveToX' => $params['moveToX'], 'moveToY' => $params['moveToY'], 'hardGame' => $this -> world -> hardGame ), array('unitRange' => $intTurnFields, 'CorpsId' => $arrUnitData['CorpsId']), $arrPartMapData) === false ) $this -> error -> printError( incl_game_unit16 .'.', 'mapa');
		
        /* Obsługuję zasięgi widoczności jednostek */
        $arrAllySeen = []; // Jednostki wroga widziane przez sojuszników

		$arrNotSeenData = $this -> checkNotSeenUnits($arrUnitData['id'], $params['moveToX'], $params['moveToY']);

        $arrNotSeenUnits = $arrNotSeenData['arrNotSeenUnits']; //Jednostki, których już nie widzi jednostka po ruchu
        $theySeeMeBefore = $arrNotSeenData['theySeeMeBefore']; // wróg widział mnie przed ruchem
        $theySeeMeStil = $arrNotSeenData['theySeeMeStil']; // Wróg mnie nie widzi

        // Dodajemy jednostki do widzialnych jednostek
		// Wg największego zasięgu widzenia = 9
		
		$arrCheckUnit = $this -> db -> query('SELECT `id` FROM `units` WHERE `x`=' . $params['moveToX'] . ' AND `y`=' . $params['moveToY'] . ' AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        if (isset($arrCheckUnit['id'])) $this -> error -> printError( comp_map14 .'.', 'mapa');
        $arrCheckUnit = null;
		
		
        $enemyUnits = $this -> db -> query('SELECT `u`.`id`, `u`.`x`, `u`.`y`, `u`.`DefensePoints`, `u`.`CityName`, `u`.`playerID`, `pl`.`playerVacation`, `u`.`UnderWater`, `t`.`nazwa`, `u`.`unitType`, `u`.`Specialty`, `u`.`tacticalDataID`, `t`.`widocznosc`, `t`.`obrazek_duzy`, `t`.`NationId`, `r`.`id` AS `seen`, `r2`.`id` AS `seen2`, `acc`.`login` AS `nickName` FROM `units` AS `u`
                LEFT JOIN `TacticalData` AS `t` ON `t`.`id`=`u`.`tacticalDataID`
                LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id`)
                LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`)
                LEFT JOIN `units_range` AS `r` ON (`r`.`seenUnitID`=`u`.`id` AND `r`.`unitID`='.$arrUnitData['id'].')
                LEFT JOIN `units_range` AS `r2` ON (`r2`.`unitID`=`u`.`id` AND `r2`.`seenUnitID`='.$arrUnitData['id'].')
                WHERE `u`.`worldID`='. $this -> world -> id .' AND `u`.`x`<=' . ($params['moveToX'] + 9) . ' AND `u`.`y`>=' . ($params['moveToY'] - 9) . ' AND `u`.`x`>='.($params['moveToX'] - 9).' AND `u`.`y`<='.($params['moveToY'] + 9).' AND `t`.`NationId` IN ('.($this -> account -> nation == 1 ? '7, 8, 9' : '1, 2, 3, 4, 5').') ');
				
        foreach ($enemyUnits as $row)
        {
			if ($this -> hex_distance(array('x' => $params['moveToX'], 'y' => $params['moveToY']), array('x' => $row['x'], 'y' => $row['y'])) <= (int)$arrUnitData['widocznosc']) // Nasza jednostka widzi przeciwnika
            {
                if (!$row['seen']) // Wcześniej go nie widziała
                {
					if ($row['Specialty'] != 18) // Okrętów zanurzonych nie mozna zobaczyć
					{
						$this -> db -> exec('INSERT INTO `units_range` (`unitID`, `unitRange`, `seenUnitID`, `seenUnitRange`) VALUES ('.$arrUnitData['id'].', '.(int)$arrUnitData['widocznosc'].', '.$row['id'].', '.(int)$row['widocznosc'].')');
						if (!isset($arrAllySeen[$row['x'] . ':' . $row['y']]))
						{
                            $vac = 0;
                            if( $row['playerVacation'] > $this -> init -> time )
                            {
                                $vac = date("Y-m-d H:i:s", $row['playerVacation']);
                            }
							$arrAllySeen[$row['x'] . ':' . $row['y']] = array('removeUnit' => false, 'addUnit' => true, 'id' => $row['id'], 'x' => $row['x'], 'y' => $row['y'], 'nation' => ($row['NationId'] != 7 && $row['NationId'] != 8 && $row['NationId'] != 9 ? 1 : 2), 'unitType' => $row['unitType'], 'uid' => $row['tacticalDataID'], 'owner' => $row['playerID'], 'td'=> array($row['tacticalDataID']), 'Specialty' => $row['Specialty'], 'UnderWater' => $row['UnderWater'], 'DefensePoints'=>$row['DefensePoints'], 'nickName'=> $row['nickName'],'un'=>'','nazwa' => $row['nazwa'], 'CityName' => $row['CityName'], 'playerVacation' => $vac );
						}
					}
                }
            }
            if ($this -> hex_distance(array('x' => $params['moveToX'], 'y' => $params['moveToY']), array('x' => $row['x'], 'y' => $row['y'])) <= (int)$row['widocznosc']) // Przeciwnik widzi naszą jednostkę
            {
                if (!$row['seen2']) // Wcześniej go nie widział
                {
                    $this -> db -> exec('INSERT INTO `units_range` (`unitID`, `unitRange`, `seenUnitID`, `seenUnitRange`) VALUES ('.$row['id'].', '.(int)$row['widocznosc'].', '.$arrUnitData['id'].', '.(int)$arrUnitData['widocznosc'].')');
                    $theySeeMeStil = true;
                }
                    else
                {
                    $theySeeMeStil = true;
                }
            }
        }
        $enemyUnits -> CloseCursor();
		
		//sprawdzam, czy pojazd inżynierów  może postawić miasto
		$cityStand = 0;
		$rowFieldMin = $this -> db -> query('SELECT * FROM `mapData` WHERE `x`='.$params['moveToX'].' AND `y`='.$params['moveToY'].' AND `worldID`='.$this-> world -> id.' LIMIT 1') -> fetch();
		
		
		if( $arrUnitData['Specialty'] == 1 )//sprawdzam, czy można na dany heks postawić miasto
		{
			$cityStand = 1;
			if ($this -> account -> nation == 1)
			{
				if ($params['moveToX'] < 97 || $rowFieldMin['fieldType'] == 1 || $rowFieldMin['fieldType'] == 8 || $rowFieldMin['fieldType'] == 9) $cityStand = 0;
			}
			elseif ($this -> account -> nation == 2)
			{
				if ($params['moveToX'] > 35 || $rowFieldMin['fieldType'] == 1 || $rowFieldMin['fieldType'] == 8 || $rowFieldMin['fieldType'] == 9) $cityStand = 0;
			}
		}
		
		if( $params['desant'] == 1 )//sprawdzam, czy skoczek skoczył
		{
			$this -> db -> exec('UPDATE `units` SET `Specialty` = 20 WHERE `id`=' . $arrUnitData['id'] . ' LIMIT 1');
			$arrUnitData['Specialty'] = 20;
		}
		
		if( $arrUnitData['Specialty'] == 20 )//sprawdzam, czy skoczek skoczył
		{
			$desant = 0;
			if ( $this -> account -> nation == 1 AND $params['moveToX'] > 97 )
			{
				$desant = 1;
			}
			elseif ( $this -> account -> nation == 2 AND $params['moveToX'] < 35 )
			{
				$desant = 1;
			}
			
			if($desant == 1 ){
				$this -> db -> exec('UPDATE `units` SET `Specialty` = 3 WHERE `id`=' . $arrUnitData['id'] . ' LIMIT 1');
				$arrUnitData['Specialty'] = 3;
			}
		}
		
		
		$unitsINT = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$arrUnitData['id'].' ')->fetch();
        $nazwa = $this -> ustalNazwe( (int)$unitsINT[0], $arrUnitData['unitType'], $arrUnitData['Specialty'], $arrUnitData['nazwa'] );

		/* kod obsługujący miny */
			$wynik_wybuchu = 0; 
			$wybuch_miny = 0;
			if( $rowFieldMin['fieldCustom'] == 14 AND $arrUnitData['unitType'] == 2 AND $rowFieldMin['fieldNation'] != $this-> account -> nation )
			{
				$wybuch_miny = 1;
			}
			if( $rowFieldMin['fieldCustom'] == 15 AND ( $arrUnitData['unitType'] == 1 OR $arrUnitData['unitType'] == 3 OR  $arrUnitData['unitType'] == 6 ) AND $rowFieldMin['fieldNation'] != $this-> account -> nation )
			{
				$wybuch_miny = 1;
			}
			if( $rowFieldMin['fieldCustom'] == 17 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] != 18 AND $rowFieldMin['fieldNation'] != $this-> account -> nation )
			{//ednostka morska wpłynęła na minę pływajacą
				$wybuch_miny = 1;
			}
			if( $rowFieldMin['fieldCustom'] == 18 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] == 18 AND $rowFieldMin['fieldNation'] != $this-> account -> nation )
			{//ednostka morska wpłynęła na minę głębinową
				$wybuch_miny = 1;
			}
			$turyPoAkcji = $arrUnitData['unitTurn']-$params['rounds'];
			if( $this -> world -> timing == 1 ){
				$turyPoAkcji = $intTury-$params['rounds'];
			}
			
			if( $wybuch_miny == 1 )
			{
				//losuje zdarzenie
				$t_zdarzenie = array(
					"0"=>3,//atrata 100% pancerza
					"1"=>4,//strata wszystkich tur
					"2"=>2,//strata 50% pancerza
					"3"=>1 //nic się nie dzieje
				); 
				foreach($t_zdarzenie as $pozycja_zdarzenie=>$waga_zdarzenie) 
				{
				  for($i_zdarzenie=0;$i_zdarzenie<$waga_zdarzenie; $i_zdarzenie++) $tab_zdarzenie[] = $pozycja_zdarzenie;
				}	 
				srand((double) microtime()*1000000);
				shuffle($tab_zdarzenie);
				$wylosowane_zdarzenie= $tab_zdarzenie[0];
					switch( $wylosowane_zdarzenie )
					{
						case 0://zniszczenie jednostki...
							$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=0 WHERE `x`=' . $params['moveToX']. ' AND `y`=' .$params['moveToY']. ' AND `worldID`='.$this-> world->id.' LIMIT 1');
								$wynik_wybuchu = wybuch6;
								if($rowFieldMin['fieldCustom'] == 17 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] != 18 )
								{//ednostka morska wpłynęła na minę pływajacą
									$wynik_wybuchu = wybuch10 .' '. wybuch11;
								}
								if( $rowFieldMin['fieldCustom'] == 18 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] == 18 )
								{//ednostka morska wpłynęła na minę głębinową
									$wynik_wybuchu = wybuch10 .' '. wybuch11;
								}
								$this->usun_jednostke($params['unitID'], $rowFieldMin['fieldPlayerID'], 'mina');
						break;
						case 1://strata wszystkich tur
							$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=0 WHERE `x`=' . $params['moveToX']. ' AND `y`=' .$params['moveToY']. ' AND `worldID`='.$this-> world->id.' LIMIT 1');
							if( $this -> world -> timing == 1 ){
								$this -> db -> exec('UPDATE `units` SET `timing1`=('. ($this -> init -> time + $this -> world -> timeToUp).' ), timing2 =('. ($this -> init -> time + $this -> world -> timeToUp).' ) WHERE `id`='.$arrUnitData['id'].' LIMIT 1');
							}else{
								$this -> db -> exec('UPDATE `units` SET `unitTurn`= 1 WHERE `id`='.$arrUnitData['id'].' LIMIT 1');
							}
							//ture jedną odejmujemy podczas wykonania ruchu
							$turyPoAkcji = 0;
							$wynik_wybuchu = wybuch7;	
							if( $rowFieldMin['fieldCustom'] == 17 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] != 18 )
							{//ednostka morska wpłynęła na minę pływajacą
								$wynik_wybuchu = wybuch10 .' '. wybuch12;
							}
							if( $rowFieldMin['fieldCustom'] == 18 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] == 18 )
							{//ednostka morska wpłynęła na minę głębinową
								$wynik_wybuchu = wybuch10 .' '. wybuch13;
							}						
							//$raporttekst_minera="Witaj $minotworca<br />
							//$nazwa_atakujacego gracza $user wpadł na twoją minę w sektorze $poziom_litera-$pion_liczba i koordzie $koordfin. W ostatniej chwili dowódca jednostki rozpoznał pole minowe. Żołnierze zostali poważnie poranieni, musz± poczekać na pomoc medyczną!  Odniesione przez nich obrażenia uniemożliwiają dalszy marsz...";   
							//$resultraport_atakujacy = mysql_query("INSERT INTO raporty (gracz, data, atakujacy, jednostka_atakowana, jednostka_atakujaca, nr_atakowanej, widziane, raport, rodzaj_raportu) VALUES ('$minotworca','$datateraz', '$user', '$nazwa_atakujacego', 'MINA', '$num','NIE','$raporttekst_minera' ,'4')" );
						break;
						case 2://strata 50% pancerza
							$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=0 WHERE `x`=' . $params['moveToX']. ' AND `y`=' .$params['moveToY']. ' AND `worldID`='.$this-> world->id.' LIMIT 1');
							$this -> db -> exec('UPDATE `units` SET `DefensePoints`=( `DefensePoints`/2 ) WHERE `id`='.$arrUnitData['id'].' LIMIT 1');
							$wynik_wybuchu = wybuch8;
							if( $rowFieldMin['fieldCustom'] == 17 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] != 18 )
							{//ednostka morska wpłynęła na minę pływajacą
								$wynik_wybuchu = wybuch10 .' '. wybuch14;
							}
							if( $rowFieldMin['fieldCustom'] == 18 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] == 18 )
							{//ednostka morska wpłynęła na minę głębinową
								$wynik_wybuchu = wybuch10 .' '. wybuch15;
							}
							//$raporttekst_minera="Witaj $minotworca<br />
							//$nazwa_atakujacego gracza $user wpadł na twoją minę w sektorze $poziom_litera-$pion_liczba i koordzie $koordfin. Jednostka ta została poważnie uszkodzona, musi zostać naprawiona! Stoi bezczynnie...";  
							//$resultraport_atakujacy = mysql_query("INSERT INTO raporty (gracz, data, atakujacy, jednostka_atakowana, jednostka_atakujaca, nr_atakowanej, widziane, raport, rodzaj_raportu) VALUES ('$minotworca','$datateraz', '$user', '$nazwa_atakujacego', 'MINA', '$num','NIE','$raporttekst_minera' ,'4')" );
						break;
						case 3://nic się nie dzieje
							$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=0 WHERE `x`=' . $params['moveToX']. ' AND `y`=' .$params['moveToY']. ' AND `worldID`='.$this-> world->id.' LIMIT 1');
							$wynik_wybuchu = wybuch9;
							if( $rowFieldMin['fieldCustom'] == 17 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] != 18 )
							{//ednostka morska wpłynęła na minę pływajacą
								$wynik_wybuchu = wybuch16;
							}
							if( $rowFieldMin['fieldCustom'] == 18 AND $arrUnitData['unitType'] == 5 AND $arrUnitData['Specialty'] == 18 )
							{//ednostka morska wpłynęła na minę głębinową
								$wynik_wybuchu = wybuch17;
							}
						break;		
					}
			}
			
		//$wynik_wybuchu['info']='przeszkoda='.$rodzaj_przeszkody.', id jendostki='.$idJednostki_mina.',zanurzenie_mina='.$zanurzenie_mina.', wybuch miny='.$wybuch_miny.',wylosowanie zdarzenie='.$wylosowane_zdarzenie.'';
		
		/* koniec kodu obsługującego miny */
		
		//zmiana danych po wybuchu miny
		$removeUnit = false;
		$sojusznikWidzi = true;
		$wrogWidzi = false;
		$timing1 = $params['licznik1'];
		$timing2 = $params['licznik2'];
		
		
		
		if( $wybuch_miny == 1 )
		{
			switch($wylosowane_zdarzenie){
				case 0:
					$turyPoAkcji = 0;
					$removeUnit = true;
					$sojusznikWidzi = false;
					$wrogWidzi = false;
				break;
				case 1:
					$turyPoAkcji = 0;
					$timing1 = $this -> init -> time + $this -> world -> timeToUp;
					$timing2 = $this -> init -> time + $this -> world -> timeToUp;
					
				break;
				case 2:
					$arrUnitData['DefensePoints'] = round( $arrUnitData['DefensePoints']/2 );
				break;
			}
		}
		
		
		/* 
			kod nagród po najechaniu na heks fieldPremium = 1
		
		lista nagród 
		
		1: super jednostka
		2: Sponsor 7 dni
		3: sponsor 30 dni
		4: złoto 10szt.
		5: złoto 50szt.
		6: złoto 100 szt.
		7: skracanie produkcji 10 minut
		8: skracanie produkcji 60 minut
		9: skracanie produkcji 4 godziny
		10: skracanie badania 10 minut
		11: skracanie badania 60 minut
		12: skracanie badania 4 godziny
		13: skracanie wszystkiego 10 minut
		14: skracanie wszystkiego 60 minut
		15: skracanie wszystkiego 4 godziny
		
		
		*/
		
		$premiumTXT = ' ';
		//$rowFieldMin['fieldPremium'] = 1;
		if( $rowFieldMin['fieldPremium'] == 1 ){
			// gracz stanał na pole Premium, wykonujemy losowanie nagrody
			$prize = rand(1,15);
			//$prize = 11;
			switch( $prize ){
				case 1:
					// super jednostka
					// losujemy jednostkę w roku między 1944 a rokiem 45
					if ($this->account->nation == 1) {
						$warunek_losowanie = "(`NationId`=1 OR `NationId`=2 OR `NationId`=3 OR `NationId`=4 OR `NationId`=5 )";
					} else {
						$warunek_losowanie = "(`NationId`=7 OR `NationId`=8 OR `NationId`=9 )";
					}
					
					$statsListPromo = $this->db->query('SELECT * FROM `TacticalData` WHERE `rocznik`>= 1944 AND ' . $warunek_losowanie . ' AND `CorpsId`!=5 ORDER BY RAND() LIMIT 1')->fetch();
                       
					$this->account->buildUnit($statsListPromo['id'], $this->account->playerID, 0);
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( jednostka <strong>'. $statsListPromo['nazwa'] .'</strong> ) została dodana do Twoich jednostek.<br> Przejdź do miasta i wystaw jednostkę na mapę ';				
				break;
				case 2:
					//bon na sponsor 1 dzień
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 1,86400, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na 1 dzień konta Sponsor ) została dodana do Twojego zasobnika';
				break;
				case 3:
					// bon na sponsor 7 dni
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 1, 604800, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na 7 dni konta Sponsor ) została dodana do Twojego zasobnika';
				break;
				case 4:
					// złoto 5 szt.
					$this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', 5 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( 5 sztuk złota ) została dodana do Twojego zasobnika';
				break;
				case 5:
					// złoto 7 szt.
					$this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', 7 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( 7 sztuk złota ) została dodana do Twojego zasobnika';
				
				break;
				case 6:
					// zoto 15 szt.
					$this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', 15 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( 15 sztuk złota ) została dodana do Twojego zasobnika';
				
				break;
				case 7:
					// skracanie produkcji 10 minut
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 2, 600, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie produkcji jednostki o 10 minut ) została dodana do Twojego zasobnika';
				break;
				case 8:
					// skracanie produkcji 60 minut
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 2, 3600, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie produkcji jednostki o 60 minut  ) została dodana do Twojego zasobnika';
				break;
				case 9:
					// skracanie produkcji 4 godziny
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 2, 14400, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie produkcji jednostki o 4 godziny  ) została dodana do Twojego zasobnika';
				break;
				case 10:
					// skracanie wszystkiego 10 minut
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 3, 600, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie czasu opracowywania technologii o 10 minut  ) została dodana do Twojego zasobnika';
				break;
				case 11:
					// skracanie wszystkiego 60 minut
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 3, 3600, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie czasu opracowywania technologii o 60 minut   ) została dodana do Twojego zasobnika';
				break;
				case 12:
					// skracanie wszystkiego 4 godziny
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 3, 14400, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie czasu opracowywania technologii o 4 godziny ) została dodana do Twojego zasobnika';
				break;
				case 13:
					// skracanie wszystkiego 10 minut
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 4, 600, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie czasu oczekiwania o 10 minut  ) została dodana do Twojego zasobnika';
				break;
				case 14:
					// skracanie wszystkiego 60 minut
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 4, 3600, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie czasu oczekiwania o 60 minut   ) została dodana do Twojego zasobnika';
				break;
				case 15:
					// skracanie wszystkiego 4 godziny
					$this->db->query('INSERT INTO `prize` (`playerID`, `prizeNumber`, `prizeTime`, `prizeUsed` ) values ('.$this -> account -> playerID.', 4, 14400, 0 )');
					$premiumTXT = 'Gratulacje :) Twoja jednostka stanęła na polu Premium!<br> Nagroda ( Bon na skrócenie czasu oczekiwania o 4 godziny ) została dodana do Twojego zasobnika';
				break;
			}
			//kasujemy pole premium
			$this -> db -> exec('UPDATE `mapData` SET `fieldPremium`=0 WHERE `fieldID`=' . $rowFieldMin['fieldID'] . ' LIMIT 1 ');
		}
		
		
		/* 
			koniec kodu pól premium
		*/
		
		
		
		
		
		
		
        $arrPlutons = array();
        $arrDataCount = $this -> db -> query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits` WHERE `connectUnit`='.$arrUnitData['id'].'');
		foreach ($arrDataCount as $rowTDA)
        {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
        $arrDataCount -> closeCursor();

		// Tablica z danymi dla sojuszników
		$arrUnitSendInfo = array(
			'removeUnit' => $removeUnit,
			'sojusznikWidzi' => $sojusznikWidzi,
			'wrogWidzi' => $wrogWidzi,
			'moveFromX' => $arrUnitData['x'],
			'moveFromY' => $arrUnitData['y'],
			'x' => $params['moveToX'],
			'y' => $params['moveToY'],
			'id' => $arrUnitData['id'],
			'view' => $arrUnitData['widocznosc'],
			'fromCity' => $params['fromCity'],
			'nation' => $this->account->nation,
            'unitType' => $arrUnitData['unitType'],
			'uid' => $arrUnitData['tacticalDataID'],
			'owner' => $this->account->playerID,
			'td' => (isset($arrPlutons[ $arrUnitData['id'] ]) ? $arrPlutons[ $arrUnitData['id'] ] : [] ),
			'Specialty' => $arrUnitData['Specialty'],
			'unitTurn' => $turyPoAkcji,
			'DefensePoints' => $arrUnitData['DefensePoints'],
			'FieldArtillery' => $arrUnitData['FieldArtillery'],
			'Torpedo' => $arrUnitData['Torpedo'],
			'Morale' => $arrUnitData['Morale'],
			'experience' => $arrUnitData['unitExperience'],
			'UnderWater' => $arrUnitData['UnderWater'],
			'nickName' => $arrUnitData['nickName'],
			'belongHQ' => $this->checkStuff($arrUnitData['id']),
            'cityStand' => $cityStand,
            'nazwa' => $nazwa,
			'mina' => $wynik_wybuchu,
			'timing1'=> $timing1,
			'timing2'=> $timing2,
			'premium' => $premiumTXT
		);
		// Do przeciwników wysyłamy prawie to samo ale mogą się zmienić parametry widoczności
		$arrEnemyUnitSendInfo = array(
			'removeUnit' => $removeUnit,
			'sojusznikWidzi' => false,
			'wrogWidzi' => $wrogWidzi,
			'x' => $params['moveToX'],
			'y' => $params['moveToY'],
			'id' => $arrUnitData['id'],
			'nation' => $this->account->nation,
            'unitType' => $arrUnitData['unitType'],
			'uid' => $arrUnitData['tacticalDataID'],
			'owner' => $this->account->playerID,
			'td' => array($arrUnitData['tacticalDataID']),
			'Specialty' => $arrUnitData['Specialty'],
			'DefensePoints' => $arrUnitData['DefensePoints'],
            'nazwa' => $nazwa,
			'nickName' => $arrUnitData['nickName']
		);
		$cityData = array(
			'x' => $arrUnitData['x'],
			'y' => $arrUnitData['y'],
			'view' => 6,
			'nation' => $this->account->nation,
			'type' => 'c',
            'unitType' => 7,
			'owner' => $this->account->playerID,
			'CityName' => 'nazwa miasta',
			'nickName' => 'nick gracza',
			'DefensePoints'=>  100
		);

		
		/* wysyłam do wroga informację o jednostce */
		if ($theySeeMeStil === true) {
			$arrEnemyUnitSendInfo['wrogWidzi'] = true;
		} /* jednostka znika wrogowi z pola widzenia */
		elseif ($theySeeMeStil === false && $theySeeMeBefore === true) {
			$arrEnemyUnitSendInfo['removeUnit'] = true;
		}
		$daneTestowe = [];
		$daneTestowe['removeUnit'] = $this->checkNotSeenUnits($arrUnitData['id'], $params['moveToX'], $params['moveToY']);
        //samouczek, sprawdzenie, czy gracz wykonał ruch
        $samouczek = [];
        $samouczek['playerID'] = $this -> account -> playerID;
        $samouczek['aktiv'] = 0;
		$goldNagroda = 0;
        if( $this -> account -> tutorialStage == 1 )//gracz ma wystawić wszystkie jendostki na mapkę
        {
            $arrUnitCount = $this -> db -> query('SELECT count(*) FROM `units` WHERE `x`=0 AND `y`=0 AND `playerID`='.$this->account->playerID.' ' )->fetch();
			if( (int)$arrUnitCount[0] == 1) {
				$samouczek['tresc'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek"> ' . mapa_tut1 . '
                        <br></div>';
				$samouczek['heightWindow'] =360;
				$this->db->exec('UPDATE `accounts` SET `tutorialStage`=2 WHERE `id`=' . $this->account->id . ' LIMIT 1');
				$goldNagroda = 20;
				$samouczek['aktiv'] = 1;
			}
            
        }
		else if( $this -> account -> tutorialStage == 6 )//gracz ma wykonać jeden ruch
        {
            if( $params['rounds'] > 1 )//gracz wykonał od razu kumulację ruchu
            {
                $samouczek['tresc'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index95 .'!<br>
                </div>';
                $samouczek['heightWindow'] =360;
				$goldNagroda = 20;
                $this -> db -> exec('UPDATE `accounts` SET `tutorialStage`=8 WHERE `id`=' . $this -> account -> id . ' LIMIT 1');
            }
            else
            {
                $samouczek['tresc'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index94 .'!<br>
                    </div>';
                $samouczek['heightWindow'] =360;
				$goldNagroda = 10;
                $this -> db -> exec('UPDATE `accounts` SET `tutorialStage`=7 WHERE `id`=' . $this -> account -> id . ' LIMIT 1');
            }
            $samouczek['aktiv'] = 1;
        }
        else if( $this -> account -> tutorialStage == 7 )//gracz ma wykonać kumulację ruchu
        {
            if( $params['rounds'] > 1 )
            {
                $samouczek['tresc'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index95 .' !<br>
                </div>';
                $samouczek['heightWindow'] =505;
				$goldNagroda = 20;
                $this -> db -> exec('UPDATE `accounts` SET `tutorialStage`=8 WHERE `id`=' . $this -> account -> id . ' LIMIT 1');
            }
            $samouczek['aktiv'] = 1;
        }

		$samouczek['goldNagroda'] = 0;
		if ( $goldNagroda > 0 )
		{
			$samouczek['goldNagrodaTXT'] = comp_index101 .' '.$goldNagroda.' '. sztuk_zlota_txt .'!<br>
					<div id="getGold">'. comp_account75 .'</div>';
			$this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', '.$goldNagroda.' )');
			$samouczek['goldNagroda'] = $goldNagroda;
			
		}
		
        $arrayData = array('activity' => 'unitMove', 'chanData' => array(
            // Sojusznicy
            array('chanName' => 'worldmap'.$this -> world -> id.'nation'.$this -> account -> nation,
                'data' => array(
                    'moveUnitData' => $arrUnitSendInfo,
					'enemyUnitsData' => $arrAllySeen+$arrNotSeenUnits, // Jednostki które sojusznicy widzą i dopiero zobaczą
					'cityData' => $cityData,
                    'samouczek' => $samouczek,
					'daneTestowe' => $daneTestowe
                )
            ),
            // Przeciwnicy.
            array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
                'data' => array(
                    'moveUnitData' => $arrEnemyUnitSendInfo,
					'cityData' => $cityData,
                    'samouczek' => $samouczek,
					'daneTestowe' => $daneTestowe
                )
            ),
        ));
		

        $objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
        echo json_encode(true);
        $arrUnitData = null;
	}

	/*
	 * Funkcja odpowiada za wykonanie działań na jednostce
	 * $intUnitID = ID jednostki na której wykonujemy działania
	 * $updateType = Typ działania jakie wykonujemy np. zanurzenie
	 */
	public function updateUnitData($intUnitID, $updateType, $zmienne)
	{
		$objHelper = init::getFactory() -> getService('helper');
		$fieldAdd = false;
		$arrUnitData = $this -> db -> query('SELECT `u`.*, `acc`.`login` AS `nickName`, `t`.`NationId`, `t`.`widocznosc`, `t`.`ruch`, `t`.`CorpsId`,`t`.`nazwa'.$this->account->lang.'` as `nazwa` FROM `units` AS `u` '
			. 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
			. 'LEFT JOIN `players` AS `pl` ON (`u`.`playerID`=`pl`.`id`) '
			. 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
			. 'WHERE `u`.`id`=' . $intUnitID . ' AND `u`.`playerID`=' . $this -> account -> playerID . ' LIMIT 1') -> fetch();
		if ( ! isset($arrUnitData['id'])) $this -> error -> printError( incl_game_unit14 .'.', 'mapa');

		// Sprawdzam czy jednostka jest widoczna dla wroga
		$intIsSeen = false;
		$arrCheckNotSeenUnits = $this -> db -> query('SELECT `seenUnitID` FROM `units_range` WHERE `seenUnitID`='.$arrUnitData['id'].' LIMIT 1') -> fetch();
		if (isset($arrCheckNotSeenUnits['seenUnitID'])) $intIsSeen = true;
		$arrCheckNotSeenUnits = null;

		$errorTXT = false;
		$alert = '';

        $arrPlutons = array();
        $arrDataCount = $this -> db -> query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits` WHERE `connectUnit`='.$arrUnitData['id'].' ');
        foreach ($arrDataCount as $rowTDA)
        {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
        $arrDataCount -> closeCursor();

        $nazwa = $this -> ustalNazwe(  count( $arrPlutons[ $arrUnitData['id'] ] ), $arrUnitData['unitType'], $arrUnitData['Specialty'], $arrUnitData['nazwa'] );
        $arrUnitSendInfo = array(
			'removeUnit' => false,
			'addUnit' => false,
			'x' => $arrUnitData['x'],
			'y' => $arrUnitData['y'],
			'id' => $arrUnitData['id'],
			'view' => $arrUnitData['widocznosc'],
			'fromCity' => false,
			'nation' => $this -> account -> nation,
            'unitType' => $arrUnitData['unitType'],
			'td' => (isset($arrPlutons[ $arrUnitData['id'] ]) ? $arrPlutons[ $arrUnitData['id'] ] : array() ),
			'owner' => $this -> account -> playerID,
			'uid' => array($arrUnitData['tacticalDataID']),
			'Specialty' => $arrUnitData['Specialty'],
			'unitTurn' => $arrUnitData['unitTurn'],
			'DefensePoints' => $arrUnitData['DefensePoints'],
			'FieldArtillery' => $arrUnitData['FieldArtillery'],
			'Torpedo' => $arrUnitData['Torpedo'],
			'Morale' => $arrUnitData['Morale'],
			'experience' => $arrUnitData['unitExperience'],
			'UnderWater' => $arrUnitData['UnderWater'],
			'nickName' => $arrUnitData['nickName'],
			'belongHQ' => $this -> checkStuff( $arrUnitData['id'] ),
            'nazwa' => $nazwa
		);

		/*
		 * Tutaj można wykonać działania na jednostce
		 */
		$up1 = 0;
		$up2 = 0;
		$licznik1 = 0;
		$licznik2 = 0;
		if( $this -> world -> timing == 1 ){// jeśli świat poligon ( na razie tylko tam są zegarki )
			// sprawdzam, czy licnziki sie wyzerowały
			$liczniki = $this -> db -> query('SELECT `timing1`, `timing2` FROM `units` WHERE `id`= '.$intUnitID.' AND `worldID`='. $this -> world -> id .'  LIMIT 1') -> fetch();	
			if( $liczniki['timing1'] > $this -> init -> time ){
				if( $liczniki['timing2'] > $this -> init -> time ){
					$this -> error -> printError( comp_battleE2 .'.', 'mapa');
					$licznik1 = $liczniki['timing1'];
					$licznik2 = $liczniki['timing2'];
				}else{
					$licznik2 = $this -> init -> time + $this -> world -> timeToUp;
					$up2 = 1;
				}
				$licznik1 = $liczniki['timing1'];
			}else{
				$licznik1 = $this -> init -> time + $this -> world -> timeToUp;
				$licznik2 = $liczniki['timing2'];
				$up1 = 1;
				//$this -> error -> printError('można działać.'.$this -> init -> time.'', 'mapa');
			}
		}
		
		$updateLiczniki = '`timing1` = 0,`timing2`=0';
		//zapisujemy dane jednostki
		if($up1==1){
			$updateLiczniki = '`timing1`='.$licznik1;
		}
		if($up2==1 AND $up1==1 ){
			$updateLiczniki = '`timing1`='.$licznik1.',`timing2`='.$licznik2;
		}
		if($up2==1 AND $up1==0 ){
			$updateLiczniki = '`timing2`='.$licznik2;
		}
		
		$arrUnitSendInfo['timing1'] = $licznik1;
		$arrUnitSendInfo['timing2'] = $licznik2;		
		
		if($this -> world -> timing == 0){
			$updateLiczniki = '`unitTurn`=(`unitTurn`-1)';
			if( $arrUnitData['unitTurn'] > 0 ){
				$up1 = 1;
			}
		}
			
			switch ($updateType)
			{
				case 'podwodniak':
					if( $arrUnitData['UnderWater'] == 1 AND $arrUnitData['Specialty'] != 18 AND ( $up1 == 1 OR $up2 == 1) )
					{//jednostka się zanurza
						$field = $this -> db -> query('SELECT `fieldType`, `fieldCustom` FROM `mapData` WHERE `x`='.$arrUnitData['x'].' AND `y`='.$arrUnitData['y'].' AND `worldID`='. $this->world->id .' LIMIT 1')->fetch();
						if( $field['fieldType'] == 1 OR $field['fieldType'] == 9 )
						{
							$this -> error -> printError( wynurzony_n_dec_txt, 'mapa');//zanurzenie niemożliwe, za płytka woda
						}
						else
						{
							// Jednostka przestaje być widoczna dla innych
							$this->db->exec('DELETE FROM `units_range` WHERE `seenUnitID`=' . $arrUnitData['id'] . '');
							// Usuwam jednostkę bo się zanurzyła
							$arrUnitSendInfo['removeUnit'] = true;
							$this -> db -> exec('UPDATE `units` SET `Specialty`=18, '.$updateLiczniki.'  WHERE `id`=' .$intUnitID. ' LIMIT 1');
							$arrUnitSendInfo['Specialty'] = 18;
							$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							if( $this -> account -> sounds == 1 )
							{
								$arrUnitSendInfo['sound'] = 'diving';
							}
						}
					}
					else if( $arrUnitData['UnderWater'] == 1 AND $arrUnitData['Specialty'] == 18 AND ( $up1 == 1 OR $up2 == 1) )
					{//jednostka się wynurza
						// Dodajemy jednostke do widzialnych jednostek
						// Wg największego zasięgu widzenia = 9
						$enemyUnits = $this -> db -> query('SELECT `u`.`id`, `u`.`x`, `u`.`y`, `t`.`widocznosc`, `t`.`obrazek_duzy` FROM `units` AS `u`
						LEFT JOIN `TacticalData` AS `t` ON `t`.`id`=`u`.`tacticalDataID`
						WHERE `u`.`worldID`='. $this -> world -> id .' AND `u`.`x`<' . ($arrUnitData['x'] + 9) . ' AND `u`.`y`>' . ($arrUnitData['y'] - 9) . ' AND `u`.`x`>'.($arrUnitData['x'] - 9).' AND `u`.`y`<'.($arrUnitData['y'] + 9).' AND `t`.`NationId` IN '.($this -> account -> nation == 2 ? '(7, 8, 9)' : '(1, 2, 3, 4, 5)').' ');
						foreach ($enemyUnits as $row)
						{
							if ($this -> hex_distance(array('x' => $arrUnitData['x'], 'y' => $arrUnitData['y']),array('x' => $row['x'], 'y' => $row['y'] ) ) <= $row['widocznosc']) // Przeciwnik widzi naszą jednostkę
							{
								$arrUnitSendInfo['removeUnit'] = false;
								$arrUnitSendInfo['addUnit'] = true;
								$this -> db -> exec('INSERT INTO `units_range` (`unitID`, `unitRange`, `seenUnitID`, `seenUnitRange`) VALUES ('.$row['id'].', '.$row['widocznosc'].', '.$arrUnitData['id'].', '.$arrUnitData['widocznosc'].')');
							}
						}
							$enemyUnits -> CloseCursor();
							$this -> db -> exec('UPDATE `units` SET `Specialty`=0, '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
							$arrUnitSendInfo['Specialty'] = 0;
							$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
					}
				
					break;
				case 'odbudowa_jednostka':
					//odbudowa pancerza jendostki
					if( $up1 == 1 OR $up2 == 1 )
					{
						if( $arrUnitData['Specialty'] == 6 )// stanowisko CKM lub fortyfikacja artyleryjska
						{
							if( $arrUnitData['DefensePoints'] == 200 )
							{
								//odsyłamy do tej samej strony bo nie ma żadnej akcji do wykonania
								$this -> error -> printError( incl_game_unit17, 'mapa');
							}
							else if( $arrUnitData['DefensePoints'] >= 190 AND $arrUnitData['DefensePoints'] < 200 )
							{
								$this -> db -> exec('UPDATE `units` SET `DefensePoints`=200, '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
								$arrUnitSendInfo['DefensePoints'] = 200;
								$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							} 
							else if( $arrUnitData['DefensePoints'] < 190 )
							{
								$this -> db -> exec('UPDATE `units` SET `DefensePoints`=( `DefensePoints`+10 ), '.$updateLiczniki.'  WHERE `id`=' .$intUnitID. ' LIMIT 1');
								$arrUnitSendInfo['DefensePoints'] = $arrUnitData['DefensePoints'] + 10;
								$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							}
						}
						else
						{
							if( $arrUnitData['DefensePoints'] == 100 )
							{
								//odsyłamy do tej samej strony bo nie ma żadnej akcji do wykonania
								$this -> error -> printError( incl_game_unit17, 'mapa');
							}
							else if( $arrUnitData['DefensePoints'] >= 90 AND $arrUnitData['DefensePoints'] < 100 )
							{
								$this -> db -> exec('UPDATE `units` SET `DefensePoints`=100, '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
								$arrUnitSendInfo['DefensePoints'] = 100;
								$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							}
							else if( $arrUnitData['DefensePoints'] < 90 )
							{
								$this -> db -> exec('UPDATE `units` SET `DefensePoints`=( `DefensePoints`+10 ), '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
								$arrUnitSendInfo['DefensePoints'] = $arrUnitData['DefensePoints'] + 10;
								$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							}
						}
					}
					else//nie ma dostępnych tur działania
					{
						$this -> error -> printError( incl_game_unit18, 'mapa');
					}
					
					break;
				case 'saper':
					$dane = $this -> db -> query('SELECT `fieldType`,`fieldCustom` FROM `mapData` WHERE `x`='.$zmienne['x'].' AND `y`='.$zmienne['y'].'  AND `worldID`='.$this -> world -> id.' LIMIT 1 ')->fetch();
						$mina = 0;
						$rev_kasa = 0;
					$cena = 0;
					switch($zmienne['mina'])
						{
							case '1'://stawianie smoczych zębów
								if( ( $dane['fieldType'] != 1 AND $dane['fieldType'] != 8 AND $dane['fieldType'] != 9 ) AND $dane['fieldCustom'] == 0 AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 150 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=12 WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = zapora_txt.' '.$zmienne['x'].','.$zmienne['y'];
									//pomniejszyć kasę gracza
									$cena = 150;
									$mina = 12;
								}
								else
								{
									$errorTXT = zaporan_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.niemozliwe_txt;
								}
							break;
							case '2'://stawianie zasieków
								if( ( $dane['fieldType'] != 1 AND $dane['fieldType'] != 8 AND $dane['fieldType'] != 9 ) AND $dane['fieldCustom'] == 0 AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 75 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=13 WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = zasieki_txt2.''.$zmienne['x'].','.$zmienne['y'];
									$cena = 75;
									$mina = 13;
								}
								else
								{
									$errorTXT = zasiekin_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.niemozliwe_txt;
								}
							break;
							case '3'://stawianie miny ppanc
								if( ( $dane['fieldType'] != 1 AND $dane['fieldType'] != 8 AND $dane['fieldType'] != 9 ) AND $dane['fieldCustom'] == 0 AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 50 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=14, `fieldNation`='.$this -> account -> nation.', `fieldPlayerID`='.$this -> account -> playerID.' WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = zaminowano_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.mina_ppanc_txt2;
									$cena = 50;
									$mina = 14;
								}
								else
								{
									$errorTXT = zaminowanie_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.mina_ppanc_txt2.' '.niemozliwe_txt;
								}
							break;
							case '4'://stawianie miny ppiech
								if( ( $dane['fieldType'] != 1 AND $dane['fieldType'] != 8 AND $dane['fieldType'] != 9 ) AND $dane['fieldCustom'] == 0  AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 25 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=15, `fieldNation`='.$this -> account -> nation.', `fieldPlayerID`='.$this -> account -> playerID.' WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = zaminowano_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.mina_ppiech_txt2 ;
									$cena = 25;
									$mina = 15;
								}
								else
								{
									$errorTXT = zaminowanie_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.mina_ppiech_txt2.' '.niemozliwe_txt;
								}
							break;
							case '5'://stawianie przęsła mostu
								if( ( $dane['fieldType'] == 1 OR $dane['fieldType'] == 9 ) AND $dane['fieldCustom'] == 0  AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 500 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=1,`points`=100 WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = przeslo_txt.' '.$zmienne['x'].','.$zmienne['y'];
									$cena = 500;
									$mina = 1;
								}
								else
								{
									$errorTXT = przeslon_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.niemozliwe_txt;
								}
								
							break;
							case '6'://stawianie miny pływającej
								if( ( $dane['fieldType'] == 1 OR $dane['fieldType'] == 8 OR $dane['fieldType'] == 9 ) AND $dane['fieldCustom'] == 0  AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 100 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=17, `fieldNation`='.$this -> account -> nation.', `fieldPlayerID`='.$this -> account -> playerID.' WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. '  AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = minpl_txt.' '.$zmienne['x'].','.$zmienne['y'];
									$cena = 100;
									$mina = 17;
								}
								else
								{
									$errorTXT = minpln_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.niemozliwe_txt;
								}
								
							break;
							case '7'://stawianie miny głębinowej
								if( $dane['fieldType'] == 8 AND $dane['fieldCustom'] == 0  AND ( $this -> account -> softCurrency + $this -> account -> PremiumCurrency ) >= 100 )
								{
									$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=18, `fieldNation`='.$this -> account -> nation.', `fieldPlayerID`='.$this -> account -> playerID.' WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
									$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
									$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
									//$alert = mingl_txt.' '.$zmienne['x'].','.$zmienne['y'];
									$cena = 100;
									$mina = 18;
								}
								else
								{
									$errorTXT = mingln_txt.' '.$zmienne['x'].','.$zmienne['y'].' '.niemozliwe_txt;
								}
								
							break;
							case '8'://usuwanie przeszkód
								if( $dane['fieldCustom'] != 0 )
								{
									switch( $dane['fieldCustom'] )
										{
											case '12':
												$rev_kasa = 75;
											break;
											case '13':
												$rev_kasa = 38;
											break;
											case '14':
												$rev_kasa = 25;
											break;
											case '15':
												$rev_kasa = 13;
											break;
											case '1':
												$rev_kasa = 250;
											break;
											case '17':
												$rev_kasa = 50;
											break;
											case '18':
												$rev_kasa = 50;
											break;
										}
										if( $arrUnitData['unitType'] == 5 AND $dane['fieldCustom'] < 15 )
										{//próba rozminowania trałowcem przeszkody lądowej 
											$errorTXT = incl_game_unit19 .' '.$zmienne['x'].','.$zmienne['y'].'. '. incl_game_unit20;
										}
										else if ( $arrUnitData['unitType'] == 1 AND $dane['fieldCustom'] > 15 )
										{//próba rozminowania saperem przeszkody morskiej
											$errorTXT = incl_game_unit21 .' '.$zmienne['x'].','.$zmienne['y'].'. '. incl_game_unit22;
										}
										else
										{	
											$this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`+'.$rev_kasa.') WHERE `id`='.$this -> account -> playerID.' LIMIT 1 ');
											$this -> db -> exec('UPDATE `mapData` SET `fieldCustom`=0,`points`=0 WHERE `x`=' .$zmienne['x']. ' AND  `y`=' .$zmienne['y']. ' AND `worldID`='.$this -> world -> id.' LIMIT 1');
											$alert = usunieto_txt.' '.$zmienne['x'].','.$zmienne['y'].'. '. incl_game_unit23 .'  '.$rev_kasa.' '. panel_gry6;
											//potrącam dwie tury z puli sapera za rozminowanie
											$this -> db -> exec('UPDATE `units` SET '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
											$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
										}
								}
							break;
						}
						if( $zmienne['mina'] != 8 )//dodajemy przeszkodę
						{
							$softCurrency    = 0;
							$premiumCurrency = 0;
							if( $errorTXT == false )
							{	
								if( $cena <= ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) )
								{
									if ( $this -> account -> softCurrency >= $cena )
									{
										$this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-' . $cena . ') WHERE `id`=' . $this -> account -> playerID . ' ');
										$softCurrency    = $cena;
										$premiumCurrency = 0;
									}
									else
									{
										if ($this -> account -> softCurrency >= 0 AND $this -> account -> softCurrency < $cena )
										{
											//gracz nie ma kasy soft, więc zerujemy jego wartość
											$this -> db -> exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this -> account -> playerID . ' ');
											//i pomniejszamy ilość premiumCurrency
											$new_PremiumCurrency = $cena - $this -> account -> softCurrency;
											$softCurrency        = $this -> account -> softCurrency;
											$premiumCurrency     = $new_PremiumCurrency;
											
											$this -> db -> exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $new_PremiumCurrency . ' ) WHERE `id`=' . $this -> account -> id . ' ');
										}
									}
								}
								else
								{
									
								}
							}
								$fieldAdd = array(
									'field' => true,
									'x'               => $zmienne['x'],
									'y'               => $zmienne['y'],
									'fieldCustom'     => $mina,
									'fieldType'       => $dane['fieldType'],
									'fieldTxt'        => $this -> okresl_podloze( $dane['fieldType'] ),
									'info'            => $alert,
									'error'           => $errorTXT,
									'owner'           =>  $this -> account -> playerID,
									'softCurrency'    => $softCurrency,
									'PremiumCurrency' => $premiumCurrency
								);
						}
						else//usuwamy przeszkodę
						{
							$fieldAdd = array(
								'field'        => false,
								'x'            => $zmienne['x'],
								'y'            => $zmienne['y'],
								'fieldCustom'  => $mina,
								'fieldType'    => $dane['fieldType'],
								'fieldTxt'     => $this -> okresl_podloze( $dane['fieldType'] ),
								'info'         => $alert,
								'error'        => $errorTXT,
								'owner'        => $this -> account -> playerID,
								'softCurrency' => $rev_kasa
							);
						}
						
					break;
				case 'artyleria':
					if( $arrUnitData['FieldArtillery'] == 1 )
					{
						if( $arrUnitData['Specialty'] == 17 AND ( $up1 == 1 OR $up2 == 1 ) )
						{//jednostka rozłożona do strzału
							$this -> db -> exec('UPDATE `units` SET `Specialty`=0, '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
							$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							$arrUnitSendInfo['Specialty'] = 0;
						}
						else if( $up1 == 1 OR $up2 == 1 )
						{
							$this -> db -> exec('UPDATE `units` SET `Specialty`=17, '.$updateLiczniki.' WHERE `id`=' .$intUnitID. ' LIMIT 1');
							$arrUnitSendInfo['unitTurn'] = $arrUnitData['unitTurn'] - 1;
							$arrUnitSendInfo['Specialty'] = 17;
						}
					}
					else
					{
						$this -> error -> printError( incl_game_unit24 ,'mapa' );
					}
					break;
			}
		
		// Deklaracja tablicy wysyłanej do mapy
		$arrayData = array('activity' => 'unitUpdate');

		// Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
		$arrayData['chanData'][] = array('chanName' => 'worldmap'.$this -> world -> id.'nation'.$this -> account -> nation,
			'data' => array(
				'updateUnitData' => $arrUnitSendInfo,
				'updateFieldData' => $fieldAdd
			)
		);

		if ($intIsSeen === true || $arrUnitSendInfo['addUnit'] === true)
		{
			// Informacja dla wrogów jeśli mnie widzą
			$arrayData['chanData'][] = array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
				'data' => array(
					'updateUnitData' => $arrUnitSendInfo,
					'updateFieldData' => $fieldAdd
				)
			);
		}
		$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

		echo json_encode(true);
		$arrUnitData = null;
	}
	public function okresl_podloze($podloze_wartosc){
		switch($podloze_wartosc){
			case 1:
			$podloze_string= woda_pl;
			break;
			case 2: 
			$podloze_string = piasek;
			break;
			case 3:
			$podloze_string = skaly;
			break;
			case 4:
			$podloze_string = droga;
			break;
			case 5:
			$podloze_string = laka;
			break;
			case 6:
			$podloze_string = bagno;
			break;
			case 7:
			$podloze_string = wyzyna;
			break;
			case 8:
			$podloze_string = woda_gl;
			break;
			case 9:
			$podloze_string = rzeka;
			break;
			case 10:
			$podloze_string = las;
			break;
			case 11:
			$podloze_string =  nabrzeze;
			break;
		}
		return $podloze_string;
	}
	
	public function ustalNazwe($liczbaJednostek, $unitType, $specialty, $nazwaUnit )
    {
        switch ( $liczbaJednostek )
        {
            case 1:
                if($unitType == 7 )
                {
                    $nowa_nazwa = comp_battleE40;
                }
                else
                {
                    $nowa_nazwa = $nazwaUnit;
                }

                break;
            case 2:
                switch ( $unitType )
                {
                    case '1':
                        $nowa_nazwa = mapa520;
                        if ($specialty == 4)
                        {
                            $nowa_nazwa = desant_nazwa_kom_txt;
                        }
                        break;
                    case 2:
                        $nowa_nazwa = mapa522;
                        break;
                    case 3:
                        $nowa_nazwa = mapa523;
                        break;
                    case 4:
                        $nowa_nazwa = mapa521;
                        break;
                    case 5:
                        if ($specialty == 1)
                        {
                            $nowa_nazwa = mapa532;
                        }
                        else
                        {
                            $nowa_nazwa = mapa524;
                        }
                        break;
                    case 6:
                        $nowa_nazwa = mapa525;
                        break;
                    case 7:
                        $nowa_nazwa = comp_battleE40;
                        break;
						default:
							$nowa_nazwa = $nazwaUnit;
						break;
                }
                break;
            case 3:
                switch ( $unitType )
                {
                    case 1:
                        if ( $specialty == 4 )
                        {
                            $nowa_nazwa = desant_nazwa_bat_txt;
                        }
                        else
                        {
                            $nowa_nazwa = mapa526;
                        }
                        break;
                    case 2:
                        $nowa_nazwa = mapa528;
                        break;
                    case 3:
                        $nowa_nazwa = mapa529;
                        break;
                    case 4:
                        $nowa_nazwa = mapa527;
                        break;
                    case 5:
                        if ($specialty == 1)
                        {
                            $nowa_nazwa = mapa533;
                        }
                        else
                        {
                            $nowa_nazwa = mapa530;
                        }
                        break;
                    case 6:
                        $nowa_nazwa = mapa531;
                        break;
                    case 7:
                        $nowa_nazwa = comp_battleE40;
                        break;
					default:
						$nowa_nazwa = $nazwaUnit;
					break;	
                }
                break;
				default:
					$nowa_nazwa = $nazwaUnit;
				break;
        }
        return $nowa_nazwa;
    }

    public function checkCapital ( $rev, $worldID )
    {
        $raport = [];
        $idCiasteczka = $this -> db -> query('SELECT `id` FROM `players` WHERE `accountID`=36 AND `worldID`='.$worldID.' ') -> fetch();
        $idPaczka = $this -> db -> query('SELECT `id` FROM `players` WHERE `accountID`=37 AND `worldID`='.$worldID.' ') -> fetch();

        $polishCapital = $this -> db -> query('SELECT count(*) FROM `units` WHERE `playerID` = '. $idCiasteczka['id'] .' AND `tacticalDataID` = 218 AND `unitType` = 7 ')->fetch();
        $germanCapital = $this -> db -> query('SELECT count(*) FROM `units` WHERE `playerID` = '. $idPaczka['id'] .' AND `tacticalDataID` = 219 AND `unitType` = 7 ')->fetch();
        $raport['pol'] = (int)$polishCapital[0];
		$raport['ger'] = (int)$germanCapital[0];
        if( (int)$polishCapital[0] < 1 )
        {
            if( $rev == 1 )
            {
                //wysyłam informację odnośnie końca wojny
                
             //Wysyłamy info do Polaków, że Warszawa została zniszczona
                $arrDatap = $this -> db -> query('SELECT `id` FROM `players` WHERE `id`!=0 AND `nation`=1 AND `worldID` = ' . $worldID . '  ');
                foreach ($arrDatap as $rowp)
                {
                    $this -> db -> exec('INSERT INTO `message` (`playerID`,`messageAuthorID`,`contentMessage`,`seeMessage`,`dateOfDispatch` ) VALUES (' . $rowp['id'] . ',\'32\', "' . opis_kapitulacja_pl . '","no",' . $this -> init -> time . ' )');
                }
            //wysyłamy do niemców info, że Polacy się poddali
                $arrDatan = $this -> db -> query('SELECT `id` FROM `players` WHERE `id`!=0 AND `nation`=2 AND `worldID` = ' . $worldID . '  ');
                foreach ($arrDatan as $rown)
                {
                    $this -> db -> exec('INSERT INTO `message` (`playerID`,`messageAuthorID`,`contentMessage`,`seeMessage`,`dateOfDispatch` ) VALUES (' . $rown['id'] . ',\'32\', "' . opis_kapitulacja . '","no",' . $this -> init -> time . ' )');
                }
            }
            $raport['continueBattle'] = 0;
        }
        else
        {
            $raport['polishRaport'] = 'Polacy grają dalej, '. (int)$polishCapital[0] .' dzielnic Warszawy pozostało';
        }
        if( (int)$germanCapital[0] < 1 )
        {
            if( $rev == 1 )
            {
                //przeprowadzam zakończenie rozgrywki
                //Wysyłamy info do Polaków, że Warszawa została zniszczona
                $arrDatap = $this -> db -> query('SELECT `id` FROM `players` WHERE `id`!=0 AND `nation`=1 AND `worldID` = ' . $worldID . '  ');
                foreach ($arrDatap as $rowp)
                {
                    $this -> db -> exec('INSERT INTO `message` (`playerID`,`messageAuthorID`,`contentMessage`,`seeMessage`,`dateOfDispatch` ) VALUES (' . $rowp['id'] . ',\'33\', "' . opis_kapitulacja . '","no",' . $this -> init -> time . ' )');
                }
                //wysyłamy do niemców info, że Polacy się poddali
                $arrDatan = $this -> db -> query('SELECT `id` FROM `players` WHERE `id`!=0 AND `nation`=2 AND `worldID` = ' . $worldID . '  ');
                foreach ($arrDatan as $rown)
                {
                    $this -> db -> exec('INSERT INTO `message` (`playerID`,`messageAuthorID`,`contentMessage`,`seeMessage`,`dateOfDispatch` ) VALUES (' . $rown['id'] . ',\'33\', "' . opis_kapitulacja_ger . '","no",' . $this -> init -> time . ' )');
                }
            }
            $raport['continueBattle'] = 0;
        }
        else
        {
            $raport['germanRaport'] = 'Niemcy grają dalej, '. (int)$germanCapital[0] .' dzielnic Berlina pozostało';
        }

        if( (int)$polishCapital[0] > 0 AND (int)$germanCapital[0] > 0 )
        {
            $raport['continueBattle'] = 1;
        }
        return $raport;
    }
	
	public function usun_jednostke( $numer_jednostki, $niszczacyID, $niszczacyIdJednostki )
	{
		$wyciagnij_dane=$this -> db -> query('select `belongHQ` from `units` WHERE `id`='.$numer_jednostki.' ')->fetch();
		if( $wyciagnij_dane['belongHQ'] == $numer_jednostki )
		{
			$this -> db -> exec('DELETE FROM `HQData` WHERE `unitsID`='.$numer_jednostki.' ');
			$this -> db -> exec('UPDATE `units` set `belongHQ`=0 WHERE `belongHQ`='.$numer_jednostki.' ');//nadpisuję jednostki przyłącozne dos ztabu
		}
		$this -> db -> exec('DELETE FROM `units` WHERE `id`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `HQwaiting` WHERE `HQID`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `walka` WHERE `idDaneJednostek_ob`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `unitsID`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `alert` WHERE `idDaneJednostek_ob`='.$numer_jednostki.' ');
        $this -> db -> exec('DELETE FROM `GoldUnits` WHERE `unitID`='.$numer_jednostki.' ');
		
		
		if($niszczacyIdJednostki != 'mina' ){
			$wyciagnijDaneAtakujacego=$this -> db -> query('select `nation` from `players` WHERE `id`='. $niszczacyID .' ')->fetch();
			$wyciagnij_daneAT = $this -> db -> query('select  `unitType`, `Specialty`,`UnderWater` from `units` WHERE `id`='.$niszczacyIdJednostki.' ')->fetch();
			switch($wyciagnij_daneAT['unitType'])
			{
				case '1':
					$do_statystyk='d_piechota';
				break;
				case '2':
					$do_statystyk='d_pancerne';
				break;
				case '3':
					$do_statystyk='d_przeciwlotnicze';
				break;
				case '4':
					$do_statystyk='d_lotnictwo_b';
				break;
				case '5':
					$do_statystyk='d_flota_n';
				break;
				case '6':
					$do_statystyk='d_artyleria';
				break;
				case '7':
					$do_statystyk='d_cities';
					break;
			}
			
			if($wyciagnij_daneAT['Specialty']==8){
				$do_statystyk='d_lotnictwo_m';
			}
			if($wyciagnij_daneAT['UnderWater']==1){
				$do_statystyk='d_flota_p';
			}
			
			$suma = $this -> db -> query('SELECT count(*) FROM `statsPlayer` WHERE `playerID`=' . $niszczacyID . ' AND `worldID`='. $this->world->id.' ')->fetch();
			if( (int)$suma[0] > 0 ){
				$this -> db -> exec('UPDATE `statsPlayer` SET '.$do_statystyk.'=(`'.$do_statystyk.'`+1) WHERE `playerID`='.$niszczacyID.' ');
			}else{
				$this -> db -> query('INSERT INTO `statsPlayer`  ('.$do_statystyk.',`playerID`,`worldID`,`nation`,`accountID`) values (1,'.$niszczacyID.','.$this->world->id.','.$wyciagnijDaneAtakujacego['nation'].','.$this->account->id.')  ');
			}
		}
	}
	
}