<?php 

class vk {	
    public $remixsid;
	public $vklogin;
	public $vkpassword;
	public $useragint;
	public $cookies;
	
	public $autocomplete;
	public $performer;
	
	function __construct() {
            $accounts = array(
                array('login' => 'tel number', 'password' => 'your pass'),
            );

            $apiaccess = array(
                    array('secret_key' => 'b9e60a2df188f96ac79880124de5fd6d5b85e08f35cc570ca614ec58474938a272e60a7ee8080223495b1', 'access_token' => '3da054ac1ed5148b7e'),
            );
            
		$account = $accounts[array_rand($accounts)];
                
		$this->vklogin = $account['login'];
		$this->vkpassword = $account['password'];
		$this->useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36';
                $cookie_file = sha1($this->vklogin.$this->vkpassword);		
		$this->cookies = array('remixsid'=>$this->remixsid, 'remixlang'=>'0');
		$autocomplete = 1;
		$performer = 1;
                
                
	}
	
	private function local_login() {
		$post = array(
			'act'=>'login',
   			'q'=>'1',
   			'al_frame'=>'1',
   			'expire'=>'',
   			'from_host'=>'vkontakte.ru',
   			'email'=>$this->vklogin,
   			'pass'=>$this->vkpassword,
   			'captcha_sid'=>'',
   			'captcha_key'=>''
    	);
		$res = $this->postRequest('http://login.vk.com/?act=login',$post,TRUE,FALSE,TRUE);
                
		if ($res) {
			if(isset($res['headers']) AND isset($res['headers'][1])) {
				$remixsid = array_keys($res['headers'][1]['cookie']);
				$remixsid = $res['headers'][1]['cookie'][end($remixsid)]['value'];
				
				return ($remixsid != 'deleted') ? $remixsid : FALSE;
			}
		}
		return FALSE;
	}

