<?php

/*
 * Klasa obsługująca zdarzenia na koncie gracza
 */

class account
{

    // KLAS
    protected $init;
    protected $db;
    protected $session;
    // Acc
    public $id;
    public $login;
    public $email;
    public $password;
    public $active;
    public $logins;
    public $lpv;
    public $lang;
    public $rank;
    public $avatar;
    public $sounds;
    public $opticalAlert;
    public $reports;
    public $SponsorAccount;
    public $gold;
    public $PremiumCurrency;
	public $descriptionProfil;
    public $experience;
    public $globalPoints;
	public $tutorialStage;
    public $ban;
	public $banEnd;
	public $banWorld;
	public $reason;
	public $cityNum;
	public $cityName;
	public $cityX;
	public $cityY;
	
	
    // Player
    public $playerID;
    public $rankLevel;
    public $nation;
    public $softCurrency;
	public $timeK;
	public $timeP;
	public $rawMaterials;
	public $materialTechnology;
	public $upSur;
    public $options = array();
    
    // Ranga
    public $stopien;
    public $stopien_odmiana;
    public $poziom;
    public $pion;
    public $min_punkty;
    public $zold;

    function __construct($init, $db, $session)
    {
        $this -> init = $init;
        $this -> db = $db -> db;
        $this -> session = $session;

        return $this;
    }

