<?php
//  AcmlmBoard XD - Member list page
//  Access: all

include("lib/common.php");

$title = __("Member list");

AssertForbidden("viewMembers");

$tpp = $loguser['threadsperpage'];
if($tpp<1) $tpp=50;

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(isset($dir)) unset($dir);
if(isset($_GET['dir']))
{
	$dir = $_GET['dir'];
	if($dir != "asc" && $dir != "desc")
		unset($dir);
}
$sort = $_GET['sort'];
$sex = $_GET['sex'];
if(isset($_GET['pow']) && $_GET['pow'] != "")
	$pow = (int)$_GET['pow'];
if(isset($_GET['letter']) && is_string($_GET['letter']))
	$letter = $_GET['letter'][0];
else
	$letter = "";
$order = "";
$where = "";

switch($sort)
{
	case "id": $order = "id ".(isset($dir) ? $dir : "asc"); break;
	case "name": $order = "name ".(isset($dir) ? $dir : "asc"); break;
	case "reg": $order = "regdate ".(isset($dir) ? $dir : "desc"); break;
	case "karma": $order = "karma ".(isset($dir) ? $dir : "desc"); break;
	default: $order="posts ".(isset($dir) ? $dir : "desc");
}

switch($sex)
{
	case "m": $where = "sex=0"; break;
	case "f": $where = "sex=1"; break;
	case "n": $where = "sex=2"; break;
	default: $where = "1";
}

if(isset($pow))
	$where.= " and powerlevel=".$pow;
if($letter != "")
{
	if($letter == "@")	//I can't figure it out. Anybody else?
		$where.= " and substring(name, 1,1) regexp '[:punct:]' or substring(displayname, 1,1) regexp '[:punct:]'";
	if($letter == "#")
		$where.= " and substring(name, 1,1) regexp '[0-9]' or substring(displayname, 1,1) regexp '[0-9]'";
	else
		$where.= " and name like '".$letter."%' or displayname like '".$letter."%'";
}

if(!(isset($pow) && $pow == 5))
	$where.= " and powerlevel < 5";

$numUsers = FetchResult("select count(*) from users where ".$where, 0, 0);

$qUsers = "select * from users where ".$where." order by ".$order.", name asc limit ".$from.", ".$tpp;
$rUsers = Query($qUsers);

$numonpage = NumRows($rUsers);
for($i = $tpp; $i < $numUsers; $i+=$tpp)
{
	if($i == $from)
		$pagelinks .= " ".(($i/$tpp)+1);
	else
		$pagelinks .= " ".mlink($sort,$sex,$pow,$tpp,$letter,$dir,$i).(($i/$tpp)+1)."</a>";
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = "1".$pagelinks;
	else
		$pagelinks = mlink($sort,$sex,$pow,$tpp,$letter,$dir,0)."1</a>".$pagelinks;
}

//$alphabet .= "<li>".mlink($sort,$sex,$pow,$tpp,"@",$dir)."@</a></li>\n";
$alphabet .= "<li>".mlink($sort,$sex,$pow,$tpp,"%23",$dir)."#</a></li>\n";
for($l = 0; $l < 26; $l++)
{
	$let = chr(65+$l);
	$alphabet .= "<li>".mlink($sort,$sex,$pow,$tpp,$let,$dir).$let."</a></li>\n";
}
$alphabet .= "<li>".mlink($sort,$sex,$pow,$tpp,"",$dir)."All</a></li>\n";

write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"8\">
				".__("Options")."
			</th>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"8\">
				".__("{0} found.")."
			</td>
		</tr>
