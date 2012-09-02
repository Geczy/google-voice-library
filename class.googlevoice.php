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

	private function getPage($url, $param = '')
	{

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0'));
		curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);

		if(!empty($param)){
			curl_setopt($ch, CURLOPT_POST, "application/x-www-form-urlencoded");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}

		$response = curl_exec($ch);
		$this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		curl_close($ch);

		return $response;

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

	public function getSMS($onlyNew = false, $page = 1)
	{

		/* @todo: Set this at execution instead of in this function. */
		$url = 'https://www.google.com/voice/b/0/request/messages?';

		$params = array('page' => $page);
		$smsParams = http_build_query($params);
		$json = $this->getPage($url.$smsParams);

		$data = json_decode($json);

		/* @todo: Put these into one array. */
		$this->unreadSMSCount = $data->unreadCounts->sms;
		$this->totalSize = $data->totalSize;
		$this->resultsPerPage = $data->resultsPerPage;

		$results = array();
		foreach($data->messageList as $thread)
		{

			/* This message is already read, so skip */
			if($onlyNew && !empty($thread->isRead))
				continue;

			/* Extract just the information that's useful. */
			$results[] = array(
				'id' => $thread->id,
				'from' => $thread->displayNumber,
				'text' => $thread->messageText,
				'date' => $thread->displayStartDateTime,
			);

		 }

		 return $results;

	}

	private function match($regex, $str, $out_ary = 0)
	{
		return preg_match($regex, $str, $match) == 1 ? $match[$out_ary] : false;
	}

}