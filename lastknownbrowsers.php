<?php
include("lib/common.php");
$title = "Last known browsers";
$isMod = $loguser['powerlevel'] > 0;
$sort = "id asc";
$ual = "?";
if(isset($_GET['byua']))
{
	$sort = "lastknownbrowser asc";
	$ual .= "byua&amp;";
}
AssertForbidden("viewLKB");
$numUsers = FetchResult("select count(*) from users", 0, 0);

$ppp = $loguser['postsperpage'];
if($ppp<1) $ppp=50;

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

$peeps = Query("select id, name, displayname, lastip, lastknownbrowser, sex, powerlevel from users where powerlevel < 5 order by ".$sort." limit ".$from.", ".$ppp);

$numonpage = NumRows($peeps);
for($i = $ppp; $i < $numUsers; $i+=$ppp)
{
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"lastknownbrowsers.php".$ual."from=".$i."\">".(($i/$ppp)+1)."</a>";
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = "1".$pagelinks;
	else
		$pagelinks = "<a href=\"lastknownbrowsers.php".$ual."\">1</a>".$pagelinks;
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);
}


if($isMod)
	$format = "
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
		</tr>
	";
else
	$format = "
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
				{5}
			</td>
		</tr>
	";

$items = "";
while($user = Fetch($peeps))
{
	$bucket = "userMangler"; include("./lib/pluginloader.php");
	$lip = $user['lastip'];
	$lkb = $user['lastknownbrowser'];
	if(isset($_GET['showfull']))
		$lkb = str_replace("-->", "", str_replace("<!--", " &mdash;", $lkb));

	$cellClass = ($cellClass+1) % 2;
	$items .= format($format, $cellClass, $user['id'], UserLink($user), IP2C($lip), $lip, $lkb);
}

if($isMod)
	write("
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th>
				".__("ID")."
			</th>
			<th>
				".__("Name")."
			</th>
			<th>
				&nbsp;
			</th>
			<th>
				".__("IP")."
			</th>
			<th>
				".__("Last known browser")."
			</th>
		</tr>
		{0}
	</table>
", $items);
else
	write("
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th>
				".__("ID")."
			</th>
			<th>
				".__("Name")."
			</th>
			<th>
				&nbsp;
			</th>
			<th>
				".__("Last known browser")."
			</th>
		</tr>
		{0}
	</table>
", $items);

function IP2C($ip)
{
	$q = @mysql_query("select cc from ip2c where ip_from <= inet_aton('".$ip."') and ip_to >= inet_aton('".$ip."')") or $r['cc'] = "";
	if($q) $r = @mysql_fetch_array($q);
	if($r['cc'])
		return " <img src=\"img/flags/".strtolower($r['cc']).".png\" alt=\"".$r['cc']."\" title=\"".$r['cc']."\" />";
}

?>