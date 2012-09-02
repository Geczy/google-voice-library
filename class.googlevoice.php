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
    private $login_auth;
    private $inboxURL = 'https://www.google.com/voice/b/0/m/';
    private $loginURL = 'https://www.google.com/accounts/ClientLogin';
    private $smsURL = 'https://www.google.com/voice/m/sendsms';
    private $UnreadURL = 'https://www.google.com/voice/m/i/unread';
   	private $Mark_Read_URL = 'https://www.google.com/voice/m/mark?';
	private $Archive_URL = 'https://www.google.com/voice/m/archive?';
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getLoginAuth()
    {
        $login_param = "accountType=GOOGLE&Email={$this->username}&Passwd={$this->password}&service=grandcentral&source=com.lostleon.GoogleVoiceTool";
        $ch = curl_init($this->loginURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20");
        curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
        curl_setopt($ch, CURLOPT_POST, "application/x-www-form-urlencoded");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $login_param);
        $html = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $this->login_auth = $this->match('/Auth=([A-z0-9_-]+)/', $html, 1);
        return $this->login_auth;
    }

    public function get_rnr_se()
    {
        $this->getLoginAuth();
        $ch = curl_init($this->inboxURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $html = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $_rnr_se = $this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1);
        return $_rnr_se;
    }

    public function Send_SMS($to_phonenumber, $smstxt)
    {
        $_rnr_se = $this->get_rnr_se();
        $sms_param = "id=&c=&number=".urlencode($to_phonenumber)."&smstext=".urlencode($smstxt)."&_rnr_se=".urlencode($_rnr_se);
        $ch = curl_init($this->smsURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_param);      
        $this->status = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $this->status;
    }
    
    public function Archive($ID)
    {
    	    	
    	$Archive_URL = $this->Archive_URL."p=1&label=unread&id=".urlencode($ID);
    	echo $Mark_Read_URL;
    	
    	    $this->getLoginAuth();
        $ch = curl_init($Archive_URL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $html = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
    } 
    
    
    
    public function Mark_Read($ID)
    {
    	    	
    	$Mark_Read_URL = $this->Mark_Read_URL."p=1&label=unread&id=".urlencode($ID)."&read=1";
    	echo $Mark_Read_URL;
    	
    	    $this->getLoginAuth();
        $ch = curl_init($Mark_Read_URL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $html = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
    }    
    
    public function Get_NEW_SMS()
    {
        $this->getLoginAuth();
        $ch = curl_init($this->UnreadURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $html_sorce = curl_exec($ch);
        
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        $html = new simple_html_dom();
        
        $html->load($html_sorce);
        
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
