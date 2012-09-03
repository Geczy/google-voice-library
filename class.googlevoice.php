<?PHP

require_once('simple_html_dom.php');

class GoogleVoice
{

	private $urls = array(
		'login'   => 'https://www.google.com/accounts/ClientLogin',
		'get'     => 'https://www.google.com/voice/b/0/request/messages/',
		'send'    => 'https://www.google.com/voice/b/0/sms/send/',
		'markRead'=> 'https://www.google.com/voice/b/0/inbox/mark/',
		'archive' => 'https://www.google.com/voice/b/0/inbox/archiveMessages/',
		'delete'  => 'https://www.google.com/voice/b/0/inbox/deleteMessages/',
	);

	public function __construct($user, $pass)
	{

		/* Start the session. */
		if (!isset($_SESSION)) session_start();

		/* Preform authentication */
		$this->getLoginAuth($user, $pass);

	}

	private function getPage($url, $params = array())
	{

		$login_auth = !empty($_SESSION['Geczy']['login_auth']) ? $_SESSION['Geczy']['login_auth'] : '';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: GoogleLogin {$login_auth}", 'User-Agent: Mozilla/5.0'));

		if(!empty($params)){
			curl_setopt($ch, CURLOPT_POST, "application/x-www-form-urlencoded");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		}

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;

	}

	private function getLoginAuth($user, $pass)
	{

		if (!empty($_SESSION['Geczy']['login_auth']))
			return false;

		$params = array(
			'accountType'=> 'GOOGLE',
			'Email'      => $user,
			'Passwd'     => $pass,
			'service'    => 'grandcentral',
			'source'     => 'com.odwdinc.GoogleVoiceTool',
		);

		$auth = strstr($this->getPage($this->urls['login'], $params), 'Auth=');

		$_SESSION['Geczy']['login_auth'] = $auth;

	}

	public function getRnrSe()
	{

		if (!empty($_SESSION['Geczy']['rnr_se']))
			return $_SESSION['Geczy']['rnr_se'];

		$result = $this->getPage($this->urls['get']);
		$result = json_decode($result);

		return $result->r;

	}

	public function sendSMS($to_phonenumber, $smstxt)
	{

		$params = array(
			'phoneNumber' => $to_phonenumber,
			'text'=> $smstxt,
			'_rnr_se'=> $this->getRnrSe(),
		);

		$this->getPage($this->urls['send'], $params);

	}

	public function delete($id)
	{

		$params = array(
			'messages'=> $id,
			'trash'   => 1,
			'_rnr_se' => $this->getRnrSe(),
		);

		$this->getPage($this->urls['delete'], $params);

	}

	public function archive($id)
	{

		$params = array(
			'messages' => $id,
			'archive' => 1,
			'_rnr_se' => $this->getRnrSe(),
		);

		$this->getPage($this->urls['archive'], $params);

	}

	public function markRead($id)
	{

		$params = array(
			'messages' => $id,
			'read' => 1,
			'_rnr_se' => $this->getRnrSe(),
		);

		$this->getPage($this->urls['markRead'], $params);

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
			if($params['onlyNew'] && $thread->isRead)
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

}