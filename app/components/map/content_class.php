<?php

/*
 * KOMPONENT: mapa
 */

class content extends EveryComponent
{



    public function __construct()
    {
        $this -> unit = init::getFactory() -> getService('unit');
    }

    /*
     * Metoda wysyła dane mapy do przeglądarki
     */

    public function showData()
    {
        // Pobieram plutony
        $arrPlutons = [];
		$objHelper = init::getFactory()->getService('helper');
        $arrDataCount = $this -> db -> query('SELECT `tacticalDataID`, `unitsID`,`connectUnit` FROM `constituentUnits`');
        foreach ($arrDataCount as $rowTDA)
        {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
        $arrDataCount -> closeCursor();

        $arrReturn = [];
        $arrViewedArea = []; // Tablica z widzialnymi przez gracza polami
        $arrFieldsWithUnits = []; // Jednostki, miasta widoczne dla gracza
        $arrEnemyUnits = []; // Jednostki, miasta wroga
        $boolHasCity = false;
		$arrTacticalData = [];

		$arrData = $this -> db -> query('SELECT `m`.*, `acc`.`login` AS `nickName`, `t`.`widocznosc`, `u`.`id` AS `uid`, `u`.`unitType`,`u`.`timing1`,`u`.`timing2`, `u`.`tacticalDataID`, `u`.`playerID`, `u`.`Specialty`, `u`.`unitTurn`,`u`.`DefensePoints`, `u`.`Torpedo`,`u`.`FieldArtillery`,`u`.`Morale`,`u`.`unitExperience`,`u`.`UnderWater`,`t`.`NationId`,`u`.`belongHQ`, `u`.`CityName`, `pl`.`nation`,`t`.`nazwa`, `pl`.`playerVacation`, `gu`.`unitID` as `goldUnit` FROM `mapData` AS `m` '
            . 'LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y` AND `u`.`worldID`='. $this -> world -> id .' AND `u`.`onMap`= 1 ) '
            . 'LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id` AND `pl`.`worldID`='. $this -> world -> id .') '
			. 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id` ) '
			. 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
            . 'LEFT JOIN `GoldUnits` AS `gu` ON (`gu`.`unitID`=`u`.`id`) '
            . 'WHERE `m`.`worldID`='. $this -> world -> id  .' '
			. "ORDER BY `m`.`fieldID` ASC");
        
		$endW = '';
		
			$idCiasteczka = $this -> db -> query('SELECT `id` FROM `players` WHERE `accountID`=36 AND `worldID`='. $this -> world -> id .' ') -> fetch();
			$idPaczka = $this -> db -> query('SELECT `id` FROM `players` WHERE `accountID`=37 AND `worldID`='. $this -> world -> id .' ') -> fetch();
			// pobieram top 10 najlepszych graczy, dodaje im punkty global
			$polishCapital = $this -> db -> query('SELECT count(*) FROM `units` WHERE `playerID` = '. $idCiasteczka['id'] .' AND `tacticalDataID` = 218 AND `unitType` = 7 ')->fetch();
			$germanCapital = $this -> db -> query('SELECT count(*) FROM `units` WHERE `playerID` = '. $idPaczka['id'] .' AND `tacticalDataID` = 219 AND `unitType` = 7 ')->fetch();
			$raport['pol'] = (int)$polishCapital[0];
			$raport['ger'] = (int)$germanCapital[0];
			
			if( (int)$polishCapital[0] > 0 AND (int)$germanCapital[0] == 0)
			{
				// jesli stolica wWarszawa isteniejee a Niemców nie istnieje, wygrali POLACY
				$endW .= '<div id="generalClass"> '.comp_engine9 .'<br>klasyfikacja GENERALNA<br>
				<table>
					<tr>
						<td> l. p.</td><td>gracz</td><td>punkty<br> w grze</td><td>nagroda: punkty GLOBAL</td>
					</tr>
				';
				//przeprowadzam wręczanie pubktów global, wiciągam 100 najlepszych graczy ze strony która wygrała 
					$arrData2 = $this -> db -> query('SELECT `pl`.`accountID`, `pl`.`id`, `pl`.`points`, `pl`.`nation`, `acc`.`login` FROM `players` AS `pl` '
					. 'LEFT JOIN `accounts` AS `acc` ON ( `acc`.`id`=`pl`.`accountID` ) '
					.' WHERE `pl`.`id`!=0 AND `pl`.`accountID`!= 36 AND `pl`.`accountID`!= 37 AND `pl`.`accountID`!= 1 AND `pl`.`points` > 30 AND `pl`.`worldID` = ' . $this -> world -> id . ' ORDER BY `pl`.`points` DESC LIMIT 30 ');
					$i = 1;
					foreach ($arrData2 as $row2) {
						$points = $objHelper->getPoints( $i );
						if( $row2['nation'] == 1 ){
							$points = '( '. $objHelper->getPoints( $i ) .' + 20 )';
						}
						$endW .= '<tr><td>'. $i .'</td><td>'.$row2['login'].'</td><td> '. round($row2['points']) .'</td><td>'. $points .'</td></tr>';
						
						//$this -> db -> exec('UPDATE `accounts` SET `globalPoints`=(`globalPoints`+' . $points . ') WHERE `id`=' . $row['accountID'] . ' LIMIT 1');
						$i++;
					}
					
					$endW .= '</div>';
			}else if( (int)$polishCapital[0] == 0 AND (int)$germanCapital[0] > 0 ){
				// jesli stolica wWarszawa nie istnieje a Niemców istnieje, wygrali Niemcy
				//przeprowadzam wręczanie pubktów global
				$endW .= '<div id="generalClass"> '.comp_engine10 .'<br>klasyfikacja GENERALNA<br>
				<table>
					<tr>
						<td> l. p.</td><td>gracz</td><td>zdobyte <br> punkty</td>
					</tr>';
				//przeprowadzam wręczanie pubktów global, wiciągam 100 najlepszych graczy ze strony która wygrała 
					$arrData2 = $this -> db -> query('SELECT `pl`.`accountID`, `pl`.`id`, `pl`.`points`, `pl`.`nation`, `acc`.`login` FROM `players` AS `pl` '
					. 'LEFT JOIN `accounts` AS `acc` ON ( `acc`.`id`=`pl`.`accountID` ) '
					.' WHERE `pl`.`id`!=0 AND `pl`.`accountID`!= 36 AND `pl`.`accountID`!= 37 AND `pl`.`accountID`!= 1 AND `pl`.`points` > 30 AND `pl`.`worldID` = ' . $this -> world -> id . ' ORDER BY `pl`.`points` DESC LIMIT 30 ');
					$i = 1;
					foreach ($arrData2 as $row2) {
						$points = $objHelper->getPoints( $i );
						if( $row2['nation'] == 2 ){
							$points = '( '. $objHelper->getPoints( $i ) .' + 20 )';
						}
						$endW .= '<tr><td>'. $i .'</td><td>'.$row2['login'].'</td><td> '. round($row2['points']) .'</td><td>'. $points .'</td></tr>';
						
						//$this -> db -> exec('UPDATE `accounts` SET `globalPoints`=(`globalPoints`+' . $points . ') WHERE `id`=' . $row['accountID'] . ' LIMIT 1');
						$i++;
					}
					$endW .= '</div>';
			}
			
			if( (int)$polishCapital[0] > 0 AND (int)$germanCapital[0] > 0 ){// wojna nadal trwa
				foreach ($arrData as $row)
				{
					if( $row['worldID'] == $this -> world -> id ) {
						if ($row['playerID'] == 0 AND $row['unitType'] == 7 AND $row['tacticalDataID'] == 218) {
							$row['nation'] = 1;
						}
						if ($row['playerID'] == 0 AND $row['unitType'] == 7 AND $row['tacticalDataID'] == 219) {
							$row['nation'] = 2;
						}

						$arrReturn[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'fieldType' => $row['fieldType'], 'fieldTxt' => $this->unit->okresl_podloze($row['fieldType']), 'fieldCustom' => $row['fieldCustom'], 'fieldNation' => $row['fieldNation'], 'fieldPlayerID' => $row['fieldPlayerID'], 'playerVacation' => $row['playerVacation']);
						// Jest jednostka 1:polak,2:angol,3:rus,4:amerykanin,5:francuz,6:niemiec,7:włoch,8:japoniec
						if (isset($row['uid'])){ //AND ( ( $row['x'] == 0  AND $row['y'] != 0 ) OR ( $row['x'] != 0  AND $row['y'] == 0 ) ) ) {
							$nazwa = $this->unit->ustalNazwe(count($arrPlutons[$row['uid']]), $row['unitType'], $row['Specialty'], $row['nazwa']);
							//echo  "ustalNazwe( ".count($arrPlutons[$row['uid']] ).", ".$row['unitType'].", ".$row['Specialty'].", ".$row['nazwa']." )";
							$belongHQ = '';
							$daneUnits = '';
							$cityStand = '';
							$vac = 0;
							if ($row['playerVacation'] > $this->init->time) {
								$vac = date("Y-m-d H:i:s", $row['playerVacation']);
							}
							if ($row['nation'] == $this->account->nation) {
								//sprawdzam, czy gracz jest w zasiegu swojego sztabu
								$belongHQ = $this->unit->checkStuff($row['uid']);
								//przygotowanie zaszyfrwanych danych do operacji jednostek

								$cityStand = 0;
								if ($row['Specialty'] == 1)//sprawdzam, czy można na dany heks postawić miasto
								{
									$cityStand = 1;
									if ($this->account->nation == 1) {
										if ($row['x'] < 97 || $row['fieldType'] == 1 || $row['fieldType'] == 8 || $row['fieldType'] == 9) $cityStand = 0;
									} elseif ($this->account->nation == 2) {
										if ($row['x'] > 35 || $row['fieldType'] == 1 || $row['fieldType'] == 8 || $row['fieldType'] == 9) $cityStand = 0;
									}
								}

								if ( $row['unitType'] == 7 ) {
									$arrFieldsWithUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'view' => 6, 'nation' => $row['nation'], 'unitType' => 7, 'owner' => $row['playerID'], 'CityName' => $row['CityName'], 'nickName' => $row['nickName'], 'DefensePoints' => $row['DefensePoints'], 'un' => '', 'playerVacation' => $vac, 'id' => $row['uid'], 'timing1' => $row['timing1'], 'timing2' => $row['timing2']);
									$arrViewedArea = $this->unit->checkUnitsVision($row['x'], $row['y'], 6, $arrViewedArea);
									if ($row['playerID'] == $this->account->playerID) {
										$boolHasCity = true;
									}
								} else {
									if ($row['Specialty'] != 18 OR $row['playerID'] == $this->account->playerID) {
										$arrFieldsWithUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'id' => $row['uid'], 'view' => $row['widocznosc'], 'nation' => $row['nation'], 'unitType' => $row['unitType'], 'uid' => $row['tacticalDataID'], 'owner' => $row['playerID'], 'td' => (isset($arrPlutons[$row['uid']]) ? $arrPlutons[$row['uid']] : array()), 'Specialty' => $row['Specialty'], 'unitTurn' => $row['unitTurn'], 'DefensePoints' => $row['DefensePoints'], 'FieldArtillery' => $row['FieldArtillery'], 'Torpedo' => $row['Torpedo'], 'Morale' => $row['Morale'], 'experience' => $row['unitExperience'], 'UnderWater' => $row['UnderWater'], 'nickName' => $row['nickName'], 'belongHQ' => $belongHQ, 'cityStand' => $cityStand, 'nazwa' => $nazwa, 'un' => '', 'goldUnit' => $row['goldUnit'], 'timing1' => $row['timing1'], 'timing2' => $row['timing2']);
										$arrViewedArea = $this->unit->checkUnitsVision($row['x'], $row['y'], $row['widocznosc'], $arrViewedArea);
									}
								}
							} else {
								if ($row['unitType'] == 7) {
									$arrEnemyUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'view' => 6, 'nation' => $row['nation'], 'unitType' => 7, 'owner' => $row['playerID'], 'CityName' => $row['CityName'], 'nickName' => $row['nickName'], 'DefensePoints' => $row['DefensePoints'], 'un' => '', 'playerVacation' => $vac, 'id' => $row['uid']);
								} else {
									if ($row['Specialty'] != 18) {
										$arrEnemyUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'id' => $row['uid'], 'nation' => $row['nation'], 'unitType' => $row['unitType'], 'uid' => $row['tacticalDataID'], 'owner' => $row['playerID'], 'td' => (isset($arrPlutons[$row['uid']]) ? $arrPlutons[$row['uid']] : array()), 'Specialty' => $row['Specialty'], 'DefensePoints' => $row['DefensePoints'], 'nickName' => $row['nickName'], 'nazwa' => $nazwa, 'un' => '');
									}
								}
							}
						}
					}
				}
			}else{// wojna zakończona
				foreach ($arrData as $row)
				{
					if( $row['worldID'] == $this -> world -> id ) {
						if ($row['playerID'] == 0 AND $row['unitType'] == 7 AND $row['tacticalDataID'] == 218) {
							$row['nation'] = 1;
						}
						if ($row['playerID'] == 0 AND $row['unitType'] == 7 AND $row['tacticalDataID'] == 219) {
							$row['nation'] = 2;
						}

						$arrReturn[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'fieldType' => $row['fieldType'], 'fieldTxt' => $this->unit->okresl_podloze($row['fieldType']), 'fieldCustom' => $row['fieldCustom'], 'fieldNation' => $row['fieldNation'], 'fieldPlayerID' => $row['fieldPlayerID'], 'playerVacation' => $row['playerVacation']);
						// Jest jednostka 1:polak,2:angol,3:rus,4:amerykanin,5:francuz,6:niemiec,7:włoch,8:japoniec
						if (isset($row['uid'])) {
							$nazwa = $this->unit->ustalNazwe(count($arrPlutons[$row['uid']]), $row['unitType'], $row['Specialty'], $row['nazwa']);
							//echo  "ustalNazwe( ".count($arrPlutons[$row['uid']] ).", ".$row['unitType'].", ".$row['Specialty'].", ".$row['nazwa']." )";
							$belongHQ = '';
							$daneUnits = '';
							$cityStand = 0;
							$vac = 0;
							
							if ($row['nation'] == $this->account->nation) {
								if ($row['unitType'] == 7) {
									$arrFieldsWithUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'view' => 6, 'nation' => $row['nation'], 'unitType' => 7, 'owner' => $row['playerID'], 'CityName' => $row['CityName'], 'nickName' => $row['nickName'], 'DefensePoints' => $row['DefensePoints'], 'un' => '', 'playerVacation' => $vac, 'id' => $row['uid'], 'timing1' => $row['timing1'], 'timing2' => $row['timing2']);
									$arrViewedArea = $this->unit->checkUnitsVision($row['x'], $row['y'], 100, $arrViewedArea);
									//$arrViewedArea[($row['x'] + $round) . ':' . ($row['y'] + ($coordY * -1))] = array('x' => $row['x'] + $round, 'y' => $row['y'] + ($coordY * -1));
									
									if ($row['playerID'] == $this->account->playerID) {

										$boolHasCity = true;
									}
								} else {
									if ($row['Specialty'] != 18 OR $row['playerID'] == $this->account->playerID) {
										$arrFieldsWithUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'id' => $row['uid'], 'view' => $row['widocznosc'], 'nation' => $row['nation'], 'unitType' => $row['unitType'], 'uid' => $row['tacticalDataID'], 'owner' => $row['playerID'], 'td' => (isset($arrPlutons[$row['uid']]) ? $arrPlutons[$row['uid']] : array()), 'Specialty' => $row['Specialty'], 'unitTurn' => $row['unitTurn'], 'DefensePoints' => $row['DefensePoints'], 'FieldArtillery' => $row['FieldArtillery'], 'Torpedo' => $row['Torpedo'], 'Morale' => $row['Morale'], 'experience' => $row['unitExperience'], 'UnderWater' => $row['UnderWater'], 'nickName' => $row['nickName'], 'belongHQ' => $belongHQ, 'cityStand' => $cityStand, 'nazwa' => $nazwa, 'un' => '', 'goldUnit' => $row['goldUnit'], 'timing1' => $row['timing1'], 'timing2' => $row['timing2']);
										$arrViewedArea = $this->unit->checkUnitsVision($row['x'], $row['y'], 100, $arrViewedArea);
									}
								}
							} else {
								if ($row['unitType'] == 7) {
									$arrEnemyUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'view' => 60, 'nation' => $row['nation'], 'unitType' => 7, 'owner' => $row['playerID'], 'CityName' => $row['CityName'], 'nickName' => $row['nickName'], 'DefensePoints' => $row['DefensePoints'], 'un' => '', 'playerVacation' => $vac, 'id' => $row['uid']);
								} else {
									if ($row['Specialty'] != 18) {
										$arrEnemyUnits[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'id' => $row['uid'], 'nation' => $row['nation'], 'unitType' => $row['unitType'], 'uid' => $row['tacticalDataID'], 'owner' => $row['playerID'], 'td' => (isset($arrPlutons[$row['uid']]) ? $arrPlutons[$row['uid']] : array()), 'Specialty' => $row['Specialty'], 'DefensePoints' => $row['DefensePoints'], 'nickName' => $row['nickName'], 'nazwa' => $nazwa, 'un' => '');
									}
								}
							}
						}
					}
				}
			}
        $arrData -> closeCursor();
		
		// sprawdzam, czy stolice istnieją
        // Sprwdzam jakie jednostki wroga są w zasięgu widzenia i zaznaczam pola, które widzi gracz
        foreach ($arrViewedArea as $key => $row)
        {
            if (isset($arrEnemyUnits[$row['x'] . ':' . $row['y']]))
            {
                $arrFieldsWithUnits[$row['x'] . ':' . $row['y']] = $arrEnemyUnits[$row['x'] . ':' . $row['y']];
            }
        }
		//przygotowanie danych do unitData
				$getTacticalData = $this -> db -> query('SELECT *, `nazwa'.$this -> account -> lang.'` as `nazwa` from `TacticalData` ');
					foreach ($getTacticalData as $rowTD)
					{
						$arrTacticalData[$rowTD['id']] = array(
							'nazwa' => $rowTD['nazwa'],
							'atak_piechota'   => $rowTD['atak_piechota'],
							'obrona_piechota' => $rowTD['obrona_piechota'],
							'atak_pancerne' => $rowTD['atak_pancerne'],
							'obrona_pancerne' => $rowTD['obrona_pancerne'],
							'atak_artyleria' => $rowTD['atak_artyleria'],
							'obrona_artyleria' => $rowTD['obrona_artyleria'],
							'atak_przeciwlotnicze' => $rowTD['atak_przeciwlotnicze'],
							'obrona_przeciwlotnicze' => $rowTD['obrona_przeciwlotnicze'],
							'atak_lotnictwo' => $rowTD['atak_lotnictwo'],
							'obrona_lotnictwo' => $rowTD['obrona_lotnictwo'],
							'atak_podwodne' => $rowTD['atak_podwodne'],
							'obrona_podwodne' => $rowTD['obrona_podwodne'],
							'atak_flota' => $rowTD['atak_flota'],
							'obrona_flota' => $rowTD['obrona_flota'],
							'atak_glebinowe' => $rowTD['atak_glebinowe'],
							'obrona_glebinowe' => $rowTD['obrona_glebinowe'],
							'ruch' => $rowTD['ruch'],
							'zasieg_strzalu' => $rowTD['ostrzal'],
							'ostrzal_przeciwlotniczy' => $rowTD['ostrzal_przeciwlotniczy'],
							'ostrzal_glebinowe' => $rowTD['ostrzal_glebinowe'],
							'ostrzal_torpeda' => $rowTD['ostrzal_torpeda'],
							'martwe_pole' => $rowTD['ostrzal_od'],
							'zasieg_widzenia' => $rowTD['widocznosc'],
							'NationID' => $rowTD['NationId'],
							'CorpsId' => $rowTD['CorpsId'],
							'zasiegSonaru' => $rowTD['zasiegSonaru'],
							'funkcje' => $rowTD['funkcje']
						);
					}
				$getTacticalData -> closeCursor();
        $opisMiasto   = '';
        $hasCityTitle = '';
		$hight='133px';
        if( $this -> account -> tutorialStage == 0 )
        {
            $opisMiasto = '<div id="tutorial_1"></div>
				<div id="tresc_samouczek">' . mapa_tut2 . '
					<br><br><div id="dalej" name="1"><div id="dalej_txt">' . samouczek_dalej . '</div></div></div>';
            $hasCityTitle = 'Samouczek. ETAP 1';
			$hight='360px';
        }
        else
        {
            $opisMiasto = comp_map2;//'Twoja kwatera główna została zniszczona. Aby ustawić ją ponownie dwukrotnie kliknij na jeden z podświetlonych heksów';
            $hasCityTitle = comp_map3;//'Ustaw swoją kwaterę główną';
        }
		
		// sprawdzam, czy gracze poleceni przez gracza account->id osiagnęli 150 punktów w grze
		$sprawdzPolecanych = $this->db->query('SELECT `acc`.`id`, `acc`.`login` FROM `accounts` AS `acc` '
                . 'LEFT JOIN `players` AS `p` ON ( `p`.`accountID`=`acc`.`id` ) '
                . 'WHERE `acc`.`refID`=' . $this -> account -> id . ' AND `p`.`points`>=150 GROUP BY `acc`.`id`');
		$iluPolecajacych = 0;
		$iluPolecajacychTXT ='';
		$sumaNagrod = 0;
		$heightPolecajacy = 103;
		foreach ($sprawdzPolecanych as $rowPolecajacy) {
			if ($rowPolecajacy['id'] != ''){
				$iluPolecajacychTXT .=  comp_map4.' '.$rowPolecajacy['login'].', '. comp_map5 .'.<br>
				'. comp_map6 .'.<br>
				'. comp_map7 .'!<br>
				';
				$iluPolecajacych++;
				$sumaNagrod += 100;
				$heightPolecajacy += 100;
			}
        }
		$iluPolecajacychTXT .='<div id="odbierzNagrode">'. comp_account75 .' '.$sumaNagrod.' '. comp_account69 .'</div>';
		
		$heightPolecajacyD = $heightPolecajacy.'px';
		$opisTutekSztaby = '';
		$tytulTutekSztaby = '';
		$hightTutekSztaby='';
		$goldNagroda = 0;
		$goldNagrodaTXT = '';
		
		if($this -> account -> tutorialStage == 10 )
        {
            // sprawdzam, czy gracz ma wybudowany sztab
			$zapytanie = $this->db->query('SELECT count(*) as `count` FROM `units` WHERE `Specialty`=7 AND `playerID`='.$this->account->playerID .'')->fetch();
			
			
			if( $zapytanie['count'] > 0 ){
				$opisTutekSztaby = '<div id="tutorial_10"></div>
				<div id="tresc_samouczek">
					'. comp_map8 .'<br>
					</div>';
				$tytulTutekSztaby = comp_map1 .' 10';
				$hightTutekSztaby = '185px';
				$this->db->exec('UPDATE `accounts` SET `tutorialStage`=11  WHERE `id`=' . $this->account->id . ' LIMIT 1');
				$goldNagroda = 50;
				$goldNagrodaTXT = comp_map9 .' 50 '. comp_account69 .'!<br>
					<div id="getGold">'. comp_account75 .'</div>';
					if ( $goldNagroda > 0 )
                    {
                        $this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', '.$goldNagroda.' )');
                    }
			}
        }
		
		// sprawdzam, czy gracz ma jakieś złoto do odebrania
		$gold = $this -> db-> query('SELECT * from `getGold` WHERE `playerID`=' . $this -> account -> playerID . ' AND `getGold`= 0 ');
		$plusGold  = 0;
		if( count($gold) > 0 ){
			foreach ($gold as $singleAddedGold) {
				$plusGold += $singleAddedGold['gold'];
			}
			$goldNagrodaTXT = comp_map10 .' '.$plusGold.' '. comp_account69 .'!<br>
			<div id="getGold">'. comp_account75 .'</div>';
			$goldNagroda = $plusGold;
		}
		
		if( $this->world->hardGame == 3 ){// jesli weteran, to ładujemy konktretne podłoża
			$lands = $this -> unit -> landFieldsTypesWeteran;
		}else{
			$lands = $this -> unit -> landFieldsTypes;
		}
		
        echo json_encode(array('map' => $arrReturn,'mapTitle' => 'MAPA', 'mapLoads'=> comp_map11,'units' => $arrFieldsWithUnits, 'hasCity' => $boolHasCity, 'hasCityTitle' => $hasCityTitle, 'hasCityDescription' => $opisMiasto,'mapIdent' => $this -> session -> session_key,'tacticalData'=> $arrTacticalData, 'mapFieldData' => $lands, 'hightWindow' => $hight,'iluPolecajacych'=>$iluPolecajacych,'polecajacyTXT'=>$iluPolecajacychTXT, 'polecajacyTitle'=>'Masz nowych polecających','heightPolecajacy'=>$heightPolecajacyD, 'opisTutekSztaby'=>$opisTutekSztaby, 'tytulTutekSztaby'=>$tytulTutekSztaby, 'hightTutekSztaby'=>$hightTutekSztaby,'goldNagroda' => $goldNagroda, 'goldNagrodaTXT'=> $goldNagrodaTXT,'tutStage'=>$this -> account -> tutorialStage, 'endW' => $endW ) );
    }

    /*
     * Metoda zapisuje mapę do bazy
     */

    public function saveData()
    {
        $mapData = $this -> path -> post('mapData');
        if ( ! is_array($mapData)) exit();

        $this -> db -> exec('TRUNCATE `mapTwo`');

        foreach ($mapData as $key => $row)
        {
            $coords = explode(',', $key);
            if (count($coords) != 2) continue;
            if ((int) $row == 0) $row = 1;
            $this -> db -> exec('INSERT INTO `mapTwo` (`x`, `y`, `fieldType`) VALUES (' . (int) $coords[0] . ',' . (int) $coords[1] . ',' . (int) $row . ')');
        }
        echo json_encode(true);
    }

    /*
    * Metoda ustawia miasto gracza na mapie
    */

    public function saveCity()
    {
        $intMoveToX = $this -> path -> post('moveToX');
        $intMoveToY = $this -> path -> post('moveToY');

        if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToX)) $this -> error -> printError('Niepoprawne koordynaty.', 'mapa');
        if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToY)) $this -> error -> printError('Niepoprawne koordynaty.', 'mapa');

        // sprawdzam czy istnieje docely wpunkt na mapie
        $arrCheckMap = $this -> db -> query('SELECT * FROM `mapData` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='.  $this -> world -> id .' LIMIT 1') -> fetch();
        if ( ! isset($arrCheckMap['fieldID'])) $this -> error -> printError( comp_map12 , 'mapa');


        // Sprawdzam czy gracz nie ma już miasta
        $arrCheckCity = $this -> db -> query('SELECT `id` FROM `units` WHERE `playerID`='.$this -> account -> playerID.' AND `unitType`=7 AND `worldID`='.  $this -> world -> id .' LIMIT 1') -> fetch();
        if (isset($arrCheckCity['id'])) $this -> error -> printError( comp_map13 , 'mapa');
        $arrCheckCity = null;

        // Sprawdzam czy na wybranym polu nic nie stoi
        $arrCheckUnit = $this -> db -> query('SELECT `id` FROM `units` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='.  $this -> world -> id .' LIMIT 1') -> fetch();
        if (isset($arrCheckUnit['id'])) $this -> error -> printError( comp_map14, 'mapa');
        $arrCheckUnit = null;

        // sprawdzam czy docelowy punkt jest w zasięgu startowym ghr
        if ($this -> account -> nation == 1)
        {
            if ($intMoveToX < 97 || $arrCheckMap['fieldType'] == 1 || $arrCheckMap['fieldType'] == 8 || $arrCheckMap['fieldType'] == 9) $this -> error -> printError(comp_map15, 'mapa');
			$unitsTDID = 218;
		}
        elseif ($this -> account -> nation == 2)
        {
            if ($intMoveToX > 35 || $arrCheckMap['fieldType'] == 1 || $arrCheckMap['fieldType'] == 8 || $arrCheckMap['fieldType'] == 9) $this -> error -> printError(comp_map15, 'mapa');
			$unitsTDID = 219;
		}

        // Wydaje się, że wszystko ok więc przenosimy jednostkę = jak już będzie wiadomo skąd to trzeba tez odjąć punkty ruchu itd
        $this -> db -> exec('INSERT INTO `units` (`playerID`,`x`, `y`, `DefensePoints`, `CityName`,`unitType`,`Morale`, `tacticalDataID`,`worldID`, `onMap`)
                VALUES ('.$this -> account -> playerID.','.$intMoveToX.', '.$intMoveToY.', 100, \'Miasto '.$this -> account -> login.'\', 7, 100, '.$unitsTDID.','. $this -> world -> id .', 1 )');
        $intCityID = $this -> db -> lastInsertID();
        $this -> db -> exec('INSERT INTO `constituentUnits` (unitsID,tacticalDataID, connectUnit) VALUES ('.$intCityID.','.$unitsTDID.','.$intCityID.' ) ');

        $arrATReturnData = $this -> unit -> checkNotSeenUnits($intCityID, $intMoveToX, $intMoveToX, true);
        $arrATNotSeenUnits = $arrATReturnData['arrNotSeenUnits'];
        $tutorialInfo =false;
        if( $this -> account -> tutorialStage == 0 )
        {
            $tutorialInfo = samouczek8;
            $this -> db -> exec('UPDATE `accounts` SET `tutorialStage`=1 WHERE `id`=' . $this -> account -> id . ' LIMIT 1');
        }
		$info ='Miasto wybudowane';
        $arrayData = array('activity' => 'newCity', 'chanData' => array(
            array('chanName' => 'worldmap'.$this -> world -> id.'nation'.$this -> account -> nation,
                'data' => array(
                    'unitData' => array(
                        'x' => $intMoveToX,
                        'y' => $intMoveToY,
						'xDel' => false,
						'yDel' => false,
                        'id' => $intCityID,
                        'view' => 6,
                        'nation' => $this -> account -> nation,
                        'unitType' => 7,
                        'uid' => $intCityID,
                        'owner' => $this -> account -> playerID,
						'DefensePoints' => 100,
						'info' => $info,
                        'tutorialInfo' => $tutorialInfo
                    ),
					'RemoveOldCity' => array(
						'x' => false,
						'remove' => false
					),
					'unitDataInz' => array(
						'remove' => false
					),
                    'enemyUnitsData' => $arrATNotSeenUnits
                )
            )
        ));
		
		//jak już mamy miasto wybudowane, sprawdzamy, czy gracz wykorzystał jednostki startowe, jeśli nie- losujemy jednostki do bazy
		if( $this -> account -> testedUnits == 0 )
		{
			if ($this -> account -> nation == 1)
			{
				$warunek_losowanie ='(`NationId`=1 OR `NationId`=2)';
			}
			else
			{
				$warunek_losowanie ='(`NationId`=7 OR `NationId`=8)';
			}
			for ($x = 1; $x <= 10; $x ++ )
			{
				$statsListPromo = $this -> db -> query('SELECT `id` FROM `TacticalData` WHERE `rocznik`<='.$this -> world -> WarDateY.' AND ' . $warunek_losowanie . ' AND `corpsID`!=5 AND `corpsID`!=7 AND `id`!=218 AND `id`!=219 AND `id`!= 39 AND `id`!=38 AND `id`!= 224 AND `id`!=225 ORDER BY RAND() LIMIT 1') -> fetch();
				$this -> account -> buildUnit( $statsListPromo['id'] , $this -> account -> playerID, 0 );
			}
			$this -> db -> exec('UPDATE `players` SET `testedUnits`=1 WHERE `id`=' . $this -> account -> playerID . ' LIMIT 1');
		}
		
        $objHelper = init::getFactory() -> getService('helper');
        $objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

        $arrCheckMap = null;
        $arrUnitData = null;
        echo json_encode(true);
    }

    /*
     * Kreator danych mapy
     */

    public function mapCreator()
    {
        echo $this -> twig -> render('map/views/creator.html');
    }

    public function loadMap()
    {
        $objHelper = init::getFactory() -> getService('helper');
        $this -> tmplData['file'] = 'mapa.html';
        $this -> tmplData['variables']['title'] = title . ' ' . $this -> tmplData['variables']['title'];
        // Start Hex
        if (isset($this -> world -> startHex[$this -> account -> nation]))
        {
            $this -> tmplData['variables']['startCoords'] = $this -> world -> startHex[$this -> account -> nation];
        }
        else {
            $this->tmplData['variables']['startCoords'] = array('x' => 93, 'y' => -58);
        }
        $this -> tmplData['variables']['opticalAlert'] = $this -> account -> opticalAlert;
        $this -> tmplData['variables']['sounds'] = $this -> account -> sounds;
		$this -> tmplData['variables']['rank'] = $this -> account -> rank;
        $this -> tmplData['variables']['id'] = $this -> account -> id;
		$this -> tmplData['variables']['timeK'] = $this -> account -> timeK;
		$this -> tmplData['variables']['timeP'] = $this -> account -> timeP;
		$this -> tmplData['variables']['rawMaterials'] = $this -> account -> rawMaterials;
		
		
        
        $ile_dni3 = "+ 3 day";
        $ile_dni7 = "+ 7 day";
        $ile_dni10 = "+ 10 day";
        $ile_dni14 = "+ 14 day";
        if ($this -> account -> playerVacation != 0)
        {
            $teraz = strtotime($this -> account -> playerVacation);
        }
        else
        {
            $datateraz = date("Y-m-d H:i:s");
            $teraz = strtotime($datateraz);
        }
        $pozniej3 = strtotime($ile_dni3, $teraz);
        $pozniej3 = date('Y-m-d H:i:s', $pozniej3);
        $this -> tmplData['variables']['pozniej3'] = $pozniej3;
        $pozniej7 = strtotime($ile_dni7, $teraz);
        $pozniej7 = date('Y-m-d H:i:s', $pozniej7);
        $this -> tmplData['variables']['pozniej7'] = $pozniej7;
        $pozniej10 = strtotime($ile_dni10, $teraz);
        $pozniej10 = date('Y-m-d H:i:s', $pozniej10);
        $this -> tmplData['variables']['pozniej10'] = $pozniej10;
        $pozniej14 = strtotime($ile_dni14, $teraz);
        $pozniej14 = date('Y-m-d H:i:s', $pozniej14);
        $this -> tmplData['variables']['pozniej14'] = $pozniej14;
        $soflow_color1 = '';
        $soflow_colormini = '';
        if ($this -> account -> SponsorAccount > $pozniej3)
        {
            $soflow_color1 = '<option value="3">' . ustawienia31 . '</option>';
        }
        if ($this -> account -> SponsorAccount > $pozniej7)
        {
            $soflow_color1.='<option value="7">' . ustawienia32 . '</option>';
        }
        if ($this -> account -> SponsorAccount > $pozniej10)
        {
            $soflow_color1.='<option value="10">' . ustawienia33 . '</option>';
        }
        if ($this -> account -> SponsorAccount > $pozniej14)
        {
            $soflow_color1.='<option value="14">' . ustawienia34 . '</option>';
        }
        $this -> tmplData['variables']['soflow-color1'] = $soflow_color1;
        $soflow_colormini = '<option value="7">' . ustawienia32 . '</option>';
        $soflow_colormini.='<option value="14">' . ustawienia34 . '</option>';
        $this -> tmplData['variables']['soflow-colormini'] = $soflow_colormini;
        $this -> tmplData['variables']['playerVacation'] = $this -> account -> playerVacation;
        $this -> tmplData['variables']['UrlopNumber'] = $this -> account -> VacationNum;
        $this -> tmplData['variables']['infoPagon'] = $this -> account -> checkRank($this -> account -> playerID, $this -> account -> experience, $this -> account -> nation);

        $tura_up = $this -> db -> query('SELECT * FROM `turnSetting` WHERE `playerID`=' . $this -> account -> playerID . ' LIMIT 1') -> fetch();
        $tura_priv = $tura_up['TurnPlayer'];
        $tura_1baza = $tura_up['Turn1'];
        $tura_2baza = $tura_up['Turn2'];
        $tura_3baza = $tura_up['Turn3'];
        $tura_4baza = $tura_up['Turn4'];
        $up = $tura_up['TurnUp'];
        $godzina = date('H');
        if ($godzina < $tura_1baza)
        {
            $twoja_tura = head15 . "( " . $tura_priv . " )" . head16 . "<br><strong>" . $tura_1baza . "</strong>," . $tura_2baza . "," . $tura_3baza . "," . $tura_4baza;
        }
        else if ($godzina >= $tura_1baza AND $godzina < $tura_2baza)
        {
            $twoja_tura = head15 . "( " . $tura_priv . " )" . head16 . "<br>" . $tura_1baza . ",<strong>" . $tura_2baza . "</strong>," . $tura_3baza . "," . $tura_4baza;
        }
        else if ($godzina >= $tura_2baza AND $godzina < $tura_3baza)
        {
            $twoja_tura = head15 . "( " . $tura_priv . " )" . head16 . "<br>" . $tura_1baza . "," . $tura_2baza . ",<strong>" . $tura_3baza . "</strong>," . $tura_4baza;
        }
        else if ($godzina >= $tura_3baza AND $godzina < $tura_4baza)
        {
            $twoja_tura = head15 . "( " . $tura_priv . " )" . head16 . "<br>" . $tura_1baza . "," . $tura_2baza . "," . $tura_3baza . ",<strong>" . $tura_4baza . "</strong>";
        }
        else if ($godzina >= $tura_4baza)
        {
            $twoja_tura = head15 . "( " . $tura_priv . " )" . head16 . "<br><strong>" . $tura_1baza . "</strong>," . $tura_2baza . "," . $tura_3baza . "," . $tura_4baza;
        }
		
		
        $this -> tmplData['variables']['infoTurn'] = $twoja_tura;
        $this -> tmplData['variables']['TurnUp'] = $up;
        if ($up == 0)
        {
            $this -> tmplData['variables']['infoUp'] = "<div id=\"tury_info_panel2\">" . head17 . "</div>";
        }
        else
        {
            $this -> tmplData['variables']['infoUp'] = '';
        }
        //obliczam dane do zegarka
        $data_zegar = strtotime(date('Y-m-d H:i:s'));
        //echo $czwarta_nastepna;
        $pierwsza     = strtotime(date('Y-m-d ' . $tura_1baza . ':00:00'));
        $druga        = strtotime(date('Y-m-d ' . $tura_2baza . ':00:00'));
        $trzecia      = strtotime(date('Y-m-d ' . $tura_3baza . ':00:00'));
        $czwarta      = strtotime(date('Y-m-d ' . $tura_4baza . ':00:00'));
        $polnoc_po    = strtotime(date('Y-m-d 00:00:01'));
        $polnoc_przed = strtotime(date('Y-m-d 23:59:59'));
        $m = date('m') - 1;
        if ($data_zegar > $czwarta AND $data_zegar <= $pierwsza)
        {
            $date = date('Y,' . $m . ',d,' . $tura_1baza . ',00,00');
            //echo "opcja1";
        }
        else if ($data_zegar > $pierwsza AND $data_zegar <= $druga)
        {
            $date = date('Y,' . $m . ',d,' . $tura_2baza . ',00,00');
            //echo "opcja2";
        }
        else if ($data_zegar > $druga AND $data_zegar <= $trzecia)
        {
            $date = date('Y,' . $m . ',d,' . $tura_3baza . ',00,00');
            //echo "opcja3";
        }
        else if ($data_zegar > $trzecia AND $data_zegar <= $czwarta)
        {
            $date = date('Y,' . $m . ',d,' . $tura_4baza . ',00,00');
            //echo "opcja4";
        }
        else if ($data_zegar > $czwarta AND $data_zegar <= $polnoc_przed AND $tura_4baza != 0 )
        {
            $date = date('Y,m,d,' . $tura_1baza . ',00,00');
            //echo "opcja5";
        }else if ($data_zegar > $czwarta AND $data_zegar <= $polnoc_przed AND $tura_4baza == 0 )
        {
            $m = date('m') + 1;
            $date = date('Y,m,d,' . $tura_4baza . ',00,00');
            //echo "opcja5";
        }
        else if ($data_zegar > $polnoc_po AND $data_zegar <= $pierwsza)
        {
            $date = date('Y,' . $m . ',d,' . $tura_1baza . ',00,00');
            //echo "opcja6";
        }
        $this -> tmplData['variables']['infoDate'] = $date;
        //ustalam, czy gracz ma do odbioru jakieś pieniadze za żołd
		$playerFinance = $this -> db -> query('SELECT count(*) as `counted` FROM `playerFinance` WHERE `playerID`=' . $this -> account -> playerID . ' ') -> fetch();
		$this -> tmplData['variables']['kasa_odbior'] = $playerFinance['counted'];
		
		if( $this -> account -> timeK < $this->init->time AND $this ->world ->timing > 0 AND $playerFinance['counted'] == 0 ){
			$this -> account -> playerFinance( $this->account->playerID, $this->account->experience, $this->account->nation);
			$this -> tmplData['variables']['kasa_odbior'] = 1;
		}
		
        $this -> tmplData['variables']['hardGame'] = $this -> world -> hardGame;
        $opis_pogody = '';
        if ($this -> world -> hardGame == 1 )
        {
            $opis_pogody = comp_map16 .'.<br><br>';
        }
		
		switch ($this -> world -> WarWeather) {
            case 1://wichura
                $wspolczynnik_pogody = 0.7;
                $opis_pogody .= head4;
                $grafika_pogody = "./app/templates/assets/images/pogoda/wiatr.png";
                $warunek_pogodowy_do_sklepu = ' AND idDaneJednostek%2=0';
                break;
            case 2://oipday sniegu
                $wspolczynnik_pogody = 0.7;
                $opis_pogody .= head5;
                $grafika_pogody = "./app/templates/assets/images/pogoda/snieg.png";
                $warunek_pogodowy_do_sklepu = ' AND point_x%2=0';
                break;
            case 3://ulewa
                $wspolczynnik_pogody = 0.8;
                $opis_pogody .= head6;
                $grafika_pogody = "./app/templates/assets/images/pogoda/deszcz.png";
                $warunek_pogodowy_do_sklepu = ' AND point_x%2=1';
                break;
            case 4://słoneczna pogoda
                $wspolczynnik_pogody = 1;
                $opis_pogody .= head7;
                $grafika_pogody = "./app/templates/assets/images/pogoda/slonecznie.png";
                $warunek_pogodowy_do_sklepu = ' AND idDaneJednostek%2=1';
                break;
            case 5: //umiarjkowana pogoda
                $wspolczynnik_pogody = 0.9;
                $opis_pogody .= head8;
                $grafika_pogody = "./app/templates/assets/images/pogoda/umiarkowane_zachmurzenie.png";
                $warunek_pogodowy_do_sklepu = ' AND point_y%2=0';
                break;
            case 6://susza
                $wspolczynnik_pogody = 1;
                $opis_pogody .= head9;
                $grafika_pogody = "./app/templates/assets/images/pogoda/upal.png";
                $warunek_pogodowy_do_sklepu = ' AND point_y%2=1';
                break;
            case 7://siarczysty mróz
                $wspolczynnik_pogody = 0.9;
                $opis_pogody .= head10;
                $grafika_pogody = "./app/templates/assets/images/pogoda/siarczysty_mroz.png";
                $warunek_pogodowy_do_sklepu = ' AND point_y%2=1 AND point_x%2=1';
                break;
            case 8://zawieja i zamieć
                $wspolczynnik_pogody = 0.8;
                $opis_pogody .= head11;
                $grafika_pogody = "./app/templates/assets/images/pogoda/zawieja_zamiec.png";
                $warunek_pogodowy_do_sklepu = ' AND point_y%2=0 AND point_x%2=1';
                break;
            default:
        }
        if ($this -> world -> WarWeather != 4 AND $this -> world -> WarWeather != 5)
        {
            $opis_pogody.=head12;
        }

        $this -> tmplData['variables']['weaterDescription'] = $opis_pogody;
        $this -> tmplData['variables']['weaterGraphics'] = $grafika_pogody;
		

        //sprawdzam, czy jest jakaś promocja
	   $this -> tmplData['variables']['promotion'] = $this -> account -> checkPromotion( $this -> account -> playerID, $this -> world -> id );
	   $this -> tmplData['variables']['questionnaire'] = $this -> account -> questionnaire();

        // poniżej kod do faq

        $faq1 = $this -> db -> query('SELECT * from `poradnik` WHERE `temat`=1');
        $i1 = 1;
        foreach ($faq1 as $daneFaq)
        {
            $arr1[$i1] = array(
              'pytanie' => $daneFaq['pytanie'],
              'odpowiedz' => $daneFaq['odpowiedz'],
              'num' => $i1
            );
            $i1 ++;
        }
        $this -> tmplData['variables']['faq1'] = $arr1;

        $faq2 = $this -> db -> query('SELECT * from `poradnik` WHERE `temat`=2');
        $i2 = 1;
        foreach ($faq2 as $daneFaq)
        {
            $arr2[$i2] = array(
              'pytanie' => $daneFaq['pytanie'],
              'odpowiedz' => $daneFaq['odpowiedz'],
              'num' => $i2
            );
            $i2 ++;
        }
        $this -> tmplData['variables']['faq2'] = $arr2;

        $faq3 = $this -> db -> query('SELECT * from `poradnik` WHERE `temat`=3');
        $i3 = 1;
        foreach ($faq3 as $daneFaq)
        {
            $arr3[$i3] = array(
              'pytanie' => $daneFaq['pytanie'],
              'odpowiedz' => $daneFaq['odpowiedz'],
              'num' => $i3
            );
            $i3 ++;
        }
        $this -> tmplData['variables']['faq3'] = $arr3;

        $faq4 = $this -> db -> query('SELECT * from `poradnik` WHERE `temat`=4');
        $i4 = 1;
        foreach ($faq4 as $daneFaq)
        {
            $arr4[$i4] = array(
              'pytanie' => $daneFaq['pytanie'],
              'odpowiedz' => $daneFaq['odpowiedz'],
              'num' => $i4
            );
            $i4 ++;
        }
        $this -> tmplData['variables']['faq4'] = $arr4;

        $faq5 = $this -> db -> query('SELECT * from `poradnik` WHERE `temat`=5');
        $i5 = 1;
        foreach ($faq5 as $daneFaq)
        {
            $arr5[$i5] = array(
              'pytanie' => $daneFaq['pytanie'],
              'odpowiedz' => $daneFaq['odpowiedz'],
              'num' => $i5
            );
            $i5 ++;
        }
        $this -> tmplData['variables']['faq5'] = $arr5;

        $faq6 = $this -> db -> query('SELECT * from `poradnik` WHERE `temat`=6');
        $i6 = 1;
        foreach ($faq6 as $daneFaq)
        {
            $arr6[$i6] = array(
              'pytanie' => $daneFaq['pytanie'],
              'odpowiedz' => $daneFaq['odpowiedz'],
              'num' => $i6
            );
            $i6 ++;
        }
        $this -> tmplData['variables']['faq6'] = $arr6;
        $arr = '';
        $logins = $this -> db -> query('SELECT * from `accounts` ');
        foreach ($logins as $dane)
        {
            $arr.= '"' . $dane['login'] . '",';
        }
        $this -> tmplData['variables']['userList'] = $arr;
		$this -> tmplData['variables']['sponsorUnitsList'] = $this -> unit -> loadUnits( '1_1' );//ładuję jendostki do paska
		$cityCoords = $this -> db -> query('SELECT `x`,`y` from `units` WHERE `playerID`='.$this -> account -> playerID.' AND `unitType`=7 AND `worldID`='.  $this -> world -> id .'')->fetch();
		if( $cityCoords ){
			$this -> tmplData['variables']['coordX'] = $cityCoords['x'];
			$this -> tmplData['variables']['coordY'] = $cityCoords['y'];
		}else{
			if ($this -> account -> nation ==  1 )
			{
				$this -> tmplData['variables']['coordX'] = 122;
				$this -> tmplData['variables']['coordY'] = -117;
			}
			else {
				$this -> tmplData['variables']['coordX'] = 10;
				$this -> tmplData['variables']['coordY'] = -61;
			}
		}
		
		
		
		//sprawdzenie, czy gracz ma jakieś wiadomości
		$arrMess = $this -> db -> query('SELECT count(*) FROM `message` WHERE `playerID`='.$this -> account -> playerID.' AND `blockPlayerID`= 0 AND `seeMessage`="no" GROUP BY `messageID`  ')->fetch();
        $this -> tmplData['variables']['mess'] = (int)$arrMess[0];
		//sprawdzenie, czy gracz ma jakieś raporty bojowe
		$arrRap = $this -> db -> query('SELECT count(*) FROM `battleRaport` WHERE `playerID`='.$this -> account -> playerID.' AND `seen`=0  ')->fetch();
		$this -> tmplData['variables']['rap'] = (int)$arrRap[0];
		//sprawdzenie,c zy gracz widział info o doładowaniu pieniedzy
		$arrOp = $this -> db -> query('SELECT count(*) FROM `operacje` WHERE `accountID`='.$this -> account -> id.' AND `seen`=0  ')->fetch();
		$this -> tmplData['variables']['op'] = (int)$arrOp[0];
		
		//sprawdzam, czy gracz ma jakieś jendostki do naprawy
		$box_title = '';
		$arrDamaged = $this -> db -> query('select  COUNT(*) as `ilosc_jednostek`, SUM(`uszkodzone`.`mozliwy`) as
		`realny_koszt`, SUM(`koszt`) as `pelny_koszt`
		FROM
		(SELECT `DefensePoints`,  `ubytek`, `koszt`,  CASE 
		WHEN `koszt`  >= 0 THEN `koszt` ELSE
		`koszt` END as `mozliwy`
		FROM
		(SELECT `DefensePoints`, (100-`DefensePoints`) as `ubytek`,
		CEIL((100-`DefensePoints`) /10) as `koszt`
		FROM `units`
		where `playerID` = '. $this -> account -> playerID .' AND `DefensePoints` < 100 AND `x`!= 0 AND `y`!=0 Group by
		`id`) `mozliwe`) `uszkodzone`') -> fetch();
		$koszt = $arrDamaged['realny_koszt'];
		
		if( $koszt < $this -> account -> gold AND $koszt != 0 )
		{
			$box_title = '<div class="repairDiv" num="1" title=" '. mapa_naprawa1 .' '.$arrDamaged['realny_koszt'].' '. sztuk_zlota_txt .'">'. $arrDamaged['realny_koszt'] .'</div>';
		}
		else if( $koszt >= $this -> account -> gold )
		{
			$box_title = '<div id="repairDivN" class="bank" title=" '. mapa_naprawa5 .' '. $arrDamaged['realny_koszt'] .' '. sztuk_zlota_txt .'! '. panel_gry9 .'">'. $arrDamaged['realny_koszt'] .'</div>';
		}
		
		$this -> tmplData['variables']['damaged'] = $box_title;
		//sprawdzenie, czy sa jakieś nowe posty w forum

        //ładuję do tabeli wszystkie posty, które user widział
        $listaWidzianychTematow = $this -> db -> query('SELECT COUNT(*) AS `ileWidzianych` FROM `forum_widziane` WHERE `accountID`='. $this -> account -> id .' ')->fetch();

        //liczę, ile tematów ma całe forum, jeśli liczba tematów jest większa niż liczba widzianych tematów to znak, że gracz nie widział któregoś :)
        $wynik = $this -> db -> query('SELECT COUNT(*) AS `liczbaTematow` FROM `forum_tematy` ') -> fetch();
        $koperta = 0;
	if( $wynik['liczbaTematow'] > $listaWidzianychTematow['ileWidzianych'] )
	{
		$koperta = 1;
	}
        $this -> tmplData['variables']['koperta'] = $koperta;
		
        //ustalam, czy jest jakiś sojusznik zalogowany

        $arrSojusznicy = $this -> db -> query('SELECT `acc`.`login`, `pl`.`id` FROM `sessions` AS `ses` LEFT JOIN `accounts` AS `acc` ON `ses`.`account` = `acc`.`id` LEFT JOIN `players` AS `pl` ON `pl`.`accountID`=`acc`.`id` WHERE `pl`.`worldID`='. $this -> world -> id .' AND `pl`.`nation`='. $this -> account -> nation .' ');

        $listaGraczy = '';
        $count  = 0;
        $count2 = 0;
        foreach ($arrSojusznicy as $listaSojusznikow)
        {
            $arrFriends = $this -> db -> query('SELECT count(`trustedPlayer`) FROM `trustedPlayers` WHERE `playerID`='.$listaSojusznikow['id'].' AND `trustedPlayer`='. $this -> account -> playerID .' ')->fetch();
            if( $arrFriends[0] == 1 )
            {
                $listaGraczy .=  $listaSojusznikow['login'].' ,';
                $count2++;
            }
            $count++;
        }

        if( $count > 1 )
        {
            $nowyCount =  $count - $count2 - 1;
            if( $count2 == 0 )
            {
                $this -> tmplData['variables']['sojusznicy'] = mapa_ally_active .': '.( $count - 1 );

            }
            else if( $count2 == ( $count - 1) )
            {
                $this -> tmplData['variables']['sojusznicy'] = mapa_ally_active .': '.$listaGraczy;

            }
            else
            {
                $this -> tmplData['variables']['sojusznicy'] = mapa_ally_active .': '.$listaGraczy.' i '.$nowyCount.' '. comp_map17;

            }
        }
        else
        {
            $this -> tmplData['variables']['sojusznicy'] = mapa_ally_no_active;
        }
        //sprawdzam, czy gracz ma zatrudnionego inzyniera echolokacji
        if ($this -> account -> nation == 1)
        {
            $personnel = ' `poleSonar`';
        }
        else
        {
            $personnel = ' `germanSonar`';
        }
        $statsUnit = $this -> db -> query('SELECT ' . $personnel . ' AS `sonar` FROM `PlayerPersonnel` WHERE `playerID`=' . $this -> account -> playerID . ' ') -> fetch();
        $this -> tmplData['variables']['sonar'] = $statsUnit['sonar'];
    }

    public function loadChatMsg()
    {
        $chatDataType = $this -> path -> get('chatType');

        $intMsgLimit = 50;

        if ($chatDataType == 'world')
        {
            // chat messages
            $arrChatMsg = $this -> db -> query('SELECT * FROM `chat` WHERE `worldID`='. $this -> world -> id .' AND `channel`=0 AND `private`=0 ORDER BY `date` DESC LIMIT '.$intMsgLimit.'');

        }
        elseif ($chatDataType == 'nation')
        {
            // chat messages
            $arrChatMsg = $this -> db -> query('SELECT * FROM `chat` WHERE `worldID`='. $this -> world -> id .' AND `channel`='. $this -> account -> nation .' AND `private`=0 ORDER BY `date` DESC LIMIT '.$intMsgLimit.'');

        }
        elseif ($chatDataType == 'private')
        {
            // friend data
            $arrFiendsIDs = array();
            $arrFiendsIDs[] = $this -> account -> playerID;// ufam sobie :)
            $arrFriends = $this -> db -> query('SELECT `playerID` FROM `trustedPlayers` WHERE `trustedPlayer`='. $this -> account -> playerID .' ORDER BY id ASC');
            foreach ($arrFriends as $row)
            {
                $arrFiendsIDs[] = $row['playerID'];
            }
            $arrFriends -> closeCursor();

            // chat messages
            $arrChatMsg = $this -> db -> query('SELECT * FROM `chat` WHERE  `worldID`='. $this -> world -> id .' AND `private`=1 '.(count($arrFiendsIDs) > 0 ? 'AND `playerID` IN ('.implode(',', $arrFiendsIDs).')' : '').' ORDER BY `id` DESC LIMIT '.$intMsgLimit.'');
        }
        $arrReturn = array_reverse($arrChatMsg -> fetchAll(PDO::FETCH_ASSOC));//odwaracamy kolejność wyświetlania postów
        $arrChatMsg -> closeCursor();

        $returnHTML = '<div class="sb" id="sb_'.$chatDataType.'"><ul>';
		
        foreach ($arrReturn as $row)
        {
			$text = $row['text'];
			$emots = [
				':)' => 'wesoly.png',
				':d' => 'szczesliwy.png',
				':(' => 'smutny.png',
				':p' => 'jezyk.png',
				';)' => 'mruga.png',
				':|' => 'pokerface.png',
				':o' => 'what.png',
				'xd' => 'iks_de.png',
				// dodajemy z duzymi literami
				':D' => 'szczesliwy.png',
				':P' => 'jezyk.png',
				':0' => 'what.png',
				'XD' => 'iks_de.png',
				'Xd' => 'iks_de.png',
				'xD' => 'iks_de.png',
				'lol' => 'iks_de.png',
				'LOL' => 'iks_de.png'
			];
			// pętla zamieniająca nazwe pliku na kod html
			
			foreach($emots as $dane => $img){
				$emots[$dane] = '<img src="../app/templates/assets/images/emots/'.$img.'" title="'.$dane.'" alt="'.$dane.'" />';
			}
			// zamiana tekstu na emotki
			$text = str_replace(array_keys($emots), array_values($emots), $text);
			
			$returnHTML .= '<li><span class="date">'.date('d/m/Y H:i:s', $row['date']).'</span>:<span class="sbUser_'.$row['nationID'].'"> '.stripslashes($row['login']).'</span> : '. $text .'</li>';
        }
        $returnHTML .= '</ul>';
		if( $this -> account -> ban == 3){
			$returnHTML .= '<p style="text-align:center;"><br>'.mapa_banowany.'</p>';
		}else{
			$returnHTML .= '<form onsubmit="sendChatMsg(&#039;'.$chatDataType.'&#039;); return false;"><input id="msg_'.$chatDataType.'" type="text" class="shout" placeholder="'.mapa_radio_mess.'"/>
					<button class="add" onclick="sendChatMsg(&#039;'.$chatDataType.'&#039;); return false;">
						'.mapa_radio_add.'
					</button>
					<input id="isSoundOn" type="checkbox" title="'.mapa_radio_notification.'" class="sound"/>
					</form>
			</div>';
		}
        echo json_encode(array('response' => $returnHTML));
    }

    public function sendChatMsg()
    {
        $objHelper = init::getFactory() -> getService('helper');

        $chatDataType = $this -> path -> get('chatType');
        $arrPostData = $this -> path -> post('postData');
        if (!isset($arrPostData['msg'])) $this -> error -> printError('Wpisz wiadomość.', 'mapa');
        $strMsg = $objHelper -> safeChatText($arrPostData['msg']);
        if (strlen($strMsg) == 0) $this -> error -> printError('Wpisz wiadomość.', 'mapa');

        if ($chatDataType == 'world')
        {
            $strChanName = 'worldchat'.$this -> world -> id;
            $sql = 'INSERT INTO `chat` (`worldID`,`nationID`,`playerID`,`login`,`text`,`date`,`private`,`channel`) VALUES ('.$this -> world -> id.', '.$this -> account -> nation.', '.$this -> account -> playerID.', :login, :text, '.time().', 0,0)';
        }
        elseif ($chatDataType == 'nation')
        {
            $strChanName = 'worldchat'.$this -> world -> id.'nation'.$this -> account -> nation;
            $sql = 'INSERT INTO `chat` (`worldID`,`nationID`,`playerID`,`login`,`text`,`date`,`private`,`channel`) VALUES ('.$this -> world -> id.', '.$this -> account -> nation.', '.$this -> account -> playerID.', :login, :text, '.time().', 0,'.$this -> account -> nation.')';
        }
        elseif ($chatDataType == 'private')
        {
            $arrPrivateChannels = array();
            $arrPrivateChannels[] = 'worldchat'.$this -> world -> id.'private'.$this -> account -> playerID;// ufam sobie :)
            $arrFriends = $this -> db -> query('SELECT `playerID` FROM `trustedPlayers` WHERE `trustedPlayer`='.$this -> account -> playerID.' ORDER BY id ASC');
            foreach ($arrFriends as $row)
            {
                $arrPrivateChannels[] = 'worldchat'.$this -> world -> id.'private'.$row['playerID'];
            }
            $arrFriends -> closeCursor();
            $strChanName = $arrPrivateChannels;
            $sql = 'INSERT INTO `chat` (`worldID`,`nationID`,`playerID`,`login`,`text`,`date`,`private`,`channel`) VALUES ('.$this -> world -> id.', '.$this -> account -> nation.', '.$this -> account -> playerID.', :login, :text, '.time().', 1,3)';
        }
        else
        {
            $this -> error -> printError( comp_map20, 'mapa');
        }

        // coordsy dodaje
        $strMsg = preg_replace('/\[([1-9-]\d*)\,([1-9-]\d*)\]/', "<span class=\"goToCoords\" x=\"$1\" y=\"$2\">[$1,$2]</span>", $strMsg);
		// tutaj dodamy emotikonki w tekscie
		
        $query = $this -> db -> prepare($sql);
        $query -> bindValue(':text',$strMsg, PDO::PARAM_STR);
        $query -> bindValue(':login', $this -> account -> login, PDO::PARAM_STR);
        $query -> execute();
		
		// emotki
		
		$emots = Array(
				':)' => 'wesoly.png',
				':d' => 'szczesliwy.png',
				':(' => 'smutny.png',
				':p' => 'jezyk.png',
				';)' => 'mruga.png',
				':|' => 'pokerface.png',
				':o' => 'what.png',
				'xd' => 'iks_de.png',
				// dodajemy z duzymi literami
				':D' => 'szczesliwy.png',
				':P' => 'jezyk.png',
				':0' => 'what.png',
				'XD' => 'iks_de.png',
				'Xd' => 'iks_de.png',
				'xD' => 'iks_de.png',
				'lol' => 'iks_de.png',
				'LOL' => 'iks_de.png',
			
            );
			// pętla zamieniająca nazwe pliku na kod html
			foreach($emots as $dane => $img){
				$emots[$dane] = '<img src="../app/templates/assets/images/emots/'.$img.'" title="'.$dane.'" alt="'.$dane.'" />';
			}
			// zamiana tekstu na emotki
			$strMsg = str_replace(array_keys($emots), array_values($emots), $strMsg);
		
		
		
		
        $arrayData = array('chanName' => $strChanName, 'message' => $strMsg, 'info' => array('chanType' => $chatDataType, 'msgDate' => date('d/m/Y H:i:s'), 'nationID' => $this -> account -> nation,'login' => $this -> account -> login, 'playerID' => $this -> account -> playerID));


        $objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/message');
        echo json_encode($arrayData);
    }

    /*
     * Metoda ustawia jednostki z miasta
     */
	
	public function setUnit()
	{
		$intMoveToX = $this -> path -> post('moveToX');
		$intMoveToY = $this -> path -> post('moveToY');
		$intUnitID  = $this -> path -> post('unitID');
		if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToX)) $this -> error -> printError(comp_map21, 'mapa');
        if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToY)) $this -> error -> printError(comp_map21, 'mapa');
        if ( ! preg_match("/^[\d]{1,11}$/i", $intUnitID)) $this -> error -> printError(comp_map22, 'mapa');
		
		//pobieram dane miasta gracza
		$arrCityData = $this -> db -> query('SELECT * FROM `units` WHERE `playerID`=' . $this -> account -> playerID . ' AND `unitType`=7 AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        if ( ! isset($arrCityData['id'])) $this -> error -> printError(comp_map23, 'mapa');

		// sprawdzam czy istnieje docely wpunkt na mapie
        $arrCheckMap = $this -> db -> query('SELECT `fieldID` FROM `mapData` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        if ( ! isset($arrCheckMap['fieldID'])) $this -> error -> printError(comp_map12, 'mapa');
		
		if ( $intMoveToX == 0 AND $intMoveToY == 0 ) $this -> error -> printError('Na to pole nie możesz stanąć ;)', 'mapa');
        $arrCheckMap = null;

        // Sprawdzam czy na wybranym polu nic nie stoi
        $arrCheckUnit = $this -> db -> query('SELECT `id` FROM `units` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        if (isset($arrCheckUnit['id'])) $this -> error -> printError(comp_map14, 'mapa');
        $arrCheckUnit = null;
		
		$up1 = 0;
		$up2 = 0;
		$licznik1 = 0;
		$licznik2 = 0;
		$liczniki = $this -> db -> query('SELECT `timing1`, `timing2`, `idLC`, `x`, `y`, `onMap` FROM `units` WHERE `id`= '.$intUnitID.' AND `worldID`='. $this -> world -> id .'  LIMIT 1') -> fetch();
		if( $this -> world -> timing == 1 ){// jeśli świat poligon ( na razie tylko tam są zegarki )
			// sprawdzam, czy licnziki sie wyzerowały
				
			if( $liczniki['timing1'] > $this -> init -> time ){
				if( $liczniki['timing2'] > $this -> init -> time ){
					$this -> error -> printError(comp_battleE2, 'mapa');
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
		$this -> unit -> getMovedUnitData(
            array(
                'unitID' => $intUnitID, // ID jednostki
                'moveToX' => $intMoveToX, // X docelowy
                'moveToY' => $intMoveToY,  // Y docelowy
                'fromCity' => true,  // jednostka wystawiana z miasta
                'moveFromX' => $arrCityData['x'], // Koordynaty miasta
                'moveFromY' => $arrCityData['y'], // Koordynaty miasta
                'rounds' => 1,
				'licznik1' => $licznik1,// timing do pierwszego zegarka
				'licznik2' => $licznik2,// timing do drugiego zegarka
				'desant' => 0
            )
        );
        // Ruch się udał, pobieram dane z zegarków 
		$updateLiczniki = ',`unitTurn`=(`unitTurn`-1)';
		//zapisujemy dane jednostki
        if($up1==1){
			$updateLiczniki = ',`timing1`='.$licznik1;
		}
		if($up2==1){
			$updateLiczniki = ',`timing2`='.$licznik2;
		}
		
		$idLC = '';
		if( $liczniki['idLC'] != $intUnitID ){// nastąpił wyładunek jednostki z barki 
			$idLC = ',`idLC` = 0';
		}
		
		$onMap = $liczniki['onMap'];// ta zmienna odpowiada za to czy jednostka jest widoczna na mapie czy nie
		if( $liczniki['x'] == 0 AND $liczniki['y'] == 0 ){
			$onMap = 1;
		}
		
		
		$this -> db -> exec('UPDATE `units` SET `onMap`= '. $onMap .', `x`=' . $intMoveToX . ', `y`=' . $intMoveToY . ' '. $updateLiczniki .' '. $idLC .' WHERE `id`=' . $intUnitID . ' LIMIT 1');
        $arrCityData = null;
	}
	
	
	public function setUnitDesant()
	{
		$intMoveToX = $this -> path -> post('moveToX');
		$intMoveToY = $this -> path -> post('moveToY');
		$intUnitID  = $this -> path -> post('unitID');
		if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToX)) $this -> error -> printError(comp_map21, 'mapa');
        if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToY)) $this -> error -> printError(comp_map21, 'mapa');
        if ( ! preg_match("/^[\d]{1,11}$/i", $intUnitID)) $this -> error -> printError(comp_map22, 'mapa');
		
		// sprawdzam czy istnieje docely wpunkt na mapie
        $arrCheckMap = $this -> db -> query('SELECT `fieldID`, `fieldType` FROM `mapData` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        if ( ! isset($arrCheckMap['fieldID'])) $this -> error -> printError(comp_map12, 'mapa');
        // sprawdzam, czy pole nie jest wodą, skoczki nie ptoraifą pływać ;)
		if ( $arrCheckMap['fieldType'] == 1 OR $arrCheckMap['fieldType'] == 8 OR $arrCheckMap['fieldType'] == 9 ) $this -> error -> printError('Skoczek choć umie pływać, nie może lądować w wodzie, nie dopłynie do brzegu!', 'mapa');
		$arrCheckMap = null;

        // Sprawdzam czy na wybranym polu nic nie stoi
        $arrCheckUnit = $this -> db -> query('SELECT `id` FROM `units` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        if (isset($arrCheckUnit['id'])) $this -> error -> printError(comp_map14, 'mapa');
        $arrCheckUnit = null;
		
		
		$up1 = 0;
		$up2 = 0;
		$licznik1 = 0;
		$licznik2 = 0;
		$liczniki = $this -> db -> query('SELECT `timing1`, `timing2`, `idLC`, `x`, `y`, `onMap` FROM `units` WHERE `id`= '.$intUnitID.' AND `worldID`='. $this -> world -> id .'  LIMIT 1') -> fetch();
		if( $this -> world -> timing == 1 ){// jeśli świat poligon ( na razie tylko tam są zegarki )
			// sprawdzam, czy licnziki sie wyzerowały
				
			if( $liczniki['timing1'] > $this -> init -> time ){
				if( $liczniki['timing2'] > $this -> init -> time ){
					$this -> error -> printError(comp_battleE2, 'mapa');
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
		$this -> unit -> getMovedUnitData(
            array(
                'unitID' => $intUnitID, // ID jednostki
                'moveToX' => $intMoveToX, // X docelowy
                'moveToY' => $intMoveToY,  // Y docelowy
                'fromCity' => false,  // jednostka wystawiana z miasta
                'moveFromX' => $liczniki['x'], // Koordynaty miasta
                'moveFromY' => $liczniki['y'], // Koordynaty miasta
                'rounds' => 1,
				'licznik1' => $licznik1,// timing do pierwszego zegarka
				'licznik2' => $licznik2,// timing do drugiego zegarka
				'desant' => 1
            )
        );
        // Ruch się udał, pobieram dane z zegarków 
		$updateLiczniki = ',`unitTurn`=(`unitTurn`-1)';
		//zapisujemy dane jednostki
        if($up1==1){
			$updateLiczniki = ',`timing1`='.$licznik1;
		}
		if($up2==1){
			$updateLiczniki = ',`timing2`='.$licznik2;
		}
		
		$idLC = '';
		if( $liczniki['idLC'] != $intUnitID ){// nastąpił wyładunek jednostki z barki 
			$idLC = ',`idLC` = 0';
		}
		
		$onMap = $liczniki['onMap'];// ta zmienna odpowiada za to czy jednostka jest widoczna na mapie czy nie
		if( $liczniki['x'] == 0 AND $liczniki['y'] == 0 ){
			$onMap = 1;
		}
		
		
		$this -> db -> exec('UPDATE `units` SET `onMap` = '. $onMap .' , `x`=' . $intMoveToX . ', `y`=' . $intMoveToY . ' '.$updateLiczniki.' '. $idLC .' WHERE `id`=' . $intUnitID . ' LIMIT 1');
        $arrCityData = null;
	}
	
	
	
	
	
	
    /*
     * Metoda przesuwa jednostki
     */
    public function moveUnit()
    {
        $intMoveToX = $this -> path -> post('moveToX');
        $intMoveToY = $this -> path -> post('moveToY');
        $intUnitID = $this -> path -> post('unitID');
        $intTury = $this -> path -> post('tury');
		
        if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToX)) $this -> error -> printError('Niepoprawne koordynaty.', 'mapa');
        if ( ! preg_match("/^-?[\d]{1,11}$/i", $intMoveToY)) $this -> error -> printError('Niepoprawne koordynaty.', 'mapa');
        if ( ! preg_match("/^[\d]{1,11}$/i", $intUnitID)) $this -> error -> printError('Niepoprawne dane jednostki.', 'mapa');
        if ($intTury < 1 || $intTury > 5) $this -> error -> printError('Niepoprawny zasięg tur.', 'mapa');
		
        // sprawdzam czy istnieje docely wpunkt na mapie
        $arrCheckMap = $this -> db -> query('SELECT `fieldID` FROM `mapData` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='. $this -> world -> id .' LIMIT 1') -> fetch();
        
		if ( ! isset($arrCheckMap['fieldID'])) $this -> error -> printError(comp_map12, 'mapa');
        $arrCheckMap = null;

        // Sprawdzam czy na wybranym polu nic nie stoi
        $arrCheckUnit = $this -> db -> query('SELECT `id` FROM `units` WHERE `x`=' . $intMoveToX . ' AND `y`=' . $intMoveToY . ' AND `worldID`='. $this -> world -> id .'  LIMIT 1') -> fetch();
        if (isset($arrCheckUnit['id'])) $this -> error -> printError(comp_map14, 'mapa');
        $arrCheckUnit = null;
		//pobieram czasy zegarków ze swiata
		$up1 = 0;
		$up2 = 0;
		$licznik1 = 0;
		$licznik2 = 0;
		$updateLiczniki = ',`timing1`=0,`timing2`=0';
		
		if( $this -> world -> timing == 1 ){// jeśli świat poligon ( na razie tylko tam są zegarki )
			// sprawdzam, czy licnziki sie wyzerowały
			$liczniki = $this -> db -> query('SELECT `timing1`, `timing2` FROM `units` WHERE `id`= '.$intUnitID.' AND `worldID`='. $this -> world -> id .'  LIMIT 1') -> fetch();
			if( $liczniki['timing1'] > $this -> init -> time ){
				if( $liczniki['timing2'] > $this -> init -> time ){
					$this -> error -> printError(comp_battleE2, 'mapa');
					$licznik1 = $liczniki['timing1'];
					$licznik2 = $liczniki['timing2'];
				}else{
					$licznik2 = $this -> init -> time + $this -> world -> timeToUp;
					$up2 = 1;
				}
				$licznik1 = $liczniki['timing1'];
			}else{
				$licznik1 = $this -> init -> time + $this -> world -> timeToUp;
				if( $liczniki['timing2'] > $this -> init -> time ){
					$licznik2 = $liczniki['timing2'];
				}else{
					$licznik2 = $this -> init -> time + $this -> world -> timeToUp;
					$up2 = 1;
				}
				$up1 = 1;
				//$this -> error -> printError('można działać.'.$this -> init -> time.'', 'mapa');
			}
			 // Ruch się udał, pobieram dane z zegarków 
		
			//zapisujemy dane jednostki
			if( ($up1 + $up2 ) < $intTury ){
				$this -> error -> printError( comp_map24, 'mapa');
			}
			if( $intTury == 1 AND $up1 == 1 ){
				$updateLiczniki = ',`timing1`='.$licznik1;
				$licznik1 = $this -> init -> time + $this -> world -> timeToUp;
				$licznik2 = $liczniki['timing2'];
			}
			if( $intTury == 1 AND $up1 == 0 AND  $up2 == 1){
				$updateLiczniki = ',`timing2`='.$licznik2;
				$licznik2 = $this -> init -> time + $this -> world -> timeToUp;
				$licznik1 = $liczniki['timing1'];
			}
			if( $intTury == 2){
				$updateLiczniki = ',`timing1`='.$licznik1.',`timing2`='.$licznik2;
				$licznik2 = $this -> init -> time + $this -> world -> timeToUp;
				$licznik1 = $this -> init -> time + $this -> world -> timeToUp;
			}
		}
		
        //funkcja aktualizująca dane
        $this -> unit -> getMovedUnitData(
            array(
                'unitID' => $intUnitID,
                'moveToX' => $intMoveToX,
                'moveToY' => $intMoveToY,
                'fromCity' => false, // ruch jednostki nie z miasta
                'moveFromX' => 0, // te dane zostaną zaktualizowane wewnątrz funkcji
                'moveFromY' => 0, // te dane zostaną zaktualizowane wewnątrz funkcji
                'rounds' => $intTury, // Liczba tur ruchu
				'licznik1' => $licznik1,// timing do pierwszego zegarka
				'licznik2' => $licznik2,// timing do drugiego zegarka
				'desant' => 0
            )
        );
		$this -> db -> exec('UPDATE `units` SET `x`=' . $intMoveToX . ', `y`=' . $intMoveToY . ', `unitTurn`=(`unitTurn`-'.$intTury.') '.$updateLiczniki.' WHERE `id`=' . $intUnitID . ' LIMIT 1');
    }
}

?>