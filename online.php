<?php
//  AcmlmBoard XD - Realtime visitor statistics page
//  Access: all

include("lib/common.php");

$title = __("Online users");

AssertForbidden("viewOnline");

$time = (int)$_GET['time'];
if(!$time) $time = 300;

$qUsers  = "select * from users where lastactivity > ".(time()-$time)." order by lastactivity desc";
$qGuests = "select * from guests where date > ".(time()-$time)." and bot = 0 order by date desc";
$qBots   = "select * from guests where date > ".(time()-$time)." and bot = 1 order by date desc";
$rUsers = Query($qUsers);
$rGuests = Query($qGuests);
$rBots = Query($qBots);

$spans = array(60, 300, 900, 3600, 86400);
$spanList = "";
foreach($spans as $span)
{
	$spanList .= format(
"
			<li>
				<a href=\"online.php?time={0}\">{1}</a>
			</li>
",	$span, timeunits($span));
}
write(
"
	<div class=\"smallFonts margin\">
		".__("Show visitors from this far back:")."
		<ul class=\"pipemenu\">
			{0}
		</ul>
	</div>
", $spanList);


$userList = "";
$i = 1;
if(NumRows($rUsers))
{
	while($user = Fetch($rUsers))
	{
		$cellClass = ($cellClass+1) % 2;
		if($user['lasturl'])
			$lastUrl = "<a href=\"".$user['lasturl']."\">".FilterURL($user['lasturl'])."</a>";
		else
			$lastUrl = __("None");

		$userList .= format(
	"
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td>
				{2}
			</td>
			<td>
				{3}
			</td>
			<td>
				{4}
			</td>
			<td>
				{5}
			</td>
".($loguser['powerlevel'] > 0 ? "
			<td>
				{6}
			</td>
" : "")."
		</tr>
	",	$cellClass, $i, UserLink($user), cdate("d-m-y G:i:s", $user['lastactivity']),
		($user['lastposttime'] ? cdate("d-m-y G:i:s",$user['lastposttime']) : __("Never")),
		$lastUrl, $user['lastip']);
		$i++;
	}
}
else
	$userList = "<tr class=\"cell0\"><td colspan=\"6\">".__("No users")."</td></tr>";

$guestList = "";
if(NumRows($rGuests))
{
	$i = 1;
	while($guest = Fetch($rGuests))
	{
		$cellClass = ($cellClass+1) % 2;
		if($guest['date'])
			$lastUrl = "<a href=\"".$guest['lasturl']."\">".FilterURL($guest['lasturl'])."</a>";
		else
			$lastUrl = __("None");
		
		$guestList .= format(
"
		<tr class=\"cell{0}\">
			<td>{1}</td>
			<td title=\"{2}\">{3}</td>
			<td>{4}</td>
			<td>{5}</td>
".($loguser['powerlevel'] > 0 ? "
			<td>
				{6} {7}
			</td>
" : "")."
		</tr>
",	$cellClass, $i, htmlspecialchars($guest['useragent']),
	htmlspecialchars(substr($guest['useragent'], 0, 65)), cdate("d-m-y G:i:s", $guest['date']),
	$lastUrl, $guest['ip']);
		$i++;
	}
}
else
	$guestList = "<tr class=\"cell0\"><td colspan=\"5\">".__("No guests")."</td></tr>";

$botList = "";
if(NumRows($rBots))
{
	$i = 1;
	while($bot = Fetch($rBots))
	{
		$cellClass = ($cellClass+1) % 2;
		if($bot['date'])
			$lastUrl = "<a href=\"".$bot['lasturl']."\">".FilterURL($bot['lasturl'])."</a>";
		else
			$lastUrl = __("None");

		$botList .= format(
"
		<tr class=\"cell{0}\">
			<td>{1}</td>
			<td title=\"{2}\">{3}</td>
			<td>{4}</td>
			<td>{5}</td>
".($loguser['powerlevel'] > 0 ? "
			<td>
				{6}
			</td>
" : "")."
		</tr>
",	$cellClass, $i, htmlspecialchars($bot['useragent']),
	htmlspecialchars(substr($bot['useragent'], 0, 65)), cdate("d-m-y G:i:s", $bot['date']),
	$lastUrl, $bot['ip']);
		$i++;
	}
} else
	$botList = "<tr class=\"cell0\"><td colspan=\"5\">".__("No bots")."</td></tr>";

write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"6\">
				".__("Online users")."
			</th>
		</tr>
		<tr class=\"header1\">
			<th style=\"width: 30px;\">
				#
			</th>
			<th>
				".__("Name")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last view")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last post")."
			</th>
			<th>
				".__("URL")."
			</th>
".($loguser['powerlevel'] > 0 ? "
			<th style=\"width: 140px;\">
				".__("IP")."
			</th>
" : "")."
		</tr>
		{0}
	</table>
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th style=\"width: 30px;\">
				".__("#")."
			</th>
			<th>
				".__("User agent")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last view")."
			</th>
			<th>
				".__("URL")."
			</th>
".($loguser['powerlevel'] > 0 ? "
			<th style=\"width: 140px;\">
				".__("IP")."
			</th>
" : "")."
		</tr>
		<tr class=\"header0\">
			<th colspan=\"5\">
				".__("Guests")."
			</th>
		</tr>
		{1}
		<tr class=\"header0\">
			<th colspan=\"5\">
				".__("Bots")."
			</th>
		</tr>
		{2}
	</table>
", $userList, $guestList, $botList);

function FilterURL($url)
{
	//$url = str_replace(array("%20","_"), " ", $url);
	$url = str_replace('_', ' ', urldecode($url));
	$url = htmlspecialchars($url);
	$url = preg_replace("@&?(key|token)=[0-9a-f]{64}@i", '', $url);
	return $url;
}

?>