	private function postRequest($url,$post,$get_headers=FALSE,$cookies=FALSE,$debug=FALSE,$max_redirect=20) {
		$result = array('html'=>'','headers'=>array());
		$cookies_array = array();
		if ($cookies)
			foreach ($cookies as $key => $value)
				$cookies_array[$key] = $value;
		if ($ch = curl_init($url)) {
			if (is_array($cookies)) {
				$cookies_line=array();
				foreach ($cookies as $key => $value)
					$cookies_line[]="$key=$value";
				if (count($cookies_line))
					curl_setopt($ch, CURLOPT_COOKIE, implode('; ',$cookies_line));
			}
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->useragint);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ini_get('open_basedir') AND ini_get('safe_mode'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_COOKIEFILE, '');
			if ($debug)
				curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
			if (is_array($post))
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			do {
            	$res = curl_exec($ch);
            	if (curl_errno($ch))
                	break;
				$headers = substr($res,0,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
				if (preg_match_all("/(..*?)\r\n\r\n/is",$headers,$arr)) {
					foreach ($arr[1] as $value) {
						$headers_tmp = $this->getHeaders($value);
						if (isset($headers_tmp['cookie'])) {
							foreach ($headers_tmp['cookie'] as $cookie_key => $cookie_value)
								$cookies_array[$cookie_key] = $cookie_value['value'];
						}
					}
					$cookies_line=array();
					foreach ($cookies_array as $cookie_key => $cookie_value)
						if (strlen($cookie_value))
							$cookies_line[]="$cookie_key=$cookie_value";
					if (count($cookies_line))
						curl_setopt($ch, CURLOPT_COOKIE, implode('; ',$cookies_line));
					$result['headers'][] = $headers_tmp;
				}
				if ($debug) {
					$result['debug']['out_headers'][] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
				}
				$return_code =  curl_getinfo($ch, CURLINFO_HTTP_CODE);
            	if ($return_code != 301 AND $return_code != 302) {
					$result['html'] = substr($res,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
                	break;
				}
				$redirect_url = end($result['headers']);
				if (!preg_match('/^[http|https]+/is',$redirect_url['Location'])) {
					$url = parse_url($url);
					$location = $url['scheme'].'://'.$url['host'].$redirect_url['Location'];
				} else {
					$location = $redirect_url['Location'];
				}
            	curl_setopt($ch, CURLOPT_URL, $location );
				curl_setopt($ch, CURLOPT_POST, 0);
        	} while (--$max_redirect);
			curl_close($ch);
			if (!$max_redirect)
				return FALSE;
			return $get_headers?$result:$result['html'];
		}
		return FALSE;
	}
        
        

	private function getRequest($url,$get_headers=FALSE,$cookies=FALSE,$max_redirect=20,$debug=FALSE) {
		$result = array('html'=>'','headers'=>array());
		$cookies_array = array();
		if ($cookies)
			foreach ($cookies as $key => $value)
				$cookies_array[$key] = $value;
		if ($ch = curl_init($url)) {
			if (is_array($cookies)) {
				$cookies_line=array();
				foreach ($cookies as $key => $value)
					$cookies_line[]="$key=$value";
				if (count($cookies_line))
					curl_setopt($ch, CURLOPT_COOKIE, implode('; ',$cookies_line));
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->useragint);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ini_get('open_basedir') AND ini_get('safe_mode'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_COOKIEFILE, '');
			do {
            	$res = curl_exec($ch);
            	if (curl_errno($ch))
                	break;
				$headers = substr($res,0,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
				if (preg_match_all("/(..*?)\r\n\r\n/is",$headers,$arr)) {
					foreach ($arr[1] as $value) {
						$headers_tmp = $this->getHeaders($value);
						if (isset($headers_tmp['cookie'])) {
							foreach ($headers_tmp['cookie'] as $cookie_key => $cookie_value)
								$cookies_array[$cookie_key] = $cookie_value['value'];
						}
					}
					$cookies_line=array();
					foreach ($cookies_array as $cookie_key => $cookie_value)
						if (strlen($cookie_value))
							$cookies_line[]="$cookie_key=$cookie_value";
					if (count($cookies_line))
						curl_setopt($ch, CURLOPT_COOKIE, implode('; ',$cookies_line));
					$result['headers'][] = $headers_tmp;
				}
				if ($debug) {
					$result['debug']['out_headers'][] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
				}
				$return_code =  curl_getinfo($ch, CURLINFO_HTTP_CODE);
            	if ($return_code != 301 AND $return_code != 302) {
					$result['html'] = substr($res,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
                	break;
				}
				$redirect_url = end($result['headers']);
				if (!preg_match('/^[http|https]+/is',$redirect_url['Location'])) {
					$url = parse_url($url);
					$location = $url['scheme'].'://'.$url['host'].$redirect_url['Location'];
				} else {
					$location = $redirect_url['Location'];
				}
            	curl_setopt($ch, CURLOPT_URL, $location);
        	} while (--$max_redirect);
			curl_close($ch);
			if (!$max_redirect)
				return FALSE;
			return $get_headers?$result:$result['html'];
		}
		return FALSE;
	}

	private function getHeaders($headers_str) {
		$result = array();
		$arr = explode("\r\n",$headers_str);
		list($key,$value) = explode("/",$arr[0]);
		$result[$key] = trim($value);
		for ($i=1;$i<count($arr);$i++) {
			list($key,$value) = explode(": ",$arr[$i]);
			if (strnatcasecmp('Set-Cookie',$key)) {
				$result[$key] = trim($value);
			} else {
				$tmp = explode(";",$value);
				list($name,$val) = explode("=",$tmp[0]);
				$result['cookie'][trim($name)] = array('value'=>trim($val));
				for ($i2=1;$i2<count($tmp);$i2++) {
					@list($n,$val) = explode("=",$tmp[$i2]);
					$result['cookie'][trim($name)][trim($n)] = $val;
				}
			}
		}
		return $result;
	}
	
	private function isCaptcha($content) {
		if (preg_match('/<!>2<!>(\d+)</is',$content,$arr))
			return $arr[1];
		return FALSE;
	}
	
	private function validate_text($text) {
		//return htmlspecialchars_decode($text, ENT_QUOTES);
		return $text;
	}
	
	private function str_to_duration($str) {
		list($min, $sec) = explode(':',trim($str));
		return (intval($min)*60)+intval($sec);
	}
	
	private function get_artist_and_title($item_content) {
		$result = array('artist'=>'','title'=>'');
		if (preg_match('/selectPerformer.*?>(.*?)<\/a.*?&ndash;(.*?)\(<a href=\'id/is',$item_content,$arr)) {
			$result['artist'] = strip_tags($arr[1]);
			$result['title'] = strip_tags($arr[2]);
		}
		return $result;
	}
	
	private function get_lyrics_id($content) {
		if (preg_match('/showLyrics\([-\'\d_ ]+,(\d+)/is',$content,$arr)) {
			return $arr[1];
		}
		return '';
	}
	
	private function parse_content($content) {
		$content = iconv('windows-1251', 'UTF-8', $content);
		if (preg_match_all("/id=\"audio(-*\d+_\d+).*?value=\"(.*?),(.*?)duration +fl_r\">([\d:]+?)</is",$content,$arr)) {
			$result = array();
			for ($i=0;$i<count($arr[0]);$i++) { 

                if($i>60){
                    break;
                }
               
				list($owner_id, $aid) = explode('_',$arr[1][$i]);
				$item = $this->get_artist_and_title($arr[3][$i]);
				$temir=strip_tags(html_entity_decode($this->validate_text($item['artist'])));
				$setTitle=strip_tags(html_entity_decode($this->validate_text($item['title'])));

				if(empty($temir)){
                	continue;
                }

				$result[] = array(
					'aid'=>trim($aid),
					'owner_id'=>trim($owner_id),
					'artist'=>$temir,
					'title'=>$setTitle,
					'duration'=>$this->str_to_duration($arr[4][$i]),
					'url'=>trim($arr[2][$i]),
					'lyrics_id'=>$this->get_lyrics_id($arr[3][$i])
				);

			}
			return $result;
		}
		return FALSE;
	}
	
	public function isAuthorized() {
		return $this->remixsid;
	}
	
	public function Login($login,$password) {
		$this->vklogin = $login;
		$this->vkpassword = $password;
		if ($this->remixsid = $this->local_login())
			$this->cookies['remixsid'] = $this->remixsid;
		return $this->remixsid;
	}
	
	private function security_check() {
		$res = $this->getRequest('http://vk.com/audio?act=popular',TRUE,$this->cookies);
		if ($res) {
			if (preg_match('/submitSecurityCheck.*?params.*?to:.*?\'(.*?)\'.*?hash:.*?\'(.*?)\'/is',$res['html'],$arr)) {
				$post = array(
					'act'=>'security_check',
					'al'=>'1',
					'al_page'=>'3',
					'hash'=>$arr[2],
					'to'=>$arr[1]
				);
				if (preg_match('/\+*[994|7]+(\d+)\d\d/is',$this->vklogin,$arr)) {
					$post['code'] = $arr[1];
					$res = $this->postRequest('http://vk.com/login.php',$post,TRUE,$this->cookies);
					return TRUE;
				} else {
					return FALSE;
				}
			}
		}
		return TRUE;
	}
	
	public function search($request, $sort = 0, $lyrics = 0, $offset = 0) {
		if ($this->isAuthorized()) {
			if (!$this->security_check())
				return array('error'=>array('error_code'=>55,'error_msg'=>'security_check'));
			$post = array(
				'act'=>'search',
				'al'=>'1000',
				'autocomplete'=>'0',
				'offset'=>$offset?$offset:'0',
				'performer'=>$this->performer,
				'q'=>$request,
				'sort'=>$sort
			);
			if (!$res = $this->postRequest('http://vk.com/audio',$post,TRUE,$this->cookies))
				return array('error'=>array('error_code'=>2,'error_msg'=>''));		
			if ($captha_id = $this->isCaptcha($res['html']))
				return array('error'=>array('error_code'=>3,'error_msg'=>'captcha','captcha_id'=>$captha_id));			
			return $this->parse_content($res['html']);
		}
		return array('error'=>array('error_code'=>1,'error_msg'=>'not authorized'));
	}
	
	public function getLyrics($lyrics_id) {
		if ($this->isAuthorized()) {
			if (!$this->security_check())
				return array('error'=>array('error_code'=>55,'error_msg'=>'security_check'));
			if (!$text = $this->postRequest('http://vk.com/audio.php?act=getLyrics&lid='.$lyrics_id, array(), TRUE, $this->cookies))
				return array('error'=>array('error_code'=>2,'error_msg'=>''));
			if ($captha_id = $this->isCaptcha($text['html']))
				return array('error'=>array('error_code'=>3,'error_msg'=>'captcha','captcha_id'=>$captha_id));			
				
			return iconv('windows-1251', 'utf-8', $text['html']);
		}
		return array('error'=>array('error_code'=>1,'error_msg'=>'not authorized'));
	}
}

?>