    public function getData($accID = 0)
	{
		if ($accID == 0 && isset($_SESSION['user_hide_id'])) $accID = $_SESSION['user_hide_id']; // ID konta ustawiane w session_class.php

		$stats = $this->db->query('SELECT `a`.*, `p`.`id` AS `playerID`,`p`.`timeK`, `p`.`nation`, `p`.`rawMaterials`, `u`.`id` as `cityNum`,`u`.`CityName`, `u`.`x`, `u`.`y`, `p`.`points`, `p`.`softCurrency`, `p`.`playerVacation`,`p`.`VacationNum`, `p`.`rankLevel`, `p`.`options`, `p`.`testedUnits`, `r`.`stopien`, `r`.`stopien_odmiana`, `r`.`poziom`, `r`.`pion`, `r`.`min_punkty`, `r`.`zold`,`b`.`banType`, `b`.`banWorld`,`b`.`banEnd`,`b`.`reason`, `a`.`getFinance` as `getFinance` FROM `accounts` AS `a` '
			. 'LEFT JOIN `players` AS `p` ON (`p`.`accountID`=`a`.`id` AND `p`.`worldID`=' . $this->session->worldID . ') '
			. 'LEFT JOIN `units` AS `u` ON (`u`.`playerID`=`p`.`id` AND `u`.`unitType`=7 ) '
			. 'LEFT JOIN `ranks` AS `r` ON (`r`.`nacja`=`p`.`nation` AND `r`.`numer_stopnia`=`p`.`rankLevel`) '
			. 'LEFT JOIN `ban` AS `b` ON (`b`.`accountID`=`p`.`accountID`) '
			. 'WHERE `a`.`id`=' . $accID . ' LIMIT 1')->fetch();

		if (!isset($stats['id'])) exit('Nie ma takiego konta.');

		// Account - dane wspólne dla wszystkich światów
		$this->id = $stats['id'];
		$this->login = $stats['login'];
		$this->email = $stats['email'];
		$this->password = $stats['password'];
		$this->active = $stats['active'];
		$this->logins = $stats['logins'];
		$this->lpv = $stats['lpv'];
		$this->lang = $stats['lang'];
		$this->rank = $stats['rank'];
		$this->avatar = $stats['avatar'];
        $this->mini = $stats['mini'];
		$this->sounds = $stats['sounds'];
		$this->opticalAlert = $stats['opticalAlert'];
		$this->reports = $stats['reports'];
		$this->SponsorAccount = $stats['SponsorAccount'];
		$this->SponsorAccountDate = date("Y-m-d H:i:s", $stats['SponsorAccount']);
		$this->gold = $stats['gold'];
		$this->PremiumCurrency = $stats['PremiumCurrency'];
		$this->globalPoints = $stats['globalPoints'];
		$this->playerVacation = $stats['playerVacation'];
		$this->VacationNum = $stats['VacationNum'];
		$this->testedSponsorAccount = $stats['testedSponsorAccount'];
		$this->tutorialStage = $stats['tutorialStage'];
		$this->points = $stats['points'];
		$this->descriptionProfil = preg_replace("/[\r\n]*/", "", $stats['description']);
		$this->testedUnits = $stats['testedUnits'];
		$this->cityNum = $stats['cityNum'];
		$this->cityX = $stats['x'];
		$this->cityY = $stats['y'];
		
		$this->cityName = $stats['CityName'];
        $this->getFinance = $stats['getFinance'];
		//dane dtyczące bana

		$this->ban = $stats['banType'];
		$this->banEnd = $stats['banEnd'];
		$this->banWorld = $stats['banWorld'];
		$this->reason = $stats['reason'];

		// Player - dane jednego świata
		$this->playerID = $stats['playerID'];
		$this->rankLevel = $stats['rankLevel'];
		$this->nation = $stats['nation'];
		$this->softCurrency = $stats['softCurrency'];
		$this->timeK = $stats['timeK'];
		$this->timeP = 0;
		$this->rawMaterials = $stats['rawMaterials'];
		
		
		$promotion = $this -> db -> query('SELECT `endTime` FROM `listPromotion` WHERE `startTime`<='. $this -> init -> time .' AND `endTime`>'. $this -> init -> time .' LIMIT 1 ') -> fetch();
		if($promotion['endTime']){
			$this->timeP = $promotion['endTime'];
		}
		// Opcje
		if ($stats['options']) {
			$this->options = unserialize(trim(base64_decode($stats['options'])));
		}

		// Dodatki
		if ($this->nation == 1) $this->experience = $stats['experience_pl'];
		elseif ($this->nation == 2) $this->experience = $stats['axperience_niem'];

		// Ranga informacje
		$this->stopien = $stats['stopien'];
		$this->stopien_odmiana = $stats['stopien_odmiana'];
		$this->poziom = $stats['poziom'];
		$this->pion = $stats['pion'];
		$this->min_punkty = $stats['min_punkty'];
		$this->zold = $stats['zold'];

		if ($this->playerID) {
			$this->db->exec("SET @id=" . $this->playerID . ",@nr=0, @idnr=0");
			$arrRankData = $this->db->query("SELECT @idnr AS nr FROM (SELECT @nr:=@nr +1, IF( ID=@id, @idnr:=@nr ,@idnr=@idnr), ID FROM `players` WHERE worldID=". $this->session->worldID ." ORDER BY points DESC) podsel WHERE podsel.ID = @id")->fetch();
			$this->worldTop = $arrRankData['nr'];
			
			$this->db->exec("SET @ids=" . $this->id . ",@nrs=0, @idnrs=0");
			$arrGlobalRankData = $this->db->query("SELECT @idnrs AS nrs FROM (SELECT @nrs:=@nrs +1, IF( ID=@ids, @idnrs:=@nrs ,@idnrs=@idnrs), ID FROM `accounts` WHERE `id`!=36 AND `id`!=37 ORDER BY globalPoints DESC) podsel WHERE podsel.ID = @ids")->fetch();
			$this->globalTop = $arrGlobalRankData['nrs'];
		}
        $stats = null;

        return $this;
    }

    public function CreateAccount($arrRegData)
    {
        $b = $this -> db -> prepare("INSERT INTO `accounts` (`login`, `email`, `password`, `lang`,`refID`, `newsletter` ) VALUES (:login,:email,:password,:lang,:refID,:newsletter ) ");
        $b -> bindParam(":email", $arrRegData['email']);
        $b -> bindParam(":password", $arrRegData['password']);
        $b -> bindParam(":login", $arrRegData['login']);
		$b -> bindParam(":refID", $arrRegData['refID']);
		$b -> bindParam(":newsletter", $arrRegData['newsletter']);
		$b -> bindParam(":lang", $this -> init -> settings['game']['language']);
        $b -> execute();

        return $this -> db -> lastInsertId();
    }
    
    /*
     * Zapisuję ustawienia gracza
     */
    public function saveSettings($arrSettings)
    {
        foreach ($arrSettings as $key => $row)
        {
            $this -> options[$key] = $row;
        }
        $this -> db -> exec('UPDATE `players` SET `options`=\''.base64_encode(serialize($this -> options)).'\' WHERE `id`='.$this -> playerID.' LIMIT 1');
    }
    /*
     * Pobiera ustawienia
     */
    public function getSetting($strName)
    {
        if (isset($this -> options[$strName])) return $this -> options[$strName];
        else return false;
    }
    
