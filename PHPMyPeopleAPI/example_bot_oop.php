<?php
/**
  Extremely simple bot example: cat bot
  Object-oriented version

  @author    Jeongkyu Shin (http://forest.nubimaru.com)
  @date      May 29, 2013
  @homepage  http://github.com/inureyes/PHPMyPeopleAPI
*/
define('ROOT',dirname(__FILE__));
require_once('./PHPMyPeopleAPI/mypeoplelib.php');

// Define cat bot.

final class catbot extends MyPeople {
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
	public function __construct() {
		parent::__construct();
		$this->action();
	}
	private function action() {
		switch($bot->action) {
			case "sendFromMessage":
			case "sendFromGroup":
				$bot->reply($bot->content."Nya");
				break;
			case "inviteToGroup":
				$bot->reply($bot->content."Wooo");
			break;
		}
	}
}
// Create bot instance
$bot = catbot::getInstance();
