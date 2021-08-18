<?php session_start();

$assets_location = '../';
$config_file = dirname(dirname(__FILE__)) ."/assets/includes/config.php";
$rand = "RandomHash!";
require_once('functions.php');

if(isset($_SESSION[$rand]) && $_SESSION[$rand] != "") { 
	$random_hash = $_SESSION[$rand];
} else {
	$random_hash = uniqid();
	$_SESSION[$rand] = $random_hash;
}

//Sandbox!
$php_version = phpversion();
$safe_mode = ini_get('safe_mode');
/*if (function_exists('apache_get_modules')) {
  $modules = apache_get_modules();
  $mod_rewrite = in_array('mod_rewrite', $modules);
} else {
  $mod_rewrite =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;
}*/
$privileges = is_writable($config_file);
$errors = array();
$step_errors = array();

$db_host = 'localhost';
$db_name = '';
$db_user = '';
$db_pass = '';
$db_table_prefix = 'p_';

$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$url = str_replace('install/' , '' , $url);
$url = str_replace('index.php' , '' , $url);

$admin_email = '';
$admin_password = '';

$step = 1;
$steps = 4;

//Builder!
require_once('../assets/includes/Library/PasswordHash.php');
require_once($config_file);

if(isset($_POST['submit'])) {
	
	//if($_POST['hash'] == $_SESSION[$rand]){
		$step = trim($_POST['step']);
		if($step == 1) {
			$db_host = trim($_POST['db_host']);
			$db_name = trim($_POST['db_name']);
			$db_user = trim($_POST['db_user']);
			$db_pass = trim($_POST['db_pass']);
			$db_table_prefix = trim($_POST['db_table_prefix']);
			
			@$connection = mysqli_connect($db_host,$db_user,$db_pass);
			if($connection) {
				if(@mysqli_select_db($connection,$db_name)) {
					
				//write to config.php
$str = "<?php //Database connection settings
defined('DBH') ? null : define ('DBH' , '{$db_host}');
defined('DBU') ? null : define ('DBU' , '{$db_user}');
defined('DBPW') ? null : define ('DBPW' , '{$db_pass}');
defined('DBN') ? null : define ('DBN' , '{$db_name}');
defined('DBTP') ? null : define ('DBTP' , '{$db_table_prefix}');\r\n\r\n";
					
					$file = fopen($config_file, "w");
					fwrite($file, $str);
					fclose($file);
					
					$step = 2;
				} else {
					$step_errors[] = "Database Selection failed! " . mysqli_error($connection);
				}
			} else {
				$step_errors[] = "MySQLi Connection failed! " . mysqli_connect_error();
			}
		} elseif($step == 2) {
			$url = trim($_POST['url']);
			$secret = trim($_POST['secret']);
			$sitekey = trim($_POST['sitekey']);
			$fb_secret = trim($_POST['fb_secret']);
			$fb_id = trim($_POST['fb_id']);
			$google_secret = trim($_POST['google_secret']);
			$google_id = trim($_POST['google_id']);
			$analytics = trim($_POST['analytics']);
			$addthis = trim($_POST['addthis']);
			
			$analy = 'false';
			if($analytics != '') {
				$analy = 'Array("UA" => "' . $analytics . '" )';
			}
			$addt = 'false';
			if($addthis != '') {
				$addt = 'Array("ra" => "' . $addthis . '" )';
			}
			
$str = "//Define your web accessible link to this script, including http:// or https:// with TRAILING SLASH / in the end !IMPORTANT
defined('WEB_LINK') ? null : define('WEB_LINK' , '{$url}');
defined('ERROR_LINK') ? null : define('ERROR_LINK' , WEB_LINK );
defined('UPL_FILES') ? null : define('UPL_FILES' , WEB_LINK.'assets');\r\n\r\n";

$str.= '//Facebook API Credentials, get them from https://developers.facebook.com/apps
$facebook_api = array("secret"=>"' . $fb_secret . '", "id" => "' . $fb_id. '");

//Google API Credentials, get them from https://console.developers.google.com
$google_api = array("secret"=>"' . $google_secret . '", "id" => "' . $google_id . '");

//Google Captcha Info, get them from https://www.google.com/recaptcha/admin
$captcha_info = array("secret"=>"' . $secret . '", "sitekey" => "' . $sitekey. '");

//Google Analytics Info, get them from https://analytics.google.com/analytics/web/
$analytics_info = '. $analy . ';

//AddThis Info, get them from https://www.addthis.com/dashboard/
$addthis_info = ' . $addt . ';

require_once("url_mapper.php");

?>';
			
			$file = fopen($config_file, "a+");
			fwrite($file, $str);
			fclose($file);
			
			$new_file = file_get_contents('../.htaccess');
			$new_file = str_replace('[WEB_LINK]', $url , $new_file);
			
			$htaccessfile = fopen("../.htaccess", "w");
			fwrite($htaccessfile, $new_file);
			fclose($htaccessfile);
			
			$step = 3;
			
		} elseif($step == 3) {
			$f_name = trim($_POST['f_name']);
			$l_name = trim($_POST['l_name']);
			$email = trim($_POST['email']);
			$username = trim($_POST['username']);
			$password = trim($_POST['password']);
			
			$phpass = new PasswordHash(8, true);
			$hashed_pass = $phpass->HashPassword($password);
			
			$new_file = file_get_contents('db.schema');
			$new_file = str_replace('[DBTP]', DBTP , $new_file);
			$new_file = str_replace('[ADMINPASS]', $hashed_pass , $new_file);
			$new_file = str_replace('[ADMINFNAME]', $f_name, $new_file);
			$new_file = str_replace('[ADMINLNAME]', $l_name, $new_file);
			$new_file = str_replace('[ADMINEMAIL]', $email, $new_file);
			$new_file = str_replace('[ADMINUSERNAME]', $username, $new_file);
			
			$dbfile = fopen("pearls.sql", "w");
			fwrite($dbfile, $new_file);
			fclose($dbfile);
			
			$con = mysqli_connect(DBH,DBU,DBPW);
			mysqli_select_db($con, DBN);
			
			$split = SplitSQL('pearls.sql' , ';' , $con);
			if($split != 'finished') {
				$step_errors[] = $split;
			}
			
			$step = 4;
			
		}		
	//} else {
		//$step_errors[] = "Authentication failed! please try again";
	//}
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="Description" content="Pearls! Installer">
	<meta name="author" content="MichaelDesigns">
    <link rel="icon" href="../favicon.ico">

    <title>Pearls! Installer</title>
	
	<!-- Bootstrap core CSS -->
    <link href="<?php echo $assets_location; ?>assets/css/bootstrap.css?v=1.01" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo $assets_location; ?>assets/css/custom.css?v=1.01" rel="stylesheet">

	<script src="https://use.fontawesome.com/48d68862e7.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="<?php echo $assets_location; ?>assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
	<link href="<?php echo $assets_location; ?>assets/plugins/summernote/summernote.css" rel="stylesheet">
	<link href="<?php echo $assets_location; ?>assets/plugins/tagsinput/bootstrap-tagsinput.css" rel="stylesheet">
	<link href="<?php echo $assets_location; ?>assets/plugins/typeahead/typeaheadjs.css?v=1.01" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<link href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css" rel="stylesheet" >
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

<body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header" style="">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          
		  
		  <a class="navbar-brand" href="#me" style="font-family:Lobster, Tahoma, Arial; color:#b92b27;font-size:25px">Pearls! Installer</a>
        </div>
		
		<div id="navbar" class="navbar-collapse collapse">
		
		<ul class="nav navbar-nav navbar-right">
            <li class='current'><a href="#me"><i class="glyphicon glyphicon-off"></i>&nbsp;&nbsp;Install</a></li>
            <li class=''><a href="mailto:michael.zohney@gmail.com"><i class="glyphicon glyphicon-exclamation-sign"></i>&nbsp;&nbsp;Support</a></li>
          </ul>
		  
        </div><!--/.nav-collapse -->
      </div>
    </nav>

<div class="container">		

<div class="row">
	
	<div class="col-md-3 hidden-sm hidden-xs">
	<i class="glyphicon glyphicon-tasks"></i>&nbsp;&nbsp;Server Requirements
	<hr>
	<ul class="feed-ul">
		<?php if($php_version && $php_version >= 5.4 ) { ?>
		<li style='color:green'><i class='glyphicon glyphicon-ok'></i> PHP Version: <?php echo phpversion(); ?></li>
		<?php } else { ?>
		<li style='color:red' ><i class='glyphicon glyphicon-remove'></i> PHP Version: <?php echo phpversion(); ?></li>
		<?php
		$errors[] = "<b>PHP Version</b> is not compatible with this script, please install PHP > 5.4 first!"; } ?>
		
		
		<?php if(!$safe_mode) { ?>
		<li style='color:green'><i class='glyphicon glyphicon-ok'></i> Safe Mode: Disabled</li>
		<?php } else { ?>
		<li style='color:red' ><i class='glyphicon glyphicon-remove'></i> Safe Mode: Enabled</li>
		<?php
		$errors[] = "<b>Safe Mode</b> is enabled, please disabled safe mode first!"; } ?>
		
		<?php /*if($mod_rewrite) { ?>
		<li style='color:green'><i class='glyphicon glyphicon-ok'></i> Mod_Rewrite: Enabled</li>
		<?php } else { ?>
		<li style='color:red' ><i class='glyphicon glyphicon-remove'></i> Mod_Rewrite: Disabled</li>
		<?php
		$errors[] = "<b>mod_rewrite</b> module is disabled, please enable mod_rewrite first!"; }*/ ?>
		
		<?php if($privileges) { ?>
		<li style='color:green'><i class='glyphicon glyphicon-ok'></i> Write Privileges: Granted</li>
		<?php } else { ?>
		<li style='color:red' ><i class='glyphicon glyphicon-remove'></i> Write Privileges: Disabled</li>
		<?php
		$errors[] = "<b>Write Privileges</b> to the config file are disabled, please correct 'assets/includes/config.php' permissions to (775) first!"; } ?>
	</ul>
	</div>
	
	
	<div class="col-md-9">
		<?php
			if (!empty($step_errors)) {
			foreach($step_errors as $error) {
		?>
			<div class="alert alert-danger">
				<i class="glyphicon glyphicon-times"></i> <strong>Error!</strong>&nbsp;&nbsp;<?php echo $error; ?>
			</div>
		<?php 
			}}
		?>
		<div class= "page-header">
			<h3>Welcome to Pearls! installer script<small class="pull-right">[ Step <?php echo $step; ?> of <?php echo $steps; ?> ]</small></h3>
		</div>
		
		<?php if(!empty($errors)) { ?>
		<div style="color:red; font-size:20px">Unfortunately, Script installation cannot be continued on this server! Errors found:</div>
		<br><ul style="color:black; font-size:18px">
			<?php foreach($errors as $error) {
				echo "<li>{$error}</li>";
			} ?>
		</ul>
		
		<?php
		} else {
		?>
		<form method="post" action="./index.php">
		<?php
		switch($step) {
				case '1' :
				?>
				<div style="color:black; font-size:16px">
				Thanks for purchasing Pearls!<br>Now let's break the ice between pearls and your server ;)
				
			<br><br>
			<div class="form-group">
				<label for="db_host">Database Host</label>
				<input type="text" class="form-control" name="db_host" id="db_host" value="<?php echo $db_host; ?>" required>
			</div>
			<div class="form-group">
				<label for="db_name">Database Name</label>
				<input type="text" class="form-control" name="db_name" id="db_name" value="<?php echo $db_name; ?>" required>
			</div>
			<div class="form-group">
				<label for="db_user">Database Username</label>
				<input type="text" class="form-control" name="db_user" id="db_user" value="<?php echo $db_user; ?>" required>
			</div>
			<div class="form-group">
				<label for="db_pass">Database Password</label>
				<input type="text" class="form-control" name="db_pass" id="db_pass" value="<?php echo $db_pass; ?>" required>
			</div>	
			<div class="form-group">
				<label for="db_table_prefix">Tables Prefix</label>
				<input type="text" class="form-control" name="db_table_prefix" id="db_table_prefix" value="<?php echo $db_table_prefix; ?>" >
			</div>
				
				</div>
				<?php
				break;
				case '2' :
			?>
			<div style="color:black; font-size:16px">
				Great! installer now can connect to MySQL Server ^_^<br>
				Now please verify your script Public URL [ <i>starting with http:// & ending with trailing slash /</i> ]
				
			<br><br>
			<div class="form-group">
				<label for="url">Script URL</label>
				<input type="text" class="form-control" name="url" id="url" value="<?php echo $url; ?>" required>
			</div>
			
			Pearls! uses some open source tools for security, analytics and sharing functions, please follow these steps to properly install them
				
			<br><br>
			<div class="form-group clearfix">
				<label for="url">Facebook API Keys (For Social Login)</label>&nbsp;&nbsp;<a href="#me" class="" data-toggle="modal" data-target="#facebook_modal">[How to get these keys?!]</a><br>
				<input type="text" class="form-control pull-left" style="width:40%" name="fb_id" id="fb_id" value="" placeholder="Facebook ID">
				<input type="text" class="form-control pull-left" style="width:40%" name="fb_secret" id="fb_secret" value="" placeholder="Facebook Secret">
			</div>
			<!-- Modal -->
			<div class="modal fade" id="facebook_modal" tabindex="-1" role="dialog" aria-labelledby="GoogleCaptchaInfo">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="GoogleCaptchaInfo">Get Facebook API Keys</h4>
				  </div>
				  <div class="modal-body">
					- First, go to <a href="https://developers.facebook.com/apps" target="_blank">this link</a>, and press on [+Add a New App]<br>
					<br>- Then, open your newly creatd app, click on (dashboard) on left sidebar, you'll see app id and secret on dashboard<br>
					<br>
					<div class="row"><div class="col-md-6"><img src="facebook_1.jpg" style="width:100%"></div><div class="col-md-6"><img src="facebook_2.jpg" style="width:100%"></div></div>
					<br>
					- From Left sidebar, Choose (+ add product) --> Facebook Login<br><br>
					- From (Choose a Platform) screen, choose web --> Enter your domain name under (Site URL) and click (Save)<br><br>
					<img src="facebook_4.jpg" style="width:100%"><br><br>
					- Choose (Settings) from left (Facebook Login) menu, Scroll to (Valid OAuth redirect URIs) section, and enter both versions of your site, with WWW and without WWW<br>
					<pre><?php 
					$url2 = str_replace('www.','',$url);
					$url2 = str_replace('http://','http://www.',$url2);
					if(substr($url2, -1) == '/') {
						$url2= substr($url2, 0, -1);
					}
					echo $url2.'<br>';
					$url2 = str_replace('www.','',$url);
					if(substr($url2, -1) == '/') {
						$url2= substr($url2, 0, -1);
					}
					echo $url2;?>
					</pre><br>
					<br>
					<img src="facebook_3.jpg" style="width:100%">
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				  </div>
				</div>
			  </div>
			</div>
			<div class="form-group clearfix">
				<label for="url">Google API Keys (For Social Login)</label>&nbsp;&nbsp;<a href="#me" class="" data-toggle="modal" data-target="#google_modal">[How to get these keys?!]</a><br>
				<input type="text" class="form-control pull-left" style="width:40%" name="google_id" id="google_id" value="" placeholder="Google ID">
				<input type="text" class="form-control pull-left" style="width:40%" name="google_secret" id="google_secret" value="" placeholder="Google Secret">
			</div>
			<!-- Modal -->
			<div class="modal fade" id="google_modal" tabindex="-1" role="dialog" aria-labelledby="GoogleCaptchaInfo">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="GoogleCaptchaInfo">Get Google API Keys</h4>
				  </div>
				  <div class="modal-body">
					- First, go to <a href="https://console.developers.google.com/apis/credentials"  target="_blank">this link</a>, and choose (Credentials) from left sidebar<br><br>
					- Then, Click on (OAuth Consent Screen), enter your app name and press (Save)<br><br>
					- Return to the (Credentials) tab, Click on (Create Credentials) --> Create client ID<br><br>
					- Choose (Web Application)<br><br>
					- Another set of options will appear, under (Authorized JavaScript origins) enter both versions your site, with WWW and without WWW (<i>Without trailig slash</i>)<br>
					<pre><?php
					$url2 = str_replace('www.','',$url);
					$url2 = str_replace('http://','http://www.',$url2);
					if(substr($url2, -1) == '/') {
						$url2= substr($url2, 0, -1);
					}
					echo $url2.'<br>';
					$url2 = str_replace('www.','',$url);
					if(substr($url2, -1) == '/') {
						$url2= substr($url2, 0, -1);
					}
					echo $url2;
					?></pre><br>
					- And under (Authorized Redirect URIs) enter this string (<i>For HybridAuth - best practice to avoid [400 invalid redirect uri] errors</i>)<br>
					<pre><?php echo $url2 . '/assets/includes/hybridauth/?hauth.done=Google' ."<br>". $url . 'assets/includes/hybridauth/?hauth.done=Google'; ?></pre>
					<br>
					- Click (Create), a message will appear telling you your new Client ID & Secret ..<br><br>
					- Last Step (<span style="color:red">Important</span>) you need to enable Google+ API, Go to <a href="https://console.developers.google.com/apis/library"  target="_blank">this link</a>, and choose (Google+ API) under (Social APIs)
					then click on (Enable) button, That's it!
				
					<center><img src="google.jpg" style="width:100%"></center>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				  </div>
				</div>
			  </div>
			</div>
			
			
			
			<div class="form-group clearfix">
				<label for="url">Google CAPTCHA Keys (Required)</label>&nbsp;&nbsp;<a href="#me" class="" data-toggle="modal" data-target="#captcha_modal">[How to get these keys?!]</a><br>
				<input type="text" class="form-control pull-left" style="width:40%" name="sitekey" id="sitekey" value="" required placeholder="Site Key">
				<input type="text" class="form-control pull-left" style="width:40%" name="secret" id="secret" value="" required placeholder="Secret Key">
			</div>
			<!-- Modal -->
			<div class="modal fade" id="captcha_modal" tabindex="-1" role="dialog" aria-labelledby="GoogleCaptchaInfo">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="GoogleCaptchaInfo">Get Google Captcha</h4>
				  </div>
				  <div class="modal-body">
					- First, we must register a famous (Google) account (who doesn't!)<br>
					- Then, go to <a href="https://www.google.com/recaptcha/admin" target="_blank">this link</a> and register your new site<br>
					- Google will give you site and secret keys once you register your new site<br>
					<center><img src="recaptcha.jpg" style="width:100%"></center>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				  </div>
				</div>
			  </div>
			</div>
			
			
			
			<div class="form-group clearfix">
				<label for="">Google Analytics Tracking ID (Optional)</label>&nbsp;&nbsp;<a href="#me" class="" data-toggle="modal" data-target="#analytics_modal">[How to get these keys?!]</a><br>
				<input type="text" class="form-control pull-left" style="width:40%" name="analytics" id="analytics" value="" placeholder="UA-">
			</div>
			<!-- Modal -->
			<div class="modal fade" id="analytics_modal" tabindex="-1" role="dialog" aria-labelledby="GoogleAnalyticsInfo">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="GoogleAnalyticsInfo">Get Google Analytics</h4>
				  </div>
				  <div class="modal-body">
					- First, we must register a famous (Google) account (who doesn't!)<br>
					- Then, go to <a href="https://www.google.com/recaptcha/admin" target="_blank">this link</a> and press on ADMIN section<br>
					- Click On (ACCOUNT) Menu, choose (Create New Account)<br>
					- Once you create an account, you can easily grab your tracking ID through copy/paste the code beside your account's name, or <a href="https://support.google.com/analytics/answer/1032385?hl=en" target="_blank">ADMIN section</a>
					<center><img src="analytics.jpg" style="width:100%"></center>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				  </div>
				</div>
			  </div>
			</div>
			
			
			<div class="form-group clearfix">
				<label for="">AddThis ID (Optional)</label>&nbsp;&nbsp;<a href="#me" class="" data-toggle="modal" data-target="#addthis_modal">[How to get these keys?!]</a><br>
				<input type="text" class="form-control pull-left" style="width:40%" name="addthis" id="addthis" value="" placeholder="ra-">
			</div>
			<!-- Modal -->
			<div class="modal fade" id="addthis_modal" tabindex="-1" role="dialog" aria-labelledby="AddThisInfo">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="AddThisInfo">Get AddThis</h4>
				  </div>
				  <div class="modal-body">
					- First, we must register an account on <a href="https://www.addthis.com" target="_blank">AddThis</a> website<br>
					- Then, click on (...) as shown on the screen below<br>
					- You'll see your ID there!
					<center><img src="addthis.jpg" style="width:100%"></center>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				  </div>
				</div>
			  </div>
			</div>
			
			
			</div>
			
			
			<?php
				break;
				case '3' :
			?>
			<div style="color:black; font-size:16px">
				Great! Now tell pearls more about you!<br>
				
			<br><br>
			<div class="form-group clearfix">
				<label for="f_name">Admin Name</label><br>
				<input type="text" class="form-control pull-left" style="width:40%" name="f_name" id="f_name" value="" required placeholder="First Name..">
				<input type="text" class="form-control pull-left" style="width:40%" name="l_name" id="l_name" value="" required placeholder="Last Name..">
			</div>
			<div class="form-group clearfix">
				<label for="Username">Admin Username</label><br>
				<input type="text" class="form-control" style="width:80%" name="username" id="username" value="admin" required>
			</div>
			<div class="form-group clearfix">
				<label for="email">Admin Email</label><br>
				<input type="email" class="form-control" style="width:80%" name="email" id="email" value="" required>
			</div>
			<div class="form-group clearfix">
				<label for="password">Admin Password</label><br>
				<input type="text" class="form-control" style="width:80%" name="password" id="password" value="" required>
			</div>
			
			</div>
			
			
			<?php
				break;
				case '4' :
			?>
			<div style="color:black; font-size:16px">
				<h3 style="color:green">Congratulations! You've installed (Pearls) !</h3><br>
				<h4>You can now <a href="<?php echo WEB_LINK; ?>">view</a> your script and <a href="<?php echo WEB_LINK; ?>/login/">login</a> using credentials you entered minutes ago.</h4>
				<h4>Don't forget to <b>delete (install) folder!</b></h4>
				<h4>Feel free to <a href="mailto:michael.zohney@gmail.com">Contact Me</a> anytime at michael.zohney@gmail.com ;)</h4>
			</div>
			
			
			<?php
				break;
			}
			
			if($step != $steps) {
			?>
			
			
			<div class="modal-footer">
				<br/>
				<center>
						<input class="btn btn-success" type="submit" name="submit" value="Submit">
				</center>
				<?php 
					echo "<input type=\"hidden\" name=\"step\" value=\"".$step."\" readonly/>";
					echo "<input type=\"hidden\" name=\"hash\" value=\"".$random_hash."\" readonly/>";
				?>
			</div>
			
			<?php } ?>
		</form>
		<?php
		}
		?>
	</div>
	
</div>
	
	<br><br><div class="master-footer">
	<a href="http://michael-designs.com" target="_blank">Michael Designs </a>&copy; 2017
	</div>	

	
    </div> <!-- /container -->
	
    <!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php echo $assets_location; ?>assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="<?php echo $assets_location; ?>assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo $assets_location; ?>assets/plugins/typeahead/typeahead.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="<?php echo $assets_location; ?>assets/js/ie10-viewport-bug-workaround.js"></script>
	
  </body>
</html>