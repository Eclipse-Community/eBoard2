<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators

include("lib/common.php");

AssertForbidden("viewAdminRoom");

if($loguser['powerlevel'] < 3)
	Kill(__("You're not an administrator. There is nothing for you here."));

$title = __("Administration");

$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");

$cell = 1;
function cell($content) {
	global $cell;
	$cell = ($cell == 1 ? 0 : 1);
	Write("
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
		</tr>
	", $cell, $content);
}

Write("
	<table class=\"outline margin width50\" style=\"float: right;\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				".__("Information")."
			</th>
		</tr>
");
cell(Format("
			
				".__("Last viewcount milestone")."
			</td>
			<td style=\"width: 60%;\">
				{0}
			",	$misc['milestone']));

$bucket = "adminright"; include("./lib/pluginloader.php");

write(
"
	</table>
");

$cell = 1;
Write("
	<table class=\"outline margin width25\">
		<tr class=\"header1\">
			<th>
				".__("Admin tools")."
			</th>
		</tr>
");
cell("<a href=\"recalc.php\">".__("Recalculate statistics")."</a>");
cell("<a href=\"lastknownbrowsers.php\">".__("Last Known Browsers")."</a> ".__("(not admin-only)"));
cell("<a href=\"editpora.php\">".__("Edit Points of Required Attention")."</a>");
cell("<a href=\"ipbans.php\">".__("Manage IP bans")."</a>");
cell("<a href=\"managemods.php\">".__("Manage local moderator assignments")."</a>");
cell("<a href=\"editfora.php?key=".$key."\">".__("Edit forum list")."</a>");
cell("<a href=\"editcats.php\">".__("Edit category list")."</a>");
cell("<a href=\"editsettings.php\">".__("Edit settings")."</a>");
cell("<a href=\"optimize.php\">".__("Optimize tables")."</a>");
cell("<a href=\"log.php\">".__("View log")."</a>");
//if($loguser['powerlevel'] == 4)
//	cell("<a href=\"sql.php\">".__("SQL Console")."</a>");

$bucket = "adminleft"; include("./lib/pluginloader.php");

write(
"
	</table>
");
?>
