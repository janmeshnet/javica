<?php
/****
 * 18:16 <+chatgrillon> il faut d'abord que l'appelé instancie sa connection au peer.js local et récupère son ID
18:17 <+chatgrillon> ensuite, l'ID est passée par le javascript, au Apache/PHP qui la garde sous la main
18:17 <+chatgrillon> disons l'appelant
18:17 <+chatgrillon> il lance un appel
18:17 <+chatgrillon> son client va faire une requète AJAX à son propre serveur Apache/PHP en localhost
18:17 <+chatgrillon> qui dira "je veux passer un appel vers <telle ipV6>"
18:18 <+chatgrillon> son serveur PHP va contacter le serveur PHP qui est à cette adresse
18:18 <+chatgrillon> ce dernier va vérifier que l'IP d'origine est bien présente dans les contacts du receveur (très important)
18:19 <+chatgrillon> et va lui passer l'ID du receveur
***/


session_start();
require_once('./jasede-lib.php');

define ('BASEPORT', 38186);

define('MOUNTPOINT', str_replace('/index.php', '', $_SERVER['PHP_SELF']));



if (!isset($_SESSION[MOUNTPOINT])){
	$_SESSION[MOUNTPOINT]=Array();
	
}

//change this later
$debug=true;


error_reporting(E_ERROR|E_PARSE);
//we still got file_get_content to possibly inexisting files therefore
//triggering warnings. FIXME



//tree directory creation

if (!file_exists('./data')){
	mkdir('./data');
	
}
if (!file_exists('./data/admin')){
	mkdir('./data/admin');
	
}
if (!file_exists('./data/conf')){
	mkdir('./data/conf');
	
}
if (!file_exists('./data/users')){
	mkdir('./data/users');
	
}
if (!file_exists('./data/users/log')){
	mkdir('./data/users/log');
	
}
if (!file_exists('./data/modules')){
	mkdir('./data/modules');
	
}
if (!file_exists('./data/.htaccess')){
	file_put_contents('./data/.htaccess', 'Require all denied	
	');
}


if (file_get_contents('http://127.0.0.1:'.BASEPORT.str_replace('index.php', '', $_SERVER['PHP_SELF']).'/data')=='This shouldn\'t be accessible from the outside!'."\n"){
		die ('Fatal ERROR! your ./data directory is accessible from the outside. Please check your web server configuration to prevent this, or data/.htaccess if your using Apache older than 2.4. With 2.4, you should enable AllowOverides All instead of None in apache.conf for the /var/www/(your install dir) directory');
}

//first off we check that the call is made from localhost only
if (!($_SERVER['REMOTE_ADDR']=='127.0.0.1'||$_SERVER['REMOTE_ADDR']=='::1')&&!isset($_GET['action'])){
	$ui = new UIUtilities;
	die(htmlspecialchars($ui->trans('This ressource is not accessible if not reached from the local computer. ', LANG)));
	}

$uiutils=new UIUtilities();
$server_instance=new MCPae($uiutils);

if (!isset($_SESSION[MOUNTPOINT]['lang'])){
	$lang='en';
}
else {
	$lang=$_SESSION[MOUNTPOINT]['lang'];
	
}
define ('LANG', $lang);



//main stuff here

//first the stuff that is when we are still unconnected to the network
// NOT USED
if (false&&strstr($_SERVER['SERVER_PROTOCOL'], 'HTTPS/')){
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"/></head>
			<body>'.$uiutils->
			trans('Please use https connexion to access 
			this service: ',LANG).
			'</body></html>';
	die();
	}
	
	
if (!file_exists('./data/confWizardCompleted.txt')&&!isset($_GET['action'])&&!isset($_GET['ajax'])){
	$wizard = new confWizard($server_instance, $uiutils);
	$wizard->doStuff();
	die();
}





if (!isset($_GET['action'])&&!isset($_GET['ajax'])){
	
	
	
//then the stuff once initial setup is completed
//and this is not an api call
//greeter then
	

	echo $server_instance::$uiutils->getHTMLHead();
	echo '<hr>';
	echo '<script>
	function ping(){
			var xhttp = new XMLHttpRequest();
		  
		  xhttp.open("GET", "./?ajax=poke&target="+encodeURI(document.getElementById("target").value), true);
		  xhttp.send();
				}
	
	</script>';
	echo $server_instance::$uiutils->trans('Send a ping poke: ', LANG);
	echo '<span style="display:inline;">';
	
	echo '<select id="target">';
	$keys=array_keys($server_instance::$contacts);
	$selected=false;
	foreach ($keys as $key){
			echo '<option value="'.htmlspecialchars($key).'"
			
						  
			';
			if (!$selected){
				echo ' selected ';
				$selected=true;
				}
			echo '>';
			echo htmlspecialchars($server_instance::$contacts[$key]);	
			echo '</option>';	 
			
	} 
	echo '</select>';
	
	echo '<button onclick="ping();">poke</button>';
	
	echo '</span>'; 
	echo '<hr/>';
	echo '<div style="background-color:black; border: solid green 1px; width: 100%;"></div>';
	echo '<hr/>';
	echo '<script>
	function refresh(){
			var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("main").innerHTML=this.responseText;
			 
			}
		  };
		  xhttp.open("GET", "?ajax=refresh", true);
		  xhttp.send();
				}
	
	setInterval(refresh, 5000);
	</script>';
	
	
	
	
	echo '<div id="main"></div>';
	echo $server_instance::$uiutils->getHTMLFooter();
	die();
} 
if (isset($_GET['ajax'])){
	$api=new AJAXStack();
	$actionarray=Array();
	$api->dispatchAction($_GET['ajax'], $_GET, $_POST);

	die();
} 

//first
//okay, we're online, update the ts
file_put_contents('./data/conf/lastseenactive.txt', microtime(true));


 //main stuff really starts now
if (isset($_GET['action'])){
	$api=new APIStack();
	$actionarray=Array();
	$api->dispatchAction($_GET['action'], $_GET, $_POST);
	
}
?>
