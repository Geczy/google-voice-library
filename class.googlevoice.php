<?PHP
/*
Version     0.2
License     This code is released under the MIT Open Source License. Feel free to do whatever you want with it.
Author      lostleon@gmail.com, http://www.lostleon.com/
LastUpdate  05/28/2010
*/
require_once('simple_html_dom.php');

class GoogleVoice
{
	public $username;
	public $password;
	public $status;
	private $lastURL;
	private $login_auth = "";
	private $rnrSe = "";

	private $inboxURL = 'https://www.google.com/voice/b/0/m/';
	private $loginURL = 'https://www.google.com/accounts/ClientLogin';
	private $smsURL = 'https://www.google.com/voice/m/sendsms';
	private $unreadURL = 'https://www.google.com/voice/m/i/unread';
	private $markReadURL = 'https://www.google.com/voice/m/mark?';
	private $archiveURL = 'https://www.google.com/voice/m/archive?';
	private $deleteURL = 'https://www.google.com/voice/b/0/inbox/deleteMessages/';


	public $unreadSMSCount;
	public $totalSize;
	public $resultsPerPage;

	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		$this->getLoginAuth();
		$this->getRnrSe();
	}


	private function getPage($URL,$param=""){


		$ch = curl_init($URL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
		if($param != ""){
			curl_setopt($ch, CURLOPT_POST, "application/x-www-form-urlencoded");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}
		$html = curl_exec($ch);

		$this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);

		return $html;
	}

	private function getLoginAuth()
	{

		if (empty($this->login_auth)) {

			$params = array(
				'accountType' => 'GOOGLE',
				'Email' => $this->username,
				'Passwd' => $this->password,
				'service' => 'grandcentral',
				'source' => 'com.odwdinc.GoogleVoiceTool',
			);

			$loginParam = http_build_query($params);
			$html = $this->getPage($this->loginURL, $loginParam);
			$this->login_auth = $this->match('/Auth=([A-z0-9_-]+)/', $html, 1);

		}

	}

	private function getRnrSe()
	{
		$html = $this->getPage($this->inboxURL);
		$this->rnrSee = $this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1);
	}



	public function sendSMS($to_phonenumber, $smstxt)
	{

		$params = array(
			'id' => '',
			'c' => '',
			'number' => $to_phonenumber,
			'smstext' => $smstxt,
			'_rnr_se' => $this->rnrSee,
		);

		$smsParam = http_build_query($params);
		$this->getPage($this->smsURL, $smsParam);

	}

	public function delete($ID)
	{

		$params = array(
			'messages' => $ID,
			'trash' => 1,
			'_rnr_se' => $this->rnrSee,
		);

		$deleteParam = http_build_query($params);
		$this->getPage($this->deleteURL, $deleteParam);

	}

	public function archive($ID)
	{

		$params = array(
			'p' => 1,
			'label' => 'unread',
			'id' => $ID,
		);

		$archiveParam = http_build_query($params);
		$formatedArchiveURL = $this->archiveURL.$archiveParam;
		$this->getPage($formatedArchiveURL);

	}

	public function markRead($ID)
	{

		$params = array(
			'p' => 1,
			'label' => 'unread',
			'id' => $ID,
			'read' => 1,
		);

		$readParam = http_build_query($params);
		$formatedMarkReadURL = $this->markReadURL.$readParam;
		$this->getPage($formatedMarkReadURL);

	}

   //work in progress
	public function getSMS($onlyNew = false, $page = 1)
	{

		$json = $this->getPage("https://www.google.com/voice/b/0/request/messages?page=$page");

		echo $json;

		$data = json_decode($json);
		$this->unreadSMSCount = $data->unreadCounts->sms;
		$this->totalSize = $data->totalSize;
		$this->resultsPerPage = $data->resultsPerPage;

		$results = array();
		foreach($data->messageList as $key => $thread)
		{

			if($onlyNew == true && $thread->isRead != 0)
			{
				continue;
			}

			//echo "<br>Key : $key <br>";
			//print_r($thread);
			//echo "<br><br>";

		 }
	}

	public function getNewSMS()
	{

		$htmlSource = $this->getPage($this->unreadURL);

		$html = new simple_html_dom();

		$html->load($htmlSource);

		$newcalls = array();



		foreach($html->find('div[class="mu"]') as $unread)
		{
			 //echo $unread;
			foreach($unread->find('div[class="ms3"]') as $uk)
			{
				$thiscall = array();

				$Last_message = $uk->find('div[class!="ms2"]',-1);

				$Buttons = $uk->find('div[class="ms2"]',0);

				$lastmessage = array();

				$lastmessage["Sender"] = $Last_message->find('span',0)->plaintext;;
				$lastmessage["Message"] = $Last_message->find('span',1)->plaintext;;
				$lastmessage["Time"] = $Last_message->find('span',2)->plaintext;;

				$thiscall["Last_Message"] = $lastmessage;


				$replay_url = "";
				$Mark_as_read_url = "";

				foreach($Buttons->find('a') as $links){

					if(strstr($links->innertext,"reply")){

						$replay_url = $links->href;
					}

					if(strstr($links->innertext,"mark read")){

						$Mark_as_read_url = $links->href;
					}

				}
				$phone = strpbrk($replay_url, "+");
				$end_pos =  strrpos($phone, "&");
				$phone =  substr ($phone , 1,$end_pos-1 );


				$ID =strpbrk($replay_url, "=");
				$end_pos = strrpos($ID , "&number");
				$ID = substr ($ID , 1,$end_pos-1 );

				$thiscall["Phone_Num"] = $phone;
				$thiscall["SMS_ID"] = $ID;

				//$thiscall["replay_url"] = $replay_url;
				//$thiscall["Mark_as_read_url"] = $Mark_as_read_url;

				$newcalls[] = $thiscall;
			}
		}

		return $newcalls;

	}

	private function match($regex, $str, $out_ary = 0)
	{
		return preg_match($regex, $str, $match) == 1 ? $match[$out_ary] : false;
	}
}
?>