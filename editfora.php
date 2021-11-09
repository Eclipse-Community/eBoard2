<?php
//  AcmlmBoard XD - Forum list editing tool
//  Access: administrators

include("lib/common.php");

AssertForbidden("editForum");

if($loguser['powerlevel'] < 3)
	Kill(__("You must be an administrator to edit the forum list."));

if($_POST['action'] == __("Move"))
{
	$qForum = "update forums set forder=".(int)$_POST['order'].", catid=".(int)$_POST['category']." where id=".(int)$_POST['fid']." limit 1";
	$rForum = Query($qForum);
	Alert(__("Forum moved."), __("Notice"));
} elseif($_POST['action'] == "Add")
{
	$qForum = "insert into forums (title, description, catid, forder) values ('".justEscape($_POST['title'])."', '".justEscape($_POST['description'])."', ".(int)$_POST['category'].", ".(int)$_POST['order'].")";
	$rForum = Query($qForum);
	Alert(__("Forum added."), __("Notice"));
} elseif($_POST['action'] == __("Remove"))
{
	$qForum = "select * from forums where id=".(int)$_POST['fid'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	write(
"
	<div class=\"outline margin center width50\" style=\"margin: 0px auto 16px;\">
		<div class=\"errort\"><strong>".__("Confirm deletion of \"{0}\"")."</strong></div>
		<div class=\"errorc cell2\">
			<form action=\"editfora.php\" method=\"post\">
				<input type=\"submit\" name=\"action\" value=\"".__("Yes, do as I say.")."\" />
				<input type=\"hidden\" name=\"fid\" value=\"{1}\" />
			</form>
		</div>
	</div>
", $forum['title'], (int)$forum['id']);
}
elseif($_POST['action'] == __("Yes, do as I say."))
{
	$qForum = "delete from forums where id=".(int)$_POST['fid'];
	$rForum = Query($qForum);
	Alert(__("Forum removed."), __("Notice"));
}
elseif($_POST['action'] == __("Edit"))
{
	$qForum = "update forums set title='".justEscape($_POST['title'])."', description='".justEscape($_POST['description'])."' where id=".(int)$_POST['fid']." limit 1";
	$rForum = Query($qForum);
	Alert(__("Forum edited."), __("Notice"));
}

$thelist = "";
$qCategories = "select * from categories";
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	while($category = Fetch($rCategories))
	{
		$qFora = "select * from forums where catid=".(int)$category['id']." order by forder";
		$rFora = Query($qFora);
		if(NumRows($rFora))
		{
			while($forum = Fetch($rFora))
			{
				$localMods = "";
				$qMods = "select * from forummods where forum=".(int)$forum['id'];
				$rMods = Query($qMods);
				if(NumRows($rMods))
				{
					while($mod = Fetch($rMods))
					{
						$qMod = "select name, id, powerlevel, sex from users where id=".$mod['user'];
						$rMod = Query($qMod);
						$mod2 = Fetch($rMod);
						$localMods .= UserLink($mod2).", ";
					}
					$localMods = __("Moderated by:")." ".substr($localMods,0,strlen($localMods)-2);
				}
				else
					$localMods = __("No mods");

				$thelist .= format(
"
		<div class=\"errorc left cell1\" style=\"clear: both; overflow: auto;\">

			<div style=\"float: left; width: 60%;\">
				<form action=\"editfora.php\" method=\"post\">
					<input type=\"text\" name=\"title\" value=\"{0}\" style=\"width: 70%;\" />
					<input type=\"submit\" name=\"action\" value=\"".__("Edit")."\" /><br />
					<input type=\"text\" name=\"description\" style=\"width: 90%;\" value=\"{1}\" />
					<input type=\"hidden\" name=\"fid\" value=\"{2}\" /><br/>
					<small>{3} (<a href=\"managemods.php\">".__("Edit")."</a>) &bull; ".__("{4} thread(s), {5} post(s).")."</small>
				</form>
			</div>

			<form action=\"editfora.php\" method=\"post\">
				{7}
				 <input type=\"text\" name=\"order\" value=\"{6}\" size=\"2\" maxlength=\"2\" /><br />
				<input type=\"submit\" name=\"action\" value=\"".__("Move")."\" /> 
				<input type=\"submit\" name=\"action\" value=\"".__("Remove")."\" />
				<input type=\"hidden\" name=\"fid\" value=\"{2}\" />
			</form>

		</div>
",	htmlval($forum['title']), htmlval($forum['description']), $forum['id'], $localMods,
	$forum['numthreads'], $forum['numposts'], $forum['forder'],
	MakeOptions($forum['catid']));
			}
		}
	}
	write(
"
	<div class=\"outline width50 margin\">
		<div class=\"errort\"><strong>".__("Forum list")."</strong></div>
		{0}
	</div>
",	$thelist);
}

write(
"
	<form action=\"editfora.php\" method=\"post\">
		<div class=\"outline width50 margin\">
			<div class=\"errort\"><strong>".__("Add a Forum")."</strong></div>
			<div class=\"errorc left cell1\" style=\"clear: both; overflow: auto;\">
				<div style=\"float: left; width: 60%;\">
					<input type=\"text\" name=\"title\" style=\"width: 70%;\" /><br/>
					<input type=\"text\" name=\"description\" style=\"width: 90%;\" />
				</div>
				{0}
 				<input type=\"text\" name=\"order\" value=\"0\" size=\"2\" maxlength=\"2\" /> 
				{1}
				<input type=\"submit\" name=\"action\" value=\"Add\" /> 
			</div>
		</div>
	</form>

	<p>
		".__("For more complex things, try PMA. This is just a toy-like quick access.")."
	</p>
",	MakeOptions(-1), $levels);

function MakeOptions($catid)
{
	$sel[$catid] = " selected=\"true\"";
	$qFora = "select id,name from categories";
	$rFora = Query($qFora);
	$result = "<select name=\"category\" size=\"1\">";
	while($forum = Fetch($rFora))
		$result .= "<option value=\"".$forum['id']."\"".$sel[$forum['id']]."\>".$forum['name']."</option>";
	$result .= "</select>";
	return $result;
}

?>
