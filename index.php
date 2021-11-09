<?php
$noAutoHeader = TRUE;
if(isset($_SERVER['HTTP_X_PURPOSE']) && $_SERVER['HTTP_X_PURPOSE'] == "preview")
	die(header("Location: speeddial.php"));

if(isset($_GET['fid']) && (int)$_GET['fid'] > 0 && !isset($_GET['action']))
	die(header("Location: forum.php?id=".(int)$_GET['fid']));
else if(isset($_GET['tid']) && (int)$_GET['tid'] > 0)
	die(header("Location: thread.php?id=".(int)$_GET['tid']));
else if(isset($_GET['uid']) && (int)$_GET['uid'] > 0)
	die(header("Location: profile.php?id=".(int)$_GET['uid']));
else if(isset($_GET['pid']) && (int)$_GET['pid'] > 0)
	die(header("Location: thread.php?pid=".(int)$_GET['pid']."#".(int)$_GET['pid']));

include("lib/common.php");

$numThreads = FetchResult("select count(*) from threads");
$numPosts = FetchResult("select count(*) from posts");
//$stats = Plural($numThreads, "thread")." and ".Plural($numPosts,"post")." total";
$stats = Format(__("{0} and {1} total"), Plural($numThreads, __("thread")), Plural($numPosts, __("post")));

$newToday = FetchResult("select count(*) from posts where date > ".(time() - 86400));
$newLastHour = FetchResult("select count(*) from posts where date > ".(time() - 3600));
$stats .= "<br />".format(__("{0} today, {1} last hour"), Plural($newToday, __("new post")), $newLastHour);

$numUsers = FetchResult("select count(*) from users");
$numActive = FetchResult("select count(*) from users where lastposttime > ".(time() - 2592000)); //30 days
$percent = $numUsers ? ceil((100 / $numUsers) * $numActive) : 0;
$rLastUser = Query("select id,name,displayname,powerlevel,sex from users order by regdate desc limit 1");
$lastUser = Fetch($rLastUser);
$last = format(__("{0}, {1} active ({2}%)"), Plural($numUsers, __("registered user")), $numActive, $percent)."<br />".format(__("Newest: {0}"), UserLink($lastUser));

$onlineUsers = OnlineUsers();

$pl = $loguser['powerlevel'];
if($pl < 0) $pl = 0;

if($loguserid && ($_GET['action'] == "markallread" || $_GET['action'] == "markasread" && isset($_GET['fid'])))
{
	$where = ($_GET['action'] == 'markallread') ? "" : " WHERE threads.forum=".(int)$_GET['fid'];	
	Query("REPLACE INTO threadsread (id,thread,date) SELECT ".$loguserid.", threads.id, ".time()." FROM threads".$where);
	die(header('Location: index.php'));
}

// Mega-Mario: could be optimized to
// $rBirthdays = Query("select birthday, id, name, displayname, powerlevel, sex from users where birthday>0 and from_unixtime(birthday, '%c-%e')='".date('n-j')."' order by name");
// but then I don't know about birthday timezones and all
// and especially why we're using gmdate()
$rBirthdays = Query("select birthday, id, name, displayname, powerlevel, sex from users where birthday > 0 order by name");
$birthdays = array();
while($user = Fetch($rBirthdays))
{
	$bucket = "userMangler"; include("./lib/pluginloader.php");
	$b = $user['birthday'];
	if(gmdate("m-d", $b) == gmdate("m-d"))
	{
		$y = gmdate("Y") - gmdate("Y", $b);
		$birthdays[] = UserLink($user)." (".$y.")";
	}
}
if(count($birthdays))
	$birthdaysToday = implode(", ", $birthdays);

include("lib/header.php");

