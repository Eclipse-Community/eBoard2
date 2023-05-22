<?php
//  AcmlmBoard XD - Thread display page
//  Access: all

include("lib/common.php");

if(isset($_GET['id']))
	$tid = (int)$_GET['id'];
elseif(isset($_GET['pid']))
{
	$pid = (int)$_GET['pid'];
	$qPost = "select * from posts where id=".$pid;
	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		Kill(__("Unknown post ID."));
	$tid = $post['thread'];
} else
	Kill(__("Thread ID unspecified."));
AssertForbidden("viewThread", $tid);

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill(__("Unknown thread ID."));

$fid = $thread['forum'];
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
}
else
	Kill(__("Unknown forum ID."));

$qCategories = "select * from categories where id=".$forum['catid'];
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	$category = Fetch($rCategories);
	if($category['minpower'] > $pl)
		Kill(__("You are not allowed to browse forums in this category."));
}
else
	Kill(__("Unknown category ID."));

//$thread['title'] .= " ".ParseThreadTags($thread['title']);
$tags = ParseThreadTags($thread['title']);
$thread['title'] = htmlspecialchars($thread['title']);
$title = $thread['title'];

$qViewCounter = "update threads set views=".($thread['views']+1)." where id=".$tid." limit 1";
$rViewCounter = Query($qViewCounter);

if(isset($_GET['vote']))
{
	AssertForbidden("vote");
	if(!$loguserid)
		Kill(__("You can't vote without logging in."));
	if($thread['closed'])
		Kill(__("Pool's closed!"));
	if($thread['poll'])
	{
		$vote = (int)$_GET['vote'];
		
		$token = hash('sha256', "{$vote},{$loguserid},{$salt}");
		if ($token != $_GET['token'])
			Kill(__("Invalid token."));
		
		$doublevote = FetchResult("select doublevote from poll where id=".$thread['poll']);
		if($doublevote)
		{
			//Multivote.
			$existing = FetchResult("select count(*) from pollvotes where poll=".$thread['poll']." and choice=".$vote." and user=".$loguserid);
			if ($existing)
				Query("delete from pollvotes where poll=".$thread['poll']." and choice=".$vote." and user=".$loguserid);
			else
				Query("insert into pollvotes (poll, choice, user) values (".$thread['poll'].", ".$vote.", ".$loguserid.")");
		}
		else
		{
			//Single vote only?
			//Remove any old votes by this user on this poll, then add a new one.
			Query("delete from pollvotes where poll=".$thread['poll']." and user=".$loguserid);
			Query("insert into pollvotes (poll, choice, user) values (".$thread['poll'].", ".$vote.", ".$loguserid.")");
		}
	}
	else
		Kill(__("This is not a poll."));
}

if(!$thread['sticky'] && $warnMonths > 0 && $thread['lastpostdate'] < time() - (2592000 * $warnMonths))
	$replyWarning = " onclick=\"if(!confirm('".__("Are you sure you want to reply to this old thread? This will move it to the top of the list. Please only do this if you have something new and relevant to share about this thread's topic that is not better placed in a new thread.")."')) return false;\"";
if($thread['closed'])
	$replyWarning = " onclick=\"if(!confirm('".__("This thread is actually closed. Are you sure you want to abuse your staff position to post in a closed thread?")."')) return false;\"";

if($loguser['powerlevel'] < 0)
	$links .= "<li>".__("You're banned.")."</li>";
elseif(IsAllowed("makeReply", $tid) && (!$thread['closed'] || $loguser['powerlevel'] > 2))
	$links .= "<li><a href=\"newreply.php?id=".$tid."\"".$replyWarning.">".__("Post reply")."</a></li>";
elseif(IsAllowed("makeReply", $tid))
	$links .= "<li>".__("Thread closed.")."</li>";
if(CanMod($loguserid,$forum['id']) && IsAllowed("editThread", $tid))
{
	$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");
	
	$links .= "<li><a href=\"editthread.php?id=".$tid."\">".__("Edit")."</a></li>";
	if($thread['closed'])
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=open&amp;key=".$key."\">".__("Open")."</a></li>";
	else
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=close&amp;key=".$key."\">".__("Close")."</a></li>";
	if($thread['sticky'])
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=unstick&amp;key=".$key."\">".__("Unstick")."</a></li>";
	else
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=stick&amp;key=".$key."\">".__("Stick")."</a></li>";
	$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=delete&amp;key=".$key."\" onclick=\"if(!confirm('".__("Are you sure you want to just up and delete this whole thread?")."') || !confirm('Seriously?')) return false;\">".__("Delete")."</a></li>";
	if(strpos($forum['description'],"[trash]") === FALSE)
		$links .= "<li><a href=\"editthread.php?id=".$tid."&amp;action=trash&amp;key=".$key."\">".__("Trash")."</a></li>";
}
else if($thread['user'] == $loguserid)
	$links .= "<li><a href=\"editthread.php?id=".$tid."\">".__("Edit")."</a></li>";

