<?php


class MCPae {
	
	static $ipv6='';
	
	static $uiutils;
	
	static $peerlist=Array();
	
	static $contacts=Array();
	
	public function __construct(UIutilities $ui){
		
		$this::$uiutils=$ui;
		
		//let's load the configuration saved
		
		$this::$ipv6 = trim(file_get_contents('./data/admin/conf/ipv6.txt'));
	
		$contactList=explode("\n", trim(file_get_contents('./data/users/contacts.txt')));
		for ($i=0;$i<count($contactList);$i++){
			$this::$contacts[trim($contactList[$i])]=trim($contactList[$i+1]);
			$i++;
		}
	}
	function isIPv6Set(){
		return file_exists('./data/admin/conf/ipv6.txt');
	}
	function getIPv6(){
		
		return $this::$ipv6;
	}
	function setIPv6(string $ip){
		$this::$ipv6=$ip;
	}
	function saveIPv6ToDisk(){
		return file_put_contents('./data/admin/conf/ipv6.txt', $this::$ipv6);
	}
	function getMountPoint(){
		return str_replace('/index.php', '', $_SERVER['PHP_SELF']);
	}

	function ping ($ip6addr, $mountpoint='/', $port=38188){
		return boolval (trim(file_get_contents('http://['.$ip6addr.']:'.$port.$mountpoint.'/?action=ping')));
	}
	function poke ($ip6addr, $mountpoint='/', $port=38188){
			return boolval (trim(file_get_contents('http://['.$ip6addr.']:'.$port.$mountpoint.'/?action=poke')));
			}
	function getNameFromIp($ip6addr){
		
			return $this::$contacts[$ip6addr];
	
		
		}
	}
	
	


class confWizard {
	private static $uiutils=null;
	private static $jasede_instance=null;
	
	public function __construct(MCPae $server_instance, UIutilities $ui = null){
		
		if ($ui===null){
			$ui = new UIUtilities();
		}
		$this::$uiutils=$ui;
		$this::$jasede_instance=$server_instance;
	}
	