if(!$noAjax)
	write(
"
	<script type=\"text/javascript\">
		window.addEventListener(\"load\",  startOnlineUsers, false);
		window.addEventListener(\"load\",  startNewMarkers, false);
	</script>
");
write(
"
	<style type=\"text/css\">
		.ignored
		{
			opacity: 0.5;
		}
	</style>

	<div class=\"outline margin width100 smallFonts\" style=\"overflow: auto;\">
		<div class=\"header0 cell2 center\" style=\"overflow: auto;\">
			<div style=\"float: left; width: 25%;\">&nbsp;<br />&nbsp;</div>
			<div style=\"float: right; width: 25%;\">{1}</div>
			<div class=\"center\">
				{0}
			</div>
		</div>
",	$stats, $last);

if($birthdaysToday)
	write("
		<div class=\"header1 cell0\" style=\"border-top: 0px; text-align: center\">
			".__("Birthdays today:")." {0}
		</div>", $birthdaysToday);
write("	</div>");

DoPrivateMessageBar();
$bucket = "userBar"; include("./lib/pluginloader.php");
if($rssBar)
{
	write("
	<div style=\"float: left; width: {1}px;\">&nbsp;</div>
	<div id=\"rss\">
		{0}
	</div>
", $rssBar, $rssWidth + 4);
}

write(
"
	<div class=\"header0 cell1 center smallFonts outline margin\" style=\"overflow: auto;\">
		&nbsp;
		<span id=\"onlineUsers\">
			{0}
		</span>
		&nbsp;
	</div>
",	$onlineUsers);

$bucket = "topBar"; include("./lib/pluginloader.php");

$lastCatID = -1;
$rFora = Query("	SELECT f.*, 
						c.name cname,
						".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
						(SELECT COUNT(*) FROM threads t".($loguserid ? " LEFT JOIN threadsread tr ON tr.thread=t.id AND tr.id=".$loguserid : "")."
							WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew,
						lu.id luid, lu.name luname, lu.displayname ludisplayname, lu.powerlevel lupowerlevel, lu.sex lusex
					FROM forums f
						LEFT JOIN categories c ON c.id=f.catid
						".($loguserid ? "LEFT JOIN ignoredforums i ON i.fid=f.id AND i.uid=".$loguserid : "")."
						LEFT JOIN users lu ON lu.id=f.lastpostuser
					WHERE c.minpower<=".$pl." AND f.minpower<=".$pl.(($pl < 1) ? " AND f.hidden=0" : '')."
					ORDER BY c.corder, c.id, f.forder, f.id");

$rMods = Query("SELECT m.forum, u.id, u.name, u.displayname, u.powerlevel, u.sex FROM forummods m LEFT JOIN users u ON m.user=u.id");
$mods = array();
while($mod = Fetch($rMods))
	$mods[$mod['forum']][] = $mod;

$theList = "";
while($forum = Fetch($rFora))
{
	$skipThisOne = false;
	$bucket = "forumListMangler"; include("./lib/pluginloader.php");
	if($skipThisOne)
		continue;

	if($forum['catid'] != $lastCatID)
	{
		$lastCatID = $forum['catid'];
		$theList .= format(
"
		<tr class=\"header0\">
			<th colspan=\"5\">
				{0}
			</th>
		</tr>
", $forum['cname']);
	}

	$forum['description'] = str_replace("[trash]","",$forum['description']);
	$newstuff = 0;
	$NewIcon = "";
	$localMods = "";

	$newstuff = $forum['ignored'] ? 0 : $forum['numnew'];
	$ignoreClass = $forum['ignored'] ? " class=\"ignored\"" : "";

	if ($newstuff > 0)
		$NewIcon = "<img src=\"img/status/new.png\" alt=\"New!\"/>".$newstuff;

	if ($mods[$forum['id']])
	{
		foreach($mods[$forum['id']] as $user)
		{
			$bucket = "userMangler"; include("./lib/pluginloader.php");
			$localMods .= UserLink($user). ", ";
		}
	}

	if($localMods)
		$localMods = "<br /><small>".__("Moderated by:")." ".substr($localMods,0,strlen($localMods)-2)."</small>";

	if($forum['lastpostdate'])
	{
		$user = array('id'=>$forum['luid'], 'name'=>$forum['luname'], 'displayname'=>$forum['ludisplayname'], 'powerlevel'=>$forum['lupowerlevel'], 'sex'=>$forum['lusex']);
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		
		$lastLink = "";
		if($forum['lastpostid'])
			$lastLink = "<a href=\"thread.php?pid=".$forum['lastpostid']."#".$forum['lastpostid']."\">&raquo;</a>";
		$lastLink = format("<span class=\"nom\">{0}<br />".__("by")." </span>{1} {2}", cdate($dateformat, $forum['lastpostdate']), UserLink($user), $lastLink);
	}
	else
		$lastLink = "----";


	$theList .= format(
"
		<tr class=\"cell1\">
			<td class=\"cell2 threadIcon newMarker\">
				{0}
			</td>
			<td>
				<h4{8}>
					<a href=\"forum.php?id={1}\">
						{2}
					</a>
				</h4>
				<span{8} class=\"nom\">
					{3}
					{4}
				</span>
			</td>
			<td class=\"center cell2\">
				{5}
			</td>
			<td class=\"center cell2\">
				{6}
			</td>
			<td class=\"smallFonts center\">
				{7}
			</td>
		</tr>
",	$NewIcon, $forum['id'], $forum['title'], $forum['description'], $localMods,
	$forum['numthreads'], $forum['numposts'], $lastLink, $ignoreClass);
}
write(
"
<table class=\"outline margin\" id=\"mainTable\">
	<tr class=\"header1\">
		<th style=\"width: 20px\"></th>
		<th style=\"width: 75%\">".__("Forum title")."</th>
		<th>".__("Threads")."</th>
		<th>".__("Posts")."</th>
		<th style=\"width: 15%\">".__("Last Post")."</th>
	</tr>
	{0}
</table>
",	$theList);

?>
