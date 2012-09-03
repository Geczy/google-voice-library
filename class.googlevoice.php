<?PHP

require_once('simple_html_dom.php');

class GoogleVoice
{

	private $login_auth;
	private $urls = array(
		'inbox'   => 'https://www.google.com/voice/b/0/m',
		'login'   => 'https://www.google.com/accounts/ClientLogin',
		'get'     => 'https://www.google.com/voice/b/0/request/messages',
		'send'    => 'https://www.google.com/voice/m/sendsms',
		'markRead'=> 'https://www.google.com/voice/m/mark',
		'archive' => 'https://www.google.com/voice/m/archive',
		'delete'  => 'https://www.google.com/voice/b/0/inbox/deleteMessages',
		'referer' => '',
	);

	public function __construct($user, $pass)
	{
		$this->getLoginAuth($user, $pass);
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

	private function getLoginAuth($user, $pass)
	{

		if (empty($this->login_auth)) {

			$params = array(
				'accountType'=> 'GOOGLE',
				'Email'      => $user,
				'Passwd'     => $pass,
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
		$this->getPage($this->urls['send'], $smsParam);

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

	public function getSMS($params = array())
	{

		$defaults = array(
			'history'=> false,
			'onlyNew'=> true,
			'page'   => 1,
		);

		$params = array_merge($defaults, $params);

		$json = $this->getPage($this->urls['get'].'?page='.$params['page']);
		$data = json_decode($json);

		$results = $this->parseSMS($data, $params);

		return $results;

	}

	private function parseSMS($data, $params)
	{

		$contacts = $data->contacts->contactPhoneMap;

		$results = array(
			'unread' => $data->unreadCounts->sms,
			'total'  => $data->totalSize,
		);

		foreach($data->messageList as $thread)
		{

			/* This message is already read, so skip */
			if($params['onlyNew'] && !empty($thread->isRead))
				continue;

			/* Extract just the information that's useful. */
			$number = $thread->phoneNumber;
			$results['texts'][$thread->id] = array(
				'from'  => $contacts->$number->name,
				'number'=> $thread->displayNumber,
				'text'  => $thread->messageText,
				'date'  => $thread->displayStartDateTime,
			);

			if ($params['history'])
			{
				foreach($thread->children as $child)
				{
					$results['texts'][$thread->id]['history'][] = array(
						'from' => $child->type == 11 ? 'Me' : $results['texts'][$thread->id]['from'],
						'time' => $child->displayStartDateTime,
						'message' => $child->message,
					);
				}
			}

		 }

		 return $results;

	}

	private function match($regex, $str, $out_ary = 0)
	{
		return preg_match($regex, $str, $match) == 1 ? $match[$out_ary] : false;
	}

}