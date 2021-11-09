<?php
//  AcmlmBoard XD - IP ban management tool
//  Access: administrators only

include("lib/common.php");

$title = __("IP bans");

AssertForbidden("editIPBans");

if($loguser['powerlevel'] < 3)
	Kill(__("Only administrators get to manage IP bans."));

MakeCrumbs(array(__("Main")=>"./", __("IP ban manager")=>""), "");

if($_POST['action'] == __("Add"))
{
	$qIPBan = "insert into ipbans (ip, reason, date) values ('".justEscape($_POST['ip'])."', '".justEscape($_POST['reason'])."', ".((int)$_POST['days'] > 0 ? time() + ((int)$_POST['days'] * 86400) : 0).")";
	$rIPBan = Query($qIPBan);
	Alert(__("Added."), __("Notice"));
}
elseif($_GET['action'] == "delete")
{
	$qIPBan = "delete from ipbans where ip='".justEscape($_GET['ip'])."' limit 1";
	$rIPBan = Query($qIPBan);
	Alert(__("Removed."), __("Notice"));
}

$qIPBan = "select * from ipbans order by date desc";
$rIPBan = Query($qIPBan);

$banList = "";
while($ipban = Fetch($rIPBan))
{
	$cellClass = ($cellClass+1) % 2;
	if($ipban['date'])
		$date = gmdate($dateformat,$ipban['date'])." (".TimeUnits($ipban['date']-time())." left)";
	else
		$date = __("Permanent");
	$banList .= format(
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
			<a href=\"ipbans.php?ip={1}&amp;action=delete\">&#x2718;</a>
		</td>
	</tr>
", $cellClass, $ipban['ip'], $ipban['reason'], $date);
}

write("
<table class=\"outline margin width50\">
	<tr class=\"header1\">
		<th>".__("IP")."</th>
		<th>".__("Reason")."</th>
		<th>".__("Date")."</th>
		<th>&nbsp;</th>
	</tr>
	{0}
</table>

<form action=\"ipbans.php\" method=\"post\">
	<table class=\"outline margin width50\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				".__("Add")."
			</th>
		</tr>
		<tr>
			<td class=\"cell2\">
				".__("IP")."
			</td>
			<td class=\"cell0\">
				<input type=\"text\" name=\"ip\" style=\"width: 98%;\" maxlength=\"25\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				".__("Reason")."
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"reason\" style=\"width: 98%;\" maxlength=\"25\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				".__("For")."
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"days\" size=\"13\" maxlength=\"13\" /> ".__("days")."
			</td>
		</tr>
		<tr class=\"cell2\">
			<td></td>
			<td>
				<input type=\"submit\" name=\"action\" value=\"".__("Add")."\" />
			</td>
		</tr>
	</table>
</form>
", $banList);

MakeCrumbs(array(__("Main")=>"./", __("IP ban manager")=>""), "");

?>
