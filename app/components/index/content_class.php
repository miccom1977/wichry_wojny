<?php

/*
 * KOMPONENT: index
 */

class content extends EveryComponent
{

    protected $init;
    protected $db;
    protected $path;
    protected $twig;

    public function __construct()
    {
        $this->unit = init::getFactory()->getService('unit');
        $this->world = init::getFactory()->getService('world');
    }

    public function initPage()
    {
        $refLink = $this->path->get('ref');
		if($refLink!= 0 )
		{
			// jest zmienna, czyli gość wszedł z linka referencyjnego
			// ustawiamy cookie, aby wykorzystać je do rejestracji
			setcookie("ref", $refLink, time() + (3600 * 30 * 24), "/", "", 0);
		}else{
			setcookie("ref", 0, time() + (3600 * 30 * 24), "/", "", 0);
		}
		
		
		$this->tmplData['file'] = 'index.html';
        $this->tmplData['variables']['title'] = title . ' ' . $this->tmplData['variables']['lang'];
        //załadowanie listy TOP graczy na stronie głównej
        $list = $this->db->query('SELECT * FROM `accounts` WHERE `id`!=36 AND `id`!=37 ORDER BY `globalPoints` DESC LIMIT 0,5');
        $list1 = $this->db->query('SELECT * FROM `accounts` WHERE `id`!=36 AND `id`!=37 ORDER BY `globalPoints` DESC LIMIT 5,5');
		$i = 1;
        foreach ($list as $lp1) {
			if($lp1['SponsorAccount'] > $this -> init ->time AND $lp1['mini'] != ''){
					$avatar = $lp1['mini'];
			}else{
				$avatar = 'avatary/mini/szwejkmini.jpg';
			} 
			$arrayPlayer1[$lp1['id']] = array(
                'num' => $i,
				'id' => $lp1['id'],
                'login' => $lp1['login'],
                'globalPoints' => $lp1['globalPoints'],
                'avatar' => $avatar
            );
			$i++;
        }
        $i2 = 6;
		foreach ($list1 as $lp2) {
            if($lp2['SponsorAccount'] > $this -> init ->time  AND $lp2['mini'] != '' ){
				$avatar2 = $lp2['mini'];
			}else{
				$avatar2 = 'avatary/mini/szwejkmini.jpg';
			} 
			$arrayPlayer2[$lp2['id']] = array(
                'num' => $i2,
				'id' => $lp2['id'],
                'login' => $lp2['login'],
                'globalPoints' => $lp2['globalPoints'],
                'avatar' => $avatar2
            );
			$i2++;
        }
        $this->tmplData['variables']['lp1'] = $arrayPlayer1;
        $this->tmplData['variables']['lp2'] = $arrayPlayer2;
    }

    public function regulamin()
    {
        $this->tmplData['file'] = 'regulamin.html';
        $this->tmplData['variables']['title'] = title_regulamin;
        $this->tmplData['variables']['desc'] = description_regulamin;
        $this->tmplData['variables']['keywords'] = keywords_regulamin;
    }

    public function wersja()
    {
        $this->tmplData['file'] = 'wersja.html';
        $this->tmplData['variables']['title'] = title_zmiany;
        $this->tmplData['variables']['desc'] = description_zmiany;
        $this->tmplData['variables']['keywords'] = keywords_zmiany;
    }

    public function wichry_team()
    {
        $this->tmplData['file'] = 'wichry-team.html';
        $this->tmplData['variables']['desc'] = description_tworcy;
        $this->tmplData['variables']['keywords'] = keywords_tworcy;
    }
	
	public function reklama()
    {
        $this->tmplData['file'] = 'reklama.html';
		$this->tmplData['variables']['title'] = 'Reklamuj się na stronach gry online Wichry Wojny';
        $this->tmplData['variables']['desc'] = 'Jeżeli szukasz taniej powierzchni reklamowej w internecie, napisz do Nas. Reklamuj się na stronach gry online Wichry Wojny!';
        $this->tmplData['variables']['keywords'] = ' reklama na stronach gry online, reklama w grze wichry wojny, reklama online, sprzedam powierzchnię reklamową w grze';
    }

    public function changeLang()
    {
        $strLang = $this->path->get('kod');
        if (strlen($strLang) != 2) $this->error->printError( comp_index1, 'komunikat');
        // sprawdzamy czy grasz jest zalogowany
        if ($this->session->access != 'GUEST') {
            //aktualizujemy zmianę jezyka gracza
            $this->db->exec("UPDATE `accounts` SET `lang`='" . $strLang . "' WHERE `id`=" . $this->account->id . " LIMIT 1");
        } else {
            // użytkownik anonimowyt
            setcookie("lang", $strLang, time() + (3600 * 30 * 24), "/", "", 0);
        }
		if(isset($_SERVER["HTTP_REFERER"])) {
			header("Location:" . str_replace('index.php', '', strip_tags($_SERVER["HTTP_REFERER"])));
        }
		exit;
    }

    public function register()
    {
        $objHelper = init::getFactory()->getService('helper');

        // DANE Z FORMULARZA
        $login = $this->path->post('login');
        $email = $this->path->post('mail');
        $email2 = $this->path->post('mail_repead');
        $password = $this->path->post('haslo');
        $password2 = $this->path->post('repeadhaslo');
        $token = $this->path->post('token');
		
		//$this->error->printError('ref='.$_COOKIE['ref'], 'komunikat');
        /*
        if( $login != 'Vampi' AND $login != 'Wilczek' )
		{
			$this -> error -> printError('Rejestracja wyłączona, planowany start gry: 30 kwietnia 2015r. Jeśli chesz się z Nami skonaktować: pisz na adres team@wichry-wojny.pl lub gadu-gadu: 9988072 ', 'komunikat');
		}
		*/
        if ($token != token) $this->error->printError( comp_index2, 'komunikat');
        //emaile sa jednakowe, maja format maila więc sprawdzam czy podane hasła sa jednakowe
        if ($password !== $password2) $this->error->printError(haslo_wrong_txt, 'komunikat');
        if (strlen($password) < 5) $this->error->printError( comp_index3.'.', 'komunikat');
        $password = $this->session->hashPassword($password); /// haszujemy hasło
        // Sprawdzam poprawność loginu
        if ($objHelper->checkLogin($login) === false) $this->error->printError( comp_index4.'.', 'komunikat');
        if (strlen($login) > 20) $this->error->printError( comp_index5.'.', 'komunikat');
        if (strlen($login) < 3) $this->error->printError( comp_index6.'.', 'komunikat');

        //sprawdzam, czy podane emaile są jednakowe
        if ($email !== $email2) $this->error->printError( comp_index7.'.', 'komunikat');

        //sprawdzam, czy emaile mają format maila
        if ($objHelper->checkEmail($email) === false) $this->error->printError(incorect_email_txt, 'komunikat');

        //hasła sa jednakowe, więc sprawdzam, czy gracz o danym loginie już istnieje
        $arrData = $this->db->query('SELECT count(*) FROM `accounts` WHERE `login`="' . $login . '" LIMIT 1')->fetch();
        if ( (int)$arrData[0] > 0 ) $this->error->printError(player_on_txt, 'komunikat');
        $arrData = null;

        //gracz o podanym loginie nie istnieje, więc sprawdzam czy ktoś rejestrował się na podany email
        $arrData = $this->db->query('SELECT count(*) FROM `accounts` WHERE `email`="' . $email . '" LIMIT 1')->fetch();
        if ( (int)$arrData[0] > 0) $this->error->printError(no_adres_txt, 'komunikat');
        $arrData = null;

        // tworze konto
		if( !$_COOKIE['ref'] ){
			$refID = 0;
		}else{
			$refID = $_COOKIE['ref'];
		}
        $intAccountID = $this->account->CreateAccount(array('email' => $email, 'password' => $password, 'login' => $login, 'refID' => $refID, 'newsletter' => 1 ));

        // Tworze dane aktywacyjne i wysyłam link
        $strActivateID = md5(time());
		
        $strEmail = email_txt1 . ' <a href=' . ADRES . '/aktywuj/' . $strActivateID . '>' . email_txt2 . '</a>.' . email_txt3;
        $this->db->exec('INSERT INTO `sendedLinks` (`ident`, `accountID`, `type`, `date`) VALUES (\'' . $strActivateID . '\', ' . $intAccountID . ', \'REGISTER\', ' . $this->init->time . ')');

        // wysyłam email
        $objHelper->sendEmail($strEmail, subject_txt, $email);

        $this->error->printError(email_txt7, 'komunikat');
    }

    /*
     * Funkcja aktywuje założone konto
     */

    public function activateAcc()
    {
        $objHelper = init::getFactory()->getService('helper');

        $strIdentyficator = $this->path->get('kod');
        if (strlen($strIdentyficator) != 32) $this->error->printError( comp_index8.'.', 'komunikat');
        if ($objHelper->checkLogin($strIdentyficator) === false) $this->error->printError( comp_index8.'.', 'komunikat');

        $arrCheck = $this->db->query('SELECT count(*) FROM `sendedLinks` WHERE `ident`=\'' . $strIdentyficator . '\' LIMIT 1')->fetch();
        if ( (int)$arrCheck[0] == 0 ) $this->error->printError( comp_index9.'.', 'komunikat');
		$arrCheck2 = $this->db->query('SELECT * FROM `sendedLinks` WHERE `ident`=\'' . $strIdentyficator . '\' LIMIT 1')->fetch();
        
        // aktywuję konto
        $this->db->exec('UPDATE `accounts` SET `active`=1 WHERE `id`=' . $arrCheck2['accountID'] . ' LIMIT 1');

        // kasuję link
        $this->db->exec('DELETE FROM `sendedLinks` WHERE `id`=\'' . $arrCheck2['id'] . '\' LIMIT 1');

        $arrCheck = null;
		$arrCheck2 = null;
        $this->error->printError( comp_index10.'.', 'komunikat');
    }
	
	
	public function crashNewsletter()
    {
        $objHelper = init::getFactory()->getService('helper');
        $strIdentyficator = $this->path->get('adres');
        if (strlen($strIdentyficator) != 32) $this->error->printError( comp_index8.'.', 'komunikat');
			if ($objHelper->checkLogin($strIdentyficator) === false) $this->error->printError(comp_index8.'.', 'komunikat');
        $arrCheck = $this->db->query('SELECT count(*) FROM `sendedLinks` WHERE `ident`=\'' . $strIdentyficator . '\' LIMIT 1')->fetch();
			if ( (int)$arrCheck[0] == 0 ) $this->error->printError( comp_index9.'.', 'komunikat');
		$arrCheck2 = $this->db->query('SELECT * FROM `sendedLinks` WHERE `ident`=\'' . $strIdentyficator . '\' LIMIT 1')->fetch();
        //hasła sa jednakowe, więc sprawdzam, czy gracz o danym loginie już istnieje
        $arrData = $this->db->query('SELECT count(*) FROM `accounts` WHERE `id`="' .  $arrCheck2['accountID'] . '" LIMIT 1')->fetch();
			if ( (int)$arrData[0] == 0 ) $this->error->printError( comp_index11.'.', 'komunikat');
        // usuwam newsletter
        $this->db->exec('UPDATE `accounts` SET `newsletter`=0 WHERE `id`="' .$arrCheck2['accountID']. '" LIMIT 1');
        $this->error->printError( comp_index12, 'komunikat');
			$arrData = null;
			$arrCheck = null;
			$arrCheck2 = null;
    }

    //funkcja która logueje gracza
    public function login()
    {
        // DANE Z FORMULARZA
        $login = $this->path->post('username');
        $haslo = $this->path->post('password');

        $initLogin = $this->session->loginPlayer($login, $haslo, 0);

        if ($initLogin !== true) $this->error->printError($initLogin, 'komunikat');
        else $this->error->printError( comp_index13.'.', 'panel');
    }

    public function choseUni()
    {
        $objHelper = init::getFactory()->getService('helper');
        $this->tmplData['file'] = 'panel.html';
        $this->tmplData['variables']['title'] = title . ' ' . $this->tmplData['variables']['title'];

        // Lista światów
        $arrWorlds = array();
        $dzisiaj = $this->init->time;
        $worldStats = $this->db->query('SELECT * FROM `worlds` WHERE `endWar`=0 ORDER BY `id` DESC ');
        foreach ($worldStats as $world) {

            $ilosctur = $world['WarTurn'];
            $rok = 1939 + floor(($ilosctur + 32) / 48);
            $numer_mc = floor(($ilosctur % 48) / 4);
            $miesiac_przesuniety = ($numer_mc + 8) % 12;
			// sprawdzam, czy gracz ma już konto na tym świecie
			$obecnyNaSwiecie = $this->db->query('SELECT count(*) FROM `players` WHERE `accountID`='.$this->account->id.' AND `worldID`='.$world['id'].' ')->fetch();
            
			$arrWorlds[$world['id']] = array(
                'id' => $world['id'],
                'name' => $world['name'],
                'numBattle' => $world['numBattle'],
                'outbreakWar' => $world['outbreakWar'],
                'WarDateY' => $rok,
                'WarDateM' => $objHelper->changeMonth($miesiac_przesuniety + 1),
                'WarTurn' => $world['WarTurn'],
                'WarDifficulty' => $world['WarDifficulty'],
                'WarMode' => $world['WarMode'],
                'hardGame' => $world['hardGame'],//poziom trudności swiata
                'WarWeather' => $world['WarWeather'],
                'endWar' => $world['endWar'],
                'needsPoints' => $world['needsPoints'],//punkty GLOBAL, które musi mieć gracz aby mógł się zalgować na dany świat 
				'timing' => $world['timing'],
				'obecny' => (int)$obecnyNaSwiecie[0],
				'timeToUp' => $world['timeToUp'],
                'pol' => 0,
                'niem' => 0,
                'online' => 0
            );
        }
        $worldStats->closeCursor();

        // Lista graczy w światach
        $arrCountNation = $this->db->query('SELECT COUNT(`nation`) AS `policzone`, `nation`, `worldID` FROM `players` GROUP BY `worldID`, `nation`');
        foreach ($arrCountNation as $row) {
            if (!isset($arrWorlds[$row['worldID']])) continue;
            if ($row['nation'] == 1) $arrWorlds[$row['worldID']]['pol'] = $row['policzone'];
            if ($row['nation'] == 2) $arrWorlds[$row['worldID']]['niem'] = $row['policzone'];
        }
        $arrCountNation->closeCursor();

        // gracze online
        $arrSes = $this->db->query('SELECT COUNT(`id`) AS `online`, `worldID` FROM `sessions` GROUP BY `worldID`');
        foreach ($arrSes as $ses) {
            if (!isset($arrWorlds[$ses['worldID']])) continue;
            $arrWorlds[$ses['worldID']]['online'] = $ses['online'];
        }
        $arrSes = null;

        $this->tmplData['variables']['worlds'] = array_reverse($arrWorlds);
    }

    /*
     * Funkcja loguje gracza na świat
     */

    public function logIntoUni()
    {
        $intWorldID = $this->path->get('wid');
        if (!preg_match("/^[\d]{1,11}$/i", $intWorldID)) $this->error->printError( comp_index14.'.', 'komunikat');
        //pobieram punkty GLOBAL graczy, wyciągam średnią wartosć,aby ustalić, czy można logować się po dwóch stronach czy tylko po jednej

        $arrWorld = $this->db->query('SELECT `w`.*, `p`.`id` AS `hasPlayers` FROM `worlds` AS `w` '
            . 'LEFT JOIN `players` AS `p` ON (`p`.`worldID`=`w`.`id` AND `p`.`accountID`=' . $this->account->id . ') '
            . 'WHERE `w`.`id`=' . $intWorldID . ' LIMIT 1')->fetch();
        if (!isset($arrWorld['id'])) $this->error->printError('Nie ma takiego świata.', 'komunikat');
            $globalGer = $this->db->query('SELECT SUM(`acc`.`globalPoints`) as `sumGer`, count(`p`.`id`) as `playerGER` FROM `accounts` AS `acc` '
                . 'LEFT JOIN `players` AS `p` ON ( `p`.`accountID`=`acc`.`id` ) '
                . 'WHERE `p`.`worldID`=' . $intWorldID . ' AND `p`.`nation`=2 ')->fetch();
            $globalPol = $this->db->query('SELECT SUM(`acc`.`globalPoints`) as `sumPol`, count(`p`.`id`) as `playerPOL` FROM `accounts` AS `acc` '
                . 'LEFT JOIN `players` AS `p` ON ( `p`.`accountID`=`acc`.`id` ) '
                . 'WHERE `p`.`worldID`=' . $intWorldID . ' AND `p`.`nation`=1 ')->fetch();
			
			
			
        if (!$arrWorld['hasPlayers']) {
            //sprawdzam, czy swiat jest nadal aktywny, jesli świat nie ma dwóch stolic gracz nie moze się zarejestrować.
			$capitalRaport = $this->unit->checkCapital(0, $this->path->get('wid'));
			if ($capitalRaport['continueBattle'] == 0) {
				$errorRaport['error'] ='Na tym świecie Wojna jest już zakończona. Wyszukaj inny świat';
				echo json_encode($errorRaport);
				exit();
			}
			echo json_encode(array('inworld' => false, 'hardGame' => $arrWorld['hardGame'], 'playerGER' => (int)$globalGer['playerGER'], 'playerPOL'=> (int)$globalPol['playerPOL'] ));
        } else {
            $this->world->loginCount($arrWorld['hasPlayers']);//zapisuję +1 logowanie na dany świat
            //$this->db->exec('INSERT INTO `fakeData` ( playerIP, przegladarka, login, worldID ) values ("' . $_SERVER["REMOTE_ADDR"] . '","' . $_SERVER["HTTP_USER_AGENT"] . '", "' . $this->account->login . '",' . $intWorldID . ' ) ');// ładujemy dane logowania dla wyszukania multikont
            $this->db->exec('UPDATE `accounts` SET `reminder` = 0, `dateOfRemoval` = 0 WHERE `id`=' . $this->account->id . ' LIMIT 1');
			// sprawdzamy, czy sa jednostki które zakończyły czas budowy

				$errorRaport['error'] = '';
				//if( $arrWorld['timing'] == 1 ){ // budowanie na wszystkich światach uzależnione jest teraz od zegarków
					$addUnitsC = $this->db->query('SELECT count(*) FROM `UnitBuild` WHERE `playerID`=' . $arrWorld['hasPlayers'] . ' AND `TurnBuild` < '. $this->init->time.' ')->fetch();
					if( (int)$addUnitsC[0] > 0 ){
						$addUnits = $this->db->query('SELECT * FROM `UnitBuild` WHERE `playerID`=' . $arrWorld['hasPlayers'] . ' AND `TurnBuild` < '. $this->init->time.' ');

						foreach ( $addUnits as $singleUnit )
						{
							$this -> account -> buildUnit($singleUnit['tacticalDataID'],$singleUnit['playerID'],0);
							$this->db->exec('DELETE FROM `UnitBuild` WHERE `id`=' . $singleUnit['id'] . ' AND `playerID`=' . $singleUnit['playerID'] . ' LIMIT 1');
						}
					
					}
				//}

				
				$addTB = $this->db->query('SELECT *, count(*) as `counted` FROM `buildingsOnBuild` WHERE `playerID`=' . $arrWorld['hasPlayers'] . ' AND `timeToEnd` < '. $this->init->time.' ')->fetch();
				
				if( $addTB['counted'] > 0 ){
					$addSingleBuild = $this->db->query('SELECT count(*) FROM `building` WHERE `playerID`=' . $arrWorld['hasPlayers'] . ' ')->fetch();
					// sprawdzamy cz gracz ma dodany już rekord budowy biura rozwoju
						switch($addTB['buildID'] ){
							case 1:
								$tab = '`developmentOffice`';
							break;
							case 2:
								$tab = '`materialTechnology`';
							break;
							case 3:
								$tab = '`optimizationStorage`';
							break;
							case 4:
								$tab = '`productionOptimization`';
							break;
							case 5:
								$tab = '`materialsScience`';
							break;
						}
						
						if( (int)$addSingleBuild[0] > 0 ){
							$this->db->exec('UPDATE `building` SET '. $tab .' = ( '. $tab .' +1 ) WHERE `playerID`=' . $arrWorld['hasPlayers'] . ' ');
						}else{
							 $this->db->query('INSERT INTO `building` (`playerID`, '. $tab .') values ('. $arrWorld['hasPlayers'] .', 1 )');
						}
						$this->db->exec('DELETE FROM `buildingsOnBuild` WHERE `playerID`=' . $arrWorld['hasPlayers'] . ' ');
				}
				
				$mt = $this -> db -> query('SELECT `materialTechnology`, `optimizationStorage` FROM `building` WHERE `playerID` =  '. $arrWorld['hasPlayers'] .' ')->fetch();
				$pl = $this -> db-> query('SELECT `lastAction`, `rawMaterials` from `players` WHERE `id`=' . $arrWorld['hasPlayers'] . ' ')->fetch();
				$b = (int)$mt['materialTechnology'] * 24;
				$procent = $b/100;
				if( (int)$mt['materialTechnology'] > 0 ){
					$czas = $this -> init ->time - $pl['lastAction'];
					if($czas > 0 ){
						//obliczamy, ile surowca doliczyć za każdą sekundę
						$rawMat = ( 0.1 + ( 0.1 * $procent ) );
						if( round($pl['rawMaterials'] + ( $rawMat*$czas  ) ) < 100000 + ( $mt['optimizationStorage'] * 100000 ) ){
							$back['rawMaterials'] = round($pl['rawMaterials'] + ( $rawMat*$czas  ) );
							$this->tmplData['variables']['rawMaterials'] = $back['rawMaterials'];
							$saveMat = $pl['rawMaterials']+($rawMat*$czas);
						}else{
							$saveMat = 100000;
						}
						$this -> db -> exec('UPDATE `players` SET `rawMaterials`= '. $saveMat .', `lastAction` = '. $this->init->time .'  WHERE `id`='. $arrWorld['hasPlayers'] .' LIMIT 1');
					}
				}
				
            echo json_encode(array('inworld' => true));
            $this->session->changeWorld($arrWorld['id']);

        }
        $arrWorld = null;
    }

    /*
     * Funkcja tworzy konto gracza na świecie i nadaje nację
     */

    public function createUniUser()
    {
        $intWorldID = $this->path->get('wid');
        $intNationID = $this->path->get('nation');
		$objHelper = init::getFactory()->getService('helper');
        $data = [];
		if (!preg_match("/^[\d]{1,11}$/i", $intWorldID)) $this->error->printError( comp_index14.'.', 'panel');
        if (!preg_match("/^[\d]{1,11}$/i", $intNationID)) $this->error->printError( comp_index15.'.', 'panel');
        if ($intNationID != 1 && $intNationID != 2) $this->error->printError( comp_index15.'.', 'panel');
		
        $arrWorld = $this->db->query('SELECT `w`.*, `p`.`id` AS `hasPlayers` FROM `worlds` AS `w` '
            . 'LEFT JOIN `players` AS `p` ON (`p`.`worldID`=`w`.`id` AND `p`.`accountID`=' . $this->account->id . ') '
            . 'WHERE `w`.`id`=' . $intWorldID . ' LIMIT 1')->fetch();
        if (!isset($arrWorld['id'])){
			$data['error'] = comp_index14;
			echo json_encode($data);
			die;
		}
		
		if ($arrWorld['hasPlayers']){
			$data['inworld'] = 1;
			echo json_encode($data);
			die;
		}
		
		$timeK = 0;
		if( $arrWorld['timing'] == 1 ){
			$timeK =  $this -> init -> time + $arrWorld['timeToUp'];
		}
		
		$this->db->exec('INSERT INTO `players` (`accountID`,`worldID`,`nation`,`softCurrency`, `logIntoWorld`, `timeK` ) VALUES (' . $this->account->id . ', ' . $arrWorld['id'] . ', ' . $intNationID . ', "4000", "1", '. $timeK .' )');
		$ntPlayerID = $this->db->lastInsertID();
		$this->session->changeWorld($intWorldID);
        //dodaję domyślne ustawiena tur gracza
        
		
		if( $arrWorld['WarMode'] == 2 ){// świat popołudniowy
            $this->db->exec('INSERT INTO `turnSetting` (`playerID`,`worldID`,`TurnPlayer`,`Turn1`,`Turn2`,`Turn3`,`Turn4`,`TurnUp`) VALUES (' . $ntPlayerID . ',' . $arrWorld['id'] . ',"1","16","18","20","22","0" ) ');
        }else if( $arrWorld['WarMode'] == 4 ){// świat SLOW
			$this->db->exec('INSERT INTO `turnSetting` (`playerID`,`worldID`,`TurnPlayer`,`Turn1`,`Turn2`,`Turn3`,`Turn4`,`TurnUp`) VALUES (' . $ntPlayerID . ',' . $arrWorld['id'] . ',"1","4","10","16","22","0" ) ');
		}else{
            $this->db->exec('INSERT INTO `turnSetting` (`playerID`,`worldID`,`TurnPlayer`,`Turn1`,`Turn2`,`Turn3`,`Turn4`,`TurnUp`) VALUES (' . $ntPlayerID . ',' . $arrWorld['id'] . ',"1","4","10","16","22","0" ) ');
        }
		
         //dodaję inżynerów jakoz atrudnionych
        if ($intNationID == 1) {
            $this->db->exec('INSERT INTO `PlayerPersonnel` (`playerID`,`pole`,`englishman`) VALUES (' . $ntPlayerID . ',"1","1" ) ');
            $this->db->exec('INSERT INTO `hiringPersonnel` ( playerID, HRStage, enginner ) values (' . $ntPlayerID . ',"1","1" ) ');
            $this->db->exec('INSERT INTO `hiringPersonnel` ( playerID, HRStage, enginner ) values (' . $ntPlayerID . ',"1","2" ) ');
        } else {
            $this->db->exec('INSERT INTO `PlayerPersonnel` (`playerID`,`german`,`italian`) VALUES (' . $ntPlayerID . ',"1","1" ) ');
            $this->db->exec('INSERT INTO `hiringPersonnel` ( playerID, HRStage, enginner ) values (' . $ntPlayerID . ',"1","7" ) ');
            $this->db->exec('INSERT INTO `hiringPersonnel` ( playerID, HRStage, enginner ) values (' . $ntPlayerID . ',"1","8" ) ');
        }
        //zapisuję nr bitwyna konto gracza
        $this->db->exec('UPDATE `accounts` SET `game`=(`game`+1) WHERE `id`=' . $this->account->id . ' ');
		$this->db->exec('INSERT INTO `statsPlayer`  (`playerID`,`worldID`,`nation`,`accountID`) values ('.$ntPlayerID.','.$intWorldID.','.$intNationID.','.$this->account->id.')  '); 
        $arrWorld = null;
		$data['inworld'] = 1;
		echo json_encode($data);
    }

    public function logout_user()
    {
        $logoutID = $this->session->logout();
        $this->error->printError( comp_index16, '');
		
    }

