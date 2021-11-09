<?php
$noAutoHeader = TRUE;
$noViewCount = TRUE;
$noOnlineUsers = TRUE;
$noFooter = TRUE;
$ajax = TRUE;
include("lib/common.php");

header("Cache-Control: no-cache");

$action = $_GET['a'];
$id = (int)$_GET['id'];
$hideTricks = " <a href=\"javascript:void(0)\" onclick=\"hideTricks(".$id.")\">".__("Back")."</a>";
if($action == "q")	//Quote
{
	$qQuote = "	select 
					p.id, p.deleted, pt.text,
					f.minpower,
					u.name poster
				from posts p
					left join posts_text pt on pt.pid = p.id and pt.revision = p.currentrevision 
					left join threads t on t.id=p.thread
					left join forums f on f.id=t.forum
					left join users u on u.id=p.user
				where p.id=".$id;
	$rQuote = Query($qQuote);
	
	if(!NumRows($rQuote))
		die(__("Unknown post ID."));

	$quote = Fetch($rQuote);

	//SPY CHECK!
	//Do we need to translate this line? It's not even displayed in its true form ._.
	if($quote['minpower'] > $loguser['powerlevel'])
		$quote['text'] = str_rot13("Pools closed due to not enough power. Prosecutors will be violated.");
		
	if ($quote['deleted'])
		$quote['text'] = __("Post is deleted");

	$reply = "[quote=\"".$quote['poster']."\" id=\"".$quote['id']."\"]".$quote['text']."[/quote]";
	$reply = str_replace("/me", "[b]* ".htmlval($quote['poster'])."[/b]", $reply);
	die($reply);
}
else if ($action == 'rp') // retrieve post
{
	$qPost = "	select 
					posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts.thread, 
					posts_text.text, posts_text.text, posts_text.revision, users.id as uid, 
					forums.id fid, 
					users.name, users.displayname, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.signsep, users.globalblock, users.lastposttime, users.lastactivity, users.regdate
				from 
					posts 
					left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision 
					left join users on users.id = posts.user
					left join threads on threads.id=posts.thread
					left join forums on forums.id=threads.forum
				where posts.id=".$id;
	$rPost = Query($qPost);
	if (!NumRows($rPost))
		die(__("Unknown post ID."));
	$post = Fetch($rPost);
		
	if (!CanMod($loguserid, $post['fid']))
		die(__("No."));
		
	if (isset($_GET['o']))
	{
		$post['deleted'] = 0;
		$post['unfoldhax'] = 1;
	}
	die(MakePost($post, $post['thread'], $post['fid']));
}
else if($action == "ou")	//Online Users
{
	die(OnlineUsers((int)$_GET['f'], false));
}
else if($action == "tf")	//Theme File
{
	include("css/themelist.php");
	$theme = $_GET['t'];
	if (!$themes[$theme]) die("css/default.css|img/themes/default/logo.png");
	$themeFile = "css/".$theme.".css";
	if(!file_exists($themeFile))
	{
		$themeFile = "css/".$theme.".php";
		if(!file_exists($themeFile))
			$themeFile = "css/default.css";
	}
	$logopic = "img/themes/default/logo.png";
	if(file_exists("img/themes/".$theme."/logo.png"))
		$logopic = "img/themes/".$theme."/logo.png";
	die($themeFile."|".$logopic);
}
else if($action == "ni")	//New Indicators
{
	$pl = $loguser['powerlevel'];

	$threadsRead = array();
	$rThreadsRead = Query("select id, lastpostdate, forum from threads");
	while($trd = Fetch($rThreadsRead))
		$threadsRead[$trd['id']] = $trd;

	$ignored = array();
	if($loguserid)
	{
		$rIgnores = Query("select fid from ignoredforums where uid=".$loguserid);
		while($ignore = Fetch($rIgnores))
			$ignored[$ignore['fid']] = TRUE;
	}

	$postreads = Query("select * from threadsread where id=".$loguserid);
	while($read1 = Fetch($postreads))
		$postread[$read1['thread']]=$read1['date'];

	$rCategories = Query("select name,minpower from categories");
	$category[] = "dummy";
	while($cat = Fetch($rCategories))
		$category[] = array("name" => $cat['name'], "minpower" => $cat['minpower']);

	$news = array();

	$rFora = Query("select f.* from forums f left join categories c on c.id=f.catid order by c.corder, c.id, f.forder");
	while($forum = Fetch($rFora))
	{
		if($category[$forum['catid']]['minpower'] > $pl)
			continue;
		if($forum['hidden'])
			continue;

		$newstuff = 0;
		if(!$ignored[$forum['id']])
			foreach($threadsRead as $trd)
				if($trd['forum'] == $forum['id'] && $trd['lastpostdate'] > $postread[$trd['id']])
					$newstuff++;
		$news[] = $newstuff;		
	}

	die(join(",", $news));
}
elseif($action == "srl")	//Show Revision List
{
	$qPost = "select currentrevision, thread from posts where id=".$id;
	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die(format(__("Unknown post ID #{0}."), $id)." ".$hideTricks);
		//die("Unknown post ID #".$id.". ".$hideTricks);

	$qThread = "select forum from threads where id=".$post['thread'];
	$rThread = Query($qThread);
	$thread = Fetch($rThread);
	$qForum = "select minpower from forums where id=".$thread['forum'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
		die(__("No.")." ".$hideTricks);

	$reply = __("Show revision:");
	for($i = 0; $i <= $post['currentrevision']; $i++)
		$reply .= " <a href=\"javascript:void(0)\" onclick=\"showRevision(".$id.",".$i.")\">".$i."</a>";
	$reply .= $hideTricks;
	die($reply);	
}
elseif($action == "sr")	//Show Revision
{

	$qPost = "select ";
	$qPost .=
		"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.signsep, users.globalblock, users.lastposttime, users.lastactivity, users.regdate, posts.thread";
	$qPost .= 
		" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = ".(int)$_GET['rev']." left join users on users.id = posts.user";
	$qPost .= " where posts_text.pid=".$id;

	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die(format(__("Unknown post ID #{0} or revision missing."), $id));

	$qThread = "select forum from threads where id=".$post['thread'];
	$rThread = Query($qThread);
	$thread = Fetch($rThread);
	$qForum = "select minpower from forums where id=".$thread['forum'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
		$post['text'] = __("No.");

	LoadBlockLayouts();
	$isBlocked = $blocklayouts[$post['uid']] /* NumRows($rBlock) */ | $post['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;
	$noSmilies = $post['options'] & 2;

	$tags = array();
	$rankHax = $post['posts'];
	if($post['num'] == "???")
		$post['num'] = $post['posts'];
	$post['posts'] = $post['num'];
	//Disable tags by commenting/removing this part.
	$tags = array
	(
		"numposts" => $post['num'],
		"numdays" => floor((time()-$post['regdate'])/86400),
		"date" => cdate($dateformat,$post['date']),
		"rank" => GetRank($post),
	);
	$bucket = "amperTags"; include("./lib/pluginloader.php");

	$post['posts'] = $rankHax;

	if($post['postheader'] && !$isBlocked)
		$postHeader = str_replace('$theme', $theme, ApplyTags(CleanUpPost($post['postheader']), $tags));

	$postText = ApplyTags(CleanUpPost($post['text'],$post['name'],$noSmilies), $tags);

	$bucket = "postMangler"; include("./lib/pluginloader.php");

	if($post['signature'] && !$isBlocked)
	{
		$postFooter = ApplyTags(CleanUpPost($post['signature']), $tags);
		if(!$post['signsep'])
			$separator = "<br />_________________________<br />";
		else
			$separator = "<br />";
	}

	$reply = $postHeader.$postText.$separator.$postFooter;

	die($reply);	
}
elseif($action == "em")	//Email
{
	$blah = FetchResult("select email from users where id=".$id." and showemail=1");
	die(htmlspecialchars($blah));
}
elseif($action == "vc")	//View Counter
{
	$blah = FetchResult("select views from misc");
	die(number_format($blah));
}

die(__("Unknown action."));
?>
