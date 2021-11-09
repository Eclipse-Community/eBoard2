<?php
//  AcmlmBoard XD - Posts by user viewer
//  Access: all

include("lib/common.php");

AssertForbidden("listPosts");

if(!isset($_GET['id']))
	Kill(__("User ID unspecified."));

$id = (int)$_GET['id'];

$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill(__("Unknown user ID."));
$bucket = "userMangler"; include("./lib/pluginloader.php");

$total = __("Post list");

$total = $user['posts'];
$ppp = $loguser['postsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$ppp) $ppp = 25;

$minpower = $loguser['powerlevel'];
if($minpower < 0)
	$minpower = 0;

$qPosts = "select ";
$qPosts .=
	"posts.thread, posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.signsep, users.globalblock, users.lastposttime, users.lastactivity, users.regdate";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where users.id=".$id." order by date asc limit ".$from.", ".$ppp;

$rPosts = Query($qPosts);
$numonpage = NumRows($rPosts);

for($i = $ppp; $i < $total; $i+=$ppp)
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"listposts.php?id=".$id."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"listposts.php?id=".$id."\">1</a>".$pagelinks;
	write("<div class=\"smallFonts pages\">"._("Pages:")." {0}</div>", $pagelinks);
}

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
		$qThread = "select * from threads where id=".$post['thread'];
		$rThread = Query($qThread);
		$thread = Fetch($rThread);

		$qForum = "select * from forums where id=".$thread['forum'];
		$rForum = Query($qForum);
		$forum = Fetch($rForum);

		$qCategory = "select * from categories where id=".$forum['catid'];
		$rCategory = Query($qCategory);
		$category = Fetch($rCategory);
		
		if($forum['minpower'] > $minpower || $catgory['minpower'] > $minpower)
			continue;
			
		$post['threadname'] = $thread['title'];

		MakePost($post, $thread, -2);
	}
}

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

MakeCrumbs(array(__("Main")=>"./", $user['name']=>"profile.php?id=".$id, __("List of posts")=>""), $links);

?>