    public function loadStuff()
    {
        $dataStuff = [];
        $dataStuff['txt'] = '';
        $stuffCode = '';
        //$stuffList = $this -> db -> query('SELECT * FROM `units` LEFT JOIN WHERE `Specialty`=7 and `playerID`='.$this -> account -> playerID.' ') -> fetch();
        $stuffList = $this->db->query('SELECT `units`.*, `units`.`id` as `uid`, `HQData`.*,`HQData`.`unitsID` as `hid`  FROM `units` JOIN `HQData`
		WHERE ( `units`.`id` = `HQData`.`unitsID`) AND `units`.`playerID`=' . $this->account->playerID . ' ');
        //if ($stuffList)
        //{
        $i = 0;
        $dataStuff['blockTitle'] = '<div id="strzalka">'.hate_bars_no_active.'</div>';
        foreach ($stuffList as $list) {
            $id_sztabu = $list['hid'];
            $nazwa = $list['nameStuff'];
            $at_pancerne = $list['tanks'];
            $at_piechota = $list['infantry'];
            $at_artyleria = $list['artillery'];
            $at_lotnictwo = $list['aircraft'];
            $at_przeciwlotnicze = $list['antiAircraft'];
            $at_podwodne = $list['underWater'];
            $at_artyleria_okretowa = $list['waterArtillery'];
            $range = $list['range'];
            switch ($range) {
                case '1':
                    $name_sztabu = sztaby_sztab_batalionu;
                    break;
                case '2':
                    $name_sztabu = sztaby_sztab_pulku;
                    break;
                case '3':
                    $name_sztabu = sztaby_sztab_brygady;
                    break;
            }
            //sprawdzam, które jednostki są w danym sztabie
            $stuffWaitingList = $this->db->query('SELECT `HQwaiting`.*, `units`.*  FROM `HQwaiting` JOIN `units` WHERE (`HQwaiting`.`unitsID`=`units`.`id`) AND `HQwaiting`.`HQID`=' . $id_sztabu . ' ');
            $stuffActiveList = $this->db->query('SELECT `u`.`id`,`u`.`tacticalDataID`, `u`.`playerID`, `acc`.`login`, `td`.`nazwa`, `u`.`x`, `u`.`y`  FROM `units` AS `u` 
			LEFT JOIN `TacticalData` AS `td` ON `td`.`id` = `u`.`tacticalDataID`
			LEFT JOIN `players` AS `p` ON `p`.`id` = `u`.`playerID` 
			LEFT JOIN `accounts` AS `acc` ON `acc`.`id` = `p`.`accountID`
			WHERE `u`.`belongHQ`= ' . $id_sztabu . ' AND `u`.`id`!=' . $id_sztabu . ' ');
            
            $listWaitingCount = $stuffWaitingList->fetch();
            $sztuki = $stuffActiveList->rowCount();
            $sztukiWaiting = $stuffWaitingList->rowCount();
            $stuffCode = '<div class="sztab_szczegoly">
						<div class="sztab_fin" name="' . $id_sztabu . '"></div>
						<div class="sztab_txt">
								' . $name_sztabu . '
										<input type="text" class="' . $id_sztabu . ' input_sztaby" name="' . $id_sztabu . '" value="' . $nazwa . '"/><br>
										<div class="sztaby_name" name="' . $id_sztabu . '" title="'.comp_index17.'"></div>
										<br><br>' . sztaby_pojemnosc_sztabu . '<strong>' . $range * 10 . '</strong> | ' . sztaby_jednostek . ': ' . $sztuki . '<br><br>';

            if ($sztukiWaiting > 0) {
                $stuffCode .= '<div id="lista_akcesow">'.comp_index18.':<br>';
                foreach ($listWaitingCount as $listWaiting) {
                    $stuffCode .= '<div class="dodanie_css"><strong>'.comp_index19.'</strong> <small>' . sztaby_gracza . ' </small><div id="' . $id_sztabu . '" var="' . $listWaiting['unitsID'] . '" class="sztaby_adm_add" title="' . sztaby_przyjmij . '">' . sztaby_przyjmij . '</div><div id="' . $id_sztabu . '" var="' . $listWaiting['unitsID'] . '" class="sztaby_adm_del" title="' . sztaby_usun . '">' . sztaby_usun . '</div></div>';
                }
                $stuffCode .= '</div>';
            }
            if ($sztuki > 0) {
                $stuffCode .= '<div class="jednostki-dodane">
													' . sztaby_jednostki_pod_dowodzeniem . '
													<table>
														<tr>';
                $i = 1;
                foreach ( $stuffActiveList as $listActive) {

                    $stuffCode .= '<td><div class="jednostka_fin unit-' . $listActive['tacticalDataID'] . ' goToCoords" x="'. $listActive['x'] .'" y="'. $listActive['y'] .'" title="' . $listActive['nazwa'] . ' ( gracza ' . $listActive['login'] . ')" name="id_jedn_code"></div><div var="' . $listActive['id'] . '" class="sztaby_adm" title="' . sztaby_usun . '">usuń</div></td>';
                    $i++;
                    if ($i % 6 == 0) {
                        $stuffCode .= '</tr><tr>';
                        $i = 0;
                    }
                }
                $stuffCode .= '</tr>
										</table>
										</div>';
            }
            $stuffCode .= '<table class="tabela_sztabowa">
										<tr class="naglowek_tabela_jednostki" >
											<td class="right_border"><strong>' . sztaby_korpus . '</strong></td>
											<td><strong>' . sztaby_bonus . '</strong></td>
										</tr>	
										<tr class="bottom_border">
											<td class="right_border">' . sztaby_artillery . '</td>
											<td>' . $at_artyleria . '</td>
										</tr>
										<tr class="bottom_border">
											<td class="right_border">' . sztaby_infantry . '</td>
											<td>' . $at_piechota . '</td>
										</tr>
										<tr class="bottom_border">
											<td class="right_border">' . sztaby_tanks . '</td>
											<td>' . $at_pancerne . '</td>
										</tr>
										<tr class="bottom_border">
											<td class="right_border">' . sztaby_anti_aircraft . '</td>
											<td>' . $at_przeciwlotnicze . '</td>
										</tr>
										<tr class="bottom_border">
											<td class="right_border">' . sztaby_aircraft . '</td>
											<td>' . $at_lotnictwo . '</td>
										</tr>
										<tr style="height:30px;">
											<td class="right_border">' . sztaby_warships . '<div class="podwodne_info">'.comp_index20.'</div><div class="artyleria_okretowa">'.comp_index21.'</div></td>
											<td>' . $at_podwodne . '<br>' . $at_artyleria_okretowa . '</td>
										</tr>
									</table>
						</div>			
					</div>';
            $i++;
            $dataStuff['txt'] .= $stuffCode;
        }

        if ($i == 0) {
            $dataStuff['blockTitle'] = '<div id="strzalka">'.hate_bars_no_active.'</div>';
            $dataStuff['txt'] = sztaby_bez_sztabow;
        }
        echo json_encode($dataStuff);
    }

    public function loadFactory()
    {
        $FactoryList = [];
        $dataFactory = [];
        $dataFactory['bodyTable'] = '';
        $intKorpusID = $this->path->get('korpus');
        if (!preg_match("/^[\d]{1,11}$/i", $intKorpusID)) $this->error->printError( comp_index22, 'mapa');
        $capitalRaport = $this->unit->checkCapital(0, $this->world->id);
		
		if ($capitalRaport['continueBattle'] == 0) {
            $dataFactory['error'] = comp_index23.'.';
            echo json_encode($dataFactory);
            exit();
        } elseif( !$this -> account -> cityNum ){
			$dataFactory['error'] = 'Aby budować jednostki musisz mieć wybudowane miasto.<br> Wybuduj miasto i dopiero wtedy przejdź do zakładki FABRYKI';
            echo json_encode($dataFactory);
            exit();
		}else{

            if ($this->account->nation == 1) {
                $personnel = '`pole`, `englishman`, `russian`, `frenchman`, `american`, `poleSonar`';
            } else {
                $personnel = '`german`, `italian`, `japanese`, `germanSonar`';
            }
            $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' ')->fetch();
            if ($this->account->nation == 1) {
                $warunek_nacja = "(`NationID`=1 ";
                if ($statsUnit['englishman'] == 1) {
                    $warunek_nacja .= " OR `NationID`=2 ";
                }
                if ($statsUnit['russian'] == 1) {
                    $warunek_nacja .= "OR `NationID`=3 ";
                }
                if ($statsUnit['frenchman'] == 1) {
                    $warunek_nacja .= "OR `NationID`=5 ";
                }
                if ($statsUnit['american'] == 1) {
                    $warunek_nacja .= "OR `NationID`=4 ";
                }
            } else {
                $warunek_nacja = "(`NationID`=7 ";
                if ($statsUnit['italian'] == 1) {
                    $warunek_nacja .= "OR `NationID`=8 ";
                }
                if ($statsUnit['japanese'] == 1) {
                    $warunek_nacja .= "OR `NationID`=9 ";
                }
            }
            $warunek_nacja .= ")";
            if (($this->account->experience >= 0 AND $this->account->experience < 200) OR $this->account->globalPoints <= 20) {
                $tier_dozwolony = 1;
                $roznica = round(200 - $this->account->experience, 2);
                $roznicaGlobal = round(20 - $this->account->globalPoints, 2);
                $next_tier = head1 . '' . $roznica . ' ' . head1a . ' ' . $roznicaGlobal . ' ' . head2;
            }
            if (($this->account->experience >= 200 AND $this->account->experience < 600) OR ($this->account->globalPoints > 20 AND $this->account->globalPoints <= 70)) {
                $tier_dozwolony = 2;
                $roznica = round(600 - $this->account->experience);
                $roznicaGlobal = round(70 - $this->account->globalPoints, 2);
                $next_tier = head1 . '' . $roznica . ' ' . head1a . ' ' . $roznicaGlobal . ' ' . head3;
            }
            if (($this->account->experience >= 600 OR $this->account->globalPoints > 70)) {
                $tier_dozwolony = 3;
                $next_tier = '';
            }

            if ($warunek_nacja == '()') {
                $dataFactory['kadra'] = comp_index24;//'Aby budować jednostki bojowe, najpierw musisz zatrudnić inżyniera';
                $dataFactory['error'] = true;
                echo json_encode($dataFactory);
                exit();
            }
            if ($intKorpusID == 3) {
                $corps = ' ( `u`.`CorpsId`=3 OR `u`.`CorpsId`=6 ) ';
				$corps2 = ' ( `corpsID`=3 OR `corpsID`=6 ) ';
            } else {
                $corps = '`u`.`CorpsId`=' . $intKorpusID . '';
				$corps2 = '`corpsID`=' . $intKorpusID . '';
            }
            $Factory = $this->db->query('SELECT `u`.*, `b`.`TurnBuild` AS `turnsToBuild`,`b`.`corpsID`,`u`.`nazwa' . $this->account->lang . '` as `nazwa`, `b`.`id` as `numID` FROM `TacticalData` AS `u` '
                . 'LEFT JOIN `UnitBuild` AS `b` ON (`b`.`tacticalDataID`=`u`.`id` AND `b`.`playerID`=' . $this->account->playerID . '  ) '
                . ' WHERE ' . $warunek_nacja . ' AND ' . $corps . ' AND `u`.`rocznik`<= ' . $this->world->WarDateY . ' AND `u`.`id`!= 224 AND `u`.`id`!= 225  ORDER BY `u`.`NationId` ASC');
            $errorBuild = '';
			$dataFactory['unitsOnBuild'] = [];
			//if( $this->world-> timing == 1 ){
				// pobieramy liste jednostek do budowy
				$unitsOnBuild =  $this->db->query('SELECT * FROM `UnitBuild` WHERE ' . $corps2 . ' AND `playerID`=' . $this->account->playerID . ' ')->fetch();
				$dataFactory['unitsOnBuild'] = $unitsOnBuild;
			//}
			foreach ($Factory as $nation) {
              
				//if( $this->world-> timing == 1 ){
					if ($nation['turnsToBuild'] > 0) {
						if ($this->account->gold >= round( $nation['cena'] / 15 ) ) {
							$blokPrzyspiesz = '<div class="przyspiesz" title="'.comp_index25.'" num="' . $nation['id'] . '"></div>';
						} else {
							$blokPrzyspiesz = '<div class="bank kupZloto" title=" '. comp_index26 .' ">BRAK ZŁOTA</div>';//dodac div dotyczący zakupu złota aby przyspieszyć jendostki
						}
						$buildID = 0;
						$errorBuild = '<div class="infoBuild"><p id="'.$nation['numID'].'" class="upCl"> licznik ' . $nation['turnsToBuild'] . '</p></div> ' . $blokPrzyspiesz . ' <div id="anuluj" title="'.fabryki_jednostka6.'" num="' . $nation['id'] . '"></div>';
					} else if (isset($nation['turnsToBuild'])) {
							$errorBuild = '<div class="infoBuild"><div id="budujSponsor" name="' . $nation['corpsID'] . '">'.comp_index27.'.</div></div>';
							$buildID = 0;
					} else if ($nation['cena'] <= ($this->account->PremiumCurrency + $this->account->softCurrency)) {
						if ($nation['tier'] <= $tier_dozwolony) {
							$buildID = 1;
						} else {
							$buildID = 0;
							$errorBuild = '<div class="error_build" title="' . fabryki_tier_alert1 . ': ' . $nation['tier'] . ', ' . fabryki_tier_alert . '' . $tier_dozwolony . '"></div>';
						}
					} else {
						$buildID = 0;
						$errorBuild = '<div class="error_build" title="' . fabryki_title_no_cash . '"></div>';
					}
				/*
				}else{
					
					if ($nation['turnsToBuild'] > 0) {
						if ($this->account->gold >= round( $nation['cena'] / 15 ) ) {
							$blokPrzyspiesz = '<div class="przyspiesz" title="'.comp_index25.'" num="' . $nation['id'] . '"></div>';
						} else {
							$blokPrzyspiesz = '<div class="bank kupZloto" title=" '. comp_index26 .' ">BRAK ZŁOTA</div>';//dodac div dotyczący zakupu złota aby przyspieszyć jendostki
						}
						$buildID = 0;
						$errorBuild = '<div class="infoBuild">'.comp_index28.' ' . $nation['turnsToBuild'] . ' tur/y</div> ' . $blokPrzyspiesz . ' <div id="anuluj" title="'.fabryki_jednostka6.'" num="' . $nation['id'] . '"></div>';
					} else if (isset($nation['turnsToBuild'])) {
						if ($this->account->SponsorAccount > $this->init->time) {
							$errorBuild = '<div class="infoBuild"><div id="budujSponsor" name="' . $nation['corpsID'] . '">'.comp_index27.'.</div></div>';
							$buildID = 0;
						} else {
							$errorBuild = '<div class="infoBuild">'.comp_index29.'</div>';
							$buildID = 0;
						}

					} else if ($nation['cena'] <= ($this->account->PremiumCurrency + $this->account->softCurrency)) {
						if ($nation['tier'] <= $tier_dozwolony) {
							$buildID = 1;
						} else {
							$buildID = 0;
							$errorBuild = '<div class="error_build" title="' . fabryki_tier_alert1 . ': ' . $nation['tier'] . ', ' . fabryki_tier_alert . '' . $tier_dozwolony . '"></div>';
						}
					} else {
						$buildID = 0;
						$errorBuild = '<div class="error_build" title="' . fabryki_title_no_cash . '"></div>';
					}
				}
				*/
				
                if ($buildID == 1) {
                    $buduj = '<div class="buduj" title="' . fabryki_buduj . '" num="' . $nation['id'] . '"></div>';
                } else {
                    $buduj = $errorBuild;
                }
				if( $this->world->timing == 1 ){
					$sStart = $nation['czas_produkcji'] * $this->world->timeToUp;
				}else{
					$sStart = $nation['czas_produkcji'] * 21600;
				}
				$s = $sStart;
				$buildingsData = $this->db->query('SELECT `productionOptimization`, `materialsScience` FROM `building` WHERE `playerID`= '. $this->account->playerID .' ')->fetch();
				$productionOptimization = $buildingsData['productionOptimization'];
				$materialsScience = $buildingsData['materialsScience'];
				$b = $c = 0;
				if( $productionOptimization > 0 ){
					$b = $productionOptimization * 6;
					$s = round( $sStart- ( $sStart*( $b/100 ) ) );
				}
				$cost = $nation['cena'];
				if( $materialsScience > 0 ){
					$c = $materialsScience * 6;
					$cost = round( $nation['cena'] - ( $nation['cena'] * ( $c / 100 ) ) );
				}
				
				$min = $s/60;				// minuty
				$h = $min/60;
				$d = $h/24;	// dni
				$sLeft = floor($s%60);	// pozostało sekund		
				$minLeft = floor($min%60);	// pozostało minut
				$hLeft = floor($h%24);
				$dLeft = floor($d);
				
				if( $dLeft == 0 ){
					$dToTmer = '';
				}else{
					$dToTmer = $dLeft.":";
				}
				// pozostało godzin	
				if ($minLeft < 10 )
				  $minLeft = "0".$minLeft;
				if ($sLeft < 10)
				  $sLeft = "0".$sLeft;
				if ($hLeft < 10 AND $dLeft > 0 )
				  $hLeft = "0".$hLeft;
				
				$timeToShow = $dToTmer."".$hLeft.":".$minLeft.":".$sLeft;
				
				

                $tooltiop = "
				<tr class=\"body_table\">
							<td>
				<div class=\"tooltip unit-" . $nation['id'] . "\" num=".$nation['id']." loc=\"1\"></div>
						</td><td class=\"kreska_jednostki\"></td><td>" . $nation['nazwa' . $this->account->lang . ''] . "</td><td class=\"kreska_jednostki\"></td><td>" . $nation['kategoria'] . "</td>
						<td class=\"kreska_jednostki\"></td><td> " . $timeToShow . "</td><td class=\"kreska_jednostki\"></td><td>" . $cost . "kr./" . $nation['tier'] . " poz.</td>
						<td class=\"kreska_jednostki\"></td>
						<td id=\"" . $nation['id'] . "\" class=\"actionTable\">" . $buduj . "</td>
					</tr>";
                $dataFactory['bodyTable'] .= $tooltiop;
            }
            $active1 = $active2 = $active3 = $active4 = $active5 = '';
            if ($intKorpusID == 1) {
                $this->tmplData['variables']['FactoryDescription'] = fabryki_koszary_opis;
                $active1 = 'active';
            } else if ($intKorpusID == 2) {
                $this->tmplData['variables']['FactoryDescription'] = fabryki_czolgi_opis;
                $active2 = 'active';
            } else if ($intKorpusID == 3) {
                $this->tmplData['variables']['FactoryDescription'] = fabryki_dziala_opis;
                $active3 = 'active';
            } else if ($intKorpusID == 4) {
                $this->tmplData['variables']['FactoryDescription'] = fabryki_hangar_opis;
                $active4 = 'active';
            } else if ($intKorpusID == 5) {
                $this->tmplData['variables']['FactoryDescription'] = fabryki_stocznia_opis;
                $active5 = 'active';
                // if ($sonar_niem == 1 OR $sonar_al == 1)
                // {
                //     $this -> tmplData['variables']['FactoryDescription'] = fabryki_stocznia_echo_on;
                // }
                //  else
                //  {
                $this->tmplData['variables']['FactoryDescription'] = fabryki_stocznia_echo_off;
                //  }
            }
            $this->tmplData['variables']['UnitsList'] = $FactoryList;

            $dataFactory['titleTableFactory'] = hate_fabrica_no_active;
            $dataFactory['TableFactory'] = '
			<div id="fabryki_menu">
				<div class="fabryki_lista" num="1939">dane taktyczne</div>
				<div id="fabryki_koszary" class="factory ' . $active1 . '" num="1">' . fabryki_koszary . '</div>
				<div id="fabryki_czolgi" class="factory ' . $active2 . '" num="2">' . fabryki_czolgi . '</div>
				<div id="fabryki_dziala" class="factory ' . $active3 . '" num="3">' . fabryki_dziala . '</div>
				<div id="fabryki_hangar" class="factory ' . $active4 . '" num="4">' . fabryki_hangar . '</div>';
            $podlozeMiasta = $this->db->query('SELECT `mapData`.`fieldType` from `mapData`
		LEFT JOIN `units` ON `mapData`.`x`=`units`.`x` AND `mapData`.`y`=`units`.`y`
		WHERE `units`.`playerID`=' . $this->account->playerID . ' AND `units`.`unitType`=7 AND `mapData`.`worldID`= ' . $this->world->id . ' LIMIT 1')->fetch();
            if ($podlozeMiasta['fieldType'] != 11) {
                $dataFactory['TableFactory'] .= '<div id="fabryki_stocznia" class="block" title="'.comp_index30.'">' . fabryki_stocznia . '</div>';
            } else {
                $dataFactory['TableFactory'] .= '<div id="fabryki_stocznia" class="factory ' . $active5 . '" num="5">' . fabryki_stocznia . '</a></div>';
            }
			$dataFactory['TableFactory'] .= '
				<div id="dropArea">';
			$droppedUnit = $this -> db -> query('SELECT * FROM `listaProdukcji` WHERE `playerID` = '. $this -> account -> playerID .' AND `endBuild` > 0 AND `dCat` = '. $intKorpusID .' ORDER BY `id` ASC ');
			$i = 1;
			foreach( $droppedUnit as $sUnit ){
				if( $i == 6 ){
					break;
				}else{
					
					$dataFactory['TableFactory'] .= '<div id="dz'.$i.'" class="dropzoneInact"><div class="unitList unit-'. $sUnit['tacticalDataID'] .'"  style="float:left"></div></div>';
					$i++;
				}
			}
			if($i == 1 ){
				$dataFactory['TableFactory'] .= '
				<div id="dz1" class="dropzone"></div>
				<div id="dz2" class="dropzone"></div>
				<div id="dz3" class="dropzone"></div>
				<div id="dz4" class="dropzone"></div>
				<div id="dz5" class="dropzone"></div>
				<div id="writeDropArea"  num="'. $intKorpusID .'"> zapisz kolejkę produkcji</div>';
			}
            $dataFactory['TableFactory'] .= '</div></div>';
            $dataFactory['headFactory'] = '';
            $dataFactory['tableDeff'] = '<br>' . $next_tier . '
			<div id="tabela">';
				
				if( $this -> account -> SponsorAccount < $this -> init -> time ){
					$dataFactory['tableDeff'] .= '<div class="reklama500_50">
						<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
						<!-- wichry_fabryka -->
						<ins class="adsbygoogle"
							 style="display:inline-block;width:500px;height:50px"
							 data-ad-client="ca-pub-3574813742955681"
							 data-ad-slot="2415664130"></ins>
						<script>
							 (adsbygoogle = window.adsbygoogle || []).push({});
						</script>
					</div>';
				}
			//'. $this->shuffleBanerF(3) .'
			
			$dataFactory['tableDeff'] .= '
			<table class="table_style">
        <tr class="padding_top">
            <td colspan="11">BONUSY TECHNOLOGII:<br>
			TECHONOLOGIA OPTYMALIZACJA PRODUKCJI: -'.$b.'%<br>
			TECHONOLOGIA MATERIAŁOZNAWSTWO: -'.$c.'%<br>
		</td>
        </tr>
        <tr class="naglowek_tabeli">
            <td class="ikonaJednostki">' . ikona_txt . '</td><td class="kreska_jednostki"></td><td class="nazwaJednostki">' . fabryki_nazwa . '</td><td class="kreska_jednostki"></td><td class="kategoriaJednostki">' . fabryki_kategoria . '</td>
            <td class="kreska_jednostki"></td><td class="produkcjaJednostki">' . fabryki_czas_produkcji . '</td><td class="kreska_jednostki"></td><td class="wymaganiaJednostki">' . fabryki_wymagania . '</td>
            <td class="kreska_jednostki"></td><td class="dzialanieJednostki">' . fabryki_dzialanie . '</td>
        </tr>';
            $dataFactory['tableEnd'] = '
					<tr class="padding_bottom">
						<td colspan="11"></td>
					</tr>
				</table>
			</div>';
            $samouczek = [];
            $samouczek['tutorial'] = $this->account->tutorialStage;
            $samouczek['playerID'] = $this->account->playerID;
	
            $dataFactory['samouczek'] = $samouczek;
            if ($this->account->tutorialStage == 2) {
                $this->db->exec('UPDATE `accounts` SET `tutorialStage`=3 WHERE `id`=' . $this->account->id . ' LIMIT 1');
				$samouczek['tutStage'] = 3;
            }
        }
		
        echo json_encode($dataFactory);
    }

    public function buildUnit()
    {
        if ($this->account->playerVacation > $this->init->time) {
            $this->error->printError( mapa_txt.' ' . date("Y-m-d H:i:s", $this->account->playerVacation) . ' '.comp_index31.' ', 'mapa');
        }
		// sprawdzam czy gracz ma miasto
		$playerCity = $this -> db -> query('SELECT count(*) FROM `units` WHERE `playerID` = '. $this->account->playerID .' AND `unitType` = 7 ')->fetch();
		if( (int)$playerCity[0] == 0 ){
			 $this->error->printError( 'Aby budować jednostki musisz wybudować najpierw miasto', 'mapa');
		}
        $dataBuilder = [];
        $intUnitID = $this->path->get('idUnit');
		
        if (!preg_match("/^[\d]{1,11}$/i", $intUnitID)) $this->error->printError( comp_index32, 'mapa');

        if (($this->account->experience >= 0 AND $this->account->experience < 200) OR $this->account->globalPoints <= 20) {
            $tier_dozwolony = 1;
        }
        if (($this->account->experience >= 200 AND $this->account->experience < 600) OR ($this->account->globalPoints > 20 AND $this->account->globalPoints <= 70)) {
            $tier_dozwolony = 2;
        }
        if (($this->account->experience >= 600 OR $this->account->globalPoints > 70)) {
            $tier_dozwolony = 3;
        }
		
        $statsUnit = $this->db->query('SELECT `cena`,`tier`,`czas_produkcji`,`CorpsId` FROM `TacticalData` WHERE `id`=' . $intUnitID . ' LIMIT 1')->fetch();
		$buildingsData = $this->db->query('SELECT `productionOptimization`, `materialsScience` FROM `building` WHERE `playerID`= '. $this->account->playerID .' ')->fetch();
		$productionOptimization = $buildingsData['productionOptimization'];
		$materialsScience = $buildingsData['materialsScience'];
		$b = $c = 0;
		
		$cost = $statsUnit['cena'];
		if( $materialsScience > 0 ){
			$c = $materialsScience * 6;
			$cost = round( $statsUnit['cena'] - ( $statsUnit['cena'] * ( $c / 100 ) ) );
		}
		
		if( $this -> world ->timing == 1 ){
			$sStart = $statsUnit['czas_produkcji'] * $this->world->timeToUp;
		} else {
			$sStart = $statsUnit['czas_produkcji'] * 21600;
		}
		
		$s = $sStart;// czas produkcji bez bonusów
		if( $productionOptimization > 0 ){
			$b = $productionOptimization * 6;
			$s = round( $sStart- ( $sStart*( $b/100 ) ) );
		}
		
		if ( $cost <= ( $this->account->PremiumCurrency + $this->account->softCurrency )) {
            
			if ($statsUnit['tier'] <= $tier_dozwolony) {
				 
                if ($statsUnit['CorpsId'] == 3 OR $statsUnit['CorpsId'] == 6) {
                   
					$rowsUnit = $this->db->query('SELECT *, count(*) as `countUnit` FROM `UnitBuild` WHERE ( `corpsID`=3 OR `corpsID`=6 ) AND `playerID`=' . $this->account->playerID . ' ')->fetch();
					
				} else {
					 //sprawdzam, czy gracz już buduje jednostkę z tego samego korpusu
                    $rowsUnit = $this->db->query('SELECT *, count(*) as `countUnit` FROM `UnitBuild` WHERE `corpsID`=' . $statsUnit['CorpsId'] . ' AND `playerID`=' . $this->account->playerID . ' ')->fetch();     
				}
				
                if ( $rowsUnit['countUnit'] != 0 ) {
		
					switch ($statsUnit['CorpsId']) {
                        case 1:
                            $nazwa_fabryki = fabryki_koszary;
                            break;
                        case 2:
                            $nazwa_fabryki = fabryki_czolgi;
                            break;
                        case 3:
                            $nazwa_fabryki = fabryki_dziala;
                            break;
                        case 4:
                            $nazwa_fabryki = fabryki_hangar;
                            break;
                        case 5:
                            $nazwa_fabryki = fabryki_stocznia;
                            break;
                        case 6:
                            $nazwa_fabryki = fabryki_dziala;
                            break;
                    }

                    $this->error->printError( comp_index33.' ' . $nazwa_fabryki . '. '.comp_index34.'.', 'mapa');
                    $rowsUnit = null;
                } else {
                    // warunki spełnione, buduemy jednostkę
					$dataBuilder['unitsOnBuild'] = [];
				
					// if( $this -> world ->timing == 1 ){  // dodano licznik zegarowy na wszystkich światach
						
						
						
						
						
						$newTime = $this->init->time + $s;
						$this->db->exec('INSERT INTO `UnitBuild` (`playerID`,`tacticalDataID`,`TurnBuild`,`corpsID`) VALUES (' . $this->account->playerID . ', ' . $intUnitID . ', ' . $newTime . ',' . $statsUnit['CorpsId'] . ')');
						$unitsOnBuild =  $this->db->query('SELECT * FROM `UnitBuild` WHERE `corpsID`='.$statsUnit['CorpsId'].' AND `playerID`=' . $this->account->playerID . ' ')->fetch();
						$dataBuilder['unitsOnBuild'] = $unitsOnBuild;
						$dataBuilder['gold'] = 0;
						$dataBuilder['softCurrency'] = 0;
						$dataBuilder['PremiumCurrency'] = 0;
						
						if ($this->account->softCurrency >= $cost ) {
							$this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-' . $cost . ') WHERE `id`=' . $this->account->playerID . ' ');
							$dataBuilder['softCurrency'] = $cost;
						} else {
							if ($this->account->softCurrency >= 0 AND $this->account->softCurrency <= $cost ) {
								//gracz nie ma kasy soft, więc zerujemy jego wartość
								//i pomniejszamy ilość premiumCurrency
								$new_PremiumCurrency = $cost - $this->account->softCurrency;
								$dataBuilder['softCurrency'] = $cost;
								$this->db->exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this->account->playerID . ' ');
								$this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $new_PremiumCurrency . ' ) WHERE `id`=' . $this->account->id . ' ');
							}
						}
						
						if ($this->account->gold >= $cost / 15) {
							$blokPrzyspiesz = '<div class="przyspiesz" title="'.comp_index25.'" num="' . $intUnitID . '"></div>';
						} else {
							$blokPrzyspiesz = '<div class="bank kupZloto" title=" '. comp_index26 .' ">BRAK ZŁOTA</div>';
						}
						
						$dataBuilder['newDiv'] = '<div class="infoBuild"><p id="'.$unitsOnBuild['id'].'" class="upCl"> licznik ' . $unitsOnBuild['TurnBuild'] . '</p></div> ' . $blokPrzyspiesz . ' <div id="anuluj" title="'.fabryki_jednostka6.'" num="' . $intUnitID . '"></div>';
						$dataBuilder['tutorial'] = false;
						
						if ($this->account->tutorialStage == 3) {
							$samouczek = array();
							$samouczek['tutorial'] = comp_index35.'.<br>';
							$samouczek['playerID'] = $this->account->playerID;
							$dataBuilder['tutorial'] = $samouczek;
							$samouczek['tutStage'] = 4;
							$this->db->exec('UPDATE `accounts` SET `tutorialStage`=4 WHERE `id`=' . $this->account->id . ' LIMIT 1');

						}
					
					/*
					
					   dodano licznik zegarowy na wszystkich światach
					   
					   
					   
					}else{
					
						$this->db->exec('INSERT INTO `UnitBuild` (`playerID`,`tacticalDataID`,`TurnBuild`,`corpsID`) VALUES (' . $this->account->playerID . ', ' . $intUnitID . ', ' . $statsUnit['czas_produkcji'] . ',' . $statsUnit['CorpsId'] . ')');
                     
						//odejmujemy wartość jednostki z puli pieniedzy gracza
						$dataBuilder['gold'] = 0;
						$dataBuilder['softCurrency'] = 0;
						$dataBuilder['PremiumCurrency'] = 0;
						
						if ($this->account->softCurrency >= $statsUnit['cena']) {
							$this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-' . $statsUnit['cena'] . ') WHERE `id`=' . $this->account->playerID . ' ');
							$dataBuilder['softCurrency'] = $statsUnit['cena'];
						} else {
							if ($this->account->softCurrency >= 0 AND $this->account->softCurrency <= $statsUnit['cena']) {
								//gracz nie ma kasy soft, więc zerujemy jego wartość
								//i pomniejszamy ilość premiumCurrency
								$new_PremiumCurrency = $statsUnit['cena'] - $this->account->softCurrency;
								$dataBuilder['softCurrency'] = $statsUnit['cena'];
								$this->db->exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this->account->playerID . ' ');
								$this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $new_PremiumCurrency . ' ) WHERE `id`=' . $this->account->id . ' ');
							}
						}
						
						//$dataBuilder['alert'] = 'jednostka została zlecona do budowy';
						if ($this->account->gold >= round( $statsUnit['cena'] / 15 ) ) {
							$blokPrzyspiesz = '<div class="przyspiesz" title="'.comp_index25.'" num="' . $intUnitID . '"></div>';
						} else {
							$blokPrzyspiesz = '<div class="bank kupZloto" title=" '. comp_index26 .' ">BRAK ZŁOTA</div>';
						}
						$dataBuilder['newDiv'] = '<div class="infoBuild">'.comp_index28.' ' . $statsUnit['czas_produkcji'] . ' tur/y</div> ' . $blokPrzyspiesz . ' <div id="anuluj" title="'.fabryki_jednostka6.'" num="' . $intUnitID . '"></div>';
						$dataBuilder['tutorial'] = false;
						if ($this->account->tutorialStage == 3) {
							$samouczek = array();
							$samouczek['tutorial'] = comp_index35.'.<br>';
							$samouczek['playerID'] = $this->account->playerID;
							$dataBuilder['tutorial'] = $samouczek;
							$samouczek['tutStage'] = 4;
							$this->db->exec('UPDATE `accounts` SET `tutorialStage`=4 WHERE `id`=' . $this->account->id . ' LIMIT 1');

						}
						
					}
					*/
                }
            } else {
                $this->error->printError( comp_index36.' ' . $statsUnit['tier'], 'mapa');
            }
        } else {
            $buildID = 0;
            $dataBuilder['error'] = fabryki_title_no_cash;
        }
		
