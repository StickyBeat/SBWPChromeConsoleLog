<?php
	/*
	Plugin Name: Chrome Console Logger
	Plugin URI: http://www.stickybeat.se/wp-chrome-console-log
	Description: Output php errors and debug info to Chrome's javascript console
	Author: Sticky Beat
	Version: 1.0
	Author URI: http://www.stickybeat.se
	*/


$GLOBALS['sb_chrome_console_log'] = new sb_chrome_console_log;

class sb_chrome_console_log {
	public function __construct() {
		set_error_handler(array($this,'sbcc_error_handler'),E_ALL);
		add_action("admin_menu", array($this,'sbcc_setup_plugin_admin_menus'));
		register_shutdown_function( array($this,'sbcc_shutdown'));
	}

	public function sbcc_error_handler($code, $msg, $file, $line) {

		$errorType = array (
				   E_ERROR            => 'ERROR',
				   E_WARNING        => 'WARNING',
				   E_PARSE          => 'PARSING ERROR',
				   E_NOTICE         => 'NOTICE',
				   E_CORE_ERROR     => 'CORE ERROR',
				   E_CORE_WARNING   => 'CORE WARNING',
				   E_COMPILE_ERROR  => 'COMPILE ERROR',
				   E_COMPILE_WARNING => 'COMPILE WARNING',
				   E_USER_ERROR     => 'USER ERROR',
				   E_USER_WARNING   => 'USER WARNING',
				   E_USER_NOTICE    => 'USER NOTICE',
				   E_STRICT         => 'STRICT NOTICE',
				   E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR'
				   );

		$showError = array (
				   E_ERROR            => get_option('sbcc_show_errors',true),
				   E_WARNING        => get_option('sbcc_show_warnings',true),
				   E_PARSE          => get_option('sbcc_show_errors',true),
				   E_NOTICE         => get_option('sbcc_show_notices',false),
				   E_CORE_ERROR     => get_option('sbcc_show_errors',true),
				   E_CORE_WARNING   => get_option('sbcc_show_warnings',true),
				   E_COMPILE_ERROR  => get_option('sbcc_show_errors',true),
				   E_COMPILE_WARNING => get_option('sbcc_show_warnings',true),
				   E_USER_ERROR     => get_option('sbcc_show_errors',true),
				   E_USER_WARNING   => get_option('sbcc_show_warnings',true),
				   E_USER_NOTICE    => get_option('sbcc_show_notices',false),
				   E_STRICT         => get_option('sbcc_show_strict_notices',false),
				   E_RECOVERABLE_ERROR  => get_option('sbcc_show_errors',true)
				   );

		if($showError[$code]) {
			$type = $errorType[$code];

			$simpleFile = basename($file);
			$file = addslashes($file);

			$inlineMsg = '';
			if(strlen($msg) < 50) {
				$inlineMsg = ' - '.addslashes($msg);
			}
			echo '<script>console.groupCollapsed("PHP '.$type.' at '.$simpleFile.' %cline '.$line.'%c'.$inlineMsg.'","color:blue;", "color: red;");'."console.log('filename: $file');console.log('line: $line');".'console.log("'.addslashes($msg).'");console.groupEnd();</script>';

		}
	}



	public function sbcc_shutdown() {
		$isError = false;

		if ($error = error_get_last()){

			switch($error['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
					$isError = true;
					break;
				}
		}

		if ($isError){
		   $this->sbcc_error_handler($error['type'],$error['message'],$error['file'],$error['line']);
		}
	}

	public function sbcc_setup_plugin_admin_menus() {
		add_submenu_page('options-general.php','Chrome Console Log Settings', 'Chrome Console Log', 'edit_pages','sbcc_plugin_settings', array($this,'sbcc_plugin_settings_page'));

	}

	public function sbcc_plugin_settings_page() {

		if (isset($_POST['update_settings'])) {
			update_option('sbcc_show_consolelog', $_POST['sbcc_show_consolelog']);
			update_option('sbcc_show_errors', $_POST['sbcc_show_errors']);
			update_option('sbcc_show_warnings', $_POST['sbcc_show_warnings']);
			update_option('sbcc_show_notices', $_POST['sbcc_show_notices']);
			update_option('sbcc_show_strict_notices', $_POST['sbcc_show_strict_notices']);
		}
		?>

		<div class="wrap">
		<h2>Chrome Console Logger Settings</h2>
		<p>
			This plugin redirects PHP output errors to Chrome's javascript console instead of the normal "inlined" messages. <br/>
			Developed for Chrome but kind of works in other browsers supporting the console.log javascript method
		</p>
		<p>It also includes a function named consoleLog() you can use to output debug text to the console.</p>
		<h3>Examples</h3>
		<pre>
consoleLog('Debug text');
consoleLog('Lots of text',$text); // this displays the text in a collapsed group for your convenience
consoleLog('My array',$array); // you can also output arrays or objects
		</pre>
		<form method="POST" action="">
			<label>
				<input type="checkbox" name="sbcc_show_consolelog" value="true"<?php if(get_option('sbcc_show_consolelog',true)) {?> checked<?php } ?>>
				Show consoleLog() messages
			</label>
			<br/>
			<label>
				<input type="checkbox" name="sbcc_show_errors" value="true"<?php if(get_option('sbcc_show_errors',true)) {?> checked<?php } ?>>
				Show Errors
			</label>
			<br/>
			<label>
				<input type="checkbox" name="sbcc_show_warnings" value="true"<?php if(get_option('sbcc_show_warnings',true)) {?> checked<?php } ?>>
				Show Warnings
			</label>
			<br/>
			<label>
				<input type="checkbox" name="sbcc_show_notices" value="true"<?php if(get_option('sbcc_show_notices',false)) {?> checked<?php } ?>>
				Show Notices
			</label>
			<br/>
			<label>
				<input type="checkbox" name="sbcc_show_strict_notices" value="true"<?php if(get_option('sbcc_show_strict_notices',false)) {?> checked<?php } ?>>
				Show Strict Notices
			</label>

		    <br/><br/>
		    <input type="hidden" name="update_settings" value="true" />
		    <input type="submit" value="Save Settings" class="button-primary">
		</form>
	<?php
		if (isset($_POST['update_settings'])) {
			?>
				<h2>Settings saved!</h2>
			<?
		}?>
		</div>
	<?php

	}



}

function consoleLog($title, $data=null) {
	if(get_option('sbcc_show_consolelog',true)) {
		if($data==null) {
			$data = $title;
			$title = null;
		}

		if(is_array($data) || is_object($data)) {
			$data = print_r($data,true);
		}

		if(!$title) {
			$data = 'PHP: '.$data;
		}
		$data = addslashes($data);
		$realdata = explode("\n",$data);
		$data = '';

		foreach($realdata as $row) {
			$data.=$row."'".'+"\n"+'."'\\\n";
		}

		if($title) {
			$title = addslashes('PHP: '.$title);
			echo "<script>console.groupCollapsed('".$title."');console.log('".$data."');console.groupEnd();</script>";
		} else {
			echo "<script>console.log('".$data."');</script>";
		}
	}
}

?>