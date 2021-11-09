<?php
//  AcmlmBoard XD - Points of Required Attention editing page
//  Access: administrators

include("lib/common.php");

$title = __("Points of Required Attention");

AssertForbidden("editPoRA");

if($loguser['powerlevel'] < 3)
	Kill(__("You must be an administrator to edit the Points of Required Attention."));
	
$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");
if (isset($_POST['action']) && $key != $_POST['key'])
	Kill(__("No."));

if($_POST['action'] == __("Edit"))
{
	//TidyPost($_POST['text']);
	$qPora = "update misc set porabox = '".justEscape($_POST['text'])."', poratitle = '".justEscape($_POST['title'])."'";
	$rPora = Query($qPora);
	Report("[b]".$loguser['name']."[/] edited the PoRA.", 1);
	Redirect(__("Edited!"), "./", __("the main page"));
}

write(
"
	<div class=\"PoRT\">
		<div class=\"errort\">
			<strong id=\"previewtitle\">
				{0}
			</strong>
		</div>
		<div class=\"errorc cell2 left\" id=\"previewtext\">
			{1}
		</div>
	</div>

	<form action=\"editpora.php\" method=\"post\">
		<table id=\"t\" class=\"outline margin width50\">
			<tr class=\"header1\">
				<th colspan=\"2\">
					".__("PoRA Editor")."
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					".__("Title (plain)")."
				</td>
				<td>
					<input type=\"text\" name=\"title\" id=\"title\" maxlength=\"256\" style=\"width: 80%;\" value=\"{2}\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					".__("Content (HTML)")."
				</td>
				<td style=\"width: 80%;\">
					<textarea name=\"text\" rows=\"16\" style=\"width: 97%;\" id=\"editbox\" onkeyup=\"startPoraUpdate()\">{3}</textarea>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"".__("Edit")."\" />
					<input type=\"hidden\" name=\"key\" value=\"{4}\" />
				</td>
			</tr>
		</table>
	</form>
",	$misc['poratitle'], $misc['porabox'], htmlval($misc['poratitle']), htmlval($misc['porabox']), $key);
?>