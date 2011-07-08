<?php
#Gintoki
#
#https://github.com/SADM/Gintoki

error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

if (get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) { $_GET[$key] = stripslashes($val); }
	foreach ($_POST as $key => $val) { $_POST[$key] = stripslashes($val); }
}
if (get_magic_quotes_runtime()) { set_magic_quotes_runtime(0); }

function fancyDie($message) {
	die('<body text="#800000" bgcolor="#FFFFEE" align="center"><br><span style="background-color: #F0E0D6;font-size: 1.25em;font-family: Tahoma, Verdana, Arial, sans-serif;padding: 7px;border: 1px solid #D9BFB7;border-left: none;border-top: none;">' . $message . '</span><br><br>- <a href="javascript:history.go(-1)">Click here to go back</a> -</body>'');
}

if (!file_exists('settings.php')) {
	fancyDie('Please rename the file settings.default.php to settings.php');
}
require 'settings.php';

$includes = array("inc/functions.php", "inc/html.php");
if (in_array(GINTOKI_DBMODE, array('mysql'))) {
	$includes[] = 'inc/database_' . GINTOKI_DBMODE . '.php';
} else {
	fancyDie("Unknown database mode specificed");
}

foreach ($includes as $include) {
	include $include;
}

$redirect = true;

if (isset($_POST["quote"])) {
	list($loggedin, $isadmin) = manageCheckLogIn();
	if ($loggedin) {
		checkMessageSize();
	
		$quote = newQuote();

		$quote['approver'] = cleanString(substr($_POST['approver'], 0, 75));
		$quote['quote'] = str_replace("\n", "<br>", cleanString(rtrim($_POST["quote"])));
		$quote['approveblock'] = approveBlock($quote['approver'],  time());

		if ($quote['quote'] == '') {
			fancyDie("Please enter a quote.");
		}
		if ($quote['approver'] == '') {
			fancyDie("Please enter an approver name.");
		}
	
		$quote['id'] = insertQuote($quote);
	}
	rebuildIndexes();
}
elseif (isset($_GET["manage"])) {
	$text = ""; $onload = ""; $navbar = "&nbsp;";
	$redirect = false; $loggedin = false; $isadmin = false;
	$returnlink = basename($_SERVER['PHP_SELF']);
	
	list($loggedin, $isadmin) = manageCheckLogIn();
	
	if ($loggedin) {
		if ($isadmin) {
			if (isset($_GET["rebuildall"])) {
				rebuildIndexes();
				$text .= "Rebuilt quotes.";
			}
		}
		
		if (isset($_GET["delete"])) {
			$quote = quoteByID($_GET['delete']);
			if ($quote) {
				deleteQuoteByID($quote['id']);
				rebuildIndexes();
				$text .= '<b>Quote No.' . $quote['id'] . ' successfully deleted.</b>';
			} else {
				fancyDie("Sorry, there doesn't appear to be a quote with that ID.");
			}
		} elseif (isset($_GET["moderate"])) {
			if ($_GET['moderate'] > 0) {
				$quote = quoteByID($_GET['moderate']);
				if ($quote) {
					$text .= manageModerateQuote($quote);
				} else {
					fancyDie("Sorry, there doesn't appear to be a quote with that ID.");
				}
			} else {
				$onload = manageOnLoad('moderate');
				$text .= manageModerateQuoteForm();
			}
		} elseif (isset($_GET["modquote"])) {
			$onload = manageOnLoad('modquote');
			$text .= manageModquoteForm();
		} elseif (isset($_GET["logout"])) {
			$_SESSION['gintoki'] = '';
			session_destroy();
			die('--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . $returnlink . '?manage">');
		}
		if ($text == '') {
			$quotes = countQuotes();
			$text = $quotes . ' ' . plural('quote', $quotes) . '.';
		}
	} else {
		$onload = manageOnLoad('login');
		$text .= manageLogInForm();
	}

	echo managePage($text, $onload);
} elseif (!file_exists('index.html') || count(allQuotes()) == 0) {
	rebuildIndexes();
}

if ($redirect) {
	echo '--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . (is_string($redirect) ? $redirect : 'index.html') . '">';
}

?>
