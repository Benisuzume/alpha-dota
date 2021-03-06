<?php

if (!isset($website) ) { header('HTTP/1.1 404 Not Found'); die; }

     $uid = safeEscape( (int) $_GET["u"] );

	$sth = $db->prepare("SELECT s.*, b.name as banname, b.reason, b.admin as isAdmin, b.date as bandate
	FROM ".OSDB_STATS." as s  
	LEFT JOIN ".OSDB_BANS." as b ON (b.name) = (s.player)
	WHERE s.id = :user_id LIMIT 1");
	
	$sth->bindValue(':user_id', (int)$uid, PDO::PARAM_INT); 
		 
	$result = $sth->execute();
	
	if ( $sth->rowCount()<=0 ) { 
    require_once(OS_PLUGINS_DIR.'index.php');
    os_init();
	header('location: '.OS_HOME.'?404'); die; 
	
	}
	
	$c=0;
    $UserData = array();
	
	 if ( file_exists("inc/geoip/geoip.inc") ) {
	 include("inc/geoip/geoip.inc");
	 $GeoIPDatabase = geoip_open("inc/geoip/GeoIP.dat", GEOIP_STANDARD);
	 $GeoIP = 1;
	 }
	
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {

	if ( isset($GeoIP) AND $GeoIP == 1) {
	$UserData[$c]["letter"]   = geoip_country_code_by_addr($GeoIPDatabase, $row["ip"]);
	$UserData[$c]["country"]  = geoip_country_name_by_addr($GeoIPDatabase, $row["ip"]);
	}
	if ($GeoIP == 1 AND empty($UserData[$c]["letter"]) ) {
		if( strlen($row["realm"]) <= 2) {
			$UserData[$c]["letter"] = "GAR";
			$UserData[$c]["country"] = "Garena";
		} else {
			if( strtolower($row["realm"]) == "europe.battle.net" ) {
        	                $UserData[$c]["letter"] = "EU";
		                $UserData[$c]["country"] = "Europe";
			}
                        else if( strtolower($row["realm"]) == "uswest.battle.net" OR strtolower($row["realm"]) == "useast.battle.net" ) {
                                $UserData[$c]["letter"] = "US";
                                $UserData[$c]["country"] = "USA";
                        }
                        else if( strtolower($row["realm"]) == "asia.battle.net" ) {
                                $UserData[$c]["letter"] = "CN";
                                $UserData[$c]["country"] = "Asia";
                        } else {
                                $UserData[$c]["letter"] = "A1";
                                $UserData[$c]["country"] = "Unknown";
                        }
		}
	}
	
	$UserData[$c]["id"]        = (int)($row["id"]);
	$UserData[$c]["player"]   = ($row["player"]);
	$PlayerName = $UserData[$c]["player"];
	$UserData[$c]["banname"]  = ($row["banname"]);
	$UserData[$c]["bandate"]  = date($DateFormat, strtotime($row["bandate"]));
	$UserData[$c]["bandate_raw"]  = ($row["bandate"]);
	$UserData[$c]["reason"]  = ($row["reason"]);
	$UserData[$c]["admin"]  = ($row["isAdmin"]);
	$UserData[$c]["score"]  = number_format($row["score"],0);
	$UserData[$c]["games"]  = number_format($row["games"],0);
	$UserData[$c]["wins"]  = number_format($row["wins"],0);
        $UserData[$c]["win"]  = number_format($row["wins"],0);
	$UserData[$c]["losses"]  = number_format($row["losses"],0);
	$UserData[$c]["draw"]  = number_format($row["draw"],0);
	$UserData[$c]["kills"]  = number_format($row["kills"],0);
	$UserData[$c]["deaths"]  = number_format($row["deaths"],0);
	$UserData[$c]["assists"]  = number_format($row["assists"],0);
	$UserData[$c]["creeps"]  = number_format($row["creeps"],0);
	$UserData[$c]["denies"]  = number_format($row["denies"],0);
	$UserData[$c]["neutrals"]  = number_format($row["neutrals"],0);
	$UserData[$c]["towers"]  = ($row["towers"]);
	$UserData[$c]["rax"]  = ($row["rax"]);
	$UserData[$c]["banned"]  = ($row["banned"]);
	$UserData[$c]["warn_expire"]  = ($row["warn_expire"]);
	$UserData[$c]["warn"]  = ($row["warn"]);
	$UserData[$c]["GameAdmin"]  = ($row["admin"]);
	$UserData[$c]["safelist"]  = ($row["safelist"]);
	$UserData[$c]["ip"]  = ($row["ip"]);
	$UserData[$c]["streak"]  = ($row["streak"]);
	$UserData[$c]["maxstreak"]  = ($row["maxstreak"]);
	$UserData[$c]["losingstreak"]  = ($row["losingstreak"]);
	$UserData[$c]["maxlosingstreak"]  = ($row["maxlosingstreak"]);
	$UserData[$c]["zerodeaths"]  = ($row["zerodeaths"]);
        $UserData[$c]["best_player"]  = ($row["best_player"]);
        $UserData[$c]["double_score"]  = ($row["double_score"]);
        $UserData[$c]["dc_count"]  = ($row["dc_count"]);
	
        if( strlen($row["realm"]) <= 2 ) {
		$UserData[$c]["realm"] = "Garena";
	} else {
		$UserData[$c]["realm"]  = ($row["realm"]);
	}
	if( !isset($UserData[$c]["realm"]) AND empty($UserData[$c]["realm"]) ) $UserData[$c]["realm"] = "Garena";
	//$UserData[$c]["avg_loading"]  = millisecondsToTime( $row["loading"]/$row["games"] );
	//$UserData[$c]["loading"]  = millisecondsToTime( $row["loading"]);
	$UserData[$c]["reserved"]  = ($row["reserved"]);
	
	$UserData[$c]["leaver"]  = ($row["leaver"]);
	if ($row["games"] >0 )
	$UserData[$c]["stayratio"] = round($row["games"]/($row["games"]+$row["leaver"]), 3)*100;
	else $UserData[$c]["stayratio"] = 0;
	
	//SET META INFORMATION AND PAGE NAME
	 $HomeTitle = ($row["player"]);
	 $HomeDesc = ($row["player"]);
	 $HomeKeywords = strtolower($row["player"]).','.$HomeKeywords;
	
	if ($row["games"]>=1 AND $row["kills"]>=1) {
	$UserData[$c]["kpg"] = round($row["kills"]/$row["games"],2); 
	}
	else $UserData[$c]["kpg"] = 0;
	
	if ($row["games"]>=1 AND $row["deaths"]>=1) {
	$UserData[$c]["dpg"] = round($row["deaths"]/$row["games"],2); 
	}
	else $UserData[$c]["dpg"] = 0;
	
	if ($row["deaths"]>=1) $UserData[$c]["kd"]  = round($row["kills"] / $row["deaths"],2);
    else $UserData[$c]["kd"] = $row["kills"];
	
	if ($row["wins"] >0 )
	$UserData[$c]["winslosses"] = round($row["wins"]/($row["wins"]+$row["losses"]), 3)*100;
	else $UserData[$c]["winslosses"] = 0;
	
	//AVG assists
	if ($row["games"]>=1 AND $row["assists"]>=1) {
	$UserData[$c]["apg"] = round($row["assists"]/$row["games"],2); 
	}
	else $UserData[$c]["apg"] = 0;
	
	//AVG creeps per game
	if ($row["games"]>=1 AND $row["creeps"]>=1) {
	$UserData[$c]["ckpg"] = ROUND($row["creeps"]/$row["games"],2); 
	}
	else $UserData[$c]["ckpg"] = 0;

	//AVG denies per game
	if ($row["games"]>=1 AND $row["denies"]>=1) {
	$UserData[$c]["cdpg"] = ROUND($row["denies"]/$row["games"],2); 
	}
	else $UserData[$c]["cdpg"] = 0;

        $IP = $UserData[$c]["ip"];
	$c++;
	}
	if ( isset($GeoIP) AND $GeoIP == 1) geoip_close($GeoIPDatabase);

    //CHECK ALL IPS
        $allips = array();
	$allipsquery = $db->prepare("SELECT * FROM `gameplayers` WHERE LOWER(name) = LOWER('".$PlayerName."') GROUP BY `ip` ORDER BY `id`;");
	$result = $allipsquery->execute();
        $numallips = $allipsquery->rowCount();
        while ($row = $allipsquery->fetch(PDO::FETCH_ASSOC)) {
		$allips[$c]["ip"] = $row["ip"];
		$c++;
	}

    //BAN QUERY
        $bans = array();
        $bansquery = $db->prepare("SELECT * FROM `bans` WHERE LOWER(name) LIKE LOWER('".$PlayerName."') ORDER BY date DESC;");
        $result = $bansquery->execute();
        $numbans = $bansquery->rowCount();
        while ($row = $bansquery->fetch(PDO::FETCH_ASSOC)) {
                $bans[$c]["name"] = $row["name"];
                $bans[$c]["server"] = $row["server"];
                $bans[$c]["ip"] = $row["ip"];
                $bans[$c]["date"] = $row["date"];
                $bans[$c]["gamename"] = $row["gamename"];
                $bans[$c]["admin"] = $row["admin"];
                $bans[$c]["reason"] = $row["reason"];
                $bans[$c]["expiredate"] = $row["expiredate"];
                $bans[$c]["warn"] = $row["warn"];
            $c++;
        }

        if ( isset($_GET["select"]) ) {
                $IP = $_GET["select"];
        }

    //IP BAN QUERY
        $ipbans = array();
        $ipbansquery = $db->prepare("SELECT * FROM `bans` WHERE ip LIKE '".$IP."' AND name NOT LIKE LOWER('".$PlayerName."') ORDER BY date DESC;");
        $result = $ipbansquery->execute();
        $numipbans = $ipbansquery->rowCount();
        while ($row = $ipbansquery->fetch(PDO::FETCH_ASSOC)) {
                $ipbans[$c]["name"] = $row["name"];
                $ipbans[$c]["server"] = $row["server"];
                $ipbans[$c]["ip"] = $row["ip"];
                $ipbans[$c]["date"] = $row["date"];
                $ipbans[$c]["gamename"] = $row["gamename"];
                $ipbans[$c]["admin"] = $row["admin"];
                $ipbans[$c]["reason"] = $row["reason"];
                $ipbans[$c]["expiredate"] = $row["expiredate"];
                $ipbans[$c]["warn"] = $row["warn"];
           $c++;
        }

    //IP RANGE ACCOUNTS & IP RANGE BANS
	if( isset($IP) AND !empty($IP) ) {
		$macqu = array();
		$bacqu = array();
		$ips = explode(".", $IP );
		$mac = $db->prepare("SELECT `player`, `games`, `ip` FROM `stats` WHERE ip LIKE '".$ips[0].".".$ips[1].".%' GROUP BY `player` ORDER BY games DESC;");
		$result = $mac->execute();
                $nummac = $mac->rowCount();
		while ($row = $mac->fetch(PDO::FETCH_ASSOC)) {
			$macqu[$c]["player"] = $row["player"];
			$macqu[$c]["ip"] = $row["ip"];
			$macqu[$c]["games"] = $row["games"];
		    $c++;
		}
		$bac = $db->prepare("SELECT `name`, `ip`, COUNT(*) FROM `bans` WHERE ip LIKE '".$ips[0].".".$ips[1].".%' AND `warn` != 1 GROUP BY `name` ORDER BY COUNT(*) DESC;");
	        $result = $bac->execute();
                $numbac = $bac->rowCount();
		while ($row = $row = $bac->fetch(PDO::FETCH_ASSOC)) {
			$bacqu[$c]["name"] = $row["name"];
			$bacqu[$c]["COUNT(*)"] = $row["COUNT(*)"];
			$bacqu[$c]["ip"] = $row["ip"];
		    $c++;
		}
	}

    //LONGEST, FASTEST GAME WON
	if ( !empty($PlayerName) ) {
	
	   if ($ShowLongFastGameWon==1) {
	   $LongGame      = $db->prepare( longGameWon     ( strtolower($PlayerName) )    );
	   $result = $LongGame->execute();
	   $lg = $LongGame->fetch(PDO::FETCH_ASSOC);
	   
	   $FastGame      = $db->prepare( fastGameWon     ( strtolower($PlayerName) )    );
	   $result = $FastGame->execute();
	   $fg = $FastGame->fetch(PDO::FETCH_ASSOC);
	   
	   $GamesDuration = $db->prepare( GetGameDurations( strtolower($PlayerName) )    );
	   $result = $GamesDuration->execute();
	   $dg = $GamesDuration->fetch(PDO::FETCH_ASSOC);
	   
	   $LongestGame = array();
	   $FastestGame = array();
	   $Durations   = array();
	   
	   $LongestGame["gameid"] = ($lg["gameid"]);
	   $LongestGame["gamename"] = ($lg["gamename"]);
	   $LongestGame["duration"] = secondsToTime($lg["duration"]);
	   $LongestGame["kills"] = ($lg["kills"]);
	   $LongestGame["deaths"] = ($lg["deaths"]);
	   $LongestGame["assists"] = ($lg["assists"]);
	   $LongestGame["creepkills"] = ($lg["creepkills"]);
	   $LongestGame["creepdenies"] = ($lg["creepdenies"]);
	   $LongestGame["neutralkills"] = ($lg["neutralkills"]);
	   $LongestGame["newcolour"] = ($lg["newcolour"]);
	   
	   $FastestGame["gameid"] = ($fg["gameid"]);
	   $FastestGame["gamename"] = ($fg["gamename"]);
	   $FastestGame["duration"] = secondsToTime($fg["duration"]);
	   $FastestGame["kills"] = ($fg["kills"]);
	   $FastestGame["deaths"] = ($fg["deaths"]);
	   $FastestGame["assists"] = ($fg["assists"]);
	   $FastestGame["creepkills"] = ($fg["creepkills"]);
	   $FastestGame["creepdenies"] = ($fg["creepdenies"]);
	   $FastestGame["neutralkills"] = ($fg["neutralkills"]);
	   $FastestGame["newcolour"] = ($fg["newcolour"]);
	   	   
	   $Durations["min_loading"] = $dg["MIN(loadingtime)"];
	   $Durations["max_loading"] = $dg["MAX(loadingtime)"];
	   $Durations["avg_loading"] = $dg["AVG(loadingtime)"];
	   }
		$sth = $db->prepare( "SELECT SUM(`left`) FROM ".OSDB_GP." 
		WHERE (name)=? LIMIT 1" );
		
		$sth->bindValue(1, "".strtolower($PlayerName)."", PDO::PARAM_STR);
	    $result = $sth->execute();
		 
	    $res = $sth->fetch(PDO::FETCH_ASSOC);
		$TotalDuration=secondsToTime($res["SUM(`left`)"]);
		
		$TotalHours=ROUND($res["SUM(`left`)"]/ 3600,1);
		$TotalMinutes=ROUND($res["SUM(`left`)"]/ 3600*60,1);
		$TimePlayed["timeplayed"] = secondsToTime( $res["SUM(`left`)"] , $lang["h"], $lang["m"], $lang["s"]);
	   //$Durations["avg_loading"] = $dg["AVG(loadingtime)"];
	   
         //GET MOST PLAYED HERO BY USER
	     $getHero = $db->prepare( GetMostPlayedHero( $PlayerName ) );
		 $result = $getHero->execute();
		 
	     $row = $getHero->fetch(PDO::FETCH_ASSOC);
	     $FavoriteHero = array();
	     $TimePlayed2["timeplayed"] = secondsToTime($row["timeplayed"], $lang["h"], $lang["m"], $lang["s"]);
	     $FavoriteHero["original"] = $row["original"];
	     $FavoriteHero["description"] = $row["description"];
		 $FavoriteHero["played"] = $row["played"];
		 
         if ( isset($ShowUserHeroStats ) AND $ShowUserHeroStats ==1 ) {
		 //GET MOST KILLS HERO BY USER
	     $getHero = $db->prepare( GetMostKillsHero( $PlayerName ) );
		 
		 $result = $getHero->execute();
	     $row = $getHero->fetch(PDO::FETCH_ASSOC);
	     $MostKillsHero = array();
	     $MostKillsHero["kills"] = ($row["maxkills"]);
		 $MostKillsHero["gameid"] = ($row["gameid"]);
	     $MostKillsHero["original"] = $row["original"];
	     $MostKillsHero["description"] = $row["description"];

		 //GET MOST DEATHS HERO BY USER
	     $getHero = $db->prepare( GetMostDeathsHero( $PlayerName ) );
		 $result = $getHero->execute();
		 
	     $row = $getHero->fetch(PDO::FETCH_ASSOC);
	     $MostDeathsHero = array();
		 $MostDeathsHero["gameid"] = ($row["gameid"]);
	     $MostDeathsHero["deaths"] = ($row["maxdeaths"]);
	     $MostDeathsHero["original"] = $row["original"];
	     $MostDeathsHero["description"] = $row["description"];

		 //GET MOST Assists HERO BY USER
	     $getHero = $db->prepare( GetMostAssistsHero( $PlayerName ) );
		 $result = $getHero->execute();
		 
	     $row = $getHero->fetch(PDO::FETCH_ASSOC);
	     $MostAssistsHero = array();
		 $MostAssistsHero["gameid"] = ($row["gameid"]);
	     $MostAssistsHero["assists"] = ($row["maxassists"]);
	     $MostAssistsHero["original"] = $row["original"];
	     $MostAssistsHero["description"] = $row["description"];

		 //GET MOST WINS HERO BY USER
	     $getHero = $db->prepare( GetMostWinsHero( $PlayerName ) );
		 $result = $getHero->execute();
		 
	     $row = $getHero->fetch(PDO::FETCH_ASSOC);
	     $MostWinsHero = array();
	     $MostWinsHero["wins"] = ($row["wins"]);
	     $MostWinsHero["original"] = $row["original"];
	     $MostWinsHero["description"] = $row["description"];
		 
		 
	     }
	   }
?>
