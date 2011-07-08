<?php
function pageHeader() {
	$return = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>
EOF;
	$return .= GINTOKI_DESC . <<<EOF
		</title>
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" type="text/css" href="css/global.css">
		<link rel="stylesheet" type="text/css" href="css/futaba.css" title="futaba">

		<script type="text/javascript">var style_cookie="wakabastyle";</script>

		<meta http-equiv="content-type" content="text/html;charset=UTF-8">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">

	</head>
EOF;
	return $return;
}

function pageFooter() {
	return <<<EOF
		<div class="footer">
			- <a href="http://www.2chan.net" target="_top">futaba</a> + <a href="https://github.com/tslocum/TinyIB" target="_top">tinyib</a> + <a href="https://github.com/SADM/Gintoki">Gintoki</a>-
		</div>
	</body>
</html>
EOF;
}

function buildQuote($quote) {
	$return = "";
	
$return .= <<<EOF
<table>
<tbody>
<tr>
<td class="doubledash">
	&#0168;
</td>
<td class="reply" id="reply${quote["id"]}">

${quote['id']}
<label>
${quote["approveblock"]}
</label>

<blockquote>
${quote["quote"]}
</blockquote>

</td>
</tr>
</tbody>
</table>
EOF;
	
	return $return;
}

function buildPage($htmlposts, $parent, $pages=0, $thispage=0) {
	$managelink = basename($_SERVER['PHP_SELF']) . "?manage";
	
	$pagenavigator = "";
		$pages = max($pages, 0);
		$previous = ($thispage == 1) ? "index" : $thispage - 1;
		$next = $thispage + 1;
		
		$pagelinks = ($thispage == 0) ? "<td>Previous</td>" : '<td><form method="get" action="' . $previous . '.html"><input value="Previous" type="submit"></form></td>';
		
		$pagelinks .= "<td>";
		for ($i = 0;$i <= $pages;$i++) {
			if ($thispage == $i) {
				$pagelinks .= '&#91;' . $i . '&#93; ';
			} else {
				$href = ($i == 0) ? "index" : $i;
				$pagelinks .= '&#91;<a href="' . $href . '.html">' . $i . '</a>&#93; ';
			}
		}
		$pagelinks .= "</td>";
		
		$pagelinks .= ($pages <= $thispage) ? "<td>Next</td>" : '<td><form method="get" action="' . $next . '.html"><input value="Next" type="submit"></form></td>';
		
		$pagenavigator = <<<EOF
<table border="1">
	<tbody>
		<tr>
			$pagelinks
		</tr>
	</tbody>
</table>
EOF;
	
	$body = <<<EOF
	<body>
		<div class="logo">
EOF;
	$body .= GINTOKI_LOGO . <<<EOF
		</div>
		<hr width="90%" size="1">
		$pagenavigator
		$htmlposts
		$pagenavigator
		<br>
EOF;
	return pageHeader() . $body . pageFooter();
}

function rebuildIndexes() {	
	$page = 0; $i = 0; $htmlquotes = "";
	$pages = ceil(countQuotes() / 50) - 1;
	$quotes = allQuotes(); 
	
	foreach ($quotes as $quote) {		
		$htmlquotes .= buildQuote($quote) . "<br clear=\"left\">\n";
		$i += 1;
		if ($i == 50) {
			$file = ($page == 0) ? "index.html" : $page . ".html";
			writePage($file, buildPage($htmlquotes, 0, $pages, $page));
			$page += 1; $i = 0; $htmlquotes = "";
		}
	}
	
	if ($page == 0 || $htmlquotes != "") {
		$file = ($page == 0) ? "index.html" : $page . ".html";
		writePage($file, buildPage($htmlquotes, 0, $pages, $page));
	}
}

function adminBar() {
	global $loggedin, $isadmin, $returnlink;
	$text = '';
	if (!$loggedin) { return '[<a href="' . $returnlink . '">Return</a>]'; }
	$text .= '[<a href="?manage&moderate">Moderate Post</a>] [<a href="?manage&modquote">Post Quote</a>] [';
	$text .= ($isadmin) ? '<a href="?manage&rebuildall">Rebuild All</a>] [' : '';
	$text .= '<a href="?manage&logout">Log Out</a>] [<a href="' . $returnlink . '">Return</a>]';
	return $text;
}

function managePage($text, $onload='') {
	$adminbar = adminBar();
	$body = <<<EOF
	<body$onload>
		<div class="adminbar">
			$adminbar
		</div>
		<div class="logo">
EOF;
	$body .= GINTOKI_LOGO . <<<EOF
		</div>
		<hr width="90%" size="1">
		<div class="replymode">Manage mode</div>
		$text
		<hr>
EOF;
	return pageHeader() . $body . pageFooter();
}

function manageOnLoad($page) {
	switch ($page) {
		case 'login':
			return ' onload="document.gintoki.password.focus();"';
		case 'moderate':
			return ' onload="document.gintoki.moderate.focus();"';
		case 'modquote':
			return ' onload="document.gintoki.quote.focus();"';
	}
}
function manageLogInForm() {
	return <<<EOF
	<form id="gintoki" name="gintoki" method="post" action="?manage">
	<fieldset>
	<legend align="center">Please enter an administrator or moderator password</legend>
	<div class="login">
	<input type="password" id="password" name="password"><br>
	<input type="submit" value="Submit" class="managebutton">
	</div>
	</fieldset>
	</form>
	<br>
EOF;
}
function manageModquoteForm() {
	return <<<EOF
	<div class="postarea">
		<form id="gintoki" name="gintoki" method="post" action="?" enctype="multipart/form-data">
		<input type="hidden" name="modquote" value="1">
		<table class="postform">
			<tbody>
				<tr>
					<td class="postblock">
						Approver
					</td>
					<td>
						<input type="text" name="approver" size="28" maxlength="75" accesskey="n">
						<input type="submit" value="Submit" accesskey="z">
					</td>
				</tr>
				<tr>
					<td class="postblock">
						Quote
					</td>
					<td>
						<textarea name="quote" cols="48" rows="4" accesskey="m"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		</form>
	</div>
EOF;
}

function manageModerateQuoteForm() {
	return <<<EOF
	<form id="gintoki" name="gintoki" method="get" action="?">
	<input type="hidden" name="manage" value="">
	<fieldset>
	<legend>Moderate a post</legend>
	<label for="moderate">Quote ID:</label> <input type="text" name="moderate" id="moderate"> <input type="submit" value="Submit" class="managebutton"><br>
	<legend>
	</fieldset>
	</form><br>
EOF;
}

function manageModerateQuote($quote) {
	global $isadmin;
	$quote_html = buildQuote($quote, false);
	return <<<EOF
	<fieldset>
	<legend>Moderating quote No.${post['id']}</legend>
	
	<div class="floatpost">
	<fieldset>
	$quote_html
	</fieldset>
	</div>
	
	<fieldset>
	<legend>Action</legend>					
	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="delete" value="${post['id']}">
	<input type="submit" value="Delete quote" class="managebutton">
	</form>
	</fieldset>
	
	</fieldset>
	<br>
EOF;
}
?>
