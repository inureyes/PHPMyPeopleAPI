<?php
/**
  Extremely simple bot example: cat bot
  Non-Object-oriented version

  @author    Jeongkyu Shin (http://forest.nubimaru.com)
  @date      May 29, 2013
  @homepage  http://github.com/inureyes/PHPMyPeopleAPI
*/
define('ROOT',dirname(__FILE__));
require_once('./PHPMyPeopleAPI/mypeoplelib.php');

// Create bot instance
$bot = MyPeople::getInstance();

// Action by 
switch($bot->action) {
	case "sendFromMessage":
	case "sendFromGroup":
		$bot->reply($bot->content."Nya");
		break;
	case "inviteToGroup":
		$bot->reply($bot->content."Wooo");
		break;
}
