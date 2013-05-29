<?php
/**
 PHP class for Mypeople bot API

 @brief Implementation of MyPeople API. 
     Based on NAF framework and MyPeople API code samples provided by Daum DNA Lab.

 @author    Jeongkyu Shin (http://forest.nubimaru.com)
 @date      May 29, 2013
 @homepage  http://github.com/inureyes/PHPMyPeopleAPI
 @version   0.01
*/
require_once('./Singleton-Needlworks.php');

class MyPeople extends Singleton {
	public $names,$action,$buddyId,$groupId,$content;
	private $api_url_prefix = "https://apis.daum.net";

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
	public function __construct() {
		try {
			$config = parse_ini_file('./config.ini');
		} catch (Exception $e) {
		    $this->errorLog($e->getMessage());
		}
		$this->apikey = $config['apikey'];
		$this->api_url_postfix = "&apikey=" .$this->apikey;
		$this->__determineAction();
	}

	private function __determineAction() {
		$this->action = $_POST['action'];
		// 	action can be "addBuddy" "sendFromMessage" "createGroup" "inviteToGroup" "exitFromGroup" "sendFromGroup"
		$this->buddyId = $_POST['buddyId'];
		$this->groupId = (!empty($_POST['groupId']) ? $_POST['groupId'] : null);
		$this->content = $_POST['content'];
		$this->fileId = (!empty($_POST['fileId']) ? $_POST['fileId'] : null);
		if (empty($this->names) || empty($this->names[$this->buddyId])) {
			$this->buddyName = $this->__getBuddyName($this->buddyId);
			$this->names[$this->buddyId] = $this->buddyName;
		}
		if (in_array($this->action,array("inviteToGroup","createGroup"))) {
			$buddys = json_decode($this->content, true);	
			foreach($buddys as $key => $value) {
				$this->names[$buddys[$key][buddyId]] = $this->__getBuddyName($buddys[$key][buddyId]);
			}
			$this->content = '';
		}
	}
	// Behavior
	public function exitGroup($groupId) {
		$url =  $this->api_url_prefix."/mypeople/group/exit.xml?groupId=" .$groupId.$this->api_url_postfix;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($ch);
		curl_close($ch);
	}

	public function sendFile($target, $targetId, $msg, $filepath) {
		$url =  $this->api_url_prefix."/mypeople/" .$target. "/send.xml?" .$target."Id=" .$targetId. "&content=attach" .$this->api_url_postfix;

		$postData = array();
		$postData['attach']	= '@'.$filepath.";type=".mime_content_type($filepath).";filename=" .basename($filepath);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($ch);
		curl_close($ch);
	}
	public function receiveFile($fileId, $targetDir) {
		if (is_dir($targetDir) == false) return false;
		$url =  $this->api_url_prefix. "/mypeople/file/download.xml?fileId=" .$fileId.$api_url_postfix;		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		if($result) {
			$filename = '';
			$results = split("\n", trim($result));
			foreach($results as $line) {
				if (strtok($line, ':') == 'Content-Disposition') {
					$parts = explode("=", $line);
					$filename = trim($parts[1]);	//// Read filename from header information.
				}
			}
			// File body
			$body = $results[count($results)-1];
			$fo = fopen($targetDir.'/'.$filename , 'w');
			fwrite($fo, $body);
			fclose($fo);
		}
	}

	// Messaging
	public function reply($msg) {
		$this->sendMessage($target = ($this->groupId ? "group":"buddy"),$targetId =($this->groupId ? $this->groupId:$this->buddyId) ,$msg);
	}

	public function sendMessage($target, $targetId, $msg) {
		// target : buddy, group
		// targetID : buddyId or groupId
		// msg : message to send

		// Point the message target URL
		$url =  $this->api_url_prefix."/mypeople/" .$target. "/send.xml";

		$msg = urlencode(str_replace(array("\n",'\n'), "\r", $msg)); // CR/LF

		// Parameter for curl call.
		$postData = array();
		$postData[$target."Id"] = $targetId;
		$postData['content'] = $msg;	
		$postData['apikey'] = $this->apikey;	
		$postVars = http_build_query($postData);
	
		// Send message
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($ch);
		curl_close($ch);
		$this->log("sendMessage: "+$result);
	}
	// Misc.
	public function isGroupChat(){
		return (is_null($this->groupId) ? False:True);
	}
	public function isMessage() {
		return (is_null($this->groupId) ? False:True);
	}
	public function doesHaveAttachment() {
		return (is_null($this->fileId) ? False:True);
	}
	// Private function
	private function __getBuddyName($buddyId) {
		$url = $this->api_url_prefix."/mypeople/profile/buddy.xml?buddyId=".$buddyId.$this->api_url_postfix;
		// HTTPRequest using cURL.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		// Parse buddy name. 
		$xml = simplexml_load_string($result);
		if ($xml->code == 200) {
			return $xml->buddys->name;
		} else {
			return null;
		}
	}
	
	private function errorLog($msg) {
		echo $msg;
	}
}
?>