    /*
     * Sprawdzamy czy gracza mają jakieś ograniczenia na koncie
     */
    public function checkLimit($strLimitType)
    {
        $checkLimit = $this -> db -> query('SELECT `limitValue` FROM `limits` WHERE `accountID`=' . $this -> id . ' AND `limitType`=\'' . $strLimitType . '\' LIMIT 1') -> fetch();
        if (isset($checkLimit['bonusValue'])) $retBonus = $checkLimit['limitValue'];
        else $retBonus = false;
        $checkLimit = null;
    }
	
	public function checkRank($user_id,$punkty,$nacja_gracza)
	{
		$rows_rev = $this -> db -> query('SELECT `numer_stopnia`, `min_punkty`, `zold`, `stopien` FROM `ranks` WHERE `min_punkty`<='.$punkty.' AND `nacja`='.$nacja_gracza.' ORDER BY `numer_stopnia` DESC LIMIT 1')->fetch();
		$rev_numer_stopnia_bd   = $rows_rev['numer_stopnia'];
		$rev_nim_punkty_bd      = $rows_rev['min_punkty'];
		$rev_zold_bd            = $rows_rev['zold'];
		$rev_stopien_odmiana_bd = $rows_rev['stopien'];
		
		$rows = $this -> db -> query('SELECT `ranks`.`numer_stopnia`,`ranks`.`min_punkty`, `ranks`.`zold`  FROM `players` JOIN `ranks` WHERE (`players`.`rankLevel`=`ranks`.`numer_stopnia` AND `players`.`nation`=`ranks`.`nacja`) AND `players`.`id`='.$user_id.' ')->fetch();
		$numer_stopnia          = $rows['numer_stopnia'];
		$min_punkty             = $rows['min_punkty'];
		$zold                   = $rows['zold'];
		
		$rows_next = $this -> db -> query('SELECT `numer_stopnia`, `min_punkty`, `zold`, `stopien` FROM `ranks` WHERE `min_punkty`<='.$punkty.' AND `nacja`='.$nacja_gracza.' ORDER BY `numer_stopnia` DESC LIMIT 1')->fetch();
		$next_numer_stopnia_bd   = $rows_next['numer_stopnia'];
		$next_nim_punkty_bd      = $rows_next['min_punkty'];
		$next_zold_bd            = $rows_next['zold'];
		$next_stopien_odmiana_bd = $rows_next['stopien'];
			
		$punkty_do_stopnia_next=round($next_nim_punkty_bd-$punkty,2);
		$awans = incl_game_acc35;			
		$opcja = 0;
		if($punkty>=129200){
			//najwyższy stopień
			$opcja = 5;
			if($nacja_gracza==1){
				$info=max_stopien_pl;
			}else{
				$info=max_stopien_niem;
			}
		}else if( $punkty >= $next_nim_punkty_bd AND $numer_stopnia < $next_numer_stopnia_bd ){
			//awans
			$info=funkcje24.'<strong>'.$next_stopien_odmiana_bd.'</strong><br>'.funkcje25;
			
		}else if( $numer_stopnia == $next_numer_stopnia_bd  ){
			//bez awansu
			$opcja=2;
			$rows_next = $this -> db -> query('SELECT `numer_stopnia`, `min_punkty`, `zold`, `stopien` FROM `ranks` WHERE `numer_stopnia`= '. ($next_numer_stopnia_bd+1) .' AND `nacja`='.$nacja_gracza.' ORDER BY `numer_stopnia` DESC LIMIT 1')->fetch();
			$next_numer_stopnia_bd   = $rows_next['numer_stopnia'];
			$next_nim_punkty_bd      = $rows_next['min_punkty'];
			$next_zold_bd            = $rows_next['zold'];
			$next_stopien_odmiana_bd = $rows_next['stopien'];
			$punkty_do_stopnia_next=round($next_nim_punkty_bd-$punkty,2);
			$info=funkcje26.'<strong>'.$next_stopien_odmiana_bd.'</strong>'.funkcje27.'<strong>'.$punkty_do_stopnia_next.'</strong>'.panel_gry20;
		}else if( $punkty < $min_punkty AND $punkty > 0 ){
			//degradacja
			$opcja=3;
			$awans = incl_game_acc36;
			$info=funkcje28.'<strong>'.$rev_stopien_odmiana_bd.'</strong>.<br>'.funkcje29;
		}else if( $punkty == 0 ){
			//pierwszy stopień
			$opcja=5;
			$info=funkcje26.'<strong>'.$next_stopien_odmiana_bd.'</strong>.<br>'.funkcje27.'<strong>'.$punkty_do_stopnia_next.'</strong>'.panel_gry20;
		}else if( $punkty < 1 ){
			//degradacja
			$opcja=4;
			$awans = incl_game_acc36;
			$info=funkcje28.'<strong>'.$rev_stopien_odmiana_bd.'</strong>.<br>'.funkcje29;
		}
		// informacja, jaki żołd przypada
		$info .= '<br>'. incl_game_acc32 .' <strong>'. $zold .'</strong> '. incl_game_acc33 .'.<br>';
		if($opcja != 5 ) {
			$info .= incl_game_acc34 .' '. $awans .' '. incl_game_acc37 .' <strong>'. $next_zold_bd .'</strong> '. panel_gry6;
		}
		return $info;
	}
	
