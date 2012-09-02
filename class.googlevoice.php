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

	private $login_auth;
	private $urls = array(
		'inbox'   => 'https://www.google.com/voice/b/0/m',
		'login'   => 'https://www.google.com/accounts/ClientLogin',
		'sms'     => 'https://www.google.com/voice/m/sendsms',
		'markRead'=> 'https://www.google.com/voice/m/mark',
		'archive' => 'https://www.google.com/voice/m/archive',
		'delete'  => 'https://www.google.com/voice/b/0/inbox/deleteMessages',
		'referer' => '',
	);

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
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: GoogleLogin {$this->login_auth}", 'User-Agent: Mozilla/5.0'));
		curl_setopt($ch, CURLOPT_REFERER, $this->urls['referer']);

		if(!empty($param)){
			curl_setopt($ch, CURLOPT_POST, "application/x-www-form-urlencoded");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}

		$response = curl_exec($ch);
		$this->urls['referer'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		curl_close($ch);

		return $response;

	}

	private function getLoginAuth()
	{

		if (empty($this->login_auth)) {

			$params = array(
				'accountType'=> 'GOOGLE',
				'Email'      => $this->username,
				'Passwd'     => $this->password,
				'service'    => 'grandcentral',
				'source'     => 'com.odwdinc.GoogleVoiceTool',
			);

			$loginParam = http_build_query($params);
			$html = $this->getPage($this->urls['login'], $loginParam);
			$this->login_auth = strstr($html, 'Auth=');

		}

	}

	private function getRnrSe()
	{
		$html = $this->getPage($this->urls['inbox']);
		$this->rnrSee = $this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1);
	}

	public function sendSMS($to_phonenumber, $smstxt)
	{

		$params = array(
			'number' => $to_phonenumber,
			'smstext'=> $smstxt,
			'_rnr_se'=> $this->rnrSee,
		);

		$smsParam = http_build_query($params);
		$this->getPage($this->urls['sms'], $smsParam);

	}

	public function delete($ID)
	{

		$params = array(
			'messages'=> $ID,
			'trash'   => 1,
			'_rnr_se' => $this->rnrSee,
		);

		$deleteParam = http_build_query($params);
		$this->getPage($this->urls['delete'], $deleteParam);

	}

	public function archive($ID)
	{

		$params = array(
			'p'    => 1,
			'label'=> 'unread',
			'id'   => $ID,
		);

		$archiveParam = http_build_query($params);
		$archiveURL = $this->urls['archive'].'?'.$archiveParam;
		$this->getPage($archiveURL);

	}

	public function markRead($ID)
	{

		$params = array(
			'p'    => 1,
			'label'=> 'unread',
			'id'   => $ID,
			'read' => 1,
		);

		$readParam = http_build_query($params);
		$readURL = $this->urls['markRead'].'?'.$readParam;
		$this->getPage($readURL);

	}

	public function getSMS($onlyNew = false, $page = 1)
	{

		$url = 'https://www.google.com/voice/b/0/request/messages?';
		$json = $this->getPage($url.'page='.$page);
		$data = json_decode($json);

		$contacts = $data->contacts->contactPhoneMap;

		$results = array(
			'unread' => $data->unreadCounts->sms,
			'total'  => $data->totalSize,
		);

		foreach($data->messageList as $thread)
		{

			/* This message is already read, so skip */
			if($onlyNew && !empty($thread->isRead))
				continue;

			/* Extract just the information that's useful. */
			$number = $thread->phoneNumber;
			$results[] = array(
				'id'    => $thread->id,
				'from'  => $contacts->$number->name,
				'number'=> $thread->displayNumber,
				'text'  => $thread->messageText,
				'date'  => $thread->displayStartDateTime,
			);

		 }

		 return $results;

	}

	private function match($regex, $str, $out_ary = 0)
	{
		return preg_match($regex, $str, $match) == 1 ? $match[$out_ary] : false;
	}

}