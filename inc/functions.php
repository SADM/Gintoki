<?php
function cleanString($string) {
	$search = array("<", ">");
	$replace = array("&lt;", "&gt;");
	
	return str_replace($search, $replace, $string);
}

function plural($singular, $count, $plural = 's') {
	if ($plural == 's') {
        $plural = $singular . $plural;
    }
    return ($count == 1 ? $singular : $plural);
}

function newQuote() {
	return array('parent' => '0',
				'timestamp' => '0',
				'approver' => '',
				'approveblock' => '',
				'quote' => '');
}

function approveBlock($approver, $timestamp) {
	$output = 'Добавил <span class="postername">' . $approver . '</span>';
	
	return $output  . ' ' . date('Y.m.d(D)H:i:s', $timestamp);
}

function writePage($filename, $contents) {
	$tempfile = tempnam('./', 'gintokitmp'); /* Create the temporary file */
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	/* If we aren't able to use the rename function, try the alternate method */
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}
	
	chmod($filename, 0664); /* it was created 0600 */
}

function checkMessageSize() {
	if (strlen($_POST["quote"]) > 8000) {
		fancyDie("Your quote is " . strlen($_POST["message"]) . " characters long, and the maximum allowed is 8000.");
	}
}

function manageCheckLogIn() {
	$loggedin = false; $isadmin = false;
	if (isset($_POST['password'])) {
		if ($_POST['password'] == GINTOKI_ADMINPASS) {
			$_SESSION['gintoki'] = GINTOKI_ADMINPASS;
		} elseif (GINTOKI_MODPASS != '' && $_POST['password'] == GINTOKI_MODPASS) {
			$_SESSION['gintoki'] = GINTOKI_MODPASS;
		}
	}
	
	if (isset($_SESSION['gintoki'])) {
		if ($_SESSION['gintoki'] == GINTOKI_ADMINPASS) {
			$loggedin = true;
			$isadmin = true;
		} elseif (GINTOKI_MODPASS != '' && $_SESSION['gintoki'] == GINTOKI_MODPASS) {
			$loggedin = true;
		}
	}
	
	return array($loggedin, $isadmin);
}
?>