	public function buildUnit($unitID, $playerID, $goldUnit)
	{
		
		if($playerID == 0)
        {
			$playerID  = 0;
			$accountID = 0;
            $worldID   = 0;
			$WarMode   = 4;
			
        }
        else
        {
			$accountIDb = $this -> db -> query('SELECT `accountID`, `worldID`,`nation` FROM `players` WHERE `id`='.$playerID.' ')->fetch();
			$accountID = $accountIDb['accountID'];
            $worldID = $accountIDb['worldID'];
			
			
			$accountIDbb = $this -> db -> query('SELECT `WarMode` FROM `worlds` WHERE `id`='.$accountIDb['worldID'].' ')->fetch();
			$WarMode = $accountIDbb['WarMode'];
		}
		
			
		$buildUnits = $this -> db -> query('SELECT * FROM `TacticalData` WHERE `id`='. $unitID .' ')->fetch();
			$rodzaj_korpusu = $buildUnits['CorpsId'];
			$id             = $buildUnits['id'];
			$nacja_bd       = $buildUnits['NationId'];
			$funkcja        = $buildUnits['funkcje'];
			$kategoria      = $buildUnits['kategoria'];
			$cena           = $buildUnits['cena'];
			$specjalnosc    = 0;
			$zanurzenie     = 0;
			$oplot_bd       = 0;
			$funkcja_bd     = 0;
			$sonar          = 0;
			$underWater     = 0;
			$fieldArtillery = 0;
			$torpedo        = 0;
			$antiAircraft   = 0;
			switch($buildUnits['CorpsId']){
				case '1':	//piechota
					$rodzaj_korpusu=1;
					$dzialanie_sztuki=5;
					$do_statystyk='b_piechota';
				break;
				case '2':	//pancerne
					$rodzaj_korpusu=2;
					$dzialanie_sztuki=5;
					$do_statystyk='b_pancerne';
				break;
				case '3': //przecwlotnicze
					$rodzaj_korpusu=3;
					$dzialanie_sztuki=5;
					$do_statystyk='b_przeciwlotnicze';
				break;
				case '4'://lotnictwo	
					$rodzaj_korpusu=4;
					$dzialanie_sztuki=5;
					$do_statystyk='b_lotnictwo_b';
				break;
				case '5'://flota
					$rodzaj_korpusu=5;
					$dzialanie_sztuki=5;
					$do_statystyk='b_flota_n';
				break;
				case '6'://artyleria
					$rodzaj_korpusu=6;
					$dzialanie_sztuki=5;
					$do_statystyk='b_artyleria';
				break;
			}
			if( $id == 38 OR $id == 39 )
			{//pojazd inżynierów
				$specjalnosc = 1;
			}
			
			if($WarMode == 4){
				$dzialanie_sztuki = 2;
			}
			switch( $funkcja )
			{
				case '1'://przeciwlotnicza
				$antiAircraft   = 1;
				$fieldArtillery = 1;
				break;
				case '2'://art polowy
				$fieldArtillery = 1;
				break;
				case '3'://samolot torpedowy
				$torpedo = 1;
				break;
				case '4'://saper
				$specjalnosc = 2;
				break;
				case '5'://okręt podwodny
				$torpedo = 1;
				$underWater = 1;
				$do_statystyk='b_flota_p';
				break;
				case '6'://statek minerski
				$specjalnosc = 4;
				break;
				case '7'://spadochroniarz
				$specjalnosc = 3;  
				$dzialanie_sztuki = 3;
				break;
				case '8'://fortyfikacja lub stanowisko ckm
				$dzialanie_sztuki = 1;
				$specjalnosc = 6;
				break;
				case '10'://mysliwiec
				$do_statystyk = 'b_lotnictwo_m';
				$specjalnosc = 10;
				break;
				case '13'://barka desantowa
				$specjalnosc = 19;
				break;
			}
			if( $rodzaj_korpusu == 5 AND $funkcja == 1 )
			{
                $fieldArtillery = 0;
			}
			if( $id == 113 OR $id == 138 OR $id == 87 OR $id == 88  )
			{//73=sdKfz 10/4;  48=M16 Gun Motor Carriage, 87= Flakpanzer IV Wirbelwind , 88=Flakpanzer IV Ostwind
				$fieldArtillery = 0;
			}
			
			$this -> db -> exec('INSERT INTO `units` (playerID,tacticalDataID,Morale,DefensePoints,UnderWater,FieldArtillery,Torpedo,AntiAircraft,Specialty,unitTurn,unitType, worldID, idLC, onMap ) VALUES ('.$playerID.','.$id.',"100","100",'.$underWater.','.$fieldArtillery.','.$torpedo.','.$antiAircraft.','.$specjalnosc.','.$dzialanie_sztuki.','.$rodzaj_korpusu.','.$worldID.', 0, 0 )');
			$idJednostkiLI = $this -> db -> lastInsertId();
			if( $funkcja == 12 )
			{//gracz buduje sztab
				//dodajemy rekord do tabeli HQData
				$this -> db -> exec('INSERT INTO `HQData` (`unitsID`,`range`,`nameStuff`) VALUES ('.$idJednostkiLI.', "1"," '.sztab_txt.'-'.$idJednostkiLI.'" )');
				$this -> db -> exec('UPDATE `units` SET `belongHQ`='.$idJednostkiLI.',`Specialty`=7 WHERE `id`='.$idJednostkiLI.' ');//dodawanie puntów graczom po wybudowaniu jendostki
			}
			
			$this -> db -> exec('INSERT INTO `constituentUnits` (unitsID,tacticalDataID, connectUnit) VALUES ('.$idJednostkiLI.','.$unitID.','.$idJednostkiLI.' ) ');
			
			if(	$playerID != 0 )
			{
				// dodaję doświadczenie dla gracza za zbudowanie jednostki
				$dodaj_za_zbudowane=round( $cena/100 );
					if( $nacja_bd < 7 )
					{
						$this -> db -> exec('UPDATE `accounts` SET `experience_pl`=(`experience_pl`+'.$dodaj_za_zbudowane.') WHERE `id`='.$accountID.' ');//dodawanie puntów graczom po wybudowaniu jendostki
					}
					else
					{
						$this -> db -> exec('UPDATE `accounts` SET `axperience_niem`=(`axperience_niem`+'.$dodaj_za_zbudowane.') WHERE `id`='.$accountID.' ');//dodawanie puntów graczom po wybudowaniu jendostki
					}
				$this -> db -> exec('UPDATE `players` SET `points`=(`points`+'.$dodaj_za_zbudowane.') WHERE `id`='.$playerID.' ');//dodawanie puntów graczom po wybudowaniu jendostki
				$suma = $this -> db -> query('SELECT count(*) FROM `statsPlayer` WHERE `playerID`=' . $playerID . ' AND `worldID`='. $accountIDb['worldID'].' ')->fetch();
				if( (int)$suma[0] > 0 ){
					$this -> db -> exec('UPDATE `statsPlayer` SET '.$do_statystyk.'=(`'.$do_statystyk.'`+1) WHERE `playerID`='.$playerID.' ');
				}else{
					$this -> db -> query('INSERT INTO `statsPlayer` ('.$do_statystyk.',`playerID`,`worldID`,`nation`,`accountID`) values (1,'.$playerID.','. $accountIDb['worldID'].','.$accountIDb['nation'].','.$accountID.')  ');
				}
			}
			else
			{
				//dodaję doswiadczenie dla danej jendostki, wyciągam losowo doswiadczenie jednostki
				$unitsExperience = $this -> db -> query('SELECT `unitExperience` FROM `units` ORDER BY RAND() LIMIT 1')->fetch();
				$this -> db -> exec('UPDATE `units` SET `unitExperience`='.$unitsExperience['unitExperience'].' WHERE `id`='.$idJednostkiLI.' ');//dodawanie losowego doświadczenia po wybudowaniu jendostki
			}
            if( $goldUnit == 1 )
            {
                $this -> db -> exec('INSERT INTO `GoldUnits` ( `playerID`,`unitID` ) values (' . $playerID . ',' . $idJednostkiLI . ' ) ');
            }
	}
	
