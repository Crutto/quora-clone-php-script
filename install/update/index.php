<?php require_once("../../assets/includes/Library/Loader.php");
if(filesize($parent.'/config.php') == '0') { die('Please install script first!'); }


$assets_location = '../../';
$config_file = dirname(dirname(dirname(__FILE__))) ."/assets/includes/config.php";
$rand = "RandomHash!";
if(isset($_SESSION[$rand]) && $_SESSION[$rand] != "") { 
	$random_hash = $_SESSION[$rand];
} else {
	$random_hash = uniqid();
	$_SESSION[$rand] = $random_hash;
}

$new_version = '1.5';
$changelog = "";

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

$step = 1;
$steps = 2;

require_once($config_file);

if(isset($_POST['submit'])) {
	$step = trim($_POST['step']);
	if($step == 1) {
		
			$new_file = file_get_contents('db.schema');
			$new_file = str_replace('[DBTP]', DBTP , $new_file);
			
			$dbfile = fopen("pearls.sql", "w");
			fwrite($dbfile, $new_file);
			fclose($dbfile);
			
			$con= mysqli_connect(DBH,DBU,DBPW);
			mysqli_select_db($con,DBN);
			
			$split = UpdateSQL('pearls.sql' , ';',$con);
			if($split != 'finished') {
				$step_errors[] = $split;
			}
			
		$step = 2;
	}
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="Description" content="Pearls! Update">
	<meta name="author" content="MichaelDesigns">
    <link rel="icon" href="../favicon.ico">

    <title>Pearls! Update</title>
	
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
          
		  
		  <a class="navbar-brand" href="#me" style="font-family:Lobster, Tahoma, Arial; color:#b92b27;font-size:25px">Pearls! Update</a>
        </div>
		
		<div id="navbar" class="navbar-collapse collapse">
		
		<ul class="nav navbar-nav navbar-right">
            <li class='current'><a href="#me"><i class="glyphicon glyphicon-retweet"></i>&nbsp;&nbsp;Update</a></li>
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
			<h3>Welcome to Pearls! Update script<small class="pull-right">[ Step <?php echo $step; ?> of <?php echo $steps; ?> ]</small></h3>
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
					This script will update your current version to (<b><?php echo $new_version; ?></b>)<br>Please <a href="database_backup.php" >Click Here</a> to create a backup from your current database then click next.<br/><br/>
				</div>
				<?php
				break;
				case '2' :
			?>
			<div style="color:black; font-size:16px">
				<h3 style="color:green">Congratulations! You've upgraded (Pearls) !</h3><br>
				<h4>You can now <a href="<?php echo WEB_LINK; ?>">view</a> your script and <a href="<?php echo WEB_LINK; ?>/login/">login</a> using credentials you entered minutes ago.</h4>
				<h4>Don't forget to <b>delete (upgrade) folder!</b></h4>
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
						<input class="btn btn-success" type="submit" name="submit" value="Next">
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