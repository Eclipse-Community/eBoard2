<?php
//  AcmlmBoard XD - Thread listing page
//  Access: all

include("lib/common.php");

if(!isset($_GET['id']))
	Kill(__("Forum ID unspecified."));

$fid = (int)$_GET['id'];
AssertForbidden("viewForum", $fid);

$pl = $loguser['powerlevel'];
if($pl < 0) $pl = 0;

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
{
	$forum = Fetch($rFora);
	if($forum['minpower'] > $pl)
		Kill(__("You are not allowed to browse this forum."));
} else
	Kill(__("Unknown forum ID."));

$title = $forum['title'];

$qCat = "select * from categories where id=".$forum['catid'];
$rCat = Query($qCat);
if(NumRows($rCat))
{
	$cat = Fetch($rCat);
	if($cat['minpower'] > $pl)
		Kill(__("You are not allowed to see this category."));
} else
	Kill(__("Unknown category ID."));

//Autolock system
if($autoLockMonths > 0)
{
	$locktime = time() - (2592000 * $autoLockMonths);
	Query("UPDATE threads SET closed=1 WHERE forum=".$fid." AND closed=0 AND lastpostdate<".$locktime);
}
//</autolock>

$isIgnored = FetchResult("select count(*) from ignoredforums where uid=".$loguserid." and fid=".$fid, 0, 0) == 1;
if(isset($_GET['ignore']))
{
	if(!$isIgnored)
	{
		Query("insert into ignoredforums values (".$loguserid.", ".$fid.")");
		Alert(__("Forum ignored. You will no longer see any \"New\" markers for this forum."));
	}
}
else if(isset($_GET['unignore']))
{
	if($isIgnored)
	{
		Query("delete from ignoredforums where uid=".$loguserid." and fid=".$fid);
		Alert(__("Forum unignored."));
	}
}

$isIgnored = FetchResult("select count(*) from ignoredforums where uid=".$loguserid." and fid=".$fid, 0, 0) == 1;
if($loguserid && $forum['minpowerthread'] <= $loguser['powerlevel'])
{
	if($isIgnored)
		$links .= "<li><a href=\"forum.php?id=".$fid."&amp;unignore\">".__("Unignore Forum")."</a></li>";
	else
		$links .= "<li><a href=\"forum.php?id=".$fid."&amp;ignore\">".__("Ignore Forum")."</a></li>";

	$links .= "<li><a href=\"newthread.php?id=".$fid."\">".__("Post Thread")."</a></li>";
	$links .= "<li><a href=\"newthread.php?id=".$fid."&amp;poll=1\">".__("Post Poll")."</a></li>";
}

DoPrivateMessageBar();
$bucket = "userBar"; include("./lib/pluginloader.php");

$onlineUsers = OnlineUsers($fid);

if(!$noAjax)
{
	write(
"
	<script type=\"text/javascript\">
		onlineFID = {0};
		window.addEventListener(\"load\",  startOnlineUsers, false);
	</script>
",	$fid, $onlineUsers);
}

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
	<div class=\"header0 cell1 center outline smallFonts margin\" style=\"overflow: auto;\">
		&nbsp;
		<span id=\"onlineUsers\">
			{0}
		</span>
		&nbsp;
	</div>
", $onlineUsers, $rssBar, $rssWidth);

MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid), $links);

