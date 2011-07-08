<?php
$link = mysql_connect(GINTOKI_DBHOST, GINTOKI_DBUSERNAME, GINTOKI_DBPASSWORD);
if (!$link) {
	fancyDie("Could not connect to database: " . mysql_error());
}
$db_selected = mysql_select_db(GINTOKI_DBNAME, $link);
if (!$db_selected) {
	fancyDie("Could not select database: " . mysql_error());
}

// Create the quote table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . GINTOKI_DBQUOTES . "'")) == 0) {
	mysql_query("CREATE TABLE `" . GINTOKI_DBQUOTES . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`timestamp` int(20) NOT NULL,
		`approver` varchar(75) NOT NULL,
		`approveblock` varchar(255) NOT NULL,
		`quote` text NOT NULL,
		PRIMARY KEY	(`id`)
	) ENGINE=MyISAM");
}

function quoteByID($id) {
	$result = mysql_query("SELECT * FROM `" . GINTOKI_DBQUOTES . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			return $post;
		}
	}
}

function insertQuote($quote) {
	mysql_query("INSERT INTO `" . GINTOKI_DBQUOTES . "` (`timestamp`, `approver`, `approveblock`, `quote`) VALUES (" . time() . ", '" . mysql_real_escape_string($quote['approver']) . "', '" . mysql_real_escape_string($quote['approveblock']) . "', '" . mysql_real_escape_string($quote['quote']) . "')");
	return mysql_insert_id();
}

function countQuotes() {	
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . GINTOKI_DBQUOTES . "`"), 0, 0);
}

function allQuotes() {	
	$quotes = array();
	$result = mysql_query("SELECT * FROM `" . GINTOKI_DBQUOTES . "` ORDER BY `timestamp` DESC");
	if ($result) {
		while ($quote = mysql_fetch_assoc($result)) {
			$quotes[] = $quote;
		}
	}
	return $quotes;
}

function deleteQuoteByID($id) {	
	mysql_query("DELETE FROM `" . GINTOKI_DBQUOTES . "` WHERE `id` = " . $id . " LIMIT 1");
}

?>