	public function checkPromotion( $playerID, $worldID )
	{
		$arr = [];
		$promotion = $this -> db -> query('SELECT * FROM `listPromotion` WHERE `startTime`<='. $this -> init -> time .' AND `endTime`>'. $this -> init -> time .' LIMIT 1 ') -> fetch();
		$arr['prom'] = (int)$promotion['id'];
		// sprawdzamy, czy gracz ma miasto na morzu	
		if( $promotion['drawCorps'] == 5){ // świeto morskie, sprwdzamc zy gracz ma miasto na nabrzeżu
			$podlozeMiasta = $this->db->query('SELECT `mapData`.`fieldType` from `mapData`
			LEFT JOIN `units` ON `mapData`.`x`=`units`.`x` AND `mapData`.`y`=`units`.`y`
			WHERE `units`.`playerID`=' . $playerID . ' AND `units`.`unitType`=7 AND `mapData`.`worldID`= ' . $worldID . ' LIMIT 1')->fetch();
			if ($podlozeMiasta['fieldType'] != 11) {
				$arr['prom'] = 0;
			}
		}
		
		
		if($arr['prom'] > 0 ){//jeśli jets jakaś promocja, sprawdzam, czy grcz już w niej uczestniczył
			$promotion1 = $this -> db -> query('SELECT etap FROM `playerPromotion` WHERE `promotionID`='. $arr['prom'] .' AND `playerID`='.$playerID.' LIMIT 1 ') -> fetch();
			
			if( (int)$promotion1['etap']==2){
				$arr['prom'] = 0;
			}
		}
		return $arr['prom'];
	}
	
	public function dateV($format,$timestamp=null,$lang)
    {
			$to_convert = array(
				'l'=>array('dat'=>'N','str'=>array( incl_game_acc1,incl_game_acc2,incl_game_acc3,incl_game_acc4,incl_game_acc5,incl_game_acc6,incl_game_acc7)),
				'F'=>array('dat'=>'n','str'=>array(funkcje1,funkcje2,funkcje3,funkcje4,funkcje5,funkcje6,funkcje7,funkcje9,funkcje9,funkcje10,funkcje11,funkcje12)),
				'f'=>array('dat'=>'n','str'=>array(incl_game_acc20,incl_game_acc21,incl_game_acc22,incl_game_acc23,incl_game_acc24,incl_game_acc25,incl_game_acc26,incl_game_acc27,incl_game_acc28,incl_game_acc29,incl_game_acc30,incl_game_acc31))
			);
	
		if ($pieces = preg_split('#[:/.\-, ]#', $format)){
			if ($timestamp === null) { $timestamp = time(); }
			foreach ($pieces as $datepart){
				if (array_key_exists($datepart,$to_convert)){
					$replace[] = $to_convert[$datepart]['str'][(date($to_convert[$datepart]['dat'],$timestamp)-1)];
				}else{
					$replace[] = date($datepart,$timestamp);
				}
			}
			$result = strtr($format,array_combine($pieces,$replace));
			return $result;
		}
	}

	public function playerFinance($user_id, $punkty, $nacja_gracza )
	{
		
		$rows_rev = $this -> db -> query('SELECT `numer_stopnia`, `min_punkty`, `zold`, `stopien` FROM `ranks` WHERE `min_punkty`<='.$punkty.' AND `nacja`='.$nacja_gracza.' ORDER BY `numer_stopnia` DESC LIMIT 1')->fetch();
		$rev_numer_stopnia_bd   = $rows_rev['numer_stopnia'];
		$rev_nim_punkty_bd      = $rows_rev['min_punkty'];
		$rev_zold_bd            = $rows_rev['zold'];
		$rev_stopien_odmiana_bd = $rows_rev['stopien'];
		
		$rows = $this -> db -> query('SELECT `ranks`.`numer_stopnia`, `ranks`.`min_punkty`, `ranks`.`zold`  FROM `players` JOIN `ranks` WHERE (`players`.`rankLevel`=`ranks`.`numer_stopnia` AND `players`.`nation`=`ranks`.`nacja`) AND `players`.`id`='.$user_id.' ')->fetch();
		$numer_stopnia          = $rows['numer_stopnia'];
		$min_punkty             = $rows['min_punkty'];
		$zold                   = $rows['zold'];
		
		$rows_next = $this -> db -> query('SELECT `numer_stopnia`, `min_punkty`, `zold`, `stopien` FROM `ranks` WHERE `min_punkty`<='.$punkty.' AND `nacja`='.$nacja_gracza.' ORDER BY `numer_stopnia` DESC LIMIT 1')->fetch();
		$next_numer_stopnia_bd   = $rows_next['numer_stopnia'];
		$next_nim_punkty_bd      = $rows_next['min_punkty'];
		$next_zold_bd            = $rows_next['zold'];
		$next_stopien_odmiana_bd = $rows_next['stopien'];
		
		$cashEngenner = $this -> db -> query('SELECT * FROM `hiringPersonnel` WHERE `playerID`='.$user_id.' ');
		$payment=0;
		$singleEngenner = $this -> db -> query('SELECT * FROM `PlayerPersonnel` WHERE `playerID`='.$user_id.' ')->fetch();
		
		foreach ($cashEngenner as $paymentCash)
		{
			switch($paymentCash['enginner'])
			{
				case '1':
				if($singleEngenner['pole'] == 1){
					$inzynierowie = 6 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '2':
				if($singleEngenner['englishman'] == 1){
					$inzynierowie = 6 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '3':
				if($singleEngenner['russian'] == 1){
					$inzynierowie = 26 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '4':
				if($singleEngenner['american'] == 1){
					$inzynierowie = 10 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '5':
				if($singleEngenner['frenchman'] == 1){
					$inzynierowie = 12 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '6':
				if($singleEngenner['poleSonar'] == 1){
					$inzynierowie = 20 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '7':
				if($singleEngenner['german'] == 1){
					$inzynierowie = 12 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '8':
				if($singleEngenner['italian'] == 1){
					$inzynierowie = 12 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '9':
				if($singleEngenner['japanese'] == 1){
					$inzynierowie = 30 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
				case '10':
				if($singleEngenner['germanSonar'] == 1){
					$inzynierowie = 30 * $paymentCash['HRStage'];
				}else{
					$inzynierowie = 0;
				}
				break;
			}
			$payment += $inzynierowie;
		}
		
		if( $punkty >= $next_nim_punkty_bd AND $numer_stopnia < $next_numer_stopnia_bd )
		{
			//awans
			$opcja=1;
			$this -> db -> exec('UPDATE `players` SET `rankLevel`='.$next_numer_stopnia_bd.' WHERE `id`='.$user_id.' ');
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"1",'.$payment.',"0") ');
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"2",'.$next_zold_bd.',"1") ');
		}
		else if( $punkty >= $min_punkty )
		{
			//bez awansu
			$opcja=2;
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"1",'.$payment.',"0") ');
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"2",'.$zold.',"1") ');
		}
		else if( $punkty < $min_punkty AND $punkty > 0 )
		{
			//degradacja
			$opcja=3;
			$this -> db -> exec('UPDATE `players` SET `rankLevel`='.$rev_numer_stopnia_bd.' WHERE `id`='.$user_id.' ');
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"1",'.$payment.',"0") ');
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"2",'.$rev_zold_bd.',"1") ');
		}
		else if($punkty < 1)
		{
			//degradacja
			$opcja=4;
			$this -> db -> exec('UPDATE `players` SET `rankLevel`=1 WHERE `id`='.$user_id.' ');
			$this -> db -> exec('INSERT INTO `playerFinance` (playerID, operation, cashValue, saldoAction) values ('.$user_id.',"1",'.$payment.',"0") ');
		}
	}
	
	
	public function questionnaire( )
	{
		$user = $this->id;
		return $user;
	}
}