if($isBot)
	$links = "";

//$links = substr($links, 0, strlen($links) - 2);

$onlineUsers = OnlineUsers($fid);

DoPrivateMessageBar();

if(!$noAjax)
	write(
"
	<script type=\"text/javascript\">
		onlineFID = {0};
		window.addEventListener(\"load\",  startOnlineUsers, false);
	</script>
", $fid, $onlineUsers);

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
	<div class=\"header0 cell1 center outline smallFonts margin\" style=\"overflow: auto;\">
		&nbsp;
		<span id=\"onlineUsers\">
			{0}
		</span>
		&nbsp;
	</div>
", $onlineUsers);

write(
"
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");

MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid), $links);

if($thread['poll'])
{
	$qPoll = "select * from poll where id=".$thread['poll'];
	$rPoll = Query($qPoll);
	if(NumRows($rPoll))
	{
		$poll = Fetch($rPoll);

		$qCheck = "select * from pollvotes where poll=".$thread['poll']." and user=".$loguserid;
		$rCheck = Query($qCheck);
		if(NumRows($rCheck))
		{
			while($check = Fetch($rCheck))
				$pc[$check['choice']] = "&#x2714; "; //use &#x2605; for a star
		}

		$qVotes = "select count(*) from pollvotes where poll=".$thread['poll'];
		$totalVotes = FetchResult($qVotes);

		$qOptions = "select * from poll_choices where poll=".$thread['poll'];
		$rOptions = Query($qOptions);
		$pops = 0;
		$options = array();
		$voters = array();
		$noColors = 0;
		$defaultColors = array(
					  "#0000B6","#00B600","#00B6B6","#B60000","#B600B6","#B66700","#B6B6B6",
			"#676767","#6767FF","#67FF67","#67FFFF","#FF6767","#FF67FF","#FFFF67","#FFFFFF",);
		while($option = Fetch($rOptions))
			$options[] = $option;

		foreach($options as $option)
		{			
			if($option['color'] == "")
				$option['color'] = $defaultColors[($pops + 9) % 15];
				
			$option['choice'] = htmlspecialchars($option['choice']);

			$qVotes = "select * from pollvotes where poll=".$thread['poll']." and choice=".$pops;
			$rVotes = Query($qVotes);
			$votes = NumRows($rVotes);
			while($vote = Fetch($rVotes))
				if(!in_array($vote['user'], $voters))
					$voters[] = $vote['user'];

			$cellClass = ($cellClass+1) % 2;
			if($loguserid && !$thread['closed'] && IsAllowed("vote"))
			{
				$token = hash('sha256', "{$pops},{$loguserid},{$salt}");
				$label = format("{0} <a href=\"thread.php?id={1}&amp;vote={2}&amp;token={4}\">{3}</a>", $pc[$pops], $thread['id'], $pops, $option['choice'], $token);
			}
			else
				$label = format("{0} {1}", $pc[$pops], $option['choice']);
			
			$bar = "&nbsp;0";
			if($totalVotes > 0)
			{
				$width = 100 * ($votes / $totalVotes);
				$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", $votes, $totalVotes, $width);
				$bar = format("<div class=\"pollbar\" style=\"background: {0}; width: {1}%;\" title=\"{2}\">&nbsp;{3}</div>", $option['color'], $width, $alt, $votes);
				if($width == 0)
					$bar = "&nbsp;".$votes;
			}

			$pollLines .= format(
"
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td class=\"width75\">
				<div class=\"pollbarContainer\">
					{2}
				</div>
			</td>
		</tr>
", $cellClass, $label, $bar);
			$pops++;
		}
		$voters = count($voters);
		write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"2\">
				".__("Poll")."
			</th>
		</tr>
		<tr class=\"cell0\">
			<td colspan=\"2\">
				{1}
			</td>
		</tr>
		{2}
		<tr class=\"cell0\">
			<td colspan=\"2\" class=\"smallFonts\">
				{3}
			</td>
		</tr>
	</table>
",	$cellClass, htmlspecialchars($poll['question']), $pollLines,
	format($voters == 1 ? __("{0} user has voted so far") : __("{0} users have voted so far"), $voters));
	}
}

$qRead = "delete from threadsread where id=".$loguserid." and thread=".$tid;
$rRead = Query($qRead);
$qRead = "insert into threadsread (id,thread,date) values (".$loguserid.", ".$tid.", ".time().")";
$rRead = Query($qRead);

$activity = array();
$rActivity = Query("select user, count(*) num from posts where date > ".(time() - 86400)." group by user");
while($act = Fetch($rActivity))
	$activity[$act['user']] = $act['num'];

$total = $thread['replies'] + 1; //+1 for the OP
$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	if(isset($pid))
		//$from = (floor(FetchResult("select count(*) from posts where thread=".$tid." and id < ".$pid) / $ppp)) * $ppp;
		$from = (floor(FetchResult("SELECT COUNT(*) FROM posts WHERE thread=".$tid." AND date<=".$post['date']." AND id!=".$pid) / $ppp)) * $ppp;
	else
		$from = 0;

$qPosts = "select ";
$qPosts .=
	"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.signsep, users.globalblock, users.lastposttime, users.lastactivity, users.regdate";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where thread=".$tid." order by date asc limit ".$from.", ".$ppp;

$rPosts = Query($qPosts);
$numonpage = NumRows($rPosts);
$numPages = ceil($total / $ppp);
$page = ceil($from / $ppp) + 1;

$first = ($from) ? "<a href=\"thread.php?id=".$tid."\">&#x00AB;</a> " : "";
$prev = ($from) ? "<a href=\"thread.php?id=".$tid."&amp;from=".($from - $ppp)."\">&#x2039;</a> " : "";
$next = ($from < $total - $ppp) ? " <a href=\"thread.php?id=".$tid."&amp;from=".($from + $ppp)."\">&#x203A;</a>" : "";
$last = ($from < $total - $ppp) ? " <a href=\"thread.php?id=".$tid."&amp;from=".(($numPages * $ppp) - $ppp)."\">&#x00BB;</a>" : "";

$pageLinks = array();
for($p = $page - 5; $p < $page + 10; $p++)
{
	if($p < 1 || $p > $numPages)
		continue;
	if($p == $page || ($from == 0 && $p == 1))
		$pageLinks[] = $p;
	else
		$pageLinks[] = "<a href=\"thread.php?id=".$tid."&amp;from=".(($p-1) * $ppp)."\">".$p."</a>";
}

$pagelinks = $first.$prev.join(array_slice($pageLinks, 0, 11), " ").$next.$last;
write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
		$user = $post;
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		$post = $user;
		//$poster = $post;
		//$poster['id'] = $post['uid'];
		$post['activity'] = $activity[$post['uid']];
		$post['closed'] = $thread['closed'];
		MakePost($post, $tid, $fid);
	}
}

