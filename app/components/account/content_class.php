<?php

/*
 * KOMPONENT: account
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
        /* trzeba tutaj koniecznie zwalidować dane przesyłane przez formularz, ja nie mam dzisiaj już siły :) */
    }

    public function editOptions()
    {
        $arrFormData = $this -> path -> post('postData');
        $revOptions=array();
		switch ($arrFormData['formStage']) {
            case '1'://form zmiany hasła
                //sprawdzam, czy user wpisał prawidłowe stare hasło
                $objHelper = init::getFactory() -> getService('helper');
                $rowsUnit = $this -> db -> query('SELECT password FROM `accounts` WHERE `id`="' . $this -> account -> id . '" ') -> fetch();
                if (strlen($arrFormData['old_form_pass']) < 1) $this -> error -> printError( comp_account104, 'komunikat');
                $oldPass = $arrFormData['old_form_pass'] . 'HashSeedWithSomeSigns';
                if (sha1(md5($oldPass)) !== $rowsUnit['password']) $this -> error -> printError( comp_account104.',<br> '.comp_account105.'<a href="mailto:team@wichry-wojny.eu">team@wichry-wojny.eu</a>', 'komunikat');
                if ($arrFormData['form_pass'] !== $arrFormData['form_pass1']) $this -> error -> printError(haslo_wrong_txt, 'komunikat');
                if (strlen($arrFormData['form_pass']) < 5) $this -> error -> printError( comp_account106, 'komunikat');
                $password = $this -> session -> hashPassword($arrFormData['form_pass']); /// haszujemy hasło
                $this -> session -> pass = $password;
                $b = $this -> db -> prepare('UPDATE `accounts` SET password=:password WHERE id="' . $this -> account -> id . '"');
                $b -> bindParam(":password", $password);
                $b -> execute();
				$revOptions['goto'] = 1;
				$revOptions['option'] = 0;
                $revOptions['info'] = changePassEnd;
                $strEmail =
                    przypomnij14.' '.$this -> account -> login.'!<br>
					'.comp_account1.'.';
                //$this -> db -> exec('INSERT INTO `sendedLinks` (`id`, `accountID`, `type`, `date`) VALUES (\'' . $strActivateID . '\', ' . $arrData['id'] . ', \'CHANGE_PASS\', ' . $this -> init -> time . ')');
                // wysyłam email
                $objHelper -> sendEmail($strEmail, comp_account2, $this -> account -> email );
                echo json_encode($revOptions);
                break;
            case '2'://form ustawiania raportów bojowych
                $b = $this -> db -> prepare('UPDATE `accounts` SET reports=:reports WHERE id="' . $this -> account -> id . '"');
                $b -> bindParam(":reports", $arrFormData['reports']);
                $b -> execute();
                $revOptions['goto'] = 1;
				$revOptions['option'] = $arrFormData['reports'];
                echo json_encode($revOptions);
                break;
            case '3'://form ustawiania tur Sponsora
                if ($this -> world -> WarMode == 3)
                {
                    $err = 0;
					$one = $two = $three = $four = $five = $six = $seven = $eight = '';
					if (!isset($arrFormData['schem_1']))
					{
						$one = comp_account3.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_2']))
					{
						$two = comp_account4.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_3']))
					{
						$three = comp_account5.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_4']))
					{
						$four = comp_account6.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_5']))
					{
						$five = comp_account7.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_6']))
					{
						$six = comp_account8.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_7']))
					{
						$seven = comp_account9.'<br>';
						$err = 1;
					}
					if (!isset($arrFormData['schem_8']))
					{
						$eight = comp_account10.'<br>';
						$err = 1;
					}
					if ($err == 1)
					{
						$this -> error -> printError($one . '' . $two . '' . $three . '' . $four.'' . $five.'' . $six.'' . $seven.'' . $eight, 'komunikat');
					}
					$this -> db -> exec('UPDATE `turnSetting` SET `Turn1`=' . $arrFormData['schem_1'] . ',`Turn2`=' . $arrFormData['schem_2'] . ',`Turn3`=' . $arrFormData['schem_3'] . ',`Turn4`=' . $arrFormData['schem_4'] . ',`Turn5`=' . $arrFormData['schem_5'] . ',`Turn6`=' . $arrFormData['schem_6'] . ',`Turn7`=' . $arrFormData['schem_7'] . ',`Turn8`=' . $arrFormData['schem_8'] . ',TurnUp=1 WHERE `playerID`=' . $this -> account -> playerID . ' LIMIT 1');
					$revOptions['height'] = 170;
				}else{
					$err = 0;
					$one = $two = $three = $four = '';
					if (empty($arrFormData['schem_1']))
					{
						$one = comp_account3.'<br>';
						$err = 1;
					}
					if (empty($arrFormData['schem_2']))
					{
						$two = comp_account4.'<br>';
						$err = 1;
					}
					if (empty($arrFormData['schem_3']))
					{
						$three = comp_account5.'<br>';
						$err = 1;
					}
					if (empty($arrFormData['schem_4']))
					{
						$four = comp_account6.'<br>';
						$err = 1;
					}
					if ($err == 1)
					{
						$this -> error -> printError($one . '' . $two . '' . $three . '' . $four, 'komunikat');
					}
					$this -> db -> exec('UPDATE `turnSetting` SET `Turn1`=' . $arrFormData['schem_1'] . ',`Turn2`=' . $arrFormData['schem_2'] . ',`Turn3`=' . $arrFormData['schem_3'] . ',`Turn4`=' . $arrFormData['schem_4'] . ',TurnUp=1 WHERE `playerID`=' . $this -> account -> playerID . ' LIMIT 1');

				}
                $revOptions['goto'] = 1;
				$revOptions['option'] = 0;
                echo json_encode($revOptions);
                break;
            case '4'://form ustawiania tur dla NIE-Sponsora
                $err = 0;
                if (empty($arrFormData['schemat']))
                {
                    $this -> error -> printError( comp_account12, 'komunikat');
                }
                if ($this -> world -> WarMode == 1 OR $this -> world -> WarMode == 2 OR $this -> world -> WarMode == 4)
                {
                    switch ($arrFormData['schemat']) {
                        case 1:
                            $tura1 = 2;
                            $tura2 = 8;
                            $tura3 = 14;
                            $tura4 = 20;
                            break;
                        case 2:
                            $tura1 = 3;
                            $tura2 = 9;
                            $tura3 = 15;
                            $tura4 = 21;
                            break;
                        case 3:
                            $tura1 = 4;
                            $tura2 = 10;
                            $tura3 = 16;
                            $tura4 = 22;
                            break;
                        case 4:
                            $tura1 = 5;
                            $tura2 = 11;
                            $tura3 = 17;
                            $tura4 = 23;
                            break;
                        case 5:
                            $tura1 = 6;
                            $tura2 = 12;
                            $tura3 = 18;
                            $tura4 = 0;
                            break;
                        default:
                    }

                    //$this -> error -> printError($this -> world-> worldNum, 'komunikat');
                    $this -> db -> exec('UPDATE `turnSetting` SET `Turn1`=' . $tura1 . ',`Turn2`=' . $tura2 . ',`Turn3`=' . $tura3 . ',`Turn4`=' . $tura4 . ',TurnUp=1 WHERE `playerID`=' . $this -> account -> playerID . ' LIMIT 1');

                    /*
                      $b = $this -> db -> prepare('UPDATE `'.$this -> init -> settings['db']['prefix'].'turnSetting` SET Turn1=:one,Turn2=:two,Turn3=:three,Turn4=:four,TurnUp=:up WHERE `id`='.$this -> account -> id.' AND `worldID`='.$this -> world-> worldNum.'    ');
                      $b -> bindParam(":one", $arrFormData['schem_1'] );
                      $b -> bindParam(":two", $arrFormData['schem_2'] );
                      $b -> bindParam(":three", $arrFormData['schem_3'] );
                      $b -> bindParam(":four", $arrFormData['schem_4'] );
                      $b -> bindParam(":up", 1 );
                      $b -> execute();
                     */
                }
                else if ($this -> world -> WarMode == 3)
                {
                    $this -> error -> printError( comp_account13 , 'komunikat');
                }
                $revOptions['goto'] = 1;
				$revOptions['option'] = 0;
                echo json_encode($revOptions);
                break;
            case '5'://form uruchamiania Urlopu
                $arrFormData['soflow-color1'];
                $ile_dni = '+ ' . $arrFormData['soflow-color1'] . ' day';
                if ($this -> account -> playerVacation > $this -> init -> time)
                {//jeśli gracz ma jeszcze aktywny urlop
                    //dodaję do czasu urlopu gracza czas z select soflow-color1 
                    $timeToBase = strtotime($ile_dni, $this -> account -> playerVacation);
                }
                else
                { //gracz ma urlop nieaktywny, uruchamiamy urlop
                    $teraz = time();
                    //dodaję do czasu time czas z select soflow-color1 
                    $timeToBase = strtotime($ile_dni, $teraz);
                }
                $this -> db -> exec('UPDATE `players` SET `playerVacation`=' . $timeToBase . ',`VacationNum`=(`VacationNum`+1) WHERE `id`=' . $this -> account -> playerID . ' LIMIT 1  ');
                $this -> db -> exec('UPDATE `UnitBuild` SET `playerVacation`=' . $timeToBase . ' WHERE `playerID`=' . $this -> account -> playerID . '  ');
                $this -> db -> exec('UPDATE `units` SET `x`=0,`y`=0, `onMap`= 0 WHERE `playerID`=' . $this -> account -> playerID . ' AND `unitType`!=7 ');

                $revOptions['goto'] = 1;
				$revOptions['option'] = date("Y-m-d H:i:s", $timeToBase);
                echo json_encode($revOptions);
                break;
			case '6'://form zapisu nazwy miasta
                $objHelper = init::getFactory() -> getService('helper');
				if( $arrFormData['form_miasto'] === 'Warszawa' OR $arrFormData['form_miasto'] === 'Berlin'  )
                {
                    $this -> error -> printError( comp_account14 , 'mapa');

                }
                else
                {
                    $this -> db -> exec('UPDATE `units` SET `CityName`="' . $objHelper -> safeChatText($arrFormData['form_miasto'])  . '" WHERE `playerID`=' . $this -> account -> playerID . ' AND `unitType`=7 LIMIT 1  ');
                    $revOptions['goto'] = 1;
                }

				$revOptions['option'] = 0;
                echo json_encode($revOptions);
                break;	
        }
    }

    public function editSounds()
    {
        $alert = [];
		if ($this -> account -> sounds == 1)
        {//jesli dzwięki są właczone, należy je wyłączyć
            $this -> db -> exec('UPDATE `accounts` SET `sounds`=0 WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
			$alert['txt'] = ustawienia14a;
			$alert['muza_txt'] = ustawienia15;
        }
        else
        {
            $this -> db -> exec('UPDATE `accounts` SET `sounds`=1 WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
			$alert['txt'] = ustawienia15a;
			$alert['muza_txt'] = ustawienia15b;
        }
        echo json_encode($alert);
    }

    public function editOpticalAlert()
    {
        $alert = [];
		if ($this -> account -> opticalAlert == 1)
        {//jesli żarówa jest właczona, należy ją wyłączyć
            $this -> db -> exec('UPDATE `accounts` SET `opticalAlert`=0 WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
            $alert['txt'] = ustawienia14a;
			$alert['zarowa_txt'] = ustawienia15;
        }
        else
        {
            $this -> db -> exec('UPDATE `accounts` SET `opticalAlert`=1 WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
            $alert['txt'] = ustawienia15a;
			$alert['zarowa_txt'] = ustawienia15b;
        }
        echo json_encode($alert);
    }

    public function checkGold()
    {
        $arrFormData = $this -> path -> post('postData');
        if ( ! isset($arrFormData['kod_premium'])) $this -> error -> printError( comp_account15, 'mapa');
        if ( ! preg_match('/^([a-zA-Z0-9\.])+$/iu', $arrFormData['kod_premium'])) $this -> error -> printError( comp_account15, 'mapa');
        $rowsCode = $this -> db -> query('SELECT `dateUsed`,`goldValue` FROM `gold` WHERE `code`="' . $arrFormData['kod_premium'] . '" LIMIT 1') -> fetch();
        if ( ! isset($rowsCode['dateUsed'])) $this -> error -> printError(comp_account16.' ' . $arrFormData['kod_premium'] . ' '.comp_account17, 'komunikat');
        if ($rowsCode['dateUsed'] > 0)
        {
            $this -> error -> printError(comp_account18.' ' . $arrFormData['kod_premium'] . ' '.comp_account19, 'mapa');
        }
        $this -> db -> exec('UPDATE `gold` SET `dateUsed`=' . $this -> init -> time . ' ,`id`=' . $this -> account -> id . ' WHERE `code`=' . $arrFormData['kod_premium'] . ' LIMIT 1 ');
        $this -> db -> exec('UPDATE `accounts` SET `gold`=(`gold`+' . $rowsCode['goldValue'] . ') WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
        echo json_encode(array('komunikat' => comp_account20.' ' . $rowsCode['goldValue'] . ' '.sztuk_zlota_txt, 'gold' => $rowsCode['goldValue']));
    }

    public function prepareFormGold()
    {
        $arrFormData = $this -> path -> post('postData');
        //if ( ! preg_match("/^[\d]{1,11}$/i", $arrFormData)) $this -> error -> printError('coś nie tak', 'komunikat');
        $sell = $arrFormData['amount'];
        // Dane punktu otrzymane po zakonczeniu procesu rejestracji
        $service = "wichry-wojny.eu(2)";        // Identyfikator punktu
        $key = "6004dacedd1109fa46fd1c9464ccc060";        // Klucz szyfrujacy
        //
		// tryb testowy ?
        //
		$modeTest = "o";
        //
        // Podpisanie wysylanych danych
        //
		$t_gold["user_id"] = $this -> account -> id;
        $dane_gold = base64_encode(serialize($t_gold));
        $lang = $this -> account -> lang;
        $sendaddr = ( $modeTest == "t" ?
                ' https://pay.cashbill.pl/form/pay.php' :
                ' https://pay.cashbill.pl/form/pay.php' );
        switch ($sell) {
            case '9':
                $value = 9;
                $ile_gold = 200;
                $desc = platnoscidesc200;
                break;
            case '20':
                $value = 20;
                $ile_gold = 500;
                $desc = platnoscidesc500;
                break;
            case '39':
                $value = 39;
                $ile_gold = 1000;
                $desc = platnoscidesc1000;
                break;
            case '75':
                $value = 75;
                $ile_gold = 2000;
                $desc = platnoscidesc2000;
                break;
            default;
        }
        $sign = md5($service . $sell . $desc . $lang . $dane_gold . $key);
        $amount = platnosci_kupuje . '' . $ile_gold . '' . platnosci_kupuje_za . '' . $sell . '' . platnosci_brutto . '<br><input type="hidden" name="amount" value="' . $sell . '"/><input type="hidden" name="desc" value="' . $desc . '"/>';
        $formularz = array(
          'dane_gold' => $dane_gold,
          'target' => $sendaddr,
          'submit' => platnosci_submit,
          'amount' => $amount,
          'lang' => $this -> account -> lang,
          'service' => $service,
          'sign' => $sign
        );
        echo json_encode($formularz);
    }

    //funkcja obsługujaca uruchamianie konta sponsor
    public function sponsor()
    {
        $stage = $this -> path -> post('stage');
        $arrData = $this -> path -> post('arrData');
        if ( ! preg_match("/^[\d]{1,11}$/i", $stage)) $sponsor['error'] = 'Błąd';
        if ( ! preg_match("/^[\d]{1,11}$/i", $arrData)) $sponsor['error'] = 'Błąd';
        $sponsor = array();
        if ($stage == 1)
        {//przygotowanie info odnośnie Sponsora
            $sponsor['titleSponsor'] = '<h2>'.comp_account22.'</h2>';
            $sponsor['sponsor_txt'] = comp_account23.'<br><br></div><br><br>';

            if ($this -> account -> gold >= 200)
            {
                $sponsor['sponsorForm'] = '<div class="formSponsor"><form><table border="0" style="margin:0 auto;">';
                if ($this -> account -> SponsorAccount > $this -> init -> time)
                {
                    if ($this -> account -> testedSponsorAccount == 0)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="7" name="sponsor"></td><td>'.comp_account24.' '.comp_account26.' '.comp_account27.'</td></tr>';
                    }
                    if ($this -> account -> gold >= 200)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="1" name="sponsor" checked="checked" ></td><td>'.comp_account24.' '.comp_account26.' '.comp_account28.'</td></tr>';
                    }
                    if ($this -> account -> gold >= 1080)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="6" name="sponsor"></td><td>'.comp_account24.' '.comp_account26.' '.comp_account29.'</td></tr>';
                    }
                    if ($this -> account -> gold >= 2000)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="12" name="sponsor"></td><td>'.comp_account24.' '.comp_account26.' '.comp_account30.'</td></tr>';
                    }
                    $sponsor['sponsorForm'].='
						<tr><td colspan="2"><input type="button" id="sponsor_continue" value="' . przedluzSponsorTxt . '"/></td></tr>
						</table>
					</form>';
                }
                else
                {
                    if ($this -> account -> testedSponsorAccount == 0)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="7" name="sponsor"></td><td>'.comp_account25.' '.comp_account26.' '.comp_account27.'</td></tr>';
                    }
                    if ($this -> account -> gold >= 200)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="1" name="sponsor" checked="checked"></td><td>'.comp_account25.' '.comp_account26.' '.comp_account28.'</td></tr>';
                    }
                    if ($this -> account -> gold >= 1080)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="6" name="sponsor"></td><td>'.comp_account25.' '.comp_account26.' '.comp_account29.'</td></tr>';
                    }
                    if ($this -> account -> gold >= 2000)
                    {
                        $sponsor['sponsorForm'].='<tr><td><input type="radio" value="12" name="sponsor"></td><td>'.comp_account25.' '.comp_account26.' '.comp_account30.'</td></tr>';
                    }
                    $sponsor['sponsorForm'].='
						<tr><td colspan="2"><input type="button" id="sponsor_continue" value="' . uruchomSponsorTxt . '"/></td></tr>
						</table>
					</form>';
                }
            }
            else
            {
                if ($this -> account -> testedSponsorAccount == 0)
                {
                    $sponsor['sponsorForm'] = '<form>
					<input type="radio" value="7" name="sponsor" checked="checked" >'.comp_account25.' '.comp_account26.' '.comp_account27.'<br>
					<input type="button" id="sponsor_continue" value="' . uruchomSponsorTxt . '"/>
					</form>
					';
                }
                else
                {
                    $sponsor['sponsorForm'] = comp_account31.'<br>
					<div id="bank">'.panel_gry9.'</div>';
                }
            }
            $sponsor['sponsorForm'] .='</div>';
        }
        else if ($stage == 2)
        {//obsługa klikniętego formularza z zakupem sponsora
            switch ($arrData) {
                case '1':
                    $gold_wymagany = 200;
                    $ile = '+1 month';
                    break;
                case '6':
                    $gold_wymagany = 1080;
                    $ile = '+6 month';
                    break;
                case '7':
                    $gold_wymagany = 0;
                    $ile = '+7 day';
                    break;
                case '12':
                    $gold_wymagany = 2000;
                    $ile = '+1 year';
                    break;
            }
            if ($this -> account -> gold < $gold_wymagany)
            {
                $sponsor['error'] = comp_account31;
                echo json_encode($sponsor);
                exit();
            }
            else
            {//gracz posiada odpowiednią ilość złota, więc wykonujemy dodanie konta sponsor
                if ( $this -> account -> SponsorAccount > $this -> init -> time )
                {//gracz ma aktywne konto sponsor, trzeba dodać do konta sponsor odpowiednią ilość dni
                    $sponsor['nowySponsorTS'] = strtotime($ile, $this -> account -> SponsorAccount);
                    $sponsor['sponsorTxt'] = comp_account32.' ' . date("Y-m-d H:i:s", $sponsor['nowySponsorTS']) . ', '.comp_account33;
                }
                else
                {//gracz ma stare konto sponsor, więc dodajemy czas sponsor do czasu time
                    $sponsor['nowySponsorTS'] = strtotime($ile, $this -> init -> time);
                    $sponsor['sponsorTxt'] = comp_account34.' ' . date("Y-m-d H:i:s", $sponsor['nowySponsorTS']) . ', '.comp_account33;
                }
                $sponsor['gold'] = $gold_wymagany;
                $this -> db -> exec('UPDATE `accounts` SET `SponsorAccount`=' . $sponsor['nowySponsorTS'] . ',`gold`=( `gold`-' . $gold_wymagany . ' ) WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
                if ($gold_wymagany == 0)
                {
                    $this -> db -> exec('UPDATE `accounts` SET `testedSponsorAccount`=1 WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');
                    $this -> db -> exec('INSERT INTO `financialOperations` ( accountID, goldValue, operation, changeDate ) values (' . $this -> account -> id . ',' . $gold_wymagany . ',"testowe konto Sponsor",' . $this -> init -> time . ' ) ');
                }
                else
                {
                    $this -> db -> exec('INSERT INTO `financialOperations` ( accountID, goldValue, operation, changeDate ) values (' . $this -> account -> id . ',' . $gold_wymagany . ',"konto Sponsor",' . $this -> init -> time . ' ) ');
                }
                $sponsor['info'] = date("Y-m-d H:i:s", $sponsor['nowySponsorTS']);
                $sponsor['info1'] = comp_account35.' '.date("Y-m-d H:i:s", $sponsor['nowySponsorTS']);
            }
        }
        echo json_encode($sponsor);
    }

    public function unitBuy()
    {
        $unitBay = array();
        $num = $this -> path -> post('num');
        $code = $this -> path -> post('code');
        $haslo =  SYSTEM_PASSWORD;
        if ($code <> md5($haslo . $num)){
            $unitBay['error'] = 'Błędne dane wejściowe';
            echo json_encode($unitBay);
            exit();
        }else{
            $t = unserialize(base64_decode($num));
            // dalsza obróbka na poprawnych danych, np. wyświetlenie
            $nr_kupionej          = $t["idj"];
            $nrShopUnits          = $t["idUP"];
            $id_gracza_kupujacego = $t["user_id"];
            $cena_jednostki       = $t["cena"];
            $zakup_za_gold        = $t["gold"];
            $adres                = $t["adres"]; //1: sklep, 2:fabryka
            $ile_mozesz = $this -> account -> rankLevel;
            //sprawdzam, czy ta jednostka jest jeszcze dostępna
			$unitBay['getOffUnit'] = $nrShopUnits;
			$ileWBazie = $this -> db -> query('SELECT count(*) from `units` WHERE `id`=' . $nrShopUnits . '') -> fetch();
			if( (int)$ileWBazie[0] == 0 ){
				$unitBay['error'] = comp_account36;
				echo json_encode($unitBay);
				exit();
			}
			//sprawdzam, ile gracz kupił już '.comp_account102.'
            $rowsUnitno = $this -> db -> query('SELECT count(*) from `GoldUnits` WHERE `playerID`=' . $this -> account -> playerID . '') -> fetch();
            if ($this -> account -> SponsorAccount > $this -> init -> time){
                $ile_mozesz = $this -> account -> rankLevel * 2;
            }
			
            if ($id_gracza_kupujacego == $this -> account -> playerID AND ( (int)$rowsUnitno[0] < $ile_mozesz OR $zakup_za_gold == 1 ) ){
				if( $zakup_za_gold == 0 ){
					
					if ($cena_jednostki > ( $this -> account -> PremiumCurrency + $this -> account -> softCurrency ) ){
						//gracz nie ma kasy na budowę
						$unitBay['error'] = comp_account37;
						echo json_encode($unitBay);
						exit();
					}else{
						
						$unitBay['gold'] = 0;
						$unitBay['softCurrency'] = 0;
						$unitBay['PremiumCurrency'] = 0;
						$unitBay['goldBuy'] = 0;
						if ($this -> account -> softCurrency >= $cena_jednostki){
							$this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-' . $cena_jednostki . ') WHERE `id`=' . $this -> account -> playerID . ' ');
							$unitBay['softCurrency'] = $cena_jednostki;
							
						}else{
							if ($this -> account -> softCurrency < $cena_jednostki AND ($this -> account -> PremiumCurrency >= ($cena_jednostki-$this -> account -> softCurrency) ) ){
								//gracz nie ma wystarczającej ilości kasy soft, wiec zerujemy kasę soft i odejmujemy od kasy Premium
								$this -> db -> exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this -> account -> playerID . ' ');
								//i pomniejszamy ilość premiumCurrency
								$new_PremiumCurrency = $cena_jednostki - $this -> account -> softCurrency;
								$unitBay['PremiumCurrency'] = $new_PremiumCurrency;
								$unitBay['softCurrency'] = $cena_jednostki;
								$this -> db -> exec('UPDATE `accounts` SET `PremiumCurrency`=(`PremiumCurrency`-' . $new_PremiumCurrency . ' ) WHERE `id`=' . $this -> account -> id . ' ');
							}else{
								$unitBay['error'] = comp_account37;
								echo json_encode($unitBay);
								exit();
							}
						}
						$unitBay['info'] = comp_account38;
						$unitBay['creditBuy'] = 1;
					}
                }else{
					if($this -> account -> gold < $cena_jednostki){
						$unitBay['error'] = comp_account39;
						echo json_encode($unitBay);
						exit();
					}else{
						// zakupy płacone złotem
						$this -> db -> exec('UPDATE `accounts` SET `gold`=(`gold`-' . $cena_jednostki . ')  WHERE `id`=' . $this -> account -> id . ' ');
						$this -> db -> exec('INSERT INTO `financialOperations` ( accountID, goldValue, operation, changeDate ) values (' . $this -> account -> id . ',' . $cena_jednostki . ',"zakup jednostki za złoto",' . $this -> init -> time . ' ) ');
						$unitBay['info'] = comp_account40;
						$unitBay['goldBuy'] = 1;
						$unitBay['creditBuy'] = 0;
						$unitBay['gold'] = $cena_jednostki;
					}
				}
				
				$this -> account -> buildUnit($nr_kupionej,$this -> account -> playerID, 1);
				$this -> db -> exec('DELETE FROM `units` WHERE `id`=' . $nrShopUnits . ' LIMIT 1 ');
            }else{
				if( (int)$rowsUnitno[0] >= $ile_mozesz ){
					$unitBay['error'] = comp_account41;
					echo json_encode($unitBay);
					exit();
				}
			}
		}
        echo json_encode($unitBay);
    }

    public function fasterUnit()
    {
        $unitFaster = [];
        $num = $this -> path -> get('idUnit');
        $getDataBuild = $this -> db -> query('SELECT * from `UnitBuild` WHERE `corpsID`=' . $num . ' AND `playerID`=' . $this -> account -> playerID . ' ') -> fetch();
        $rowsUnitno = $this -> db -> query('SELECT count(*) from `fasterUnits` WHERE `corpsID`=' . $num . ' AND `playerID`=' . $this -> account -> playerID . '') -> fetch();
        
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
		//$costGoldPrepare = $this->init->time - $this->world->timeToUp;
		$toPlayerInfo = ' ( sekund: '.( round( ( $getDataBuild['TurnBuild'] - $this->init->time) ) );
		$quarter =  round( ( ( $getDataBuild['TurnBuild'] - $this->init->time ) / 900 )/30 );
		
		
	//}
	
		if ( (int)$rowsUnitno[0] > 0) {
			$cena_gold = ( $quarter * $getDataBuild['corpsID'] ) * ( (int)$rowsUnitno[0] + 1);
		} else {
			$cena_gold = $quarter * $getDataBuild['corpsID'];
		}
		
		
		
        if ( $cena_gold >= $this -> account -> gold )
        {
            $unitFaster['error'] = comp_account42;
            echo json_encode($unitFaster);
            exit();
        }
        $this -> db -> exec('UPDATE `accounts` SET `gold`=(`gold`-' . $cena_gold . ' ) WHERE `id`=' . $this -> account -> id . ' ');
        //budujemy jednostkę
		$this -> account -> buildUnit( $getDataBuild['tacticalDataID'], $this -> account -> playerID, 0 );
		$this -> db -> exec('DELETE FROM `UnitBuild` WHERE `corpsID`=' . $num . ' AND `playerID`=' . $this -> account -> playerID . ' LIMIT 1');
        $this -> db -> exec('INSERT INTO `fasterUnits` ( `corpsID`, `playerID`) values (' . $num . ',' . $this -> account -> playerID . ' ) ');
        $unitFaster['info'] = comp_account43;
        $unitFaster['gold'] = $cena_gold;
        if( $this -> account -> tutorialStage == 4 )
        {
            $this -> db -> exec('UPDATE `accounts` SET `tutorialStage`=5 WHERE `id`=' . $this -> account -> id . ' LIMIT 1');

        }
        echo json_encode($unitFaster);
    }

    public function showPlayerProfil()
    {
        $objHelper = init::getFactory() -> getService('helper');
		$playerData = array();
        $playerID = $this -> path -> get('playerID');
        $edit_profil = 0;

        if ($playerID == $this -> account -> id)
        {
            $edit_profil = 1;
        }
        $stats = $this -> db -> query('SELECT * FROM `accounts` WHERE `id`=' . $playerID . ' LIMIT 1') -> fetch();
        if ( ! isset($stats['id'])) exit(comp_account44.'.');
        
        $arr = '';

        $logins = $this -> db -> query('SELECT * from `accounts` ORDER BY `login` ASC');
        foreach ($logins as $dane)
        {
            $arr.= '<option value="' . $dane['login'] . '">';
        }

        $this -> tmplData['variables']['userList'] = $arr;
        $playerData['title'] = comp_account45;
        $playerData['playerBody'] = '<div id="profil_gracza">
							<label for="tags">'.comp_account46.': </label>
							<input list="playerListId"/>
							<datalist id="playerListId">
								' . $arr . '
							</datalist>
							<input type="button" value="'.comp_account47.'" id="submit_user"/>

						<div id="avek_ramka">	
							<div id="avatar_gracza">';
        if ($stats['SponsorAccount'] >= $this -> init -> time)
        {
            if ($stats['avatar'] != "")
            {
                $playerData['playerBody'].='<img src="' . $stats['avatar'] . '"/><br>';
                if ($edit_profil == 1)
                {
                    $playerData['playerBody'].='<div id="usun_fota">' . mess_usun . '</div>';
                }
            }
            else
            {
                if ($edit_profil == 1)
                {
                    $playerData['playerBody'].='<strong id="info_upload"><br>' . ustawienia9 . '</strong>
												<form id="uploadimage" name="uploadimage" method="post" enctype="multipart/form-data">
                                                  <input id="uploadImage" type="file" accept="image/jpeg" name="image" required/>
                                                  <input type="submit"  onclick="uploadImage(); return false;" class="submit_fota" value=" '. zapisz.'"/>
                                                </form>';
                }
                else
                {
                    $playerData['playerBody'].='<img src="app/templates/assets/images/szwejk.gif"/>';
                }
            }
        }
        else
        {
            $playerData['playerBody'].='<img src="app/templates/assets/images/szwejk.gif"/><br>';
            if ($edit_profil == 1)
            {
                $playerData['playerBody'].=ustawienia10;
            }
        }

       $playerData['playerBody'].='
                           </div>
                       </div>
                           <div id="kampanie">
                               <strong id="kampanie_przycisk">'.profil1.'</strong>
                           </div>
                           <div id="nick_points">
                               <div id="oficerProfil">
                               </div>
                               <div id="nick_txt">
                                   '.profil2.':
                               </div>
                               <div id="pucharProfil">
                               </div>
                               <div id="punkty_txt">
                                   '.profil3.':
                               </div>
                               <div id="nick_profil">
                                   ' . $stats['login'] . '
                               </div>
                               <div id="punkty_profil">
                                   ' . $stats['globalPoints'] . '
                               </div>
                           </div>
                           <div id="megafonProfil">
                           </div>
                           <div id="motto_txt">
                               '.profil4.':
                           </div>
                           <div id="motto_profil">';
       if ($stats['SponsorAccount'] >= $this -> init -> time)
       {
           if ( ! $stats['description'])
           {
               $playerData['playerBody'].=profil5;
           }
           else
           {
               $playerData['playerBody'].='<div id="motto_text">' . $stats['description'] . '</div>';
           }

       }
           if ($edit_profil == 1 AND $stats['SponsorAccount'] >= $this -> init -> time )
           {
               $playerData['playerBody'].='</div><div id="edit_motto">'.profil6.'</div>';
           }
           else if($edit_profil == 1 AND $stats['SponsorAccount'] < $this -> init -> time)
           {
               $playerData['playerBody'].= profil7.'</div>';
           }
           else
           {
               $playerData['playerBody'].= profil5.'</div>';

           }
                           $playerData['playerBody'].='<div id="ocenaProfil">
                           </div>
                           <div id="ocena_txt">
                               '.profil8.':
                           </div>
                           <div id="ocena_profil">
                               '.$objHelper -> playerRating($stats['globalPoints'],$stats['game']).'
                           </div>
                           <div id="odznaczeniaProfil">
                           </div>
                           <div id="odznaczenia_txt">
                               '.profil9.':
                           </div>
                           <div id="odznaczenia_profil">';				   
		   $statsP = $this -> db -> query('SELECT * FROM `statsPlayer` WHERE `accountID`=' . $playerID . ' AND `nation`=1 ') -> fetch();
			$piech = $statsP['d_piechota'];
			$panc = $statsP['d_pancerne'];
			$art = $statsP['d_artyleria'];
			$plot = $statsP['d_przeciwlotnicze'];
			$blot = $statsP['d_lotnictwo_b'];
			$lot = $statsP['d_lotnictwo_m'];
			$pod = $statsP['d_flota_p'];
			$mw = $statsP['d_flota_n'];

		   $statsN = $this -> db -> query('SELECT * FROM `statsPlayer` WHERE `accountID`=' . $playerID . ' AND `nation`=2 ') -> fetch();
			$wer = $statsN['d_piechota'];
			$panz = $statsN['d_pancerne'];
			$art_c = $statsN['d_artyleria'];
			$flak = $statsN['d_przeciwlotnicze'];
			$luftb = $statsN['d_lotnictwo_b'];
			$luft = $statsN['d_lotnictwo_m'];
			$uboot = $statsN['d_flota_p'];
			$kriegs = $statsN['d_flota_n'];


		   $playerData['playerBody'].='<table>
                           <tr>
                               <td>' . $this -> sprawdz_order(1, $wer,$edit_profil) . '</td><td>' . $this -> sprawdz_order(13, $panz,$edit_profil) . '</td><td>' . $this -> sprawdz_order(2, $luftb,$edit_profil) . '</td><td>' . $this -> sprawdz_order(3, $luft,$edit_profil) . '</td><td>' . $this -> sprawdz_order(4, $kriegs,$edit_profil) . '</td><td>' . $this -> sprawdz_order(5, $uboot,$edit_profil) . '</td><td>' . $this -> sprawdz_order(6, $flak,$edit_profil) . '</td>
                           </tr>
                           <tr>
                               <td>' . $this -> sprawdz_order(7, $piech,$edit_profil) . '</td><td>' . $this -> sprawdz_order(14, $panc,$edit_profil) . '</td><td>' . $this -> sprawdz_order(8, $blot,$edit_profil) . '</td><td>' . $this -> sprawdz_order(9, $lot,$edit_profil) . '</td><td>' . $this -> sprawdz_order(10, $mw,$edit_profil) . '</td><td>' . $this -> sprawdz_order(11, $pod,$edit_profil) . '</td><td>' . $this -> sprawdz_order(12, $plot,$edit_profil) . '</td>
                           </tr>
                       </table>
                           </div>
                       </div>';
		
		   $playerData['playerBody'].='<div id="profil_kampanie">
		   <div id="profil_kampanie_top">
			   <div id="kampanie_przycisk1"><- '.mess_powrot.'</div>
			   <div id="kampanie_txt">
				   '.profil1.' ' . $stats['login'] . '
			   </div>
		   </div>
		   <div id="profil_kampanie_body">';
			$statsWorld = $this -> db -> query('SELECT * FROM `statsPlayer` WHERE `accountID`=' . $playerID . ' ');
			$count=0;
			foreach ($statsWorld as $singleWorld)
			{
				$statsSingleWorld = $this -> db -> query('SELECT * from `worlds` WHERE `id`='.$singleWorld['worldID'].' ')->fetch();
				$statsSinglePlayer = $this -> db -> query('SELECT * from `players` WHERE `id`='.$singleWorld['playerID'].' ')->fetch();
				$punkty_gracza = floor($statsSinglePlayer['points']);
				$sztuki_piechota = $singleWorld['b_piechota'];
				$sztuki_lotnictwo_b = $singleWorld['b_lotnictwo_b'];
				$sztuki_lotnictwo_m = $singleWorld['b_lotnictwo_m'];
				$sztuki_pancerne = $singleWorld['b_pancerne'];
				$sztuki_przeciwlotnicze = $singleWorld['b_przeciwlotnicze'];
				$sztuki_flota_n = $singleWorld['b_flota_p'];
				$sztuki_flota_p = $singleWorld['b_flota_n'];
				$sztuki_artyleria = $singleWorld['b_artyleria'];
				$dsztuki_piechota = $singleWorld['d_piechota'];
				$dsztuki_lotnictwo_b = $singleWorld['d_lotnictwo_b'];
				$dsztuki_lotnictwo_m = $singleWorld['d_lotnictwo_m'];
				$dsztuki_pancerne = $singleWorld['d_pancerne'];
				$dsztuki_przeciwlotnicze = $singleWorld['d_przeciwlotnicze'];
				$dsztuki_flota_n = $singleWorld['d_flota_p'];
				$dsztuki_flota_p = $singleWorld['d_flota_n'];
				$dsztuki_artyleria = $singleWorld['d_artyleria'];
				$nacja_gracza_id = $statsSinglePlayer['nation'];
				$start_wojny = $statsSingleWorld['outbreakWar'];
				$koniec_wojny = $statsSingleWorld['endWar'];
				$trudnosc_swiata = $statsSingleWorld['WarDifficulty'];
				$wygrany = $statsSingleWorld['winNation'];
				$ilosc_tur = $statsSingleWorld['WarTurn'];
				$nazwa = $statsSingleWorld['name'];
				$rok = 1939 + floor(($ilosc_tur + 32) / 48);
				$numer_mc = floor(($ilosc_tur % 48) / 4);
				$miesiac_przesuniety = ($numer_mc + 8) % 12;
				$sztuki_flota = $sztuki_flota_n + $sztuki_flota_p;
				$dsztuki_flota = $dsztuki_flota_n + $dsztuki_flota_p;
				$sztuki_lotnictwo = $sztuki_lotnictwo_b + $sztuki_lotnictwo_m;
				$dsztuki_lotnictwo = $dsztuki_lotnictwo_b + $dsztuki_lotnictwo_m;
				if ($nacja_gracza_id == 1)
				{
					$nacja_txt = profil49;
				}
				else
				{
					$nacja_txt = profil50;
				}
				if($koniec_wojny == 0 ){
					$koniec_wojny = profil54;
					$wygrali ='';
				}else{
					$koniec_wojny = profil55.' '. date('Y-m-d',$koniec_wojny). '</strong>';
					if($wygrany == 1){
						$wygrali = profil57 .' <strong>'.dane_taktyczne8.'</strong>!';
					}else{
						$wygrali = profil57 .' <strong>'.dane_taktyczne7.'</strong>!';
					}
					
				}
				$playerData['playerBody'].='<div class="kamp">
				<div class="more"></div>
				<div class="background_header">
				<div class="spinka">
				</div>
				<div class="puchar">
				</div>
				<div class="flaga">
				</div>
				<div class="nazwa_kampanii_txt">
				Nazwa kampanii:
				</div>
				<div class="nazwa_kampanii">
				"' . $nazwa . '"
				</div>
				<div class="zdobyte_punkty_txt">
				Zdobyte punkty:
				</div>
				<div class="zdobyte_punkty"> '.$punkty_gracza.'  </div>
				<div class="strona_txt">
				Strona:
				</div>
				<div class="strona">
				' . $nacja_txt . '
				</div>
				</div>
				<div class="szczegoly_kampanii">
				<div class="ksiazka">
				</div>
				<div class="szczegoly_txt">
				Szczegóły kampanii
				</div>
				<div class="start_koniec_txt">
				'.profil53.' ' . date('Y-m-d',$start_wojny) . '  '. $koniec_wojny .'<br>
				'.panel_data.': <strong>' . $objHelper->changeMonth($miesiac_przesuniety+1) . '</strong> '.comp_account48.' <strong>' . $rok . '</strong><br>
				Upłynęło <strong>' . $ilosc_tur . '</strong> '.comp_account49.'.<br>
				'.$wygrali .'<br>
				</div>
				<div class="staty_gracza_txt">
				'.statystyki_stat.'
				</div>
				<div class="wyprodukowano_txt">
				'.profil58.'
				</div>
				<div class="zniszczono_txt">
				'.profil59.'
				</div>
				<div class="wsi" title="'.profil58.' ' . $sztuki_lotnictwo_b . ' '.profil60 .' ' . $sztuki_lotnictwo_m . ' '.profil61.' ">
				</div>
				<div class="ws">
				' . $sztuki_lotnictwo . '
				</div>
				<div class="zsi" title="'.profil59.' ' . $dsztuki_lotnictwo_b . ' '.profil60 .' ' . $dsztuki_lotnictwo_m . ' '.profil61.' ">
				</div>
				<div class="zs">
				' . $dsztuki_lotnictwo . '
				</div>
				<div class="wci" title="'.profil58.' ' . $sztuki_pancerne . ' '.profil62.' ">
				</div>
				<div class="wc">
				' . $sztuki_pancerne . '
				</div>
				<div class="zci" title="'.profil59.' ' . $dsztuki_pancerne . ' '.profil62.' ">
				</div>
				<div class="zc">
				' . $dsztuki_pancerne . '
				</div>
				<div class="wfi"  title="'.profil58.' ' . $sztuki_flota_n . ' '.profil63.' ' . $sztuki_flota_p . ' '.profil64.' ">
				</div>
				<div class="wf">
				' . $sztuki_flota . '
				</div>
				<div class="zfi" title=" '.profil59.' ' . $dsztuki_flota_n . ' '.profil63.' ' . $dsztuki_flota_p . ' '.profil64.' ">
				</div>
				<div class="zf">
				' . $dsztuki_flota . '
				</div>
				<div class="wppi" title="'.profil58.' ' . $sztuki_piechota . ' '.profil65.' ">
				</div>
				<div class="wpp">
				' . $sztuki_piechota . '
				</div>
				<div class="zppi" title="'.profil59.' ' . $dsztuki_piechota . ' '.profil65.' ">
				</div>
				<div class="zpp">
				' . $dsztuki_piechota . '
				</div>
				<div class="wpi" title="'.profil58.' ' . $sztuki_przeciwlotnicze . ' '.profil66.' ">
				</div>
				<div class="wp">
				' . $sztuki_przeciwlotnicze . '
				</div>
				<div class="zpi" title="'.profil59.' ' . $dsztuki_przeciwlotnicze . ' '.profil66.' ">
				</div>
				<div class="zp">
				' . $dsztuki_przeciwlotnicze . '
				</div>
				<div class="wai" title="'.profil58.' ' . $sztuki_artyleria . ' '.profil67.' ">
				</div>
				<div class="wa">
				' . $sztuki_artyleria . '
				</div>
				<div class="zai" title="'.profil59.' ' . $dsztuki_artyleria . ' '.profil67.' ">
				</div>
				<div class="za">
				' . $dsztuki_artyleria . '
				</div>
				</div>
				</div>';
				$count++;
			}
		if($count == 0){
			$playerData['playerBody'].= comp_account50.' :)<br>
				';
		}		  
        echo json_encode($playerData);
    }

    public function editProfil()
    {
        $editProfil = array();
		$objHelper = init::getFactory() -> getService('helper');
        $data = $this -> path -> post('data');
        $stage = $this -> path -> post('stage');
        switch ($stage) {
            case '1'://zapis motto gracza
			$this -> db -> exec('UPDATE `accounts` SET `description`="'.$objHelper -> safeText($data).'" WHERE `id`=' . $this -> account -> id . ' ');
			$editProfil['body']= comp_account51;
			break;
        }


        echo json_encode($editProfil);
    }
	
	public function messageBox()
    {
        $messageBox = [];
		$messageBox['title']= comp_account52;
		$messageBox['authorList']='';
		$messageBox['authorList'] = $this -> getAuthorList(0);
        echo json_encode($messageBox);
    }
	
	public function actionMessageBox()
    {
        $messageBox = [];
		$messageBox['title']= comp_account52;
		$messageBox['authorList']='';
		$messageBox['error']='';
		$messageBox['num']='';
		$objHelper = init::getFactory() -> getService('helper');
        $descriptionMessage = $this -> path -> post('descriptionMessage');
		$addresseUser = $this -> path -> post('addresseUser');
		if (strlen($addresseUser) < 1){
			$messageBox['error']= comp_account53;
			echo json_encode($messageBox);
			exit();
		}
		
		$Factory = $this -> db -> query('SELECT `acc`.`id` AS `accountID`, `pl`.`id` as `playerID`,`acc`.`login` FROM `accounts` AS `acc` '
            . 'LEFT JOIN `players` AS `pl` ON (`acc`.`id`=`pl`.`accountID` AND `pl`.`worldID`=' . $this -> world -> id . ') '
            . ' WHERE `acc`.`login`="'.$addresseUser.'" ')-> fetch();
		
		if ( ! isset($Factory['playerID'])){
			$messageBox['error']= comp_account54;
			echo json_encode($messageBox);
			exit();
		}
		
		if (strlen($descriptionMessage) < 5){
			$messageBox['error']= comp_account55;
			echo json_encode($messageBox);
			exit();
		}
		
		$next='';
			$playerID = $this -> db -> query('SELECT *, count(*) as `countMessage` from `message` WHERE (`messageAuthorID`='.$this -> account -> playerID.' AND `playerID`= '.$Factory['playerID'].') OR (`playerID`='.$this -> account -> playerID.' AND `messageAuthorID`= '.$Factory['playerID'].' ) LIMIT 1 ')->fetch();
			if ( $playerID['countMessage'] == 0 ){
				
				$playerID2 = $this -> db -> query('SELECT max(`messageID`) as `maxMessageID` from `message` LIMIT 1 ')->fetch();
				if( $playerID2['maxMessageID'] == 0 ){
					$next = 1;
				}else{
					$next = $playerID2['maxMessageID']+1;
				}
		
				$sql = 'INSERT INTO `message` (`messageID`, `contentMessage`, `messageAuthorID`, `playerID`, `dateOfDispatch`,`seeMessage`) values ('.$next.',:contentMessage, '.$this -> account -> playerID.','.$Factory['playerID'].', ' . $this -> init -> time . ',"no")';
				$strMsg = $objHelper -> safeChatText($descriptionMessage);
				$query = $this -> db -> prepare($sql);
				$query -> bindValue(':contentMessage',$strMsg, PDO::PARAM_STR);
				$query -> execute();
				
				$messageBox['messageHistory'] = $this-> getDialog( $next, 1);
				$messageBox['authorList'] = $this -> getAuthorList($Factory['playerID']);
				$messageBox['num']=$next;
				
			}else{
				$sql = 'INSERT INTO `message` (`messageID`, `contentMessage`, `messageAuthorID`, `playerID`, `dateOfDispatch`,`seeMessage`) values ('.$playerID['messageID'].', :contentMessage, '.$this -> account -> playerID.','.$Factory['playerID'].', ' . $this -> init -> time . ',"no")';
				$strMsg = $objHelper -> safeChatText($descriptionMessage);
				$query = $this -> db -> prepare($sql);
				$query -> bindValue(':contentMessage',$strMsg, PDO::PARAM_STR);
				$query -> execute();
				$messageBox['messageHistory'] = $this-> getDialog( $playerID['messageID'], 1 );
				$messageBox['authorList'] = $this -> getAuthorList($Factory['playerID']);
				$messageBox['num']=$playerID['messageID'];
			}
			$arrMess = $this -> db -> query('SELECT count(*) FROM `message` WHERE `playerID`='.$Factory['playerID'].' AND `blockPlayerID`= 0  AND `seeMessage`="no" GROUP BY `messageID`  ')->fetch();
			
			$arrayData = array(
				'activity' => 'addContMessageTopic',
				'chanData' => array(
					array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
						'data' => array(
							'odbiorcaID' => $Factory['playerID'],
							'messAll'    => (int)$arrMess[0]
						),
					),
					array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
						'data' => array(
							'odbiorcaID' => $Factory['playerID'],
							'messAll'    => (int)$arrMess[0]
						)
					)
				)
			);
			$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
			
			
			$messageBox['nav']='<div id="application" num="'.$messageBox['num'].'">'.comp_account56.'</div>';
		
       echo json_encode($messageBox);
    }
	
	public function actionMessage()
    {
        $messageBox = array();
		$messageBox['title']= comp_account52;
		$messageBox['messageHistory'] ='';
		$messageBox['authorList']='';
		$messageBox['error']='';
        $num = $this -> path -> post('num');
		$step = $this -> path -> post('step');
		$descriptionMessage = $this -> path -> post('descriptionMessage');
		switch($step){
			case '1'://ładowanie całego dialogu
				$messageBox['messageHistory'] = $this-> getDialog( $num,1 );
				$playerID = $this -> db -> query('SELECT `messageAuthorID`, `playerID` from `message` WHERE `messageID`='.$num.' LIMIT 1 ')->fetch();
				if( $playerID['messageAuthorID'] == $this -> account -> playerID )
				{
					$messageBox['authorList'] = $this -> getAuthorList($playerID['playerID']);
				}else{
					$messageBox['authorList'] = $this -> getAuthorList($playerID['messageAuthorID']);
				}
				$arrMess = $this -> db -> query('SELECT count(*) FROM `message` WHERE ( ( `playerID`='.$this -> account -> playerID.' AND `blockPlayerID`= 0 ) OR ( `messageAuthorID`='.$this -> account -> playerID.' AND `blockAMID`= 0 ) ) AND `seeMessage`="no" GROUP BY `messageID`  ')->fetch();
				$messageBox['mess'] = (int)$arrMess[0];
			break;
			case '2'://dodawanie kolejnej wypowiedzi do dialogu o ID num
				$objHelper = init::getFactory() -> getService('helper');
				
				
				if (strlen($descriptionMessage) < 5)
				{
					$messageBox['error']= comp_account55;
					echo json_encode($messageBox);
					exit();
				}
				$playerID = $this -> db -> query('SELECT `messageAuthorID`, `playerID` from `message` WHERE `messageID`='.$num.' LIMIT 1 ')->fetch();
				
				
				if( $playerID['messageAuthorID'] == $this -> account -> playerID )
				{
					$sql = 'INSERT INTO `message` (`messageID`, `contentMessage`, `messageAuthorID`, `playerID`, `dateOfDispatch`,`seeMessage`) values ('.$num.', :contentMessage , '. $this -> account -> playerID .','. $playerID["playerID"].', ' . $this -> init -> time . ',"no")';
					$strMsg = $objHelper -> safeChatText($descriptionMessage);
					$query = $this -> db -> prepare($sql);
					$query -> bindValue(':contentMessage',$strMsg, PDO::PARAM_STR);
					$query -> execute();
					$messageBox['messageHistory'] = $this-> getDialog($num,1);
					
					$arrMess = $this -> db -> query('SELECT count(*) FROM `message` WHERE `playerID`='.$playerID['playerID'].' AND `blockPlayerID`= 0 AND `seeMessage`="no" GROUP BY `messageID`  ')->fetch();
					$arrayData = array(
						'activity' => 'addContMessageTopic',
						'chanData' => array(
							// Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
							array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
								'data' => array(
									'odbiorcaID' => $playerID['playerID'],
									'messAll'    => (int)$arrMess[0]
								),
							),
							array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
								'data' => array(
									'odbiorcaID' => $playerID['playerID'],
									'messAll'    => (int)$arrMess[0]
								)
							)
						)
					);
					$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
					$messageBox['authorList'] = $this -> getAuthorList($playerID['playerID']);
				}
				else
				{
					$sql = 'INSERT INTO `message` (`messageID`, `contentMessage`, `messageAuthorID`, `playerID`, `dateOfDispatch`,`seeMessage`) values ('.$num.', :contentMessage , '.$this -> account -> playerID.','.$playerID["messageAuthorID"].', ' . $this -> init -> time . ',"no")';
					$strMsg = $objHelper -> safeChatText($descriptionMessage);
					$query = $this -> db -> prepare($sql);
					$query -> bindValue(':contentMessage',$strMsg, PDO::PARAM_STR);
					$query -> execute();
					
					$messageBox['messageHistory'] = $this-> getDialog($num,1);
					$arrMess = $this -> db -> query('SELECT count(*) FROM `message` WHERE  `playerID`='.$playerID['messageAuthorID'].' AND `blockPlayerID`= 0 AND `seeMessage`="no"  GROUP BY `messageID`  ')->fetch();
					
					$arrayData = array(
						'activity' => 'addContMessageTopic',
						'chanData' => array(
							// Informacja dla sojuszników (w JS zanurzone jednostki trzeba rozpatrzyć w specjalny sposób)
							array('chanName' => 'worldmap' . $this->world->id . 'nation' . $this->account->nation,
								'data' => array(
									'odbiorcaID' => $playerID['messageAuthorID'],
									'messAll'    => (int)$arrMess[0]
								),
							),
							array('chanName' => 'worldmap'.$this -> world -> id.'nation'.($this -> account -> nation == 1 ? 2 : 1),
								'data' => array(
									'odbiorcaID' => $playerID['messageAuthorID'],
									'messAll'    => (int)$arrMess[0]
								)
							)
						)
					);
					$objHelper -> sendReqToNodeJS($arrayData, 'https://www.wichry-wojny.eu:3000/game');
					$messageBox['authorList'] = $this -> getAuthorList($playerID['messageAuthorID']);
				}
			break;
			case '3'://blokowanie / usuwanie dialogu o ID num
				   //nieaktywne
			break;
			case '4'://zgłoszenie do administracji dialogu o ID num
				$objHelper = init::getFactory() -> getService('helper');
				$strEmail='
					Dzień dobry :)
					Gracz '.$this -> account -> login.' zgłosił dialog o ID '.$num.' do administracji.
					<i><pre>
					'. $this-> getDialog($num,1) .'
					</pre>
					</i>
					Działamy!
				';
				$objHelper -> sendEmail($strEmail, "Zgłoszenie do administracji", 'team@wichry-wojny.eu');
				$this -> db -> query('INSERT INTO `supportApplication` (`playerID`, `descApp`, `topicApp`) values ('.$this -> account -> playerID.',"Zgłoszenie dialogu o id '.$num.'","Zgłoszenie dialogu" )');
				$messageBox['error']= comp_account57;
				echo json_encode($messageBox);
				exit();
			break;
			case '5'://pobranie wszystkich raportów systemowych
				$messageBox['messageHistory'] = $this-> getDialog( 0, 2 );
				$messageBox['authorList'] = $this -> getAuthorList(0);
				echo json_encode($messageBox);
				exit();
			break;
		}
		$messageBox['nav']='<div id="application" num="'.$num.'">'.comp_account56.'</div>';
		//$messageBox['authorList'] = $this -> getAuthorList(1333);
       echo json_encode($messageBox);
    }
	

	public function loadUnits()
    {
		$page = $this -> path -> get('data');
		echo $this -> unit -> loadUnits( $page );//ładuję jendostki do paska
    }
	
	public function editCash()//pobieramy listę płatności i wynagrodzeń gracza
    {
        $stage = $this -> path -> post('stage');
		$dataArr['title']= comp_account58;
				$topBox = $this -> db -> query('SELECT * FROM `playerFinance` WHERE `playerID`='. $this -> account -> playerID .' ');

                $i = 1;
				$dataArr['body'] = '
					<div id="tableTopBox">
						<table class="topBox" border="1">
							<tr>
								<td>lp</td><td></td><td>'. comp_account58 .'</td><td></td><td>'. comp_account60 .'</td>
							</tr>	
				';
				$koszta=0;
				$przychod=0;
				foreach ($topBox as $tableTopBox)
				{
					$operacja = $tableTopBox['operation'];
					$kwota    = $tableTopBox['cashValue'];
					$zadanie  = $tableTopBox['saldoAction'];
						if($operacja==1){
							$operacja_txt=finanse_wynagrodzenie_txt;
							$bg = 'first';
						}else if($operacja==2){
							$operacja_txt=finanse_zold_txt;
							$bg = 'second';
						}
						if($zadanie==0){
							$zadanie_wynik='-'.$kwota;
							$koszta -= $kwota;
						}else if($zadanie==1){
							$zadanie_wynik='+'.$kwota;
							$przychod += $kwota;
						}
					$dataArr['body'].= '<tr style="border-top: 1px solid #6b553c; border-bottom: 1px solid #6b553c;">
											<td class="'.$bg.'">'.$i.'</td><td class="'.$bg.'"></td><td class="'.$bg.'">'.$operacja_txt.'</td><td class="'.$bg.'"></td><td class="'.$bg.'" ><strong>'.$zadanie_wynik.'</strong> '.panel_gry6.'</td>
										</tr>';
							$i++;	
				}
                if($i == 1){
                    $dataArr['error'] = comp_account61;
                    echo json_encode($dataArr);
                    exit();
                }

				$wynik_gracza=$koszta+$przychod;
				if( $wynik_gracza < 0 ){
					$wynik_gracza_txt='<strong style="color:#ff6666">'.$wynik_gracza.'</strong>';
				}else{
					$wynik_gracza_txt=$wynik_gracza;
				}
		switch($stage)
		{	
			case '1'://przygotowanie listy płatności i wynagrodzeń	
				$dataArr['body'].= "<tr><td colspan=\"4\">".suma_txt."</td><td>".$wynik_gracza_txt." ".panel_gry6."</td></tr>
				<tr><td colspan=\"5\" style=\"text-align:center;\"><div id=\"akcja\">".skarbiec_txt."</div></td></tr></table>";
			break;
			case '2'://uzupełnienie danych, aktualizacja finansów gracza
				$dataArr['body']='';
				$dataArr['error']='';
				$dataArr['minusSoft']=0;
				$dataArr['minusPrem']=0;
				$dataArr['plusSoft']=0;
				$dataArr['width'] = '640px';
				$dataArr['height'] = '600px';
				if( $wynik_gracza < 0 )//gracz ma koszta większe niż jego przychody
				{
					if( abs($wynik_gracza) < $this -> account -> softCurrency)
					{
						$dataArr['error'].= comp_account62.' '.abs($wynik_gracza).' '.panel_gry6;
						$dataArr['newSoft']=$this -> account -> softCurrency-abs($wynik_gracza);
						$dataArr['minusSoft']=abs($wynik_gracza);
						$this -> db -> exec('UPDATE `players` SET `softCurrency`=(`softCurrency`-'.abs($wynik_gracza).') WHERE `id`=' . $this -> account -> playerID . ' ');
						$this -> db -> exec('UPDATE `accounts` SET `getFinance`=(`getFinance`+1) WHERE `id`=' . $this->account->id . ' ');
						$this -> db -> exec('DELETE FROM `playerFinance` WHERE `playerID`=' . $this -> account -> playerID . ' ');
					}
					else
					{
						if( $this -> account -> softCurrency > 0 )
						{
							if( $this -> account -> PremiumCurrency <= abs($wynik_gracza) - $this -> account -> softCurrency )
							{
								$dataArr['newSoft'] = 0;
								$dataArr['minusSoft'] = $this -> account -> softCurrency;
								$dataArr['newPrem'] = 0;
								$dataArr['minusPrem'] = $this -> account -> PremiumCurrency;
								$this -> db -> exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this -> account -> playerID . ' ');
								$this -> db -> exec('UPDATE `accounts` SET `premiumCurrency`=0,`getFinance`=(`getFinance`+1) WHERE `id`=' . $this -> account -> id . ' ');
								$dataArr['body'].= comp_account63.' ;/';
							}
							else
							{
								$dataArr['newSoft']=0;
								$dataArr['minusSoft'] = $this -> account -> softCurrency;
								$this -> db -> exec('UPDATE `players` SET `softCurrency`=0 WHERE `id`=' . $this -> account -> playerID . ' ');
								$dataArr['newPrem']= $this -> account -> PremiumCurrency - ( abs($wynik_gracza) - $this -> account -> softCurrency );
								$dataArr['minusPrem'] = abs($wynik_gracza) - $this -> account -> softCurrency;
								$this -> db -> exec('UPDATE `accounts` SET `premiumCurrency`='.$dataArr['newPrem'].',`getFinance`=(`getFinance`+1) WHERE `id`=' . $this -> account -> id . ' ');
								$this -> db -> exec('DELETE FROM `playerFinance` WHERE `playerID`=' . $this -> account -> playerID . ' ');
								$dataArr['body'].=  comp_account64.'. '. (abs($wynik_gracza) - $this -> account -> softCurrency) .' '.comp_account64;
							}
						}
						else//waluta miękka na minusie
						{
							if( $this -> account -> PremiumCurrency > abs($wynik_gracza) )
							{
								$dataArr['newSoft']= $this -> account -> softCurrency;
								$dataArr['newPrem']= $this -> account -> PremiumCurrency - abs($wynik_gracza);
								$dataArr['minusPrem'] = abs($wynik_gracza);
								$this -> db -> exec('UPDATE `accounts` SET `premiumCurrency`='.$dataArr['newPrem'].', `getFinance`=( `getFinance`+1 ) WHERE `id`=' . $this -> account -> id . ' ');
								$this -> db -> exec('DELETE FROM `playerFinance` WHERE `playerID`=' . $this -> account -> playerID . ' ');
								$dataArr['body'].= comp_account63.' '.comp_account64.' '.abs($wynik_gracza).' '.comp_account65;
							}
							else
							{
								$dataArr['body'].= comp_account63.' ;/';
							}
						}
					}
				}
				else//gracz ma większe przychody jak koszta
                {
                    $this->db->exec('UPDATE `players` SET `softCurrency`=(`softCurrency`+' . $wynik_gracza_txt . ') WHERE `id`=' . $this->account->playerID . ' ');
                    $this -> db -> exec('UPDATE `accounts` SET `getFinance`=(`getFinance`+1) WHERE `id`=' . $this -> account -> id . ' ');
					$this->db->exec('DELETE FROM `playerFinance` WHERE `playerID`=' . $this->account->playerID . ' ');
                    $textNagroda = '';
                    $goldNagroda = 0;
                    /*
					
					dodac ładniejsza wersję sprawdzania zołdów :)
					
					
					$arrayNumber = array(1,2,3,4,5, 10, 20, 30, 40, 50, 100, 200, 300, 500, 1000, 1500, 2000);
					if (in_array($this->account->getFinance, $arrayNumber, true)) {
						$textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 1  '.comp_account69.'<br>'.comp_account70.' ( 5 '.comp_account69.' ) '.comp_account71.' 10 '.comp_account72;
                       
					}
					
					
					
					*/
					
					
					
					if ($this->account->getFinance < 5) {
                        $textNagroda = comp_account68;
                        $goldNagroda = 1;
                    } else if ($this->account->getFinance == 5) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 1  '.comp_account69.'<br>'.comp_account70.' ( 3 '.comp_account69.' ) '.comp_account71.' 10 '.comp_account72;
                        $goldNagroda = 1;
                    } else if ($this->account->getFinance == 10) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 3  '.comp_account69.'<br>'.comp_account70.' ( 5 '.comp_account69.' ) '.comp_account71.' 20 '.comp_account72;
                        $goldNagroda = 5;
                    } else if ($this->account->getFinance == 20) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 5 '.comp_account69.'<br>'.comp_account70.' ( 7 '.comp_account69.' ) '.comp_account71.' 30 '.comp_account72;
                        $goldNagroda = 7;
                    } else if ($this->account->getFinance == 30) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 7 '.comp_account69.'<br>'.comp_account70.' ( 9 '.comp_account69.' ) '.comp_account71.' 40 '.comp_account72;
                        $goldNagroda = 10;
                    } else if ($this->account->getFinance == 40) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 9 '.comp_account69.'<br>'.comp_account70.' ( 12 '.comp_account69.' ) '.comp_account71.' 50 '.comp_account72;
                        $goldNagroda = 20;
                    } else if ($this->account->getFinance == 50) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 12 '.comp_account69.'<br>'.comp_account70.' ( 15 '.comp_account69.' ) '.comp_account71.' 100 '.comp_account72;
                        $goldNagroda = 25;
                    } else if ($this->account->getFinance == 100) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 15 '.comp_account69.'<br>'.comp_account70.' ( 19 '.comp_account69.' ) '.comp_account71.' 200 '.comp_account72;
                        $goldNagroda = 40;
                    } else if ($this->account->getFinance == 200) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 19 '.comp_account69.'<br>'.comp_account70.' ( 23 '.comp_account69.' ) '.comp_account71.' 300 '.comp_account72;
                        $goldNagroda = 60;
                    } else if ($this->account->getFinance == 300) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 23 '.comp_account69.'<br>'.comp_account70.' ( 30 '.comp_account69.' ) '.comp_account71.' 500 '.comp_account72;
                        $goldNagroda = 80;
                    } else if ($this->account->getFinance == 500) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 30 '.comp_account69.'<br>'.comp_account70.' ( 35 '.comp_account69.' ) '.comp_account71.' 1000 '.comp_account72;
                        $goldNagroda = 100;
                    } else if ($this->account->getFinance == 1000) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 35 '.comp_account69.'<br>'.comp_account70.' ( 50 '.comp_account69.' ) '.comp_account71.' 1500 '.comp_account72;
                        $goldNagroda = 100;
                    } else if ($this->account->getFinance == 1500) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 50 '.comp_account69.'<br>'.comp_account70.' ( 68 '.comp_account69.' ) '.comp_account71.' 2000 '.comp_account72;
                        $goldNagroda = 100;
                    } else if ($this ->account->getFinance == 2000) {
                        $textNagroda = comp_account66.' '.$this->account->getFinance.' '.comp_account67.' + 68 '.comp_account69;
                        $goldNagroda = 100;
                    }

                    if ($this->account->getFinance > 5 AND $this->account->getFinance < 10 ) {
                        $textNagroda = comp_account73.' ( 3 '.comp_account69.' ) '.comp_account74.' '. ( 10 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance > 10 AND $this->account->getFinance < 20) {
                        $textNagroda = comp_account73.' ( 5 '.comp_account69.' ) '.comp_account74.' '. ( 20 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance > 20 AND $this->account->getFinance < 30) {
                        $textNagroda = comp_account73.' ( 7 '.comp_account69.' ) '.comp_account74.' '. ( 30 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 30 AND $this->account->getFinance < 40) {
                        $textNagroda = comp_account73.' ( 9 '.comp_account69.' ) '.comp_account74.' '. ( 40 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 40 AND $this->account->getFinance < 50) {
                        $textNagroda = comp_account73.' ( 12 '.comp_account69.' ) '.comp_account74.' '. ( 50 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 50 AND $this->account->getFinance < 100) {
                        $textNagroda = comp_account73.' ( 15 '.comp_account69.' ) '.comp_account74.' '. ( 100 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 100 AND $this->account->getFinance < 200) {
                        $textNagroda = comp_account73.'  ( 19 '.comp_account69.' ) '.comp_account74.' '. ( 200 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 200 AND $this->account->getFinance < 300) {
                        $textNagroda = comp_account73.' ( 23 '.comp_account69.' ) '.comp_account74.' '. ( 300 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 300 AND $this->account->getFinance < 500) {
                        $textNagroda = comp_account73.' ( 30 '.comp_account69.' ) '.comp_account74.' '. ( 500 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 500 AND $this->account->getFinance < 1000) {
                        $textNagroda = comp_account73.' ( 35 '.comp_account69.' ) '.comp_account74.' '. ( 1000 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 1000 AND $this->account->getFinance < 1500) {
                        $textNagroda = comp_account73.' ( 50 '.comp_account69.' ) '.comp_account74.' '. ( 1500 - $this->account->getFinance-1 ) .' '.comp_account72;
                    } else if ($this->account->getFinance  > 1500 AND $this->account->getFinance < 2000) {
                        $textNagroda = comp_account73.' ( 68 '.comp_account69.' ) '.comp_account74.' '. ( 2000 - $this->account->getFinance-1 ) .' '.comp_account72;
                    }


                    $dataArr['body'] .= finanse_txt;
					$dataArr['goldNagroda'] = $goldNagroda;
                    $dataArr['plusSoft'] = $wynik_gracza;
                    $dataArr['plusGold'] = $goldNagroda;
					$dataArr['width'] = '300px';
					$dataArr['height'] = '150px';
					$dataArr['goldNagrodaTxt'] = $textNagroda.'<br>
					<div id="getGold">'.comp_account75.'</div>';
                    if ( $goldNagroda > 0 )
                    {
                        $this->db->query('INSERT INTO `getGold` (`playerID`, `gold`) values ('.$this -> account -> playerID.', '.$goldNagroda.' )');
                    }
                }
				$dataArr['timeK'] = '';
				$dataArr['onTimer'] = 0;
				if( $this -> world-> timing == 1 ){
					$dataArr['timeK'] = $this -> init-> time + $this -> world-> timeToUp;
					$this -> db -> exec('UPDATE `players` SET `timeK`='. ( $this -> init-> time + $this -> world-> timeToUp ) .' WHERE `id`=' . $this -> account -> playerID . ' ');
					$dataArr['onTimer'] = 1;
				}
			break;
		}
		echo json_encode($dataArr);
    }
	
	public function endPayUser()
	{
		$strData = $this -> path -> get('data');
        parse_str(substr($strData,1), $arrReturnData);

        $service = $arrReturnData['service'];
		$orderid = $arrReturnData['orderid'];
		$amount = $arrReturnData['amount'];
		$userdata = $arrReturnData['userdata'];
		$status = $arrReturnData['status'];
		$sign = $arrReturnData['sign'];

			// Dane punktu otrzymane po zakonczeniu procesu rejestracji
			$serviceURL	= "wichry-wojny.eu(2)";        // Identyfikator punktu
			$key   		= "6004dacedd1109fa46fd1c9464ccc060";        // Klucz szyfrujacy
			// Funkcja sprawdzajaca poprawnosc sygnatury
			$hash=md5($service.$orderid.$amount.$userdata.$status.$key);
			if($hash==$sign && strtoupper($status) == 'OK' && $service == $serviceURL)
			{
				switch( $amount ){
					case '9':
						$ile_gold=200;
						$desc= platnoscidesc200;
						break;
					case '20':
						$ile_gold=500;
						$desc= platnoscidesc500;
						break;
					case '39':
						$ile_gold=1000;
						$desc= platnoscidesc1000;
						break;
					case '75':
						$ile_gold=2000;
						$desc= platnoscidesc2000;
						break;	
					default:
				}
				$this -> db -> exec('UPDATE `operacje` SET `seen`=1 WHERE `orderid`="'.$orderid.'" ');
				$this -> error -> printError( comp_account76.' '.$desc, 'mapa');
			}
			else
			{
				// Obsluga transakcji negatywnie zautoryzowanej
				$this -> error -> printError( comp_account77, 'mapa');
			}
	}
			
	public function endPay()
	{
        $service  = $this -> path -> post('service');
		$orderid  = $this -> path -> post('orderid');
		$amount   = $this -> path -> post('amount');
		$userdata = $this -> path -> post('userdata');
		$status   = $this -> path -> post('status');
		$sign     = $this -> path -> post('sign');
		
			// Dane punktu otrzymane po zakonczeniu procesu rejestracji
			$serviceURL	= "wichry-wojny.eu(2)";        // Identyfikator punktu
			$key   		= "6004dacedd1109fa46fd1c9464ccc060";        // Klucz szyfrujacy
			// Funkcja sprawdzajaca poprawnosc sygnatury
			$hash=md5($service.$orderid.$amount.$userdata.$status.$key);
			if($hash==$sign && strtoupper($status) == 'OK' && $service == $serviceURL){
				// Obsluga transakcji pozytywnie zautoryzowanej
				//dane dostepowe do mojej bazy danych
				$sql_order_id = $this -> db -> query('SELECT * FROM `operacje` WHERE `orderid`=\''.$orderid.'\' ')->fetch();
				if( !$sql_order_id['auto_id'] )
				{
					$t = unserialize(base64_decode($userdata));
					$id_gracza = $t["user_id"];
					$result = $this -> db -> query('SELECT `gold` FROM `accounts` WHERE `id`='.$id_gracza.' ')->fetch();
					switch( $amount ){
						case '9':
							$ile_gold=200;
							$desc= platnoscidesc200;
							break;
						case '20':
							$ile_gold=500;
							$desc= platnoscidesc500;
							break;
						case '39':
							$ile_gold=1000;
							$desc= platnoscidesc1000;
							break;
						case '75':
							$ile_gold=2000;
							$desc= platnoscidesc2000;
							break;
						default:
					}
					/*
					$datateraz    = date("Y-m-d H:i:s");
					$data = new DateTime($datateraz);
					$data_promocja = new DateTime("2014-02-07 17:00:00");
					if($data>$data_promocja){
						$ile_gold=$ile_gold+($ile_gold*0.2);
					}
					*/
					//$new_gold=$gold+$ile_gold;
					$this -> db -> exec('UPDATE `accounts` SET `gold`=(`gold`+'.$ile_gold.') WHERE `id`='.$id_gracza.' ');
					//zapis operacji wykonania przelewu
					$this -> db -> exec('INSERT INTO operacje (data, accountID, ile_mial, ile_dodano, orderid) VALUES ('.$this -> init -> time .', '.$id_gracza.','.$result['gold'].', '.$ile_gold.', \''.$orderid.'\') ');
					echo 'OK';
				}else{
					echo "taka oeracja była już wykonywana";
				}
			}else{
				// Obsluga transakcji negatywnie zautoryzowanej
				echo " wystąpił jakiś błąd";
			}
	}		

	public function loadPlayerStatusBar()
    {
        $gracz = $this -> path -> get('accountID');
        //echo "gracz o id=".$gracz;
        if( $gracz )
        {
            $this -> db -> exec("SET @id=" . $gracz . ",@nr=0, @idnr=0");
            $arrGlobalRankData = $this -> db -> query("SELECT @idnr AS nr FROM (SELECT @nr:=@nr +1, IF( ID=@id, @idnr:=@nr ,@idnr=@idnr), ID FROM `accounts` ORDER BY globalPoints DESC) podsel WHERE podsel.ID = @id")->fetch();
            //echo "ranking=" . $arrGlobalRankData['nr'];
            $dane = $this -> db -> query('SELECT * FROM `accounts` WHERE `id`='.$gracz.' ')->fetch();

            if( $arrGlobalRankData['nr'] == 0 )
            {
                echo statusbar1;
            }
            else
            {

                $logo_file = "./app/templates/assets/images/ordery.png";
                $image_file = "./app/templates/assets/images/baar.jpg";
                $photo = imagecreatefromjpeg($image_file);
                $fotoW = imagesx($photo);
                $fotoH = imagesy($photo);
                $logoImage = imagecreatefrompng($logo_file);
                $logoW = imagesx($logoImage);
                $logoH = imagesy($logoImage);
                $photoFrame = imagecreatetruecolor($fotoW,$fotoH);
                $dest_x = $fotoW - $logoW;
                $dest_y = $fotoH - $logoH;
                imagecopyresampled($photoFrame, $photo, 0, 0, 0, 0, $fotoW, $fotoH, $fotoW, $fotoH);
                $statsP = $this -> db -> query('SELECT * FROM `statsPlayer` WHERE `accountID`=' . $gracz . ' AND `nation`=1 ') -> fetch();
                $piech = $statsP['d_piechota'];
                $panc = $statsP['d_pancerne'];
                $art = $statsP['d_artyleria'];
                $plot = $statsP['d_przeciwlotnicze'];
                $blot = $statsP['d_lotnictwo_b'];
                $lot = $statsP['d_lotnictwo_m'];
                $pod = $statsP['d_flota_p'];
                $mw = $statsP['d_flota_n'];

                $statsN = $this -> db -> query('SELECT * FROM `statsPlayer` WHERE `accountID`=' . $gracz . ' AND `nation`=2 ') -> fetch();
                $wer = $statsN['d_piechota'];
                $panz = $statsN['d_pancerne'];
                $art_c = $statsN['d_artyleria'];
                $flak = $statsN['d_przeciwlotnicze'];
                $luftb = $statsN['d_lotnictwo_b'];
                $luft = $statsN['d_lotnictwo_m'];
                $uboot = $statsN['d_flota_p'];
                $kriegs = $statsN['d_flota_n'];
                /*
                if($gracz == 1)
                {
                    $wer=15000;
                    $panz=15000;
                    $flak=15000;
                    $luftb=15000;
                    $luft=15000;
                    $uboot=15000;
                    $kriegs=15000;
                }
                */
                $wer=15000;
                $panz=15000;
                $flak=15000;
                $luftb=15000;
                $luft=15000;
                $uboot=15000;
                $kriegs=15000;
                if($wer>=15000){
                    imagecopy($photoFrame, $logoImage, 0, 55, 100, 0, 50, 50);//złoty
                }else if($wer<15000 AND $wer>=10000){
                    imagecopy($photoFrame, $logoImage, 0, 55, 50, 0, 50, 50);//srebrny
                }else if($wer<10000 AND $wer>=5000){
                    imagecopy($photoFrame, $logoImage, 0, 55, 0, 0, 50, 50);//brązowy
                }
                if($panz>=15000){
                    imagecopy($photoFrame, $logoImage, 40, 55, 100, 50, 50, 50);//złoty
                }else if($panz<15000 AND $panz>=10000){
                    imagecopy($photoFrame, $logoImage, 40, 55, 50, 50, 50, 50);//srebrny
                }else if($panz<10000 AND $panz>=5000){
                    imagecopy($photoFrame, $logoImage, 40, 55, 0, 50, 50, 50);//brązowy
                }
                if($art_c>=15000){

                }else if($art_c<15000 AND $art_c>=10000){

                }else if($art_c<10000 AND $art_c>=5000){

                }
                if($flak>=15000){
                    imagecopy($photoFrame, $logoImage, 0, 100, 100, 300, 50, 50);//złoty
                }else if($flak<15000 AND $flak>=10000){
                    imagecopy($photoFrame, $logoImage, 0, 100, 50, 300, 50, 50);//złoty
                }else if($flak<10000 AND $flak>=5000){
                    imagecopy($photoFrame, $logoImage, 0, 100, 0, 300, 50, 50);//złoty
                }
                if($luftb>=15000){
                    imagecopy($photoFrame, $logoImage, 125, 100, 100, 150, 50, 50);//złoty
                }else if($luftb<15000 AND $luftb>=10000){
                    imagecopy($photoFrame, $logoImage, 125, 100, 50, 150, 50, 50);//złoty
                }else if($luftb<10000 AND $luftb>=5000){
                    imagecopy($photoFrame, $logoImage, 125, 100, 0, 150, 50, 50);//złoty
                }
                if($luft>=15000){
                    imagecopy($photoFrame, $logoImage, 40, 102, 100, 200, 50, 50);//złoty
                }else if($luft<15000 AND $luft>=10000){
                    imagecopy($photoFrame, $logoImage, 40, 102, 50, 200, 50, 50);//złoty
                }else if($luft<10000 AND $luft>=5000){
                    imagecopy($photoFrame, $logoImage, 40, 102, 0, 200, 50, 50);//złoty
                }
                if($uboot>=15000){
                    imagecopy($photoFrame, $logoImage, 85, 60, 100, 100, 50, 50);//złoty
                }else if($uboot<15000 AND $uboot>=10000){
                    imagecopy($photoFrame, $logoImage, 85, 60, 50, 100, 50, 50);//złoty
                }else if($uboot<10000 AND $uboot>=5000){
                    imagecopy($photoFrame, $logoImage, 85, 60, 0, 100, 50, 50);//złoty
                }
                if($kriegs>=15000){
                    imagecopy($photoFrame, $logoImage, 85, 100, 100, 250, 50, 50);//złoty
                }else if($kriegs<15000 AND $kriegs>=10000){
                    imagecopy($photoFrame, $logoImage, 85, 100, 50, 250, 50, 50);//złoty
                }else if($kriegs<10000 AND $kriegs>=5000){
                    imagecopy($photoFrame, $logoImage, 85, 100, 0, 250, 50, 50);//złoty
                }

                /*
                if( $gracz == 1 )
                {
                    $piech=15000;
                    $panc=15000;
                    $plot=15000;
                    $blot=15000;
                    $lot=15000;
                    $pod=15000;
                    $mw=15000;
                }
                */
                $piech=15000;
                $panc=15000;
                $plot=15000;
                $blot=15000;
                $lot=15000;
                $pod=15000;
                $mw=15000;
                if($piech>=15000){
                    imagecopy($photoFrame, $logoImage, 320, 100, 350, 0, 50, 50);//złoty
                }else if($piech<15000 AND $piech>=10000){
                    imagecopy($photoFrame, $logoImage, 320, 100, 300, 0, 50, 50);//złoty
                }else if($piech<10000 AND $piech>=5000){
                    imagecopy($photoFrame, $logoImage, 320, 100, 250, 0, 50, 50);//złoty
                }
                if($panc>=15000){
                    imagecopy($photoFrame, $logoImage, 350, 100, 350, 50, 50, 50);//złoty
                }else if($panc<15000 AND $panc>=10000){
                    imagecopy($photoFrame, $logoImage, 350, 100, 300, 50, 50, 50);//złoty
                }else if($panc<10000 AND $panc>=5000){
                    imagecopy($photoFrame, $logoImage, 350, 100, 250, 50, 50, 50);//złoty
                }
                if($art>=15000){
                    $art_img="";
                }else if($art<15000 AND $art>=10000){
                    $art_img="";
                }else if($art<10000 AND $art>=5000){
                    $art_img="";
                }else{
                    $art_img="";
                }
                if($plot>=15000){
                    imagecopy($photoFrame, $logoImage, 440, 50, 350, 300, 50, 50);//złoty
                }else if($plot<15000 AND $plot>=10000){
                    imagecopy($photoFrame, $logoImage, 440, 50, 300, 300, 50, 50);//złoty
                }else if($plot<10000 AND $plot>=5000){
                    imagecopy($photoFrame, $logoImage, 440, 50, 250, 300, 50, 50);//złoty
                }
                if($blot>=15000){
                    imagecopy($photoFrame, $logoImage, 380, 100, 350, 150, 50, 50);//złoty
                }else if($blot<15000 AND $blot>=10000){
                    imagecopy($photoFrame, $logoImage, 380, 100, 300, 150, 50, 50);//złoty
                }else if($blot<10000 AND $blot>=5000){
                    imagecopy($photoFrame, $logoImage, 380, 100, 250, 150, 50, 50);//złoty
                }
                if($lot>=15000){
                    imagecopy($photoFrame, $logoImage, 410, 100, 350, 200, 50, 50);//złoty
                }else if($lot<15000 AND $lot>=10000){
                    imagecopy($photoFrame, $logoImage, 410, 100, 300, 200, 50, 50);//złoty
                }else if($luft<10000 AND $lot>=5000){
                    imagecopy($photoFrame, $logoImage, 410, 100, 250, 200, 50, 50);//złoty
                }
                if($pod>=15000){
                    imagecopy($photoFrame, $logoImage, 320, 60, 350, 100, 50, 50);//złoty
                }else if($pod<15000 AND $pod>=10000){
                    imagecopy($photoFrame, $logoImage, 320, 60, 300, 100, 50, 50);//złoty
                }else if($pod<10000 AND $pod>=5000){
                    imagecopy($photoFrame, $logoImage, 320, 60, 250, 100, 50, 50);//złoty
                }
                if($mw>=15000){
                    imagecopy($photoFrame, $logoImage, 440, 100, 350, 250, 50, 50);//złoty
                }else if($mw<15000 AND $mw>=10000){
                    imagecopy($photoFrame, $logoImage, 440, 100, 300, 250, 50, 50);//złoty
                }else if($mw<10000 AND $mw>=5000){
                    imagecopy($photoFrame, $logoImage, 440, 100, 250, 250, 50, 50);//złoty
                }else{
                    $mw_img="";
                }
                putenv('GDFONTPATH=' . realpath('.'));
                $n0 = statusbar4;
                $n = $dane['login'];
                $n1 = statusbar3;
                $n2 = $arrGlobalRankData['nr'];

                $font = "./app/templates/assets/fonts/urbana/urbana.ttf";
                $fontsize = 17;
                $fontsize1 = 11;
                $text_color = imagecolorallocate($photoFrame, 233, 14, 91);
                $bialy = imagecolorallocate($photoFrame, 255, 255, 255);
                function textCenter($photoFrame, $text, $size, $font) {
                    $t = imagettfbbox($size, 0, $font, $text);
                    $x = (imagesx($photoFrame)/2) - (($t[4] - $t[6])/2);
                    $y = (imagesy($photoFrame)/2) + (($t[1] - $t[7])/2);
                    return array("x" => round($x), "y" => round($y));
                }
                $t0 = textCenter($photoFrame, $n0, $fontsize1, $font);
                $t = textCenter($photoFrame, $n, $fontsize, $font);
                $t1 = textCenter($photoFrame, $n1, $fontsize1, $font);
                $t2 = textCenter($photoFrame, $n2, $fontsize, $font);
                imagettftext($photoFrame, $fontsize1, 0, $t0['x'], 12, $bialy, $font, $n0);
                imagettftext($photoFrame, $fontsize, 0, $t['x'], 30, $text_color, $font, $n);
                imagettftext($photoFrame, $fontsize1, 0, $t1['x'], 126, $bialy, $font, $n1);
                imagettftext($photoFrame, $fontsize, 0, $t2['x'], 144, $text_color, $font, $n2);

                header("Content-Type: image/jpeg");
                imagejpeg($photoFrame);
                imagedestroy($photoFrame);
            }
        }
        else
        {
            echo statusbar2;
        }
    }


	public function getPaymentCode()
	{
        $paymentCode  = $this -> path -> post('pID');
		$revData = [];
		switch( $paymentCode ){
			case 1:
				$revData['rev'] = '
				<div id="set1" class="platnosc_box">
					<div class="wartosc"><strong class="big_int">100</strong><br>sztuk<br>złota</div>
					<div class="kwota">6,15<br>PLN</div>
					<div class="skrzynia_100"></div>
					<div class="zakup"><strong class="buy" id="buy1">Kupuję</strong></div>
				</div>
				<div id="set2" class="platnosc_box">
					<div class="procenty">10% <br>taniej!</div>
					<div class="wartosc"><strong class="big_int">200</strong><br>sztuk<br>złota</div>
					<div class="kwota">11,07<br><small style="text-decoration: line-through">12,30</small><br>PLN</div>
					<div class="skrzynia_200"></div>
					<div class="zakup"><strong class="buy" id="buy2">Kupuję</strong></div>
				</div>
				<div id="set4" class="platnosc_box">
					<div class="procenty">13%<br> taniej!</div>
					<div class="wartosc"><strong class="big_int">450</strong><br>sztuk<br>złota</div>
					<div class="kwota">24,6<br><small style="text-decoration: line-through">28,30</small><br>PLN</div>
					<div class="skrzynia_450"></div>
					<div class="zakup"><strong class="buy" id="buy3">Kupuję</strong></div>
				</div>
				<div id="set6" class="platnosc_box">
					<div class="procenty">17%<br> taniej!</div>
					<div class="wartosc"><strong class="big_int">600</strong><br>sztuk<br>złota</div>
					<div class="kwota">30,75<br><small style="text-decoration: line-through">36,90</small><br>PLN</div>
					<div class="skrzynia_600"></div>
					<div class="zakup"><strong class="buy" id="buy4">Kupuję</strong></div>
				</div>';
			break;
			case 2:
				$revData['rev'] = '
				W szybki sposób dzięki płatnościom CASH BILL zasilisz swoje konto.
					<form id="cashBillForm">
					<div id="set1" class="platnosc_box">
						<div class="wartosc"><strong class="big_int">200</strong><br>sztuk<br>złota</div>
						<div class="kwota">9<br>PLN</div>
						<div class="skrzynia_200"></div>
						<div class="zakup"><input type="radio" name="amount" value="9" checked="checked"/></div>
					</div>
					<div id="set2" class="platnosc_box">
						<div class="procenty">10%<br> taniej!</div>
						<div class="wartosc"><strong class="big_int">500</strong><br>sztuk<br>złota</div>
						<div class="kwota">20<br><small style="text-decoration: line-through">22,5</small><br>PLN</div>
						<div class="skrzynia_600"></div>
						<div class="zakup"><input type="radio" name="amount" value="20"/></div>
					</div>
					<div id="set4" class="platnosc_box">
						<div class="procenty">13%<br> taniej!</div>
						<div class="wartosc"><strong class="big_int">1000</strong><br>sztuk<br>złota</div>
						<div class="kwota">39<br><small style="text-decoration: line-through">45</small><br>PLN</div>
						<div class="skrzynia_1000"></div>
						<div class="zakup"><input type="radio" name="amount" value="39"/></div>
					</div>
					<div id="set6" class="platnosc_box">
						<div class="procenty">17%<br> taniej!</div>
						<div class="wartosc"><strong class="big_int">2000</strong><br>sztuk<br>złota</div>
						<div class="kwota">75<br><small style="text-decoration: line-through">90</small><br>PLN</div>
						<div class="skrzynia_2000"></div>
						<div class="zakup"><input type="radio" name="amount" value="75"/></div>
					</div>
					<div id="przycisk_online">
					 Wybierz z powyższych opcji o ile złota chcesz zwiększyć swoje konto.<br>
					<input type="button" class="button_sell" id="button_sell" value="kupuję złoto" />
					</div>
					</form>
					<div id="logo_platnosci"><img src="./app/templates/assets/images/pcb_630x170_on-line.jpg"/></div>';
			break;
			case 3:
			$revData['rev'] = '
				<p>'. ingame31 .'
				<textarea rows="3" cols="85" readonly="readonly" style="font-size:10px;">
					<a href="https://www.wichry-wojny.eu/ref/'. $this -> account -> id .'" title="'. ingame32 .'">'. ingame32 .'</a>
				</textarea><br><br><br><br><br>
				'. ingame34 .' :)<br><br>
				<textarea rows="4" cols="85" readonly="readonly" style="font-size:10px;"><a href="https://www.wichry-wojny.eu/ref/'. $this -> account -> id .'" title="'. ingame32 .'">
					<img src="https://www.wichry-wojny.eu/banery/banner1_468x60.jpg" alt="'. ingame32 .'"/></a>
				</textarea>
				<br>'. ingame35 .':<br>
					<img src="https://www.wichry-wojny.eu/banery/banner1_468x60.jpg" alt="'. ingame32 .'"/><br><br><br><br><br>
				<textarea rows="4" cols="85" readonly="readonly" style="font-size:10px;"><a href="https://www.wichry-wojny.eu/ref/'. $this -> account -> id .'" title="'. ingame32 .'">
				<img src="https://www.wichry-wojny.eu/banery/banner2_468x60.jpg" alt="'. ingame32 .'"/></a>
				</textarea>
				<br>'. ingame35 .':<br>
					<img src="https://www.wichry-wojny.eu/banery/banner2_468x60.jpg" alt="'. ingame32 .'"/><br><br><br><br><br>
				<textarea rows="4" cols="85" readonly="readonly" style="font-size:10px;"><a href="https://www.wichry-wojny.eu/ref/'. $this -> account -> id .'" title="'. ingame32 .'">
				<img src="https://www.wichry-wojny.eu/banery/banner_300x300.jpg" alt="'. ingame32 .'"/></a></textarea>
				<br><br>'. ingame35 .':<br>
				<img src="https://www.wichry-wojny.eu/banery/banner_300x300.jpg" alt="'. ingame32 .'"/><br></p>';
				break;
			case 4:
				$revData['rev'] = '
				<p>
					Wystarczy, jeśli wykonasz przelew środków na dane:<br>
					PPHU Miccom Caser Michał Marcak<br> ul. Porzeczkowa 12<br> 43-140 Lędziny<br>
					nr. konta: mBank 90 1140 2004 0000 3902 5424 3547<br>
					w tytule podając odpowiednio:<br>
					<strong>220 sztuk złota w grze Wichry Wojny ( gracz '. $this -> account -> login .' )</strong> i wpłacając kwotę <strong>9pln</strong><br>
					<strong>550 sztuk złota w grze Wichry Wojny ( gracz '. $this -> account -> login .' )</strong> i wpłacając kwotę <strong>20pln</strong><br>
					<strong>1100 sztuk złota w grze Wichry Wojny ( gracz '. $this -> account -> login .' )</strong> i wpłacając kwotę <strong>39pln</strong><br>
					<strong>2200 sztuk złota w grze Wichry Wojny ( gracz '. $this -> account -> login .' )</strong> i wpłacając kwotę <strong>75pln</strong><br><br><br>
					Złoto zostanie dodane do twojego konta natychmiast po zaksięgowaniu pieniędzy na koncie firmy:)<br>
				</p>';
			break;
			case 5:
				$revData['rev'] = '
					W szybki sposób dzięki PayPal zasilisz swoje konto.<br>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="T2TDYNF83FYQL">
					<input type="hidden" name="item_name" value="Wirtualna waluta Premium">
					<input type="hidden" name="button_subtype" value="services">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="cn" value="Add special instructions to seller:">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="currency_code" value="PLN">
					<input type="hidden" name="rm" value="1">
					<input type="hidden" name="return" value="http://www.wichry-wojny.eu/accept_paypal.php">
					<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
					<table>
					<input type="hidden" name="on0" value="ILOŚĆ ZŁOTA">
					<div id="set1" class="platnosc_box">
						<div class="wartosc"><strong class="big_int">200</strong><br>sztuk<br>złota</div>
						<div class="kwota">9<br>PLN</div>
						<div class="skrzynia_200"></div>
						<div class="zakup"><input type="radio" name="os0" value="200 szt. złota" checked="checked"/></div>
					</div>
					<div id="set2" class="platnosc_box">
						<div class="procenty">10%<br> taniej!</div>
						<div class="wartosc"><strong class="big_int">500</strong><br>sztuk<br>złota</div>
						<div class="kwota">20<br><small style="text-decoration: line-through">22,5</small><br>PLN</div>
						<div class="skrzynia_600"></div>
						<div class="zakup"><input type="radio" name="os0" value="500 szt. złota"/></div>
					</div>
					<div id="set4" class="platnosc_box">
						<div class="procenty">13%<br> taniej!
						</div>
						<div class="wartosc"><strong class="big_int">1000</strong><br>sztuk<br>złota</div>
						<div class="kwota">39<br><small style="text-decoration: line-through">45</small><br>PLN</div>
						<div class="skrzynia_1000"></div>
						<div class="zakup"><input type="radio" name="os0" value="1000 szt. złota"/></div>
					</div>
					<div id="set6" class="platnosc_box">
						<div class="procenty">17%<br> taniej!</div>
						<div class="wartosc"><strong class="big_int">2000</strong><br>sztuk<br>złota</div>
						<div class="kwota">75<br><small style="text-decoration: line-through">90</small><br>PLN</div>
						<div class="skrzynia_2000"></div>
						<div class="zakup"><input type="radio" name="os0" value="2000 szt. złota"/></div>
					</div>
					<div id="przycisk_online">
					 Wybierz z powyższych opcji o ile złota chcesz zwiększyć swoje konto.<br>
					 <input type="hidden" name="currency_code" value="PLN">
					 <input type="hidden" name="option_select0" value="200 szt. złota">
					<input type="hidden" name="option_amount0" value="9.00">
					<input type="hidden" name="option_select1" value="500 szt. złota">
					<input type="hidden" name="option_amount1" value="20.00">
					<input type="hidden" name="option_select2" value="1000 szt. złota">
					<input type="hidden" name="option_amount2" value="39.00">
					<input type="hidden" name="option_select3" value="2000 szt. złota">
					<input type="hidden" name="option_amount3" value="75.00">
					<input type="hidden" name="option_index" value="0">
					<input type="image" src="https://www.paypalobjects.com/pl_PL/PL/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal — Płać wygodnie i bezpiecznie">
					<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
					</div>
					</form>';
			break;	
		}
		echo json_encode($revData);
	}


	private function getAuthorList($authorID)//pobiera listę graczy do bloku z wiadomościami
    {
        $messageBox['authorList']='<form name="form1"><div class="writeMessage" num="0">napisz wiadomość</div><input  type="button" id="send" value="'.mess_usun.'"/> <input type="checkbox" id="del_all"  value="'.mess_zaznacz_wszystkie.'" onclick="zaznacz()" />';
		$getAuthorMessage = $this -> db -> query('SELECT `m`.*, `a`.`login` FROM `message` AS `m` LEFT JOIN `players` AS `p` ON ( `p`.`id` = `m`.`playerID` OR `p`.`id` = `m`.`messageAuthorID` ) LEFT JOIN `accounts` AS `a` ON `a`.`id` = `p`.`accountID` WHERE ( ( `m`.`messageAuthorID` = '. $this -> account -> playerID .' AND `m`.`blockAMID`= 0 ) OR ( `m`.`playerID` = '. $this -> account -> playerID .' AND `m`.`blockPlayerID`= 0 ) ) GROUP BY `m`.`messageID` ORDER BY `m`.`dateOfDispatch` DESC');
		$i=0;
		$authID = '';
		$logID = '';
		$koperta ='';
		foreach ($getAuthorMessage as $singleAuthorMessage)
		{
			$getAuthorMessage1 = $this -> db -> query('SELECT `seeMessage` FROM `message` WHERE `playerID`='.$this -> account -> playerID.' AND `messageID`='.$singleAuthorMessage['messageID'].' ORDER BY id DESC ') -> fetch();
			if($getAuthorMessage1['seeMessage']=='no' )
            {
				$koperta='<div class="koperta">N</div>';
			}
            else
            {
				$koperta='';
			}
			
			if( $singleAuthorMessage['messageAuthorID'] == $this -> account -> playerID ){
				$authID = $singleAuthorMessage['playerID'];
			}else{
				$authID = $singleAuthorMessage['messageAuthorID'];
			}
			
			$login = $this -> db -> query('SELECT  `acc`.`login`  FROM `accounts` as `acc` LEFT JOIN `players` as `pl` ON `acc`.`id`=`pl`.`accountID` WHERE `pl`.`id`='. $authID .' ') -> fetch();
			
			$loginGracza =  $login['login'];
			if( $singleAuthorMessage['messageAuthorID'] == 32 ){
				$loginGracza = 'Generał Ciasteczko';
			}else if( $singleAuthorMessage['messageAuthorID'] == 33  ){
				$loginGracza =  'Generał Pączek';
			}
			
			$messageBox['authorList'].= '<div class="authorDialog" num="'.$singleAuthorMessage['messageID'].'"><div class="authorDialogBG"></div>'. $loginGracza .' '.$koperta.'</div><input type="checkbox" class="check" name="wiadomosc[]" value="w_'.$singleAuthorMessage['messageID'].'"/>';
			$i++;
		}
		$getAuthorOperation = $this -> db -> query('SELECT o.* FROM `operacje` AS `o` '
		.' LEFT JOIN `accounts` AS `a` ON `a`.`id` = `o`.`accountID` '
		.' LEFT JOIN `players` AS `p` ON ( `p`.`accountID` = `a`.`id` AND `o`.`accountID` = `a`.`id` ) '
		.' WHERE `a`.`id`='.$this -> account -> id.' AND `o`.`archiwum`=0 GROUP BY `o`.`accountID` ORDER BY `seen` DESC');
		$i1=0;
		foreach ($getAuthorOperation as $singleMessage)
		{
			$getAuthorMessage2 = $this -> db -> query('SELECT `seen` FROM `operacje` WHERE `accountID`='.$this -> account -> id.' AND `orderid`="'.$singleMessage['orderid'].'" ') -> fetch();
			if($getAuthorMessage2['seen'] == 0 ){
				$koperta='<div class="koperta">N</div>';
			}
			$messageBox['authorList'].= '<div class="authorAlert"><div class="authorAlertBG"></div>SYSTEM '.$koperta.'</div><input type="checkbox" class="check" name="wiadomosc[]" value="sm_'.$singleMessage['auto_id'].'"/>';
			$i1++;
		}
		
		$getBattleRaport = $this -> db -> query('SELECT `br`.* FROM `battleRaport` AS `br` '
		.' LEFT JOIN `players` AS `p` ON ( `p`.`id`=`br`.`playerID` ) '
		.' WHERE `br`.`playerID`='.$this -> account -> playerID.' ORDER BY `br`.`data` DESC');
		$i2=0;
		foreach ($getBattleRaport as $singleRaport)
		{
			if($singleRaport['seen'] == 0 )
			{
				$koperta='<div class="koperta">N</div>';
			}
			else
			{
				$koperta='';
			}
			$messageBox['authorList'].= '<div class="battleRaport" rapid="'.$singleRaport['id'].'"><div class="battleRaportBG"></div>'.bitwa.' '.$singleRaport['topic'].' '.$koperta.'</div><input type="checkbox" class="check" name="wiadomosc[]" value="r_'.$singleRaport['id'].'"/>';
			$i2++;
		}
		$messageBox['authorList'].= '</form>';
		
		if( $i == 0 AND $i1 == 0 AND $i2 == 0 ){
			$messageBox['authorList']='<div class="writeMessage" num="0">'.comp_account78.'</div><br>'.mess_no_mess;
			$messageBox['messageBody']=' ';
		}
		return $messageBox['authorList'];
	}
	private function getDialog($numerDialogu, $zadanie )//pobiera listę graczy do bloku z wiadomościami
    {
        switch($zadanie) {
            case '1'://pobranie dialogu
                if ($numerDialogu == 0)
                {//znak, że dialog to raporty gen. Pączka lub Gen. Ciasteczka
                    $messageHistoryquery = $this->db->query('SELECT * from `message` WHERE `messageID`=' . $numerDialogu . ' AND `playerID`='. $this -> account -> playerID .' ORDER BY `dateOfDispatch` DESC');
                    $messageBox['messageHistory'] = '';
                    $this -> db -> exec('UPDATE `message` SET `seeMessage`="see" WHERE `messageID`=' . $numerDialogu . ' AND `playerID` ='. $this -> account -> playerID .' ');
					foreach ($messageHistoryquery as $messageHistory)
                    {
                        $messageBox['messageHistory'] .= '<div class="callerWords">' . $messageHistory['contentMessage'] . '<span class="dateOfMessage">' . date('d/m/Y H:i:s', $messageHistory['dateOfDispatch']) . '</span></div>';
                    }
                }
                else
                {
                    $messageHistoryquery = $this->db->query('SELECT * from `message` WHERE `messageID`=' . $numerDialogu . ' AND ( ( `messageAuthorID`= '. $this->account->playerID  .' AND `blockAMID`= 0 ) OR ( `playerID`= '. $this->account->playerID  .' AND `blockPlayerID`= 0 ) ) ORDER BY `dateOfDispatch` DESC');
                    $messageBox['messageHistory'] = '';
                    foreach ($messageHistoryquery as $messageHistory) {
                        if ($messageHistory['messageAuthorID'] == $this->account->playerID) {//moja wiadomość
                            $messageBox['messageHistory'] .= '<div class="myWords">' . $messageHistory['contentMessage'] . '<span class="dateOfMessage">' . date('d/m/Y H:i:s', $messageHistory['dateOfDispatch']) . '</span></div>';
                        } else {
                            $messageBox['messageHistory'] .= '<div class="callerWords">' . $messageHistory['contentMessage'] . '<span class="dateOfMessage">' . date('d/m/Y H:i:s', $messageHistory['dateOfDispatch']) . '</span></div>';
                        }
						$this->db->exec('UPDATE `message` SET `seeMessage`="see" WHERE `messageID`=' . $numerDialogu . ' AND `playerID`='.$this->account->playerID.' AND `seeMessage`="no" ');
                    }
                }
			break;
			case '2'://pobranie wszystkich raportów systemowych
			$messageHistoryquery = $this -> db -> query('SELECT * from `operacje` WHERE `accountID`='.$this -> account -> id.' ORDER BY `data` DESC');
			$messageBox['messageHistory']='';
			foreach ($messageHistoryquery as $messageHistory)
			{
				$messageBox['messageHistory'].= '<div class="callerWords"> '.comp_account79.' '.$messageHistory['ile_dodano'].' '. comp_account69.' . <span class="dateOfMessage">'.date('d/m/Y H:i:s', $messageHistory['data']).'</span></div>';
			}
			$this -> db -> exec('UPDATE `operacje` SET `seen`=1 WHERE `accountID`='.$this -> account -> id.' ');	
			break;
		}
		return $messageBox['messageHistory'];
	}

    public function usunWiadomosci()
    {
        $message = array();
        $wiadomosc  = $this -> path -> post('data');
        //print_r($wiadomosc);
        $wywalic = array("wiadomosc%5B%5D","&");
        $messages = str_replace($wywalic, "", $wiadomosc);
        $rodzaj = explode('=', $messages[0]);
        $number = count( $rodzaj );
        $p1 = $rodzaj;
        for ($i = 1; $i < $number; $i++) {
            $wiadomosc = $p1[$i];
            $podzial = explode('_', $wiadomosc);
            if ($podzial[0] == 'r') {
                //echo 'usuwam raport o id ' . $podzial[1] . '<br>';
                $this->db->exec('DELETE FROM `battleRaport` WHERE `playerID`=' . $this->account->playerID . ' AND `id`=' . $podzial[1] . ' ');//usunięcie raportu bojowego
                $message['info'] = usun1 . ' ' . $podzial[1] . '<br>';
            } else if ($podzial[0] == 'w') {
                //echo 'usuwam wiadomość o id ' . $podzial[1] . '<br>';
                $this->db->exec('UPDATE `message` SET `blockPlayerID`=1, `seeMessage`="see" WHERE `playerID`=' . $this->account->playerID . ' AND `messageID`=' . $podzial[1] . ' ');//usunięcie prywatnej wiadomości
                $this->db->exec('UPDATE `message` SET `blockAMID`=1,`seeMessage`="see" WHERE `messageAuthorID`=' . $this->account->playerID . ' AND `messageID`=' . $podzial[1] . ' ');//usunięcie prywatnej wiadomości
                $message['info'] = usun1 . ' ' . $podzial[1] . '<br>';
            } else if ($podzial[0] == 'sm') {
                //echo 'usuwam wiadomość systemową o id ' . $podzial[1] . '<br>';
                $this->db->exec('UPDATE `operacje` SET `archiwum`=1 WHERE `accountID`=' . $this->account->id . ' ');//archiwizowanie wiadomości systemowych
                $message['info'] = comp_account80;
            }
            if ($i > 3) {
                $message['info'] = usun2 . '<br>';
            }
        }
        $message['authorList'] = $this -> getAuthorList(1333);
		$arrMess = $this -> db -> query('SELECT count(*) FROM `message` WHERE ( ( `playerID`='.$this -> account -> playerID.' AND `blockPlayerID`= 0 ) OR ( `messageAuthorID`='.$this -> account -> playerID.' AND `blockAMID`= 0 ) ) AND `seeMessage`="no" GROUP BY `messageID`  ')->fetch();
		//sprawdzenie, czy gracz ma jakieś raporty bojowe
		$arrRap = $this -> db -> query('SELECT count(*) FROM `battleRaport` WHERE `playerID`='.$this -> account -> playerID.' AND `seen`=0  ')->fetch();
		$message['mess'] = (int)$arrMess[0];
		$message['rap'] = (int)$arrRap[0];
        echo json_encode($message);
    }


    public function usun_konto()
    {
        $zaSiedemDni = strtotime("+7 day");
        $this -> db -> exec('UPDATE `players` SET `deleteAccount`='. $zaSiedemDni .' WHERE `id`=' . $this -> account -> playerID . ' ');//zapisanie daty usunięcia konta///
        $this -> db -> exec('UPDATE `accounts` SET `reminder`=1, `dateOfRemoval`='.$zaSiedemDni.' WHERE `id`='. $this -> account -> id .' LIMIT 1');
        $this -> error -> printError( comp_account81.' :)', 'komunikat');
    }

    public function loadImage()
    {
        $valid_exts = array('jpeg', 'jpg', 'png', 'gif');
        $max_file_size = 150 * 1024; #200kb
        $dataArr = array();
        /**
         * Generuje miniaturkę lub pomniejszony obrazek,
         * wysokość domyślnie ustawiona jest na 35 pikseli.
         * @param string $plik - ścieżka do pliku jpeg
         * @param string $szerokosc - szerokość pliku w px
         * @return boolean
         */
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_FILES['image'])){
                if (! $_FILES['image']['error'] && $_FILES['image']['size'] < $max_file_size) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $valid_exts)) {
                        $path = 'avatary/' . uniqid() . '.' . $ext;
                        $pathm = 'avatary/mini/' . uniqid() . '.' . $ext;
                        $size = getimagesize($_FILES['image']['tmp_name']);
                        $x =  $size[0];
                        $y =  $size[1];
                        $data = file_get_contents($_FILES['image']['tmp_name']);
                        $szerokosc = $size[0];//max szerokość pliku
                        $wysokosc  = $size[1];
                        if($size[0]>150)
                        {
                            $szerokosc = 150;
                            $proporcje = $size[1] / $size[0];
                            $wysokosc  = $szerokosc * $proporcje;
                        }
                        $vImg = imagecreatefromstring($data);
                        $dstImg = imagecreatetruecolor($szerokosc, $wysokosc);
                        imagecopyresampled($dstImg, $vImg, 0, 0, 0, 0, $szerokosc, $wysokosc, $x, $y);
                        imagejpeg($dstImg, $path);

                        // pomniejzamy grafikę dla wyświetlania na stronie głównej
                        $szerokoscMini = $size[0];//max szerokość pliku
                        $wysokoscMini  = $size[1];
                        if($size[0]>20)
                        {
                            $szerokoscMini = 20;
                            $proporcjeMini = $size[1] / $size[0];
                            $wysokoscMini  = $szerokoscMini * $proporcjeMini;
                        }
                        $vImgm = imagecreatefromstring($data);
                        $dstImgm = imagecreatetruecolor($szerokoscMini, $wysokoscMini);
                        imagecopyresampled($dstImgm, $vImgm, 0, 0, 0, 0, $szerokoscMini, $wysokoscMini, $x, $y);
                        imagejpeg($dstImgm, $pathm);
                        imagedestroy($dstImg);
                        imagedestroy($dstImgm);
                        $this -> db -> exec('UPDATE `accounts` SET `avatar`="'.$path.'", `mini`="'.$pathm.'" WHERE `id`=' . $this -> account -> id . ' ');
                        $dataArr['fota'] = $path;
                    }else{
                        $dataArr['fota'] = nie_tak;
                    }
                }else{
                    $dataArr['fota'] = maly_obrazek;
                }
            }else{
                $dataArr['fota'] = no_plik;
            }
        }
        echo json_encode($dataArr);
    }
    public function deleteImage()
    {
        $dataArr = array();
        //usuwanie zdjęcie przez gracza
        unlink($this -> account -> avatar );
        unlink($this -> account -> mini );
        $this -> db -> exec('UPDATE `accounts` SET `avatar`=null, `mini`=null WHERE `id`=' . $this -> account -> id . ' ');

        if ( $this -> account -> SponsorAccount >= $this -> init -> time)
        {
            $dataArr['info'] = '<strong id="info_upload"><br>' . ustawienia9 . '</strong>
                <form id="uploadimage" name="uploadimage" method="post" enctype="multipart/form-data">
                  <input id="uploadImage" type="file" accept="image/jpeg" name="image" required/>
                  <input type="submit"  onclick="uploadImage(); return false;" class="submit_fota" value=" '. zapisz.'"/>
                </form>';
        }
        else
        {
            $dataArr['info'] = '<img src="app/templates/assets/images/szwejk.gif"/>'.ustawienia10;
        }
        echo json_encode($dataArr);
    }

	public function settings()
    {
		$num  = $this -> path -> get('num');
		$data = array();
		$data['title'] = '<strong>'. hate_settings_no_active .'</strong>';
		switch($num){
			case 1:
				$data['bodyTable'] = '<div id="ustawienia_ogolne" class="ustawienia" num="1" style="color:#f6c770"><strong>'. hate_settings_no_active .'</strong> '. comp_account82 .'</div>
					<div id="ustawienia_dzwieki" class="ustawienia" num="2"><strong>'. hate_settings_no_active .'</strong> '.comp_account83.'</div>
					<div id="ustawienia_gry" class="ustawienia" num="3"><strong>'. hate_settings_no_active .'</strong> '.comp_account84.'</div>
					<div id="ustawienia_body1">
						<div id="miasto_edit">
							<form id="editCityName">';
								if($this -> account -> cityNum == 0 ) {
									$data['bodyTable'] .= ustawienia12;
								}else{
									if($this -> account -> SponsorAccount > time()  ){
										$data['bodyTable'] .= '<strong>'.ustawienia13.'</strong> : <input type="text" maxlength="20" name="form_miasto" id="form_miasto" value="'.$this -> account -> cityName.'" required/><div id="before_input_miasto"></div><br><br><div id="zapisz_miasto">'.zapisz_miasto.'</div><br>';
									}else{
										$data['bodyTable'] .= '<strong>'.ustawienia13.'</strong> : <input type="text" maxlength="20" name="form_miasto" id="form_miasto" value="'.$this -> account -> cityName.'" readonly="readonly"/><div id="before_input_miasto"></div><br>'.ustawienia21;
									}
								}
				$data['bodyTable'] .= '			
								<input type="hidden" name="formStage" class="formStage" value="6"/>
							</form>
						</div>
						<div id="pass_edit">
							<form id="password_edit">
								<strong>'.comp_account85.'</strong> : <input type="password" maxlength="20" name="old_form_pass" id="old_form_pass"/><div id="old_before_input_pass"></div><br>
								<strong>'.comp_account86.'</strong> : <input type="password" maxlength="20" name="form_pass" id="form_pass"/><div id="before_input_pass"></div><br>
								<strong>'.comp_account87.'</strong> : <input type="password" maxlength="20" name="form_pass1" id="form_pass1"/><div id="before_input_pass1"></div>
								<input type="hidden" name="formStage" class="formStage" value="1"/>
							</form>
							<div id="submit_pass">
								'.ustawienia22.'
							</div>
						</div>
						<header id="changeLangIngame">
							<div id="changeLang">
								<h2>'.comp_account88.'</h2>
								<div class="flagsOptions">';
									$active1 = '';
									$active2 = '';
									$active3 = '';
									$active4 = '';
									$active5 = '';
									$active6 = '';
									$active7 = '';
									switch($this -> account -> lang){
										case 'pl':
											$active1 = 'active';
										break;
										case 'cz':
											$active2 = 'active';
										break;
										case 'de':
											$active3 = 'active';
										break;
										case 'fr':
											$active4 = 'active';
										break;
										case 'ru':
											$active5 = 'active';
										break;
										case 'en':
											$active6 = 'active';
										break;
										case 'it':
											$active7 = 'active';
										break;
									}
				$data['bodyTable'] .='	
									<a href="jezyk/pl"><div class="flaga1 '.$active1.'" alt="" title="'.lang_polski.'"></div></a>
									<a href="jezyk/cz"><div class="flaga2 '.$active2.'" alt="" title="'.lang_czeski.'"></div></a>
									<a href="jezyk/de"><div class="flaga3 '.$active3.'" alt="" title="'.lang_niemiecki.'"></div></a>
									<a href="jezyk/fr"><div class="flaga4 '.$active4.'" alt="" title="'.lang_francuski.'"></div></a>
									<a href="jezyk/ru"><div class="flaga5 '.$active5.'" alt="" title="'.lang_rosyjski.'"></div></a>
									<a href="jezyk/en"><div class="flaga6 '.$active6.'" alt="" title="'.lang_angielski.'"></div></a>
									<a href="jezyk/it"><div class="flaga7 '.$active7.'" alt="" title="'.lang_wloski.'"></div></a>
								</div>
							</div>
						</header>
						<div id="delete_player">
							<h2>'. ustawienia49 .'</h2><br>
							'. ustawienia50a .'<br>
							<div id="usuwanie_link">'. ustawienia50b .'</div><br>
							<div id="usuwanie_div">'. ustawienia50c .'</div><br>
						</div>
					</div>';
			break;
			case 2:
				$data['bodyTable'] = '
					<div id="ustawienia_ogolne" class="ustawienia" num="1"><strong> '. hate_settings_no_active .'</strong> '.comp_account82.'</div>
					<div id="ustawienia_dzwieki" class="ustawienia" num="2" style="color:#f6c770"><strong>'. hate_settings_no_active .'</strong> '.comp_account83.'</div>
					<div id="ustawienia_gry" class="ustawienia" num="3"><strong>'. hate_settings_no_active .'</strong> '.comp_account84.'</div>
						<div id="ustawienia_body2">
							<div id="muza_edit">';
								if($this -> account -> sounds == 1){
									$data['bodyTable'] .= ustawienia14.'<span id="muza_txt">'.ustawienia15b.'</span><div id="muza">'.ustawienia15a.'</div>';
								}else{
									$data['bodyTable'] .= ustawienia14.'<span id="muza_txt">'.ustawienia15.'</span><div id="muza">'.ustawienia14a.'</div>';
								}
				$data['bodyTable'] .= '
							</div>
							<div id="zarowa_edit">';
							if($this -> account -> opticalAlert == 1){
								$opticalalert = ustawienia15b;
								$opticalalert2 = ustawienia15a;
							}else{
								$opticalalert = ustawienia15;
								$opticalalert2 = ustawienia14a;
							}
							if( $this -> account -> SponsorAccount > time() ){
								$data['bodyTable'] .= zarowa.'<span id="zarowa_txt">'.$opticalalert.'</span><div id="zarowa">'.$opticalalert2.'</div>'; 
							}else{
								$data['bodyTable'] .= comp_account89;
							}
				$data['bodyTable'] .= '</div>
						</div>
				';
			break;
			case 3:
				$data['bodyTable'] = '
					<div id="ustawienia_ogolne" class="ustawienia" num="1"><strong>'. hate_settings_no_active .'</strong> '.comp_account82.'</div>
					<div id="ustawienia_dzwieki" class="ustawienia" num="2"  ><strong>'. hate_settings_no_active .'</strong> '.comp_account83.'</div>
					<div id="ustawienia_gry" class="ustawienia" num="3" style="color:#f6c770"><strong>'. hate_settings_no_active .'</strong> '.comp_account84.'</div>
						<div id="ustawienia_body3">
							<div id="raporty_edit">
								<h2>'.raporty.'</h2>';							
								$checked1 = $checked2 = $checked3 = $checked4 = '';
								switch ($this -> account -> reports) {
									case '0':
										$checked1 = "checked";
									break;
									case '1':
										$checked2 = "checked";
									break;
									case '2':
										$checked3 = "checked";
									break;
									case '3':
										$checked4 = "checked";
									break;
								}
							
								if( $this -> account -> SponsorAccount > time() ){
									$data['bodyTable'] .= '
										<form id="reports_edit">
											<table>
												<tr>
													<td>
													'.ustawienia17.'
													</td>
													<td>
													<input type="radio" name="reports" value="0" '.$checked1.'/>
													</td>
													<td>
													</td>
												</tr>
												<tr>
													<td>
													'.ustawienia18.'
													</td>
													<td>
													<input type="radio" name="reports" value="1" '.$checked2.'/>
													</td>
													<td>
													</td>
												</tr>
												<tr>
													<td>
													'.ustawienia19.'
													</td>
													<td>
													<input type="radio" name="reports" value="2" '.$checked3.'/>
													</td>
													<td>
													</td>
												</tr>
												<tr>
													<td>
													'.ustawienia20.'
													</td>
													<td>
													<input type="radio" name="reports" value="3" '.$checked4.'/>
													</td>
													<td>
													<input type="hidden" name="formStage" class="formStage" value="2"/>
													<input type="button" class="zapisz_raporty" value="'.comp_account90.'"/>
													</td>
												</tr>
											</table>
										</form>';
								}else{
									if($this -> account -> cityNum == 0){
										$data['bodyTable'] .= ustawienia12;
									}else{
										$data['bodyTable'] .= comp_account91;
									}
								}
								
				//--------------------------- formularz dla turek ============================

				$formTurki = '';
					$tura_up = $this -> db -> query('SELECT `TurnUp` FROM `turnSetting` WHERE `playerID`=' . $this -> account -> playerID . ' LIMIT 1') -> fetch();
						if( $tura_up['TurnUp'] == 0){
							if ( $this -> world -> WarMode == 1 OR $this -> world -> WarMode == 4 ){
								if( $this -> account -> SponsorAccount > time() ){
									$formTurki .= ustawienia42.'<br>
									<form id="formularz_schemat">
										<fieldset>
											<div class="schema1">
												'.ustawienia43.'<br>
												<input type="radio" name="schem_1" value="2"/> 2<br>
												<input type="radio" name="schem_1" value="3"/> 3<br>
												<input type="radio" name="schem_1" value="4"/> 4<br>
												<input type="radio" name="schem_1" value="5"/> 5<br>
												<input type="radio" name="schem_1" value="6"/> 6<br>
											</div>
											<div class="schema1">
												'.ustawienia44.'<br>
												<input type="radio" name="schem_2" value="8"/> 8<br>
												<input type="radio" name="schem_2" value="9"/> 9<br>
												<input type="radio" name="schem_2" value="10"/> 10<br>
												<input type="radio" name="schem_2" value="11"/> 11<br>
												<input type="radio" name="schem_2" value="12"/> 12
											</div>
											<div class="schema1">
												'.ustawienia45.'<br>
												<input type="radio" name="schem_3" value="14"/> 14<br>
												<input type="radio" name="schem_3" value="15"/> 15<br>
												<input type="radio" name="schem_3" value="16"/> 16<br>
												<input type="radio" name="schem_3" value="17"/> 17<br>
												<input type="radio" name="schem_3" value="18"/> 18
											</div>
											<div class="schema1">
												'.ustawienia46.'<br>
												<input type="radio" name="schem_4" value="20"/> 20<br>
												<input type="radio" name="schem_4" value="21"/> 21<br>
												<input type="radio" name="schem_4" value="22"/> 22<br>
												<input type="radio" name="schem_4" value="23"/> 23<br>
												<input type="radio" name="schem_4" value="00"/> 00
											</div>
										</fieldset>
										<input type="hidden" name="formStage" class="formStage" value="3"/>
										<input id="zapisz_tury"  name="zapisz_tury" type="button" value="'.ustawienia48.'" />
									</form>';
									$data['height'] = 280;
								}else{
									$formTurki .= '<div id="tury_nos">
										'.ustawienia47.'
											<form id="formularz_schemat" >
												<input type="radio" value="1" name="schemat"/>2/8/14/20<br>
												<input type="radio" value="2" name="schemat"/>3/9/15/21<br>
												<input type="radio" value="3" name="schemat"/>4/10/16/22<br>
												<input type="radio" value="4" name="schemat"/>5/11/17/23<br>
												<input type="radio" value="5" name="schemat"/>6/12/18/00<br>
												<input type="hidden" name="formStage" class="formStage" value="4"/>
												<input id="zapisz_tury" name="zapisz_tury" type="button" value="'.ustawienia48.'" />
											</form>
									</div>';
									$data['height'] = 280;
								}
							}else if ($this -> world -> WarMode == 2  ){
								if( $this -> account -> SponsorAccount > time() ){
									$formTurki .= '<div id="tury_no_active">
										JUż niebawem będzie działać:)
									</div>';
								}else{
									$formTurki .= '<div id="tury_no_active">
										'.comp_account92.' '.comp_account93.' '.comp_account95.'
									</div>';
								}
							}else{
								if( $this -> account -> SponsorAccount > time() ){
									if($this -> world -> WarMode == 2){
										$formTurki .= '<div id="tury_no_active">
											Już wkrótce zacznie działać
										</div>';
									}else{
										$formTurki .= ustawienia42.'<br>
										<form id="formularz_schemat">
											<fieldset>
												<div id="first_schema" class="schema">
													'.ustawienia43.'<br>
													<input type="radio" name="schem_1" value="0"/> 0<br>
													<input type="radio" name="schem_1" value="1"/> 1<br>
													<input type="radio" name="schem_1" value="2"/> 2
												</div>
												<div id="second_schema" class="schema">
													'.ustawienia44.'<br>
													<input type="radio" name="schem_2" value="3"/> 3<br>
													<input type="radio" name="schem_2" value="4"/> 4<br>
													<input type="radio" name="schem_2" value="5"/> 5

												</div>
												<div id="three_schema" class="schema">
													'.ustawienia45.'<br>
													<input type="radio" name="schem_3" value="6"/> 6<br>
													<input type="radio" name="schem_3" value="7"/> 7<br>
													<input type="radio" name="schem_3" value="8"/> 8
												</div>
												<div id="four_schema" class="schema">
													'.ustawienia46.'<br>
													<input type="radio" name="schem_4" value="9"/> 9<br>
													<input type="radio" name="schem_4" value="10"/> 10<br>
													<input type="radio" name="schem_4" value="11"/> 11
												</div>
												<div id="five_schema" class="schema">
													'.ustawienia54.'<br>
													<input type="radio" name="schem_5" value="12"/> 12<br>
													<input type="radio" name="schem_5" value="13"/> 13<br>
													<input type="radio" name="schem_5" value="14"/> 14
												</div>
												<div id="six_schema" class="schema">
													'.ustawienia55.'<br>
													<input type="radio" name="schem_6" value="15"/> 15<br>
													<input type="radio" name="schem_6" value="16"/> 16<br>
													<input type="radio" name="schem_6" value="17"/> 17
												</div>
												<div id="seven_schema" class="schema">
													'.ustawienia56.'<br>
													<input type="radio" name="schem_7" value="18"/> 18<br>
													<input type="radio" name="schem_7" value="19"/> 18<br>
													<input type="radio" name="schem_7" value="20"/> 20
												</div>
												<div id="eight_schema" class="schema">
													'.ustawienia57.'<br>
													<input type="radio" name="schem_8" value="21"/> 21<br>
													<input type="radio" name="schem_8" value="22"/> 22<br>
													<input type="radio" name="schem_8" value="23"/> 23
												</div>
											</fieldset>
											<input type="hidden" name="formStage" class="formStage" value="3"/>
											<input id="zapisz_tury"  name="zapisz_tury" type="button" value="'.ustawienia48.'" />
										</form>';
										$data['height'] = 280;
									}
								}else{
									$formTurki .= '<div id="tury_no_active">
										'.comp_account92.' '.comp_account94.' '.comp_account95.'
									</div>';
								}
							}
						}else{
							$formTurki .= '<div id="tury_no_active">
								'.comp_account96.'
							</div>';
						}
					$formTurki .= '</div>';				
									
				$data['bodyTable'] .= '
					</form>
				</div>
				<div id="tury_edit">		
					<h2>'.ustawienia41.'</h2>
						<div id="turki_edit">
							'.$formTurki.'
						</div>
				<div id="urlop_edit">
					<h2>'.comp_account97.'<div id="info_urlop_on">?</div></h2>
				<div id="info_urlop">
				'.ustawienia25;
				if( $this -> account -> SponsorAccount > time() ){	
					$data['bodyTable'] .= ustawienia26;
				}else{
					$data['bodyTable'] .= ustawienia27;
				}
				$data['bodyTable'] .= ustawienia28.'</div>';
				if ($this -> account -> SponsorAccount < time() ){//brak sponsora
					$data['bodyTable'] .= '<div id="urlop_txt">';
					if ($this -> account -> playerVacation < time() AND $this -> account -> playerVacation!=0){
						$data['bodyTable'] .= urlop_po.' '.$this -> account -> playerVacation.' '.ustawienia37.' '.ustawienia36a;
					}else if ($this -> account -> playerVacation > time() ){
						$data['bodyTable'] .= urlop_nadal.' '.date("Y-m-d H:i:s", $this -> account -> playerVacation).' '.ustawienia36a;
					}
					
					if ( $this -> account -> playerVacation <= time() AND ( $this -> account -> VacationNum > 0 ) AND ( $this -> account -> VacationNum < 4 ) ) {
						$data['bodyTable'] .= ustawienia37.' '.ustawienia36a;
					}else if ($this -> account -> playerVacation <= time() AND $this -> account -> playerVacation != 0){
						$data['bodyTable'] .= ustawienia37;
					}else if ($this -> account -> playerVacation == 0){
						 
						if ($this -> account -> VacationNum > 0){
							$data['bodyTable'] .= ustawienia37.' '.ustawienia36a;
						}else if ($this -> account -> playerVacation <= time()  and $this -> account -> playerVacation != 0){
							$data['bodyTable'] .= ustawienia38.' '.ustawienia36a;
						}else{
							$data['bodyTable'] .= ustawienia39.'
							<div id="form_urlop">
							<form id="editUrlop">
							<select id="soflow-color1" name="soflow-color1">
							<option value="7">'.ustawienia32.'</option>
							<option value="14">'.ustawienia34.'</option>
							</select>
							<input type="hidden" name="formStage" class="formStage" value="5"/>
							<input class="zapisz_urlop" type="button" value="'.ustawienia35.'" />
							</form>
							</div>';
						}
					}
					$data['bodyTable'] .= '</div>';
				}else{
					if($this -> account -> VacationNum > 0){
						if ($this -> account -> playerVacation !=0){
							$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia29.' '.date("Y-m-d H:i:s", $this -> account -> playerVacation).'</div>';
						}
						if ($this -> account -> VacationNum > 1){
							$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia40.'</div>';
						}else if ($this -> account -> playerVacation <= time() AND $this -> account -> playerVacation > 0){
							$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia37.'</div>';
						}else if ($this -> account -> playerVacation <= time() AND $this -> account -> playerVacation != 0){
							$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia38.'</div>';
						}else{
							$data['bodyTable'] .= '
								<div id="form_urlop">
									<form id="editUrlop">
										<select id="soflow-color1" name="soflow-color1">
										<option value="7">'.ustawienia32.'</option>
										<option value="14">'.ustawienia34.'</option>
										</select>
										<input type="hidden" name="formStage" class="formStage" value="5"/>
										<input class="zapisz_urlop" type="button" value="'.ustawienia30.'" />
									</form>
								</div>';
						}
					}else if ($this -> account -> VacationNum == 0){
						$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia39.'</div>
						<div id="form_urlop">
						<form id="editUrlop">
						<select id="soflow-color1" name="soflow-color1">
						<option value="7">'.ustawienia32.'</option>
						<option value="14">'.ustawienia34.'</option>
						</select>
						<input type="hidden" name="formStage" class="formStage" value="5"/>
						<input class="zapisz_urlop" type="button" value="'.ustawienia35.'" />
						</form>
						</div>';
						if ($this -> account -> playerVacation <= time() AND $this -> account -> VacationNum > 0){
							$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia37.'</div>';
						}else if ($this -> account -> playerVacation <= time() AND $this -> account -> playerVacation !=0){
							$data['bodyTable'] .= '<div id="urlop_txt">'.ustawienia38.'</div>';
						}			
					}
				}
			break;
		}
	$data['sound'] = $this -> account -> sounds;
	echo json_encode($data);
}

	
	public function logoutWindow()
    {
		//ustalam, na jakich światach gra gracz aby pokazać w formularzu zmiany światów
        // Lista światów
        $dataArr = array();
        $dataArr['loadWindow'] = '<div class="srobka_left_up"></div>
					<div id="form_change_world">
                        <p>'.comp_account98.' </p>
						<form id="FormWorld">
						<select name="world-id" id="soflow-color">';
		$worldStats = $this -> db -> query('SELECT `worlds`.`name`, `worlds`.`id` FROM `worlds` JOIN `players` WHERE `worlds`.`id` = `players`.`worldID` AND `players`.`accountID`=' . $this -> account -> id . ' AND `worlds`.`endWar`= 0 ORDER BY `worlds`.`id` ASC');
		foreach ($worldStats as $world)
        {
            $selected = ' ';
            if ($world['id'] == $this -> session -> worldID)
            {
                $selected = ' selected="selected" ';
            }
            $dataArr['loadWindow'] .= '
									<option value="'.$world['id'].'" '.$selected.' >'.$world['name'].'</option>';
        }
		 $dataArr['loadWindow'] .= ' </select><input type="button" value="'.comp_account99.'" class="submit_zmiana"/>
                        </form>
                    </div>';
        $dataArr['loadWindow'] .='<br><a href="/wyloguj" id="logout">'.panel_logout.'</a><br>
		<div id="closeWyloguj" title="'.comp_account100.'"></div>
		<div class="srobka_right_down"></div>';
        echo json_encode($dataArr);
    }
	
	
	public function questionnaire(){
		// funkcja odpowiedzialna za budowę ankiety
		$data = [];
		$data['title'] = 'Wichrowa ankieta';
		$data['body'] = '
			<div id="questionnaire">
				Odpowiedz na kilka pytań a w podziękowaniu za odpowiedzi otrzymasz 200 sztuk złota.<br>
				<span class="question">
					<p>Pytanie pierwsze:</p>
					<label><input type="radio" name="qu1">Słabo</label>
					<label><input type="radio" name="qu1">Może być</label>
					<label><input type="radio" name="qu1">Fajnie</label>
					<label><input type="radio" name="qu1">Rewelacyjnie</label>
				</span>
				<span class="question">
					<p>Pytanie drugie:</p>
					<label><input type="radio" name="qu2">Słabo</label>
					<label><input type="radio" name="qu2">Może być</label>
					<label><input type="radio" name="qu2">Fajnie</label>
					<label><input type="radio" name="qu2">Rewelacyjnie</label>
				</span>
			</div>';
		echo json_encode($data);
	}
	
	
	public function tray()
    {
        $dane_json = [];
        $dane_json['title'] ='Twój zasobnik';
		$dane_json['height'] = 118;
		$rev = '<div id="trayBody">
			<table>
				<tr>
					<td>
						Rodzaj nagrody
					</td>
					<td>
						Czas trwania
					</td>
					<td>
						działanie
					</td>
				</tr>
		';
		// wyciagamy dane zasobnika
		$dane = $this->db->query('SELECT * FROM `prize` WHERE `playerID`=' . $this->account->playerID . ' AND `prizeUsed`= 0 ');
        if( $dane->rowCount() == 0 ){
			$rev = 'Nie masz nic w zasobniku';
		}else{
			foreach ( $dane as $singlePrize ) {
				switch( $singlePrize['prizeNumber'] ){
					case 1:
						$txt1 = 'Bon konta SPONSOR';
						$txt1Info = '<div class="usePrizeAcc" num="'. $singlePrize['id'] .'">użyj</div>';
					break;
					case 2:
						$txt1 = 'Bon skracający czas budowy jednostki';
						$txt1Info = 'użyj w fabrykach';
					break;
					case 3:
						$txt1 = 'Bon skracający czas badania';
						$txt1Info = 'użyj w mieście do przyspieszania badań';
					break;
					case 4:
						$txt1 = 'Bon skracający czas oczekiwania';
						$txt1Info = 'użyj wszędzie tam, gdzie zegarek odlicza czas :)';
					break;
					
				}
				
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
					case 86400:
						$txt2 = '1 dzień';
					break;
					case 604800:
						$txt2 = '7 dni';
					break;
					case 2592000:
						$txt2 = '30 dni';
					break;
					
				}
				
				$rev .= '<tr><td>'. $txt1 .'</td><td>'. $txt2 .'</td><td>'. $txt1Info .'</td></tr>';
				$dane_json['height'] += 40;
			}
			
			$rev .= '</table>
			</div>
			';
		}
		
		$dane_json['body'] = $rev;
        echo json_encode($dane_json);
    }
	
	
	public function usePrize()
    {
        $id = $this->path->post('id');
		$dane_json = [];
        $dane_json['title'] ='Twój zasobnik';
		$dane_json['height'] = 185;
		$rev = '<div id="trayBody">';
		// wyciagamy dane zasobnika
		$dane = $this->db->query('SELECT * FROM `prize` WHERE `id`=' . $id . ' ');
        if( $dane->rowCount() == 0 ){
			$dane_json['error'] = 'Nie masz takiego przedmiotu w zasobniku';
		}else{
			$singlePrize = $dane->fetch();
			switch( $singlePrize['prizeNumber'] ){
				case 1:
					// bon na konto SPONSOR
					if ($this -> account -> SponsorAccount >= $this -> init -> time ){
						// dodajemy do sponosra gracza odpowiedni czas
						$this -> account -> SponsorAccount = $this -> account -> SponsorAccount + $singlePrize['prizeTime'];
					}else{
						$this -> account -> SponsorAccount = $this -> init -> time + $singlePrize['prizeTime'];
					}
					// zapisujemy do bazy nowy czas sponsor
					$this -> db -> exec('UPDATE `accounts` SET `SponsorAccount`=' . $this -> account -> SponsorAccount . ' WHERE `id`=' . $this -> account -> id . ' LIMIT 1 ');	
					$this -> db -> exec('UPDATE `prize` SET `prizeUsed`=1 WHERE `id`=' . $id . ' LIMIT 1 ');	
					
					$rev .= comp_account32.' ' . date("Y-m-d H:i:s", $this -> account -> SponsorAccount ) . ', '.comp_account33;
					$dane_json['newTime'] = $this -> account -> SponsorAccount;
				break;
				case 2:
					// bon skracajacy czas budowy jednostki
					$txt1 = 'Bon skracający czas budowy jednostki';
				break;
			}
			
			
			$rev .= '</div>';
		}
		
		$dane_json['body'] = $rev;
        echo json_encode($dane_json);
    }
	
	
	
	
	
	
	
	
	
	
    private function sprawdz_order($order, $punkty, $editProfil )
    {
        $punkty = (int)$punkty;
        if ($editProfil == 1 )
        {
            if ($punkty >= 50 AND $punkty < 100)
            {
                $info_range = profil59.' ' . $punkty . ' '.comp_account21.'. <meter min="50" value="' . $punkty . '" max="100" title=" '.profil59.' ' . $punkty . ' '.comp_account21.'. "></meter>';
            }
            else if ($punkty >= 100 AND $punkty < 150)
            {
                $info_range = profil59.' ' . $punkty . ' '.comp_account21.'. <meter min="100" value="' . $punkty . '" max="150" title=" '.profil59.' ' . $punkty . ' '.comp_account21.'. "></meter>';
            }
            else if ($punkty >= 150)
            {
                $info_range = profil59.' ' . $punkty . ' '.comp_account21.'. <meter value="' . $punkty . '" max="150" title=" '.profil59.' ' . $punkty . ' '.comp_account21.'. "></meter>';
            }
            else
            {
                $info_range = profil59.' ' . $punkty . ' '.comp_account21.'. <meter value="' . $punkty . '" max="5000" title=" '.profil59.' ' . $punkty . ' '.comp_account21.'. "></meter>';
            }
        }
        else
        {
            $info_range = comp_account101.' '.(int)$punkty.' '.comp_account102;
        }

        switch ($order) {
            case '1':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div  style="background: url(app/templates/assets/images/ordery.png) 0px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_1\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_1\').style.display = \'none\' ;" ><div id="opis_1" class="order_niem"><img src="app/templates/assets/images/ordery/b_wehrmacht.png"/><div id="opis_order">'.profil12.' <strong>'.profil18.' '.profil13.'</strong> '.comp_account107.' 50 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_1\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_1\').style.display = \'none\' ;" ><div id="opis_1" class="order_niem"><img src="app/templates/assets/images/ordery/s_wehrmacht.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil13.'</strong> '.comp_account107.' 100 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_1\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_1\').style.display = \'none\' ;" ><div id="opis_1" class="order_niem"><img src="app/templates/assets/images/ordery/z_wehrmacht.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil13.'</strong> '.comp_account107.' 150 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_1\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_1\').style.display = \'none\' ;" ><div id="opis_1" class="order_niem"><img src="app/templates/assets/images/ordery/no_wehrmacht.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil13.'</strong> '.comp_account108.' 50 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '2':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) 0px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_2\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_2\').style.display = \'none\' ;" ><div id="opis_2" class="order_niem"><img src="app/templates/assets/images/ordery/b_luftwaffeb.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil25.'</strong> '.profil23.' '.comp_account107.' 50 '.comp_account102.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_2\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_2\').style.display = \'none\' ;" ><div id="opis_2" class="order_niem"><img src="app/templates/assets/images/ordery/s_luftwaffeb.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil25.'</strong> '.profil23.' '.comp_account107.' 100 '.comp_account102.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_2\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_2\').style.display = \'none\' ;" ><div id="opis_2" class="order_niem"><img src="app/templates/assets/images/ordery/z_luftwaffeb.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil25.'</strong> '.profil23.' '.comp_account107.' 150 '.comp_account102.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_2\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_2\').style.display = \'none\' ;" ><div id="opis_2" class="order_niem"><img src="app/templates/assets/images/ordery/no_luftwaffeb.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil25.'</strong> '.comp_account108.' 50 '.comp_account102.' '.comp_account113.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '3':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) 0px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_3\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_3\').style.display = \'none\' ;" ><div id="opis_3" class="order_niem"><img src="app/templates/assets/images/ordery/b_luftwaffe.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil26.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_3\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_3\').style.display = \'none\' ;" ><div id="opis_3" class="order_niem"><img src="app/templates/assets/images/ordery/s_luftwaffe.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil26.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_3\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_3\').style.display = \'none\' ;" ><div id="opis_3" class="order_niem"><img src="app/templates/assets/images/ordery/z_luftwaffe.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil26.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_3\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_3\').style.display = \'none\' ;" ><div id="opis_3" class="order_niem"><img src="app/templates/assets/images/ordery/no_luftwaffe.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil26.'</strong>, '.comp_account108.' 50 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '4':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) 0px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_4\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_4\').style.display = \'none\' ;" ><div id="opis_4" class="order_niem"><img src="app/templates/assets/images/ordery/b_kriegsmarine.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil28.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_4\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_4\').style.display = \'none\' ;" ><div id="opis_4" class="order_niem"><img src="app/templates/assets/images/ordery/s_kriegsmarine.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil28.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_4\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_4\').style.display = \'none\' ;" ><div id="opis_4" class="order_niem"><img src="app/templates/assets/images/ordery/z_kriegsmarine.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.''.profil28.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_4\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_4\').style.display = \'none\' ;" ><div id="opis_4" class="order_niem"><img src="app/templates/assets/images/ordery/no_kriegsmarine.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil28.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '5':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) 0px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_5\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_5\').style.display = \'none\' ;" ><div id="opis_5" class="order_niem"><img src="app/templates/assets/images/ordery/b_uboot.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil30.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_5\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_5\').style.display = \'none\' ;" ><div id="opis_5" class="order_niem"><img src="app/templates/assets/images/ordery/s_uboot.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil30.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_5\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_5\').style.display = \'none\' ;" ><div id="opis_5" class="order_niem"><img src="app/templates/assets/images/ordery/z_uboot.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil30.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_5\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_5\').style.display = \'none\' ;" ><div id="opis_5" class="order_niem"><img src="app/templates/assets/images/ordery/no_uboot.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil30.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '6':
                if ($punkty >= 50  AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) 0px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_6\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_6\').style.display = \'none\' ;" ><div id="opis_6" class="order_niem"><img src="app/templates/assets/images/ordery/b_flak.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil32.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_6\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_6\').style.display = \'none\' ;" ><div id="opis_6" class="order_niem"><img src="app/templates/assets/images/ordery/s_flak.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil32.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_6\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_6\').style.display = \'none\' ;" ><div id="opis_6" class="order_niem"><img src="app/templates/assets/images/ordery/z_flak.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil32.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_6\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_6\').style.display = \'none\' ;" ><div id="opis_6" class="order_niem"><img src="app/templates/assets/images/ordery/no_flak.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil32.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '7':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_7\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_7\').style.display = \'none\' ;" ><div id="opis_7" class="order_pol"><img src="app/templates/assets/images/ordery/b_piech.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.comp_account103.'</strong> '.comp_account107.' 50 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_7\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_7\').style.display = \'none\' ;" ><div id="opis_7" class="order_pol"><img src="app/templates/assets/images/ordery/s_piech.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.comp_account103.'</strong> '.comp_account107.'100 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_7\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_7\').style.display = \'none\' ;" ><div id="opis_7" class="order_pol"><img src="app/templates/assets/images/ordery/z_piech.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.comp_account103.'</strong> '.comp_account107.' 150 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -400px 0px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_7\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_7\').style.display = \'none\' ;" ><div id="opis_7" class="order_pol"><img src="app/templates/assets/images/ordery/no_piech.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.comp_account103.'</strong> '.comp_account108.' 50 '.comp_account102.' '.comp_account112.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '8':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_8\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_8\').style.display = \'none\' ;" ><div id="opis_8" class="order_pol"><img src="app/templates/assets/images/ordery/b_bomb.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil34.'</strong>'.comp_account107.' 50 '.comp_account102.' '.profil23.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_8\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_8\').style.display = \'none\' ;" ><div id="opis_8" class="order_pol"><img src="app/templates/assets/images/ordery/s_bomb.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil34.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil23.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_8\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_8\').style.display = \'none\' ;" ><div id="opis_8" class="order_pol"><img src="app/templates/assets/images/ordery/z_bomb.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil34.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil23.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -150px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_8\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_8\').style.display = \'none\' ;" ><div id="opis_8" class="order_pol"><img src="app/templates/assets/images/ordery/no_bomb.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil34.'</strong> '.comp_account108.' 50 '.comp_account102.' '.comp_account113.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '9':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_9\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_9\').style.display = \'none\' ;" ><div id="opis_9" class="order_pol"><img src="app/templates/assets/images/ordery/b_lot.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil37.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_9\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_9\').style.display = \'none\' ;" ><div id="opis_9" class="order_pol"><img src="app/templates/assets/images/ordery/s_lot.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil37.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_9\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_9\').style.display = \'none\' ;" ><div id="opis_9" class="order_pol"><img src="app/templates/assets/images/ordery/z_lot.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil37.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -400px -200px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_9\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_9\').style.display = \'none\' ;" ><div id="opis_9" class="order_pol"><img src="app/templates/assets/images/ordery/no_lot.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil37.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil27.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '10':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_10\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_10\').style.display = \'none\' ;" ><div id="opis_10" class="order_pol"><img src="app/templates/assets/images/ordery/b_mw.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil38.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_10\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_10\').style.display = \'none\' ;" ><div id="opis_10" class="order_pol"><img src="app/templates/assets/images/ordery/s_mw.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil38.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_10\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_10\').style.display = \'none\' ;" ><div id="opis_10" class="order_pol"><img src="app/templates/assets/images/ordery/z_mw.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil38.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -400px -250px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_10\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_10\').style.display = \'none\' ;" ><div id="opis_10" class="order_pol"><img src="app/templates/assets/images/ordery/no_mw.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil38.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil39.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '11':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_11\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_11\').style.display = \'none\' ;" ><div id="opis_11" class="order_pol"><img src="app/templates/assets/images/ordery/b_pod.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil43.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_11\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_11\').style.display = \'none\' ;" ><div id="opis_11" class="order_pol"><img src="app/templates/assets/images/ordery/s_pod.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil43.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_11\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_11\').style.display = \'none\' ;" ><div id="opis_11" class="order_pol"><img src="app/templates/assets/images/ordery/z_pod.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil43.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -400px -100px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_11\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_11\').style.display = \'none\' ;" ><div id="opis_11" class="order_pol"><img src="app/templates/assets/images/ordery/no_pod.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil43.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil31.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '12':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_12\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_12\').style.display = \'none\' ;" ><div id="opis_12" class="order_pol"><img src="app/templates/assets/images/ordery/b_oplot.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil44.'</strong> '.comp_account107.' 50 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_12\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_12\').style.display = \'none\' ;" ><div id="opis_12" class="order_pol"><img src="app/templates/assets/images/ordery/s_oplot.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil44.'</strong> '.comp_account107.' 100 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_12\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_12\').style.display = \'none\' ;" ><div id="opis_12" class="order_pol"><img src="app/templates/assets/images/ordery/z_oplot.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil44.'</strong> '.comp_account107.' 150 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -400px -300px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_12\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_12\').style.display = \'none\' ;" ><div id="opis_12" class="order_pol"><img src="app/templates/assets/images/ordery/no_oplot.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil44.'</strong> '.comp_account108.' 50 '.comp_account102.' '.profil33.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '14':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -250px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_13\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_13\').style.display = \'none\' ;" ><div id="opis_13" class="order_pol"><img src="app/templates/assets/images/ordery/b_panc.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil45.'</strong> '.comp_account107.' 50 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -300px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_13\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_13\').style.display = \'none\' ;" ><div id="opis_13" class="order_pol"><img src="app/templates/assets/images/ordery/s_panc.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil45.'</strong> '.comp_account107.' 100 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -350px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_13\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_13\').style.display = \'none\' ;" ><div id="opis_13" class="order_pol"><img src="app/templates/assets/images/ordery/z_panc.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil45.'</strong> '.comp_account107.' 150 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -400px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_13\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_13\').style.display = \'none\' ;" ><div id="opis_13" class="order_pol"><img src="app/templates/assets/images/ordery/no_panc.png"/><div class="opis_order">Aby zdobyć <strong>'.profil18.' '.profil45.'</strong> '.comp_account108.' 50 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account111.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            case '13':
                if ($punkty >= 50 AND $punkty < 100)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) 0px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_14\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_14\').style.display = \'none\' ;" ><div id="opis_14" class="order_niem"><img src="app/templates/assets/images/ordery/b_niem_panc25.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil47.'</strong> '.comp_account107.' 50 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 100 AND $punkty < 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -50px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_14\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_14\').style.display = \'none\' ;" ><div id="opis_14" class="order_niem"><img src="app/templates/assets/images/ordery/s_niem_panc25.png"/><div class="opis_order">'.profil12.' <strong>'.profil19.' '.profil47.'</strong> '.comp_account107.' 100 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else if ($punkty >= 150)
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -100px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_14\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_14\').style.display = \'none\' ;" ><div id="opis_14" class="order_niem"><img src="app/templates/assets/images/ordery/z_niem_panc25.png"/><div class="opis_order">'.profil12.' <strong>'.profil20.' '.profil47.'</strong> '.comp_account107.' 150 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                else
                {
                    $img = '<div style="background: url(app/templates/assets/images/ordery.png) -150px -50px no-repeat; width:50px; height:50px;" onmouseover="document.getElementById(\'opis_14\').style.display = \'block\' ;" onmouseout="document.getElementById(\'opis_14\').style.display = \'none\' ;" ><div id="opis_14" class="order_niem"><img src="app/templates/assets/images/ordery/no_niem_panc25.png"/><div class="opis_order">'.profil12.' <strong>'.profil18.' '.profil47.'</strong> '.comp_account108.' 50 '.comp_account102.' '.comp_account114.'. '.comp_account109.' '.comp_account110.'.<br> ' . $info_range . '</div></div></div>';
                }
                break;
            default:
        }
        return $img;
    }


}

?>