",	Plural($numUsers, __("user")));
if (!$isBot)
{
	write(
"
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Sort by")."
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{1}
					</li>
					<li>
						{2}
					</li>
					<li>
						{3}
					</li>
					<li>
						{4}
					</li>
					<li>
						{5}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Order")."
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{6}
					</li>
					<li>
						{7}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Sex")."
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{8}
					</li>
					<li>
						{9}
					</li>
					<li>
						{10}
					</li>
					<li>
						{11}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Power")."
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					<li>
						{12}
					</li>
					<li>
						{13}
					</li>
					<li>
						{14}
					</li>
					<li>
						{15}
					</li>
					<li>
						{16}
					</li>
					<li>
						{17}
					</li>
					<li>
						{18}
					</li>
				</ul>
			</td>
		</tr>
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Name")."
			</td>
			<td colspan=\"6\">
				<ul class=\"pipemenu\">
					{19}
				</ul>
			</td>
		</tr>
",	null,

	mlink(""     ,$sex,$pow,$tpp,$letter,$dir).__("Posts")."</a>",
	mlink("id"   ,$sex,$pow,$tpp,$letter).__("ID")."</a>",
	mlink("name" ,$sex,$pow,$tpp,$letter,$dir).__("Username")."</a>",
	mlink("karma",$sex,$pow,$tpp,$letter,$dir).__("Karma")."</a>",
	mlink("reg"  ,$sex,$pow,$tpp,$letter,$dir).__("Registration date")."</a>",

	mlink($sort,$sex,$pow,$tpp,$letter,"asc").__("Ascending")."</a>",
	mlink($sort,$sex,$pow,$tpp,$letter,"desc").__("Descending")."</a>",

	mlink($sort,"m",$pow,$tpp,$letter,$dir).__("Male")."</a>",
	mlink($sort,"f",$pow,$tpp,$letter,$dir).__("Female")."</a>",
	mlink($sort,"n",$pow,$tpp,$letter,$dir).__("N/A")."</a>",
	mlink($sort,"", $pow,$tpp,$letter,$dir).__("All")."</a>",
	
	mlink($sort,$sex,"-1",$tpp,$letter,$dir).__("Banned")."</a>",
	mlink($sort,$sex, "0",$tpp,$letter,$dir).__("Normal")."</a>",
	mlink($sort,$sex, "1",$tpp,$letter,$dir).__("Local moderator")."</a>", 
	mlink($sort,$sex, "2",$tpp,$letter,$dir).__("Full moderator")."</a>",
	mlink($sort,$sex, "3",$tpp,$letter,$dir).__("Administrator")."</a>",
	mlink($sort,$sex, "4",$tpp,$letter,$dir).__("Root")."</a>",
	mlink($sort,$sex, "", $tpp,$letter,$dir).__("All")."</a>",
	
	$alphabet
);
}

if($pagelinks)
{
	write(
"
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Page")."
			</td>
			<td colspan=\"6\">
				{0}
			</td>
		</tr>
",	$pagelinks);
}

$memberList = "";
if($numUsers)
{
	while($user = Fetch($rUsers))
	{
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		$daysKnown = (time()-$user['regdate'])/86400;
		$user['average'] = sprintf("%1.02f", $user['posts'] / $daysKnown);

		$userPic = "";
		if($user['picture'] && $hacks['themenames'] != 3)
			$userPic = "<img src=\"".str_replace("img/avatars/", "img/avatars/", $user['picture'])."\" alt=\"\" style=\"width: 60px;\" />";

		$cellClass = ($cellClass+1) % 2;
		$memberList .= format(
"
		<tr class=\"cell{0}\">
			<td>{1}</td>
			<td>{2}</td>
			<td>{3}</td>
			<td>{4}</td>
			<td>{5}</td>
			<td>{6}</td>
			<td>{7}</td>
			<td>{8}</td>
		</tr>
",	$cellClass, $user['id'], $userPic, UserLink($user), $user['posts'],
	$user['average'], $user['karma'],
	($user['birthday'] ? cdate("M jS", $user['birthday']) : "&nbsp;"),
	cdate("M jS Y", $user['regdate'])
	);
	}
} else
{
	$memberList = format(
"
		<tr class=\"cell0\">
			<td colspan=\"8\">
				".__("Nothing here.")."
			</td>
		</tr>
");
}

write(
"
		<tr class=\"header1\">
			<th style=\"width: 30px; \">#</th>
			<th style=\"width: 62px; \">".__("Picture")."</th>
			<th>".__("Name")."</th>
			<th style=\"width: 50px; \">".__("Posts")."</th>
			<th style=\"width: 50px; \">".__("Average")."</th>
			<th style=\"width: 50px; \">".__("Karma")."</th>
			<th style=\"width: 80px; \">".__("Birthday")."</th>
			<th style=\"width: 130px; \">".__("Registered on")."</th>
		</tr>
		{0}
",	$memberList);

if($pagelinks)
{
	write(
"
		<tr class=\"cell2 smallFonts\">
			<td colspan=\"2\">
				".__("Page")."
			</td>
			<td colspan=\"6\">
				{0}
			</td>
		</tr>
",	$pagelinks);
}

write("
	</table>
");

function mlink($sort,$sex,$pow,$tpp,$letter="",$dir="",$from=1)
{
	return "<a href=\"memberlist.php?"
			.($sort   ?"sort=$sort":"")
			.($sex    ?"&amp;sex=$sex":"")
			.(isset($pow)?"&amp;pow=$pow":"")
			.($letter!=""?"&amp;letter=$letter":"")
			.($dir   ?"&dir=$dir":"")
			.($from!=1?"&amp;from=$from":"")
			."\" rel=\"nofollow\">";
}

?>
