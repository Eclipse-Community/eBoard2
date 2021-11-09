<?php
//  AcmlmBoard XD - Forum category editing tool
//  Access: administrators

include("lib/common.php");

AssertForbidden("editCats");

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to edit the category list.");
	
$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");
if (isset($_POST['action']) && $key != $_POST['key'])
	Kill(__("No."));

elseif($_POST['action'] == "Add")
{
	$newID = FetchResult("SELECT id+1 FROM categories WHERE (SELECT COUNT(*) FROM categories c2 WHERE c2.id=categories.id+1)=0 ORDER BY id ASC LIMIT 1");
	if($newID < 1) $newID = 1;
	$qCategory = "insert into categories (id, name, corder, minpower) values (".$newID.", '".justEscape($_POST['name'])."', ".(int)$_POST['corder'].", ".(int)$_POST['minpower'].")";
	$rCategory = Query($qCategory);
	Alert("Category added.","Notice");
} elseif($_POST['action'] == "Remove")
{
	$qCategory = "select * from categories where id=".(int)$_POST['cid'];
	$rCategory = Query($qCategory);
	$category = Fetch($rCategory);
	write(
"
	<div class=\"errort\">
		<strong>Confirm deletion of \"{0}\"</strong>
	</div>
	<div class=\"errorc cell2\">
		<form action=\"editcats.php\" method=\"post\">
			<input type=\"submit\" name=\"action\" value=\"Yes, do as I say.\" />
			<input type=\"hidden\" name=\"cid\" value=\"{1}\" />
			<input type=\"hidden\" name=\"key\" value=\"{2}\" />
		</form>
	</div>
",	$category['name'], (int)$category['id'], $key);
}
elseif($_POST['action'] == "Yes, do as I say.")
{
	$qCategory = "delete from categories where id=".(int)$_POST['cid'];
	$rCategory = Query($qCategory);
	Alert("Category removed.","Notice");
}
elseif($_POST['action'] == "Edit")
{
	$qCategory = "update categories set name='".justEscape($_POST['name'])."', corder=".(int)$_POST['corder'].", minpower=".(int)$_POST['minpower']." where id=".(int)$_POST['cid']." limit 1";
	$rCategory = Query($qCategory);
	Alert("Category edited.","Notice");
}

$levels = array(-1 => "-1 - Banned", 0 => "0 - Normal user", 1 => "1 - Local Mod", 2 => "2 - Full Mod", 3 => "3 - Admin");


$cats = "";
$qCategories = "select * from categories";
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	while($category = Fetch($rCategories))
	{
		$cats .= format(
"
		<div class=\"errorc left cell0\" style=\"clear: both; overflow: auto;\">
			<form action=\"editcats.php\" method=\"post\">
				<input type=\"text\" name=\"name\" class=\"width50\" value=\"{0}\" />
				{1}
				<input type=\"text\" name=\"corder\" size=\"2\" value=\"{3}\" />
				<input type=\"submit\" name=\"action\" value=\"Edit\" />
				<input type=\"submit\" name=\"action\" value=\"Remove\" />
				<input type=\"hidden\" name=\"cid\" value=\"{2}\" />
				<input type=\"hidden\" name=\"key\" value=\"{4}\" />
			</form>
		</div>
",	htmlval($category['name']),
	MakeSelect("minpower",$category['minpower'],$levels),
	$category['id'], $category['corder'], $key);
	}
}

write(
"
	<div class=\"outline margin width50\">
		<div class=\"errort center\"><strong>Category list</strong></div>
		{0}
	</div>
	<form action=\"editcats.php\" method=\"post\">
		<div class=\"outline margin width50\">
			<div class=\"errort center\"><strong>Add a Category</strong></div>
			<div class=\"errorc left cell1\" style=\"clear: both; overflow: auto;\">
				<input type=\"text\" name=\"name\" class=\"width50\" />
				{1}
				<input type=\"text\" name=\"corder\" size=\"2\" value=\"0\" />
				<input type=\"submit\" name=\"action\" value=\"Add\" />
				<input type=\"hidden\" name=\"key\" value=\"{2}\" />
			</div>
		</div>
	</form>
	<p>
		".__("For more complex things, try PMA. This is just a toy-like quick access.")."
	</p>
",	$cats, MakeSelect("minpower",0,$levels), $key);

function MakeSelect($fieldName, $checkedIndex, $choicesList)
{
	global $id;
	$checks[$checkedIndex] = " selected=\"true\"";
	$result = "<select name=\"".$fieldName."\" size=\"1\"".$kawa.">";
	foreach($choicesList as $key=>$val)
		$result .= "<option value=\"".$key."\"".$checks[$key].">".$val."</option>";
	$result .= "</select>";
	return $result;
}

?>
