<?php
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
if ($_SERVER['REMOTE_ADDR']!=='127.0.0.1'&&!isset($_GET['action'])){
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
	
	
if (!file_exists('./data/confWizardCompleted.txt')&&!isset($_GET['action'])){
	$wizard = new confWizard($server_instance, $uiutils);
	$wizard->doStuff();
	die();
}





if (!isset($_GET['action'])){
	
	
	
//then the stuff once initial setup is completed
//and this is not an api call
//greeter then
	

	echo $server_instance::$uiutils->getHTMLHead();
	
	
	echo $server_instance::$uiutils->getHTMLFooter();
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