        $statsUnit = null;
        echo json_encode($dataBuilder);
    }

    public function buildGoldUnit()
    {
        $dataBuilder = [];
        $intUnitID = $this->path->get('idUnit');
        if (!preg_match("/^[\d]{1,11}$/i", $intUnitID)) $this->error->printError( comp_index32, 'fabryki/1');

        if (($this->account->experience >= 0 AND $this->account->experience < 200) OR $this->account->globalPoints <= 20) {
            $tier_dozwolony = 1;
        }
        if (($this->account->experience >= 200 AND $this->account->experience < 600) OR ($this->account->globalPoints > 20 AND $this->account->globalPoints <= 70)) {
            $tier_dozwolony = 2;
        }
        if (($this->account->experience >= 600 OR $this->account->globalPoints > 70)) {
            $tier_dozwolony = 3;
        }
        $statsUnit = $this->db->query('SELECT `cena`,`tier`,`CorpsId`,`nazwa' . $this->account->lang . '` AS `nazwa` FROM `TacticalData` WHERE `id`=' . $intUnitID . ' LIMIT 1')->fetch();
        
		
		if ($statsUnit['cena'] / 15 < $this->account->gold) {
            if ($statsUnit['tier'] <= $tier_dozwolony) {
                //sprawdzam, czy gracz już buduje jednostkę z tego samego korpusu
                $rowsUnit = $this->db->query('SELECT * FROM `UnitBuild` WHERE `corpsID`=' . $statsUnit['CorpsId'] . ' AND `playerID`=' . $this->account->playerID . ' ')->fetch();
     
				if (isset($rowsUnit['id'])) {
                    //spradzam, ile jednostek kupił już gracz, aby bliczyć, ie złota musi zapłacić za natychmiastowe dokoczenie budowy
                    $rowsUnitno = $this->db->query('SELECT count(*) from `fasterUnits` WHERE `corpsID`=' . $statsUnit['CorpsId'] . ' AND `playerID`=' . $this->account->playerID . '')->fetch();
                    
					/*
					$costGoldPrepare = $rowsUnit['TurnBuild'];
					$toPlayerInfo = '( tur: '.$rowsUnit['TurnBuild'];
					*/
					//if( $this -> world ->timing == 1 ){
						
						//$costGoldPrepare = $this->init->time - $this->world->timeToUp;
						$toPlayerInfo = ' ( sekund: '.( round( ( $rowsUnit['TurnBuild'] - $this->init->time) ) );
						$quarter =  round( ( ( $rowsUnit['TurnBuild'] - $this->init->time ) / 900 )/10 );
						
						
					//}
					
					if ( (int)$rowsUnitno[0] > 0) {
                        $cena_gold = round( ($quarter * $statsUnit['CorpsId']) + (int)$rowsUnitno[0] );
                    } else {
                        $cena_gold = round( $quarter * $statsUnit['CorpsId'] );
                    }
                    $errEt = '<div id="end"> ' . fabryki_jednostka . ' <strong> ' . $statsUnit['nazwa'] . ' </strong>' . fabryki_jednostka1 . ' ' . $toPlayerInfo . ' ' . fabryki_jednostka3 . ' ' . $cena_gold . ' ' . fabryki_jednostka4;
                    if ($this->account->gold >= $cena_gold) {
                        $errEt .= '<div id="przyspiesz" num="' . $statsUnit['CorpsId'] . '">' . fabryki_prem . '</div>';
                    } else {
                        $errEt .= '<div class="bank kupZloto" title=" '. comp_index26 .' ">BRAK ZŁOTA</div>';
                    }
                    $errEt .= '</div>';
                    $dataBuilder['info'] = $errEt;
                    $rowsUnit = null;
                } else {
                    //budujemy fabrykę za złoto
                }
            } else {
                $dataBuilder['error'] = comp_index36.' ' . $statsUnit['tier'];
            }
        } else {
            $buildID = 0;
            $dataBuilder['error'] = fabryki_title_no_cash;
        }
        $statsUnit = null;
        echo json_encode($dataBuilder);
    }
	
	public function loadBlockForum()
    {
        $objHelper = init::getFactory()->getService('helper');
        
		
		//$s = $this->path->post('page');
        $dataForum = array();
        //if (!preg_match("/^[\d]{1,11}$/i", $s)) $this->error->printError('Niepoprawne dane', 'mapa');
		
        $dataForum['txt'] = '<div id="twoje_jednostki">
			' . forum . '
		</div>';
        $dataForum['body'] = '
		<div id="forum">
			' . forum_txt;
        $dataForum['body'] .= '<table class="table_forum">
		<tr>
            <td><strong>'.comp_index37.'</strong></td>
			<td></td>
	        <td><strong>'.comp_index38.'</strong></td>
			<td></td>
			<td><strong>'.comp_index39.'</strong></td>
		</tr>
		';
        // $na_stronie = 10;         // liczba rekordow widocznych na stronie
        //$na_pasku = 7;          // liczba odpowiedzi/2 na pasku
       
		$rowsSection = $this->db->query('SELECT * from `forumSection`');
		$rSection = $rowsSection->fetchAll();
        $i = 1;
        foreach ($rSection as $dane) {
            $nameSection = $objHelper->wrap($dane['nameSection'], 25, '<br>');
            $rowsShowSectionCount = $this->db->query('SELECT count(*) from `forum_tematy`  WHERE `sectionID`=' . $dane['sectionID'] . '')->fetch();
			$rowsShowSection = $this->db->query('SELECT * from `forum_tematy`  WHERE `sectionID`=' . $dane['sectionID'] . ' ORDER BY `ostatni_komentarz` DESC LIMIT 1')->fetch();
			
		//$lastComment = $rowsShowSection['id'];
		  
			if ($i % 2 == 1) {
                $bg_kreska = "first";
            } else {
                $bg_kreska = "second";
            }
			$znaczek ='';
			$znaczek_b ='';  
			if($rowsShowSection['id'] != ''){
				//ustalamy czy dać ikonkę NEW POST
				
				$rowsShow = $this->db->query('SELECT `topicID` from `forum_widziane`  WHERE `topicID`=' . $rowsShowSection['id'] . ' AND `accountID`=' . $this->account->id . ' ')->fetch();
				if (!$rowsShow['topicID']) {
					$znaczek = '<div class="newPost" title="' . forum7 . '">N</div>';
				} else {
					$znaczek = " ";
				}
				
				$rowsShowLastComment = $this->db->query('SELECT * from `forum_komentarze`  WHERE `id_tematu`=' . $rowsShowSection['id'] . ' ORDER BY `data_komentarza` DESC LIMIT 1')->fetch();
				$lastComment = $rowsShowLastComment['autor_komentu'];
				if($rowsShowLastComment['autor_komentu'] != ''){
					$autorComment = '<br>, '.comp_index40.': '.$rowsShowLastComment['autor_komentu'];
				}else{
					$autorComment = '';
				}
				
				$temat = '<tr class="forum_row" style="border-top:1px solid #6b553c;border-bottom:1px solid #6b553c;">
						<td class="' . $bg_kreska . ' section" id="' . $dane['sectionID'] . '" style="text-align:left;padding-left:20px;position:relative;" numer="' . $dane['sectionID'] . '" num="1">' . $dane['nameSection'] . ' ' . $znaczek . ' ' . $znaczek_b . ' </td>
						<td class="' . $bg_kreska . '_kreska"></td>
						<td class="' . $bg_kreska . '" ><small>' . (int)$rowsShowSectionCount[0]. '</small></td>
						<td class="' . $bg_kreska . '_kreska"></td>
						<td class="' . $bg_kreska . ' temat" id="' . $rowsShowSection['id'] . '" style="text-align:left;padding-left:20px;position:relative;" numer="' . $rowsShowSection['id'] . '" num="1"" style="padding: 0px 5px 0px 5px;"><strong>'.$rowsShowSection['tytul'].'</strong> (<small>autor: ' . $rowsShowSection['autor'] . ''.$autorComment.'</small>)</td>
					</tr>';
				$i++;
				$dataForum['body'] .= $temat;
			}else{
				$temat = '<tr class="forum_row" style="border-top:1px solid #6b553c;border-bottom:1px solid #6b553c;">
						<td class="' . $bg_kreska . ' section" id="' . $dane['sectionID'] . '" style="text-align:left;padding-left:20px;position:relative;" numer="' . $dane['sectionID'] . '" num="1">' . $dane['nameSection'] . ' ' . $znaczek . ' ' . $znaczek_b . ' </td>
						<td class="' . $bg_kreska . '_kreska"></td>
						<td class="' . $bg_kreska . '" >0</td>
						<td class="' . $bg_kreska . '_kreska"></td>
						<td class="' . $bg_kreska . '" style="text-align:left;padding-left:20px;position:relative;" style="padding: 0px 5px 0px 5px;">'.comp_index41.'</td>
					</tr>';
				$i++;
				$dataForum['body'] .= $temat;
			}
        }
        echo json_encode($dataForum);
    }
	
	
    public function loadSingleBlockForum()
    {
        $objHelper = init::getFactory()->getService('helper');
		$s = $this->path->post('page');
        $dataForum = array();
        if (!preg_match("/^[\d]{1,11}$/i", $s)) $this->error->printError('Niepoprawne dane', 'mapa');
		
        $dataForum['txt'] = '<div id="twoje_jednostki">
			' . forum . '
		</div>';
        $dataForum['body'] = '
		<div id="forum">
			' . forum_txt;
			
        if ($this->account->ban == 3 AND ($this->account->banWorld == $this->world->id OR $this->account->banWorld == 0) and $this->account->banEnd > $this->init->time) {
            $dataForum['body'] .= '<div id="no_post">
				'.comp_index42.'.<br>
				'.comp_index43.'.<br>
				'.comp_index44.': <strong>' . $this->account->reason . '</strong><br>
				'.comp_index45.' <strong>' . date("Y-m-d H:i:s", $this->account->banEnd) . '</strong>.<br>
				<br>
				'.comp_index46.' team@wichry-wojny.eu
			</div>';
        } else {
            $dataForum['body'] .= '<div id="backToSectionList">
				'.comp_index47.'
			</div><div id="dodaj_post">
				' . forum1 . '
			</div>
			<div id="forumDiv">
			';
        }
        $dataForum['body'] .= '<table class="table_forum">
		<tr>
            <td><strong>' . forum3 . '</strong></td>
			<td></td>
	        <td><strong>' . forum4 . '</strong></td>
			<td></td>
			<td><strong>' . forum5 . '</strong></td>
			<td></td>
            <td><strong>' . forum6 . '</strong></td>
		</tr>
		';
        $na_stronie = 10;         // liczba rekordow widocznych na stronie
        $na_pasku = 7;          // liczba odpowiedzi/2 na pasku
       
		$rowsTopic = $this->db->query('SELECT `f_t`.*,`f_k`.`autor_komentu` from `forum_tematy` AS `f_t` LEFT JOIN `forum_komentarze` AS `f_k` ON ( `f_t`.`id`=`f_k`.`id_tematu` ) GROUP BY `f_t`.`id` ORDER BY `f_t`.`ostatni_komentarz` DESC ');
		$rek = $rowsTopic->fetchAll();
		$rekordow = $rowsTopic->rowCount();
        $stron = ceil($rekordow / $na_stronie);
        if ($s >= 1 and $rekordow > 0) {
            $start = ($s - 1) * $na_stronie;
            $rowsTopic = $this->db->query('SELECT `f_t`.*,`f_k`.`autor_komentu` from `forum_tematy` AS `f_t` LEFT JOIN `forum_komentarze` AS `f_k` ON ( `f_t`.`id`=`f_k`.`id_tematu` ) GROUP BY `f_t`.`id` ORDER BY `f_t`.`ostatni_komentarz` DESC LIMIT ' . $start . ', ' . $na_stronie . ' ');
            $rek = $rowsTopic->fetchAll();
        }
        if ($s == 1) {
            $i = 1;
        } else if ($s > 1) {
            $i = $start + 1;
        };
		
        foreach ($rek as $dane) {
            $tytul = $dane['tytul'];
            $tytul = $objHelper->wrap($tytul, 25, '<br>');
            $autor = $dane['autor'];
            if (strlen($autor) > 20) $autor = substr($autor, 0, 19) . "...";
            $wyswietlen = $dane['wyswietlen'];
            $id_artu = $dane['id'];
            $zablokowany_art = $dane['zablokowany'];
            $rowsShow = $this->db->query('SELECT `topicID` from `forum_widziane`  WHERE `topicID`=' . $id_artu . ' AND `accountID`=' . $this->account->id . ' ')->fetch();
            if (!$rowsShow['topicID']) {
                $znaczek = '<div class="newPost" title="' . forum7 . '">N</div>';
            } else {
                $znaczek = " ";
            }
            if ($zablokowany_art == 1) {
                $znaczek_b = '<div class="closePost" title="' . forum8 . '">Z</div>';
            } else {
                $znaczek_b = " ";
            }
            $rowsKoment = $this->db->query('SELECT `autor_komentu` from `forum_komentarze` WHERE `id_tematu`=' . $id_artu . ' ORDER BY `data_komentarza` DESC');
            $rekKom = $rowsKoment->fetchAll();
            $odpowiedzi = $rowsKoment->rowCount();
            if ($odpowiedzi == 0) {
                $tabOdp = '<small>'.temat_list6.'</small>';
            } else {
                $tabOdp = $odpowiedzi . '<br><small>' . forum9 . ' ' . $rekKom[0]['autor_komentu'] . '</small>';
            }
            if ($i % 2 == 1) {
                $bg_kreska = "first";
            } else {
                $bg_kreska = "second";
            }
            $temat = '<tr class="forum_row" style="border-top:1px solid #6b553c;border-bottom:1px solid #6b553c;">
					<td class="' . $bg_kreska . ' temat" id="' . $id_artu . '" style="text-align:left;padding-left:20px;position:relative;" numer="' . $id_artu . '" num="1">' . $tytul . ' ' . $znaczek . ' ' . $znaczek_b . ' </td>
					<td class="' . $bg_kreska . '_kreska"></td>
					<td class="' . $bg_kreska . '" style="padding: 0px 5px 0px 5px;"><small>' . $autor . '</small></td>
					<td class="' . $bg_kreska . '_kreska"></td>
					<td class="' . $bg_kreska . '" ><small>' . $wyswietlen . '</small></td>
					<td class="' . $bg_kreska . '_kreska"></td>
					<td class="' . $bg_kreska . '" style="width:20%;">' . $tabOdp . '</td>
				</tr>';
            $i++;
            $dataForum['body'] .= $temat;
        }
	
        $dataForum['body'] .= '</table>
		</div>
		</div>
		<div id="pasek_forum">
			' . $objHelper->pasek($rekordow, $na_stronie, $na_pasku, 'forum', $s, tematow, '0') . '
		</div>
		';
        echo json_encode($dataForum);
    }

    public function loadTopic()
    {
        $objHelper = init::getFactory()->getService('helper');
        $id_tematu = $this->path->post('topic');
        $s = $this->path->post('page');
        $dataTopic = array();
        if (!preg_match("/^[\d]{1,11}$/i", $s)) $this->error->printError( comp_index32, 'forum');
        if (!preg_match("/^[\d]{1,11}$/i", $id_tematu)) $this->error->printError( comp_index32, 'forum');

        $dataTopic['bodyTopic'] = '<div id="forum">';
        $na_stronie = 15;         // liczba rekordow widocznych na stronie
        $na_pasku = 7;          // liczba odpowiedzi/2 na pasku
        $start = ($s - 1) * $na_stronie;
        $rowsTopic = $this->db->query('SELECT * from `forum_komentarze` WHERE `id_tematu`=' . $id_tematu . ' ');
        $rek = $rowsTopic->fetchAll();
        $rekordow = $rowsTopic->rowCount();
        $stron = ceil($rekordow / $na_stronie);
        if ($s >= 1 and $rekordow > 0) {
            $start = ($s - 1) * $na_stronie;
            $rowsTopic = $this->db->query('SELECT * from `forum_komentarze` WHERE id_tematu=' . $id_tematu . ' ORDER BY `data_komentarza` DESC LIMIT ' . $start . ', ' . $na_stronie . ' ');
            $rek = $rowsTopic->fetchAll();
        }
        if ($s == 1) {
            $i = 1;
        } else if ($s > 1) {
            $i = $start + 1;
        };
        $rowsTopicText = $this->db->query('SELECT * from `forum_tematy` WHERE `id`=' . $id_tematu . '')->fetch();
        $dataTopic['Topic'] = $objHelper->wrap($rowsTopicText['tytul'], 75, '<br>');
        $dataTopic['bodyTopic'] .= '<small>
				Dodał :<strong>' . $rowsTopicText['autor'] . '</strong><br>
				Kiedy : ' . date("Y-m-d H:i:s", $rowsTopicText['data']) . '</small><br><br>
		<div><p class="p_forum_temat">' . $objHelper->wrap($rowsTopicText['tresc'], 75, '<br>') . '</p></div><br><br><br>';

        if ($this->account->id == $rowsTopicText['accountID'] AND $this->account->rank != 'ADMIN') {
            $dataTopic['bodyTopic'] .= '<div class="przyciski_forum"><div id="dodaj_kom" title="' . dodaj_komentarz_txt . '" num="' . $id_tematu . '"></div><div class="edit_temat" title="' . profil6 . '" num="' . $id_tematu . '"></div></div>';
        } else if ($this->account->rank == 'ADMIN') {
            $dataTopic['bodyTopic'] .= '<div class="przyciski_forum"><div id="dodaj_kom" title="' . dodaj_komentarz_txt . '" num="' . $id_tematu . '"></div><div class="edit_temat" title="' . profil6 . '" num="' . $id_tematu . '"></div><div class="delete_"  title="' . sztaby_usun . '" num="' . $id_tematu . '"></div><div class="blockAutor" title="'.comp_index48.'" num="' . $rowsTopicText['accountID'] . '"></div></div>';
        } else {
            if ($this->account->ban == 3 AND ($this->account->banWorld == $this->world->id OR $this->account->banWorld == 0) and $this->account->banEnd > $this->init->time) {
                $dataTopic['bodyTopic'] .= '<div class="przyciski_forum">'.comp_index50.'</div>';
            } else {
                $dataTopic['bodyTopic'] .= '<div class="przyciski_forum"><div id="dodaj_kom" title="' . dodaj_komentarz_txt . '" num="' . $id_tematu . '">' . $this->account->ban . '</div></div>';
            }
        }
        if ($rekordow >= 1) {
            $dataTopic['bodyTopic'] .= '<table>';
            foreach ($rek as $dane) {
                $dataTopicText = '<tr>
							<td>
								<small>';
                $dataTopicText .= $dane['autor_komentu'] . '</b></font> '.temat_list4.' ' . date("Y-m-d H:i:s", $dane['data_komentarza']) . ' '.temat_list5.':
								</small>
								<p class="p_forum" id="' . $dane['id'] . '">
									' . $objHelper->wrap($dane['komentarz'], 75, '<br>') . '
								</p>
							</td>
							<td>';
                if ($this->account->id == $dane['id_autora_komentu'] AND $this->account->rank != 'ADMIN' AND $this->account->rank != 'MODERATOR') {
                    $dataTopicText .= '<div class="przyciski_forum_odp"><div class="edit_" title="' . profil6 . '" num="' . $dane['id'] . '"></div>';
                } else if ($this->account->rank == 'ADMIN' OR $this->account->rank == 'MODERATOR') {
                    $dataTopicText .= '<div class="przyciski_forum_odp"><div class="edit_" title="' . profil6 . '" num="' . $dane['id'] . '"></div><div class="deleteCom" title="' . sztaby_usun . '" num="' . $dane['id'] . '"></div><div class="blockAutor" title="'.comp_index47.'" num="' . $dane['id_autora_komentu'] . '"></div></div>';
                } else {
                    $dataTopicText .= '<div class="przyciski_forum_odp"><div class="report" title="'.comp_index49.'" num="' . $dane['id'] . '"></div></div>';
                }
                $dataTopicText .= '
							</td>
						</tr>';
                $i++;
                $dataTopic['bodyTopic'] .= $dataTopicText;
            }

            $dataTopic['bodyTopic'] .= '</table>
				<div id="pasek_forum">
					' . $objHelper->pasek($rekordow, $na_stronie, $na_pasku, 'temat' , $s, comp_index51, $id_tematu) . '
				</div>
				</div>
				';
        } else {
            $dataTopic['bodyTopic'] .= temat_list6 .'!';
        }
        //zapisujemy, ze gracz widział nowe post
        $rowsShow = $this->db->query('SELECT `topicID` from `forum_widziane`  WHERE `topicID`=' . $id_tematu . ' AND `accountID`=' . $this->account->id . ' ')->fetch();
        if ( !$rowsShow['topicID'] ) {
            $this->db->exec('INSERT INTO `forum_widziane` (`accountID`,`topicID` ) VALUES ( ' . $this->account->id . ', ' . $id_tematu . ' ) ');
        }
        $this->db->exec('UPDATE `forum_tematy` SET `wyswietlen`=( `wyswietlen`+1 ) WHERE `id`=' . $id_tematu . ' LIMIT 1');
        echo json_encode($dataTopic);
    }
	
	public function loadSectionTopic()
    {
        $objHelper = init::getFactory()->getService('helper');
        $sectionID = $this->path->post('section');
        $s = $this->path->post('page');
        $dataTopic = array();
        if (!preg_match("/^[\d]{1,11}$/i", $s)) $this->error->printError('Niepoprawne dane', 'forum');
        if (!preg_match("/^[\d]{1,11}$/i", $sectionID)) $this->error->printError('Niepoprawne dane', 'forum');
        $dataForum['txt'] = '<div id="twoje_jednostki">
			' . forum . '
		</div>';
        $dataForum['body'] = '
		<div id="forum">
			' . forum_txt;
			
        if ($this->account->ban == 3 AND ($this->account->banWorld == $this->world->id OR $this->account->banWorld == 0) and $this->account->banEnd > $this->init->time) {
            $dataForum['body'] .= '<div id="no_post">
				'.comp_index41.'.<br>
				'.comp_index42.'.<br>
				'.comp_index43.': <strong>' . $this->account->reason . '</strong><br>
				'.comp_index44.' <strong>' . date("Y-m-d H:i:s", $this->account->banEnd) . '</strong>.<br>
				<br>
				'.comp_index45.' team@wichry-wojny.eu
			</div>';
        } else {
            $dataForum['body'] .= '<div id="backToSectionList" class="forum" num="1">
				'.comp_index47.'
			</div><div id="dodaj_post" num="'.$sectionID.'">
				' . forum1 . '
			</div>
			<div id="forumDiv">
			';
        }
        $dataForum['body'] .= '<table class="table_forum">
		<tr>
            <td><strong>' . forum3 . '</strong></td>
			<td></td>
	        <td><strong>' . forum4 . '</strong></td>
			<td></td>
			<td><strong>' . forum5 . '</strong></td>
			<td></td>
            <td><strong>' . forum6 . '</strong></td>
		</tr>
		';
        $na_stronie = 10;         // liczba rekordow widocznych na stronie
        $na_pasku = 7;          // liczba odpowiedzi/2 na pasku
       
		$rowsTopic = $this->db->query('SELECT `f_t`.*,`f_k`.`autor_komentu` from `forum_tematy` AS `f_t` LEFT JOIN `forum_komentarze` AS `f_k` ON ( `f_t`.`id`=`f_k`.`id_tematu` )  WHERE `f_t`.`sectionID`='.$sectionID.' GROUP BY `f_t`.`id` ORDER BY `f_t`.`ostatni_komentarz` DESC ');
		$rek = $rowsTopic->fetchAll();
		$rekordow = $rowsTopic->rowCount();
        $stron = ceil($rekordow / $na_stronie);
        if ($s >= 1 and $rekordow > 0) {
            $start = ($s - 1) * $na_stronie;
            $rowsTopic = $this->db->query('SELECT `f_t`.*,`f_k`.`autor_komentu` from `forum_tematy` AS `f_t` LEFT JOIN `forum_komentarze` AS `f_k` ON ( `f_t`.`id`=`f_k`.`id_tematu` )  WHERE `f_t`.`sectionID`='.$sectionID.' GROUP BY `f_t`.`id` ORDER BY `f_t`.`ostatni_komentarz` DESC LIMIT ' . $start . ', ' . $na_stronie . ' ');
            $rek = $rowsTopic->fetchAll();
        }
        if ($s == 1) {
            $i = 1;
        } else if ($s > 1) {
            $i = $start + 1;
        };
		
        foreach ($rek as $dane) {
            $tytul = $dane['tytul'];
            $tytul = $objHelper->wrap($tytul, 25, '<br>');
            $autor = $dane['autor'];
            if (strlen($autor) > 20) $autor = substr($autor, 0, 19) . "...";
            $wyswietlen = $dane['wyswietlen'];
            $id_artu = $dane['id'];
            $zablokowany_art = $dane['zablokowany'];
            $rowsShow = $this->db->query('SELECT `topicID` from `forum_widziane`  WHERE `topicID`=' . $id_artu . ' AND `accountID`=' . $this->account->id . ' ')->fetch();
            if (!$rowsShow['topicID']) {
                $znaczek = '<div class="newPost" title="' . forum7 . '">N</div>';
            } else {
                $znaczek = " ";
            }
            if ($zablokowany_art == 1) {
                $znaczek_b = '<div class="closePost" title="' . forum8 . '">Z</div>';
            } else {
                $znaczek_b = " ";
            }
            $rowsKoment = $this->db->query('SELECT `autor_komentu` from `forum_komentarze` WHERE `id_tematu`=' . $id_artu . ' ORDER BY `data_komentarza` DESC');
            $rekKom = $rowsKoment->fetchAll();
            $odpowiedzi = $rowsKoment->rowCount();
            if ($odpowiedzi == 0) {
                $tabOdp = '<small>'.temat_list6.'</small>';
            } else {
                $tabOdp = $odpowiedzi . '<br><small>' . forum9 . ' ' . $rekKom[0]['autor_komentu'] . '</small>';
            }
            $bg_kreska = '';
			if ($i % 2 == 1) {
                $bg_kreska = "first";
            } else {
                $bg_kreska = "second";
            }
            $temat = '<tr class="forum_row" style="border-top:1px solid #6b553c;border-bottom:1px solid #6b553c;">
					<td class="' . $bg_kreska . ' temat" id="' . $id_artu . '" style="text-align:left;padding-left:20px;position:relative;" numer="' . $id_artu . '" num="1">' . $tytul . ' ' . $znaczek . ' ' . $znaczek_b . ' </td>
					<td class="' . $bg_kreska . '_kreska"></td>
					<td class="' . $bg_kreska . '" style="padding: 0px 5px 0px 5px;"><small>' . $autor . '</small></td>
					<td class="' . $bg_kreska . '_kreska"></td>
					<td class="' . $bg_kreska . '" ><small>' . $wyswietlen . '</small></td>
					<td class="' . $bg_kreska . '_kreska"></td>
					<td class="' . $bg_kreska . '" style="width:20%;">' . $tabOdp . '</td>
				</tr>';
            $i++;
            $dataForum['body'] .= $temat;
        }
	
        $dataForum['body'] .= '</table>
		</div>
		</div>
		<div id="pasek_forum">
			' . $objHelper->pasek($rekordow, $na_stronie, $na_pasku, 'section', $s, tematow, $sectionID) . '
		</div>
		';
        echo json_encode($dataForum);
    }
	
	

	public function editTopic()
	{
		$objHelper = init::getFactory()->getService('helper');
		$tytul = $this->path->post('topic');
		$tresc = $this->path->post('content');
		$action = $this->path->post('action');
		$numPost = $this->path->post('num');
		$sectionID = $this->path->post('sectionID');
		$dataTopic = array();
		if (!preg_match("/^[\d]{1,11}$/i", $action)) $this->error->printError( comp_index32, 'forum');
		if (!preg_match("/^[\d]{0,11}$/i", $numPost)) $this->error->printError( comp_index32, 'forum');

		$sprawdzonyTytul = $objHelper->safeChatText($tytul);
		$sprawdzonaTresc = $objHelper->safeChatText($tresc);
			switch ($action) {
				case '1'://dodawanie tematu na forum
				$rowsTopic = $this->db->query('SELECT * from `forum_tematy` WHERE `tytul`="' . $tytul . '" LIMIT 1')->fetch();
					if ($rowsTopic['id']) {
						$dataTopic['error'] = comp_index52;//'Taki temat mamy już na naszym forum';
						$rowsTopic = null;
						echo json_encode($dataTopic);
						exit();
					}
					
					$sql = 'INSERT INTO `forum_tematy` (`tytul`,`tresc`,`autor`, `accountID`, `data`,`ostatni_komentarz`, `sectionID`  ) VALUES (:tytul,:tresc,:login,' . $this->account->id . ', ' . $this->init->time . ', ' . $this->init->time . ',:sectionID ) ';
					$query = $this -> db -> prepare($sql);
					$query -> bindValue(':tytul',$sprawdzonyTytul, PDO::PARAM_STR);
					$query -> bindValue(':tresc',$sprawdzonaTresc, PDO::PARAM_STR);
					$query -> bindValue(':login',$this -> account -> login, PDO::PARAM_STR);
					$query -> bindValue(':sectionID',$sectionID, PDO::PARAM_STR);
					
					
					$query -> execute();
					$intoID = $this->db->lastInsertID();// sprawdzam numer ID dodanego tematu
					$this->db->exec('INSERT INTO `forum_widziane` (`accountID`,`topicID` ) VALUES ( ' . $this->account->id . ', ' . $intoID . ' ) ');
					$dataTopic['info'] = comp_index53;//'Twój temat jest już widoczny na naszym forum';
					
					//wysyłamy info o nowym temacie d wszystkich graczy
					$arrayData = array(
						'activity' => 'addTopic',
						'chanData' => array(
							array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
								'data' => array(
									'playerID' => $this->account->playerID
								),
							),
							array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
								'data' => array(
										'playerID' => $this->account->playerID
								)
							)
						)
					);
					$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
				break;
				case '2'://zapis edycji tematu na forum
					$rowsTopic = $this->db->query('SELECT * from `forum_tematy` WHERE `accountID`=' . $this->account->id . ' AND `id`=' . $numPost . ' LIMIT 1')->fetch();
					if (!isset($rowsTopic['id']) AND $this->account->rank != 'ADMIN' AND $this->account->rank != 'MODERATOR') {
						$dataTopic['error'] = comp_index54;//'Nie jesteś autorem tego tematu, więc nie możesz go edytować';
						$rowsTopic = null;
						echo json_encode($dataTopic);
						exit();
					}
					$this->db->exec('UPDATE `forum_tematy` SET `tytul`="' . $sprawdzonyTytul . '", `tresc`="' . $sprawdzonaTresc . '", `ostatni_komentarz`=' . $this->init->time . ' WHERE `id`=' . $numPost . ' LIMIT 1');
					$this -> db -> exec('DELETE FROM `forum_widziane` WHERE `topicID`=' . $numPost . ' AND `accountID`!='. $this->account->id .' ');
					$dataTopic['info'] = comp_index55;//'Edycja tematu zapisana';
					$dataTopic['nTopic'] = $sprawdzonyTytul;
					if ($this->account->rank == 'ADMIN' OR $this->account->rank == 'MODERATOR') {
						$this->db->exec('INSERT INTO `administrativeOperations` (`operacja`,`adminID`,`dataOperacji`) VALUES ("edycja tematu nr ' . $numPost . '",' . $this->account->id . ',NOW() ) ');
					}
				break;
				case '3'://dodawanie komentarza do posta
					$sql = 'INSERT INTO `forum_komentarze` (`id_tematu`,`autor_komentu`,`id_autora_komentu`,`komentarz`,`data_komentarza`) VALUES (' . $numPost . ',:autor_komentu,' . $this->account->id . ',:komentarz, ' . $this->init->time . ' ) ';
					$query = $this -> db -> prepare($sql);
					$query -> bindValue(':komentarz',$sprawdzonaTresc, PDO::PARAM_STR);
					$query -> bindValue(':autor_komentu',$this -> account -> login, PDO::PARAM_STR);
					$query -> execute();
					$dataTopic['info'] = comp_index56;
					$this->db->exec('DELETE FROM `forum_widziane` WHERE `topicID`='.$numPost .' AND `accountID`!='.$this->account->id.' ');
					$this->db->exec('UPDATE `forum_tematy` SET `ostatni_komentarz`=' . $this->init->time . ' WHERE `id`=' . $numPost . ' LIMIT 1');
					$arrayData = array(
						'activity' => 'addKom',
						'chanData' => array(
							array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
								'data' => array(
									'playerID' => $this->account->playerID
								),
							),
							array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
								'data' => array(
										'playerID' => $this->account->playerID
								)
							)
						)
					);
					$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
					
					
					
				break;
				case '4'://edycja komentarza
					//$this->db->exec('UPDATE `forum_komentarze` SET `komentarz`="' . $sprawdzonaTresc . '", `data_komentarza`=' . $this->init->time . '  WHERE `id`=' . $numPost . ' LIMIT 1');
					
					
					
					$sql = 'UPDATE `forum_komentarze` SET `komentarz`= ?, `data_komentarza`= ? WHERE  `id`=?';
					$query = $this -> db -> prepare($sql);
					$query->execute([$sprawdzonaTresc, $this->init->time, $numPost]);
					$query -> execute();
					$this->db->exec('UPDATE `forum_tematy` SET `ostatni_komentarz`=' . $this->init->time . '  WHERE `id`=' . $numPost . ' LIMIT 1');
					$this->db->exec('DELETE FROM `forum_widziane` WHERE `topicID`='.$numPost .' AND `accountID`!='.$this->account->id.' ');
					$dataTopic['info'] = 'Edycja komentarza zapisana';
					$dataTopic['nOpis'] = $sprawdzonaTresc;
					if ($this->account->rank == 'ADMIN' OR $this->account->rank == 'MODERATOR') {
						$this->db->exec('INSERT INTO `administrativeOperations` (`operacja`,`adminID`,`dataOperacji`) VALUES ("edycja komentarza nr ' . $numPost . '",' . $this->account->id . ', NOW() ) ');
					}
				break;
				case '5'://zamykanie tematu na forum
				break;
				case '6'://usuwanie tematu na forum
				break;
				case '7'://pobranie treści tematu forum do edycji prze admina
					$rowsTopic = $this->db->query('SELECT * from `forum_tematy` WHERE `id`=' . $numPost . ' LIMIT 1')->fetch();
					if (!isset($rowsTopic['id'])) {
						$dataTopic['error'] = comp_index54;//'Nie jesteś autorem tego tematu, więc nie możesz go edytować';
						$rowsTopic = null;
						echo json_encode($dataTopic);
						exit();
					}
					$dataTopic['tresc'] = $rowsTopic['tresc'];
					$dataTopic['tytul'] = $rowsTopic['tytul'];
				break;
			}
		echo json_encode($dataTopic);
	}


	public function commentTopic()
	{
		$objHelper = init::getFactory()->getService('helper');
		$num = $this->path->post('num');
		$dataTopic = [];
		$rowsComment = $this->db->query('SELECT `komentarz` from `forum_komentarze` WHERE `id`="' . $num . '" LIMIT 1')->fetch();
		$dataTopic['num'] = $num;
		$dataTopic['tresc'] = $rowsComment['komentarz'];
		echo json_encode($dataTopic);
	}

    public function loadShop()
    {
        $datashop = [];
		//sprawdzam, czy wojna nadal trwa
        $capitalRaport = $this->unit->checkCapital(0, $this->world->id);
        if ($capitalRaport['continueBattle'] == 0)//jeśli wojna jest zakończona
        {
            $datashop['error'] = comp_index57 .'.';
            echo json_encode($datashop);
            exit();
        } else {
            $objHelper = init::getFactory()->getService('helper');
            //sprawdzam, ile gracz kupił już jednostek
            $rowsUnitno = $this->db->query('SELECT count(*) from `GoldUnits` WHERE `playerID`=' . $this->account->playerID . '')->fetch();
            $dataShop['body'] = $objHelper->odmiana_jednostek( (int)$rowsUnitno[0] ) . '<br>
		' . sklep1;
            $ile_mozesz = $this->account->rankLevel;
            $ile_sponsor = ile_sponsor1 . ' <strong>' . $this->account->rankLevel . '</strong> ' . ile_sponsor2;
            if ($this->account->SponsorAccount > $this->init->time) {
                $ile_mozesz = $this->account->rankLevel * 2;
                $ile_sponsor = ' + ' . $objHelper->odmiana_jednostki($this->account->rankLevel) . '  ' . ile_sponsor3;
            }
            $dataShop['body'] .= $objHelper->odmiana_jednostki($this->account->rankLevel) . ' ' . $ile_sponsor . '<br><br><br>';
            if ($this->account->nation == 1) {
                $personnel = '`pole`, `englishman`, `russian`, `frenchman`, `american`';
            } else {
                $personnel = '`german`, `italian`, `japanese`';
            }
            $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' ')->fetch();
            if ($this->account->nation == 1) {
                $warunek_nacja = "(`td`.`NationID`=1 ";
                if ($statsUnit['englishman'] == 1) {
                    $warunek_nacja .= " OR `td`.`NationID`=2 ";
                }
                if ($statsUnit['russian'] == 1) {
                    $warunek_nacja .= "OR `td`.`NationID`=3 ";
                }
                if ($statsUnit['frenchman'] == 1) {
                    $warunek_nacja .= "OR `td`.`NationID`=5 ";
                }
                if ($statsUnit['american'] == 1) {
                    $warunek_nacja .= "OR `td`.`NationID`=4 ";
                }
            } else {
                $warunek_nacja = "(`td`.`NationID`=7 ";
                if ($statsUnit['italian'] == 1) {
                    $warunek_nacja .= "OR `td`.`NationID`=8 ";
                }
                if ($statsUnit['japanese'] == 1) {
                    $warunek_nacja .= "OR `td`.`NationID`=9 ";
                }
            }
            $warunek_nacja .= ")";

            if ($this->world->WarWeather >= 4) {
                $warunekOgraniczajacySztuki = 'AND MOD(`u`.`id`,2)=0';
            } else if ($this->world->WarWeather < 4) {
                $warunekOgraniczajacySztuki = 'AND MOD(`u`.`tacticalDataId`,2)=0';
            } else if ($this->world->WarWeather >= 9) {
                $warunekOgraniczajacySztuki = 'AND MOD(`u`.`tacticalDataId`,1)=0';
            } else if ($this->world->WarWeather < 9) {
                $warunekOgraniczajacySztuki = 'AND MOD(`u`.`id`,1)=0';
            } else {
                $warunekOgraniczajacySztuki = ' ';
            }
			$warunekOgraniczajacySztuki = ' ';
			//sprawdzam, na jakim heksie stoi miasto, czy gracz może kupić okręty 
			$podlozeMiasta = $this->db->query('SELECT `mapData`.`fieldType` from `mapData`
			LEFT JOIN `units` ON `mapData`.`x`=`units`.`x` AND `mapData`.`y`=`units`.`y`
			WHERE `units`.`playerID`=' . $this->account->playerID . ' AND `units`.`unitType`=7 AND `mapData`.`worldID`= ' . $this->world->id . ' LIMIT 1')->fetch();
            if ($podlozeMiasta['fieldType'] != 11) {
                $warunekStocznia = 'AND `u`.`unitType`!=5';
            }else{
				$warunekStocznia = ' ';
			}
			
            $rowsShopUnits = $this->db->query('SELECT `td`.*,`u`.`tacticalDataID`,`u`.`id` as `uid`, `u`.`unitExperience` FROM `TacticalData` AS `td` LEFT JOIN `units` AS `u` ON (`u`.`tacticalDataID`=`td`.`id`) WHERE ' . $warunek_nacja . ' AND `td`.`rocznik`<= ' . $this->world->WarDateY . ' AND `u`.`tacticalDataID`=`td`.`id` AND `u`.`playerID`=0 AND `u`.`unitType`!=7 ' . $warunekStocznia . ' ' . $warunekOgraniczajacySztuki . ' ORDER BY RAND() LIMIT 24');
            $units = $rowsShopUnits->fetchAll();
            $ile_jednostek_do_sprzedazy = $rowsShopUnits->rowCount();
            $unity = 0;
			$width = 335;
            if ($this->account->playerVacation > $this->init->time) {//gracz jest na urlopie, więc nie może kupowac jednostek
                $dataShop['body'] .= sklep2 . '<br><br><br>';
                $unity = 0;
            } else if ($ile_jednostek_do_sprzedazy == 0) {//nie ma jednostek w sklepie
                $dataShop['body'] .= sklep9;
                $unity = 0;
            } else if ($ile_jednostek_do_sprzedazy > 0 AND (int)$rowsUnitno[0] < $ile_mozesz) {//gracz może kupić jednostki za kredyty
                $dataShop['body'] .= '<br><div id="tabelaShop"><table style="margin:0 auto;">
				<tr>';
                $unity = 1;
            } else if ($ile_jednostek_do_sprzedazy > 0 AND (int)$rowsUnitno[0] >= $ile_mozesz) {//gracz może kupić jendostki tylko za złoto
                $dataShop['body'] .= sklep_only_gold . '<div id="tabelaShop"><table style="margin:0 auto;">
				<tr>';
                $unity = 1;
				$width += 90;
            }
            if ($unity == 1) {
                $i = 1;

                foreach ($units as $dane) {
                    $cena = 0;
                    if ($dane['nazwa'] == 'Pojazd inżynierów') {
                        $cena = 500;
                    } else {
                        $cena = $dane['cena'];
                    }
					
					// dodajemy koszt upływających tur
					if( $this->world->timing == 1 ){
						$serverTime = $this->world->timeToUp;
					}else{
						//sprWDZAMY, NA JAKIM SWIECIE GRAJĄ GRACZE
						switch($this->world->WarMode){
							case 3:
								$serverTime = 10800;
							break;
							case 4:
								$serverTime = 43200;
							break;
							default:
								$serverTime = 14400;
							break;
						}
					}
					
					$quarter =  round( ( ( ( $dane['czas_produkcji'] * $serverTime ) / 900 )/10 ) + $cena / 15 );// koszt złota za czas produkcji
					if ( (int)$rowsUnitno[0] > $ile_mozesz ) {
                        $cena_gold = round( ($quarter * $dane['CorpsId']) + ( (int)$rowsUnitno[0]-$ile_mozesz ) );
                    } else {
                        $cena_gold = round( $quarter * $dane['CorpsId'] );
                    }
					
					
                    if ( (int)$rowsUnitno[0] < $ile_mozesz AND $cena <= $this->account->PremiumCurrency + $this->account->softCurrency) {
                        $haslo = SYSTEM_PASSWORD;
                        $t["idj"] = $dane['tacticalDataID'];
                        $t["idUP"] = $dane['uid'];
                        $t["user_id"] = $this->account->playerID;
                        $t["cena"] = $cena;
                        $t["gold"] = 0;
                        $t["adres"] = 1;
                        $dane_first = base64_encode(serialize($t));
                        $dane_second = md5($haslo . $dane_first);
                        $cena_actual = '<strong>' . $cena . '</strong> '. panel_gry6;
                        $kup_t = "<div class=\"zakup_sklep\" num=\"" . $dane_first . "\" code=\"" . $dane_second . "\">" . sklep4 . "</div>";
                    } else if ( $cena_gold <= $this->account->gold ) {
                        $haslo = SYSTEM_PASSWORD;
                        $t["idj"] = $dane['tacticalDataID'];
                        $t["idUP"] = $dane['uid'];
                        $t["user_id"] = $this->account->playerID;
                        $t["cena"] = $cena_gold;
                        $t["gold"] = 1;
                        $t["adres"] = 1;
                        $dane_first = base64_encode(serialize($t));
                        $dane_second = md5($haslo . $dane_first);
                        $cena_actual = "<b>" . $cena_gold . "</b> szt. złota";
                        $kup_t = "<div class=\"zakup_sklep\" num=\"" . $dane_first . "\" code=\"" . $dane_second . "\">" . sklep4 . "</div>";
                    } else {
                        $kup_t = 'nie masz złota';
                        $cena_actual = '';
                    }
                    $dataShop['body'] .= "
					<td id=\"un_".$dane['uid']."\">
						<div class=\"tooltip unit-" . $dane['tacticalDataID'] . "\" num=".$dane['uid']." loc=\"2\"></div>
						<div class=\"kup_teraz\">
							" . $kup_t . "
							" . $cena_actual . "
						</div>
					</td>";
                    $i++;
                    if ($i % 9 == 0) {
                        $dataShop['body'] .= '</tr><tr>';
                        $i = 1;
                    }
                }
                $dataShop['body'] .= '</tr>
								</table></div>';								
            }
			$dataShop['width'] = $width;
            $dataShop['txt'] = 'Sklep';
            echo json_encode($dataShop);
        }
    }

    public function destroyBuild()
    {
        $dataBuilder = array();
        $intUnitID = $this->path->get('idUnit');
        if (!preg_match("/^[\d]{1,11}$/i", $intUnitID)) $this->error->printError('Niepoprawne dane', 'fabryki/1');

        $statsUnit = $this->db->query('SELECT `UnitBuild`.`TurnBuild`, `TacticalData`.`cena`,`TacticalData`.`czas_produkcji`  FROM `TacticalData` JOIN `UnitBuild`
		WHERE ( `TacticalData`.`id`= `UnitBuild`.`tacticalDataID`) AND `TacticalData`.`id`=' . $intUnitID . ' AND `UnitBuild`.`playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
        $cena_jednostki = $statsUnit['cena'];
        $czas_produkcji = $statsUnit['czas_produkcji'];
        
		//if( $this->world->timing == 1 ){
		if( $this->world->timing == 1 ){
			$timeToUp = $this->world->timeToUp;
		}else{
			$timeToUp = 21600;
		}	
			
			$czas_produkcji_fabryka = round( (($this->init->time - $timeToUp)/$statsUnit['TurnBuild']), 1);
        //}else{
		//	$czas_produkcji_fabryka = $statsUnit['TurnBuild'];
		//}
		$oddac = $czas_produkcji_fabryka * (round($cena_jednostki / $czas_produkcji, 2));
        $this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency`+' . $oddac . ') WHERE `id`=' . $this->account->playerID . ' ');
        $this->db->exec('DELETE FROM `UnitBuild` WHERE `tacticalDataID`=' . $intUnitID . ' AND `playerID`=' . $this->account->playerID . ' LIMIT 1');

        $dataBuilder['loadDiv'] = '<div class="buduj" title="'. comp_index58 .'" num="' . $intUnitID . '"></div>';
        $dataBuilder['alert'] = comp_index59;
        $dataBuilder['softCurrency'] = $oddac;
        echo json_encode($dataBuilder);
    }

    public function editOption()
    {
        $objHelper = init::getFactory()->getService('helper');

        $dataEdit = array();
        $dane1 = $this->path->post('dane1');
        $dane2 = $this->path->post('dane2');
        $dane3 = $this->path->post('dane3');
        if (!preg_match("/^[\d]{1,11}$/i", $dane1)) $this->error->printError('Niepoprawne dane', 'fabryki/1');
        if (!preg_match("/^[\d]{1,11}$/i", $dane2)) $this->error->printError('Niepoprawne dane', 'fabryki/1');
        $dane3 = $objHelper->safeText($dane3);

        switch ($dane1) {
            case '1'://zmiana nazwy sztabu
                $this->db->exec('UPDATE `HQData` SET `nameStuff`="' . $dane3 . '"  WHERE `unitsID`=' . $dane2 . ' ');
                $dataEdit['alert'] = sztaby_active2 .' '. $dane3;
                $dataEdit['newName'] = $dane3;
                break;
            case '2'://dodanie jednostki do sztabu
                $this->db->exec('UPDATE `units` SET `belongHQ`=' . $dane2 . '  WHERE `id`=' . $dane3 . ' ');
                $this->db->exec('DELETE FROM `HQwaiting` WHERE `unitsID`=' . $dane3 . ' LIMIT 1');
                $dataEdit['alert'] = sztaby3;
                break;
            case '3'://odrzucenie prośby o dodanie jednostki do sztabu
                $this->db->exec('UPDATE `units` SET `belongHQ`=0  WHERE `id`=' . $dane3 . ' ');
                $dataEdit['alert'] = sztaby4;
                break;
        }
        echo json_encode($dataEdit);
    }

    public function loadPersonnel()
    {
        $dataPersonnel = array();

        if ($this->account->nation == 1) {
            $personnel = '`pole`, `englishman`, `russian`, `frenchman`, `american`, `poleSonar`';
        } else {
            $personnel = '`german`, `italian`, `japanese`, `germanSonar`';
        }
        $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' ')->fetch();

        // kadra_opis_txt;
       $dataPersonnel['TableTopKadra'] = '<table class="table_style">
			 <tr class="padding_top">
				<td colspan="11"></td>
			</tr>
			 <tr class="naglowek_tabeli">
				 <td></td>
				 <td><strong>' . kadra1 . '</strong></td><td><strong>' . kadra2 . '</strong></td>
				 <td><strong>' . sklep7 . '</strong></td><td><strong>' . kadra3 . '</strong></td>
				 <td><strong>' . kadra4 . '</strong></td>
				 <td></td>
			 </tr>';

		$dataPersonnel['titleKadra'] = comp_index60;
        $dataPersonnel['bodyKadra'] = $this->pokaz_inz();
        $podlozeMiasta = $this->db->query('SELECT `mapData`.`fieldType` from `mapData`
		LEFT JOIN `units` ON `mapData`.`x`=`units`.`x` AND `mapData`.`y`=`units`.`y`
		WHERE `units`.`playerID`=' . $this->account->playerID . ' AND `units`.`unitType`=7 AND `mapData`.`worldID`= ' . $this->world->id . ' LIMIT 1')->fetch();
        $dataPersonnel['tableDeff'] = comp_index61 .'.<br>
		'. comp_index62 .'.<br><br>
			 
			<table class="table_style">
			 <tr class="padding_top">
				<td colspan="11"></td>
			</tr>
			 <tr class="naglowek_tabeli">
				 <td></td>
				 <td><strong>' . kadra1 . '</strong></td><td><strong>' . kadra2 . '</strong></td>
				 <td><strong>' . sklep7 . '</strong></td><td><strong>' . kadra3 . '</strong></td>
				 <td><strong>' . kadra4 . '</strong></td>
				 <td></td>
			 </tr>';
        $dataPersonnel['tableEnd'] = '	
					<tr class="padding_bottom">
					<td colspan="11"></td>
				</tr>
			</table>
			';
        echo json_encode($dataPersonnel);
    }

    private function pokaz_inz()
    {
        $wynik = '';
        if ($this->account->nation == 1) {
            $x_per = 1;
            $personnel = '`pole`,`englishman`,`russian`,`american`,`frenchman`, `poleSonar`';
            $xwar = 6;
        } else {
            $x_per = 7;
            $personnel = '`german`,`italian`,`japanese`,`germanSonar`';
            $xwar = 10;
        }
        $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' ')->fetch();
        $xvn = 0;
        for ($x = $x_per; $x <= $xwar; $x++) {
            $personnelStats = $this->db->query('SELECT * FROM `PersonnelDescription` WHERE `nationID`=' . $x . ' ')->fetch();
            $HRstage = $this->db->query('SELECT `HRStage`, count(*) as counted FROM `hiringPersonnel` WHERE `enginner`=' . $x . ' AND `playerID`=' . $this->account->playerID . ' ')->fetch();
            if ($HRstage['counted'] == 0) {
                $HRstage['HRStage'] = 1;
            }
            if ($statsUnit[$xvn] == 1) {
                $dzialanie = '<div class="editPersonnel" name="' . $x . '">' . zwolnij_txt . '</div>';
                $status = kadra5;
            } else {
                if ($personnelStats['cena'] <= ($this->account->softCurrency + $this->account->PremiumCurrency)) {
                    $dzialanie = '<div class="editPersonnel" name="' . $x . '">' . fabryki_zatrudnij . '</div>';
                    $status = brak_txt;
                } else {
                    $dzialanie = fabryki_title_no_cash;
                    $status = fabryki_title_no_cash;
                }
            }
            $wynik .= '
					<tr class="body_table">
				<td></td>
				<td style="padding-left: 15px;width:300px;">
					<div class="oknoInz" style="background: url(app/templates/assets/images/inzynierowie.png) -' . $personnelStats['poziomd'] . 'px -' . $personnelStats['piond'] . 'px no-repeat;">
					</div>
					<strong>' . $personnelStats['nazwa'] . '</strong><br><br><br>
					' . $personnelStats['opis'] . '
				</td>
				<td>
					' . $personnelStats['cenamiech'] . ' x ' . $HRstage['HRStage'] . '
				</td>
				<td>
					' . $personnelStats['cena'] . '
				</td>
				<td>
					' . $status . '
				</td>
				<td>
					' . $dzialanie . '
				</td>
				<td></td>
			</tr>';
            $xvn++;
        }
        return $wynik;
    }

    public function loadPromotion()
    {
        $odpowiedz_serwera['dane'] = '';
        $odpowiedz_serwera['bodyLosowanie'] = '';
        $idPromotion = $this->account->checkPromotion($this->account->playerID, $this->world->id);
        if ($idPromotion != 0) {
            $promotion = $this->db->query('SELECT * FROM `listPromotion` WHERE `id`=' . $idPromotion . ' LIMIT 1 ')->fetch();
            $odpowiedz_serwera['title'] = $promotion['title'];
            $statsPromo = $this->db->query('SELECT *, count(*) as `countered` FROM `playerPromotion` WHERE `playerID`=' . $this->account->playerID . ' AND `promotionID`= ' . $idPromotion . ' LIMIT 1')->fetch();
            $data_start = $this->account->dateV('j f Y', $promotion['startTime'], $this->account->lang);
            $data_koniec = $this->account->dateV('j f Y', $promotion['endTime'], $this->account->lang);
            if ($statsPromo['etap'] == 0 OR $statsPromo['countered'] == 0) {
                switch ($promotion['drawCorps']) {
                    case 1:
                        $text = comp_account102 .' '.comp_index63;
                        break;
                    case 2:
                        $text = comp_account102 .' '.comp_index64;
                        break;
                    case 3:
                        $text = comp_account102 .' '.comp_index65;
                        break;
                    case 4:
                        $text = comp_account102 .' '.comp_index66;
                        break;
                    case 5:
                        $text = comp_account102 .' '.comp_index67;
                        break;
                    case 6:
                        $text = comp_account102 .' '.comp_index68;
                        break;
                    case 7:
                        $text = comp_account102 .' '.comp_index69;
                        break;
                }
				
                $odpowiedz_serwera['bodyLosowanie'] .= '
									<div id="wynik_losowania">
									<div id="opis_promocji1">
									' . $promotion['description'] . ' <br>
									'. comp_index70 .' ' . $text . ' !<br>
									'. comp_account59 .' "' . $odpowiedz_serwera['title'] . '" '. comp_index71 .' ' . $data_start . ' '. comp_index72 .' ' . date('G:i', $promotion['startTime']) . '<br> '. comp_index73 .'  ' . $data_koniec . ' '. comp_index72 .' ' . date('G:i', $promotion['endTime']) . '!<br>
								</div>
								<div id="opis_promocji2">
									'. comp_index74 .'?<br>
									'. comp_index75 .' ' . $text . '<br> '. comp_index76 .' '. comp_index79 .'<br>
									'. comp_index77 .'.<br>
									'. comp_index78 .'!<br>
								</div>
								<div id="przycisk_promocji">
									<strong>'. comp_index79 .'</strong>
								</div>';
                $odpowiedz_serwera['bodyLosowanie'] .= '</div>';
            } else {
                if ($statsPromo['etap'] == 1) {//gracz wylosował sobie juz jednostki
                    $odpowiedz_serwera['bodyLosowanie'] .= '<style>
										#przycisk_promocji{line-height:18px;}
									</style>
									<div id="wynik_losowania">
										'. losowanie1 .'!<br>
										<div id="tabela_losowanie">
					<table class="tabela_losowanie">
						<tr>';
                    $tabela_jednostek = unserialize($statsPromo['tabela']);
                    $liczba_jednostek = count($tabela_jednostek);
                    $cena = 0;
                    for ($x = 0; $x <= $liczba_jednostek - 1; $x++) {
                        $statsListPromo = $this->db->query('SELECT * FROM `TacticalData` WHERE id=' . $tabela_jednostek[$x] . ' LIMIT 1')->fetch();
                        $cena += $statsListPromo['cena'];
                        $odpowiedz_serwera['bodyLosowanie'] .= "<td id=\"" . $statsListPromo['id'] . "\" onmouseover=\"document.getElementById('opis_" . $x . "').style.display = 'block' ;\" onmouseout=\"document.getElementById('opis_" . $x . "').style.display = 'none' ;\" class=\"unit-" . $statsListPromo['id'] . "\">
															<div class=\"promocja_jednostki\" id=\"opis_" . $x . "\">
																<div id=\"srobka_left_up\"></div>
																<img src=\"./app/templates/assets/images/" . $statsListPromo['obrazek_duzy'] . "\" alt=\"" . $statsListPromo['nazwa'] . "\"/><br>
																<strong>" . $statsListPromo['nazwa'] . "</strong><br>
															<div id=\"srobka_right_down\"></div>
															</div>
														</td>";
                        $cena_new = $cena;
                        $cena_new1 = round(($cena_new / 15) / 2);
                        //$cena_new1=0;
                    }
                    $odpowiedz_serwera['bodyLosowanie'] .= '</tr>
										</table> '. losowanie2 .' ' . $cena_new . ' '. panel_gry6 .'!
									</div>
									<div id="opis_promocji2">';
                    $odpowiedz_serwera['bodyLosowanie'] .= '<div id="biore_za_gold">'. losowanie3 .' ' . $statsPromo['wartosc'] . ' '. losowanie4 .'</div><div id="nextDraw">'. losowanie5 .'</div></div></div>';
                } else {
                    $odpowiedz_serwera['bodyLosowanie'] .= comp_index80 .' ' . $odpowiedz_serwera['title'] . ' '. comp_index81 .'!';
                }
            }
        }
        echo json_encode($odpowiedz_serwera);
    }

    public function Promotion()
    {
        $dane_json = array();
        $stage = $this->path->post('stage');
        $idPromotion = $this->account->checkPromotion($this->account->playerID, $this->world->id);
        $promotion = $this->db->query('SELECT * FROM `listPromotion` WHERE `id`=' . $idPromotion . ' LIMIT 1 ')->fetch();
        $statsPromo = $this->db->query('SELECT *, count(*) as countered FROM `playerPromotion` WHERE `playerID`=' . $this->account->playerID . ' AND `promotionID`=' . $idPromotion . ' LIMIT 1')->fetch();
        if ($promotion['drawCorps'] == 7) {
            $corps = ' `CorpsId`!=5 ';
        } else {
            $corps = ' `CorpsId` = ' . $promotion['drawCorps'] . ' ';
        }
		
		if ($promotion['drawCorps'] == 5) { // jednostki morskie
            $corps = ' `CorpsId` = 5 ';
        }
		
		
        if ($statsPromo['etap'] == 0 OR $statsPromo['countered'] == 0) {//pierwszy etap, losowanie jednostek
            $dane_json['tabela'] = '<div id="wynik_losowania">
									'. losowanie1 .'<br>
									<div id="tabela_losowanie">
				<table class="tabela_losowanie">
					<tr>';
            if ($this->account->nation == 1) {
                $personnel = '`pole`, `englishman`, `russian`, `frenchman`, `american` ';
            } else {
                $personnel = '`german`, `italian`, `japanese` ';
            }
            $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
			if ($this->account->nation == 1) {
                $warunek_nacja = "(`NationId`=1 ";
                if ($statsUnit['englishman'] == 1) {
                    $warunek_nacja .= "OR `NationId`=2 ";
                }
                if ($statsUnit['russian'] == 1) {
                    $warunek_nacja .= "OR `NationId`=3 ";
                }
                if ($statsUnit['american'] == 1) {
                    $warunek_nacja .= "OR `NationId`=4 ";
                }
                if ($statsUnit['frenchman'] == 1) {
                    $warunek_nacja .= "OR `NationId`=5 ";
                }
                $warunek_nacja .= ")";
                $warunek_losowanie = ' AND ' . $warunek_nacja . ' AND ' . $corps . ' ';
            } else {
                $warunek_nacja = "(`NationId`=7 ";
                if ($statsUnit['italian'] == 1) {
                    $warunek_nacja .= "OR `NationId`=8 ";
                }
                if ($statsUnit['japanese'] == 1) {
                    $warunek_nacja .= "OR `NationId`=9 ";
                }
                $warunek_nacja .= ")";
                $warunek_losowanie = ' AND ' . $warunek_nacja . ' AND ' . $corps . ' ';
            }

            $cena_new = 0;
			for ($x = 1; $x <= 10; $x++) {
                $statsListPromo = $this->db->query('SELECT * FROM `TacticalData` WHERE `rocznik`<= ' . $this->world->WarDateY . '  ' . $warunek_losowanie . ' ORDER BY RAND() LIMIT 1')->fetch();
                $dane_json['tabela'] .= "<td id=\"" . $statsListPromo['id'] . "\" onmouseover=\"document.getElementById('opis_" . $x . "').style.display = 'block' ;\" onmouseout=\"document.getElementById('opis_" . $x . "').style.display = 'none' ;\" class=\"unit-" . $statsListPromo['id'] . "\">
											<div class=\"promocja_jednostki\" id=\"opis_" . $x . "\">
												<div id=\"srobka_left_up\"></div>
												<img src=\"./app/templates/assets/images/" . $statsListPromo['obrazek_duzy'] . "\" alt=\"" . $statsListPromo['nazwa'] . "\"/><br>
												<strong>" . $statsListPromo['nazwa'] . "</strong><br>
											<div id=\"srobka_right_down\"></div>
											</div>
										</td>";
                $cena_new += $statsListPromo['cena'];
                $cena_new1 = round(($cena_new / 15) / 2);
                $tablica[] = $statsListPromo['id'];
            }
				$dane_json['tabela'] .= '</tr>
									</table>'. losowanie2 .' ' . $cena_new . ' '. panel_gry6 .'!
								</div>
								<div id="opis_promocji2">';				
			$dane_json['tabela'] .= '<div id="biore_za_gold">'. losowanie3 .' '.$cena_new1.' '. losowanie4 .'</div><div id="nextDraw">'. losowanie5 .'</div>';	
            
			$tab = serialize($tablica);
			
            $this->db->exec('INSERT INTO `playerPromotion` (playerID,wartosc,tabela,etap,promotionID) VALUES (' . $this->account->playerID . ',' . $cena_new1 . ',\'' . $tab . '\',"1",' . $idPromotion . ') ');
        } else if ($statsPromo['etap'] == 1) {//sprawdzenie, gracz kupuje wylosowane jendostki
            if ($stage == 1) {//gracz przycisnął prycisk kolejnego losowania
                if ($this->account->gold >= 5) {
                    $dane_json['tabela2'] = '<div id="wynik_losowania">
									'. losowanie1 .'<br>
									<div id="tabela_losowanie">
					<table class="tabela_losowanie">
					<tr>';
                    if ($this->account->nation == 1) {
                        $personnel = '`pole`, `englishman`, `russian`, `frenchman`, `american` ';
                    } else {
                        $personnel = '`german`, `italian`, `japanese` ';
                    }
                    $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
                    if ($this->account->nation == 1) {
                        $warunek_nacja = "(`NationId`=1 ";
                        if ($statsUnit['englishman'] == 1) {
                            $warunek_nacja .= "OR `NationId`=2 ";
                        }
                        if ($statsUnit['russian'] == 1) {
                            $warunek_nacja .= "OR `NationId`=3 ";
                        }
                        if ($statsUnit['american'] == 1) {
                            $warunek_nacja .= "OR `NationId`=4 ";
                        }
                        if ($statsUnit['frenchman'] == 1) {
                            $warunek_nacja .= "OR `NationId`=5 ";
                        }
                        $warunek_nacja .= ")";
                        $warunek_losowanie = ' AND ' . $warunek_nacja . ' AND ' . $corps . ' ';
                    } else {
                        $warunek_nacja = "(`NationID`=7 ";
                        if ($statsUnit['italian'] == 1) {
                            $warunek_nacja .= "OR `NationId`=8 ";
                        }
                        if ($statsUnit['japanese'] == 1) {
                            $warunek_nacja .= "OR `NationId`=9 ";
                        }
                        $warunek_nacja .= ")";
                        $warunek_losowanie = ' AND ' . $warunek_nacja . ' AND ' . $corps . ' ';
                    }
                    $cena_new = 0;
                    for ($x = 1; $x <= 10; $x++) {
                        $statsListPromo = $this->db->query('SELECT * FROM `TacticalData` WHERE `rocznik`<=1945 ' . $warunek_losowanie . ' ORDER BY RAND() LIMIT 1')->fetch();
                        $dane_json['tabela2'] .= "<td id=\"" . $statsListPromo['id'] . "\" onmouseover=\"document.getElementById('opis_" . $x . "').style.display = 'block' ;\" onmouseout=\"document.getElementById('opis_" . $x . "').style.display = 'none' ;\" class=\"unit-" . $statsListPromo['id'] . "\">
											<div class=\"promocja_jednostki\" id=\"opis_" . $x . "\">
												<div id=\"srobka_left_up\"></div>
												<img src=\"./app/templates/assets/images/" . $statsListPromo['obrazek_duzy'] . "\" alt=\"" . $statsListPromo['nazwa'] . "\"/><br>
												<strong>" . $statsListPromo['nazwa'] . "</strong><br>
											<div id=\"srobka_right_down\"></div>
											</div>
										</td>";
                        $cena_new += $statsListPromo['cena'];
                        $cena_new1 = round(($cena_new / 15) / 2);
                        $tablica[] = $statsListPromo['id'];
                    }
                    $dane_json['tabela2'] .= '</tr>
									</table>'. losowanie2 .' ' . $cena_new . ' '. panel_gry6 .'!
								</div>
								<div id="opis_promocji2">';
                    $tab = serialize($tablica);
                    $dane_json['cenaGold2'] = $cena_new1;
                    $this->db->exec('UPDATE `playerPromotion` SET `wartosc`=' . $dane_json['cenaGold2'] . ',`tabela`=\'' . $tab . '\' WHERE `playerID`=' . $this->account->playerID . ' AND `promotionID`=' . $idPromotion . ' LIMIT 1');
                    $this->db->exec('UPDATE `accounts` SET `gold`=(`gold`-5)  WHERE `id`=' . $this->account->id . ' LIMIT 1');
                } else {
                    $dane_json['error'] = comp_index82 .'<br> '.panel_gry9;
                }
            } else {
                if ($this->account->gold >= $statsPromo['wartosc']) {
                    $tabela_jednostek = unserialize($statsPromo['tabela']);
                    $liczba_jednostek = count($tabela_jednostek);
                    for ($x = 0; $x <= $liczba_jednostek - 1; $x++) {
                        //budujemy wylosowane jednostki
                        $this->account->buildUnit($tabela_jednostek[$x], $this->account->playerID, 0);
                    }
                    $dane_json['body'] = comp_index83;
                    $this->db->exec('UPDATE `playerPromotion` SET `etap`=2 WHERE `playerID`=' . $this->account->playerID . ' AND `promotionID`=' . $idPromotion . '  LIMIT 1');
                    $this->db->exec('INSERT INTO `financialOperations` ( accountID, goldValue, operation, changeDate ) values (' . $this->account->id . ',' . $statsPromo['wartosc'] . ',"kupno jednostek w promocji (' . $statsPromo['wartosc'] . ')",' . $this->init->time . ' ) ');
                    $this->db->exec('UPDATE `accounts` SET `gold`=(`gold`-' . $statsPromo['wartosc'] . ')  WHERE `id`=' . $this->account->id . ' LIMIT 1');
                    $dane_json['gold'] = $statsPromo['wartosc'];
                } else {
                    $dane_json['error'] = losowanie8 .'<br>'. panel_gry9;
                }
            }
        } else {
            $dane_json['error'] = losowanie8 .'<br> '.panel_gry9;
        }
        echo json_encode($dane_json);
    }

    public function changeGold()
    {
        $blockChangeGold = array();
        $stage = $this->path->post('stage'); //etap wymiany złota 1: przygotowanie formularza, 2:wymiana złota na kredyty
        $arrData = $this->path->post('arrData'); //ilość złota
        if (!preg_match("/^[\d]{1,11}$/i", $stage)) $this->error->printError('Niepoprawne dane', 'mapa');
        if (!preg_match("/^[\d]{1,11}$/i", $arrData)) $this->error->printError('Niepoprawne dane', 'mapa');
        $blockChangeGold['title'] = wymiana_zlota_txt;
        if ($stage == 1) {
            if ($this->account->gold > 0) {
                $blockChangeGold['body'] = '<div id="wymiana_zlota"><div id="goldSmall"></div><div id="creditsSmall"></div>' . replace1 . '<br>

					<script>
					var currentValue_gold = $(\'#currentValue_gold\');
							var currentValue_credits = $(\'#currentValue_credits\');
							$(\'#defaultSlider\').change(function(){
								currentValue_gold.html(' . $this->account->gold . '-this.value);
								currentValue_credits.html(this.value*15);
							});
							$(\'#defaultSlider\').change();
					</script>
					<div id="golden">
						<span id="currentValue_gold">' . $this->account->gold . '</span>
					</div><div id="suwak"><input id="defaultSlider" type="range" min="0" max="' . $this->account->gold . '" value="0"/></div>
					<div id="credits">
						<span id="currentValue_credits">0</span>
					</div>
					<div id="replace_button">
						' . replace4 . '
					</div>
				</div>
				';
                $blockChangeGold['gold'] = $this->account->gold;
            } else {
                $blockChangeGold['body'] = replace5; //"nie masz złota do wymiany";
                $blockChangeGold['gold'] = 0;
            }
        } else if ($stage == 2) {//wymiana złota
            if ($this->account->gold >= $arrData) {
                $newChangeGold = $arrData * 15;
                $this->db->exec('INSERT INTO `financialOperations` ( accountID, goldValue, operation, changeDate ) values (' . $this->account->id . ',' . $arrData . ',"wymiana złota na kredyty",' . $this->init->time . ' ) ');
                $this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`+' . $newChangeGold . '),`gold`=(`gold`-' . $arrData . ')  WHERE `id`=' . $this->account->id . ' ');
                $blockChangeGold['body'] = replace_gold1 .' ' . $arrData . ' '. replace_gold2 .' ' . $newChangeGold . ' '. panel_gry6;
                $blockChangeGold['gold'] = $arrData;
                $blockChangeGold['PremiumCurrency'] = $newChangeGold;
                if ($this->account->gold == $arrData) {
                    $blockChangeGold['gold0'] = 1;
                    $blockChangeGold['gold_info'] = panel_gry8;
                    $blockChangeGold['bank'] = panel_gry9;
                }
            } else {
                $this->error->printError( losowanie8 .'<br> '.panel_gry9 , 'mapa');
            }
        }
        echo json_encode($blockChangeGold);
    }

    public function editEngeener()
    {
        $data = $this->path->post('data');
        if (!preg_match("/^[\d]{1,11}$/i", $data)) $this->error->printError( comp_index32, 'mapa');

        switch ($data) {
            case '1':
                $personnel = '`pole`';
                break;
            case '2':
                $personnel = '`englishman`';
                break;
            case '3':
                $personnel = '`russian`';
                break;
            case '4':
                $personnel = '`american`';
                break;
            case '5':
                $personnel = '`frenchman`';
                break;
            case '6':
                $personnel = '`poleSonar`';
                break;
            case '7':
                $personnel = '`german`';
                break;
            case '8':
                $personnel = '`italian`';
                break;
            case '9':
                $personnel = '`japanese`';
                break;
            case '10':
                $personnel = '`germanSonar`';
                break;
        }
        $statsUnit = $this->db->query('SELECT ' . $personnel . ' FROM `PlayerPersonnel` WHERE `playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
        $statsUnit1 = $this->db->query('SELECT count(*) FROM `hiringPersonnel` WHERE `playerID`=' . $this->account->playerID . ' AND `enginner`=' . $data . '')->fetch();
        $dataInz = array();
        if ($statsUnit[0] == 1) {//znak, że gracz zwalnia inzyniera
            if ($data == 1 OR $data == 7) {
                $dataInz['error'] = comp_index84 .'!';
            } else {
                if( (int)$statsUnit1[0] == 0) {
                    $this->db->exec('INSERT INTO `hiringPersonnel` ( playerID, HRStage, enginner ) values (' . $this->account->playerID . ',1,' . $data . ' ) ');
                } else {
                    $this->db->exec('UPDATE `hiringPersonnel` SET `HRStage`=(`HRStage`+1) WHERE `playerID`=' . $this->account->playerID . ' AND `enginner`=' . $data . ' ');
                }
                $this->db->exec('UPDATE `PlayerPersonnel` SET ' . $personnel . '=0 WHERE `playerID`=' . $this->account->playerID . ' ');
                $this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency`+1500) WHERE `id`=' . $this->account->playerID . ' ');
                //usuwam jednostki danego inżyniera z tabeli budowanie
                $this->db->exec('DELETE `ub`.* FROM `UnitBuild` as `ub` LEFT JOIN `TacticalData` as `td` ON `td`.`id` = `ub`.`tacticalDataID` WHERE `ub`.`playerID`=' . $this->account->playerID . ' AND `td`.`NationId`=' . $data . ' ');
                $dataInz['softCurrency'] = 1500;
                $dataInz['alert'] = comp_index85;
                $dataInz['buttonText'] = fabryki_zatrudnij;
            }
        } else {//gracz zatrudnia inżyniera
            if ($this->account->softCurrency + $this->account->PremiumCurrency >= 2000) {
                if ($this->account->softCurrency >= 2000) {
                    $this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-2000) WHERE `id`=' . $this->account->playerID . ' ');
                    $dataInz['softCurrency'] = 2000;
                    $dataInz['globalCurrency'] = 2000;
                    $dataInz['PremiumCurrency'] = 0;
                } else if ($this->account->softCurrency < 2000 AND $this->account->PremiumCurrency >= (2000 - $this->account->softCurrency)) {
                    $this->db->exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this->account->playerID . ' ');
                    $koszt = 2000 - $this->account->softCurrency;
                    $this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $koszt . ') WHERE `id`=' . $this->account->id . ' ');
                    $dataInz['softCurrency'] = $this->account->softCurrency;
                    $dataInz['PremiumCurrency'] = $koszt;
                    $dataInz['globalCurrency'] = $this->account->softCurrency + $koszt;
                }
                $this->db->exec('UPDATE `PlayerPersonnel` SET ' . $personnel . '=1 WHERE `playerID`=' . $this->account->playerID . ' ');
                $dataInz['alert'] = comp_index86;
                $dataInz['edit'] = 1;
                $dataInz['buttonText'] = zwolnij_txt;
                if( (int)$statsUnit1[0] == 0) {
                    $this->db->exec('INSERT INTO `hiringPersonnel` ( playerID, HRStage, enginner ) values (' . $this->account->playerID . ',1,' . $data . ' ) ');
                }
            } else {
                $dataInz['error'] = comp_index87 .', '. comp_index88;
            }
        }
        echo json_encode($dataInz);
    }

    public function loadTutorial()
    {
        $dane = array();
        $dane['title'] = comp_index89;
		$dane['goldNagrodaTXT'] = '';
		$goldNagroda = 0;
        switch ($this->account->tutorialStage) {
            case 0:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
				<div id="tresc_samouczek">
				' . mapa_tut2 . '
					<br><br><div id="dalej" name="1"><div id="dalej_txt">' . samouczek_dalej . '</div></div></div>';
                break;
            case 1:
				$dane['tutStage'] = 1;
                //gracz miał zadanie wystawić wszyskie jendostki, sprawdzam czy to uczynił
                $ileDoWystwienia = $this->db->query('SELECT count(*) FROM `units` WHERE `playerID`=' . $this->account->playerID . ' AND `x`=0 AND `y`=0 ')->fetch();

                if ( (int)$ileDoWystwienia[0] > 0) {
                    $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">' . samouczek8 . ' '. comp_index100 .'
                        <br></div>';
						$dane['heightWindow'] =360;
                } else {
                    $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek"> ' . mapa_tut1 . '
                        <br></div>';
						$dane['heightWindow'] =360;
                    $this->db->exec('UPDATE `accounts` SET `tutorialStage`=2 WHERE `id`=' . $this->account->id . ' LIMIT 1');
					$dane['tutStage'] = 2;
					$goldNagroda = 20;
                }
                break;
            case 2:
                //gracz miał zadaenie wystawić wszyskie jendostki, sprawdzam czy to uczynił
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">' . mapa_tut1a . '
                        <br></div>';
						$dane['heightWindow'] =170;
						$dane['tutStage'] = 2;
                break;
            case 3:
                //gracz miał zadaenie wystawić wszyskie jendostki, sprawdzam czy to uczynił
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index90 .'. <br><br>' . samouczek9 . '<br></div>';
					$dane['heightWindow'] =360;
					$dane['tutStage'] = 3;
				break;
            case 4:
                //gracz miał zadaenie wystawić wszyskie jendostki, sprawdzam czy to uczynił
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek"> '. comp_index90 .', '. comp_index91 .'.
                    </div>';
					$dane['heightWindow'] =360;
					$dane['tutStage'] = 4;
                break;
            case 5:
                //gracz potrafi przyspieszać jednostki, czas pokazać, że jendostki mozna połączyć
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index92 .'.<br>
                    </div>';
					$dane['heightWindow'] =190;
                //$this->db->exec('UPDATE `accounts` SET `tutorialStage`=6 WHERE `id`=' . $this->account->id . ' LIMIT 1');
				$dane['tutStage'] = 5;
                break;
            case 6:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index93 .'!<br>
                    </div>';
					$dane['heightWindow'] =240;
					$dane['tutStage'] = 6;
                break;
            case 7:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index94 .'!<br>'. comp_index100 .'
                    </div>';
					$dane['heightWindow'] = 505;
					$dane['tutStage'] = 7;
                break;
            case 8:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index95 .'!<br>
                </div>';
				$dane['heightWindow'] =440;
				$dane['tutStage'] = 8;
                break;
            case 9:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                                <div id="tresc_samouczek">'. comp_index96 .'!<br>
                            </div>';
							$dane['heightWindow'] =440;
							$dane['tutStage'] = 9;
			break;
			case 10:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index97 .'.<br>
                      ' . mapa_tut3 . '
                        <br>
						'. comp_index98 .' <strong>'. comp_index99 .'</strong>.<br>
						To okno możesz zamknąć.<br>
						</div>';
					$dane['heightWindow'] = 440;
					$dane['tutStage'] = 10;
			break;		
			case 11:
                $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">' . mapa_tut4 . '
                        <br></div>';
						$dane['heightWindow'] = 440;
                $this->db->exec('UPDATE `accounts` SET `tutorialStage`=12 WHERE `id`=' . $this->account->id . ' LIMIT 1');
				$goldNagroda = 50;
				$dane['tutStage'] = 12;
			break;
            default:
				$dane['bodySamouczek'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">Samouczek zakończony.<br>
                    Zaglądaj tutaj co jakiś czas, jeśli dodamy jakieś zadania- bedą one do zrealizowania.<br><br>
					Zadania do realizacji<br><br>
					9. BUDOWA SZTABU zaliczone / pokaż<br>
					8. ZNAJOMOŚĆ JEDNOSTEK: SAPER, SZTAB, DESANT zaliczone / pokaż<br>
					7. DODAWANIE graczy jako przyjaciół zaliczone / pokaż<br>
					6. ŁACZENIE JEDNOSTEK zaliczone / pokaż<br>
					5. KUMULACJA TUR zaliczone / pokaż<br>
					4. RUCH JEDNOSTKI zaliczone / pokaż<br>
					3. BUDOWANIE JEDNOSTEK zaliczone / pokaż<br>
					2. WYSTAWIANIE JEDNOSTEK STARTOWYCH zaliczone / pokaż<br>
					1. BUDOWA MIASTA zaliczone / pokaż<br>
                        <br></div>';
				$dane['heightWindow'] =360;
        }
		$dane['goldNagroda'] = 0;
		if ( $goldNagroda > 0 )
		{
			$dane['goldNagrodaTXT'] = comp_index101 .' '.$goldNagroda.' '. sztuk_zlota_txt .'!<br>
					<div id="getGold">'. comp_account75 .'</div>';
			$this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', '.$goldNagroda.' )');
			$dane['goldNagroda'] = $goldNagroda;
		}
		
		
		
        echo json_encode($dane);
    }

	public function uploadTutorial()
    {
       $idTut = $this->path->post('id');
	   $data = [];
	   $data['title'] = comp_index89;
	   switch($idTut){
		    case 6:
			    $dane['bodySamouczek'] = '<div id="tutorial_1"></div>
					<div id="tresc_samouczek">'. comp_index93 .'!<br>
					</div>';
				$dane['heightWindow'] =240;
				$dane['tutStage'] = 6;
				$dane['goldNagroda'] = 0;
				$dane['goldNagrodaTXT'] = '';
				$this->db->exec('UPDATE `accounts` SET `tutorialStage`=6 WHERE `id`=' . $this->account->id . ' LIMIT 1');
			break;
	    }
        echo json_encode($dane);
    }
	
    public function searchAnswer()
    {
        $dane = array();
        $frazy = $this->path->post('frazy');
        $dane['title'] = comp_index102;
        $dane['body'] = '<div id="simple-accordion" class="lista_pytan">';
        //$this -> db -> exec('ALTER TABLE `poradnik` ADD FULLTEXT(pytanie,odpowiedz) ');
        $searchAnswers = $this->db->query('SELECT *, MATCH(`pytanie`,`odpowiedz`) AGAINST ("' . $frazy . '") AS `score` FROM `poradnik` WHERE MATCH(`pytanie`,`odpowiedz`) AGAINST ("' . $frazy . '") ORDER BY `score` DESC');
        foreach ($searchAnswers as $daneFaq) {
            $odp = '<h1 class="vertical_accordion_toggle">' . $daneFaq['pytanie'] . '</h1>
							<div class="vertical_accordion_content">
								<p>
									' . $daneFaq['odpowiedz'] . '
								</p>
							</div>';
            $dane['body'] .= $odp;
        }
        $dane['body'] .= '</div>';
        echo json_encode($dane);
    }

    public function showToken()
    {
        //kod rysujący kod do przepisania w rejestracji/logowaniu
        $img_width = 120;
        $img_height = 20;
        $string = token;
        $im = imagecreate($img_width, $img_height);

        $bg_color = imagecolorallocate($im, 163, 163, 163);
        $font_color = imagecolorallocate($im, 252, 252, 252);
        $grid_color = imagecolorallocate($im, 31, 0, 0);
        $border_color = imagecolorallocate($im, 174, 174, 174);

        imagefill($im, 1, 1, $bg_color);

        for ($i = 0; $i < 1000; $i++) {

            $rand1 = rand(0, $img_width);
            $rand2 = rand(0, $img_height);
            imageline($im, $rand1, $rand2, $rand1, $rand2, $grid_color);

        }

        $x = rand(5, $img_width / (14 / 2));

        imagerectangle($im, 0, 0, $img_width - 1, $img_height - 1, $border_color);

        for ($a = 0; $a < 14; $a++) {

            imagestring($im, 3, $x, rand(3, $img_height / 4), substr($string, $a, 1), $font_color);
            $x += (5 * 1.5); #odstęp

        }
        header("Content-type: image/gif");
        imagegif($im);
        imagedestroy($im);
    }

    public function remindPassword()
    {
        $objHelper = init::getFactory()->getService('helper');

        // DANE Z FORMULARZA
        $email = $this->path->post('mail_rev');

        //sprawdzam, czy emaile mają format maila
        if ($objHelper->checkEmail($email) === false) $this->error->printError(incorect_email_txt, 'komunikat');

        //gracz o podanym loginie nie istnieje, więc sprawdzam czy ktoś rejestrował się na podany email
        $arrData = $this->db->query('SELECT `id`,`login` FROM `accounts` WHERE `email`="' . $email . '" LIMIT 1')->fetch();
        if (!$arrData['id']) $this->error->printError( comp_index103, 'komunikat');
        //przygotowanie linka do zmiany hasła
        $haslo =  SYSTEM_PASSWORD;
        $strActivateID = md5(time());
        $t["email"] = $email;
        $t["user_id"] = $arrData['id'];
        $t["strActivateID"] = $strActivateID;
        $d['dane'] = base64_encode(serialize($t));
        $d['jel'] = md5($haslo . $d['dane']);

        $strEmail =
            przypomnij14 . ' ' . $arrData['login'] . '!<br>
			<a href=' . ADRES . '/zmien-haslo/' . $d['dane'] . '/' . $d['jel'] . '><strong>' . przypomnij10 . '</strong></a> ' . przypomnij11 . '.<br><br><br>';
        //$this -> db -> exec('INSERT INTO `sendedLinks` (`id`, `accountID`, `type`, `date`) VALUES (\'' . $strActivateID . '\', ' . $arrData['id'] . ', \'CHANGE_PASS\', ' . $this -> init -> time . ')');
        // wysyłam email
        $objHelper->sendEmail($strEmail, przypomnij8, $email);
        $this->error->printError(przypomnij12, 'komunikat');

    }

    public function changePassword()
    {
        $dane = $this->path->get('dane');
        $jel = $this->path->get('jel');
        $haslo =  SYSTEM_PASSWORD;
        if ($jel <> md5($haslo . $dane)) {
            $this->error->printError( comp_index32, 'komunikat');
        } else {
            $t = unserialize(base64_decode($dane));
            // dalsza obróbka na poprawnych danych
            $email_gracza = $t["email"];
            $accountID = $t["user_id"];
            $strActivateID = $t["strActivateID"];
            $arrData = $this->db->query('SELECT `login` FROM `accounts` WHERE `id`=' . $accountID . ' LIMIT 1')->fetch();
            $this->tmplData['file'] = 'zmien-haslo.html';
            $this->tmplData['variables']['title'] = przypomnij1 .' ' . $this->tmplData['variables']['title'];
            $this->tmplData['variables']['user'] = $arrData['login'];
            $this->tmplData['variables']['dane'] = $dane;
            $this->tmplData['variables']['jel'] = $jel;
        }
    }

    public function savePassword()
    {
        $objHelper = init::getFactory()->getService('helper');
        $dataArr = array();
        $dane = $this->path->post('dane');
        $jel = $this->path->post('jel');
        $firstPass = $this->path->post('firstPass');
        $secondPass = $this->path->post('secondPass');
        $token = $this->path->post('token');
        $haslo =  SYSTEM_PASSWORD;
        $dataArr['error'] = false;
        $dataArr['info'] = false;
        if ($jel <> md5($haslo . $dane)) {
            $dataArr['error'] = comp_index32;
        } else {
            $t = unserialize(base64_decode($dane));
            // dalsza obróbka na poprawnych danych
            $email_gracza = $t["email"];
            $accountID = $t["user_id"];
            if ($token != token) $dataArr['error'] = incorrect_token_txt;
            //emaile sa jednakowe, maja format maila więc sprawdzam czy podane hasła sa jednakowe
            if ($firstPass !== $secondPass) $dataArr['error'] = haslo_wrong_txt;
            if (strlen($firstPass) < 5) $dataArr['error'] = comp_index3;
            if (!$dataArr['error']) {
                $password = $this->session->hashPassword($firstPass); /// haszujemy hasło
                $this->db->exec('UPDATE `accounts` SET `password`="' . $password . '" WHERE `id`=' . $accountID . ' LIMIT 1');
                $arrData = $this->db->query('SELECT `login` FROM `accounts` WHERE `id`=' . $accountID . ' LIMIT 1')->fetch();
                $dataArr['info'] = changePassEnd . ', login=' . $arrData['login'] . ', email=' . $email_gracza;
                $strEmail =
                    przypomnij14 . ' ' . $arrData['login'] . '!<br>
					'. comp_account1 .'.';
                //$this -> db -> exec('INSERT INTO `sendedLinks` (`id`, `accountID`, `type`, `date`) VALUES (\'' . $strActivateID . '\', ' . $arrData['id'] . ', \'CHANGE_PASS\', ' . $this -> init -> time . ')');
                // wysyłam email
                $objHelper->sendEmail($strEmail, comp_account2, $email_gracza);
            }
        }
        echo json_encode($dataArr);
    }

    public function showTopBox()//kod do pokazania po kliknięciu w button topBox
    {
        $data = $this->path->post('data');
        $dataArr = array();

        if ($data == 1) {
            $dataArr['title'] = comp_index104;
            $topBox = $this->db->query('SELECT `account`.`login`,`account`.`id`,`account`.`globalPoints`,`player`.`points`, `player`.`nation`, `player`.`id` AS `pid` FROM `players` AS `player` '
                . 'LEFT JOIN `accounts` AS `account` ON `account`.`id`=`player`.`accountID`  '
                . ' WHERE `player`.`worldID`=' . $this->world->id . ' AND `player`.`accountID`!=36 AND `player`.`accountID`!=37'
                . ' ORDER BY `points` DESC');
            $dataArr['body'] = '
				<div id="tableTopBox">
					<table class="topBox" border="1">
						<tr>
							<td>'. comp_index105 .'</td><td></td><td>'. profil2 .'</td><td></td><td>'. profil3 .'</td><td></td><td>GLOBAL POINTS</td><td></td><td>'. statystyki_nacja .'</td><td></td><td>'. comp_index106 .'</td>
						</tr>
			';
            $i = 1;
            $arrTrustedPlayers = array();
            $arrTrusted = $this->db->query('SELECT `trustedPlayer` FROM `trustedPlayers` WHERE `playerID`=' . $this->account->playerID . ' ');
            foreach ($arrTrusted as $rowTDA) {
                $arrTrustedPlayers[] = $rowTDA['trustedPlayer'];
            }
            $arrTrusted->closeCursor();
            foreach ($topBox as $tableTopBox) {
                if ($i % 2 == 1) {
                    $bg = 'first';
                } else {
                    $bg = 'second';
                }
                if ($tableTopBox['pid'] != $this->account->playerID) {
                    if (in_array($tableTopBox['pid'], $arrTrustedPlayers) == false) {
                        $tru = 'trusted" id="' . $tableTopBox['pid'] . '" >' . mapa_radio_add;
                    } else {
                        $tru = 'trusted" id="' . $tableTopBox['pid'] . '" >' . sztaby_usun;
                    }
                } else {
                    $tru = '">';
                }
                if ($tableTopBox['login'] == $this->account->login) {
                    $tableTopBox['login'] = '<strong>' . $tableTopBox['login'] . '</strong>';
                }
                if ($tableTopBox['nation'] == 1) {
                    $nation = '<div class="polFlag" title="' . mapa_polak . '"></div>';
                } else {
                    $nation = '<div class="niemFlag" title="' . mapa_niemiec . '"></div>';
                }
                $dataArr['body'] .= '<tr style="border-top: 1px solid #6b553c; border-bottom: 1px solid #6b553c;">
										<td class="' . $bg . '">' . $i . '</td><td class="' . $bg . '"></td><td class="' . $bg . ' profil_button" num="' . $tableTopBox['id'] . '">' . $tableTopBox['login'] . '</td><td class="' . $bg . '"></td><td class="' . $bg . '" >' . round($tableTopBox['points'], 0) . '</td><td class="' . $bg . '"></td><td class="' . $bg . '" >' . $tableTopBox['globalPoints'] . '</td><td class="' . $bg . '"></td><td class="' . $bg . '" >' . $nation . '</td><td class="' . $bg . '"></td><td class="' . $bg . ' ' . $tru . '</td>
									</tr>';
                $i++;

            }
        } else if ($data == 2) {
            $dataArr['title'] = comp_index107;
            $topBox1 = $this->db->query('SELECT `login`,`id`,`globalPoints` FROM `accounts` WHERE `id`!=36 AND `id`!=37 ORDER BY `globalPoints` DESC');
            $dataArr['body'] = '
				<div id="tableTopBox">
					<table class="topBox" border="1">
						<tr>
							<td>'. comp_index105 .'</td><td></td><td> '. profil2 .'</td><td></td><td></td><td>GLOBAL POINTS</td><td></td><td></td>
						</tr>
			';
            $ii = 1;

            foreach ($topBox1 as $tableTopBox1) {
                if ($ii % 2 == 1) {
                    $bg = 'first';
                } else {
                    $bg = 'second';
                }
                if ($tableTopBox1['login'] == $this->account->login) {
                    $tableTopBox1['login'] = '<strong>' . $tableTopBox1['login'] . '</strong>';
                }
                $dataArr['body'] .= '<tr style="border-top: 1px solid #6b553c; border-bottom: 1px solid #6b553c;">
										<td class="' . $bg . '">' . $ii . '</td><td class="' . $bg . '"></td><td class="' . $bg . ' profil_button" num="' . $tableTopBox1['id'] . '">' . $tableTopBox1['login'] . '</td><td class="' . $bg . '"></td><td class="' . $bg . '"></td><td class="' . $bg . '" >' . $tableTopBox1['globalPoints'] . '</td><td class="' . $bg . '"></td><td class="' . $bg . '"></td>
									</tr>';
                $ii++;

            }

        }

        $dataArr['body'] .= '</table>
		</div>';
        echo json_encode($dataArr);
    }

    public function trustEdit()//kod do pokazania po kliknięciu w button topBox
    {
        $num = $this->path->post('num');
        $dataArr = array();
        $trustPlayer = $this->db->query('SELECT `trust`.`id`,`players`.`worldID` FROM `players` AS `players`'
            . 'LEFT JOIN `trustedPlayers` AS `trust` ON ( `players`.`id`=`trust`.`playerID` AND  `trust`.`trustedPlayer`=' . $num . ' )
			WHERE players.id=' . $this->account->playerID . ' LIMIT 1')->fetch();
        if (!isset($trustPlayer['id'])) {
            if ($trustPlayer['worldID'] == $this->world->id) {
                //dodaję gracza zaufanego
                $this->db->exec('INSERT INTO `trustedPlayers` ( playerID, trustedPlayer ) values (' . $this->account->playerID . ',' . $num . ' ) ');
                $dataArr['text'] = sztaby_usun;
            } else {
                $this->error->printError( mess_adresatp_alert, 'mapa');
            }
        } else {
            //usuwam gracza z listy zaufanych
            $this->db->exec('DELETE FROM `trustedPlayers` WHERE `trustedPlayer`=' . $num . ' AND `playerID`=' . $this->account->playerID . ' LIMIT 1');
            $dataArr['text'] = mapa_radio_add;
        }
        $samouczek = array();
        $samouczek['aktiv'] = 0;
        $samouczek['playerID'] = $this->account->playerID;
        if ($this->account->tutorialStage == 9) {
            $this->db->exec('UPDATE `accounts` SET `tutorialStage`=10 WHERE `id`=' . $this->account->id . ' LIMIT 1');
            $samouczek['tresc'] = '<div id="tutorial_1"></div>
                    <div id="tresc_samouczek">'. comp_index97 .'.<br>
                      ' . mapa_tut3 . '
                        <br>
						'. comp_index98 .': <strong>'. comp_index99 .'</strong>.<br>
						'. comp_index100 .'.<br>
						</div>';
            $samouczek['aktiv'] = 1;
			$samouczek['tutStage'] = 9;
        }
        $dataArr['samouczek'] = $samouczek;
        echo json_encode($dataArr);
    }

    public function loadAllPlayers()//kod ładje nicki graczy po kliknięciu w przycisk pokaż listę graczy
    {
        $dataArr = array();
        $dataArr['title'] = comp_index107;
        $dataArr['playerList'] = '<div class="box">
					<table style="width:100%;">
                        ';
        $list = $this->db->query('SELECT * FROM `accounts` WHERE `id`!=36 AND `id`!=37 ORDER BY globalPoints DESC');
        foreach ($list as $lp1) {
            if($lp1['SponsorAccount'] > $this -> init ->time AND $lp1['mini'] != ''){
				$avatar = $lp1['mini'];
			}else{
				$avatar = 'avatary/mini/szwejkmini.jpg';
			}
			
            $dataArr['playerList'] .= '<tr class="gracz">
									<td class="rect" style="background:url(../' . $avatar . ') no-repeat;"></td><td><h3 class="profil_button" num="' . $lp1['id'] . '">' . $lp1['login'] . '</h3></a></td><td><div class="star"></div></td><td><p>' . $lp1['globalPoints'] . ' p.</p></td></tr>';
        }
        $dataArr['playerList'] .= '</table></div>';
        echo json_encode($dataArr);
    }

    public function showCityData()
    {
		$objHelper = init::getFactory()->getService('helper');
		$revData = [];
			// sprawdzam czy jakiś budynek nie jest już ukończony
			$addTB = $this->db->query('SELECT *, count(*) as `counted` FROM `buildingsOnBuild` WHERE `playerID`=' . $this->account->playerID . ' AND `timeToEnd` < '. $this->init->time.' ')->fetch();
			if( $addTB['counted'] > 0 ){
				$addSingleBuild = $this->db->query('SELECT count(*) FROM `building` WHERE `playerID`=' . $this->account->playerID . ' ')->fetch();
				// sprawdzamy cz gracz ma dodany już rekord budowy biura rozwoju
				switch($addTB['buildID'] ){
					case 1:
						$tab = '`developmentOffice`';
					break;
					case 2:
						$tab = '`materialTechnology`';
					break;
					case 3:
						$tab = '`optimizationStorage`';
					break;
					case 4:
						$tab = '`productionOptimization`';
					break;
					case 5:
						$tab = '`materialsScience`';
					break;
				}
				
				if( (int)$addSingleBuild[0] > 0 ){
					$this->db->exec('UPDATE `building` SET '. $tab .' = ( '. $tab .' +1 ) WHERE `playerID`=' . $this->account->playerID . ' ');
				}else{
					 $this->db->query('INSERT INTO `building` (`playerID`, '. $tab .') values ('. $this->account->playerID .', 1 )');
				}
				$this->db->exec('DELETE FROM `buildingsOnBuild` WHERE `playerID`=' . $this->account->playerID . ' ');
			}
			
			// sprawdzam poziom rozwiniętych budynków
			$buildingsData = $this->db->query('SELECT * FROM `building` WHERE `playerID`= '. $this->account->playerID .' ')->fetch();
			$playerData = $this->db->query('SELECT `rawMaterials` FROM `players` WHERE `id`= '. $this->account->playerID .' ')->fetch();
			
			$developmentOffice      = $buildingsData['developmentOffice'];
			$materialTechnology     = $buildingsData['materialTechnology'];
			$optimizationStorage    = $buildingsData['optimizationStorage'];
			$productionOptimization = $buildingsData['productionOptimization'];
			$materialsScience       = $buildingsData['materialsScience'];

			$timer1 = $timer2 = $timer3 = $timer4 = $timer5 = 0;
			$revData['time']  = 0;
			$revData['num']   = 0;
			$revData['numID'] = 0;
			$revData['num']   = 0;
			$buildingsOnBuildData = $this->db->query('SELECT * FROM `buildingsOnBuild` WHERE `playerID`= '. $this->account->playerID .' ');
				foreach ($buildingsOnBuildData as $singleBuild ) {
					if( $singleBuild['timeToEnd'] > $this->init->time ){
						switch( $singleBuild['buildID'] ){
							case 1:
								$timer1 = $singleBuild['timeToEnd'];
							break;
							case 2:
								$timer2 = $singleBuild['timeToEnd'];
							break;
							case 3:
								$timer3 = $singleBuild['timeToEnd'];
							break;
							case 4:
								$timer4 = $singleBuild['timeToEnd'];
							break;
							case 5:
								$timer5 = $singleBuild['timeToEnd'];
							break;
						}
					}else{
						//robimy update fabryki
						
						
						
						
					}
				}
			
				$b = $developmentOffice * 6;
				$procent=$b/100;
				
			$revData['cityPlane'] = '
				<div id="closeCity">Wróć na mapę</div>
				<div id="technologyBlock">
					<div id="developmentOffice">
						BIURO ROZWOJU '. $developmentOffice .'<br>
						<div class="infoCity" title="Aktualny poziom skraca badania o '. $b .' %.<br>
						Biuro odpowiada za rozwój technologii które pozawalają zwiększyć przewagę na polu boju!<br>
						Każdy poziom BIURA ROZWOJU skraca czas opracowywania technologii o 6%<br>">i</div>';
						if(  $developmentOffice == 10 ){
							$revData['cityPlane'] .= 'Technologia osiągnęła maksymalny poziom';
						}else if( $timer1 > 0 ){
							$revData['cityPlane'] .= '<div id="buildUp1" class="buildUpTB" num="1">'. $objHelper -> liczCzas( ($timer1-$this->init->time) ) .'</div>';
							$revData['time'] = $timer1;
							$revData['numID'] = 1;
							$revData['num'] = $this -> account -> playerID . 1;
						}else{
							// obliczamy koszt rozbudowy
							$cost = round(50+((50/35)*$developmentOffice * $this -> account -> rankLevel ));
							// obniżamy o odpowiedni procent
														
							$time = round( ( 8000*(2*$developmentOffice+1)-( ( 8000*(2*$developmentOffice+1) ) * $procent ) ) );
							//$time = 3600;
							$revData['cityPlane'] .= 'Koszt:<br>';
							if( $cost < ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ){
							// gracz ma potrzebne kredyty
								$revData['cityPlane'] .= $cost .' kredytów  i ';
							}else{
								$revData['cityPlane'] .= '<span class="red"  title="Aby zdobyć kredyty zaczekaj na żołd lub niszcz jednostki wroga">Koszt: '. $cost .' kredytów</span> i ';
							}
								
							if( $this -> account -> points > $cost ){
								// gracz ma punkty
								$revData['cityPlane'] .= $cost .' punktów';
							}else{
								$revData['cityPlane'] .= '<span class="red" title="Aby zdobyć punkty musisz budować jednostki bojowe<br> lub atakować jednostki wroga">'. $cost .' punktów</span>';
							}
							$revData['cityPlane'] .= '<br>Czas badania: '. $objHelper -> liczCzas( $time ) .'<br>';
							
							
							if( ( $cost < ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ) AND $this -> account -> points > $cost ) {
								$revData['cityPlane'] .= '<div class="buildUpTB"  id="buildUp1" num="1">PRZYCISK ROZBUDUJ</div>';
							}else{
								$revData['cityPlane'] .= '<br>Aby rozbudować tą technologię spełnij powyższe wymagania';
							}
						}
						
						$bMat = (int)$materialTechnology * 24;
						$procentMat = $bMat/100;
						$startMat = 0.1;
						$przyrostNaGodzine = round( ( ( $startMat + ( $startMat * $procentMat ) )*3600) );
					$revData['cityPlane'] .= '</div>
					<div id="materialTechnology">
						TECHNOLOGIA SUROWCÓW '. $materialTechnology .'<br>
						<div class="infoCity" title="Badania nad coraz wydajniejszym procesem produkcji surowców<br> to kluczowa dziedzina podczas działań wojennych.<br>
						Każdy poziom tej technologii zwiększa o 24% poziom przyrostu surowców<br>
						<table>
							<tr>
								<td>poziom</td><td>przyrost</td>
							</tr>						
						';
						
						for ($x = 1; $x < 11; $x ++ )
						{
							$bFor = (int)$x * 24;
							$procentFor = $bFor/100;
							$startFor = 0.1;
							$przyrostNaGodzine = round( ( ( $startFor + ( $startFor * $procentFor ) )*3600) );
							if( ( $x ) == $materialTechnology){
								$revData['cityPlane'] .= '<tr><td>'. $x .'</td><td><strong>'. $przyrostNaGodzine .'</strong></td></tr>';
							}else{
								$revData['cityPlane'] .= '<tr><td>'. $x .'</td><td> '. $przyrostNaGodzine .'</td></tr>';
							}
						}
						
						$revData['cityPlane'] .= '</tr></table>">i</div>
						';
						if(  $materialTechnology == 10 ){
							$revData['cityPlane'] .= 'Technologia osiągnęła maksymalny poziom';
						}else if( $timer2 > 0 ){
							$revData['cityPlane'] .= '<div id="buildUp2" class="buildUpTB" num="2">'. $objHelper -> liczCzas( ( $timer2-$this->init->time) ) .'</div>';
							$revData['time'] = $timer2;
							$revData['numID'] = 2;
							$revData['num'] = $this -> account -> playerID . 2;							
						}else if( $developmentOffice >= 1 AND $timer2 == 0 ){
							// obliczamy koszt rozbudowy
							$cost2 = round(100+((100/35)*$materialTechnology* $this -> account -> rankLevel ));
							$time2 = round( ( 10000*(2.5*$materialTechnology+1)-( ( 10000*(2.5*$materialTechnology+1) ) * $procent ) ) );
							
							
							
							$buildD2 = 1;
							if( $cost2 > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ) {
								$costD2 = '<strong class="red">'. $cost2 .' kredytów</strong>';
								$buildD2 = 0;
							}else{
								$costD2 = $cost2 .' kredytów';
							}
							
							if( $this -> account -> points < $cost2 ){
								$pointsD2 = '<strong class="red" title="Aby zdobyć punkty musisz budować jednostki bojowe<br> lub atakować jednostki wroga">'. $cost2 .' punktów</strong>';
								$buildD2 = 0;
							}else{
								$pointsD2 = $cost2 .' punktów';
							}
							
							if( $playerData['rawMaterials'] < ( 15000*$materialTechnology) ){
								$materialsD2 = '<strong class="red" title="Aby zdobyć odpowiednią ilość surowców- rozwijaj technologie">'. ( 15000*$materialTechnology) .' surowców</strong>';
								$buildD2 = 0;
							}else{
								$materialsD2 = ( 15000*$materialTechnology) .' surowców';
							}
							
							
							
							
							if( $buildD2 == 1 ){
								$revData['cityPlane'] .= 'Koszt: '. $costD2 .', '. $pointsD2 .' i '. $materialsD2 .'<br>Czas badania: '. $objHelper -> liczCzas( $time2 ) .'<br><div class="buildUpTB" id="buildUp2" num="2">PRZYCISK ROZBUDUJ</div>';
							}else{
								$revData['cityPlane'] .= 'Koszt: '. $costD2 .', '. $pointsD2 .' i '. $materialsD2 .'<br><br>Czas badania: '. $objHelper -> liczCzas( $time2 ) .'<br>Aby rozbudować tą technologię spełnij powyższe wymagania';
							}
						}else{
							$revData['cityPlane'] .= 'WYMAGANIA:<br><span class="red">BIURO ROZWOJU 1</span><br>';
						}
						 $revData['cityPlane'] .= '
					</div>	 
					<div id="optimizationStorage">
						OPTYMALIZACJA SKŁADOWANIA '. $optimizationStorage .'<br>
						<div class="infoCity" title="Technologia Składowania materiałów ma bardzo duży wpływ na porzadek na magazynach i punktach składowania surowców.<br>
						Każdy poziom tej technologii zwiększa magazyn o 100 tys. szt. surowców<br>">i</div>';
						if(  $optimizationStorage == 10 ){
							$revData['cityPlane'] .= 'Technologia osiągnęła maksymalny poziom';
						}else if( $timer3 > 0 ){
							$revData['cityPlane'] .= '<div id="buildUp3" class="buildUpTB" num="3">'. $objHelper -> liczCzas( ( $timer3-$this->init->time) ) .'</div>';
							$revData['time'] = $timer3;
							$revData['num'] = $this -> account -> playerID . '3';
							$revData['numID'] = 3;
							$revData['num'] = $this -> account -> playerID . 3;						
						}else if( $developmentOffice >= 2 AND $materialTechnology >= 3  AND $timer3 == 0 ){
							// obliczamy koszt rozbudowy
							$cost3 = round(150+((150/35)*$optimizationStorage* $this -> account -> rankLevel ));
							$time3 = round( ( 12000*(3*$optimizationStorage+1)-( ( 12000*(3*$optimizationStorage+1) ) * $procent ) ) );
							
							$buildD3 = 1;
							if( $cost3 > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ) {
								$costD3 = '<strong class="red"  title="Aby zdobyć kredyty zaczekaj na żołd lub niszcz jednostki wroga">'. $cost3 .' kredytów</strong>';
								$buildD3 = 0;
							}else{
								$costD3 = $cost3 .' kredytów';
							}
							
							if( $this -> account -> points < $cost3 ){
								$pointsD3 = '<strong class="red"  title="Aby zdobyć punkty musisz budować jednostki bojowe<br> lub atakować jednostki wroga">'. $cost3 .' punktów</strong>';
								$buildD3 = 0;
							}else{
								$pointsD3 = $cost3 .' punktów';
							}
							
							if( $playerData['rawMaterials'] < ( 20000*$optimizationStorage) ){
								$materialsD3 = '<strong class="red" title="Aby zdobyć odpowiednią ilość surowców- rozwijaj technologie">'. ( 20000*$optimizationStorage) .' surowców</strong>';
								$buildD3 = 0;
							}else{
								$materialsD3 = ( 20000*$optimizationStorage) .' surowców';
							}
							
							
							if( $buildD3 == 1 ){
								$revData['cityPlane'] .= 'Koszt: '. $costD3 .', '. $pointsD3 .' i '. $materialsD3 .'<br>Czas badania: '. $objHelper -> liczCzas( $time3 ) .'<br><div class="buildUpTB" id="buildUp3" num="3">PRZYCISK ROZBUDUJ</div>';
							}else{
								$revData['cityPlane'] .= 'Koszt: '. $costD3 .', '. $pointsD3 .' i '. $materialsD3 .'<br><br>Czas badania: '. $objHelper -> liczCzas( $time3 ) .'<br>Aby rozbudować tą technologię spełnij powyższe wymagania';
							}
							
						}else{
							$revData['cityPlane'] .= '
								WYMAGANIA:<br>';
							if( $developmentOffice < 2 ){
								$revData['cityPlane'] .= '
								<span class="red">BIURO ROZWOJU 2</span><br>';
							}else{
								$revData['cityPlane'] .= '
								BIURO ROZWOJU 2<br>';
							}
							if( $materialTechnology < 3 ){
								$revData['cityPlane'] .= '
								<span class="red">TECHNOLOGIA SUROWCÓW 3.</span><br>';
							}else{
								$revData['cityPlane'] .= '
								TECHNOLOGIA SUROWCÓW 3.<br>';
							}
						}
						$revData['cityPlane'] .= '
					</div>
					<div id="productionOptimization">
						OPTYMALIZACJA PRODUKCJI '. $productionOptimization .'<br>
						<div class="infoCity" title="Wprowadzenie tej technologii pozwala zastosować nowoczesne linie produkcyjne i prodcedury zarządzania załogami fabryk.<br>
						Każdy poziom tej technologii skraca czas produkcji jednostek ( każdy poziom o 6% od poprzedniej wartości )<br>">i</div>
						';
						if(  $productionOptimization == 10 ){
							$revData['cityPlane'] .= 'Technologia osiągnęła maksymalny poziom';
						}else if( $timer4 > 0 ){
							$revData['cityPlane'] .= '<div id="buildUp4" class="buildUpTB" num="4">'. $objHelper -> liczCzas( ( $timer4-$this->init->time) ) .'</div>';
							$revData['time'] = $timer4;
							$revData['num'] = $this -> account -> playerID . '4';
							$revData['numID'] = 4;
							$revData['num'] = $this -> account -> playerID . 4;
						}else if( $developmentOffice >= 4 AND $optimizationStorage >= 2  AND $timer4 == 0  ){
							// obliczamy koszt rozbudowy
							$cost4 = round(200+((200/35)*$productionOptimization* $this -> account -> rankLevel ));
							$time4 = round( (14000*(3.5*$productionOptimization+1)-( ( 14000*(3.5*$productionOptimization+1) ) * $procent ) ) );
							
							
							$buildD4 = 1;
							if( $cost4 > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ) {
								$costD4 = '<strong class="red"  title="Aby zdobyć kredyty zaczekaj na żołd lub niszcz jednostki wroga">'. $cost4 .' kredytów</strong>';
								$buildD4 = 0;
							}else{
								$costD4 = $cost4 .' kredytów';
							}
							
							if( $this -> account -> points < $cost4 ){
								$pointsD4 = '<strong class="red"  title="Aby zdobyć punkty musisz budować jednostki bojowe<br> lub atakować jednostki wroga">'. $cost4 .' punktów</strong>';
								$buildD4 = 0;
							}else{
								$pointsD4 = $cost4 .' punktów';
							}
							
							if( $playerData['rawMaterials'] < ( 25000*$productionOptimization) ){
								$materialsD4 = '<strong class="red" title="Aby zdobyć odpowiednią ilość surowców- rozwijaj technologie">'. ( 25000*$productionOptimization) .' surowców</strong>';
								$buildD4 = 0;
							}else{
								$materialsD4 = ( 25000*$productionOptimization) .' surowców';
							}
							
							
							if( $buildD4 == 1 ){
								$revData['cityPlane'] .= 'Koszt: '. $costD4 .', '. $pointsD4 .' i '. $materialsD4 .'<br>Czas badania: '. $objHelper -> liczCzas( $time4 ) .'<br><div class="buildUpTB" id="buildUp4" num="4">PRZYCISK ROZBUDUJ</div>';
							}else{
								$revData['cityPlane'] .= 'Koszt: '. $costD4 .', '. $pointsD4 .' i '. $materialsD4 .'<br><br>Czas badania: '. $objHelper -> liczCzas( $time4 ) .'<br>Aby rozbudować tą technologię spełnij powyższe wymagania';
							}
							
						}else{
							$revData['cityPlane'] .= '
							WYMAGANIA:<br>';
							if( $developmentOffice < 4 ){
								$revData['cityPlane'] .= '
								<span class="red">BIURO ROZWOJU 4</span><br>';
							}else{
								$revData['cityPlane'] .= '
								BIURO ROZWOJU 2<br>';
							}
							if( $optimizationStorage < 2 ){
								$revData['cityPlane'] .= '
								<span class="red">OPTYMALIZACJA SKŁADOWANIA 2.</span><br>';
							}else{
								$revData['cityPlane'] .= '
								OPTYMALIZACJA SKŁADOWANIA 2.<br>';
							}
						}
						$revData['cityPlane'] .= '
					</div>
					<div id="materialsScience">
						MATERIAŁOZNAWSTWO '. $materialsScience .'<br>
						<div class="infoCity" title="Materiałoznawstwo pozwala opracować a następnie zastosować nowe, tańsze stopy metali, rewolucyjne rozwiązania w produkcji.<br> 
						Każdy poziom tej technologii obniża cenę produkcji jednostek ( każdy poziom o 6% od poprzedniej wartości )">i</div>
						';
						if(  $materialsScience == 10 ){
							$revData['cityPlane'] .= 'Technologia osiągnęła maksymalny poziom';
						}else if( $timer5 > 0 ){
							$revData['cityPlane'] .= '<div id="buildUp5" class="buildUpTB" num="5">'. $objHelper -> liczCzas( ( $timer5-$this->init->time) ) .'</div>';
							$revData['time'] = $timer5;
							$revData['num'] = $this -> account -> playerID . '5';
							$revData['numID'] = 5;
							$revData['num'] = $this -> account -> playerID . 5;
						}else if( $developmentOffice >= 6 AND $optimizationStorage >= 4 AND $productionOptimization >= 6  AND $timer5 == 0  ){
							// obliczamy koszt rozbudowy
							$cost5 = round(250+((250/35)*$materialsScience* $this -> account -> rankLevel ));
							$time5 = round( ( 16000*(4*$materialsScience+1)-( ( 16000*(4*$materialsScience+1) ) * $procent ) ) );
							
							$buildD5 = 1;
							if( $cost5 > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ) {
								$costD5 = '<strong class="red"  title="Aby zdobyć kredyty zaczekaj na żołd lub niszcz jednostki wroga">'. $cost5 .' kredytów</strong>';
								$buildD5 = 0;
							}else{
								$costD5 = $cost5 .' kredytów';
							}
							
							if( $this -> account -> points < $cost5 ){
								$pointsD5 = '<strong class="red"  title="Aby zdobyć punkty musisz budować jednostki bojowe<br> lub atakować jednostki wroga">'. $cost5 .' punktów</strong>';
								$buildD5 = 0;
							}else{
								$pointsD5 = $cost5 .' punktów';
							}
							
							if( $playerData['rawMaterials'] < ( 30000*$materialsScience) ){
								$materialsD5 = '<strong class="red" title="Aby zdobyć odpowiednią ilość surowców- rozwijaj technologie">'. ( 30000*$materialsScience) .' surowców</strong>';
								$buildD5 = 0;
							}else{
								$materialsD5 = ( 30000*$materialsScience) .' surowców';
							}
							
							
							if( $buildD5 == 1 ){
								$revData['cityPlane'] .= 'Koszt: '. $costD5 .', '. $pointsD5 .' i '. $materialsD5 .'<br>Czas badania: '. $objHelper -> liczCzas( $time5 ) .'<br><div class="buildUpTB" id="buildUp5" num="5">PRZYCISK ROZBUDUJ</div>';
							}else{
								$revData['cityPlane'] .= 'Koszt: '. $costD5 .', '. $pointsD5 .' i '. $materialsD5 .'<br><br>Czas badania: '. $objHelper -> liczCzas( $time5 ) .'<br>Aby rozbudować tą technologię spełnij powyższe wymagania';
							}
							
						}else{
							$revData['cityPlane'] .= '
							WYMAGANIA:<br>';
							if( $developmentOffice < 6 ){
								$revData['cityPlane'] .= '
								<span class="red">BIURO ROZWOJU 6</span><br>';
							}else{
								$revData['cityPlane'] .= '
								BIURO ROZWOJU 6<br>';
							}
							if( $optimizationStorage < 4 ){
								$revData['cityPlane'] .= '
								<span class="red">OPTYMALIZACJA SKŁADOWANIA 4.</span><br>';
							}else{
								$revData['cityPlane'] .= '
								OPTYMALIZACJA SKŁADOWANIA 4.<br>';
							}
							if( $productionOptimization < 6 ){
								$revData['cityPlane'] .= '
								<span class="red">OPTYMALIZACJA PRODUKCJI 6</span><br>';
							}else{
								$revData['cityPlane'] .= '
								OPTYMALIZACJA PRODUKCJI 6<br>';
							}
						}
						$revData['cityPlane'] .= '
					</div>
				</div>';
		
		if ($this->account->playerVacation > $this->init->time) {
            $this->error->printError( mapa_txt .' ' . date("Y-m-d H:i:s", $this->account->playerVacation) . ' '. comp_index108, 'mapa');
        }
        $sponsorUnitsList = [];
        $sponsorUnitsList['showData'] = '<p class="tekstUnits">'. comp_index109 .'.</p><div class="unitListCity"><table class="tabela_losowanie"><tr>';
        $arrPlutons = [];
        $arrDataCount = $this->db->query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits`');
        foreach ($arrDataCount as $rowTDA) {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
        $arrDataCount->closeCursor();
        $per_page = 7;
        $getUnits = $this->db->query('SELECT `u`.`tacticalDataID`,`u`.`idLC`, `u`.`unitType`, `u`.`Specialty`,`u`.`id` AS `uid`,`u`.`unitTurn`,`td`.`nazwa' . $this->account->lang . '` as `nazwa` from `units` AS `u` '
            . ' LEFT JOIN `TacticalData` AS `td` ON ( `u`.`tacticalDataID`=`td`.`id` ) '
            . 'WHERE `u`.`playerID`=' . $this->account->playerID . ' AND `u`.`x`=0 AND `u`.`y`=0 ');
        $i = 0;
        foreach ($getUnits as $prepareUnitsList) {
			if( $prepareUnitsList['idLC'] == 0 OR ( $prepareUnitsList['idLC'] == $prepareUnitsList['uid']) ){
				$nowa_nazwa = $this->unit->ustalNazwe(count($arrPlutons[$prepareUnitsList['uid']]), $prepareUnitsList['unitType'], $prepareUnitsList['Specialty'], $prepareUnitsList['nazwa']);
				$tab = implode(',', $arrPlutons[$prepareUnitsList['uid']]);
				// min - klasa, która umożliwia reakcje na kliknięcice w miniature
				$sponsorUnitsList['showData'] .= '<td id = "' . $prepareUnitsList['uid'] . '" td="' . $tab . '" class = "min units unit-' . $prepareUnitsList['tacticalDataID'] . '" title="' . $nowa_nazwa . '" x="'. $this->account->cityX .' " y="'. $this->account->cityY .'" ><p class="ilosc_tur">' . $prepareUnitsList['unitTurn'] . '</p></td>';
				$i++;
			}
            if ($i % $per_page == 0) {
                $sponsorUnitsList['showData'] .= '</tr><tr>';
            }
        }
        $sponsorUnitsList['showData'] .= '</table></div>';
        if ($i == 0) {
            $sponsorUnitsList['showData'] = '<p class="tekstUnits">'. comp_index110 .' ;)</p>';
        }
		
		$revData['showData'] = $sponsorUnitsList['showData'];
		
        echo json_encode($revData);

    }
	
	
	public function buildAction()
    {
		$buildID = $this->path->post('bid');
		$objHelper = init::getFactory()->getService('helper');
		$countBuild = $this->db->query('SELECT *, count(*) AS `counted` FROM `buildingsOnBuild` WHERE `playerID`= '. $this->account->playerID .' ')->fetch();
		$buildingsData = $this->db->query('SELECT *  FROM `building` WHERE `playerID`= '. $this->account->playerID .' ')->fetch();
		$playerData = $this->db->query('SELECT `rawMaterials` FROM `players` WHERE `id`= '. $this->account->playerID .' ')->fetch();
		
		$odpowiedz = [];
		
		$developmentOffice      = $buildingsData['developmentOffice'];
		$materialTechnology     = $buildingsData['materialTechnology'];
		$optimizationStorage    = $buildingsData['optimizationStorage'];
		$productionOptimization = $buildingsData['productionOptimization'];
		$materialsScience       = $buildingsData['materialsScience'];
		
		$b = $developmentOffice * 6;
		$procent = $b/100;
		
		switch($buildID){
			case 1:
				$cost = round(50+((50/35)*$developmentOffice* $this -> account -> rankLevel ));
				$time = round( ( 8000*(2*$developmentOffice+1)-( ( 8000*(2*$developmentOffice+1) ) * $procent ) ) ) + $this->init->time;
				$costMaterials = 0;
			break;
			case 2:
				$cost = round(100+((100/35)*$materialTechnology* $this -> account -> rankLevel ));
				$time = round( ( 10000*(2.5*$materialTechnology+1)-( ( 10000*(2.5*$materialTechnology+1) ) * $procent ) ) ) + $this->init->time;
				$costMaterials = (15000*$materialTechnology);
			break;
			case 3:
				$cost = round(150+((150/35)*$optimizationStorage* $this -> account -> rankLevel ));
				$time = round( ( 12000*(3*$optimizationStorage+1)-( ( 12000*(3*$optimizationStorage+1) ) * $procent ) ) ) + $this->init->time;
				$costMaterials = (20000*$optimizationStorage);
			break;
			case 4:
				$cost = round(200+((200/35)*$productionOptimization* $this -> account -> rankLevel ));
				$time = round( (14000*(3.5*$productionOptimization+1)-( ( 14000*(3.5*$productionOptimization+1) ) * $procent ) ) ) + $this->init->time;
				$costMaterials = (25000*$productionOptimization);
			break;
			case 5:
				$cost = round(250+((250/35)*$materialsScience* $this -> account -> rankLevel ));
				$time = round( ( 16000*(4*$materialsScience+1)-( ( 16000*(4*$materialsScience+1) ) * $procent ) ) ) + $this -> init -> time;
				$costMaterials = (30000*$materialsScience);
			break;
		}
		
		
		$odpowiedz['doTech'] = 0;
		$odpowiedz['height'] = 135;
		if( $countBuild['counted'] == 0 ){
			//sprawdzamy, czy gracz ma kasę
			if( $cost > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) AND $this -> account -> points < $cost ){
				$odpowiedz['error'] = 'Nie masz kasy albo punktów';
				
			}else{
				if ($this -> account -> softCurrency >= $cost ){
					$this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-' . $cost . '), `rawMaterials`= ( `rawMaterials`- '. $costMaterials .' ) WHERE `id`=' . $this -> account -> playerID . ' ');
				}else{
					if ($this -> account -> softCurrency < $cost AND ($this -> account -> PremiumCurrency >= ( $this -> account -> softCurrency - $cost ) ) ){
						if( $costMaterials < $playerData['rawMaterials'] ){
							//gracz nie ma wystarczającej ilości kasy soft, wiec zerujemy kasę soft i odejmujemy od kasy Premium
							$this -> db -> exec('UPDATE `players` SET `softCurrency`=0, `rawMaterials`= ( `rawMaterials`- '. $costMaterials .' ) WHERE `id`=' . $this -> account -> playerID . ' ');
							//i pomniejszamy ilość premiumCurrency
							$new_PremiumCurrency = $this -> account -> softCurrency - $cost;
							$this -> db -> exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $new_PremiumCurrency . ' ) WHERE `id`=' . $this -> account -> id . ' ');
						}
					}
				}
				// dodajemy do bazy rekord
				$this->db->exec('INSERT INTO `buildingsOnBuild` (playerID, buildID, timeToEnd) VALUES (' . $this->account->playerID . ',' . $buildID . ',' . $time . ' ) ');
				$odpowiedz['time'] = $time;
				$odpowiedz['num'] = $this -> account -> playerID . $buildID;
				$odpowiedz['zegar'] = $objHelper->liczCzas( $time - $this -> init -> time );
				$odpowiedz['bid'] = $buildID;
				$odpowiedz['cost'] = $cost;
				$odpowiedz['costMaterials'] = $costMaterials;
				$odpowiedz['doTech'] = 1;
			}
		}else{
			switch($countBuild['buildID']){
				case 1:
				$txt = 'BIURO ROZWOJU';
			break;
			case 2:
				$txt = 'TECHNOLOGIA SUROWCÓW';
			break;
			case 3:
				$txt = 'OPTYMALIZACJA SKŁADOWANIA ';
			break;
			case 4:
				$txt = 'OPTYMALIZACJA PRODUKCJI';
			break;
			case 5:
				$txt = 'MATERIAŁOZNWSTWO';
			break;
			}
			$odpowiedz['error'] = '<div id="repeatBox"><strong>'. $txt .'</strong> jest w trakcie opracowywana.<br>';
			// sprawdzam, czy gracz ma jakieś bony na przyspieszenia
			$dane = $this->db->query('SELECT * FROM `prize` WHERE ( `prizeNumber`= 3 OR `prizeNumber`= 4 ) AND `prizeUsed`= 0 AND `playerID`='. $this->account->playerID .' ');
				if( $dane->rowCount() == 0 ){
					$odpowiedz['error'] .= 'Nie masz bonów na skrócenie czasu opracowywania technologii';
					$rev = '';
				}else{
					$rev = 'Możesz użyć bonów przyspieszających.<br><table>';
					foreach ( $dane as $singlePrize ) {
						switch( $singlePrize['prizeTime'] ){
							case 600:
								$txt2 = '10 minut';
							break;
							case 3600:
								$txt2 = '60 minut';
							break;
							case 14400:
								$txt2 = '4 godziny';
							break;
							case 604800:
								$txt2 = '7 dni';
							break;
							case 2592000:
								$txt2 = '30 dni';
							break;
						}
						
						$odpowiedz['height'] += 20;
						$rev .= '<tr><td>'. $txt2 .'</td><td><div class="usePrize" num="'. $singlePrize['id'] .'" uid="'. $countBuild['buildID'] .'" step="2" step2="2">użyj</td></tr>';
					}
					
					$rev .= '</table></div>';
				}
			$odpowiedz['error'] .= $rev;
			$odpowiedz['doTech'] = 0;
		}
		echo json_encode($odpowiedz);
	}
	
	
	
	public function showCLUnits( )
    {
        $uid = $this -> path -> get('uid');
		$sponsorUnitsList = [];
        $sponsorUnitsList['showData'] = '<table class="clUnits"><tr>';
		// jednostka jest barką desantową
		// roimy liste jendostek na barce
		$getLoadingUnits = $this -> db -> query('SELECT `tacticalDataID`, `id` from `units` WHERE `idLC` = '.$uid.' AND `id` != '.$uid.' ');
		$uidData = $this -> db -> query('SELECT `x`, `y`, `rawMaterials` from `units` WHERE `id` = '.$uid.' ')->fetch();
		$iUn = 0;
		$iC = 0;
		foreach ( $getLoadingUnits as $rowSingleUnits )
		{
			$iUn++;
			$sponsorUnitsList['showData'] .= '<td class="reloadUnit units unit-'.$rowSingleUnits['tacticalDataID'].'" num="'.$rowSingleUnits['id'].'" tdi="'.$rowSingleUnits['tacticalDataID'].'" clx="'.$uidData['x'].'" cly="'.$uidData['y'].'"></td>';
			if( $iUn == 5 ){
				$sponsorUnitsList['showData'] .= '</tr><tr>';
				$iUn = 0;
			}
			$iC++;
		}
		$i = 0;
		if( $this->world->warMapNum == 2 ){ // mapka morska z wysepką
			for( $i = 0; $i < ($uidData['rawMaterials']/1000); $i++ ){
				$sponsorUnitsList['showData'] .= '<td class="reloadMaterials" num="'.$uid.'" title="surowiec"></td>';
				$iUn++;
				if( $iUn == 5 ){
					$sponsorUnitsList['showData'] .= '</tr><tr>';
					$iUn = 0;
				}
			}
			
			// sprawdzamy, czy barka jest w zasięgu miasta lub bazy do rozładunku.
			// zakładamy że max. odległość to 3 heksy
			$dystanceToCity = $this -> unit -> hex_distance(array('x' => $uidData['x'], 'y' => $uidData['y']),array('x' => $this->account->cityX, 'y' => $this->account->cityY ) );
			$siloOn = '';
			if( $dystanceToCity > 3 ){
				// sprawdzamy czy w pobliżu jest silos
				if( $this->account->nation == 1 ){
					$getUnitD = $this -> db -> query('SELECT `x`, `y`, `id` from `units` WHERE `tacticalDataID`= 224 AND `worldID` = '. $this->world->id .' ')->fetch();
				}else{
					$getUnitD = $this -> db -> query('SELECT `x`, `y`, `id` from `units` WHERE `tacticalDataID`= 225 AND `worldID` = '. $this->world->id .' ')->fetch();
				}
				
				if( $getUnitD['x'] ){
					$dystanceToCity = $this -> unit -> hex_distance(array('x' => $uidData['x'], 'y' => $uidData['y']),array('x' => $getUnitD['x'], 'y' => $getUnitD['y'] ) );
					$siloOn = 'var="'. $getUnitD['id'].'"';
				}
			}
			
			if( $dystanceToCity <= 3 AND ( ( $iC + $i ) < 10 ) AND ( $this->account->rawMaterials >= 1000 ) AND empty($getUnitD['x']) ){
				$sponsorUnitsList['showData'] .= '<div id="plusSurka" uid="'. $uid .'">+</div>';
			}
			if( $dystanceToCity <= 3 AND $i > 0 ){
				$sponsorUnitsList['showData'] .= '<div id="minusSurka" uid="'. $uid .'" '. $siloOn .'>-</div>';
			}else if( $dystanceToCity > 3 AND $i > 0 ){
				$sponsorUnitsList['showData'] .= 'Aby wyładować surowce musisz być w pobliżu silosu lub miasta';
			}
		} 
		
		
		$sponsorUnitsList['showData'] .= '</tr></table>';
        if ($iC == 0 AND $i == 0 ) {
            $sponsorUnitsList['showData'] .= '<p>'. comp_index111 .' :)</p>';
        }
        echo json_encode($sponsorUnitsList);
    }
	
	public function showDataSilo( )
    {
        $uid = $this -> path -> get('uid');
		$sponsorUnitsList = [];
		$uidData = $this -> db -> query('SELECT `rawMaterials`, `time` from `siloData` WHERE `unitID` = '.$uid.' ')->fetch();
		$sponsorUnitsList['time'] = 0;
		if( $uidData['rawMaterials'] >= 100000 ){
			// surowców mamy odpowiednia ilość, więc odliczamy czas d 10 minut do finału
			$timer = '<div id="timerSilo" title="jeśli ten timer skończy odlicznie- wygraliście!"></div>';
			if( $uidData['time'] > $this->init->time ){
				$sponsorUnitsList['time'] = $uidData['time'];
			}
			$sponsorUnitsList['num'] = $uid;
		}else{
			$timer = '';
		}
        $sponsorUnitsList['showData'] = '
			<div id="dataSilo">
				<div id="siloM">Minimum: 100 tys.<br>
				Magazynowane: '. $uidData['rawMaterials'] .'<br>
				'. $timer .'
				</div>
			</div>';
        echo json_encode($sponsorUnitsList);
    }
	
	

    public function editStuff()
    {
        $objHelper = init::getFactory()->getService('helper');
        $haslo =  SYSTEM_PASSWORD;
        $md5 = $this->path->post('id');
        $dane = $this->path->post('var');
		$stuffReverse = [];
		
        if ($md5 <> md5($haslo . $dane)) {
            // uwaga... dane modyfikowane
            $stuffReverse['error'] = "błędne dane wejściowe";
            //exit();
        } else {
            $t = unserialize(base64_decode($dane));
            // dalsza obróbka na poprawnych danych
            $sztab = $t["id_sztabu"];
            $nr_jednostki = $t["nr_jednostki"];
            $funkcja = $t["funkcja"];

            $dane = $this->db->query('SELECT `u`.*, `acc`.`login` AS `nickName`, `t`.`widocznosc`, `t`.`CorpsId`, `t`.`nazwa` FROM `units` AS `u` '
                . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
                . 'LEFT JOIN `players` AS `pl` ON (`u`.`playerID`=`pl`.`id`) '
                . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
                . 'WHERE `u`.`id`=' . $nr_jednostki . ' AND `u`.`playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
            if (!isset($dane['id'])) $this->error->printError('To nie twoja jednostka.', 'mapa');
            $specjalnosc = $dane['Specialty'];
            $pancerz = $dane['DefensePoints'];
            $dostepneTury = $dane['unitTurn'];
            $xOne = $dane['x'];
            $yOne = $dane['y'];
			
			switch ($funkcja) {
                case 1://wysłanie zgłoszenia do sztabu
                    $daneSTUFF = $this->db->query('SELECT count(*) as `counted`, `hq`.`range`,`un`.`playerID` FROM `units` as `un`
					LEFT JOIN `HQData` AS `hq` ON `hq`.`unitsID`=' . $sztab . '
					WHERE `un`.`id`=' . $sztab . ' ')->fetch();
                    if ($daneSTUFF['range'] * 10 > $daneSTUFF['counted'] - 1) {
                        if ($daneSTUFF['playerID'] == $this->account->playerID) {
                            $stuffReverse['title'] = 'Dodanie jednostki do sztabu ' . $sztab;
                            $this->db->exec('UPDATE `units` SET `belongHQ`=' . $sztab . ' WHERE `id`=' . $nr_jednostki . ' LIMIT 1');
                            $stuffReverse['info'] = sztaby3;
                        } else {
                            $stuffReverse['title'] = 'Zgłoszenie jednostki do sztabu ' . $sztab;
                            $this->db->exec('INSERT INTO `HQwaiting` ( `HQID`, `unitsID` ) values (' . $sztab . ',' . $nr_jednostki . ' ) ');
                            $stuffReverse['info'] = sztaby1 . ' <strong>' . $sztab . '</strong>';
                        }
                    } else {
                        $stuffReverse['title'] = comp_index112 .' ' . $sztab;
                        $stuffReverse['error'] = comp_index113;
                    }
                    break;
                case 2://anulowanie złoszenia do sztabu
                    $this->db->exec('DELETE FROM `HQwaiting` WHERE `unitsID`=' . $nr_jednostki . ' LIMIT 1');
                    $stuffReverse['title'] = comp_index114 .' ' . $sztab;
                    $stuffReverse['info'] = sztaby2;
                    break;
                case 3://przyjęcie ejdnostki do sztabu
                    $daneSTUFF = $this->db->query('SELECT count(*) as `counted`, `hq`.`range`,`un`.`playerID` FROM `units` as `un`
					LEFT JOIN `HQData` AS `hq` ON `hq`.`unitsID`=' . $sztab . '
					WHERE `un`.`belongHQ`=' . $sztab . ' ')->fetch();
                    if ($daneSTUFF['range'] * 10 > $daneSTUFF['counted'] - 1) {
                        $this->db->exec('UPDATE `units` SET `belongHQ`=' . $sztab . ' WHERE `id`=' . $nr_jednostki . ' LIMIT 1');
                        $this->db->exec('DELETE FROM `HQwaiting` WHERE `unitsID`=' . $nr_jednostki . ' LIMIT 1');
                        $stuffReverse['info'] = sztaby3;
                        $stuffReverse['title'] = comp_index115 .' ' . $sztab;
                    }
                    break;
                case 4://usunięcie jendostki ze sztabu
                    $this->db->exec('UPDATE `units` SET `belongHQ`=0 WHERE `id`=' . $nr_jednostki . ' LIMIT 1');
                    $stuffReverse['info'] = sztaby4;
                    $stuffReverse['title'] = comp_index114 .' ' . $sztab;
                    break;
            }
			
			$unitsINT = $this -> db -> query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`='.$nr_jednostki.' ')->fetch();
			$nazwa = $this -> unit -> ustalNazwe( (int)$unitsINT[0], $dane['unitType'], $dane['Specialty'], $dane['nazwa'] );	
			$arrayData = array('activity' => 'unitUpdate');
            $arrUnitSendInfo = array(
				'removeUnit' => false,
                'addUnit' => false,
                'x' => $xOne,
                'y' => $yOne,
                'id' => $nr_jednostki,
                'view' => $dane['widocznosc'],
                'fromCity' => false,
                'nation' => $this->account->nation,
                'type' => 'u',
                'uid' => $dane['tacticalDataID'],
                'owner' => $this->account->playerID,
                'td' => array($dane['tacticalDataID']),
                'Specialty' => $specjalnosc,
                'unitTurn' => $dane['unitTurn'],
                'DefensePoints' => $dane['DefensePoints'],
                'FieldArtillery' => $dane['FieldArtillery'],
                'Torpedo' => $dane['Torpedo'],
                'Morale' => $dane['Morale'],
                'experience' => $dane['unitExperience'],
                'UnderWater' => $dane['UnderWater'],
                'nickName' => $dane['nickName'],
				'nazwa'    => $nazwa,
                'belongHQ' => $this->unit->checkStuff($dane['id']),
				'timing1'=> $dane['timing1'],
				'timing2'=> $dane['timing2']
            );
            // Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
            $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
                'data' => array(
                    'updateUnitData' => $arrUnitSendInfo
                )
            );
            $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

            echo json_encode($stuffReverse);
            $arrUnitData = null;
			exit();
        }
		echo json_encode($stuffReverse);
		exit();
    }

    public function editUnitOption()
    {
        $x = $this->path->post('x');
        $y = $this->path->post('y');
        $nr_jednostki = $this->path->post('unitID');
        $zadanie = $this->path->post('zadanie');
        $mina = $this->path->post('code');
        $daneUnits = array();
        $wykonanie = 1;
        $dane = $this->db->query('SELECT `Specialty`,`DefensePoints`,`unitTurn`,`FieldArtillery`,`x`,`y`,`unitType` FROM `units` WHERE `id`=' . $nr_jednostki . ' ')->fetch();
        $specjalnosc = $dane['Specialty'];
        $pancerz = $dane['DefensePoints'];
        $dostepneTury = $dane['unitTurn'];
        $FieldArtillery = $dane['FieldArtillery'];
        $xOne = $dane['x'];
        $yOne = $dane['y'];
		
		$up1 = 0;
		$up2 = 0;
		$licznik1 = 0;
		$licznik2 = 0;
		
		
		
        switch ($zadanie) {
            case '1'://budowa miasta przez inżyniera
                $arrCheckMap = $this->db->query('SELECT * FROM `mapData` WHERE `x`=' . $xOne . ' AND `y`=' . $yOne . ' AND `worldID`=' . $this->world->id . ' LIMIT 1')->fetch();
                if (!isset($arrCheckMap['fieldID'])) $this->error->printError('Punkt poza mapą.', 'mapa');
				// sprawdzam czy gracz ma jakieś jednostki nie wystawione
				$arrUnitsCheckMap = $this->db->query('SELECT * FROM `units` WHERE `onMap`= 0 AND `unitType`!=5 AND `playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
                if (isset($arrUnitsCheckMap['id'])) $this->error->printError('Aby przenieść miasto musisz wystawić wszystkie jednostki poza jednostkami wodnymi.', 'mapa');

                // sprawdzam czy docelowy punkt jest w zasięgu startowym
                if ($this->account->nation == 1) {
                    if ($xOne < 97 || $arrCheckMap['fieldType'] == 1 || $arrCheckMap['fieldType'] == 8 || $arrCheckMap['fieldType'] == 9 || $arrCheckMap['fieldCustom'] != 0) $this->error->printError('Na wybranym polu nie można postawić miasta.', 'mapa');
                    $unitIDTD = 218;
                } elseif ($this->account->nation == 2) {
                    if ($xOne > 35 || $arrCheckMap['fieldType'] == 1 || $arrCheckMap['fieldType'] == 8 || $arrCheckMap['fieldType'] == 9 || $arrCheckMap['fieldCustom'] != 0) $this->error->printError('Na wybranym polu nie można postawić miasta.', 'mapa');
                    $unitIDTD = 219;
                }
                //pobieram stare koordy miasta
                $daneStareMiasto = $this->db->query('SELECT `x`,`y`,`id` FROM `units` WHERE `playerID`=' . $this->account->playerID . ' AND `unitType`=7 LIMIT 1 ')->fetch();


                //wszytko ok, usuwam miasto i pojazd inżynierów
                $this->db->exec('DELETE FROM `units` WHERE `playerID`=' . $this->account->playerID . ' AND `unitType`=7 LIMIT 1');
                $this->db->exec('DELETE FROM `units` WHERE `id`=' . $nr_jednostki . ' LIMIT 1');
                $this->db->exec('DELETE FROM `GoldUnits` WHERE `unitID`=' . $nr_jednostki . ' AND `playerID`=' . $this->account->playerID . ' LIMIT 1');
                $this->db->exec('DELETE FROM `constituentUnits` WHERE `unitsID`=' . $nr_jednostki . ' LIMIT 1');//usuwam dane starego mista
                //dodaję nowe miasto
                $this->db->exec('INSERT INTO `units` (`playerID`,`x`, `y`, `DefensePoints`, `CityName`,`unitType`,`Morale`, `tacticalDataID`, `worldID`, `idLC`,`onMap`)
                VALUES (' . $this->account->playerID . ',' . $xOne . ', ' . $yOne . ', 100, \'Miasto ' . $this->account->login . '\', 7, 100, ' . $unitIDTD . ',' . $this->world->id . ', 0, 1 )');
                $intCityID = $this->db->lastInsertID();
                $this->db->exec('INSERT INTO `constituentUnits` (unitsID,tacticalDataID, connectUnit) VALUES (' . $intCityID . ',' . $unitIDTD . ',' . $intCityID . ' ) ');

                $arrATReturnData = $this->unit->checkNotSeenUnits($intCityID, $xOne, $yOne, true);
                $arrATNotSeenUnits = $arrATReturnData['arrNotSeenUnits'];
                $tutorialInfo = '';
                $arrayData = array('activity' => 'newCity', 'chanData' => array(
                    array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
                        'data' => array(
                            'unitData' => array(
                                'x' => $xOne,
                                'y' => $yOne,
                                //'xDel' => $daneStareMiasto['x'],
                                //'yDel' => $daneStareMiasto['y'],
                                'id' => $intCityID,
                                'view' => 6,
                                'nation' => $this->account->nation,
                                'unitType' => 7,
                                'uid' => $intCityID,
                                'owner' => $this->account->playerID,
                                'DefensePoints' => 100,
                                'tutorialInfo' => $tutorialInfo,
                                'info' => bud_o_txt
                            ),
							'unitDataInz' => array(
								'remove' => true,
                                'x' => $xOne,
                                'y' => $yOne,
                                'id' =>$nr_jednostki
                            ),
							'RemoveOldCity' => array(
                                'x' => $daneStareMiasto['x'],
                                'y' => $daneStareMiasto['y'],
                                'id' => $daneStareMiasto['id'],
								'remove' => true
                            ),
                            'enemyUnitsData' => $arrATNotSeenUnits
                        )
                    )
                ));
                $objHelper = init::getFactory()->getService('helper');
                $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

                $arrCheckMap = null;
                echo json_encode(true);
                break;
            case '3'://odbudowa pancerza
                $this->unit->updateUnitData($nr_jednostki, 'odbudowa_jednostka', 0);
                break;
            case '4'://zanurzenie / wynurzenie
                $this->unit->updateUnitData($nr_jednostki, 'podwodniak', 0);
                break;
		case '5'://połączenie jednostek
			$objHelper = init::getFactory()->getService('helper');
			//sprawdzam, czy jednostkę można połączyć
			$getUnits = $this->db->query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`=' . $nr_jednostki . ' ')->fetch();
			$moveRange = $this->db->query('SELECT `ruch` FROM `TacticalData` WHERE `id`=' . $mina . ' LIMIT 1 ')->fetch();
			if ( (int)$getUnits[0] < 3) {
				//szukamy jendostek, możliwych do połaczenia
				$arrConnectData = $this->db->query('SELECT `m`.*, `u`.`id` AS `unitID`,`u`.`tacticalDataID`,`u`.`unitType` AS `corpsId` FROM `mapData` AS `m`
				LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`)
				LEFT JOIN `TacticalData` AS `td` ON (`u`.`tacticalDataID`=`td`.`id`)
				WHERE `m`.`x`<=' . ($xOne + $moveRange['ruch']) . ' AND `m`.`y`>=' . ($yOne - $moveRange['ruch']) . ' AND `m`.`x`>=' . ($xOne - $moveRange['ruch']) . ' AND `m`.`y`<=' . ($yOne + $moveRange['ruch']) . ' AND `u`.`id`!=false AND `u`.`unitType`= \'' . $dane['unitType'] . '\' AND `u`.`Specialty`!=6 AND `u`.`Specialty`!=7 AND `u`.`Specialty`!=1 AND `u`.`Specialty`!=2 AND `u`.`playerID`=' . $this->account->playerID . ' AND `u`.`worldID`='.$this -> world -> id.' AND `u`.`id`!=' . $nr_jednostki . ' ');
				$arrConnData = array();
				foreach ($arrConnectData as $row) {
					$getSingleUnits = $this->db->query('SELECT COUNT(*) FROM `constituentUnits` WHERE `connectUnit`=' . $row['unitID'] . ' ')->fetch();
					if ( (int)$getSingleUnits[0] + (int)$getUnits[0] <= 3) {
						// sprawdzam, czy jednostka może przejsć				
						$arrConnData[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'unit' => $row['unitID'], 'con'=>1);
					}
				}
				
				
				$arrayData = array('activity' => 'showConnectUnits');
				$arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
				'data' => array(
				'showConnectUnits' => $arrConnData,
				'owner' => $this -> account -> playerID,
				'connUnit' => $nr_jednostki,
				'funkcje' => ''//$row['funkcje']
				)
				);
				$objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
				echo json_encode(true);
				$arrayData = null;
			} else {
				echo comp_index116;
			}
		break;
            case '6'://saper minuje
                $dane = array(
                    'x' => $x,
                    'y' => $y,
                    'mina' => $mina
                );
                $this->unit->updateUnitData($nr_jednostki, 'saper', $dane);
                break;
            case '7'://rozłożenie/złożenie artylerii
                $this->unit->updateUnitData($nr_jednostki, 'artyleria', 0);
                break;
            case '8'://działąnie sonaru
                $objHelper = init::getFactory()->getService('helper');
				$errory = [];
				$up1 = $up2 = 0;
				$licznik1 = '';
				$licznik2 = '';
				
				if( $this -> world -> timing == 1 ){// jeśli świat poligon ( na razie tylko tam są zegarki )
					// sprawdzam, czy licnziki sie wyzerowały
					$liczniki = $this -> db -> query('SELECT `timing1`, `timing2` FROM `units` WHERE `id`= '.$nr_jednostki.' AND `worldID`='. $this -> world -> id .'  LIMIT 1') -> fetch();	
					
					
					if( $liczniki['timing1'] > $this -> init -> time ){
						if( $liczniki['timing2'] > $this -> init -> time ){
							$errory['error']= 'Liczniki jeszcze nie zakończyły odliczania.';
							$licznik1 = $liczniki['timing1'];
							$licznik2 = $liczniki['timing2'];
							echo json_encode($errory);
							exit();
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
				
				
				$arrPlutons = array();
                $arrDataCount = $this->db->query('SELECT `tacticalDataID`, `unitsID`,`connectUnit` FROM `constituentUnits`');
                foreach ($arrDataCount as $rowTDA) {
                    $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
                }
                $arrDataCount->closeCursor();
                //pobieram zasieg sonaru jednostki
                $sonarRange = $this->db->query('SELECT `zasiegSonaru` FROM `TacticalData` WHERE `id`=' . $mina . ' LIMIT 1 ')->fetch();
                //szukamy wrogich zanurzonych okrętów podwodnych w zasiegu jednostki
                $arrConnectData = $this->db->query('SELECT `m`.*, `acc`.`login` AS `nickName`, `u`.`id` AS `uid`, `u`.`unitType`, `pl`.`nation`, `u`.`tacticalDataID`, `u`.`playerID`, `u`.`Specialty`, `u`.`DefensePoints`, `t`.`nazwa` FROM `mapData` AS `m` '
                    . 'LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`) '
                    . 'LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id`) '
                    . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
                    . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
                    . 'WHERE `m`.`x`<=' . ($xOne + $sonarRange['zasiegSonaru']) . ' AND `m`.`y`>=' . ($yOne - $sonarRange['zasiegSonaru']) . ' AND `m`.`x`>=' . ($xOne - $sonarRange['zasiegSonaru']) . ' AND `m`.`y`<=' . ($yOne + $sonarRange['zasiegSonaru']) . ' AND `u`.`id`!=false AND `u`.`Specialty`=18 AND `u`.`playerID` != ' . $this->account->playerID . ' AND `u`.`worldID`=' . $this->world->id . ' ');
                $arrConnData = array();
                $i = 0;
                foreach ($arrConnectData as $row) {
                    $nazwa = $this->unit->ustalNazwe(count($arrPlutons[$row['uid']]), $row['unitType'], $row['Specialty'], $row['nazwa']);
                    $arrConnData[$row['x'] . ':' . $row['y']] = array('unit' => $row['uid'], 'x' => $row['x'], 'y' => $row['y'], 'nation' => $row['nation'], 'unitType' => $row['unitType'], 'uid' => $row['tacticalDataID'], 'owner' => $row['playerID'], 'td' => (isset($arrPlutons[$row['uid']]) ? $arrPlutons[$row['uid']] : array()), 'Specialty' => $row['Specialty'], 'DefensePoints' => $row['DefensePoints'], 'nickName' => $row['nickName'], 'nazwa' => $nazwa, 'un' => 'un');
                    $i++;
                }

                
                if ($this->account->sounds == 1) {
                    $sound = 'sonar';
                } else {
                    $sound = false;
                }
				
				$updateLiczniki = '';
				//zapisujemy dane jednostki
				if( $up1==1 ){
					$updateLiczniki = ',`timing1`='.$licznik1;
				}
				if( $up2==1 ){
					$updateLiczniki = ',`timing2`='.$licznik2;
				}
                //odejmuję jedną ture za uruchomienie sonaru
                $this->db->exec('UPDATE `units` SET `unitTurn`=(`unitTurn`-('.$dostepneTury.' - 1) ) '.$updateLiczniki.'  WHERE `id`=' . $nr_jednostki . ' LIMIT 1 ');

				$arrayData = array('activity' => 'showUnderedUnits');
                $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
                    'data' => array(
                        'showUnderedUnits' => $arrConnData,
                        'unterEnemy' => $i,
                        'owner' => $this->account->playerID,
                        'sound' => $sound,
                        'x' => $xOne,
                        'y' => $yOne,
						'timing1' => $licznik1,
						'timing2' => $licznik2,
						'up1' => $up1,
						'up2' => $up2,
                        'unitTurn' => $dostepneTury - 1
                    )
                );
                $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

                echo json_encode(true);
                $arrayData = null;
                break;
				
				case '9'://ładowanie jednostki na barkę desantową
					$objHelper = init::getFactory()->getService('helper');
					//sprawdzam, czy jednostkę można ząładować na barkę
					$arrConnectData = $this->db->query('SELECT `m`.*, `u`.`id` AS `unitID`,`u`.`tacticalDataID`,`u`.`unitType` AS `corpsId` FROM `mapData` AS `m`
					LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`)
					LEFT JOIN `TacticalData` AS `td` ON (`u`.`tacticalDataID`=`td`.`id`)
					WHERE `m`.`x`<=' . ($xOne + 2) . ' AND `m`.`y`>=' . ($yOne - 2) . ' AND `m`.`x`>=' . ($xOne - 2) . ' AND `m`.`y`<=' . ($yOne + 2) . ' AND `u`.`id`!=false AND `u`.`Specialty`=19 AND `u`.`playerID`=' . $this->account->playerID . ' AND `u`.`worldID`='.$this -> world -> id.' AND `u`.`id`!=' . $nr_jednostki . ' ');
					$arrConnData = array();
					foreach ($arrConnectData as $row) {
						$getSingleUnits = $this->db->query('SELECT COUNT(*) FROM `units` WHERE `idLC`=' . $row['unitID'] . ' ')->fetch();
						if ( (int)$getSingleUnits[0] < 10) {
							// sprawdzam, czy jednostka może przejsć				
							$arrConnData[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'], 'unit' => $row['unitID'], 'con'=>1);
						}
					}
					
					
					$arrayData = array('activity' => 'showLandingCraft');
					$arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
						'data' => array(
							'showLandingCraft' => $arrConnData,
							'owner' => $this -> account -> playerID,
							'connUnit' => $nr_jednostki
						)
					);
					$objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
					echo json_encode(true);
					$arrayData = null;
				break;
	
				
				
				
        }
        //echo json_encode($daneUnits);
    }

    public function reparedUnits()
    {
        $data = array();
        $arrDamaged = $this->db->query('select  COUNT(*) as `ilosc_jednostek`, SUM(`uszkodzone`.`mozliwy`) as
		`realny_koszt`, SUM(`koszt`) as `pelny_koszt`
		FROM
		(SELECT `unitTurn`, `DefensePoints`,  `ubytek`, `koszt`,  CASE
		WHEN `unitTurn` - `koszt`  >= 0 THEN `koszt` ELSE
		`koszt`-(`koszt`-`unitTurn`) END as `mozliwy`
		FROM
		(SELECT `unitTurn`, `DefensePoints`, (100-`DefensePoints`) as `ubytek`,
		CEIL((100-`DefensePoints`) /10) as `koszt`
		FROM `units`
		where `playerID` = ' . $this->account->playerID . ' AND `DefensePoints` < 100 AND `unitTurn` > 0 Group by
		`id`) `mozliwe`) `uszkodzone`')->fetch();
        $koszt = $arrDamaged['realny_koszt'];
        if ($this->account->SponsorAccount >= $this->init->time) {
            $koszt = ceil($arrDamaged['realny_koszt'] / 2);
        }
        if ($koszt <= $this->account->gold) {
            $this->db->exec('UPDATE `units` as `old`
			JOIN `units` AS `new` USING (`id`)
			SET `new`.`unitTurn` = CASE  WHEN `old`.`unitTurn` -
			CEIL((100-`old`.`DefensePoints`) /10) >= 0 THEN `old`.`unitTurn` -
			CEIL((100-`old`.`DefensePoints`) /10) ELSE 0 END,
			`new`.`DefensePoints` = CASE  WHEN `old`.`unitTurn` - CEIL((100-`old`.`DefensePoints`) /10)
			>= 0 THEN 100 ELSE 100 -((CEIL((100-`old`.`DefensePoints`) /10)-`old`.`unitTurn`)*10) END
			WHERE `old`.`playerID` = ' . $this->account->playerID . '
			AND `old`.`unitTurn` > 0
			AND `old`.`DefensePoints` < 100');
            $this->db->exec('UPDATE `accounts` SET 	`gold`=(`gold`-' . $koszt . ') WHERE `id`=' . $this->account->id . ' LIMIT 1 ');
            $this->db->exec('INSERT INTO `financialOperations` ( accountID, goldValue, operation, changeDate ) values (' . $this->account->id . ',' . $koszt . ',"naprawa jendostek rel_all.",' . $this->init->time . ' ) ');
            $data['gold'] = $koszt;
        } else {
            $data['error'] = 'Nie masz tyle złota';
        }
        echo json_encode($data);
    }

    public function buildUnitSponsor()
    {
        $corpsID = $this->path->post('name');
        $data = array();
        $Factory = $this->db->query('SELECT * FROM `UnitBuild` WHERE `corpsID`=' . $corpsID . ' AND `playerID`=' . $this->account->playerID . ' LIMIT 1')->fetch();
        if ($Factory['TurnBuild'] == 0) {
            // sprawdzam, czy user ma nadal miasto :)
			$playerCity = $this -> db -> query('SELECT count(*) FROM `units` WHERE `playerID` = '. $this->account->playerID .' AND `unitType` = 7 ')->fetch();
			if( (int)$playerCity[0] == 1 ){
				$this->db->exec('DELETE FROM `UnitBuild` WHERE `corpsID`=' . $corpsID . ' AND `playerID`=' . $this->account->playerID . ' LIMIT 1');
				$this->account->buildUnit($Factory['tacticalDataID'], $this->account->playerID, 0);
				$data['goto'] = comp_index117 .'.';
			}else{
				$data['goto'] = 'Aby budować jednostki- wybuduj miasto';
			}
        }
        echo json_encode($data);
    }

    public function connectUnits()
    {
        $idOne = $this->path->post('idOne');
        $idTwo = $this->path->post('idTwo');
        $objHelper = init::getFactory()->getService('helper');
        $data = array();
        $data['druga'] = $idTwo;
        $arrOne = $this->db->query('SELECT `m`.*, `acc`.`login` AS `nickName`, `t`.`widocznosc`, `u`.`id` AS `uid`, `u`.`unitType`, `u`.`tacticalDataID`, `u`.`playerID`, `u`.`Specialty`, `u`.`unitTurn`,`u`.`DefensePoints`, `u`.`Torpedo`,`u`.`FieldArtillery`,`u`.`Morale`,`u`.`unitExperience`,`u`.`UnderWater`,`t`.`NationId`,`u`.`belongHQ`, `u`.`CityName`, `pl`.`nation`,`t`.`nazwa` FROM `mapData` AS `m` '
            . 'LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`) '
            . 'LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id`) '
            . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
            . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
            . 'WHERE `u`.`id`=' . $idOne . ' ')->fetch();
        $arrTwo = $this->db->query('SELECT `m`.*, `acc`.`login` AS `nickName`, `t`.`widocznosc`, `u`.`id` AS `uid`, `u`.`unitType`, `u`.`tacticalDataID`, `u`.`playerID`, `u`.`Specialty`, `u`.`unitTurn`,`u`.`DefensePoints`, `u`.`Torpedo`,`u`.`FieldArtillery`,`u`.`Morale`,`u`.`unitExperience`,`u`.`UnderWater`,`t`.`NationId`,`u`.`belongHQ`, `u`.`CityName`, `pl`.`nation`,`t`.`nazwa` FROM `mapData` AS `m` '
            . 'LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`) '
            . 'LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id`) '
            . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
            . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
            . 'WHERE `u`.`id`=' . $idTwo . ' ')->fetch();
        $data['polaczenie'] = 0;
        $sonarnowy = 0;
        /*
        if( ( $ilosc1 + $ilosc2 ) > 3 )
        {
            $data['polaczenie'] = 0;
        }
        */
        if ($arrOne['playerID'] != $arrTwo['playerID']) {
            $data['error'] = comp_index118 .' '. sztaby_gracza;
        }
        if ($arrOne['unitType'] != $arrTwo['unitType']) {
            $data['error'] = comp_index118 .' '. comp_index119;
        }

        if ($arrOne['Torpedo'] == 1 OR $arrTwo['Torpedo'] == 1) {
            $sonarnowy = 1;
        }


        $doswiadczenienowe = ($arrOne['unitExperience'] + $arrTwo['unitExperience']) / 2;
        $moralenowe = round(($arrOne['Morale'] + $arrTwo['Morale']) / 2);
        $pancerznowy = round(($arrOne['DefensePoints'] + $arrTwo['DefensePoints']) / 2);
        $turynowe = floor(($arrOne['unitTurn'] + $arrTwo['unitTurn']) / 2);
        
        if ($arrOne['belongHQ'] > 0) {
            $sztabowo = $arrOne['belongHQ'];
        } else if ($arrOne['belongHQ'] == 0 AND $arrTwo['belongHQ'] > 0) {
            $sztabowo = $arrTwo['belongHQ'];
        } else {
            $sztabowo = 0;
        }
        
		if( $arrOne['Specialty'] == 3  OR $arrTwo['Specialty'] == 3 OR $arrOne['Specialty'] == 20  OR $arrTwo['Specialty'] == 20 ){
			$func = 0;
		}else{
			$func = $arrTwo['Specialty'];
		}
		
		
        $this->db->exec('UPDATE `units` SET `Torpedo`=' . $sonarnowy . ', `belongHQ`=' . $sztabowo . ', `unitExperience`=' . $doswiadczenienowe . ', `Morale`=' . $moralenowe . ', `DefensePoints`=' . $pancerznowy . ', `unitTurn`=' . $turynowe . ', `Specialty`=' . $func . ' WHERE `id`=' . $idTwo . ' LIMIT 1 ');
        $this->db->exec('DELETE FROM `units` WHERE `id`=' . $idOne . ' LIMIT 1');
        $this->db->exec('DELETE FROM `alert` WHERE `idDaneJednostek_ob`=' . $idOne . ' LIMIT 1');
        $this->db->exec('DELETE FROM `alert` WHERE `idDaneJednostek_ob`=' . $idTwo . ' LIMIT 1');
        $this->db->exec('DELETE FROM `HQwaiting` WHERE `unitsID`=' . $idOne . ' LIMIT 1');
        $this->db->exec('DELETE FROM `HQwaiting` WHERE `unitsID`=' . $idTwo . ' LIMIT 1');
        $this->db->exec('UPDATE `constituentUnits` SET `connectUnit`=' . $idTwo . ' WHERE `connectUnit`=' . $idOne . ' ');
        $this->db->exec('UPDATE `GoldUnits` SET `unitID`=' . $idTwo . ' WHERE `unitID`=' . $idOne . ' ');

        $data['info'] = zadanie1;
        $arrPlutons = array();
        $arrDataCount = $this->db->query('SELECT `tacticalDataID`, `unitsID`, `connectUnit` FROM `constituentUnits` WHERE `connectUnit`=' . $idTwo . '');
        foreach ($arrDataCount as $rowTDA) {
            $arrPlutons[$rowTDA['connectUnit']][] = $rowTDA['tacticalDataID'];
        }
        $arrDataCount->closeCursor();
        $nazwa = $this->unit->ustalNazwe(count($arrPlutons[$idTwo]), $arrTwo['unitType'], $arrTwo['Specialty'], $arrTwo['nazwa']);
        //echo "nazwanew" . $nazwa;
        // Sprawdzam czy jednostka jest widoczna dla wroga
        $intIsSeen = false;
        $arrCheckNotSeenUnits = $this->db->query('SELECT `seenUnitID` FROM `units_range` WHERE `seenUnitID`=' . $idOne . ' LIMIT 1')->fetch();
        if (isset($arrCheckNotSeenUnits['seenUnitID'])) $intIsSeen = true;
        $arrCheckNotSeenUnits = null;

        $arrATReturnData = $this->unit->checkNotSeenUnits($arrOne['uid'], $arrOne['x'], $arrOne['y'], true);
        $arrATNotSeenUnits = $arrATReturnData['arrNotSeenUnits'];

        $arrUnitSendInfo = array(
            'x' => $arrTwo['x'],
            'y' => $arrTwo['y'],
            'id' => $arrTwo['uid'],
            'view' => $arrTwo['widocznosc'],
            'nation' => $this->account->nation,
            'type' => 'u',
            'uid' => $arrTwo['tacticalDataID'],
            'owner' => $this->account->playerID,
            'td' => (isset($arrPlutons[$arrTwo['uid']]) ? $arrPlutons[$arrTwo['uid']] : array()),
            'Specialty' => $func,
            'unitType' => $arrTwo['unitType'],
            'unitTurn' => $turynowe,
            'DefensePoints' => $pancerznowy,
            'FieldArtillery' => $arrTwo['FieldArtillery'],
            'Torpedo' => $sonarnowy,
            'Morale' => $moralenowe,
            'experience' => $doswiadczenienowe,
            'UnderWater' => $arrTwo['UnderWater'],
            'nickName' => $this->account->login,
            'belongHQ' => $this->unit->checkStuff($arrTwo['uid']),
            'nazwa' => $nazwa
        );
        $arrUnitSendToEnemy = array(
            'x' => $arrTwo['x'],
            'y' => $arrTwo['y'],
            'id' => $arrTwo['uid'],
            'nation' => $this->account->nation,
            'type' => 'u',
            'unitType' => $arrTwo['unitType'],
            'uid' => $arrTwo['tacticalDataID'],
            'owner' => $this->account->playerID,
            'td' => (isset($arrPlutons[$arrTwo['uid']]) ? $arrPlutons[$arrTwo['uid']] : array()),
            'Morale' => $moralenowe,
            'experience' => $doswiadczenienowe,
            'Specialty' => $func,
            'DefensePoints' => $pancerznowy,
            'nickName' => $this->account->login,
            'nazwa' => $nazwa
        );
        //jednostka do usuniecia z  mapki
        $dellUnit = array(
            'x' => $arrOne['x'],
            'y' => $arrOne['y'],
            'id' => $arrOne['uid']
        );
        //samouczek, sprawdzamy, czy gracz połączył jednostki
        $samouczek = array();
        $samouczek['aktiv'] = 0;
        if ($this->account->tutorialStage == 8)//gracz ma wykonać jeden ruch
        {
            $samouczek['tresc'] = '<div id="tutorial_1"></div>
                                <div id="tresc_samouczek">'. comp_index96 .'!<br></div>';
            $samouczek['gold'] = 10;
            $samouczek['aktiv'] = 1;
			$samouczek['heightWindow'] =640;
            $samouczek['playerID'] = $this->account->playerID;
			$samouczek['tutStage'] = 9;
            $this->db->exec('UPDATE `accounts` SET `tutorialStage`=9,`gold`=( `gold` + 10 ) WHERE `id`=' . $this->account->id . ' LIMIT 1');
        }


        // Deklaracja tablicy wysyłanej do mapy
        $arrayData = array('activity' => 'conectedUnits');

        // Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
        $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
            'data' => array(
                'unitOne' => $arrUnitSendInfo,
                'unitTwo' => $dellUnit,
                'enemyUnitsData' => $arrATNotSeenUnits,
                'samouczek' => $samouczek
            )
        );

        if ($intIsSeen === true) {
            // Informacja dla wrogów jeśli mnie widzą
            $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . ($this->account->nation == 1 ? 2 : 1),
                'data' => array(
                    'unitOne' => $arrUnitSendToEnemy,
                    'unitTwo' => $dellUnit,
                    'enemyUnitsData' => $arrATNotSeenUnits
                )
            );
        }
        $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
        echo json_encode(true);
    }


	public function loadUnitToLC()
    {
        $idOne = $this->path->post('idOne');// jednostka łączona
        $idTwo = $this->path->post('idTwo');// landingCruser
        $objHelper = init::getFactory()->getService('helper');
		
        $arrOne = $this->db->query('SELECT `m`.*, `acc`.`login` AS `nickName`, `t`.`widocznosc`, `u`.`id` AS `uid`, `u`.`unitType`, `u`.`tacticalDataID`, `u`.`playerID`, `u`.`Specialty`, `u`.`unitTurn`,`u`.`DefensePoints`, `u`.`Torpedo`,`u`.`FieldArtillery`,`u`.`Morale`,`u`.`unitExperience`,`u`.`UnderWater`,`t`.`NationId`,`u`.`belongHQ`, `u`.`CityName`, `pl`.`nation`,`t`.`nazwa` FROM `mapData` AS `m` '
            . 'LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`) '
            . 'LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id`) '
            . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
            . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
            . 'WHERE `u`.`id`=' . $idOne . ' ')->fetch();
        $arrTwo = $this->db->query('SELECT `m`.*, `acc`.`login` AS `nickName`, `t`.`widocznosc`, `u`.`id` AS `uid`, `u`.`unitType`, `u`.`tacticalDataID`, `u`.`playerID`, `u`.`Specialty`, `u`.`unitTurn`,`u`.`DefensePoints`, `u`.`Torpedo`,`u`.`FieldArtillery`,`u`.`Morale`,`u`.`unitExperience`,`u`.`UnderWater`,`t`.`NationId`,`u`.`belongHQ`, `u`.`CityName`, `pl`.`nation`,`t`.`nazwa` FROM `mapData` AS `m` '
            . 'LEFT JOIN `units` AS `u` ON (`u`.`x`=`m`.`x` AND `u`.`y`=`m`.`y`) '
            . 'LEFT JOIN `players` AS `pl` ON ( `u`.`playerID`=`pl`.`id`) '
            . 'LEFT JOIN `accounts` AS `acc` ON (`pl`.`accountID`=`acc`.`id`) '
            . 'LEFT JOIN `TacticalData` AS `t` ON (`u`.`tacticalDataID`=`t`.`id`) '
            . 'WHERE `u`.`id`=' . $idTwo . ' ')->fetch();
			
		// zapisujemy id barki i likwidujemy jednostkę z mapy	
        $this->db->exec('UPDATE `units` SET `idLC`='.$idTwo.', `x`=0, `y`=0 WHERE `id`=' . $idOne . ' LIMIT 1 ');
        
        $intIsSeen = false;
        $arrCheckNotSeenUnits = $this->db->query('SELECT `seenUnitID` FROM `units_range` WHERE `seenUnitID`=' . $idOne . ' LIMIT 1')->fetch();
        if (isset($arrCheckNotSeenUnits['seenUnitID'])) $intIsSeen = true;
        $arrCheckNotSeenUnits = null;

        $arrATReturnData = $this->unit->checkNotSeenUnits($arrOne['uid'], $arrOne['x'], $arrOne['y'], true);
        $arrATNotSeenUnits = $arrATReturnData['arrNotSeenUnits'];

        $arrUnitSendInfo = array(
            'x' => $arrTwo['x'],
            'y' => $arrTwo['y'],
            'id' => $arrTwo['uid'],
			'owner' => $this->account->playerID			/*,
            'view' => $arrTwo['widocznosc'],
            'nation' => $this->account->nation,
            'type' => 'u',
            'uid' => $arrTwo['tacticalDataID'],
            'owner' => $this->account->playerID,
            'td' => (isset($arrPlutons[$arrTwo['uid']]) ? $arrPlutons[$arrTwo['uid']] : array()),
            'Specialty' => $func,
            'unitType' => $arrTwo['unitType'],
            'unitTurn' => $arrTwo['unitTurn'],
            'DefensePoints' => $pancerznowy,
            'FieldArtillery' => $arrTwo['FieldArtillery'],
            'Torpedo' => $sonarnowy,
            'Morale' => $moralenowe,
            'experience' => $doswiadczenienowe,
            'UnderWater' => $arrTwo['UnderWater'],
            'nickName' => $this->account->login,
            'belongHQ' => $this->unit->checkStuff($arrTwo['uid']),
            'nazwa' => $nazwa
			*/
        );
        $arrUnitSendToEnemy = array(
            'x' => $arrTwo['x'],
            'y' => $arrTwo['y'],
            'id' => $arrTwo['uid']/*,
            'nation' => $this->account->nation,
            'type' => 'u',
            'unitType' => $arrTwo['unitType'],
            'uid' => $arrTwo['tacticalDataID'],
            'owner' => $this->account->playerID,
            'td' => (isset($arrPlutons[$arrTwo['uid']]) ? $arrPlutons[$arrTwo['uid']] : array()),
            'Morale' => $moralenowe,
            'experience' => $doswiadczenienowe,
            'Specialty' => $func,
            'DefensePoints' => $pancerznowy,
            'nickName' => $this->account->login,
            'nazwa' => $nazwa
			*/
        );
        //jednostka do usuniecia z  mapki
        $dellUnit = array(
            'x' => $arrOne['x'],
            'y' => $arrOne['y'],
            'id' => $arrOne['uid']
        );
        //samouczek, sprawdzamy, czy gracz połączył jednostki
        
        $arrayData = array('activity' => 'loadLC');

        // Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
        $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
            'data' => array(
                'unitOne' => $arrUnitSendInfo,
                'unitTwo' => $dellUnit,
                'enemyUnitsData' => $arrATNotSeenUnits
            )
        );

        if ($intIsSeen === true) {
            // Informacja dla wrogów jeśli mnie widzą
            $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . ($this->account->nation == 1 ? 2 : 1),
                'data' => array(
                    'unitOne' => $arrUnitSendToEnemy,
                    'unitTwo' => $dellUnit,
                    'enemyUnitsData' => $arrATNotSeenUnits
                )
            );
        }
        $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
        echo json_encode(true);
    }





    public function loadTacticalData()
    {

        $dataFactory = array();
        $rok = $this->path->get('rok');

        if (!preg_match("/^[\d]{1,11}$/i", $rok)) $this->error->printError('Nie ma takiego roku', 'mapa');
        if ($rok == 1939) {
            $rokDB = '`rocznik` <= 1939';
        } else {
            $rokDB = '`rocznik` = ' . $rok . ' ';
        }
        $Factory = $this->db->query('SELECT *,`nazwa' . $this->account->lang . '` as `nazwa` FROM `TacticalData` WHERE ' . $rokDB . ' AND `id`!=218 AND `id`!=219 ORDER BY `NationId` ASC');
        $dataFactory['bodyTD'] = '';
        $errorBuild = '';
        foreach ($Factory as $nation) {
            $tooltiop = "
                                <tr class=\"body_table\">
                                            <td>
                                <div class=\"tooltip unit-" . $nation['id'] . "\" num=".$nation['id']." loc=\"1\"></div>
                                            </td><td class=\"kreska_jednostki\"></td><td>" . $nation['nazwa' . $this->account->lang . ''] . "</td><td class=\"kreska_jednostki\"></td><td>" . $nation['kategoria'] . "</td>
                                            <td class=\"kreska_jednostki\"></td><td>" . $nation['czas_produkcji'] . "</td><td class=\"kreska_jednostki\"></td><td>" . $nation['cena'] . "kr./" . $nation['tier'] . " poz.</td>
                                            <td class=\"kreska_jednostki\"></td>
                                        </tr>";
            $dataFactory['bodyTD'] .= $tooltiop;
        }

        $dataFactory['titleTableFactory'] = '<div id="strzalka">
                                <div id="twoje_jednostki">
                                    ' . hate_fabrica_no_active . '
                                </div>
                            </div>';
        $dataFactory['TableTD'] = '
                                <div id="fabryki_menu" style="height:100px !important;">
                                    <div class="fabryki_lista active" num="1939">dane taktyczne</div>
                                    <div id="fabryki_koszary" class="factory" num="1">' . fabryki_koszary . '</div>
                                    <div id="fabryki_czolgi" class="factory" num="2">' . fabryki_czolgi . '</div>
                                    <div id="fabryki_dziala" class="factory" num="3">' . fabryki_dziala . '</div>
                                    <div id="fabryki_hangar" class="factory" num="4">' . fabryki_hangar . '</div>';
        $podlozeMiasta = $this->db->query('SELECT `mapData`.`fieldType` from `mapData`
                            LEFT JOIN `units` ON `mapData`.`x`=`units`.`x` AND `mapData`.`y`=`units`.`y`
                            WHERE `units`.`playerID`=' . $this->account->playerID . ' AND `units`.`unitType`=7 AND `mapData`.`worldID`= ' . $this->world->id . ' LIMIT 1')->fetch();
        if ($podlozeMiasta['fieldType'] != 11) {
            $dataFactory['TableTD'] .= '<div id="fabryki_stocznia" class="block" title=" '. comp_index30 .'">' . fabryki_stocznia . '</div>';
        } else {
            $dataFactory['TableTD'] .= '<div id="fabryki_stocznia" class="factory" num="5">' . fabryki_stocznia . '</a></div>';
        }
        $dataFactory['TableTD'] .= '
                                </div>';
        $active1 = $active2 = $active3 = $active4 = $active5 = $active6 = $active7 = '';
        if ($rok == 1939) {
            $active1 = 'active';
        } else if ($rok == 1940) {
            $active2 = 'active';
        } else if ($rok == 1941) {
            $active3 = 'active';
        } else if ($rok == 1942) {
            $active4 = 'active';
        } else if ($rok == 1943) {
            $active5 = 'active';
        } else if ($rok == 1944) {
            $active6 = 'active';
        } else if ($rok == 1945) {
            $active7 = 'active';
        }

        $dataFactory['TableTDm'] = '
            <div id="menum">
                <div class="fabryki_lista ' . $active1 . '" num="1939">'. comp_index120 .' 1939</div>
                <div class="fabryki_lista ' . $active2 . '" num="1940">'. comp_index120 .' 1940</div>
                <div class="fabryki_lista ' . $active3 . '" num="1941">'. comp_index120 .' 1941</div>
                <div class="fabryki_lista ' . $active4 . '" num="1942">'. comp_index120 .' 1942</div>
                <div class="fabryki_lista ' . $active5 . '" num="1943">'. comp_index120 .' 1943</div>
                <div class="fabryki_lista ' . $active6 . '" num="1944">'. comp_index120 .' 1944</div>
                <div class="fabryki_lista ' . $active7 . '" num="1945">'. comp_index120 .' 1945</div>
            </div>
            <div id="tabela">';
        $dataFactory['headTD'] = '';
        $dataFactory['tableDeff'] = '
                        <table class="table_style">
                            <tr class="padding_top">
                                <td colspan="11"></td>
                            </tr>
                            <tr class="naglowek_tabeli">
                                <td>' . ikona_txt . '</td><td class="kreska_jednostki"></td><td>' . fabryki_nazwa . '</td><td class="kreska_jednostki"></td><td>' . fabryki_kategoria . '</td>
                                <td class="kreska_jednostki"></td><td>' . fabryki_czas_produkcji . '</td><td class="kreska_jednostki"></td><td>' . fabryki_wymagania . '</td>
                            </tr>';
        $dataFactory['tableEnd'] = '
                                        <tr class="padding_bottom">
                                            <td colspan="11"></td>
                                        </tr>
                                    </table>
                                </div>';
        echo json_encode($dataFactory);

    }

    public function rebuildCity()
    {
        $id = $this->path->post('id');
        $objHelper = init::getFactory()->getService('helper');
        $naprawianeMiasto = $this->db->query('SELECT * from `units` WHERE `id`=' . $id . ' LIMIT 1')->fetch();
        $nowyDP = 0;
        if ($this->account->softCurrency + $this->account->PremiumCurrency >= 2000) {
            if ($naprawianeMiasto['DefensePoints'] >= 100) {
                if ($this->account->softCurrency <= 0 AND $this->account->PremiumCurrency >= 2000) {
                    if ($naprawianeMiasto['DefensePoints'] >= 140 AND $naprawianeMiasto['DefensePoints'] < 150) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=150 WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = 150;
                    } else if ($naprawianeMiasto['DefensePoints'] < 140) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=( `DefensePoints` + 10 ) WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = $naprawianeMiasto['DefensePoints'] + 10;
                    }
                    $this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=( `PremiumCurrency` - 2000 ) WHERE `id`=' . $this->account->id . ' ');
                    $softCurrency = 0;
                    $premiumCurrency = 2000;

                } else if ($this->account->softCurrency >= 2000) {
                    if ($naprawianeMiasto['DefensePoints'] >= 140 AND $naprawianeMiasto['DefensePoints'] < 150) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=150 WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = 150;
                    } else if ($naprawianeMiasto['DefensePoints'] < 140) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=(`DefensePoints`+10) WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = $naprawianeMiasto['DefensePoints'] + 10;
                    }
                    $this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency` - 2000 ) WHERE `id`=' . $this->account->playerID . ' ');
                    $softCurrency = 2000;
                    $premiumCurrency = 0;
                } else if ($this->account->softCurrency < 2000 AND $this->account->softCurrency > 0 AND $this->account->PremiumCurrency >= (2000 - $this->account->softCurrency)) {
                    if ($naprawianeMiasto['DefensePoints'] >= 140 AND $naprawianeMiasto['DefensePoints'] < 150) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=150 WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = 150;
                    } else if ($naprawianeMiasto['DefensePoints'] < 140) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=(`DefensePoints`+10) WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = $naprawianeMiasto['DefensePoints'] + 10;
                    }
                    $nowa_kasa = 2000 - $this->account->softCurrency;
                    $this->db->exec('UPDATE `players` SET `softCurrency` = 0 WHERE `id`=' . $this->account->playerID . ' ');
                    $this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $nowa_kasa . ' ) WHERE `id`=' . $this->account->id . ' ');
                    $softCurrency = $this->account->softCurrency;
                    $premiumCurrency = $nowa_kasa;

                }
                $sumCurrency = 2000;
                $arrayData = array('activity' => 'rebuildCity', 'chanData' => array(
                    array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
                        'data' => array(
                            'unitData' => array(
                                'x' => $naprawianeMiasto['x'],
                                'y' => $naprawianeMiasto['y'],
                                'CityName' => $naprawianeMiasto['CityName'],
                                'id' => $id,
                                'view' => 6,
                                'nation' => $this->account->nation,
                                'unitType' => 7,
                                'uid' => $id,
                                'owner' => $naprawianeMiasto['playerID'],
                                'DefensePoints' => $nowyDP,
                                'un' => '',
                                'playerVacation' => ''
                            ),
                            'userData' => array(
                                'softCurrency' => $softCurrency,
                                'PremiumCurrency' => $premiumCurrency,
                                'sumCurrency' => $sumCurrency,
                                'playerID' => $this->account->playerID
                            )
                        )

                    )
                ));
                $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

                echo json_encode(true);
            } else {
                if ($this->account->softCurrency <= 0 AND $this->account->PremiumCurrency >= 1000) {
                    if ($naprawianeMiasto['DefensePoints'] >= 90) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=100 WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = 100;
                    } else if ($naprawianeMiasto['DefensePoints'] < 90) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=(`DefensePoints`+10) WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = $naprawianeMiasto['DefensePoints'] + 10;
                    }
                    $this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-1000 ) WHERE `id`=' . $this->account->id . ' ');
                    $softCurrency = 0;
                    $premiumCurrency = 1000;
                } else if ($this->account->softCurrency >= 1000) {
                    if ($naprawianeMiasto['DefensePoints'] >= 90) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=100 WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = 100;
                    } else if ($naprawianeMiasto['DefensePoints'] < 90) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=(`DefensePoints`+10) WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = $naprawianeMiasto['DefensePoints'] + 10;
                    }
                    $this->db->exec('UPDATE `players` SET `softCurrency`=( `softCurrency` - 1000 ) WHERE `id`=' . $this->account->playerID . ' ');
                    $softCurrency = 1000;
                    $premiumCurrency = 0;
                } else if ($this->account->softCurrency < 1000 AND $this->account->softCurrency > 0 AND $this->account->PremiumCurrency >= (1000 - $this->account->softCurrency)) {
                    if ($naprawianeMiasto['DefensePoints'] >= 90) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=100 WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = 100;

                    } else if ($naprawianeMiasto['DefensePoints'] < 90) {
                        $this->db->exec('UPDATE `units` SET `DefensePoints`=( `DefensePoints`+ 10 ) WHERE `id`=' . $id . ' ');//dodawanie punktów obrony do miasta gracza
                        $nowyDP = $naprawianeMiasto['DefensePoints'] + 10;
                    }
                    $softCurrency = 1000 - $this->account->softCurrency;
                    $premiumCurrency = 0;
                    $this->db->exec('UPDATE `players` SET `softCurrency`= 0 WHERE `id`=' . $this->account->playerID . ' ');
                    $this->db->exec('UPDATE `accounts` SET `PremiumCurrency`=( `PremiumCurrency` - ' . $nowa_kasa . ' ) WHERE `id`= ' . $this->account->id . ' ');
                }
                $sumCurrency = 1000;
                $arrayData = array('activity' => 'rebuildCity', 'chanData' => array(
                    array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
                        'data' => array(
                            'unitData' => array(
                                'x' => $naprawianeMiasto['x'],
                                'y' => $naprawianeMiasto['y'],
                                'CityName' => $naprawianeMiasto['CityName'],
                                'id' => $id,
                                'view' => 6,
                                'nation' => $this->account->nation,
                                'unitType' => 7,
                                'uid' => $id,
                                'owner' => $naprawianeMiasto['playerID'],
                                'DefensePoints' => $nowyDP,
                                'un' => '',
                                'playerVacation' => ''
                            ),
                            'userData' => array(
                                'softCurrency' => $softCurrency,
                                'PremiumCurrency' => $premiumCurrency,
                                'sumCurrency' => $sumCurrency,
                                'playerID' => $this->account->playerID
                            )
                        )

                    )
                ));
                $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

                echo json_encode(true);
            }
        } else {
            $this->error->printError('Nie masz pieniedzy aby wykonac to działanie', 'mapa');
        }

    }

    public function loadSingleNews()
    {
        $newsID = $this->path->post('data');
        $newsData = array();
        $newsData = $this->db->query('SELECT * from `news` WHERE `id`=' . $newsID . ' LIMIT 1')->fetch();
        $newsData['date'] = date("Y-m-d", $newsData['date']);

        echo json_encode($newsData);
    }

	public function loadMission()
	{
		$data = [];
		$data['height'] = 130;
		if( $this -> account -> rankLevel >= 2  ){
			if( $this -> account -> id == 1 ){
				//sprawdzam, czy gracz zlecał już jakąś misję, jeśli nie, to pokazuję mu informację do czego służą misje
				$mission = $this -> db-> query('SELECT * from `mission` WHERE `missionComplete`= 0 AND `nation` = '.$this -> account -> nation.' AND `rank`>= '.$this -> account -> rankLevel.' AND `worldID`='. $this->world->id .' ');
				$ileMisji = $mission->rowCount();
				if( $ileMisji == 0 ){
					$data['info'] = '
					<div id="yourMission">
						<div id="imageMission"></div>
						'. comp_index121 .'.<br>
					</div>
					<div id="PrepareMission">'. comp_index122 .':<br>
						<form id="newMission">
							<select name="newCommand">
								<option value="1" selected="selected">'. comp_index123 .'<br>
							</select><br>
							<input type="text" placeholder="'. comp_index124 .'" id="placeCommand"><br>
							<input type="button" value="'. comp_index125 .'"><br>
						</form>
					</div>
					';
				}else{
					$data['info'] = comp_index126 .': '.$ileMisji.'<br><table width="100%"><tr><td>Zadanie misji</td><td>Koordynaty zadania</td><td> działanie</td></tr>';
					$i = 1;
					foreach ($mission as $singleMission ){
						switch($singleMission['missionID']){
							case 1:
							$nazwaMisji = comp_index130 .'. '.$i;
							break;
						}
						switch($singleMission['corpsID']){
							case 1:
								$korpusMisji = 'piechoty ';
							break;
							case 2:
								$korpusMisji = 'pancernych ';
							break;
							case 3:
								$korpusMisji = 'przeciwlotniczych ';
							break;
							case 4:
								$korpusMisji = 'lotnictwa ';
							break;
							case 5:
								$korpusMisji = 'floty ';
							break;
							case 6:
								$korpusMisji = 'artylerii ';
							break;
							case 7:
								$korpusMisji = 'wszystkich rodzajów ';
							break;
						}
						
						if( $singleMission['playerID'] == $this -> account -> playerID ){
							$crashMission = '<div class="crashMission" num="'. $singleMission['id'] .'">USUŃ MISJĘ</div>';
						}else{
							$crashMission = ' ';
						}
						$data['info'] .=  '<tr><td>'. comp_index123.' '.$korpusMisji.'</td><td><div class="goToCoords" x="'.$singleMission['x'].'" y="'.$singleMission['y'].'">['.$singleMission['x'].','.$singleMission['y'].']</div></td><td>'. $crashMission .'</td></tr>';
						$i++;
						$data['height'] += 50;
					}
					
					$data['info'] .= '</table>';
				}
			}else{
				$data['info'] = comp_index127 .'! ;)  hi hi';
			}
		}else{
			$data['info'] = comp_index127 .'! ;)  hi hi';
		}
		echo json_encode($data);
	}
	
	public function prepareMission()
	{
		$xCell = $this->path->post('x');
		$yCell = $this->path->post('y');
		$data=array();
		if( $this -> account -> rankLevel >= 2  ){
			if( $this -> account -> id == 1 ){
					//sprawdzam, czy gracz zlecał już jakąś misję, jeśli nie, to pokazuję mu informację do czego służą misje
					$mission = $this -> db-> query('SELECT count(*) from `mission` WHERE `missionComplete`= 0 AND `nation` = '.$this -> account -> nation.' AND `rank`>= '.$this -> account -> rankLevel.' AND `x`<=' . ($xCell + 10) . ' AND `y`>=' . ($yCell - 10) . ' AND `x`>='.($xCell - 10).' AND `y`<='.($yCell+ 10).'')->fetch();
				$ileMisji = (int)$mission[0];
				if( $ileMisji == 0 ){
					$data['info'] = '
					<div id="yourMission">
						<div id="imageMission"></div>
						'. comp_index128 .'.<br>
					</div>
					<div id="prepareMission">
						<form id="newMission" coordx="'.$xCell.'" coordy="'.$yCell.'">
							<select id="newCommand">
								<option value="1" selected="selected">'. comp_index123 .'<br>
							</select><br>
							koordynaty rozkazu: '.$xCell.','.$yCell.'<br>
							<select id="corpusNewCommand">
								<option value="1"> '. piechota_txt .'<br>
								<option value="2">'. pancerne_txt .'<br>
								<option value="3">'. przeciwlotnicze_txt .'<br>
								<option value="4">'. lotnictwo_txt .'<br>
								<option value="5">'. flota_txt .'<br>
								<option value="6">'. artyleria_txt .'<br>
								<option value="7" selected="selected">'. comp_index129 .'<br>
							</select><br>
							<div id="btn_formMission">'. comp_index125 .'</div>
						</form>
					</div>';
				}else{
					$mission = $this -> db-> query('SELECT * from `mission` WHERE `missionComplete`= 0 AND `nation` = '.$this -> account -> nation.' AND `rank`>= '.$this -> account -> rankLevel.' AND `x`<=' . ($xCell + 10) . ' AND `y`>=' . ($yCell - 10) . ' AND `x`>='.($xCell - 10).' AND `y`<='.($yCell+ 10).'');
						$nazwaMisjiTXT = '';
						
						foreach ($mission as $singleMission) {
							$i = 1;
							switch($singleMission['missionID']){
								case 1:
								$nazwaMisji = comp_index130 .'. '. $i .': '. comp_index123;
								break;
							}
							switch($singleMission['corpsID']){
								case 1:
									$korpusMisji = 'piechoty ';
								break;
								case 2:
									$korpusMisji = 'pancernych ';
								break;
								case 3:
									$korpusMisji = 'przeciwlotniczych ';
								break;
								case 4:
									$korpusMisji = 'lotnictwa ';
								break;
								case 5:
									$korpusMisji = 'floty ';
								break;
								case 6:
									$korpusMisji = 'artylerii ';
								break;
								case 7:
									$korpusMisji = 'wszystkich rodzajów ';
								break;
							}
							
							$nazwaMisjiTXT .= $nazwaMisji .''.$korpusMisji.' '. comp_index131 .' <div class="goToCoords" x="'.$singleMission['x'].'" y="'.$singleMission['y'].'">['.$singleMission['x'].','.$singleMission['y'].']</div>';
							$i++;
						}
								
					
					$data['info'] = $nazwaMisjiTXT;
					
				}
			}else{
				$data['info'] = comp_index127 .'! ;)  hi hi';
			}
		}else{
			$data['info'] = comp_index127 .'! ;)  hi hi';
		}
		echo json_encode($data);
	}
	
	public function saveMission(){
		$missionID = $this->path->post('mission');
		$corpsID = $this->path->post('corpsID');
		$x       = $this->path->post('x');
		$y       = $this->path->post('y');
		$data = [];
		
		if($this->account->id == 1){
			$this->db->exec('INSERT INTO `mission` (`x`, `y`, `nation`, `worldID`, `corpsID`, `missionID`,`rank`,`playerID`, `missionComplete`) VALUES ('.$x.', '.$y.','. $this->account->nation .', ' . $this->world->id . ', '. $corpsID .', '. $missionID .', '. $this->account->rankLevel .', '. $this->account->playerID .', 0 )');
			$data['info'] = '1:'.$missionID.',2:'.$corpsID.',3:'.$x.',4:'.$y;
		}else{
			$data['info'] = comp_index132 .' :)';
		}
		echo json_encode($data);
	}
	
	public function crashMission(){
		$missionID = $this->path->post('mission');
		$data = [];
		$mission = $this -> db-> query('SELECT * from `mission` WHERE `id`= '. $missionID .' ');
		$missionData = $mission->fetch();
				
		if( $this->account->playerID == $missionData['playerID'] ){
			$this->db->exec('DELETE FROM `mission` WHERE `id`=' . $missionID . ' LIMIT 1');
			$data['info'] = 'Misja '.$missionID.' została anulowana';
		}else{
			$data['info'] = comp_index132 .' :)';
		}
		echo json_encode($data);
	}
	
	public function checkTiming()
    {
		$idTimera = $this->path->post('idTimera');
		
		$exploded = explode("_",$idTimera);
		$odpowiedz = [];
		$idJednostki = $exploded[0];
		$numerLicznika = $exploded[1];
		$timing = $this -> db-> query('SELECT `timing'. $numerLicznika .'` as `timing` from `units` WHERE `playerID`=' . $this -> account -> playerID . ' AND `id`='. $idJednostki)->fetch();
		if( $timing['timing'] > 0 ){
			if( $timing['timing'] < $this -> init -> time ){
				$odpowiedz['idTimera'] = $idTimera;
				$odpowiedz['timer'] ='off';
			}else{
				$odpowiedz['timer'] ='on';
			}
		}else{
			$odpowiedz['error'] = comp_index133;
		}
		echo json_encode($odpowiedz);
	}
	
	public function checkTimingBuild()
    {
		$idTimera = $this->path->post('idTimera');
		$odpowiedz = [];
		$timing = $this -> db-> query('SELECT * from `UnitBuild` WHERE `id`='. $idTimera .' AND `playerID`=' . $this -> account -> playerID . '')->fetch();
		
			if( $timing['TurnBuild'] < $this -> init -> time AND $timing['TurnBuild'] != '' ){
				$odpowiedz['idTimera'] = $idTimera;
				$odpowiedz['timer'] ='off';
				$this->account->buildUnit($timing['tacticalDataID'], $this->account->playerID, 0);
				$odpowiedz['goto'] = comp_index117;
				$this->db->exec('DELETE FROM `UnitBuild` WHERE `tacticalDataID`=' . $timing['tacticalDataID'] . ' AND `playerID`=' . $this->account->playerID . ' LIMIT 1');
			
			}else{
				$odpowiedz['timer'] ='on';
			}
		echo json_encode($odpowiedz);
	}
	
	public function checkTimingK()
    {
		$idTimera = $this->path->post('idTimera');
		$odpowiedz = [];
		$timing = $this -> db-> query('SELECT `timeK` from `players` WHERE `id`=' . $this -> account -> playerID . ' ')->fetch();
			if( $timing['timeK'] < $this -> init -> time ){
				$odpowiedz['idTimera'] = $idTimera;
				$odpowiedz['timer'] ='off';
				$this -> account -> playerFinance( $this->account->playerID, $this->account->experience, $this->account->nation);
			}else{
				$odpowiedz['timer'] ='on';
			}
		echo json_encode($odpowiedz);
	}
	
	
	public function getGold()
    {
		$gold = $this -> db-> query('SELECT * from `getGold` WHERE `playerID`=' . $this -> account -> playerID . ' AND `getGold`= 0 ');
		$odpowiedz = array();
		$odpowiedz['info'] = '';
		$odpowiedz['plusGold']  = 0;
		if( count($gold) > 0 ){
			foreach ($gold as $singleAddedGold) {
				$this->db->exec('UPDATE `getGold` SET `getGold`=' . $this->init->time . ' WHERE `id`=' . $singleAddedGold['id'] . ' ');
				$odpowiedz['plusGold'] += $singleAddedGold['gold'];
				$this->db->exec('UPDATE `accounts` SET `gold`=(`gold`+' . $singleAddedGold['gold'] . ') WHERE `id`=' . $this->account->id . ' ');
				$odpowiedz['info'] .= comp_index134 .' '.$singleAddedGold['gold'].' '. zlota .'<br>';
			}
			
		}else{
			$odpowiedz['error'] = comp_index135;
		}
		echo json_encode($odpowiedz);
	}
	
	public function getUnitData()
    {
		$idJednostki = $this->path->post('unitID');
		$lokalizacja = $this->path->post('loc');
		$odpowiedz = array();
		$odpowiedz['bodyTable'] ='lok='.$lokalizacja;
		switch($lokalizacja){
			case 1://ikonka kliknieta w fabryce, pokazujemy normalne parametry jednostki
				$unitData = $this->db->query('SELECT * FROM `TacticalData` WHERE `id`='.$idJednostki.' LIMIT 1')->fetch();
                if( $unitData['CorpsId'] == 5) // flota morska
                {
                    $ostrzalTable ="
						<tr>
							<td>" . fabryki_war_table . "</td><td class=\"first_kreska_fabryki\"></td><td> ". comp_index136 .": " . $unitData['atak_flota'] . " | ". comp_index137 .": " . $unitData['atak_podwodne'] . " | ". comp_index138 .": " . $unitData['atak_glebinowe'] . "</td><td class=\"first_kreska_fabryki\"></td><td> ". comp_index136 .": " . $unitData['obrona_flota'] . " | ". comp_index137 .": " . $unitData['obrona_podwodne'] . " | ". comp_index138 .": " . $unitData['obrona_glebinowe'] . "</td>
						</tr>
						<tr class=\"zasiegi\">
							<td>" . zasieg_strzalu_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\">". comp_index136 .":<strong>" . $unitData['ostrzal_od'] . "-" . $unitData['ostrzal'] . "</strong> | ". comp_index137 .":<strong>".$unitData['ostrzal_torpeda']."</strong> | ". comp_index138 .": <strong>".$unitData['ostrzal_glebinowe']."</strong> | ". comp_index139 .": <strong>".$unitData['ostrzal_przeciwlotniczy']."</strong></td>
						</tr>";
                }else{
                    $ostrzalTable = "
						<tr>
							<td>" . fabryki_war_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_flota'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_flota'] . "</td>
						</tr>
						<tr class=\"zasiegi\">
							<td>" . zasieg_strzalu_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\"><b>" . $unitData['ostrzal_od'] . "-" . $unitData['ostrzal'] . "</b></td>
						</tr>";
                }
                $tooltiop = "
			<div id=\"closeOknoJednostki\"></div>
			<div id=\"srobka_left_up\"></div>
			<div id=\"srobka_right_down\"></div>
			<h3 class=\"nazwa_fabryki\">" . $unitData['nazwa' . $this->account->lang . ''] . "</h3>
			<div class=\"grafika_fabryki\" style=\"background:url( ./app/templates/assets/images/" . $unitData['obrazek_duzy'] . ") no-repeat;\" title=\"" .$unitData['nazwa' . $this->account->lang . ''] . "\">
			</div>	
			<p class=\"opis_fabryki\">" . $unitData['opis' . $this->account->lang . ''] . "</p>
			
						<table class=\"tabelka_fabryki\">
							<tr class=\"naglowek_tabela_jednostki\">
								<td><b>" . fabryki_corp_table . "</b></td><td class=\"kreska_jednostki\"></td><td><b>" . fabryki_att_table . "</b></td><td class=\"kreska_jednostki\"></td><td><b>" . fabryki_def_table . "</b></td>
							</tr>
							
							<tr>
								<td>" . fabryki_arty_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_artyleria'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_artyleria'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_inf_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_piechota'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_piechota'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_tanks_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_pancerne'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_pancerne'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_anty_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_przeciwlotnicze'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_przeciwlotnicze'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_airc_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_lotnictwo'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_lotnictwo'] . "</td>
							</tr>

							".$ostrzalTable."
							<tr class=\"zasiegi\">
								<td>" . zasieg_ruchu_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\"><b>" . $unitData['ruch'] . "</b></td>
							</tr>
							<tr class=\"zasiegi\">
								<td>" . zasieg_widzenia_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\"><b>" . $unitData['widocznosc'] . "</b></td>
							</tr>
						</table></div>
						</div>";
                $odpowiedz['bodyTable'] = $tooltiop;
			break;
			case 2://ikonka kliknieta w sklepie,  pokazujemy parametry jednostki o podanym id z listy jednostek
				$unitData = $this->db->query('SELECT `td`.*,`u`.`tacticalDataID`,`u`.`id` as `uid`, `u`.`unitExperience` FROM `TacticalData` AS `td` LEFT JOIN `units` AS `u` ON (`u`.`tacticalDataID`=`td`.`id`) WHERE `u`.`tacticalDataID`=`td`.`id` AND `u`.`id` = '.$idJednostki.' ')->fetch();
			   if( $unitData['CorpsId'] == 5) // flota morska
				{
					$ostrzalTable ="
						<tr>
							<td>" . fabryki_war_table . "</td><td class=\"first_kreska_fabryki\"></td><td>". comp_index136 .": " . $unitData['atak_flota'] . " | ". comp_index137 .": " . $unitData['atak_podwodne'] . " | ". comp_index138 .": " . $unitData['atak_glebinowe'] . "</td><td class=\"first_kreska_fabryki\"></td><td>". comp_index136 .": " . $unitData['obrona_flota'] . " | ". comp_index137 .": " . $unitData['obrona_podwodne'] . " | ". comp_index138 .": " . $unitData['obrona_glebinowe'] . "</td>
						</tr>
						<tr class=\"zasiegi\">
							<td>" . zasieg_strzalu_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\">". comp_index136 .":<b>" . $unitData['ostrzal_od'] . "-" . $unitData['ostrzal'] . "</b> | ". comp_index137 .":<b>".$unitData['ostrzal_torpeda']."</b> | ". comp_index138 .": <b>".$unitData['ostrzal_glebinowe']."</b> | ". comp_index139 .": <b>".$unitData['ostrzal_przeciwlotniczy']."</b></td>
						</tr>";
				}else{
					$ostrzalTable = "
						<tr>
							<td>" . fabryki_war_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_flota'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_flota'] . "</td>
						</tr>
						<tr class=\"zasiegi\">
							<td>" . zasieg_strzalu_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\"><b>" . $unitData['ostrzal_od'] . "-" . $unitData['ostrzal'] . "</b></td>
						</tr>";
				}
                $tooltiop = "
				<div id=\"closeOknoJednostki\"></div>
				<div id=\"srobka_left_up\"></div>
				<div id=\"srobka_right_down\"></div>
				<h3 class=\"nazwa_fabryki\">" . $unitData['nazwa' . $this->account->lang . ''] . "</h3>
				<div class=\"grafika_fabryki\" style=\"background:url( ./app/templates/assets/images/" . $unitData['obrazek_duzy'] . ") no-repeat;\" title=\"" .$unitData['nazwa' . $this->account->lang . ''] . "\"></div>
					<div class=\"opis_male\">
						skład<br>" . sklep5 . ": <b>" . $unitData['uid'] . "</b><br>
							 <b>" . sklep6 . "</b>: " . $unitData['unitExperience'] . "<br>
					</div>
						<table class=\"tabelka_fabryki\">
							<tr class=\"naglowek_tabela_jednostki\">
								<td><b>" . fabryki_corp_table . "</b></td><td class=\"kreska_jednostki\"></td><td><b>" . fabryki_att_table . "</b></td><td class=\"kreska_jednostki\"></td><td><b>" . fabryki_def_table . "</b></td>
							</tr>
							<tr>
								<td>" . fabryki_arty_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_artyleria'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_artyleria'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_inf_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_piechota'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_piechota'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_tanks_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_pancerne'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_pancerne'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_anty_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_przeciwlotnicze'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_przeciwlotnicze'] . "</td>
							</tr>
							<tr>
								<td>" . fabryki_airc_table . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['atak_lotnictwo'] . "</td><td class=\"first_kreska_fabryki\"></td><td>" . $unitData['obrona_lotnictwo'] . "</td>
							</tr>

							".$ostrzalTable."
							<tr class=\"zasiegi\">
								<td>" . zasieg_ruchu_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\"><b>" . $unitData['ruch'] . "</b></td>
							</tr>
							<tr class=\"zasiegi\">
								<td>" . zasieg_widzenia_txt . "</td><td class=\"first_kreska_fabryki\"></td><td style=\"text-align:left;\" colspan=\"3\"><b>" . $unitData['widocznosc'] . "</b></td>
							</tr>
						</table></div>
						</div>";
                $odpowiedz['bodyTable'] = $tooltiop;
			break;
		}
		echo json_encode($odpowiedz);
	}
	public function odbierzNagrode(){
		$odpowiedz = [];
		$sprawdzPolecanych = $this->db->query('SELECT `acc`.`id`, `acc`.`login` FROM `accounts` AS `acc` '
                . 'LEFT JOIN `players` AS `p` ON ( `p`.`accountID`=`acc`.`id` ) '
                . 'WHERE `acc`.`refID`=' . $this -> account -> id . ' AND `p`.`points`>=150 GROUP BY `acc`.`id`');
		$iluPolecajacych = 0;
		$sumaNagrod = 0;
		foreach ($sprawdzPolecanych as $rowPolecajacy) {
			if ($rowPolecajacy['id'] != ''){
				$iluPolecajacych++;
				$sumaNagrod += 100;
			}
        }
		if($iluPolecajacych > 0){
			$this -> db -> exec('UPDATE `accounts` SET `gold`=( `gold`+'.$sumaNagrod.') WHERE `id`=' . $this -> account -> id . ' LIMIT 1');
			$this -> db -> exec('UPDATE `accounts` SET `refID`=0 WHERE `refID`=' . $this -> account -> id . ' ');
		}
		$odpowiedz['gold'] = $sumaNagrod;
		$odpowiedz['info'] = comp_index140;
		echo json_encode($odpowiedz);
	}
	
	
	public function showUnitList(){
		$odpowiedz = array();
		echo json_encode($odpowiedz);
	}
	
	public function shuffleBaner()
    {
		$odpowiedz = [];
		$blockID = $this->path->get('num');
		$odpowiedz['active'] = 0;
		$odpowiedz['baner'] = '';
		if( $this->account->SponsorAccount > $this->init->time ){
			$odpowiedz['active'] = 1;
		}else{
			// sprawdzam, ile jest z rek 0, jesli nie ma- to liczę rekord 1 ;)
			$rows = $this -> db -> query('SELECT * FROM `banerData` WHERE `dataEnd`>'. $this -> init -> time .' AND `placeID`='. $blockID .' AND ( `dispEnd` > `disp` ) ORDER BY rand() LIMIT 1')->fetch();
			$this -> db -> exec('UPDATE `banerData` SET `disp`=( `disp`+1 ) WHERE `id`='.$rows['id'].' LIMIT 1');
			$txt = '';
			if( $rows['destAddress'] == '/reklama'){
				$txt = '<p class="absolute">Chcesz mieć tutaj swoją reklamę? Dodaj swój baner!</p>';
			}
			
			switch( $rows['placeID'] ){
				case 1:// baner długi
					$div = '<div class="reklama728_90">';
				break;
				case 2:// baner 470x90 panel
					$div = '<div class="reklama470_90">';
				break;
				case 3:// baner 470x90 fabryki
					$div = '<div class="reklama470_90F">';
				break;
			}
			$odpowiedz['baner'] = 'Wspierają nas '. $div .'<a href="'. $rows['destAddress'] .'">'. $txt .'<img src="'. $rows['bannerPath'] .'" alt="Chcesz mieć tutaj swoją reklamę? Dodaj swój baner!"/></a></div>';
		}
		echo json_encode($odpowiedz);
	}
	
	public function shuffleBanerF($blockID)
    {
		/*
		
		$odpowiedz = [];
		$odpowiedz['active'] = 0;
		$odpowiedz['baner'] = '';
		if( $this->account->SponsorAccount > $this->init->time ){
			$odpowiedz['active'] = 1;
		}else{
			// sprawdzam, ile jest z rek 0, jesli nie ma- to liczę rekord 1 ;)
			$rows = $this -> db -> query('SELECT * FROM `banerData` WHERE `dataEnd`>'. $this -> init -> time .' AND `placeID`='. $blockID .' AND ( `dispEnd` > `disp` ) ORDER BY disp ASC LIMIT 1')->fetch();
			$this -> db -> exec('UPDATE `banerData` SET `disp`=( `disp`+1 ) WHERE `id`='.$rows['id'].' LIMIT 1');
			$txt = '';
			if( $rows['destAddress'] == '/reklama'){
				$txt = '<p class="absolute">Chcesz mieć tutaj swoją reklamę? Dodaj swój baner!</p>';
			}
			
			switch( $rows['placeID'] ){
				case 1:// baner długi
					$div = '<div class="reklama728_90">';
				break;
				case 2:// baner 470x90 panel
					$div = '<div class="reklama470_90">';
				break;
				case 3:// baner 470x90 fabryki
					$div = '<div class="reklama500_50">';
				break;
			}
			$odpowiedz['baner'] = 'Wspierają nas '. $div .'<a href="'. $rows['destAddress'] .'">'. $txt .'<img src="'. $rows['bannerPath'] .'" alt="Chcesz mieć tutaj swoją reklamę? Dodaj swój baner!"/></a></div>';
		}
		return $odpowiedz['baner'];
		*/
	}
	
	public function checkMaterials(){
		// kod obsługujący surowce
		$back = [];
		$mt = $this -> db -> query('SELECT `materialTechnology`,`optimizationStorage` FROM `building` WHERE `playerID` =  \''. $this -> account -> playerID .'\' ')->fetch();
		$mLA = $this -> db -> query('SELECT `lastAction`, `rawMaterials` FROM `players` WHERE `id` =  \''. $this -> account -> playerID .'\' ')->fetch();
		$b = (int)$mt['materialTechnology'] * 14;
		$procentFor = $b/100;
		$startFor = 0.1;
		if( (int)$mt['materialTechnology'] > 0 ){
			$czas = $this -> init ->time - $mLA['lastAction'];
			if($czas > 0 ){
				//obliczamy, ile surowca doliczyć za każdą sekundę
				$rawMat = ( $startFor + ( $startFor * $procentFor ) );
				if( round($mLA['rawMaterials'] + ( $rawMat*$czas  ) ) < 100000 + ( $mt['optimizationStorage'] * 100000 ) ){
					$back['rawMaterials'] = round($mLA['rawMaterials'] + ( $rawMat*$czas  ) );
					$this->tmplData['variables']['rawMaterials'] = $back['rawMaterials'];
					$saveMat = $mLA['rawMaterials']+($rawMat*$czas);
				}else{
					$saveMat = 100000;
					$back['rawMaterials'] = 100000;
					$this->tmplData['variables']['rawMaterials'] = $back['rawMaterials'];
				}
				$this -> db -> exec('UPDATE `players` SET `rawMaterials`='. $saveMat .', `lastAction` = '. $this->init->time .'  WHERE `id`='. $this -> account -> playerID .' LIMIT 1');
			}
		}
		 echo json_encode( $back );
	}
	
	public function repairUnits(){
		/*
		// kod obsługujący naprawę jednostek
		$step = $this->path->post('s');
		$objHelper = init::getFactory() -> getService('helper');
		$back = [];
		if( $step == 1 ){
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
			where `playerID` = '. $this -> account -> playerID .' AND `DefensePoints` < 100 Group by
			`id`) `mozliwe`) `uszkodzone`') -> fetch();
			$koszt = $arrDamaged['realny_koszt'];
			
			if( $koszt > $this -> account -> gold AND $koszt != 0 )
			{
				$back['error'] = 'Nie masz złota, kup złoto';
				echo json_encode( $back );
			}
			else if( $koszt <= $this -> account -> gold )
			{
				$back['info'] ='Naprawa jednostek wyniesie '. $arrDamaged['realny_koszt'] .' szt. złota. Jesli chcesz naprawić jednostki- kliknij przycisk poniżej.<br>
				<div class="repairDiv alerton" num="2" title=" '. mapa_naprawa1 .' '.$arrDamaged['realny_koszt'].' '. sztuk_zlota_txt .'">'. $arrDamaged['realny_koszt'] .'</div>';
				$back['step'] = 1;
			}
			echo json_encode( $back );
		}else if( $step == 2 ){
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
				$arrDamaged2 = $this -> db -> query('select * FROM `units` where `playerID` = '. $this -> account -> playerID .' AND `DefensePoints` < 100 AND `x`!= 0 AND `y`!=0 ');
				$arrConnData = [];
				foreach ($arrDamaged2 as $row) {			
					$arrConnData[$row['x'] . ':' . $row['y']] = array('x' => $row['x'], 'y' => $row['y'] );
				}
				
				$this -> db -> exec('UPDATE `units` SET `DefensePoints`= 100  WHERE `playerID`='. $this -> account -> playerID .' AND `DefensePoints`< 100 AND `x`!= 0 AND `y`!=0');
				$this -> db -> exec('UPDATE `accounts` SET `gold`=( `gold`- '. $koszt .' )  WHERE `id`='. $this -> account -> id .' LIMIT 1 ');
				$back['info'] ='Jednostki naprawione!';
				$back['gold'] = $koszt;
				$back['step'] = 2;
				$back['playerID'] = $this -> account -> playerID;
				
				
				$arrayData = array('activity' => 'repairUnit');
                $arrayData['chanData'][] = array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
                    'data' => array(
                        'unitList' => $arrConnData,
						'back' => $back
                    )
                );
                $objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');

                echo json_encode(true);
                $arrayData = null;
			}
			else if( $koszt >= $this -> account -> gold )
			{
				$back['error'] = 'Nie masz złota, kup złoto';
				echo json_encode( $back );
			}
		}
		*/
		
		$back['error'] = 'Nie działa na zyczenie graczy';
		echo json_encode( $back );
	}
	
	public function writeDropArea()
    {
        $dz1  = $this->path->post('dz1');
        $dz2  = $this->path->post('dz2');
        $dz3  = $this->path->post('dz3');
        $dz4  = $this->path->post('dz4');
        $dz5  = $this->path->post('dz5');
		$dCat = $this->path->post('dCat');
		$back = [];
		$back['info'] = '';
		$addDropedUnit = [ $dz1, $dz2, $dz3, $dz4, $dz5 ];
		if(in_array(0, $addDropedUnit)) {
			$back['error'] = 'Aby uruchomić kolejkę budowania musisz wybrac 5 jednostek do budowy.<br> "Złap" jednostkę z listy jednostek ( najedź kursorem myszki na grafikę jednostki, wciśnij prawy klawisz myszy ) i przeciągnij kursor na dane pole listy produkcji i "upuść" ( zwolnij przycisk myszy ). Zapełnij wszystkie 5 pól jednostkami a następnie kliknij przycisk "zapisz kolejkę produkcji"';
		} else {
			$droppedUnit = $this -> db -> query('SELECT * FROM `listaProdukcji` WHERE `playerID` = '. $this -> account -> playerID .' AND `dCat` = '. $dCat .' AND `endBuild` > 0 ')->fetch();
			if ( (int)$droppedUnit[0] > 0 ) {
				$back['error'] = ' twoja kolejka budowania jeszcze trwa';
			}else{
				$sumCost = 0;
				/*
				if( $this->world->timing == 1 ){
					$sStart = $nation['czas_produkcji'] * $this->world->timeToUp;
				}else{
					$sStart = $nation['czas_produkcji'] * 21600;
				}
				$s = $sStart;
				*/
				$buildingsData = $this->db->query('SELECT `productionOptimization`, `materialsScience` FROM `building` WHERE `playerID`= '. $this->account->playerID .' ')->fetch();
				$productionOptimization = $buildingsData['productionOptimization'];
				$materialsScience = $buildingsData['materialsScience'];
				$b = $c = 0;
				
				foreach( $addDropedUnit as &$sUnit ){
					$cena_jednostki = $this->db->query('SELECT `cena` FROM `TacticalData` WHERE `id`= ' . $sUnit . ' ')->fetch();
					$sumCost += $cena_jednostki['cena'];
				}
				// dodajemy bonus technologii do ceny
				$cost = $sumCost;
				if( $materialsScience > 0 ){
					$c = $materialsScience * 6;
					$cost = round( $sumCost - ( $sumCost * ( $c / 100 ) ) );
				}
				
				
				
				if ( $cost > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency AND $this -> account -> gold > 10 ) ){
					//gracz nie ma kasy na budowę
					if( $this -> account -> gold < 10 ){
						$back['error'] = 'Nie masz 10 sztuk złota aby uruchomić kolejkę budowania';
					}else{
						$back['error'] = 'Nie masz tylu kredytów, aby uruchomić kolejke budowania, wybierz tańsze jednostki albo wymieć złoto na kredyty';
					}
					echo json_encode($back);
					exit();
				}else{
					if ($this -> account -> softCurrency >= $cost ){
						$this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-' . $cost . ') WHERE `id`=' . $this -> account -> playerID . ' ');
						$this -> db -> exec('UPDATE `accounts` SET `gold`=(`gold`- 10 ) WHERE `id`=' . $this -> account -> id . ' ');
						$back['currency'] = $cost;
						$back['gold'] = 20;
					}else{
						if ($this -> account -> softCurrency < $sumCost AND ($this -> account -> PremiumCurrency >= ( $cost - $this -> account -> softCurrency) ) ){
							//gracz nie ma wystarczającej ilości kasy soft, wiec zerujemy kasę soft i odejmujemy od kasy Premium
							$this -> db -> exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this -> account -> playerID . ' ');
							//i pomniejszamy ilość premiumCurrency
							$new_PremiumCurrency = $cost - $this -> account -> softCurrency;
							$back['currency'] = $cost;
							$back['gold'] = 20;
							$this -> db -> exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $new_PremiumCurrency . ' ), `gold`= ( `gold`-10 ) WHERE `id`=' . $this -> account -> id . ' ');
						}else{
							$back['error'] = comp_account37;
							echo json_encode($back);
							exit();
						}
					}
				}
				
				$droppedUnitLast = $this -> db -> query('SELECT `endBuild` FROM `listaProdukcji` WHERE `playerID` = '. $this -> account -> playerID .' AND `endBuild` > 0 ORDER by `endBuild` DESC LIMIT 1')->fetch();
				if( $droppedUnitLast['endBuild'] != 0 ){
					$buildEnd = $droppedUnitLast['endBuild'];
				}else{
					$buildEnd = $this->init->time;
				}
				foreach( $addDropedUnit as $sUnit ){
					$czas_prod = $this->db->query('SELECT `czas_produkcji` FROM `TacticalData` WHERE `id`= ' . $sUnit . ' ')->fetch();
					if( $this->world-> timing == 1 ){
						$timeProduction = $czas_prod['czas_produkcji'] * $this->world->timeToUp;
						if( $productionOptimization > 0 ){
							$b = $productionOptimization * 6;
							$timeProduction = round( $timeProduction - ( $timeProduction *( $b/100 ) ) );
						}
					}else{
						$timeProduction =  $czas_prod['czas_produkcji'] * 21600;
						if( $productionOptimization > 0 ){
							$b = $productionOptimization * 6;
							$timeProduction = round( $timeProduction - ( $timeProduction *( $b/100 ) ) );
						}
					}
					
					$buildEnd = $buildEnd + $timeProduction;
					
					
					$this->db->exec('INSERT INTO `listaProdukcji` (`playerID`, `tacticalDataID`, `dCat`, `endBuild` ) VALUES ( '. $this -> account -> playerID .','.$sUnit.','.$dCat.','. $buildEnd .' )');
				}
				$back['info'] .= 'Kolejka budowania uruchomiona';
			}
		}
		echo json_encode( $back );
	}
	
	
	public function checkProductionQueue(){
		// kod obsługujący surowce
		$back = [];
		// sprAwdzam, czy coś jest wybudowane
		$droppedUnitBuild = $this -> db -> query('SELECT * FROM `listaProdukcji` WHERE `playerID` = '. $this -> account -> playerID .' AND `endBuild` <= '. $this -> init ->time .' AND `endBuild` != 0  ');
		
		$listUnit = $droppedUnitBuild->fetchAll();
		//$stmt->rowCount();
		if( $droppedUnitBuild->rowCount() > 0 ){
			foreach( $listUnit  as $sUnit ){
				$this -> account -> buildUnit( $sUnit['tacticalDataID'], $this -> account -> playerID, 0 );
				$this -> db -> exec('UPDATE `listaProdukcji` SET `endBuild`= 0 WHERE `id`=' . $sUnit['id'] . ' ');
			}
		}
		$droppedUnit = $this -> db -> query('SELECT * FROM `listaProdukcji` WHERE `playerID` = '. $this -> account -> playerID .' AND `endBuild` > 0 ORDER BY `id` ASC ');	
		$back['productionQueue'] = '<div class="titleDiv">KOLEJKA BUDOWY</div>';
		$i = 0;
		foreach( $droppedUnit as $sUnit ){
			if( $i == 5 ){
				break;
			}else{
				// pokazujemy licznik tej jednostki
				if( $i == 0 ){
					$back['toTimer'] = $sUnit['endBuild'] - $this -> init ->time;
					$back['productionQueue'] .= '<div class="unitList unit-'. $sUnit['tacticalDataID'] .'" style="float:left" title="po zakończeniu odliczania jednostka zostanie wystawiona na heks miejski"><div id="timerKU"></div></div>';
				}else{
					$back['productionQueue'] .= '<div class="unitList unit-'. $sUnit['tacticalDataID'] .'" style="float:left"></div>';
				}
			}
			$i++;
		}
		
		if( $i == 0 ){
			$back['productionQueue'] .= 'Uruchom w FABRYKACH kolejkę budowania';
		}
		echo json_encode( $back );
	}
	
	
	
	
	public function editSur(){
		$uid = $this->path->post('uid');
		$edit = $this->path->post('edit');
		$var = $this->path->post('varID');
		$iUn = 0;
		$iC = 0;
		$sponsorUnitsList = [];
        $sponsorUnitsList['showData'] = '<table class="clUnits"><tr>';
		// jednostka jest barką desantową
		// roimy liste jendostek na barce
		$getLoadingUnits = $this -> db -> query('SELECT `tacticalDataID`, `id` from `units` WHERE `idLC` = '.$uid.' AND `id` != '.$uid.' ');
		$uidData = $this -> db -> query('SELECT `x`, `y`, `rawMaterials` from `units` WHERE `id` = '.$uid.' ')->fetch();
		$iUn = 0;
		$iC = 0;
		
		switch($edit){
			case 1:
				if( $uidData['rawMaterials'] < 10000 ){
					$this -> db -> exec('UPDATE `units` SET `rawMaterials`= (`rawMaterials` + 1000 ) WHERE `id`=' . $uid . ' ');
					$this -> db -> exec('UPDATE `players` SET `rawMaterials`= (`rawMaterials` - 1000 ) WHERE `id`=' . $this->account->playerID . ' ');
					$uidData['rawMaterials'] = $uidData['rawMaterials'] + 1000;
					$sponsorUnitsList['editRawMaterials'] = round($this->account->rawMaterials - 1000);
				}
			break;
			case 2:
				if( $uidData['rawMaterials'] > 0 ){
					$this -> db -> exec('UPDATE `units` SET `rawMaterials`= (`rawMaterials` - 1000 ) WHERE `id`=' . $uid . ' ');
					if( $var > 0 ){
						// wyładunek do silosu
						$siloData = $this -> db -> query('SELECT `rawMaterials` from `siloData` WHERE `unitID` = '.$var.' ')->fetch();
						if( $siloData['rawMaterials'] >= 99000 AND $siloData['rawMaterials'] < 100000 ){
							// osiągamy minimum, więc odpalamy zegarek
							$this -> db -> exec('UPDATE `siloData` SET `time`= ( '. ( $this->init->time + 3600 ) .') WHERE `unitID`=' . $var . ' ');
						}
						$this -> db -> exec('UPDATE `siloData` SET `rawMaterials`= (`rawMaterials` + 1000 ) WHERE `unitID`=' . $var . ' ');
						$uidData['rawMaterials'] = $uidData['rawMaterials'] - 1000;
						$sponsorUnitsList['editRawMaterials'] = $this->account->rawMaterials;
					}else{
						// wyładunek do konta gracza
						$this -> db -> exec('UPDATE `players` SET `rawMaterials`= (`rawMaterials` + 1000 ) WHERE `id`=' . $this->account->playerID . ' ');
						$uidData['rawMaterials'] = $uidData['rawMaterials'] - 1000;
						$sponsorUnitsList['editRawMaterials'] = round($this->account->rawMaterials + 1000);
					}
				}
			break;
		}
		
		
		foreach ( $getLoadingUnits as $rowSingleUnits )
		{
			$iUn++;
			$sponsorUnitsList['showData'] .= '<td class="reloadUnit units unit-'.$rowSingleUnits['tacticalDataID'].'" num="'.$rowSingleUnits['id'].'" tdi="'.$rowSingleUnits['tacticalDataID'].'" clx="'.$uidData['x'].'" cly="'.$uidData['y'].'"></td>';
			if( $iUn == 5 ){
				$sponsorUnitsList['showData'] .= '</tr><tr>';
				$iUn = 0;
			}
			$iC++;
		}
		$i = 0;
		if( $this->world->warMapNum == 2 ){ // mapka morska z wysepką
			for( $i = 0; $i < ($uidData['rawMaterials']/1000); $i++ ){
				$sponsorUnitsList['showData'] .= '<td class="reloadMaterials" num="'.$uid.'" title="surowiec"></td>';
				$iUn++;
				if( $iUn == 5 ){
					$sponsorUnitsList['showData'] .= '</tr><tr>';
					$iUn = 0;
				}
			}
			// sprawdzamy, czy barka jest w zasięgu miasta lub bazy do rozładunku.
			// zakładamy że max. odległość to 3 heksy
			$dystanceToCity = $this -> unit -> hex_distance(array('x' => $uidData['x'], 'y' => $uidData['y']),array('x' => $this->account->cityX, 'y' => $this->account->cityY ) );
			if( $dystanceToCity <= 3 AND ( ( $iC + $i ) < 10 ) AND ( $this->account->rawMaterials >= 1000 ) ) {
				$sponsorUnitsList['showData'] .= '<div id="plusSurka" uid="'. $uid .'">+</div>';
			}
			
			$siloOn = '';
			if( $dystanceToCity > 3 ){
				// sprawdzamy czy w pobliżu jest silos
				if( $this->account->nation == 1 ){
					$getUnitD = $this -> db -> query('SELECT `x`, `y`, `id` from `units` WHERE `tacticalDataID`= 224 AND `worldID` = '. $this->world->id .' ')->fetch();
				}else{
					$getUnitD = $this -> db -> query('SELECT `x`, `y`, `id` from `units` WHERE `tacticalDataID`= 225 AND `worldID` = '. $this->world->id .' ')->fetch();
				}
				
				if( $getUnitD['x'] ){
					$dystanceToCity = $this -> unit -> hex_distance(array('x' => $uidData['x'], 'y' => $uidData['y']),array('x' => $getUnitD['x'], 'y' => $getUnitD['y'] ) );
					$siloOn = 'var="'. $getUnitD['id'].'"';
				}
			}
			
			if( $dystanceToCity <= 3 AND $i > 0 ){
				$sponsorUnitsList['showData'] .= '<div id="minusSurka" uid="'. $uid .'" '. $siloOn .' >-</div>';
			}
		}
		$sponsorUnitsList['showData'] .= '</tr></table>';
		if ($iC == 0 AND $i == 0 ) {
            $sponsorUnitsList['showData'] = '<p>'. comp_index111 .' :)</p>';
        }
        echo json_encode($sponsorUnitsList);
	}
	
	public function buildSilo()
    {
        $nr_jednostki = $this->path->post('uid');
        $daneUnits = [];
        $wykonanie = 1;
		//pobieram stare koordy pojazdu inżyniera
        $dane = $this->db->query('SELECT `Specialty`,`unitTurn`,`x`,`y`,`unitType` FROM `units` WHERE `id`=' . $nr_jednostki . ' ')->fetch();
        if( $this->account->nation == 1 AND $dane['x'] > 75 ){
			$daneUnits['error'] ='Silos można budować tylko na wyspie!';
			echo json_encode($daneUnits);
			exit();
		}
		if( $this->account->nation == 2 AND $dane['x'] < 58 ){
			$daneUnits['error'] ='Silos można budować tylko na wyspie!';
			echo json_encode($daneUnits);
			exit();
		}
		
		if( $this->account->nation == 1 ){
			$getUnitD = $this -> db -> query('SELECT `x`, `y`, `id` from `units` WHERE `tacticalDataID`= 224 AND `worldID` = '. $this->world->id .' ')->fetch();
			$unitID = 224;
		}else{
			$getUnitD = $this -> db -> query('SELECT `x`, `y`, `id` from `units` WHERE `tacticalDataID`= 225 AND `worldID` = '. $this->world->id .' ')->fetch();
			$unitID = 225;
		}
		
		if( $getUnitD['x'] ){
			$daneUnits['error'] ='Twoi sojusznicy wybudowali już Silos. Poszukaj go na wyspie i zapełnij go surowcami';
			echo json_encode($daneUnits);
			exit();
		}
		
		$specjalnosc = $dane['Specialty'];
        $dostepneTury = $dane['unitTurn'];
        $xOne = $dane['x'];
        $yOne = $dane['y'];
		$up1 = 0;
		$up2 = 0;
		$licznik1 = 0;
		$licznik2 = 0;
		$daneUnits['info'] ='zbudowane';
		// zmieniamy dane jednoski na silos
		$this -> db -> exec('UPDATE `units` SET `unitType`= 8, `tacticalDataID` = '. $unitID .', `Specialty`=21  WHERE `id`=' . $nr_jednostki . ' ');
		// zapisujemy poziomy silosu
		$this->db->exec('INSERT INTO `siloData` (`unitID`) VALUES (\'' . $nr_jednostki . '\' )');


		$arrayData = array('activity' => 'buildSilo', 'chanData' => array(
			array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
				'data' => array(
					'unitData' => array(
						'x' => $xOne,
						'y' => $yOne,
						'id' => $nr_jednostki,
						'nation' => $this->account->nation,
						'unitType' => 8,
						'owner' => $this->account->playerID,
						'DefensePoints' => 100,
						'info' => 'silos wybudowany'
					)
				)
			)
		));
		$objHelper = init::getFactory()->getService('helper');
		$objHelper->sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
		$arrCheckMap = null;
		//echo json_encode(true);
        echo json_encode($daneUnits);
    }
	
	
	public function usePrize()
    {
        $uid = $this->path->post('uid');
		$step = $this->path->post('step');
		$step2 = $this->path->post('step2');
		$num = $this->path->post('num');
		$dane_json = [];
        $dane_json['title'] ='Twój zasobnik';
		$dane_json['height'] = 138;
		$rev = '<div id="trayBody">';
		// wyciagamy dane zasobnika
		
		switch( $step ){
			case 1:
			//przygotowanie okna z dostepnymi bonami
				$dane = $this->db->query('SELECT * FROM `prize` WHERE ( `prizeNumber`= 2 OR `prizeNumber`= 4 ) AND `prizeUsed`= 0 AND `playerID`= '.$this->account->playerID.'');
				if( $dane->rowCount() == 0 ){
					$dane_json['error'] = 'Nie masz bonów na skrócenie czasu produkcji jednostki';
				}else{
					$rev .= 'Poniższe bony możesz wykorzystać na przyspieszenie budowy jednostek bojowych.<br><table>';
					
					foreach ( $dane as $singlePrize ) {
						switch( $singlePrize['prizeTime'] ){
							case 600:
								$txt2 = '10 minut';
							break;
							case 3600:
								$txt2 = '60 minut';
							break;
							case 14400:
								$txt2 = '4 godziny';
							break;
							case 604800:
								$txt2 = '7 dni';
							break;
							case 2592000:
								$txt2 = '30 dni';
							break;
							
						}
						$rev .= '<tr><td>'. $txt2 .'</td><td><div class="usePrize" num="'. $singlePrize['id'] .'" uid="'. $uid .'" step="2" step2="1">użyj</td></tr>';
						$dane_json['height'] += 20;
					
					}
					
					$rev .= '</table>';
				}
			break;
			case 2:
				//realizacja bonu ( klikniecie w konkretny bon )
				$dane = $this->db->query('SELECT * FROM `prize` WHERE `id`= '. $num .' AND `prizeUsed`= 0  AND `playerID`= '.$this->account->playerID.' ');
				if( $dane->rowCount() == 0 ){
					$dane_json['error'] = 'Nie masz bonów na skrócenie czasu';
				}else{
					$singlePrize = $dane->fetch();
					switch( $singlePrize['prizeTime'] ){
						case 600:
							$txt2 = '10 minut';
						break;
						case 3600:
							$txt2 = '60 minut';
						break;
						case 14400:
							$txt2 = '4 godziny';
						break;
						case 604800:
							$txt2 = '7 dni';
						break;
						case 2592000:
							$txt2 = '30 dni';
						break;
					}
					switch( $step2 ){
						case 1: // update czasu buudowy jednostki
							$this -> db -> exec('UPDATE `UnitBuild` SET `TurnBuild`= ( `TurnBuild`- '.$singlePrize['prizeTime'].' ) WHERE `id`=' . $uid . ' LIMIT 1 ');	
							$rev .= 'Czas produkcji jednostki został skrócony o '. $txt2;
						break;
						case 2:// update czasu opracowywania technologii
							$this -> db -> exec('UPDATE `buildingsOnBuild` SET `timeToEnd`= ( `timeToEnd`- '.$singlePrize['prizeTime'].' ) WHERE `buildID`=' . $uid . ' AND `playerID`='. $this->account->playerID .' LIMIT 1 ');	
							$rev .= 'Czas opracowywania technologii został skrócony o '. $txt2;
						break;
					}
					$this -> db -> exec('UPDATE `prize` SET `prizeUsed`=1 WHERE `id`=' . $num . ' LIMIT 1 ');
					
				}
				$rev .= '</table>';
			break;
		}
		
		
		
		$dane_json['body'] = $rev;
        echo json_encode($dane_json);
    }
	
	
	
	
	
	
}
?>