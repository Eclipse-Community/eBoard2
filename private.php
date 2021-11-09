<?php
//  AcmlmBoard XD - Private message inbox/outbox viewer
//  Access: users

include("lib/common.php");

AssertForbidden("viewPM");

$title = "Private messages";

if(!$loguserid)
	Kill(__("You must be logged in to view your private messages."));

$user = $loguserid;
if(isset($_GET['user']) && $loguser['powerlevel'] > 2)
{
	$user = (int)$_GET['user'];
	$snoop = "&amp;snooping=1";
	$userGet = "&amp;user=".$user;
}

if(isset($_POST['action']))
{
	if($_POST['action'] == "multidel" && $_POST['delete'] && $snoop != 1)
	{
		$deleted = 0;
		foreach($_POST['delete'] as $pid => $on)
		{
			$rPM = Query("select * from pmsgs where id = ".$pid." and (userto = ".$loguserid." or userfrom = ".$loguserid.")");
			if(NumRows($rPM))
			{
				$pm = Fetch($rPM);
				$val = $pm['userto'] == $loguserid ? 2 : 1;
				$newVal = ($pm['deleted'] | $val);
				if($newVal == 3)
				{
					Query("delete from pmsgs where id = ".$pid);
					Query("delete from pmsgs_text where pid = ".$pid);
				}
				else
					Query("update pmsgs set deleted = ".$newVal." where id = ".$pid);
				$deleted++;
			}
		}
		Alert(format(__("{0} deleted."), Plural($deleted, __("private message"))));
	}
}

if(isset($_GET['del']))
{
	$pid = (int)$_GET['del'];
	$rPM = Query("select * from pmsgs where id = ".$pid." and (userto = ".$loguserid." or userfrom = ".$loguserid.")");
	if(NumRows($rPM))
	{
		$pm = Fetch($rPM);
		$val = $pm['userto'] == $loguserid ? 2 : 1;
		$newVal = ($pm['deleted'] | $val);
		if($newVal == 3)
		{
			Query("delete from pmsgs where id = ".$pid);
			Query("delete from pmsgs_text where pid = ".$pid);
		}
		else
			Query("update pmsgs set deleted = ".$newVal." where id = ".$pid);
		Alert(__("Private message deleted."));
	}
}

$whereFrom = "userfrom = ".$user;
$drafting = 0;
$deleted = 2;
if(isset($_GET['show']))
{
	$show = "&amp;show=".(int)$_GET['show'];
	if($_GET['show'] == 1)
		$deleted = 1;
	else if($_GET['show'] == 2)
		$drafting = 1;
}
else
{
	$whereFrom = "userto = ".$user;
}
$whereFrom .= " and drafting = ".$drafting;

$qTotal = "select count(*) from pmsgs where ".$whereFrom." and deleted != ".$deleted;
$total = FetchResult($qTotal);

$ppp = $loguser['postsperpage'];

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;


$links = "<ul class=\"pipemenu\"><li><a href=\"private.php".(isset($_GET['show']) ? str_replace("&amp;", "?", $userGet) . "\">".__("Show received") : "?show=1".$userGet."\">".__("Show sent"))."</a></li>";
$links .= "<li><a href=\"private.php?show=2".$userGet."\">".__("Show drafts")."</a></li>";
$links .= "<li><a href=\"sendprivate.php\">".__("Send new PM")."</a></li></ul>";

MakeCrumbs(array(__("Main")=>"./", __("Private messages")=>"private.php"), $links);

$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where ".$whereFrom." and deleted != ".$deleted." order by date desc limit ".$from.", ".$ppp;

//print $qPM;

$rPM = Query($qPM);
$numonpage = NumRows($rPM);

for($i = $ppp; $i < $total; $i+=$ppp)
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"private.php?from=".$i.$show.$userGet."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"private.php".str_replace("&amp;","?", $show).$userGet."\">1</a>".$pagelinks;
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);
}

if(NumRows($rPM))
{
	while($pm = Fetch($rPM))
	{
		$qUser = "select * from users where id = ".(isset($_GET['show']) ? $pm['userto'] : $pm['userfrom']);
		$rUser = Query($qUser);
		if(NumRows($rUser))
			$user = Fetch($rUser);

		$cellClass = ($cellClass+1) % 2;
		if(!$pm['msgread'])
			$img = "<img src=\"img/status/new.png\" alt=\"New!\" />";
		else
			$img = "";

		$sender = (NumRows($rUser) ? UserLink($user) : "???");

		$check = $snoop ? "" : "<input type=\"checkbox\" name=\"delete[{2}]\" />";

		$pms .= format(
"
		<tr class=\"cell{0}\">
			<td>
				".$check."
			</td>
			<td class=\"center\">
				{1}
			</td>
			<td>
				<a href=\"showprivate.php?id={2}{3}\">{4}</a>{7}
			</td>
			<td>
				{5}
			</td>
			<td>
				{6}
			</td>
		</tr>
",	$cellClass, $img, $pm['id'], $snoop, htmlspecialchars($pm['title']), $sender, cdate($dateformat,$pm['date']), $snoop == "" ? "<sup>&nbsp;<a href=\"private.php?del=".$pm['id'].$show."\">&#x2718;</a></sup>" : "");
	}
}
else
	$pms = format(
"
		<tr class=\"cell1\">
			<td colspan=\"6\">
				".__("There are no messages to display.")."
			</td>
		</tr>
");

write(
"
	<form method=\"post\" action=\"private.php\">
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th style=\"width: 22px;\">
				<input type=\"checkbox\" id=\"ca\" onchange=\"checkAll();\" />
			</th>
			<th style=\"width: 22px;\">&nbsp;</th>
			<th style=\"width: 75%;\">".__("Title")."</th>
			<th>{0}</th>
			<th>".__("Date")."</th>
		</tr>
		{1}
		<tr class=\"header1\">
			<th style=\"text-align: right;\" colspan=\"6\">
				<input type=\"hidden\" name=\"action\" value=\"multidel\" />
				<a href=\"javascript:void();\" onclick=\"document.forms[1].submit();\">".__("delete checked")."</a>
			</th>
		</tr>
	</table>
	</font>
", (isset($_GET['show']) ? __("To") : __("From")), $pms);

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

MakeCrumbs(array(__("Main")=>"./", __("Private messages")=>"private.php"), $links);

?>