$total = $forum['numthreads'];
$tpp = $loguser['threadsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$tpp) $tpp = 50;

$rThreads = Query("	SELECT 
						t.*,
						".($loguserid ? "tr.date readdate," : '')."
						su.id suid, su.name suname, su.displayname sudisplayname, su.powerlevel supowerlevel, su.sex susex,
						lu.id luid, lu.name luname, lu.displayname ludisplayname, lu.powerlevel lupowerlevel, lu.sex lusex
					FROM 
						threads t
						".($loguserid ? "LEFT JOIN threadsread tr ON tr.thread=t.id AND tr.id=".$loguserid : '')."
						LEFT JOIN users su ON su.id=t.user
						LEFT JOIN users lu ON lu.id=t.lastposter
					WHERE forum=".$fid." 
					ORDER BY sticky DESC, lastpostdate DESC LIMIT ".$from.", ".$tpp);

$numonpage = NumRows($rThreads);

for($i = $tpp; $i < $total; $i+=$tpp)
	if($i == $from)
		$pagelinks .= " ".(($i/$tpp)+1);
	else
		$pagelinks .= " <a href=\"forum.php?id=".$fid."&amp;from=".$i."\">".(($i/$tpp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"forum.php?id=".$fid."\">1</a>".$pagelinks;
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);
}

$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;

$bucket = "topBar"; include("./lib/pluginloader.php");

if(NumRows($rThreads))
{	
	$forumList = "";
	while($thread = Fetch($rThreads))
	{
		$user = array('id'=>$thread['suid'], 'name'=>$thread['suname'], 'displayname'=>$thread['sudisplayname'], 'powerlevel'=>$thread['supowerlevel'], 'sex'=>$thread['susex']);
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		$starter = $user;
		
		$user = array('id'=>$thread['luid'], 'name'=>$thread['luname'], 'displayname'=>$thread['ludisplayname'], 'powerlevel'=>$thread['lupowerlevel'], 'sex'=>$thread['lusex']);
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		$last = $user;

		$tags = ParseThreadTags($thread['title']);

		$NewIcon = "";
		$newstuff = 0;
		if($thread['closed'])
			$NewIcon = "off";
		if($thread['replies'] >= $misc['hotcount'])
			$NewIcon .= "hot";
		if((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
			($loguserid && $thread['lastpostdate'] > $thread['readdate']) &&
			!$isIgnored)
		{
			$NewIcon .= "new";
			$newstuff++;
		}
		else if(!$thread['closed'] && !$thread['sticky'] && $warnMonths > 0 && $thread['lastpostdate'] < time() - (2592000 * $warnMonths))
			$NewIcon = "old";
		
		if($NewIcon)
			$NewIcon = "<img src=\"img/status/".$NewIcon.".png\" alt=\"\"/>";

		if($thread['icon'])
			$ThreadIcon = "<img src=\"".htmlspecialchars($thread['icon'])."\" alt=\"\" class=\"smiley\"/>";
		else
			$ThreadIcon = "";

		$cellClass = ($cellClass + 1) % 2;

		//if($thread['sticky'])
		//	$cellClass = 2;

		if($thread['sticky'] == 0 && $haveStickies == 1)
		{
			$haveStickies = 2;
			$forumList .= "<tr class=\"header1\"><th colspan=\"7\" style=\"height: 8px;\"></th></tr>";
		}
		if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

		$poll = ($thread['poll'] ? "<img src=\"img/poll.png\" alt=\"Poll\"/> " : "");

		$n = 4;
		$total = $thread['replies'];
		$numpages = floor($total / $ppp);
		$pl = "";
		if($numpages <= $n * 2)
		{
			for($i = 1; $i <= $numpages; $i++)
				$pl .= " <a href=\"thread.php?id=".$thread['id']."&amp;from=".($i * $ppp)."\">".($i+1)."</a>";
		}
		else
		{
			for($i = 1; $i < $n; $i++)
				$pl .= " <a href=\"thread.php?id=".$thread['id']."&amp;from=".($i * $ppp)."\">".($i+1)."</a>";
			$pl .= " &hellip; ";
			for($i = $numpages - $n + 1; $i <= $numpages; $i++)
				$pl .= " <a href=\"thread.php?id=".$thread['id']."&amp;from=".($i * $ppp)."\">".($i+1)."</a>";
		}
		if($pl)
			$pl = " <span class=\"smallFonts\">[<a href=\"thread.php?id=".$thread['id']."\">1</a>".$pl."]</span>";

		$lastLink = "";
		if($thread['lastpostid'])
			$lastLink = " <a href=\"thread.php?pid=".$thread['lastpostid']."#".$thread['lastpostid']."\">&raquo;</a>";

		$forumList .= Format(
"
		<tr class=\"cell{0}\">
			<td class=\"cell2 threadIcon\">{1}</td>
			<td class=\"threadIcon\" style=\"border-right: 0px none;\">
				{2}
			</td>
			<td style=\"border-left: 0px none;\">
				{3}
				<a href=\"thread.php?id={4}\">
					{5}
				</a>
				{6}
				{7}
			</td>
			<td class=\"center\">
				{8}
			</td>
			<td class=\"center\">
				{9}
			</td>
			<td class=\"center\">
				{10}
			</td>
			<td class=\"smallFonts center\">
				{11}<br />
				".__("by")." {12} {13}</td>
		</tr>
",	$cellClass, $NewIcon, $ThreadIcon, $poll, $thread['id'], strip_tags($thread['title']), $pl, $tags,
	UserLink($starter), $thread['replies'], $thread['views'],
	cdate($dateformat,$thread['lastpostdate']), UserLink($last), $lastLink);
	}
	Write(
"
	<table class=\"outline margin width100\">
		<tr class=\"header1\">
			<th style=\"width: 20px;\">&nbsp;</th>
			<th style=\"width: 16px;\">&nbsp;</th>
			<th style=\"width: 60%;\">".__("Title")."</th>
			<th>".__("Started by")."</th>
			<th>".__("Replies")."</th>
			<th>".__("Views")."</th>
			<th>".__("Last post")."</th>
		</tr>
		{0}
	</table>
",	$forumList);
} else
	if($forum['minpowerthread'] > $loguser['powerlevel'])
		Alert(__("You cannot start any threads here."), __("Empty forum"));
	elseif($loguserid)
		Alert(format(__("Would you like to {0}post something{1}?"), "<a href=\"newthread.php?id=".$fid."\">", "</a>"), __("Empty forum"));
	else
		Alert(format(__("{0}Log in{1} so you can post something."), "<a href=\"login.php\">", "</a>"), __("Empty forum"));

if($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid), $links);
ForumJump();


function ForumJump()
{
	global $fid, $loguser;
	
	$pl = $loguser['powerlevel'];
	if($pl < 0) $pl = 0;
	
	$lastCatID = -1;	
	$rFora = Query("	SELECT 
							f.id, f.title, f.catid,
							c.name cname
						FROM 
							forums f
							LEFT JOIN categories c ON c.id=f.catid
						WHERE c.minpower<=".$pl." AND f.minpower<=".$pl.(($pl < 1) ? " AND f.hidden=0" : '')."
						ORDER BY c.corder, c.id, f.forder");
	
	$theList = "";
	$optgroup = "";
	while($forum = Fetch($rFora))
	{
		if($forum['catid'] != $lastCatID)
		{
			$lastCatID = $forum['catid'];
			$theList .= format(
"
			{0}
			<optgroup label=\"{1}\">
", $optgroup, strip_tags($forum['cname']));
			$optgroup = "</optgroup>";
		}

		$theList .= format(
"
				<option value=\"{0}\"{2}>{1}</option>
",	$forum['id'], strip_tags($forum['title']), ($forum['id'] == $fid ? " selected=\"selected\"" : ""));
	}

	write(
"
	<label>
		".__("Forum Jump:")."
		<select onchange=\"document.location='forum.php?id='+this.options[this.selectedIndex].value;\">
			{0}
			</optgroup>
		</select>
	</label>
",	$theList);
}

?>
