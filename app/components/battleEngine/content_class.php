<?php
/*
* KOMPONENT: battleEngine
*/

class content extends EveryComponent
{
	protected $init;
    protected $db;
    protected $path;
    protected $twig;
	
	public function __construct()
	{
		$this -> unit = init::getFactory() -> getService('unit');
	}
	public function battleEngine()
	{
		
		$objHelper = init::getFactory() -> getService('helper');
		$unitIDAttack = $this -> path -> post('unitID');
		$coordX = $this -> path -> post('x');
		$coordY = $this -> path -> post('y');
		$rodzajStrzaluAtakujacego = $this -> path -> post('attack');
		$battleArray=array();
		$battleArray['error'] ='';
		$raport_txt = '';
		/*
		ZAŁOZENIA BITEWN
		Podczas ataku
		*/
		

		$attackID = $this -> db -> query('SELECT `un`.*, `pl`.`accountID`, `pl`.`nation`,`acc`.`login` FROM `units` as `un`
		LEFT JOIN `players` as `pl` ON `un`.`playerID`=`pl`.`id`
		LEFT JOIN `accounts` as `acc` ON `pl`.`accountID`=`acc`.`id`
		WHERE `un`.`id`='.$unitIDAttack.' AND `un`.`worldID`='. $this -> world -> id .' AND `un`.`onMap`= 1 ') -> fetch();
		if(!$attackID['id']){
			$battleArray['error'] = comp_battleE1;
				echo json_encode($battleArray);
				exit();
		}
		
		
		$intTury = 0;
		$up1 = 0;
		$up2 = 0;
		$licznik1 = $attackID['timing1'];
		$licznik2 = $attackID['timing2'];
		
		if( $this -> world -> timing == 1 ){// jeśli świat poligon ( na razie tylko tam są zegarki )
			// sprawdzam, czy licnziki sie wyzerowały
			if($attackID['timing1'] > $this -> init -> time ){
				if( $attackID['timing2'] > $this -> init -> time ){
					$licznik1 = $attackID['timing1'];
					$licznik2 = $attackID['timing2'];
					$battleArray['error'] = comp_battleE2;
					echo json_encode($battleArray);
					exit();
				}else{
					$licznik2 = $this -> init -> time + $this -> world -> timeToUp;
					$up2 = 1;
					$intTury++;
				}
				$licznik1 = $attackID['timing1'];
			}else{
				$licznik1 = $this -> init -> time + $this -> world -> timeToUp;
				$licznik2 = $attackID['timing2'];
				$up1 = 1;
				$intTury++;
				//$this -> error -> printError('można działać.'.$this -> init -> time.'', 'mapa');
			}
			
			if( $up1 == 0 AND $up2 ==0 ){
				$battleArray['error'] = comp_battleE3;
				echo json_encode($battleArray);
				exit();
			}
		}else{
			if( $attackID['unitTurn'] <= 0 ){
				$battleArray['error'] = comp_battleE3;
				echo json_encode($battleArray);
				exit();
			}
		}
		
		
		
		$defenseID = $this -> db -> query('SELECT `un`.*, `pl`.`accountID`,`pl`.`nation`,`pl`.`points`,`pl`.`playerVacation`,`acc`.`login`, `acc`.`opticalAlert`, `acc`.`SponsorAccount` FROM `units` as `un`
		LEFT JOIN `players` as `pl` ON `un`.`playerID`=`pl`.`id`
		LEFT JOIN `accounts` as `acc` ON `pl`.`accountID`=`acc`.`id`
		WHERE `un`.`x`='.$coordX.' AND `un`.`y`='.$coordY.' AND `un`.`worldID`='. $this -> world -> id .'  AND `un`.`onMap`= 1 ') -> fetch();
		if(!$defenseID['id']){
			// nie ma jednostki, szukamy mostów na tym polu
			$defenseFieldCustom = $this -> db -> query('SELECT * FROM `mapData` WHERE `x`='.$coordX.' AND `y`='.$coordY.' AND `worldID`='. $this -> world -> id .' AND `fieldCustom` = 1 ') -> fetch();
			if(!$defenseFieldCustom['fieldID']){
				// nie ma mostu na tym heksie
				$battleArray['error'] = comp_battleE4;
				echo json_encode($battleArray);
				exit();
			}
			// most na danym heksie istnieje, wykonujemy strzał
			$battleArray['error'] = 'wykonujemy atak na most';
			// zapisujemy fakt strzału
			$updateLiczniki = '';
			//zapisujemy dane jednostki
			if($up1==1){
				$updateLiczniki = ',`timing1`='.$licznik1;
			}
			if($up2==1){
				$updateLiczniki = ',`timing2`='.$licznik2;
			}
			$this -> db -> query('UPDATE `units` SET  `unitTurn`=(`unitTurn`-1) '.$updateLiczniki.'  WHERE id='.$attackID['id'].' ');
			$this-> goToDestroyBridge($defenseFieldCustom, $attackID, $licznik1, $licznik2,$up1, $up2 );
		}
		
		if($defenseID['Specialty'] == 21 ){
			// atakujemy silos, więc prenosimy do systemu niszczenia silosu
			$defenseField = $this -> db -> query('SELECT * FROM `siloData` WHERE `unitID`='. $defenseID['id'] .' ') -> fetch();
			if(!$defenseFieldCustom['fieldID']){
				// nie ma mostu na tym heksie
				$battleArray['error'] = comp_battleE4;
				echo json_encode($battleArray);
				exit();
			}
			// most na danym heksie istnieje, wykonujemy strzał
			$battleArray['error'] = 'wykonujemy atak na most';
			// zapisujemy fakt strzału
			$updateLiczniki = '';
			//zapisujemy dane jednostki
			if($up1==1){
				$updateLiczniki = ',`timing1`='.$licznik1;
			}
			if($up2==1){
				$updateLiczniki = ',`timing2`='.$licznik2;
			}
			$this -> db -> query('UPDATE `units` SET  `unitTurn`=(`unitTurn`-1) '.$updateLiczniki.'  WHERE id='.$attackID['id'].' ');
			$this-> goToDestroySilo($defenseField, $attackID, $licznik1, $licznik2, $up1, $up2 );
		}
		
		

		if( $defenseID['nation'] == $attackID['nation'] ){
			$battleArray['error'] = comp_battleE5;
			echo json_encode($battleArray);
			exit();
		}
	
	$capitalRaport = $this -> unit -> checkCapital(0, $this -> world -> id );
	
	if( $capitalRaport['continueBattle'] ==  0 ){
		$battleArray['error'] = comp_battleE6;
		echo json_encode($battleArray);
		exit();
        }
		
        if( $defenseID['playerVacation'] > $this -> init -> time){
			$battleArray['error'] = comp_battleE7 .' '.date("Y-m-d H:i:s", $defenseID['playerVacation']);
			echo json_encode($battleArray);
			exit();
        }
		
		switch($rodzajStrzaluAtakujacego){//sprawdzamy, jakim rodzajem strzela gracz
			case 1:
				$rodzajStrzaluAt = 'lotnictwo';
				$rodzajOstrzalAt = 'min(`td`.`ostrzal_przeciwlotniczy`) as `ostrzal`';
				$rodzajOstrzalOdAt = 'min(`td`.`ostrzal_od`) as `ostrzal_od`';
			break;
			case 2:
				$rodzajStrzaluAt = 'podwodne';
				$rodzajOstrzalAt = 'min(`td`.`ostrzal_torpeda`) as `ostrzal`';
				$rodzajOstrzalOdAt = 'min(`td`.`ostrzal_od`) as `ostrzal_od`';
			break;
			case 3:
				$rodzajStrzaluAt = 'glebinowe';
				$rodzajOstrzalAt = 'min(`td`.`ostrzal_glebinowe`) as `ostrzal`';
				$rodzajOstrzalOdAt = 'min(`td`.`ostrzal_od`) as `ostrzal_od`';
			break;
			default:
				$rodzajStrzaluAt = $this->zmien_korpus($defenseID['unitType']);
				$rodzajOstrzalAt = 'min(`td`.`ostrzal`) as `ostrzal`';
				$rodzajOstrzalOdAt = 'min(`td`.`ostrzal_od`) as `ostrzal_od`';
		}

		$attackTacticData = $this -> db -> query('select '.$rodzajOstrzalAt.', '.$rodzajOstrzalOdAt.', min(`td`.`ostrzal_przeciwlotniczy`) as `ostrzalPrzeciwlotniczy`, sum(`td`.`atak_'.$rodzajStrzaluAt.'`) as `atak_at`, sum(`td`.`obrona_'.$rodzajStrzaluAt.'`) as `obrona_at`, sum(`td`.`obrona_przeciwlotnicze`) as `obrona_atAntiAircraft`, sum(`td`.`cena`)  as `cena`, `td`.`obrazek_duzy` as `obrazek_at`, `td`.`nazwa` as `nazwa_at`,MIN(`td`.`widocznosc`) as `widocznosc` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$unitIDAttack.' ')->fetch();
		$atak_at               = $attackTacticData['atak_at'];
		$obrona_at             = $attackTacticData['obrona_at'];
		$obrona_atAntiAircraft = $attackTacticData['obrona_atAntiAircraft'];
		$cena_at               = $attackTacticData['cena'];
		$zasieg_strzalu_at     = $attackTacticData['ostrzal'];
		$zasieg_strzalu_od_at  = $attackTacticData['ostrzal_od'];
		$obrazek_at            = $attackTacticData['obrazek_at'];
		$widocznosc_at         = $attackTacticData['widocznosc'];
		$zasiegStrzaluOPLOT_at = $attackTacticData['ostrzalPrzeciwlotniczy'];
		
		$deffenceTacticData = $this -> db -> query('select min(`td`.`ostrzal`) as `ostrzal`, min(`td`.`ostrzal_od`) as `ostrzal_od`, sum(`td`.`atak_'.$this->zmien_korpus($attackID['unitType']).'`) as `atak_ob`, sum(`td`.`obrona_'.$this->zmien_korpus($attackID['unitType']).'`) as `obrona_ob`, sum(`td`.`cena`)  as `cena`, `td`.`obrazek_duzy` as `obrazek_ob`, `td`.`nazwa` as `nazwa_ob`, MIN(`td`.`widocznosc`) as `widocznosc` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$defenseID['id'].' ')->fetch();
		$atak_ob               = $deffenceTacticData['atak_ob'];
		$obrona_ob             = $deffenceTacticData['obrona_ob'];
		$cena_ob               = $deffenceTacticData['cena'];
		$zasieg_strzalu_ob     = $deffenceTacticData['ostrzal'];
		$zasieg_strzalu_od_ob  = $deffenceTacticData['ostrzal_od'];
		$obrazek_ob            = $deffenceTacticData['obrazek_ob'];
		$widocznosc_ob         = $deffenceTacticData['widocznosc'];
		//pobrać dane z accounts 
		
		
	$stolica = 0;
	if( $defenseID['unitType'] == 7 ){
		//gracz atakuje miasto
		$atak_ob               = 70;
		$obrona_ob             = 50;
		$cena_ob               = 1000;
		$zasieg_strzalu_ob     = 6;
		$zasieg_strzalu_od_ob  = 1;
		$obrazek_ob            = 'miasto_duze.png';
		$widocznosc_ob         = 5;
		
		if( $defenseID['playerID'] == 32 ){
			$stolica = 1;
			$defenseID['login'] = comp_battleE8;
		}else if( $defenseID['playerID'] == 33 ){
			$stolica = 1;
			$defenseID['login'] = comp_battleE9;
		}
    }

	$user_at = $attackID['login'];
	$user_ob = $defenseID['login'];
	$nacjaJednostki_at=$attackID['nation'];
	$nacjaJednostki_ob=$defenseID['nation'];
	$accountIDAT=$attackID['accountID'];
	$accountIDOB=$defenseID['accountID'];
	$dystans_strzalu = $this -> unit -> hex_distance(array('x' => $attackID['x'], 'y' => $attackID['y']),array('x' => $defenseID['x'], 'y' => $defenseID['y']) );

        //A => x,y i B => x,y
	//echo "dystans strzału=".$dystans_strzalu;
	
	$pogoda_at_txt = raport_txt9 .'<br>';//"Brak wpływu pogody na strzał agresora";
	$pogoda_ob_txt = raport_txt13 .'<br>'; //"Brak wpływu pogody na strzał obrońcy";
	
		// wpływ pogody na strzał atakującego
		if( $this -> world -> hardGame > 1 )// jeśli świat jest trudniejszy niż KADET ( czyli WOJAK lub WETERAN ), to obliczamy wpływ pogody na atak
		{
			if( ( $attackID['unitType'] == 3 OR ( $attackID['unitType'] == 5 AND $attackID['Specialty'] == 0 ) OR $attackID['unitType'] == 6 ) AND in_array( $this -> world -> WarWeather, array(1,2,8) ) == true ){//idJednostki= 1:piechota; 2:pancerne; 3:przeciwlotnicze; 4:lotnictwo; 5:flota; 6:artyleria
				$zasieg_strzalu_at = $zasieg_strzalu_at * 0.75;
				$pogoda_at_txt = raport_txt10 .'<br>'; //"Z powodu złej pogody zasieg strzału obniżony do 75%";
			}
			if( $attackID['unitType'] == 5 AND $attackID['Specialty']==0 AND in_array($this -> world -> WarWeather, array(2,7) )== true){
				$zasieg_strzalu_at = $zasieg_strzalu_at * 0.50;
				$pogoda_at_txt = raport_txt11 .'<br>'; //"Z powodu złej pogody zasieg strzału obniżony do 50%";
			}
			if( $attackID['unitType'] == 4 AND in_array($this -> world -> WarWeather, array(1,2,8) )== true ){
				$zasieg_strzalu_at = 1;
				$atak_at           = 0;
				$obrona_at         = 0;
				$pogoda_at_txt = raport_txt12 .'<br>'; //"Z powodu złej pogody strzał niemożliwy";
				//exit();
			}
			// wpływ pogody na strzał obrońcy
			if( ( $defenseID['unitType'] == 3 OR $defenseID['unitType'] == 5 ) AND in_array( $this -> world -> WarWeather, array(1,2,8) )== true ){//idJednostki= 1:piechota; 2:lotnictwo; 3:pancerne; 4:OPLOT; 5:flota; 6:artyleria
				$zasieg_strzalu_ob = $zasieg_strzalu_ob * 0.75;
				$pogoda_ob_txt = raport_txt14 .'<br>'; //"Z powodu złej pogody strzał obrońcy obniżony do 75%";
			}
			if( $defenseID['unitType'] == 5 AND in_array( $this -> world -> WarWeather, array(2,7) )== true ){
				$zasieg_strzalu_ob = $zasieg_strzalu_ob * 0.50;
				$pogoda_ob_txt = raport_txt15 .'<br>'; //"Z powodu złej pogody strzał obrońcy obniżony do 50%";
			}
			if( $defenseID['unitType'] == 4 AND in_array( $this -> world -> WarWeather, array(1,2,8) )== true ){
				$zasieg_strzalu_ob = 1;
				$atak_ob           = 0;
				$obrona_ob         = 0;
				$pogoda_ob_txt = raport_txt16 .'<br>'; //"Z powodu złej pogody strzał obrońcy niemożliwy";
				//exit();
			}
		}
		
		
		if( $dystans_strzalu < $zasieg_strzalu_od_at )
		{
			$battleArray['error'] .=  comp_battleE10;//"Wróg jest za blisko";
			exit();
		}
		if( $dystans_strzalu > $zasieg_strzalu_at )
		{
			$battleArray['error'] .=  comp_battleE11;//"Jednostka atakowana jest za daleko";
			exit();
		}
		if( $dystans_strzalu < $zasieg_strzalu_od_ob )
		{
			$battleArray['error'] .= comp_battleE12;//"Atakujący jest za blisko, aby obrońca mógł się bronić";
		}
		if( $dystans_strzalu > $zasieg_strzalu_ob )
		{
			$battleArray['error'] .=  comp_battleE13;//"Jednostka atakująca jest za daleko, aby obrońca mógł się obronić";
		}
		
		//sprawdzam, czy jendostka atakowana jest w zasięgu swojego sztabu
		$bonus_at = 0;
		$bonus_ob = 0;
		$newAtOB = $atak_ob;
		$newObOB = $obrona_ob;	
			if( $defenseID['belongHQ']!=0 )
			{
				switch( $defenseID['unitType'] )
				{
					case '1':
						$nameRekord2 = 'infantry';
					break;
					case '2':
						$nameRekord2 = 'tanks';
					break;
					case '3':
						$nameRekord2 = 'antiAircraft';
					break;
					case '4':
						$nameRekord2 = 'aircraft';
					break;
					case '5':
						if($attackID['UnderWater']==1)
						{
							$nameRekord2 = 'underWater';
						}
						else
						{
							$nameRekord2 = 'waterArtillery';
						}
					break;
					case '6':
						$nameRekord2 = 'artillery';
					break;
					case '7':
						$nameRekord2 = 'infantry';
					break;
				}
				
				$belongHQDeffense = $this -> db -> query('SELECT `u`.`x` as `xBelong`,`u`.`y` as `yBelong`, `hqd`.`range`,`hqd`.`'.$nameRekord2.'` as `bonusHQAT` FROM `units` AS `u` LEFT JOIN `HQData` AS `hqd` ON `hqd`.`id`=`u`.`id`  WHERE `hqd`.`id`='.$defenseID['belongHQ'].' ')->fetch();
					$odlegloscHQDeffense = $this -> unit -> hex_distance(array('x' => $belongHQDeffense['xBelong'], 'y' => $belongHQDeffense['yBelong']),array('x' => $defenseID['x'], 'y' => $defenseID['y']) );
					if( $belongHQDeffense['range'] * 10 >= $odlegloscHQDeffense )
					{
						$bonusHQOB = $belongHQDeffense['bonusHQAT'];
						$raport_sztab_txtOB = comp_battleE14 .' '. $bonusHQOB .'% '. comp_battleE15 .'<br>';
						$newAtOB = $atak_ob + ( ( $atak_ob * $bonusHQOB )/100 );
						$newObOB = $obrona_ob + ( ( $obrona_ob * $bonusHQOB )/100 );
						$obronaObAA = $obrona_obAA_ost + ( ( $obrona_obAA_ost * $bonusHQOB )/100 );
						$bonus_ob = 1;
					}else{
						$raport_sztab_txtOB = comp_battleE16 .'<br>'. comp_battleE17 .'='.$odlegloscHQDeffense;
						
					}
			}else{
				$raport_sztab_txtOB = comp_battleE21 .' '. comp_battleE18 .'<br>';
			}
			
			
			$obronaArray=$this->calcParameters($defenseID['unitExperience'],$defenseID['Morale'],$defenseID['DefensePoints'],$newAtOB, $newObOB, $this -> world -> hardGame );
			$bonusHQOB=0;//bonus ze sztabu dla atakującego
			$newAtOB=$obronaArray['atak'];
			$newObOB=$obronaArray['obrona'];
				
				if($attackID['unitExperience']<=0)
				{
					$procent_atAA_ob=0;					
				}else{
					$procent_atAA_ob=($attackID['unitExperience']/100)*$obrona_atAntiAircraft;
				}
				
				$obrona_atAA1=$obrona_atAntiAircraft+$procent_atAA_ob;//obniżam punkty obrony proporcjonalnie do doświadczenia obroncy
				$obrona_atAA2=($attackID['Morale']/100)*$obrona_atAA1;//obbniżam punkty obrony proporcjonalnie do morale jednostki
				$obrona_atAA_ost=($attackID['DefensePoints']/100)*$obrona_atAA2;//analizuję punkty DefensePoints jendostki
				
				$bonusHQAT=0;//bonus ze sztabu dla atakującego
				$obronaAtAA=$obrona_atAA_ost;
				$newAtAT = $atak_at;
				$newObAT = $obrona_at;			
			
			//sprawdzam, czy atakująca jednostka jest w sztabie
			if($attackID['belongHQ']!=0)
			{
				switch($attackID['unitType'])
				{
					case '1':
						$nameRekord = 'infantry';
					break;
					case '2':
						$nameRekord = 'tanks';
					break;
					case '3':
						$nameRekord = 'antiAircraft';
					break;
					case '4':
						$nameRekord = 'aircraft';
					break;
					case '5':
						if($defenseID['UnderWater']==1)
						{
							$nameRekord = 'underWater';
						}
						else
						{
							$nameRekord = 'waterArtillery';
						}
					break;
					case '6':
						$nameRekord = 'artillery';
					break;
					case '7':
						$nameRekord = 'infantry';
					break;
				}
				
				$belongHQAttack = $this -> db -> query('SELECT `u`.`x` as `xBelong`,`u`.`y` as `yBelong`, `hqd`.`range`,`hqd`.`'.$nameRekord.'` as `bonusHQAT` FROM `units` AS `u` LEFT JOIN `HQData` AS `hqd` ON `hqd`.`unitsID`=`u`.`id`  WHERE `hqd`.`unitsID`='.$attackID['belongHQ'].' ')->fetch();
					$odlegloscHQAttack = $this -> unit -> hex_distance(array('x' => $belongHQAttack['xBelong'], 'y' => $belongHQAttack['yBelong']),array('x' => $attackID['x'], 'y' => $attackID['y']) );
					if( $belongHQAttack['range'] * 10 >= $odlegloscHQAttack )
					{
						$bonusHQAT = $belongHQAttack['bonusHQAT'];
						$raport_sztab_txtAT = comp_battleE19 .' '. $bonusHQAT .'% '. comp_battleE15 .'<br>';
						$newAtAT = $atak_at + ( ( $atak_at * $bonusHQAT )/100 );
						$newObAT = $obrona_at + ( ( $obrona_at * $bonusHQAT )/100 );
						$obronaAtAA = $obrona_atAA_ost + ( ( $obrona_atAA_ost * $bonusHQAT )/100 );
						$bonus_at = 1;
					}else{
						$raport_sztab_txtAT = comp_battleE22 .' '. comp_battleE20 .'<br>';
					}
			}else{
				$raport_sztab_txtAT = comp_battleE22 .' '. comp_battleE18 .'<br>';
			}
		
			//ustalam realne parametry jednostki atakującej
				//doswiadczenie jednostki
				$atakArray=$this->calcParameters($attackID['unitExperience'],$attackID['Morale'],$attackID['DefensePoints'],$newAtAT, $newObAT, $this -> world -> hardGame);
				$bonusHQOB=0;//bonus ze sztabu dla atakującego
				$newAtAT=$atakArray['atak'];
				$newObAT=$atakArray['obrona'];

		
		//Sprawdzamy, czy jednostka atakująca jest samolotem, jeśli jest
		
		$ktostrzelaAA ='';
		
/* *********************** AUTOMATYCZNY OSTRZAŁ PRZECIWLOTNICZY **********************************************************************	*/
		$straty_at = 0;
		$pancerz_at = $attackID['DefensePoints'];
		$startAT = 'JEDNOSTKA ATAKUJĄCA:<br>
		-PANCERZ:'.$pancerz_at.'<br>
		-ATAK:'.$atak_at.'<br>
		-OBRONA:'.$obrona_at.'<br><br>';
		
		$straty_ob = 0;
		$pancerz_ob = $defenseID['DefensePoints'];
		$startOB = 'JEDNOSTKA ATAKOWANA:<br>
		-PANCERZ:'.$pancerz_ob.'<br>
		-ATAK:'.$atak_ob.'<br>
		-OBRONA:'.$obrona_ob.'<br><br>';
		$raport_oplot='';
		
		//-> sprawdzamy, czy jest ochrona nocna, jeśli jest ( czyli od 23 do  5 rano ) obniżamy atak o 50%. 	
			$datateraz=date('Y-m-d H:i:s');
			if( $this -> world -> WarMode == 1 ){
				$data22= date('Y-m-d 23:30:00');
				$polnoc_przed=date('Y-m-d 23:59:59');
				$polnoc_po=date('Y-m-d 00:00:01');
				$data06= date('Y-m-d 06:30:00');
			}else if( $this -> world -> WarMode == 2 ){
				$data22= date('Y-m-d 23:00:01');
				$polnoc_przed=date('Y-m-d 23:59:59');
				$polnoc_po=date('Y-m-d 00:00:01');
				$data06= date('Y-m-d 16:00:01');
			}else{
				$data22= date('Y-m-d 23:00:00');
				$polnoc_przed=date('Y-m-d 23:59:59');
				$polnoc_po=date('Y-m-d 00:00:01');
				$data06= date('Y-m-d 06:00:00');
			}
			if( ( $datateraz > $data22 AND $datateraz < $polnoc_przed ) OR ( $datateraz > $polnoc_po AND $datateraz < $data06 ) ){
				$newAtAT = $newAtAT * 0.5;
			}
		
		$straty_atOPLOT=0;
		$endOPLOTAT='';
		$endOPLOTOB='';
		
		
		if( $attackID['unitType'] == 4 )
		{//
			$destroyForOplot = 0;
			//jednostka atakujaca jest samolotem
			$allyUnits = $this -> db -> query('SELECT `u`.`id`, `u`.`AntiAircraft`,`u`.`DefensePoints`,`u`.`Morale`,`u`.`unitExperience`,`u`.`belongHQ`,`u`.`playerID`,`u`.`x`, `u`.`y`, `t`.`widocznosc`, `t`.`ostrzal`, `t`.`obrazek_duzy`,`t`.`nazwa'.$this -> account -> lang.'`,`t`.`atak_lotnictwo` FROM `units` AS `u` LEFT JOIN `TacticalData` AS `t` ON `t`.`id`=`u`.`tacticalDataID` WHERE `u`.`x`<=' . ($attackID['x'] + 10) . ' AND `u`.`y`>=' . ($attackID['y'] - 10) . ' AND `u`.`x`>='.($attackID['x'] - 10).' AND `u`.`y`<='.($attackID['y'] + 10).' AND `u`.`AntiAircraft`=1 AND ( ( `u`.`FieldArtillery`=1 AND `u`.`Specialty`=17  ) OR `u`.`FieldArtillery`=0  ) AND `u`.`worldID`='.$this -> world -> id.' AND `u`.`id`!='.$defenseID['id'].' AND `u`.`x`!=0 AND `u`.`y`!=0 AND `t`.`NationId` IN '.($this -> account -> nation == 1 ? '(7, 8, 9)' : '(1, 2, 3, 4, 5)').' ');
			$i=0;
			$daneAntiArcraft=array();
			foreach ($allyUnits as $enemyAntiAircraftUnits)
			{
					$aaTacticData = $this -> db -> query('select min(`td`.`ostrzal`) as `ostrzal`, sum(`td`.`atak_lotnictwo`) as `atak_lotnictwo`, `td`.`nazwa` as `nazwa_aa` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$enemyAntiAircraftUnits['id'].' ')->fetch();
					//wyciągam dane połączonych oplotów
					$odleglosc = $this -> unit -> hex_distance(array('x' => $attackID['x'], 'y' => $attackID['y']),array('x' => $enemyAntiAircraftUnits['x'], 'y' => $enemyAntiAircraftUnits['y']) );
				if( $aaTacticData['ostrzal'] >= $odleglosc AND  $odleglosc <= 5 ){
					//ustalam, czy jednostka przeciwlotnicza jest w sztabie
					$bonus_obAA=0;
					$belongHQ['antiAircraft']=0;
					if($enemyAntiAircraftUnits['belongHQ']!=0){
						//jednostka jest przydzielona do sztabu
						//pobieram koordynaty  sztabu i sprawdzam, czy on widzi ww jednostkę
						/*
						$belongHQ = $this -> db -> query('SELECT `u`.`x`,`u`.`y`, `u`.`id`,`bhq`.`antiAircraft`,`bhq`.`range` FROM `units` AS `u` LEFT JOIN `HQData` AS `bhq` ON `u`.`belongHQ`=`bhq`.`id` WHERE `bhq`.`id`='.$enemyAntiAircraftUnits['belongHQ'].' ')->fetch();
							$odlegloscHQ = count($objHelper -> bresenham($enemyAntiAircraftUnits['x'], $enemyAntiAircraftUnits['y'], $belongHQ['x'], $belongHQ['y'], false) );
							echo "odległosć do sztabu=".$odlegloscHQ.", range=".( $belongHQ['range'] * 10 ) ."aa=".$belongHQ['antiAircraft']."";
							if( $belongHQ['range'] * 10 >= $odlegloscHQ){
								$bonus_ob=1;
								echo "bonus sztabu aktywny";
							}
							*/
							$bonus_obAA=1;
							$belongHQ['antiAircraft']=20;
 					}
					//obliczam realny atak jednostki przeciwlotniczej
					if($enemyAntiAircraftUnits['unitExperience']<=0)
					{
						$procent_ob_obAA=0; 
					}else{
						$procent_ob_obAA=($enemyAntiAircraftUnits['unitExperience']/100)*$aaTacticData['atak_lotnictwo'];
					}
					if($bonus_obAA==1)
					{
						$bonus_ob_obAA=($belongHQ['antiAircraft']/100)*$aaTacticData['atak_lotnictwo'];//obniżam punkty obrony proporcjonalnie do pancerza
					}else{
						$bonus_ob_obAA=0;
					}
					
					$obrona_obAA1=$aaTacticData['atak_lotnictwo']+$procent_ob_obAA+$bonus_ob_obAA;//obniżam punkty obrony proporcjonalnie do doświadczenia obroncy
					$obrona_obAA2=($enemyAntiAircraftUnits['Morale']/100)*$obrona_obAA1;//obbniżam punkty obrony proporcjonalnie do morale jednostki
					$obrona_ob_ostAA=($enemyAntiAircraftUnits['DefensePoints']/100)*$obrona_obAA2;

						//ustalam nazwę połączonej jednostki przeciwlotniczej
						$unitsAA = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$enemyAntiAircraftUnits['id'].' ')->fetch();
						$daneAntiArcraft[] = array(
							'nazwaJednostki'  => $this -> unit -> ustalNazwe( (int)$unitsAA[0], 3, 0, $aaTacticData['nazwa_aa'] ),
							'idGracza'        => $enemyAntiAircraftUnits['playerID'],
							'unitID'          => $enemyAntiAircraftUnits['id'],
							'round'           => $odleglosc,
							'moc'             => $obrona_ob_ostAA
						);
				}
				$i++;
			}
			$moc = 0;
			$nazwaJednostki = '';
			$idDaneJednostek_oplot=0;
			$active_oplot = 0;
			foreach($daneAntiArcraft as $tab)
			{
				if($tab['moc'] > $moc ){
					$moc                   = $tab['moc'];
					$idGracza_oplot        = $tab['idGracza'];
					$idDaneJednostek_oplot = $tab['unitID'];
					$nazwaJednostki        = $tab['nazwaJednostki'];
					$round                 = $tab['round'];
				}
				
				//$ktostrzelaAA .= ' jednostka '.$tab['nazwaJednostki'].', odległosć='.$tab['round'].', moc='.$tab['moc'];
			}
			
			
			if($moc>0){
				$atakujacy=array(
					'opancerzenie' => $attackID['DefensePoints'],
					'atak'         => (int)$newAtAT,
					'obrona'       => (int)$obronaAtAA
				);
				
				$atakujacy = $this -> cios($moc,$atakujacy);
				$pancerz_at = $atakujacy['opancerzenie'];
				$straty_atOPLOT = $atakujacy['dmg'];
				$raport_oplot = comp_battleE23 .'<br>'. autooplot1 .' '. $nazwaJednostki .' '. sztaby_gracza .' '.$idGracza_oplot.' '. mess_gracz4 .' '.$round.' z mocą '.$moc.', '. autooplot2 .' '.$straty_atOPLOT.'<br><br>';
				//wykonujemy przeliczenie po starciu między samolotem atakującym a automatycznym strzałem przeciwlotniczym
				
				/*
				!!!!!!!!!!!!!!!!!!!!!!!!dodać bitwę między jednostka przeciwlotniczą a atakującym samolotem !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				*/ 
				
				
				$active_oplot = 1;
				if( $pancerz_at <= 0 ){
					//po strzale oplot  jendostka zostaje zniszczona.
					$raport_oplot.= comp_battleE24;
					$destroyForOplot = 1;
				}
			}else{
				$raport_oplot = autooplot3 .'<br>';
			}
			
			$atakArray=$this->calcParameters($attackID['unitExperience'],$attackID['Morale'],$pancerz_at, $newAtAT, $newObAT, $this -> world -> hardGame );
			$newAtAT=$atakArray['atak'];
			$newObAT=$atakArray['obrona'];
			
			$endOPLOTAT="Parametry po bitwie przeciwlotniczej:<br>
			JEDNOSTKA ATAKUJĄCA:<br>
			-PANCERZ:".$pancerz_at."<br>
			-ATAK:".$atak_at."<br>
			-OBRONA:".$obrona_at."<br><br>";
			
			
			
			$pancerz_ob = $defenseID['DefensePoints'];
			$endOPLOTOB="Parametry po bitwie przeciwlotniczej:<br>
			JEDNOSTKA ATAKOWANA:<br>
			-PANCERZ:".$pancerz_ob."<br>
			-ATAK:".$atak_ob."<br>
			-OBRONA:".$obrona_ob."<br><br>
			";
		}
		
		
		
/* *********************** KONIEC  AUTOMATYCZNY OSTRZAŁ PRZECIWLOTNICZY **********************************************************************	*/	
			
		
		
		//-> analizujemy atak jednostki lub samolotu z moca pomniejszoną o otrzymane obrażenia w starciu z jednostką przeciwlotniczą ) na właściwą jednostkę atakowaną:
			//-> sprawdzamy, czy jednostka atakowana może się bronić: 
				//-> jeśli artyleria kołowa ( fieldArtillery==1 ) którą trzeba rozkładać do strzału i składać do ruchu, musi być rozłożona ( speciality==17 ) aby się bronić
			if($defenseID['FieldArtillery'] == 1 )//jdnostka wymagająca rozłożenia do strzału
			{
				if( $defenseID['Specialty'] != 17 )//jendostka artyleryjska w poozeniu marszowym, bez możliwości obrony
				{
					//echo 'jednostka atakowana jest artylerią, znajduje się w połozeniu Marszowym, brak możliwości obrony';
					$newAtOB=0;
					$newObOB=0;
				}
			}
			
			//sprawdzam, czy jednostka atakowana jest widoczna
			$arrCheckNotSeenUnits = $this -> db -> query('SELECT `seenUnitID` FROM `units_range` WHERE `seenUnitID`='.$defenseID['id'].' LIMIT 1') -> fetch();
			if ( isset( $arrCheckNotSeenUnits['seenUnitID'] ) )//jednostka widoczna
			{
				$mgla_txt = comp_battleE25;//'jednostka widoczna'; //" strzał osłabiony o 25% z uwagi na brak zwiadu";
				$jednostkawidoczna = 1;
			}
			else//jednostka niewidoczna, strzął w ciemno
			{
				$mgla_txt = raport_txt8; //" strzał osłabiony o 25% z uwagi na brak zwiadu";
				$jednostkawidoczna = 0;
			}	
			$arrCheckNotSeenUnits = null;
			
		//Gradacja celności strzału w związku z odległością strzału-------------------------------------------------------------
			//-> obliczamy, jaka odległość dzieli obie jednostki
		$damage_at_proc = 100;
		$damage_ob_proc = 100;
		if( $this -> world -> hardGame == 3 ){
		//jeśli trudność świata to WETERAN, obliczamy gradację strzału ze względu na odległość
            $polowa_ob=round($zasieg_strzalu_ob/2);
            $heksy_efektywne_ob=$zasieg_strzalu_ob-$zasieg_strzalu_od_ob;
            $max_strzal_bez_strat_ob=$zasieg_strzalu_od_ob+($heksy_efektywne_ob/2);
            if($dystans_strzalu>=$zasieg_strzalu_od_ob){
                if($zasieg_strzalu_ob>3){// jeśli jednostka ma mniejszy lub równy 3 to nie gradujemy strzału.
                    $procent_heks_ob=(100/$heksy_efektywne_ob)/2;
                    if($dystans_strzalu>$polowa_ob AND $dystans_strzalu>3  AND $dystans_strzalu>$max_strzal_bez_strat_ob){
                        $ilosc_heks_damage_ob = $dystans_strzalu-$max_strzal_bez_strat_ob;
                        $procenty_ob=$ilosc_heks_damage_ob*$procent_heks_ob;
                        $damage_ob_proc = 100 - $procenty_ob;
                        $proc_o_ob = '0.'.$damage_ob_proc;
                        $newAtOB = $newAtOB * $proc_o_ob;
                    }else{
                        //brak gradacji strzału, moc strzału 100%
                    }
                }else{
                    //brak gradacji strzału, moc strzału 100%
                }
            }else{
                //strzał w za bliską odległość
            }


            $polowa_at=round($zasieg_strzalu_at/2);
            $heksy_efektywne_at=$zasieg_strzalu_at-$zasieg_strzalu_od_at;
            $max_strzal_bez_strat_at=$zasieg_strzalu_od_at+($heksy_efektywne_at/2);
            if($dystans_strzalu>=$zasieg_strzalu_od_at){
                if($zasieg_strzalu_at>3){// jeśli jednostka ma mniejszy lub równy 3 to nie gradujemy strzału.
                    $procent_heks_at=(100/$heksy_efektywne_at)/2;
                    if($dystans_strzalu>$polowa_at AND $dystans_strzalu>3  AND $dystans_strzalu>$max_strzal_bez_strat_at){
                        $ilosc_heks_damage_at = $dystans_strzalu-$max_strzal_bez_strat_at;
                        $procenty_at=$ilosc_heks_damage_at*$procent_heks_at;
                        $damage_at_proc=100-$procenty_at;
                        $proc_o_at='0.'.$damage_at_proc;
                        $newAtAT = $newAtAT * $proc_o_at;
                    }else{
                        //brak gradacji strzału, moc strzału 100%
                    }
                }else{
                    //brak gradacji strzału, moc strzału 100%
                }
            }else{
                //strzał w za bliską odległość
            }
        }


		
		$gradAT="Parametry po gradacji strzału w związku z odległością do celu:<br>
		JEDNOSTKA ATAKUJĄCA:<br>
		-PANCERZ:".$pancerz_at."<br>
		-ATAK:".$atak_at."<br>
		-OBRONA:".$obrona_at."<br><br>";
		
		
		
		
		
		$gradOB="Parametry po gradacji strzału w związku z odległoscią do celu:<br>
		JEDNOSTKA BORNIĄCA SIĘ:<br>
		-PANCERZ:".$pancerz_ob."<br>
		-ATAK:".$atak_ob."<br>
		-OBRONA:".$obrona_ob."<br><br>
		";
		
		//Obliczamy ciosy poszczególnych jednostek
				$atakujacy=array(
					'name'=>'atak',
					'opancerzenie'=>$pancerz_at,
					'atak'=>(int)$newAtAT, 
					'obrona'=>(int)$newObAT

				);
				$broniacy=array(
					'name'=>'def',
					'opancerzenie'=>$pancerz_ob,
					'atak'=>(int)$newAtOB,
					'obrona'=>(int)$newObOB
				);
				
				$broniacy=$this->cios($atakujacy['atak'],$broniacy);
				$atakujacy=$this->cios($broniacy['atak'],$atakujacy);
		
				$pancerz_ob_po_ataku=$broniacy['opancerzenie'];
				$pancerz_at_po_ataku=$atakujacy['opancerzenie'];
		
				$straty_at=$atakujacy['dmg'];
				$straty_ob=$broniacy['dmg'];
				
				$endBAT = "Parametry po bitwie:<br>JEDNOSTKA ATAKUJĄCA:<br>
		-PANCERZ:".$pancerz_at_po_ataku."<br>
		-STRATY w PANCERZU:".$straty_at."<br><br>";
		
		$endBOB ="JEDNOSTKA ATAKOWANA:<br>
		-PANCERZ:".$pancerz_ob_po_ataku."<br>
		-STRATY w PANCERZU:".$straty_ob."<br><br>
		";
		if( $pancerz_at_po_ataku > 0 )
		{
			$pancerz_at_po_ataku = $pancerz_at - $straty_at;
			$PA=1;
		}
		else
		{
			$pancerz_at_po_ataku = 0;
			$PA = 0;
		}
		if( $straty_at == 0 )
		{
			$pancerz_at_po_ataku = $pancerz_at;
		}
		if( $pancerz_ob_po_ataku > 0 )
		{
			$pancerz_ob_po_ataku = $pancerz_ob - $straty_ob;
			$PO = 1;
		}
		else
		{
			$pancerz_ob_po_ataku = 0;
			$PO = 0;
		}
		if( $straty_ob == 0 )
		{
			$pancerz_ob_po_ataku = $pancerz_ob;
		}
	//-> Obliczamy nowe morale, jeśli straty zadane są większe niż otrzymane -> morale rośnie o +1	
	if( $straty_ob > $straty_at )
	{
		if( $attackID['Morale'] < 100 )
		{
			$n_morale_at = $attackID['Morale']+1;
		}
		else
		{
			$n_morale_at = 100;
		}
		if( $defenseID['Morale'] > 0 )
		{
			$n_morale_ob = $defenseID['Morale']-1;
		}
		else
		{
			$n_morale_ob = 0;
		}
	}
	else if( $straty_ob < $straty_at )
	{
		if( $defenseID['Morale'] < 100 )
		{
			$n_morale_ob = $defenseID['Morale']+1;
		}
		else
		{
			$n_morale_ob = 100;
		}
		if( $attackID['Morale'] > 0 )
		{
			$n_morale_at = $attackID['Morale']-1;
		}
		else
		{
			$n_morale_at = 0;
		}
	}
	else
	{
		$n_morale_at = $attackID['Morale'];
		$n_morale_ob = $defenseID['Morale'];
	}
        $unitsAT = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$attackID['id'].' ')->fetch();
        $unitsOB = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$defenseID['id'].' ')->fetch();
        $nazwa_at = $this -> unit -> ustalNazwe( (int)$unitsAT[0], $attackID['unitType'], $attackID['Specialty'], $attackTacticData['nazwa_at'] );
		$nazwa_ob = $this -> unit -> ustalNazwe( (int)$unitsOB[0], $defenseID['unitType'], $defenseID['Specialty'], $deffenceTacticData['nazwa_ob'] );
		
		
		
		
			switch( $attackID['unitType'] )
			{
				case '1':
					$atakujacy_opis= comp_battleE26 .' '. comp_battleE33;//"Odział piechoty w potyczce";
					break;
				case '2':
					$atakujacy_opis= comp_battleE27;//"Doborowa jednostka wojsk pancernych zaatakowała";
					break;
				case '3':
					$atakujacy_opis= comp_battleE28;//"Bateria artylerii przeciwlotniczej ostrzelała";
					break;
				case '4':
					$atakujacy_opis= comp_battleE29;//"Dywizjon samolotów zaatakował";
					break;
				case '5':
					$atakujacy_opis= comp_battleE30;//"Okręty wojenne zatakowały";
					break;
				case '6':
					$atakujacy_opis= comp_battleE31;//"Bateria artylerii ostrzelała";
					break;
				case '7':
					$atakujacy_opis= comp_battleE32;//"Miasto ostrzelało";
					break;
			}
			$atakujacy_opis=$nazwa_at.' '. comp_battleE33;
			switch( $defenseID['unitType'] )
			{
				case '1':
					$broniacy_opis= comp_battleE34 .' '. comp_battleE33;//"odział piechoty";
					break;
				case '2':
					$broniacy_opis= comp_battleE35;//"jednostkę wojsk pancernych";
					break;
				case '3':
					$broniacy_opis= comp_battleE36;//"baterię artylerii przeciwlotniczej";
					break;
				case '4':
					$broniacy_opis= comp_battleE37;//"dywizjon samolotów";
					break;
				case '5':
					$broniacy_opis= comp_battleE38;//"okręty wojenne";
					break;
				case '6':
					$broniacy_opis= comp_battleE39;//"baterię artylerii";
					break;
				case '7':
					$broniacy_opis= comp_battleE40;//"miasto";
					break;
			}
			$broniacy_opis=$nazwa_ob;
		
		//============================WARUNKI WZGLĘDEM ATAKUJĄCY-ATAKOWANY=================================================================================================
			
			
			
			
			//ATAK NA MIASTO
			if( $defenseID['unitType'] == 7 )
			{//gracz atakuje miasto
				if( $zasieg_strzalu_ob >= $dystans_strzalu )
				{
					if( $PA == 1 AND $PO == 1 )
					{
							$nr_operacji=10;
							$raport_warunek= comp_battleE41 .'! '. comp_battleE42;//Agresor atakuje miasto, Miasto pozostaje na planszy
					}
					else if( $PA == 1 AND $PO == 0 AND ( $attackID['unitType'] == 5 OR $attackID['unitType'] == 4 ) )
					{
							$nr_operacji=8;
							$raport_warunek= comp_battleE41 .'! ';// Agresor atakuje miasto, Miasto zostaje zniszczone, miasto ostrzelane artylerią okrętów wojennych lub zbombardowane nie może być ograbione. ';
					}
					else if( $PA == 1 AND $PO == 0 AND ( $attackID['unitType'] != 5 OR $attackID['unitType'] != 4 ) )
					{
							$nr_operacji=11;
							$raport_warunek= comp_battleE41 .'! '. comp_battleE43 .', '. comp_battleE44;// Miasto zostaje zniszczone, miasto zostaje ograbione.';
					}
					else if( $PA == 0 AND $PO == 0 )
					{
							$nr_operacji=12;
							$raport_warunek= comp_battleE41 .'!'. comp_battleE45;// Zacięte walki powodują, że obie jednostki zostają zniszczone';
					} 
					else if( $PA == 0 AND $PO == 1 )
					{
							$nr_operacji=13;
							$raport_warunek= comp_battleE41 .'! '. comp_battleE46; //Zacięta obrona ludności i wojsk wspierających obronę miasta powoduje zbyt duże straty w szeregach jednostki, jednostka zostaje zniszczona';
					}
				}
				else if( $zasieg_strzalu_ob < $dystans_strzalu )
				{
					if($PO == 0 AND ( $attackID['unitType'] == 5 OR $attackID['unitType'] == 4 ) )
					{
						$nr_operacji=9;
						$raport_warunek= comp_battleE41 .'! '. comp_battleE43 .', '. comp_battleE47 .'.!';//Miasto zostaje zniszczone, miasto ostrzelane artylerią okrętów wojennych lub zbombardowane nie może być ograbione. Atakujący nie ponosi strat.!';
					}
					else if( $PO == 0 AND ( $attackID['unitType'] != 5 OR $attackID['unitType'] != 4 ) )
					{
						$nr_operacji=14;
						$raport_warunek= comp_battleE41 .'! '. comp_battleE48 .'. '. comp_battleE49 .'!';//Agresor jest zbyt daleko aby obrona miasta mogła odpowiedzieć ogniem. Miasto zostaje zniszczone i ograbione!';
					}
					else if( $PO == 1 )
					{		 
						$nr_operacji=15;
						$raport_warunek= comp_battleE41 .'! '. comp_battleE48 .'. '. comp_battleE42 .'!';//Agresor jest zbyt daleko aby miasto mogło skutecznie się bronić
					}
				}
			}
			else if( $attackID['unitType'] == 4 ) 
			{//ATAK LOTNICTWO
                if( $defenseID['unitType'] == 4 )
				{//SAMOLOT ATAKUJE SAMOLOT, teraz
					if( $zasieg_strzalu_ob >= $dystans_strzalu )
					{
						if( $zasieg_strzalu_ob <= $dystans_strzalu )
						{											
							if( $PA == 1 AND $PO == 1 )
							{
								$nr_operacji=1;
								$raport_warunek = comp_battleE50 .'. '. comp_battleE51;
							}
							else if( $PA == 1 AND $PO == 0 )
							{
								$nr_operacji=2;
								$raport_warunek = comp_battleE50 .'. '. comp_battleE52;
							}
							else if( $PA == 0 AND $PO == 0 )
							{
								$nr_operacji=3;
								$raport_warunek= comp_battleE50 .'. '. comp_battleE45;
							}
							else if( $PA == 0 AND $PO == 1 )
							{
								$nr_operacji=6;
								$raport_warunek = comp_battleE50 .'. '. comp_battleE53;
							}
						}
						else
						{
							if( $PO == 0 )
							{
								$nr_operacji=4;
								$raport_warunek = comp_battleE54 .' '. $broniacy_opis .', '. comp_battleE55;
							}
							else if( $PO == 1 )
							{		 
								$nr_operacji=5;
								$raport_warunek = comp_battleE54 .' '. $broniacy_opis .', '. comp_battleE55;
							}	
						}
					}
					else if( $zasieg_strzalu_ob < $dystans_strzalu AND $defenseID['AntiAircraft'] == 0 )
					{
						if( $PO == 0 )
						{
							 $nr_operacji=4;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .', '. comp_battleE56;
						}
						else if( $PO == 1 )
						{		 
							 $nr_operacji=5;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .', '. comp_battleE57 .'. '. comp_battleE51;
						}
					}
				}
				else if( $defenseID['Specialty'] == 18 )
				{//SAMOLOT ATAKUJE OKRĘT PODWODNY ZANURZONY 
					//samolot bombarduje okręt podwodny w zanurzeniu, ten nie może odpowiedzieć.
					if( $attackID['Torpedo'] == 0  )
                    {
                        $battleArray['error'] = comp_battleE58 .'.';
                        echo json_encode($battleArray);
                        exit();
                    }
                    if( $PA == 1 AND $PO == 1 )
					{
						if( $active_oplot == 1 )
						{
							$nr_operacji=1;
							$raport_warunek = comp_battleE59 .'.';
						}
						else
						{
							$nr_operacji=5;
							$raport_warunek = comp_battleE59 .'.';
						}
					}
					else if( $PA == 1 AND $PO == 0 )
					{
						 $nr_operacji=4;
						 $raport_warunek = comp_battleE59 .'. '. comp_battleE60;
					}
				}
				else
				{//SAMOLOT ATAKUJE POZOSTAŁE KORPUSY
					if( $active_oplot == 1 )
					{//agresor poza zasiegiem obrońcy, ale obrońca otrzymuje wsparcie oplot
						if( $PA == 1 AND $PO == 1 )
						{
							$nr_operacji=1;
							$raport_warunek = comp_battleE54 .' '. $broniacy_opis .' '. comp_battleE61 .'. '. comp_battleE51;
						}
						else if( $PA == 1 AND $PO == 0 )
						{
							 $nr_operacji=2;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .' '. comp_battleE61 .'. '. comp_battleE62;
						}
						else if( $PA == 0 AND $PO == 0 )
						{
							 $nr_operacji=3;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .' '. comp_battleE61 .'.  '. comp_battleE45;
						}
						else if( $PA == 0 AND $PO == 1 )
						{
							 $nr_operacji=6;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .' '. comp_battleE61 .' '. comp_battleE63;
						}
					}
					else if( $defenseID['AntiAircraft'] == 1 ) 
					{
						if( $PA == 1 AND $PO == 1 )
						{
							 $nr_operacji=1;
							 $raport_warunek = comp_battleE54 .' '. comp_battleE64 .'!. '. comp_battleE51;
						}
						else if( $PA == 1 AND $PO == 0 )
						{
							 $nr_operacji=2;
							 $raport_warunek = comp_battleE54 .' '. comp_battleE64 .'! '. comp_battleE52;
						}
						else if( $PA == 0 AND $PO == 0 )
						{
							 $nr_operacji=3;
							 $raport_warunek = comp_battleE54 .' '. comp_battleE64 .'! '. comp_battleE45;
						}
						else if( $PA == 0 AND $PO == 1 )
						{
							 $nr_operacji=6;
							 $raport_warunek = comp_battleE54 .' '. comp_battleE64 .'! '. comp_battleE53;
						}
					}
					else
					{
						if( $PO == 0 )
						{
							 $nr_operacji=4;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .' , '. comp_battleE52 .'. '. comp_battleE55;
						}
						else if( $PO == 1 ) 
						{		 
							 $nr_operacji=5;
							 $raport_warunek = comp_battleE54 .' '. $broniacy_opis .'. '. comp_battleE55 .'. '. comp_battleE51;
						}
					}
				}
			}
			else
			{//ATAK POZOSTAŁYCH KORPUSÓW NA INNE KORPUSY
                if( $attackID['Specialty'] == 18 AND $defenseID['unitType'] != 5  )
                {
                    $battleArray['error'] = comp_battleE65 .'. '. comp_battleE66;
                    echo json_encode($battleArray);
                    exit();
                }
                if( $attackID['Torpedo'] == 0 AND $defenseID['Specialty'] == 18  )
                {
                    $battleArray['error'] = comp_battleE67 .'.';
                    echo json_encode($battleArray);
                    exit();
                }
				if( $defenseID['unitType'] == 4 AND $attackID['unitType'] != 4 AND $attackID['unitType'] != 3 AND $zasiegStrzaluOPLOT_at < $dystans_strzalu )
					//strzał przeciwlotniczy, samolot jest za daleko
                {
                    $battleArray['error'] = comp_battleE68;
                    echo json_encode($battleArray);
                    exit();
                }
                if( $zasieg_strzalu_ob >= $dystans_strzalu ){
					if( $zasieg_strzalu_od_ob <= $dystans_strzalu )
					{
						if( $attackID['Torpedo'] == 1 AND $defenseID['Specialty'] == 18 )
						{
							if( $PO == 1 )
							{
								$nr_operacji=5;
								$raport_warunek = comp_battleE69 .'. '. comp_battleE70 .'. '. comp_battleE51;
							}
							else if($PO == 0 )
							{
								$nr_operacji=4;
								$raport_warunek = comp_battleE69 .', '. comp_battleE52 .'. '. comp_battleE70;

							}
						}else{
							if( $PA == 1 AND $PO == 1 )
							{
								$nr_operacji=1;
								$raport_warunek = $atakujacy_opis.' '.$broniacy_opis.'. '. comp_battleE51;
							}
							else if( $PA == 1 AND $PO == 0 )
							{
								$nr_operacji=2;
								$raport_warunek = $atakujacy_opis.' '.$broniacy_opis.'. '. comp_battleE71;
							}
							else if( $PA == 0 AND $PO == 0 )
							{
								$nr_operacji=3;
								$raport_warunek = $atakujacy_opis.' '.$broniacy_opis.'. '. comp_battleE45;
							}
							else if( $PA == 0 AND $PO == 1 )
							{
								$nr_operacji = 6;
								$raport_warunek = $atakujacy_opis.' '.$broniacy_opis.'. '. comp_battleE53;
							}
						}
					}
					else
					{
						if( $PO == 0 )
						{
							$nr_operacji=4;
							$raport_warunek = $atakujacy_opis.' '.$broniacy_opis.', '. comp_battleE52 .'. '. comp_battleE55;
						}
						else if( $PO == 1 )
						{		 
							$nr_operacji=5;
							$raport_warunek = $atakujacy_opis .' '. $broniacy_opis .', '. comp_battleE55 .'. '. comp_battleE51;
						}
					}
			}
			else if( $zasieg_strzalu_ob < $dystans_strzalu )
			{
				if( $PO == 0 )
				{
					$nr_operacji=4;
					$raport_warunek = $atakujacy_opis .' '. $broniacy_opis .', '. comp_battleE56;
				}
				else if( $PO == 1 )
				{		 
					$nr_operacji=5;
					$raport_warunek = $atakujacy_opis .' '. $broniacy_opis .', '. comp_battleE57 .'. '. comp_battleE51;
				}
			}
		}


//wpływ pogody
$battleArray['error'] .= $pogoda_at_txt;
$battleArray['error'] .= $pogoda_ob_txt;
//wpływ sztabu
$battleArray['error'] .= $raport_sztab_txtAT;
$battleArray['error'] .= $raport_sztab_txtOB;

//dane startowe
$battleArray['error'] .= $startAT;
$battleArray['error'] .= $startOB;
//ostrzął oplot
$battleArray['error'] .= $raport_oplot;
$battleArray['error'] .= $endOPLOTAT;
// gradacja e wznględu na odległość
$battleArray['error'] .= $gradAT;
$battleArray['error'] .= $gradOB;
//dane po ostrzale oplot


if( $straty_at==0 AND $straty_atOPLOT==0 AND $straty_ob==0 ){
	$battleArray['error'] .= comp_battleE72;
	echo json_encode($battleArray);
	exit();
}

$battleArray['error'] .= 'numer operacji='.$nr_operacji.'<br>'.$raport_warunek.'<br><br>';
//dane po bitwie
$battleArray['error'] .= $endBAT;
$battleArray['error'] .= $endBOB;
$battleArray['error']='';
$n_doswiad_ob = round( ($straty_at/100),2);
$n_doswiad_at = round( ($straty_ob/100),2);
$n_doswiadczenie_at = $attackID['unitExperience']+$n_doswiad_at;//doÂ¶wiadczenie suma jendostki at!
$n_doswiadczenie_ob = $defenseID['unitExperience']+$n_doswiad_ob;//doÂ¶wiadczenie  suma jendostki 







if($damage_at_proc<100){
		$damage_proc_txt= raport_txt19 .': - '.round($damage_at_proc,2).' %';
	}else{
		$damage_proc_txt='';
	}
$n_doswiad_ob=round( ($straty_at/100),2);
$n_doswiad_at=round( ($straty_ob/100),2);
$n_doswiadczenie_at = $attackID['unitExperience']+$n_doswiad_at;//doÂ¶wiadczenie suma jendostki at!
$n_doswiadczenie_ob = $defenseID['unitExperience']+$n_doswiad_ob;//doÂ¶wiadczenie  suma jendostki 
$n_punkty_at = $this -> account -> points + $n_doswiad_at;//do wyświetlenia w tabeli
$n_punkty_ob = $defenseID['points'] +$n_doswiad_ob;//do wyświetlenia w tabeli
	$zanurzenie_at=0;
	$zanurzenie_ob=0;
	
	
	
	if( $attackID['Specialty'] == 18 )
	{
		$zanurzenie_at = 1;
	}
	if( $defenseID['Specialty'] == 18 )
	{
		$zanurzenie_ob = 1;
	}
	if( $straty_ob > $straty_at )
	{
		if( $attackID['Morale'] < 100 )
		{
			$n_morale_at = $attackID['Morale'] + 1;
		}
		else
		{
			$n_morale_at = 100;
		}
		if( $defenseID['Morale'] > 0 )
		{
			$n_morale_ob = $attackID['Morale'] - 1;
		}
		else
		{
			$n_morale_ob = 0;
		}
	}
	else if( $straty_ob < $straty_at )
	{
		if( $defenseID['Morale'] < 100 )
		{
			$n_morale_ob = $defenseID['Morale'] + 1;
		}
		else
		{
			$n_morale_ob = 100;
		}
		if( $attackID['Morale'] > 0 )
		{
			$n_morale_at = $attackID['Morale'] - 1;
		}
		else
		{
			$n_morale_at = 0;
		}
	}
	else
	{
		$n_morale_at = $attackID['Morale'];
		$n_morale_ob = $defenseID['Morale'];
	}
	
	$usunJednostkeAT=false;
	
	if($jednostkawidoczna==1){
		$usunJednostkeOB=false;
	}else{
		$usunJednostkeOB=true;
	}
	
if( $nr_operacji == 1 )
{
	//peĹ_ny raport, obie jednostki tracÄ_, ale pozostajÄ_ na planszy
	$this->nadpisz_jednostke($pancerz_at_po_ataku,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty']);
	$this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty']);
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
	$this->dodaj_tab_alert( $defenseID['id'], $attackID['id'], $defenseID['playerID'], $nazwa_ob, $nazwa_at, $user_at, $straty_ob, $datateraz );
}
else if( $nr_operacji == 2 )
{
   //agresor i obroĹ_ca ponosza straty, ale obroĹ_ca zostaje zniszczony
	$this->nadpisz_jednostke($pancerz_at_po_ataku,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
	$this->oblicz_kase($defenseID['id'], $cena_ob, $nazwa_ob, $nazwa_at);
	//$this->nadpisz_tab_stat_jednostki($defenseID['id'],$rok,$miesiac,$attackID['playerID'],$rozgrywka,$Session->gracz['rozgrywka_swiatowa'],AppSession::read('swiat'));
	$this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
	$usunJednostkeOB=true;
}
else if( $nr_operacji == 3 )
{
	//obie jednostki zostajÄ_ zniszczone
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
	$this->oblicz_kase($defenseID['id'],$cena_ob,$nazwa_ob,$nazwa_at );
	$this->oblicz_kase($attackID['id'],$cena_at,$nazwa_at,$nazwa_ob);
	//$this->nadpisz_tab_stat_jednostki($defenseID['id'],$rok,$miesiac,$attackID['playerID'],$rozgrywka,$Session->gracz['rozgrywka_swiatowa'],AppSession::read('swiat'));
	//$this->nadpisz_tab_stat_jednostki($attackID['id'],$rok,$miesiac,$defenseID['playerID'],$rozgrywka,$Session->gracz['rozgrywka_swiatowa'],AppSession::read('swiat'));
	$this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
	$this->usun_jednostke($attackID['id'],$defenseID['playerID'], $defenseID['id']);
	$usunJednostkeAT=true;
	$usunJednostkeOB=true;
}
else if( $nr_operacji == 4 )
{
	//agresor atakuje, obroĹ_ca zostaje zniszczony, traci tylko obroĹ_ca 
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->nadpisz_jednostke($pancerz_at,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	$this->oblicz_kase($defenseID['id'],$cena_ob,$nazwa_ob,$nazwa_at);
	//$this->nadpisz_tab_stat_jednostki($defenseID['id'],$rok,$miesiac,$attackID['playerID'],$rozgrywka,$Session->gracz['rozgrywka_swiatowa'],AppSession::read('swiat'));
	$this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
	$usunJednostkeOB=true;
	$pancerz_at_po_ataku=$pancerz_at;
}
else if( $nr_operacji == 5 )
{
	//agresor atakuje,  traci tylko obroĹ_ca. obie jednostki pozostajÄ_ na planszy
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
	$this->nadpisz_jednostke($pancerz_at,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	$this->dodaj_tab_alert( $defenseID['id'], $attackID['id'], $defenseID['playerID'], $nazwa_ob, $nazwa_at, $user_at, $straty_ob, $datateraz );
	$pancerz_at_po_ataku=$pancerz_at;
}
else if( $nr_operacji == 6 )
{
	//agresor atakuje, obroĹ_ca siÄ_ broni, agresor zostaje zniszczony	
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	
	$this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
	
	
	$this->oblicz_kase($attackID['id'],$cena_at,$nazwa_ob,$nazwa_at);
	
	//$this->nadpisz_tab_stat_jednostki($attackID['id'],$rok,$miesiac,$defenseID['playerID'],$rozgrywka,$Session->gracz['rozgrywka_swiatowa'],AppSession::read('swiat'));
	$this->usun_jednostke($attackID['id'],$defenseID['playerID'], $defenseID['id']);
	$usunJednostkeAT=true;	
}
else if( $nr_operacji == 7 )
{
	//Atakuje łódĽ podwodna w zanurzeniu. Obie jednostki pozostaj± na planszy, agresor bez strat
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
	$this->nadpisz_jednostke($pancerz_at,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	$this->dodaj_tab_alert( $defenseID['id'], $attackID['id'], $defenseID['playerID'], $nazwa_ob, $nazwa_at, $user_at, $straty_ob, $datateraz );
	$pancerz_at_po_ataku=$pancerz_at;
}
else if( $nr_operacji == 8 )
{
	//$raport_warunek='Agresor atakuje miasto! Miasto zostaje zniszczone, ale nie ograbione';	
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->nadpisz_jednostke($pancerz_at_po_ataku,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
    $this->usun_jednostke($defenseID['id'],$attackID['playerID'],$attackID['id']);
}
else if( $nr_operacji == 9 )
{
	//$raport_warunek='Agresor atakuje miasto! Miasto zostaje zniszczone, ale nie ograbione, agresor poza zasiegiem miasta';	
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
    $pancerz_at_po_ataku=$pancerz_at;
}
else if( $nr_operacji == 10 )
{
	//$raport_warunek='Agresor atakuje miasto! Miasto pozostaje na planszy';	 
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->nadpisz_jednostke($pancerz_at_po_ataku,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	//dane do wyświetlenia
}
else if( $nr_operacji == 11 )
{
	//$raport_warunek='Agresor atakuje miasto! Miasto zostaje zniszczone';	
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
	//$this->nadpisz_dane_miasto($pancerz_ob_po_ataku, $nrHexa_ob);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->nadpisz_jednostke($pancerz_at_po_ataku,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	//$this->nadpisz_tab_stat_jednostki_miasto($attackID['playerID'], $rok, $miesiac, $rozgrywka);
    $this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
    if( $stolica == 0 )
	{
		$this->podziel_kase_gracza($attackID['playerID'],$defenseID['playerID']);
	}
}
else if( $nr_operacji == 12 )
{
	//$raport_warunek='Agresor atakuje miasto! Zacięte walki powoduj±, że obie jednostki pozstaj± zniszczone';	 
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
	$this->podziel_kase_gracza($attackID['playerID'],$defenseID['playerID']);	 
	$this->oblicz_kase($attackID['id'],$cena_at,$nazwa_at,$nazwa_ob);
	$this->oblicz_kase($defenseID['id'],$cena_ob,$nazwa_ob,$nazwa_at);
	$this->usun_jednostke($attackID['id'],$defenseID['playerID'], $defenseID['id']);
	$this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
	$usunJednostkeAT=true;
	$usunJednostkeOB=true;

	//echo dodaj_raport_miasto4($obronca);
	//echo dodaj_staty_za_miasto($atakujacy);
	//dane do wyświetlenia
}
else if( $nr_operacji == 13 )
{
	//$raport_warunek='Agresor atakuje miasto! Zacięta obrona ludno¶ci i wojsk wspieraj±cych obronę miasta powoduje zbyt duże straty w szeregach jednostki, jednostka zostaje zniszczona';
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
	$this->oblicz_statystyki_tab_walka($attackID['id'], $defenseID['id'], $attackID['playerID'], $defenseID['playerID'], $straty_at, $nazwa_ob, $nazwa_at, $user_ob,$nacjaJednostki_ob,$accountIDOB);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->oblicz_kase($attackID['id'],$cena_at,$nazwa_at,$nazwa_ob);
	$this->usun_jednostke($attackID['id'],$defenseID['playerID'], $defenseID['id']);
	$usunJednostkeAT=true;
	//echo dodaj_raport($atakujacy);
}
else if( $nr_operacji == 14 )
{
	//$raport_warunek='Agresor atakuje miasto! Agresor jest zbyt daleko aby obrona miasta mogła odpowiedzieć ogniem. Miasto zostaje zniszczone!';	 	 
	$this->podziel_kase_gracza($attackID['playerID'],$defenseID['playerID']);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->usun_jednostke($defenseID['id'],$attackID['playerID'], $attackID['id']);
    if( $stolica == 0 )
    {
        $this->podziel_kase_gracza($attackID['playerID'],$defenseID['playerID']);
    }

    $pancerz_at_po_ataku=$pancerz_at;
}
else if( $nr_operacji == 15 )
{
	//$raport_warunek='Agresor atakuje miasto! Agresor jest zbyt daleko aby miasto mogło skutecznie się bronić. Miasto pozostaje na planszy!';				
	$this->oblicz_statystyki_tab_walka($defenseID['id'], $attackID['id'], $defenseID['playerID'], $attackID['playerID'], $straty_ob, $nazwa_at, $nazwa_ob, $user_at,$nacjaJednostki_at,$accountIDAT);
    $this->nadpisz_jednostke($pancerz_ob_po_ataku,$n_morale_ob,$n_doswiad_ob,$defenseID['id'],$bonus_ob,$defenseID['belongHQ'],$defenseID['unitType'],$defenseID['accountID'],$nacjaJednostki_ob,$defenseID['Specialty'] );
    $this->nadpisz_jednostke($pancerz_at,$n_morale_at,$n_doswiad_at,$attackID['id'],$bonus_at,$attackID['belongHQ'],$attackID['unitType'],$attackID['accountID'],$nacjaJednostki_at,$attackID['Specialty'] );
	$pancerz_at_po_ataku=$pancerz_at;
}
	
//if( $defenseID['unitType'] == 7 )
//{
    //sprawdzam, zy gracz atakuje miasto, jesli miasto, sprawdzam czy pozostały jakieś stolice
    $this -> unit -> checkCapital(1,$this -> world -> id );
//}


	$dane_atakujacego=array(
	    'obrazek_at'              => $obrazek_at,
		'user_at'                 => $this -> account -> login,
		'nazwa_at'                => $nazwa_at,
		'pancerzprzed_at'         => $pancerz_at,
		'pancerzprzedOplot_at'    => $attackID['DefensePoints'],
		'doswiadczenieprzed_at'   => $attackID['unitExperience'],
		'punkty_gracza_przed_at'  => $this -> account -> points,
		'moraleprzed_at'          => $attackID['Morale'],
		'pancerzpo_at'            => $pancerz_at_po_ataku,
		'n_doswiadczenie_at'      => $n_doswiadczenie_at,
		'n_punkty_at'             => $n_punkty_at,
		'n_Morale'                => $n_morale_at,
		'idGracza_at'             => $attackID['playerID'],
		'raport_warunek'          => $raport_warunek.', odległość strzału='. $dystans_strzalu,
		'raport_oplot'            => $raport_oplot,
		'numer_operacji'          => $nr_operacji,
		'dzwiek'                  => 1,
		'korpus_at'               => $attackID['unitType'],
		'korpus_ob'               => $defenseID['unitType'],
		'dystans_strzalu'         => $dystans_strzalu,
		'pogoda_at'               => $pogoda_at_txt,//atak obniżony do x % zwiazany z pogodą
		'mgla_at'                 => $mgla_txt,//atak obniżony do 50 % zwiazany ze strzałem w mgłę
		'sztab_at'                => $raport_sztab_txtAT,//bonus za sztab
		'raportowanie_at'         => $this -> account -> reports,
		'data_bitwy'              => date("Y-m-d H:i:s"),
		'zanurzenie_at'           => $zanurzenie_at,
		'x'                       => $attackID['x'],
		'y'                       => $attackID['y'],
		'endOPLOTAT'              => $endOPLOTAT

		
	);
	$dane_obroncy=array(
	    'obrazek_ob'              => $obrazek_ob,
		'user_ob'                 => $user_ob,
		'nazwa_ob'                => $nazwa_ob,
		'pancerzprzed_ob'         => $pancerz_ob,
		'doswiadczenieprzed_ob'   => $defenseID['unitExperience'],
		'punkty_gracza_przed_ob'  => $defenseID['points'],
		'moraleprzed_ob'          => $defenseID['Morale'],
		'pancerzpo_ob'            => $pancerz_ob_po_ataku,
		'n_doswiadczenie_ob'      => $n_doswiadczenie_ob,
		'n_punkty_ob'             => $n_punkty_ob,
		'idGracza_ob'             => $defenseID['playerID'],
		'pogoda_ob'               => $pogoda_ob_txt,//obrona obniżona do x % zwiazany z pogodą
		'sztab_ob'                => $raport_sztab_txtOB,//bonus za sztab
		'n_Morale'                => $defenseID['Morale'],
		'x'                       => $defenseID['x'],
		'y'                       => $defenseID['y'],
		'endOPLOTOB'              => $endOPLOTOB
	);
		$battleArray['bitwa']=array(
			'dane_atakujacego' => $dane_atakujacego,
			'dane_obroncy'     => $dane_obroncy
		);
		$battleArray['bitwaT']='
		<div class="boxRaport">
		<div id="tab_data">Dnia '.date("Y-m-d H:i:s").'</div>
		<div id="tab_0_1" style="background:url(./app/templates/assets/images/'.$dane_atakujacego['obrazek_at'].') no-repeat;"></div>
		<div id="tab_1_1">'.$dane_atakujacego['user_at'].'</div>
		<div id="tab_2_1">'.$dane_atakujacego['nazwa_at'].'</div>
		<div id="pancerz_przed1" class="dane_okno">'. pancerz_txt .'</div>
		<div id="pancerz_przed2" class="dane_okno">'. pancerz_txt .'</div>
		<div id="doswiadczenie_przed1" class="dane_okno">'. doswiadczenie_txt .'</div>
		<div id="doswiadczenie_przed2" class="dane_okno">'. doswiadczenie_txt .'</div>
		<div id="punktygracza_przed1" class="dane_okno">'. statystyki_punkty .'</div>
		<div id="punktygracza_przed2" class="dane_okno">'. statystyki_punkty .'</div>
		<div id="morale_przed1" class="dane_okno">'. morale_txt .'</div>
		<div id="morale_przed2" class="dane_okno">'. morale_txt .'</div>
		<div id="tab_3_1" class="styl_tab">'.$dane_atakujacego['pancerzprzedOplot_at'].'</div>
		<div id="tab_4_1" class="styl_tab">'.$dane_atakujacego['doswiadczenieprzed_at'].'</div>
		<div id="tab_5_1" class="styl_tab">'.$dane_atakujacego['punkty_gracza_przed_at'].'</div>
		<div id="tab_6_1" class="styl_tab">'.$dane_atakujacego['moraleprzed_at'].'</div>
		<div id="tab_7_1" class="styl_tab">'.$dane_atakujacego['pancerzpo_at'].'</div>
		<div id="tab_8_1" class="styl_tab">'.$dane_atakujacego['n_doswiadczenie_at'].'</div>
		<div id="tab_9_1" class="styl_tab">'.$dane_atakujacego['n_punkty_at'].'</div>
		<div id="tab_10_1" class="styl_tab">'.$dane_atakujacego['n_Morale'].'</div>
		<div id="tab_0_2" class="minPanel" style="background:url(./app/templates/assets/images/'.$dane_obroncy['obrazek_ob'].') no-repeat;" coord="'.$defenseID['x'].'_'.$defenseID['y'].'"></div>
		<div id="tab_1_2">'.$dane_obroncy['user_ob'].'</div>
		<div id="tab_2_2">'.$dane_obroncy['nazwa_ob'].'</div>
		<div id="tab_3_2" class="styl_tab">'.$dane_obroncy['pancerzprzed_ob'].'</div>
		<div id="tab_4_2" class="styl_tab">'.$dane_obroncy['doswiadczenieprzed_ob'].'</div>
		<div id="tab_5_2" class="styl_tab">'.$dane_obroncy['punkty_gracza_przed_ob'].'</div>
		<div id="tab_6_2" class="styl_tab">'.$dane_obroncy['moraleprzed_ob'].'</div>
		<div id="tab_7_2" class="styl_tab">'.$dane_obroncy['pancerzpo_ob'].'</div>
		<div id="tab_8_2" class="styl_tab">'.$dane_obroncy['n_doswiadczenie_ob'].'</div>
		<div id="tab_9_2" class="styl_tab">'.$dane_obroncy['n_punkty_ob'].'</div>
		<div id="tab_10_2" class="styl_tab">'.$dane_obroncy['n_Morale'].'</div>
		<div id="raport_bojowy"> '.$dane_atakujacego['raport_warunek'].'<br>'.$dane_atakujacego['raport_oplot'].'<br>'.$dane_atakujacego['mgla_at'].'<br>'.$dane_atakujacego['pogoda_at'].'<br>'.$dane_atakujacego['sztab_at'].'<br>operacja nr='.$dane_atakujacego['numer_operacji'].'
		, dystans:'.$dystans_strzalu.', zasieg strzału='.$zasieg_strzalu_at.'
		kto strzela OPLT='.$ktostrzelaAA.'</div>';
		
		//<div id="raport_bojowy">atak AT='.$atak_at.', atak AT obliczony='.$newAtAT.'<br> atak OB='.$atak_ob.', atak OB obliczony='.$newAtOB.'</div>
		
		
		if( $this -> account -> sounds == 1 ){
			
			if( $attackID['unitType'] == 4 AND $defenseID['unitType'] != 4 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/airbomb.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/airbomb.mp3"/></audio</div>';
			}else if( $attackID['unitType'] == 6 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/artyleria.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/artyleria.mp3"/></audio></div>';
			}else if( $attackID['unitType'] == 4 AND $defenseID['unitType'] == 4 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/airbattle.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/airbattle.mp3"/></audio></div>';
			}else if( $attackID['unitType'] == 1 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/mg.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/mg.mp3"/></audio></div>';
			}else if( $attackID['unitType'] == 2 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/atak_czolg.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/atak_czolg.mp3"/></audio></div>';
			}else if( $attackID['unitType'] == 5 AND $zanurzenie_at != 1 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/sea_gun.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/sea_gun.mp3"/></audio></div>';
			}else if( $attackID['unitType'] == 5 AND $zanurzenie_at == 1 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/torpeda.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/torpeda.mp3"/></audio></div>';
			}
		}
		$battleArray['temat']= $nazwa_at." vs ".$nazwa_ob;
		$tablica_bitwa = serialize($battleArray['bitwa']);
		//obniżam ilość tur za potyczkę
		
		$updateLiczniki = '';
		//zapisujemy dane jednostki
        if($up1==1){
			$updateLiczniki = ',`timing1`='.$licznik1;
		}
		if($up2==1){
			$updateLiczniki = ',`timing2`='.$licznik2;
		}
		
		$this -> db -> query('UPDATE `units` SET  `unitTurn`=(`unitTurn`-1) '.$updateLiczniki.'  WHERE id='.$attackID['id'].' ');
	
		if( $this -> account -> SponsorAccount > $this -> init -> time AND ( $this -> account -> reports == 0 OR $this -> account -> reports == 3 ) )
		{
			if( $nr_operacji != 2 AND $nr_operacji != 3 AND $nr_operacji != 4 )
			{
				$this -> db -> exec('INSERT INTO  `battleRaport` ( `data`, `playerID`, `descRaport`,`seen`, `topic`, `attackedUnit` ) values ( '.$this -> init -> time .', '.$attackID['playerID'].', \''.$tablica_bitwa.'\',\'1\',\''.$battleArray['temat'].'\','.$attackID['id'].' ) ');
				$this -> db -> exec('INSERT INTO  `battleRaport` ( `data`, `playerID`, `descRaport`,`seen`, `topic`, `attackedUnit` ) values ( '.$this -> init -> time .', '.$defenseID['playerID'].', \''.$tablica_bitwa.'\',\'0\',\''.$battleArray['temat'].'\','.$defenseID['id'].' ) ');
			}
			else
			{
				$this -> db -> exec('INSERT INTO  `battleRaport` ( `data`, `playerID`, `descRaport`,`seen`, `topic`, `attackedUnit` ) values ( '.$this -> init -> time .', '.$attackID['playerID'].', \''.$tablica_bitwa.'\',\'1\',\''.$battleArray['temat'].'\','.$attackID['id'].' ) ');
			}
		}
		else
		{
			$this -> db -> exec('INSERT INTO  `battleRaport` ( `data`, `playerID`, `descRaport`,`seen`, `topic`, `attackedUnit` ) values ( '.$this -> init -> time .', '.$attackID['playerID'].',\''.$tablica_bitwa.'\',\'0\',\''.$battleArray['temat'].'\','.$attackID['id'].' ) ');
			$this -> db -> exec('INSERT INTO  `battleRaport` ( `data`, `playerID`, `descRaport`,`seen`, `topic`, `attackedUnit` ) values ( '.$this -> init -> time .', '.$defenseID['playerID'].', \''.$tablica_bitwa.'\',\'0\',\''.$battleArray['temat'].'\','.$defenseID['id'].' ) ');
		}

        $arrPlutons = array();
        $arrDataCount = $this -> db -> query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits` WHERE `connectUnit`='.$attackID['id'].'');
        foreach ($arrDataCount as $rowTDA)
        {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
		
		$arrDataCount2 = $this -> db -> query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits` WHERE `connectUnit`='.$defenseID['id'].'');
        foreach ($arrDataCount2 as $rowTDD)
        {
            $arrPlutons[$rowTDD['connectUnit']][] = $rowTDD['tacticalDataID'];
        }
		
        $arrDataCount -> closeCursor();
		$arrDataCount2 -> closeCursor();
		// Deklaracja tablicy wysyłanej do mapy
        $vac = 0;
        if( $defenseID['playerVacation'] > $this -> init -> time )
        {
            $vac = date("Y-m-d H:i:s", $defenseID['playerVacation']);
        }

		// Units_range usuwanie danych
		$arrATNotSeenUnits = [];
		if ($usunJednostkeAT)
		{
			// Jednostka atakująca
			$arrATReturnData = $this -> unit -> checkNotSeenUnits($attackID['id'], $attackID['x'], $attackID['y'], true);
			$arrATNotSeenUnits = $arrATReturnData['arrNotSeenUnits'];
		}
		$arrOBNotSeenUnits = [];
		if ($usunJednostkeOB)
		{
			$arrOBReturnData = $this -> unit -> checkNotSeenUnits($defenseID['id'], $defenseID['x'], $defenseID['y'], true);
			$arrOBNotSeenUnits = $arrOBReturnData['arrNotSeenUnits'];
		}

		// Sprawdzam czy jednostka jest widoczna dla wroga
		$intIsSeen = false;
		$arrCheckNSU = $this -> db -> query('SELECT `seenUnitID` FROM `units_range` WHERE `seenUnitID`='.$attackID['id'].' LIMIT 1') -> fetch();
		if (isset($arrCheckNSU['se​enUnitID'])) $intIsSeen = true;
		$arrCheckNSU = null;

		// atakujacy
		$arrUnits = [];
		if( $this -> world -> timing == 1 ){
			$turyPoAkcji = $intTury;
		}else{
			$turyPoAkcji = $attackID['unitTurn'] - 1;
		}
		
		
		
		$arrUnits['myUnit'] = array(
			'removeUnit' => $usunJednostkeAT,
			'addUnit' => false,
			'x' => $attackID['x'],
			'y' => $attackID['y'],
			'id' => $attackID['id'],
			'view' => $widocznosc_at,
			'fromCity' => false,
			'nation' => $this -> account -> nation,
			'uid' => array($attackID['tacticalDataID']),
			'unitType' => $attackID['unitType'],
			'owner' => $this -> account -> playerID,
			'td' => (isset($arrPlutons[ $attackID['id'] ]) ? $arrPlutons[ $attackID['id'] ] : array() ),
			'Specialty' => $attackID['Specialty'],
			'unitTurn' => $turyPoAkcji,
			'DefensePoints' => $pancerz_at_po_ataku,
			'FieldArtillery' => $attackID['FieldArtillery'],
			'Torpedo' => $attackID['Torpedo'],
			'Morale' => $n_morale_at,
			'experience' => $n_doswiadczenie_at,
			'UnderWater' => $attackID['UnderWater'],
			'nickName' => $attackID['login'],
			'belongHQ' => $this -> unit -> checkStuff( $attackID['id'] ),
			'timing1' => $licznik1,
            'timing2' => $licznik2,			
			'nazwa' => $nazwa_at,
			'un' => ''
		);
		
		$arrUnits['enemyUnit'] = array(
			'removeUnit' => $usunJednostkeOB,
			'addUnit' => false,
			'x' => $defenseID['x'],
			'y' => $defenseID['y'],
			'id' => $defenseID['id'],
			'view' => $widocznosc_ob,
			'fromCity' => false,
			'nation' => $defenseID['nation'],
			'unitTurn' => $defenseID['unitTurn'],
			'unitType' => $defenseID['unitType'],
			'uid' => $defenseID['tacticalDataID'],
			'owner' => $defenseID['playerID'],
			'td' => (isset($arrPlutons[ $defenseID['id'] ]) ? $arrPlutons[ $defenseID['id'] ] : array() ),
			'Specialty' => $defenseID['Specialty'],
			'DefensePoints' => $pancerz_ob_po_ataku,
			'FieldArtillery' => $defenseID['FieldArtillery'],
			'Torpedo' => $defenseID['Torpedo'],
			'Morale' => $n_morale_ob,
			'experience' => $n_doswiadczenie_ob,
			'UnderWater' => $defenseID['UnderWater'],
			'nickName' => $defenseID['login'],
			'timing1' => $defenseID['timing1'],
            'timing2' => $defenseID['timing2'],	
			'nazwa' => $nazwa_ob,
			'un' => '',
			'playerVacation' => $vac,
			'CityName' => $defenseID['CityName']
		);
		
		
		
		
        $arrAlarmaData = array();
        $arrAlarmaData['playerID'] = $defenseID['playerID'];
            if( $defenseID['SponsorAccount'] > $this -> init -> time  )
            {
                $arrAlarmaData['zarowa']   = $defenseID['opticalAlert'];
            }
            else
            {
                $arrAlarmaData['zarowa']   = 0;
            }
        $arrAlarmaData['x']        = $defenseID['x'];
        $arrAlarmaData['y']        = $defenseID['y'];
		
		
		
		
		
		$arrayData = array(
			'activity' => 'afterFight',
			'chanData' => array(
				// Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
				array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
					'data' => array(
						'updateUnitData' => $arrUnits,
						'enemyUnitsData' => $arrATNotSeenUnits,
						'alarmaData' => $arrAlarmaData

					),
				),
			)
		);
		
		
		$arrUnitsToEnemy['myUnit'] = $arrUnits['enemyUnit'];
		if ($intIsSeen === true || $arrUnits['myUnit']['addUnit'] === true)
		{
			// Informacja dla wrogów jeśli mnie widzą jt
			$arrUnitsToEnemy['enemyUnit'] = $arrUnits['myUnit'];
		}
		$arrayData['chanData'][] = array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
			'data' => array(
				'updateUnitData' => $arrUnitsToEnemy,
				'enemyUnitsData' => $arrOBNotSeenUnits,
                'alarmaData' => $arrAlarmaData
			)
		);
		

		$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
		echo json_encode($battleArray);
		exit();

}

	private function cios($atakujacy,$broniacy)
	{
		$br_o=$broniacy['obrona'];
			if($br_o>=80){
				$p=0.80;
			}elseif($br_o<10){
				$p='0.0'.$br_o;
			}else{
				$p='0.'.$br_o;
			}
			$r=($atakujacy*$p);
			$dmg=round($atakujacy-$r);
			$broniacy['opancerzenie'] = $broniacy['opancerzenie']-$dmg;
			$broniacy['dmg']          = $dmg;
		return $broniacy;
	}
	
	private function zmien_korpus($dane)
	{
		switch($dane){
			case '1':
				$rodzaj_korpusu='piechota';
			break;
			case '2':
				$rodzaj_korpusu='pancerne';	
			break;
			case '3':
				$rodzaj_korpusu='przeciwlotnicze';		
			break;
			case '4':
				$rodzaj_korpusu='lotnictwo';		
			break;
			case '5':
				$rodzaj_korpusu='flota';	
			break;
			case '6':
				$rodzaj_korpusu='artyleria';	
			break;
			case '7':
				$rodzaj_korpusu='piechota';	
			break;
			case 'piechota':
				$rodzaj_korpusu=1;		
			break;
			case 'pancerne':
				$rodzaj_korpusu=2;
			break;
			case 'przeciwlotnicze':
				$rodzaj_korpusu=3;
			break;
			case 'lotnictwo':
				$rodzaj_korpusu=4;	
			break;
			case 'flota':
				$rodzaj_korpusu=5;
			break;
			case 'artyleria':
				$rodzaj_korpusu=6;
			break;
			default:
		}
		return $rodzaj_korpusu;
	}
	
	private function calcParameters($experience,$morale,$defensePoints,$atak, $obrona,$hardGame)
	{
		$data=array();
		if($experience<=0)
		{
			$procent_at=0;
			$procent_ob=0;					
		}
		else
		{
			$procent_at=($experience/100)*$atak;
			$procent_ob=($experience/100)*$obrona;
		}
        if($hardGame == 1 )//jeśli trudność świata to KADET, nie ma doświadczenia i morale
        {
            $procent_at = 0;
            $morale     = 100;
			$procent_ob = 0;
        }

		$atak1=$atak+$procent_at;//obniżam punkty obrony proporcjonalnie do doświadczenia obroncy
		$atak2=($morale/100)*$atak1;//obbniżam punkty obrony proporcjonalnie do morale jednostki
		$data['atak']=($defensePoints/100)*$atak2;//analizuję punkty DefensePoints jendostki
		
		$obrona1=$obrona+$procent_ob;//obniżam punkty obrony proporcjonalnie do doświadczenia obroncy
		$obrona2=($morale/100)*$obrona1;//obbniżam punkty obrony proporcjonalnie do morale jednostki
		$data['obrona']=($defensePoints/100)*$obrona2;//analizuję punkty DefensePoints jendostki
		return $data;
	}
	
	private function nadpisz_jednostke($pancerz,$morale,$doswiadczenie,$id_jednostki,$bonus_sztabu,$sztab,$rodzaj_jednostki,$id_gracza,$nacja_gracza,$specjalnosc)
	{
		$this -> db -> query('UPDATE `units` SET  `DefensePoints`='.$pancerz.', `Morale`='.$morale.', `unitExperience`=(`unitExperience`+'.$doswiadczenie.') WHERE id='.$id_jednostki.' ');
		switch($rodzaj_jednostki)
		{
			case '1':
				$do_statystyk='d_piechota';
				$HQrodzaj='infantry';
			break;
			case '2':
				$do_statystyk='d_pancerne';
				$HQrodzaj='tanks';
			break;
			case '3':
				$do_statystyk='d_przeciwlotnicze';
				$HQrodzaj='antiAircraft';
			break;
			case '4':
				$do_statystyk='d_lotnictwo_b';
				$HQrodzaj='aircraft';
			break;
			case '5':
				$do_statystyk='d_flota_n';
				if( $specjalnosc == 18 )
				{
					$HQrodzaj='underWater';
				}
				else
				{
					$HQrodzaj='waterArtillery';
				}
			break;
			case '6':
				$do_statystyk='d_artyleria';
				$HQrodzaj='artillery';
			break;
            case '7':
                $do_statystyk='d_cities';
                $HQrodzaj='cities';
                break;

		}
		if( $bonus_sztabu == 1 )
		{
			$new_doswiadczenie = round($doswiadczenie/10,3);
			$this -> db -> exec('UPDATE `HQData` SET  `'.$HQrodzaj.'`=(`'.$HQrodzaj.'`+'.$new_doswiadczenie.') WHERE `unitsID`='.$sztab.' LIMIT 1 ');
		}
	}
	
	private function oblicz_statystyki_tab_walka ($numer_jednostki_obrony, $num, $idGracza_ob, $idGracza_at, $straty_obroncy, $nazwa_at, $nazwa_ob, $user_at,$nacja_gracza, $accountID)
	{
		if($straty_obroncy>0){
			$rowspunkty = $this -> db -> query('SELECT COUNT(*) FROM  `walka` WHERE idDaneJednostek_ob='.$numer_jednostki_obrony.' AND idDaneJednostek_at='.$num.' ')->fetch();//sprawdzam, czy atak tej jednostki juz  był
				if( (int)$rowspunkty[0] == 0 ){
					$this -> db -> query('INSERT INTO `walka` (idDaneJednostek_ob, idDaneJednostek_at, idGracza_ob, idGracza_at, ilosc_zniszczen, nazwa_at, nazwa_ob, user_at) VALUES ('.$numer_jednostki_obrony.','.$num.','.$idGracza_ob.', '.$idGracza_at.', '.$straty_obroncy.',"'.$nazwa_at.'","'.$nazwa_ob.'","'.$user_at.'" )');
				}else{
					$this -> db -> exec('UPDATE `walka` SET ilosc_zniszczen=(`ilosc_zniszczen`+'.$straty_obroncy.') WHERE idDaneJednostek_ob='.$numer_jednostki_obrony.' AND idDaneJednostek_at='.$num.' ');
				}
		}
		$nowe_punkty=round( ($straty_obroncy/100),2);
        $this -> db -> exec('UPDATE `players` SET `points`=(`points`+'.$nowe_punkty.') WHERE `id`='.$idGracza_at.' ');
        if($nacja_gracza==1){
			$this -> db -> exec('UPDATE `accounts` SET `experience_pl`=(`experience_pl`+'.$nowe_punkty.') WHERE `id`='.$accountID.' ');
		}else if($nacja_gracza==2){
			$this -> db -> exec('UPDATE `accounts` SET `axperience_niem`=(`axperience_niem`+'.$nowe_punkty.') WHERE `id`='.$accountID.' ');
		}
	}
	
	private function dodaj_tab_alert($idDaneJednostek_ob, $idDaneJednostek_at, $idGracza_ob, $nazwa_ob, $nazwa_at, $user_at, $straty_obroncy, $datateraz)
	{
		$this -> db -> query('INSERT INTO `alert` (idDaneJednostek_ob, idDaneJednostek_at, idGracza_ob, nazwa_ob, nazwa_at, user_at, ilosc_zniszczen, data ) VALUES ('.$idDaneJednostek_ob.','.$idDaneJednostek_at.', '.$idGracza_ob.', "'.$nazwa_ob.'", "'.$nazwa_at.'", "'.$user_at.'", '.$straty_obroncy.', "'.$datateraz.'")');
	}
	
	private function usun_jednostke( $numer_jednostki, $niszczacyID, $niszczacyIdJednostki )
	{
		$wyciagnij_dane=$this -> db -> query('select `belongHQ`, `Specialty`, `unitType`, `playerID` from `units` WHERE `id`='.$numer_jednostki.' ')->fetch();
		if( $wyciagnij_dane['belongHQ'] == $numer_jednostki )
		{
			$this -> db -> exec('DELETE FROM `HQData` WHERE `unitsID`='.$numer_jednostki.' ');
			$this -> db -> exec('UPDATE `units` set `belongHQ`=0 WHERE `belongHQ`='.$numer_jednostki.' ');//nadpisuję jednostki przyłącozne dos ztabu
		}
		
		if( $wyciagnij_dane['Specialty'] == 19 )// jednostka zniszczona to barka desantowa, usuwamy wszystkie jednostki które były na barce
		{
			$getLoadingUnits = $this -> db -> query('select `id`, `belongHQ` from `units` WHERE `idLC`='.$numer_jednostki.' ');// wyciagamy jednostki które były na barce
			foreach ( $getLoadingUnits as $rowSingleUnits )
			{
				if( $rowSingleUnits['belongHQ'] == $rowSingleUnits['id'] )
				{
					$this -> db -> exec('DELETE FROM `HQData` WHERE `unitsID`='.$rowSingleUnits['id'].' ');
					$this -> db -> exec('UPDATE `units` set `belongHQ`=0 WHERE `belongHQ`='.$rowSingleUnits['id'].' ');//nadpisuję jednostki przyłącozne dos ztabu
				}
				$this -> db -> exec('DELETE FROM `units` WHERE `id`='.$rowSingleUnits['id'].' ');
				$this -> db -> exec('DELETE FROM `HQwaiting` WHERE `HQID`='.$rowSingleUnits['id'].' ');
				$this -> db -> exec('DELETE FROM `walka` WHERE `idDaneJednostek_ob`='.$rowSingleUnits['id'].' ');
				$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `unitsID`='.$rowSingleUnits['id'].' ');
				$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `connectUnit`='.$rowSingleUnits['id'].' ');
				$this -> db -> exec('DELETE FROM `alert` WHERE `idDaneJednostek_ob`='.$rowSingleUnits['id'].' ');
				$this -> db -> exec('DELETE FROM `GoldUnits` WHERE `unitID`='.$rowSingleUnits['id'].' ');
			}
		}
		
		if( $wyciagnij_dane['unitType'] == 7 AND $stolica == 0 )// jednostka zniszczona to miasto, usuwamy wszystkie jednostki które nie były wystawione
		{
			
			$getLoadingUnitsCity = $this -> db -> query('select `id`, `belongHQ` from `units` WHERE `playerID`='.$wyciagnij_dane['playerID'].' AND `onMap` = 0 ');// wyciagamy jednostki które były w mieście
			
			foreach ( $getLoadingUnitsCity as $rowSingleUnitsCity )
			{
				if( $rowSingleUnitsCity['belongHQ'] == $rowSingleUnitsCity['id'] )
				{
					$this -> db -> exec('DELETE FROM `HQData` WHERE `unitsID`='.$rowSingleUnitsCity['id'].' ');
					$this -> db -> exec('UPDATE `units` set `belongHQ`=0 WHERE `belongHQ`='.$rowSingleUnitsCity['id'].' ');//nadpisuję jednostki przyłącozne dos ztabu
				}
				$this -> db -> exec('DELETE FROM `units` WHERE `id`='.$rowSingleUnitsCity['id'].' ');
				$this -> db -> exec('DELETE FROM `HQwaiting` WHERE `HQID`='.$rowSingleUnitsCity['id'].' ');
				$this -> db -> exec('DELETE FROM `walka` WHERE `idDaneJednostek_ob`='.$rowSingleUnitsCity['id'].' ');
				$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `unitsID`='.$rowSingleUnitsCity['id'].' ');
				$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `connectUnit`='.$rowSingleUnitsCity['id'].' ');
				$this -> db -> exec('DELETE FROM `alert` WHERE `idDaneJednostek_ob`='.$rowSingleUnitsCity['id'].' ');
				$this -> db -> exec('DELETE FROM `GoldUnits` WHERE `unitID`='.$rowSingleUnitsCity['id'].' ');
			}
		}
		
		
		
		
		
		
		$this -> db -> exec('DELETE FROM `units` WHERE `id`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `HQwaiting` WHERE `HQID`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `walka` WHERE `idDaneJednostek_ob`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `unitsID`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `constituentUnits` WHERE `connectUnit`='.$numer_jednostki.' ');
		$this -> db -> exec('DELETE FROM `alert` WHERE `idDaneJednostek_ob`='.$numer_jednostki.' ');
        $this -> db -> exec('DELETE FROM `GoldUnits` WHERE `unitID`='.$numer_jednostki.' ');
		
		$wyciagnijDaneAtakujacego=$this -> db -> query('select `nation` from `players` WHERE `id`='.$niszczacyID.' ')->fetch();
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
	
	private function nadpisz_tab_stat_jednostki($numer_jednostki,$rok,$miesiac,$user_id,$rozgrywka,$rozgrywka_swiatowa,$serwer)
	{
		/*
		$wyciagnij_dane=mysql_query("select sztab, nacjaJednostki, idJednostki,specjalnosc from danejednostek where idDaneJednostek='$numer_jednostki'");
	    $id_sztabu=mysql_fetch_array($wyciagnij_dane);
		$id_sztabu2=$id_sztabu['sztab'];
		switch($id_sztabu['idJednostki']){
			case '1':
				$do_statystyk='d_piechota';
			break;
			case '2':
				$do_statystyk='d_lotnictwo_b';
			break;
			case '3':
				$do_statystyk='d_pancerne';
			break;
			case '4':
				$do_statystyk='d_przeciwlotnicze';
			break;
			case '5':
				$do_statystyk='d_flota_n';
			break;
			case '6':
				$do_statystyk='d_artyleria';
			break;
			default:
		}
		switch($id_sztabu['specjalnosc']){
			case 1://okręt podwodny
			$do_statystyk='d_flota_p';
			break;
			case 10://mysliwiec
			$do_statystyk='d_lotnictwo_m';
			break;
			default:
		}
		if($id_sztabu['nacjaJednostki']==1){
			$nacja_do_bazy=2;
		}else if($id_sztabu['nacjaJednostki']==2){
			$nacja_do_bazy=1;
		}
		$suma_jednostek_=$baza_starter->query("SELECT * FROM statystyki_gracza WHERE rozgrywka='$rozgrywka' AND rozgrywka_swiatowa='$rozgrywka_swiatowa' AND serwer='$serwer' AND user_id='$user_id' AND rok='$rok' AND miesiac='$miesiac' AND nacja='$nacja_do_bazy' ");
		$suma_jednostek=$suma_jednostek_->num_rows;
		if($suma_jednostek>0){
			$baza_starter->query("UPDATE statystyki_gracza SET $do_statystyk=(`$do_statystyk`+1) WHERE rozgrywka='$rozgrywka'  AND rozgrywka_swiatowa='$rozgrywka_swiatowa' AND serwer='$serwer' AND user_id='$user_id' AND rok='$rok' AND miesiac='$miesiac' AND nacja='$nacja_do_bazy' ");	
		}else{
			$baza_starter->query("INSERT INTO statystyki_gracza ($do_statystyk, rozgrywka, rozgrywka_swiatowa,user_id, miesiac, rok,nacja,serwer,skad) values('1','$rozgrywka','$rozgrywka_swiatowa', '$user_id','$miesiac','$rok', '$nacja_do_bazy','$serwer','atak.php' ) ");
		}
		*/
	}
	
	private function nadpisz_tab_stat_jednostki_miasto($user_id, $rok, $miesiac, $rozgrywka)
    {
        /*
        $rowspunkty1_policz_at=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM  stat_zniszczone WHERE idJednostek='7' AND idGracza='$user_id' AND rok='$rok' AND miesiac='$miesiac' AND rozgrywka='$rozgrywka' ") );//sprawdzam, czy atak tej jednostki juz  był
            $zapytanie_stat_at=$rowspunkty1_policz_at[0];
                if($zapytanie_stat_at==0){
                    $staty_at = mysql_query("INSERT INTO stat_zniszczone ( rodzaj_korpusu, idGracza, rok, miesiac, sztuki, rozgrywka ) VALUES ('7','$user_id','$rok', '$miesiac', '1','$rozgrywka' )");
                }else{
                    $staty_at  = mysql_query("UPDATE stat_zniszczone SET sztuki=(`sztuki`+1) WHERE idJednostek='7' AND idGracza='$user_id' AND rok='$rok' AND  miesiac='$miesiac' and rozgrywka='$rozgrywka'");
                }
        //return "nadpisuję statystyki dla miasta usera o id=$user_id<br>";
        */
    }
	
	private function nadpisz_dane_miasto($pancerz, $koordfin)
	{
		//$resultwynik  = mysql_query("UPDATE miasta SET  poz_obrony='$pancerz' WHERE nrHexa='$koordfin'");//odejmuję punkty obrony z miasta atakowanego
		//return "nadpisuję pancerz miasta zkoordu $koordfin<br>";
	}	
	
	private function podziel_kase_gracza($user_grabiacy, $user_grabiony)
	{
        $wyciagnij_dane = $this -> db -> query('SELECT `softCurrency` FROM `players` WHERE `id`='.$user_grabiony.' ')->fetch();//ustalam kwotę, jaka gracz atakowany posiada
        if( $wyciagnij_dane['softCurrency'] > 2 )
        {
            $zysk_atakujacego = round( $wyciagnij_dane['softCurrency']/2,2 );
        }
        else
        {
            $zysk_atakujacego = 0;
        }
        $this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`+'.$zysk_atakujacego.') WHERE `id` = '.$user_grabiacy.' ');//dodaje kwotę 50% kwoty gracza do sumy kasy gracza atakującego
        $this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-'.$zysk_atakujacego.') WHERE `id` = '.$user_grabiony.' ');//odejmję kwotę 50% kwoty gracza od sumy kasy gracza broniącego się
    }

    private function oblicz_kase($numer_jednostki_zniszczonej, $cena_zniszczonej, $nazwa_zniszczonej, $nazwa_at )
    {
        $daneAtakowanego = $this -> db -> query('SELECT  `players`.`nation`,`units`.`playerID` FROM `players` LEFT JOIN `units` ON `units`.`playerID`=`players`.`id` WHERE `units`.`id`='. $numer_jednostki_zniszczonej .' ') -> fetch();

        $suma_zniszczen = $this -> db -> query('SELECT SUM(`ilosc_zniszczen`) AS `zniszczenia` FROM `walka` WHERE `idDaneJednostek_ob`='.$numer_jednostki_zniszczonej.' ')->fetch();
        $zniszczenia = $suma_zniszczen['zniszczenia'];
        $procent_zniszczen = $zniszczenia/100;
		
        //wyciągam zadane straty danej jendostce z podziałem na jednostki atakujące
        $wykonaj = $this -> db -> query('SELECT *, SUM(`ilosc_zniszczen`) as `ilosc_zniszczen` FROM `walka` WHERE `idDaneJednostek_ob`='.$numer_jednostki_zniszczonej.' AND `ilosc_zniszczen`>0 GROUP BY `idDaneJednostek_at` ');//dzielę kasę z jednostki atakujacej na 5 najlepszych
		
        $wyniki = '';
        $arrPlutons = array();
        $arrDataCount = $this -> db -> query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits`');
        foreach ($arrDataCount as $rowTDA)
        {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
        $arrDataCount -> closeCursor();
        foreach ( $wykonaj as $wiersz )
        {
			//wyciągam dane jednostki atakującej i gracza atakującego
            $daneAtakujacego = $this -> db -> query('SELECT `pl`.`nation`, `pl`.`rankLevel`, `acc`.`login` FROM `players` AS `pl`
            LEFT JOIN `accounts` AS `acc` ON `acc`.`id`=`pl`.`accountID`
            WHERE `pl`.`id`='. $wiersz['idGracza_at'] .' ') -> fetch();
            $ilosc_zniszczen      = $wiersz['ilosc_zniszczen'];
            $procenty_atakujacego =  $ilosc_zniszczen / $procent_zniszczen;
            $zarobek_atakujacego  = round( ( ( ( $cena_zniszczonej / 100 ) * $procenty_atakujacego ) /2 ), 2);
            $this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`+'.$zarobek_atakujacego.') WHERE `id` = '.$wiersz['idGracza_at'].' LIMIT 1 ');
            $wyniki .= $daneAtakujacego['login'].' '. raport1 .' <strong> '. $wiersz['nazwa_at'].' </strong>'. raport2 .'<strong> '.$ilosc_zniszczen.' </strong> '. raport3 .' <br>';
        }
        $raporttekst = raport_txt1 .'<strong>' . $nazwa_zniszczonej .'</strong> '. raport_txt2 .' <strong>'. $numer_jednostki_zniszczonej .'</strong> '. raport_txt3 .' <strong>'. $nazwa_at .'</strong>.<br>
           '. raport_txt4 .' '.$zniszczenia.' '. raport_txt5 .' <br>
           '. $wyniki .' <br>
           '. raport_txt6;
		
        if( $daneAtakowanego['nation'] == 1 )
        {
            $idCiasteczka = $this -> db -> query('SELECT `id` FROM `players` WHERE `accountID`=36 AND `worldID`='. $this -> world -> id .' ') -> fetch();
			$this -> db -> exec('INSERT INTO  `message` ( `dateOfDispatch`, `messageAuthorID`, `playerID`, `contentMessage`, `seeMessage` ) values ( '. $this -> init -> time .', '. $idCiasteczka['id'] .', '. $daneAtakowanego['playerID'].', "'.$raporttekst.'","no" ) ');
        }
        else
        {
            $idPaczka = $this -> db -> query('SELECT `id` FROM `players` WHERE `accountID`=37 AND `worldID`='. $this -> world -> id .' ') -> fetch();
   			$this -> db -> exec('INSERT INTO  `message` ( `dateOfDispatch`, `messageAuthorID`, `playerID`, `contentMessage`, `seeMessage` ) values ( '. $this -> init -> time .', '. $idPaczka['id'] .', '. $daneAtakowanego['playerID'].', "'.$raporttekst.'","no" ) ');
        }
    }
	
	public function getBattleRaport()
	{
		//funkcja pokazuje raport bojowy 
		$raportID = $this -> path -> post('num');
		$getBattleRaport = $this -> db -> query('SELECT * FROM `battleRaport` WHERE `id`='.$raportID.' LIMIT 1 ')->fetch();
		$dane_mess = unserialize($getBattleRaport['descRaport']);
		$battleArray['bitwaT']='
		<div class="boxRaport">
		<div id="tab_data">'.$dane_mess['dane_atakujacego']['data_bitwy'].'</div>
		<div id="tab_0_1" style="background:url(./app/templates/assets/images/'.$dane_mess['dane_atakujacego']['obrazek_at'].') no-repeat;"></div>
		<div id="tab_1_1">'.$dane_mess['dane_atakujacego']['user_at'].'</div>
		<div id="tab_2_1">'.$dane_mess['dane_atakujacego']['nazwa_at'].'</div>
		<div id="pancerz_przed1" class="dane_okno">'. pancerz_txt .'</div>
		<div id="pancerz_przed2" class="dane_okno">'. pancerz_txt .'</div>
		<div id="doswiadczenie_przed1" class="dane_okno">'. doswiadczenie_txt .'</div>
		<div id="doswiadczenie_przed2" class="dane_okno">'. doswiadczenie_txt .'</div>
		<div id="punktygracza_przed1" class="dane_okno">'. statystyki_punkty .'</div>
		<div id="punktygracza_przed2" class="dane_okno">'. statystyki_punkty .'</div>
		<div id="morale_przed1" class="dane_okno">'. morale_txt .'</div>
		<div id="morale_przed2" class="dane_okno">'. morale_txt .'</div>
		<div id="tab_3_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['pancerzprzedOplot_at'].'</div>
		<div id="tab_4_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['doswiadczenieprzed_at'].'</div>
		<div id="tab_5_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['punkty_gracza_przed_at'].'</div>
		<div id="tab_6_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['moraleprzed_at'].'</div>
		<div id="tab_7_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['pancerzpo_at'].'</div>
		<div id="tab_8_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['n_doswiadczenie_at'].'</div>
		<div id="tab_9_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['n_punkty_at'].'</div>
		<div id="tab_10_1" class="styl_tab">'.$dane_mess['dane_atakujacego']['n_Morale'].'</div>
		<div id="tab_0_2" class="minPanel" style="background:url(./app/templates/assets/images/'.$dane_mess['dane_obroncy']['obrazek_ob'].') no-repeat;" coord="'.$dane_mess['dane_obroncy']['x'].'_'.$dane_mess['dane_obroncy']['y'].'"></div>
		<div id="tab_1_2">'.$dane_mess['dane_obroncy']['user_ob'].'</div>
		<div id="tab_2_2">'.$dane_mess['dane_obroncy']['nazwa_ob'].'</div>
		<div id="tab_3_2" class="styl_tab">'.$dane_mess['dane_obroncy']['pancerzprzed_ob'].'</div>
		<div id="tab_4_2" class="styl_tab">'.$dane_mess['dane_obroncy']['doswiadczenieprzed_ob'].'</div>
		<div id="tab_5_2" class="styl_tab">'.$dane_mess['dane_obroncy']['punkty_gracza_przed_ob'].'</div>
		<div id="tab_6_2" class="styl_tab">'.$dane_mess['dane_obroncy']['moraleprzed_ob'].'</div>
		<div id="tab_7_2" class="styl_tab">'.$dane_mess['dane_obroncy']['pancerzpo_ob'].'</div>
		<div id="tab_8_2" class="styl_tab">'.$dane_mess['dane_obroncy']['n_doswiadczenie_ob'].'</div>
		<div id="tab_9_2" class="styl_tab">'.$dane_mess['dane_obroncy']['n_punkty_ob'].'</div>
		<div id="tab_10_2" class="styl_tab">'.$dane_mess['dane_obroncy']['n_Morale'].'</div>
		<div id="raport_bojowy">'.$dane_mess['dane_atakujacego']['data_bitwy'].' '.$dane_mess['dane_atakujacego']['raport_warunek'].'<br>'.$dane_mess['dane_atakujacego']['raport_oplot'].'<br>'.$dane_mess['dane_atakujacego']['mgla_at'].'<br>'.$dane_mess['dane_atakujacego']['pogoda_at'].'<br>'.$dane_mess['dane_atakujacego']['sztab_at'].'<br>operacja nr='.$dane_mess['dane_atakujacego']['numer_operacji'].'
		<br>
		'.$dane_mess['dane_atakujacego']['endOPLOTAT'].'<br>
		'.$dane_mess['dane_obroncy']['endOPLOTOB'].'<br>
		
		
		
		</div>
					
		';
		if( $this -> account -> sounds == 1 ){
			if( $dane_mess['dane_atakujacego']['korpus_at'] == 4 AND $dane_mess['dane_atakujacego']['korpus_ob'] != 4 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/airbomb.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/airbomb.mp3"/></audio</div>';
			}else if( $dane_mess['dane_atakujacego']['korpus_at'] == 6 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/artyleria.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/artyleria.mp3"/></audio></div>';
			}else if( $dane_mess['dane_atakujacego']['korpus_at'] == 4 AND $dane_mess['dane_atakujacego']['korpus_ob'] == 4 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/airbattle.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/airbattle.mp3"/></audio></div>';
			}else if( $dane_mess['dane_atakujacego']['korpus_at'] == 1 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/mg.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/mg.mp3"/></audio></div>';
			}else if( $dane_mess['dane_atakujacego']['korpus_at'] == 2 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/atak_czolg.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/atak_czolg.mp3"/></audio></div>';
			}else if( $dane_mess['dane_atakujacego']['korpus_at'] == 5 AND $dane_mess['dane_atakujacego']['zanurzenie_at'] != 1 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/sea_gun.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/sea_gun.mp3"/></audio></div>';
			}else if( $dane_mess['dane_atakujacego']['korpus_at'] == 5 AND $dane_mess['dane_atakujacego']['zanurzenie_at'] == 1 ){
				$battleArray['bitwaT'].='<div id="bitwa"><audio autoplay><source src="./app/templates/assets/sounds/torpeda.ogg" type="audio/ogg"/><source src="./app/templates/assets/sounds/torpeda.mp3"/></audio></div>';
			}
		}
		$battleArray['temat']= bitwa .' '.$getBattleRaport['topic'];
		$battleArray['seen']=$getBattleRaport['seen'];
		$this -> db -> exec('UPDATE `battleRaport` SET `seen`=1 WHERE `id`='.$raportID.' LIMIT 1 ');
		echo json_encode($battleArray);
	}
	
	public function deleteRaport()
	{
		$battleArray=array();
		//funkcja kasuje raport bojowy 
		$raportID = $this -> path -> post('num');
		$getBattleRaport = $this -> db -> query('SELECT * FROM `battleRaport` WHERE `id`='.$raportID.' LIMIT 1 ')->fetch();
		if( $getBattleRaport['playerID'] == $this -> account -> playerID )
		{
			$this -> db -> exec('DELETE  FROM `battleRaport` WHERE `id`='.$raportID.' LIMIT 1 ');
		}
		else
		{
			$battleArray['error'] = comp_battleE73;
		}
		echo json_encode($battleArray);
	}
	
	public function goToDestroyBridge($defenseFieldCustom, $attackID, $licznik1, $licznik2, $up1, $up2 ){
		$objHelper = init::getFactory() -> getService('helper');
		$battleArray = [];
		$aAircraft = false;
		// sprawdzamy, czy most jest pod ochroną 
		$allyUnits = $this -> db -> query('SELECT `u`.`id`, `u`.`AntiAircraft`,`u`.`DefensePoints`,`u`.`Morale`,`u`.`unitExperience`,`u`.`belongHQ`,`u`.`playerID`,`u`.`x`, `u`.`y`, `t`.`widocznosc`, `t`.`ostrzal`, `t`.`obrazek_duzy`,`t`.`nazwa'.$this -> account -> lang.'`,`t`.`atak_lotnictwo` FROM `units` AS `u` LEFT JOIN `TacticalData` AS `t` ON `t`.`id`=`u`.`tacticalDataID` WHERE `u`.`x`<=' . ($attackID['x'] + 5) . ' AND `u`.`y`>=' . ($attackID['y'] - 5) . ' AND `u`.`x`>='.($attackID['x'] - 5).' AND `u`.`y`<='.($attackID['y'] + 5).' AND `u`.`AntiAircraft`=1 AND ( ( `u`.`FieldArtillery`=1 AND `u`.`Specialty`=17  ) OR `u`.`FieldArtillery`=0  ) AND `u`.`worldID`='.$this -> world -> id.' AND `u`.`x`!=0 AND `u`.`y`!=0 AND `t`.`NationId` IN '.($this -> account -> nation == 1 ? '(7, 8, 9)' : '(1, 2, 3, 4, 5)').' ');
		$allyUnitsCts = $this -> db -> query('SELECT count(*) FROM `units` AS `u` LEFT JOIN `TacticalData` AS `t` ON `t`.`id`=`u`.`tacticalDataID` WHERE `u`.`x`<=' . ($attackID['x'] + 5) . ' AND `u`.`y`>=' . ($attackID['y'] - 5) . ' AND `u`.`x`>='.($attackID['x'] - 5).' AND `u`.`y`<='.($attackID['y'] + 5).' AND `u`.`AntiAircraft`=1 AND ( ( `u`.`FieldArtillery`=1 AND `u`.`Specialty`=17  ) OR `u`.`FieldArtillery`=0  ) AND `u`.`worldID`='.$this -> world -> id.' AND `u`.`x`!=0 AND `u`.`y`!=0 AND `t`.`NationId` IN '.($this -> account -> nation == 1 ? '(7, 8, 9)' : '(1, 2, 3, 4, 5)').' ')->fetch();
		$revUnit = false;
		$defensePoints = $attackID['DefensePoints'];
		
		
		if( $attackID['unitType'] == 4 AND (int)$allyUnitsCts[0] > 0 )
		{
			
			
			
			$destroyForOplot = 0;
			$attackTacticData = $this -> db -> query('select sum(`td`.`atak_pancerne`) as `atak_at`, `td`.`obrazek_duzy` as `obrazek_at`, `td`.`nazwa` as `nazwa_at`, sum(`td`.`obrona_przeciwlotnicze`) as `obrona_przeciwlotnicze` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$attackID['id'].' ')->fetch();
			$atakArray=$this->calcParameters($attackID['unitExperience'],$attackID['Morale'],$attackID['DefensePoints'], $attackTacticData['atak_at'], 0, $this -> world -> hardGame );
			$atak_at=$atakArray['atak'];
			
			$atakArray2=$this->calcParameters($attackID['unitExperience'],$attackID['Morale'],$attackID['DefensePoints'], $attackTacticData['obrona_przeciwlotnicze'], 0, $this -> world -> hardGame );
			$obAA    = $atakArray2['atak'];
			$rev = false;
			//jednostka atakujaca jest samolotem
			$i=0;
			foreach ($allyUnits as $enemyAntiAircraftUnits)
			{
					$aaTacticData = $this -> db -> query('select min(`td`.`ostrzal`) as `ostrzal`, sum(`td`.`atak_lotnictwo`) as `atak_lotnictwo`, `td`.`nazwa` as `nazwa_aa` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$enemyAntiAircraftUnits['id'].' ')->fetch();
					//wyciągam dane połączonych oplotów
					$odleglosc = $this -> unit -> hex_distance(array('x' => $attackID['x'], 'y' => $attackID['y']),array('x' => $enemyAntiAircraftUnits['x'], 'y' => $enemyAntiAircraftUnits['y']) );
				if( $aaTacticData['ostrzal'] >= $odleglosc AND  $odleglosc <= 5 ){
					//ustalam, czy jednostka przeciwlotnicza jest w sztabie
					$bonus_obAA=0;
					$belongHQ['antiAircraft']=0;
					if($enemyAntiAircraftUnits['belongHQ']!=0){
						//jednostka jest przydzielona do sztabu
						//pobieram koordynaty  sztabu i sprawdzam, czy on widzi ww jednostkę
						/*
						$belongHQ = $this -> db -> query('SELECT `u`.`x`,`u`.`y`, `u`.`id`,`bhq`.`antiAircraft`,`bhq`.`range` FROM `units` AS `u` LEFT JOIN `HQData` AS `bhq` ON `u`.`belongHQ`=`bhq`.`id` WHERE `bhq`.`id`='.$enemyAntiAircraftUnits['belongHQ'].' ')->fetch();
							$odlegloscHQ = count($objHelper -> bresenham($enemyAntiAircraftUnits['x'], $enemyAntiAircraftUnits['y'], $belongHQ['x'], $belongHQ['y'], false) );
							echo "odległosć do sztabu=".$odlegloscHQ.", range=".( $belongHQ['range'] * 10 ) ."aa=".$belongHQ['antiAircraft']."";
							if( $belongHQ['range'] * 10 >= $odlegloscHQ){
								$bonus_ob=1;
								echo "bonus sztabu aktywny";
							}
							*/ 
							$bonus_obAA=1;
							$belongHQ['antiAircraft']=20;
 					}
					//obliczam realny atak jednostki przeciwlotniczej
					if($enemyAntiAircraftUnits['unitExperience']<=0)
					{
						$procent_ob_obAA=0; 
					}else{
						$procent_ob_obAA=($enemyAntiAircraftUnits['unitExperience']/100)*$aaTacticData['atak_lotnictwo'];
					}
					if($bonus_obAA==1)
					{
						$bonus_ob_obAA=($belongHQ['antiAircraft']/100)*$aaTacticData['atak_lotnictwo'];//obniżam punkty obrony proporcjonalnie do pancerza
					}else{
						$bonus_ob_obAA=0;
					}
					
					$obrona_obAA1=$aaTacticData['atak_lotnictwo']+$procent_ob_obAA+$bonus_ob_obAA;//obniżam punkty obrony proporcjonalnie do doświadczenia obroncy
					$obrona_obAA2=($enemyAntiAircraftUnits['Morale']/100)*$obrona_obAA1;//obbniżam punkty obrony proporcjonalnie do morale jednostki
					$obrona_ob_ostAA=($enemyAntiAircraftUnits['DefensePoints']/100)*$obrona_obAA2;

						//ustalam nazwę połączonej jednostki przeciwlotniczej
						$unitsAA = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$enemyAntiAircraftUnits['id'].' ')->fetch();
						$daneAntiArcraft[] = array(
							'nazwaJednostki'  => $this -> unit -> ustalNazwe( (int)$unitsAA[0], 3, 0, $aaTacticData['nazwa_aa'] ),
							'idGracza'        => $enemyAntiAircraftUnits['playerID'],
							'unitID'          => $enemyAntiAircraftUnits['id'],
							'round'           => $odleglosc,
							'moc'             => $obrona_ob_ostAA
						);	
				}
				$i++;
			
			}
				
				// przypadek gdy most jest chroniony przez obronę przeciwlotniczą
				$moc = 0;
				$nazwaJednostki = '';
				$idDaneJednostek_oplot=0;
				$active_oplot = 0;
				$ktostrzelaAA ='';
				foreach($daneAntiArcraft as $tab)
				{
					if($tab['moc'] > $moc ){
						$moc                   = $tab['moc'];
						$idGracza_oplot        = $tab['idGracza'];
						$idDaneJednostek_oplot = $tab['unitID'];
						$nazwaJednostki        = $tab['nazwaJednostki'];
						$round                 = $tab['round'];
						$ktostrzelaAA .= fabryki_jednostka .' '.$tab['nazwaJednostki'].', '. comp_battleE74 .'='.$tab['round'].', '. comp_battleE75 .'='.$tab['moc'];
					}
				}		
				
				
				if( $moc > 0 ){
					$aAircraft = true;
					
					$atakujacy=array(
						'opancerzenie' => $attackID['DefensePoints'],
						'atak'         => (int)$atak_at,
						'obrona'       => (int)$obAA
					);
					
					
					
					$atakujacy = $this -> cios($moc,$atakujacy);
					$pancerz_at = $atakujacy['opancerzenie'];
					$straty_atOPLOT = $atakujacy['dmg'];	
					
					$raport_oplot = comp_battleE23 .'<br>'. autooplot1 .' '. $nazwaJednostki .' '. sztaby_gracza .' '.$idGracza_oplot.' '. mess_gracz4 .' '.$round.' z mocą '.$moc.', '. autooplot2 .' '.$straty_atOPLOT.'<br><br>';
					//wykonujemy przeliczenie po starciu między samolotem atakującym a automatycznym strzałem przeciwlotniczym
					
					/*
					!!!!!!!!!!!!!!!!!!!!!!!!dodać bitwę między jednostka przeciwlotniczą a atakującym samolotem !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					*/
					
					$active_oplot = 1;
					if( $pancerz_at <= 0 ){
						//po strzale oplot  jendostka zostaje zniszczona.
						$raport_oplot.= comp_battleE24;
						$destroyForOplot = 1;
						$revUnit = true;
						// usuwamy jednsotkę z mapy
					}
					// przygotowuję dane wyjściowe do aktualizacji jednostki na mapie
					// nadpisujemy dane mostu
					
					
					if( $defenseFieldCustom['points'] > $atak_at ){
						$this -> db -> query('UPDATE `mapData` SET  `points`= (`points` - '. $atak_at .') WHERE `fieldID`='.$defenseFieldCustom['fieldID'].' ');
						$battleArray['error'] = comp_battleE80 .' '.$atak_at.' '. comp_battleE78 .'.<br> '.$raport_oplot;
					}else{
						// usuwamy most z mapy
						$battleArray['error'] = comp_battleE79;//'Most zniszczony';
						$this -> db -> query('UPDATE `mapData` SET  `points`=0, `fieldCustom`=0, `fieldPlayerID`=0, `fieldNation`=0 WHERE `fieldID`='.$defenseFieldCustom['fieldID'].' ');
						$rev = true;
					}
					$defensePoints = $attackID['DefensePoints']-$straty_atOPLOT;
					$this -> db -> query('UPDATE `units` SET  `DefensePoints`= ( `DefensePoints` - '.$straty_atOPLOT.' ) WHERE id='.$attackID['id'].' ');
				}else{
					// brak obrony plot
					$raport_oplot = autooplot3 .'<br>';
					if( $defenseFieldCustom['points'] > $atak_at ){
						$this -> db -> query('UPDATE `mapData` SET  `points`= (`points` - '. $atak_at .') WHERE `fieldID`='.$defenseFieldCustom['fieldID'].' ');
						$battleArray['error'] = comp_battleE76 .', '. mess_gracz6 .' '.( $defenseFieldCustom['points'] - $atak_at  ).' '. comp_battleE78;
					}else{
						// usuwamy most z mapy
						$battleArray['error'] = comp_battleE79;//'Most zniszczony';
						$this -> db -> query('UPDATE `mapData` SET  `points`=0, `fieldCustom`=0, `fieldPlayerID`=0, `fieldNation`=0 WHERE `fieldID`='.$defenseFieldCustom['fieldID'].' ');
						$rev = true;
					}
				}
		}else{
			// strzela artyleria
			$rev = false;
			$attackTacticData = $this -> db -> query('select sum(`td`.`atak_pancerne`) as `atak_at`, `td`.`obrazek_duzy` as `obrazek_at`, `td`.`nazwa` as `nazwa_at` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$attackID['id'].' ')->fetch();
			$atak_at = $attackTacticData['atak_at'];
			
			$atakArray=$this->calcParameters($attackID['unitExperience'],$attackID['Morale'],$attackID['DefensePoints'], $attackTacticData['atak_at'], 0, $this -> world -> hardGame );
			$newAtAT=$atakArray['atak'];
			
			if( $defenseFieldCustom['points'] > $newAtAT ){
				$this -> db -> query('UPDATE `mapData` SET  `points`= (`points` - '. $newAtAT .') WHERE `fieldID`='.$defenseFieldCustom['fieldID'].' ');
				
				if( $attackID['unitType'] == 4 ){
					$battleArray['error'] = comp_battleE76 .', '. mess_gracz6 .' '.( $defenseFieldCustom['points'] - $newAtAT  ).' '. comp_battleE78;
				}else{
					$battleArray['error'] = comp_battleE77 .', '. mess_gracz6 .' '.( $defenseFieldCustom['points'] - $newAtAT  ).' '. comp_battleE78;
				}
				
			}else{
				// usuwamy most z mapy
				$battleArray['error'] = comp_battleE79;//'Most zniszczony';
				$this -> db -> query('UPDATE `mapData` SET  `points`=0, `fieldCustom`=0, `fieldPlayerID`=0, `fieldNation`=0 WHERE `fieldID`='.$defenseFieldCustom['fieldID'].' ');
				$rev = true;
			}
		}
		
		$attackID['timing1'] = $licznik1;
		$attackID['timing2'] = $licznik2;
		$attackID['DefensePoints'] = $defensePoints;
		
		if( $this->world->timing == 1 ){
			$unitTurn = false;
		}else{
			$unitTurn = $attackID['unitTurn']-1;	
		}	
				
		$arrayData = array(
			'activity' => 'shotOnBridge',
			'chanData' => array(
				// Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
				array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
					'data' => array(
						'removeBridge' => $rev,
						'removeUnit' => $revUnit,
						'aAircraft' => $aAircraft,
						'x' => $defenseFieldCustom['x'],
						'y' => $defenseFieldCustom['y'],
						'fieldType' => $defenseFieldCustom['fieldType'],
						'fieldTxt'  => $this->unit->okresl_podloze($defenseFieldCustom['fieldType']),
						'unitsID'   => $attackID,
						'timing1'   => $licznik1,
						'timing2'   => $licznik2,
						'unitTurn'  => $unitTurn,
						'defensePoints'  => ( $defensePoints/10 ),
						'up1' => $up1,
						'up2' => $up2
						
					),
				),
			)
		);
		$arrayData['chanData'][] = array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
			'data' => array(
				'removeBridge' => $rev,
				'x' => $defenseFieldCustom['x'],
				'y' => $defenseFieldCustom['y'],
				'fieldType' => $defenseFieldCustom['fieldType'],
				'fieldTxt' => $this->unit->okresl_podloze($defenseFieldCustom['fieldType'])
			)
		);
		$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');	
		echo json_encode($battleArray);
		exit();
	}
	
	public function goToDestroySilo($defenseID, $attackID, $licznik1, $licznik2, $up1, $up2 ){
		$objHelper = init::getFactory() -> getService('helper');
		$battleArray = [];	
		$attackTacticData = $this -> db -> query('select sum(`td`.`atak_pancerne`) as `atak_at`, `td`.`nazwa` as `nazwa_at` FROM `TacticalData` as `td` LEFT JOIN `constituentUnits` as `cu` ON `cu`.`tacticalDataID` = `td`.`id` WHERE `cu`.`connectUnit` ='.$attackID['id'].' ')->fetch();
		$atak_at = $attackTacticData['atak_at'];
		
		$atakArray=$this->calcParameters($attackID['unitExperience'],$attackID['Morale'],$attackID['DefensePoints'], $attackTacticData['atak_at'], 0, $this -> world -> hardGame );
		$newAtAT=$atakArray['atak'];
		
		if( $defenseID['rawMaterials'] > $newAtAT ){
			$this -> db -> query('UPDATE `siloData` SET  `rawMaterials`= (`rawMaterials` - '. $newAtAT .') WHERE `unitID`='.$defenseID['unitID'].' ');
		}else if( $defenseID['rawMaterials'] <= $newAtAT ){
			// zerujemy surowce
			$this -> db -> query('UPDATE `siloData` SET  `rawMaterials`= 0 WHERE `unitID`='.$defenseID['unitID'].' ');
		}
		
		$attackID['timing1'] = $licznik1;
		$attackID['timing2'] = $licznik2;
		
		if( $this->world->timing == 1 ){
			$unitTurn = false;
		}else{
			$unitTurn = $attackID['unitTurn']-1;	
		}	
				
		$arrayData = array(
			'activity' => 'shotOnSilo',
			'chanData' => array(
				// Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
				array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
					'data' => array(
						'unitsID'   => $attackID,
						'timing1'   => $licznik1,
						'timing2'   => $licznik2,
						'unitTurn'  => $unitTurn,
						'up1' => $up1,
						'up2' => $up2,
						'info' => ' Atak na SILOS udany!'
					),
				),
			)
		);
		$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');	
		echo json_encode($battleArray);
		exit();
	}
	

}
?>