	function doStuff() {
		//kinda interserver stuff :
		//reply to api call
		//isipv6ours=token
		if (isset($_GET['checkipv6isours'])&&
					is_numeric($_GET['checkipv6isours'])
			){
				file_put_contents('./data/admin/ipisours.txt', $_GET['checkipv6isours']);
				echo 'written';
				return;
			}
		
		if (!file_exists('./data/admin/conf/ipv6.txt')){
			//asktosetipV6 ; set it
			if (isset($_POST['setipv6'])){
				
				$message='';
				//what will be outputed to user
				$myip=trim($_POST['setipv6']);
				//check if ipv6 is valid
				if (networkUtilities::checkIfIPv6IsValid($myip)
					&& !strstr($myip, '.')
				
				
						){
					//check is ipv6 is really ours ? 
					$youcancheck=false;
					$token=microtime(true);
					
					/* if (file_get_contents('https://['.$myip.']'.str_replace('index.php', '', $_SERVER['PHP_SELF']).'?checkipv6isours='.$token)=='written'){
						$youcancheck=true;
					}
					else */ if (file_get_contents('http://['.$myip.']:38188/?action=conf&checkipv6isours='.$token)==='written'){
						$youcancheck=true;
						}
					else {
						$message.=htmlspecialchars($this::$uiutils->trans('No connection possible with the provided IPv6. ', LANG));
						$message.='<a href="javascript:history.back();">'.htmlspecialchars($this::$uiutils->trans('Go back', LANG)).'</a>';
					}
					
					if ($youcancheck){
						$remotetoken=trim(file_get_contents('./data/admin/ipisours.txt'));
						if (trim(str_replace($remotetoken, '', $token))==''){
							//here we are, the step is fullfilled
							$this::$jasede_instance->setIPv6(trim($_POST['setipv6']));
							$this::$jasede_instance->saveIPv6ToDisk();
							$message.=htmlspecialchars($this::$uiutils->trans('Success! ',LANG));
							$message.='<a href="./">'.htmlspecialchars($this::$uiutils->trans('Proceed ',LANG)).'</a>';
							
						}
						else {
							$message.=htmlspecialchars($this::$uiutils->trans('It seems the IPv6 
								you entered belongs to another Javica instance elsewhere. ', LANG));
							$message.=htmlspecialchars($this::$uiutils->trans('Please recheck with 
							the ifconfig command the IPv6 address associated 
							with your tun0 interface. ', LANG));
							
							$message.='<a 
							href="javascript:history.back();">'.htmlspecialchars($this::$uiutils
							->trans('Go back', LANG)).'</a>';
							
						}
					}
					
				
				}
				else {
					$message.=htmlspecialchars($this::$uiutils->trans('The IPv6 address you entered is not a valid one. Please recheck. ', LANG));
					$message.='<a 
						href="javascript:history.back();">'.htmlspecialchars($this::$uiutils
						->trans('Go back', LANG)).'</a>';
							
					}
				echo $this::$uiutils->getHTMLHead();
				echo '<h1>Operation result: </h1>';
				echo $message;
				echo $this::$uiutils->getHTMLFooter();
				
			}
			else {
			echo $this::$uiutils->getHTMLHead($this::$uiutils->trans('Javica Setup Wizard - Indicate your server IPv6 address', LANG));
			echo '<h1>'.htmlspecialchars($this::$uiutils->trans('Please indicate here the inet6 address of 
			your tun0 interface', LANG)).'</h1>'.htmlspecialchars($this::$uiutils->trans('(see the output of the ifconfig command). This will be of the form
			 of a long hexadecimal string of 8 blocks separated by colons 
			 (ie 0123:4567:89ab:cdef:fedc:ba98:7654:321
			  as an example)', LANG)).'';
			echo $this::$uiutils->getIPv6AskPanel();
			echo $this::$uiutils->getHTMLFooter();
			
			}
		}
		else if (true){//&&!isset($_GET['specifypeers'])){
			touch ('./data/confWizardCompleted.txt');
			echo $this::$uiutils->getHTMLHead();
			echo '<h2>'.$this::$uiutils->trans('Congratulation, initial setup is completed. ',LANG).'</h2>';
			echo '<a href="./">'.$this::$uiutils->trans('Continue',LANG).'</a>';
			echo $this::$uiutils->getHTMLFooter();
			
			
		}
	}
}

class UIUtilities {
	function getHTMLHead(String $title = 'Javica', String $description = 'A Javica installation', String $csspath='./style.css', Array $GPScoord = Array ( 'lon' => 0, 'lat' =>0 )){
		$output = '<!DOCTYPE html>';
		$output .= '<html><head>';
		$output .= '<meta charset="UTF-8">';
		$output .= '<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($csspath).'">';
		
		$output .='<title>'.htmlspecialchars($title).'</title>';
		
		$output.='</head><body>';
		$breakme=false;
		
		$serv=new MCPae($this);
		if ($serv->isIPv6Set()){
			$output.='<span class="labelInstanceAddressTop">';
			
			$output.=$serv::$uiutils->trans("Your CA number is: ", LANG);
			
			$output.=htmlspecialchars($serv->getIPv6());
			
			
			$output.='</span>';
			$breakme=true;
		}
		
		
		if ($breakme){
			$output.='<br style="float:none;">';
			
		}
		
		return $output;
			
	}
	function getHTMLFooter(){
			return '<div style="font-size:78%">Powered by Javica from <a href="http://janmesh.net">the Janmesh Project</a></div></body></html>';
		}
		
	function getIPv6AskPanel(){
		$output='';
		$output.='<form action="./?action=conf&itsmyip=1" method="POST">';
		$output.=htmlspecialchars($this->trans('IPv6 address: ',LANG)).' <input type="text" name="setipv6"/><br/>';
		$output.='<input type="submit" name="submit"/>';
		$output.='</form>';
		return $output;
	}	
		
	
	
	
	
	
	function trans($message, $lang){
		return $message;
	}
}
class networkUtilities {
	function checkIfIPv6IsValid($addr){
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
		
	}
}
class APIStack {
	function dispatchAction ($action, Array $params, Array $postparams){
		
		switch ($action){
			case 'ping':
				header('content-type: text/plain');
				$this->processPing();
				die();
			case 'poke':
				header('content-type: text/plain');
				$this->processPoke();
				die();
			case 'conf':
				$this->processConf();
		
		
		}
	}
	function processPing(){
		echo '1';
	
		
	}
	function processPoke(){
		$server = new MCPae(new UIUtilities());
		if (in_array($_SERVER['REMOTE_ADDR'], array_keys($server::$contacts)))
			{
			$data=Array ();
			$data['action']='pingued';
			$data['param']=Array($_SERVER['REMOTE_ADDR']);
			if (file_put_contents('./data/users/log/'.microtime(true),
									serialize($data))){
					echo '1';
				}
				else{
					die();
				}
			}
		else
			{
			
				die();
			}
	}
	function processConf(){
		$wizard = new confWizard(new MCPae(new UIUtilities));
		$wizard->doStuff();
		die();
	}
}
class AJAXStack {
	function dispatchAction ($action, Array $params, Array $postparams){
		header('content-type: text/plain');
		
		switch ($action){
			case 'refresh':
				$this->processRefresh();
				die();
				
			case 'ping':
				$this->processPing(htmlspecialchars_decode($params['target']));
				die();
			case 'poke':
				$this->processPoke(htmlspecialchars_decode($params['target']));
				die();
			
		}
	}
	function processRefresh(){
		$serv=new MCPae(new UIUtilities());
		
		$files = array_diff(scandir('./data/users/log'), Array('..', '.'));
		sort($files);
		$files = array_reverse($files);
		foreach ($files as $file) {
		$filepath = './data/users/log/'.$file;
		
		$data = unserialize(file_get_contents($filepath));
		
		if ($data['action']==='pinging'){
			$who=$data['param'][0];
			$time=$data['param'][1];
			echo htmlspecialchars($serv::$uiutils->trans('Your ping has been echoed. ', LANG));
			echo '<br/>';
			echo htmlspecialchars($serv::$uiutils->trans('Who: ', LANG).$serv->getNameFromIp($who));
			echo ' - ';
			echo htmlspecialchars($serv::$uiutils->trans('Time: ', LANG).$time);

			
			}
		else if ($data['action']==='pingued'){
			$who=$data['param'][0];
			echo htmlspecialchars($serv::$uiutils->trans('You have received a ping poke. ', LANG));
			echo '<br/>';
			echo htmlspecialchars($serv::$uiutils->trans('Who: ', LANG).$serv->getNameFromIp($who));
			echo ' - ';
			echo htmlspecialchars($serv::$uiutils->trans('Time: ', LANG).date(DATE_RSS, $file));

			
			}


			//else if action autre chose
		echo '<hr/>'; 
		}//foreach entry
		
	}
	function processPoke($ip){
		$serv=new MCPae(new UIUtilities());
		
		$start=microtime(true);
			$branch=$serv->poke($ip);
			if ($branch){
				$stop=microtime(true);
				
				$time=$stop-$start;
				$wrap=Array();
				$wrap['action']='pinging';
				$wrap['param']=Array ($ip, $time. ' s');
				$data=serialize($wrap);
				
		
				return file_put_contents('./data/users/log/'.microtime(true), $data);
				}
			
			else {
				return $branch;
			}	
	}
	function processPing($ip){
		$serv=new MCPae(new UIUtilities());
		
		return $serv->ping($ip);
		
	}

}
?>
