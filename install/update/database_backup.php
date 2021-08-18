<?php 
require_once("../../assets/includes/route.php");

ini_set('max_execution_time', 300);

if ($session->is_logged_in() != true ) { redirect_to("../../login.php"); }
$current_user = User::get_specific_id($session->admin_id);

if ($current_user->prvlg_group == "1") {
	
	$db->backup_database();
	Log::log_action($current_user->id , "Database Backup" , "Download a database backup");
	
} else {

	redirect_to("index.php");

}
?>