if($loguserid && $loguser['powerlevel'] >= $forum['minpowerreply'] && $loguser['powerlevel'] >= $category['minpower'] && (!$thread['closed'] || $loguser['powerlevel'] > 0) && !isset($replyWarning))
{
	$ninja = FetchResult("select id from posts where thread=".$tid." order by date desc limit 0, 1",0,0);
	
	//Quick reply goes here		
	if(CanMod($loguserid, $fid))
	{
		//print $thread['closed'];
		if(!$thread['closed'])
			$mod .= "<label><input type=\"checkbox\" name=\"lock\">&nbsp;".__("Close thread", 1)."</label>\n";
		else
			$mod .= "<label><input type=\"checkbox\" name=\"unlock\">&nbsp;".__("Open thread", 1)."</label>\n";
		if(!$thread['sticky'])
			$mod .= "<label><input type=\"checkbox\" name=\"stick\">&nbsp;".__("Sticky", 1)."</label>\n";
		else
			$mod .= "<label><input type=\"checkbox\" name=\"unstick\">&nbsp;".__("Unstick", 1)."</label>\n";
	}
	$moodOptions = "<option ".$moodSelects[0]."value=\"0\">".__("[Default avatar]")."</option>\n";
	$rMoods = Query("select mid, name from moodavatars where uid=".$loguserid." order by mid asc");
	while($mood = Fetch($rMoods))
		$moodOptions .= format(
"
	<option {0} value=\"{1}\">{2}</option>
",	$moodSelects[$mood['mid']], $mood['mid'], htmlval($mood['name']));

	write(
	"
	<form action=\"newreply.php\" method=\"post\">
		<input type=\"hidden\" name=\"ninja\" value=\"{0}\" />
		<table class=\"outline margin width75\" style=\"margin: 4px auto;\" id=\"quickreply\">
			<tr class=\"header1\">
				<th>
					".__("Quick Reply")."
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<textarea id=\"text\" name=\"text\" rows=\"8\" style=\"width: 98%;\">{3}</textarea>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td>
					<input type=\"submit\" name=\"action\" value=\"".__("Post")."\" /> 
					<input type=\"submit\" name=\"action\" value=\"".__("Preview")."\" />
					<select size=\"1\" name=\"mood\">
						{4}
					</select>
					<label>
						<input type=\"checkbox\" name=\"nopl\" {5} />&nbsp;".__("Disable post layout", 1)."
					</label>
					<label>
						<input type=\"checkbox\" name=\"nosm\" {6} />&nbsp;".__("Disable smilies", 1)."
					</label>
					<label>
						<input type=\"checkbox\" name=\"nobr\" {9} />&nbsp;".__("Disable auto-<br>", 1)."
					</label>
					<input type=\"hidden\" name=\"id\" value=\"{7}\" />
					{8}
				</td>
			</tr>
		</table>
	</form>
",	$ninja, htmlval($postingAsUser['name']), $_POST['password'], $prefill, $moodOptions, $nopl, $nosm, $tid, $mod, $nobr);
}

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid), $links);

?>
