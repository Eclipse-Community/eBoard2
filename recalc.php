<?php
//  AcmlmBoard XD - Report/content mismatch fixing utility
//  Access: staff

include("lib/common.php");

AssertForbidden("recalculate");

if($loguser['powerlevel'] < 1)
		Kill(__("Staff only, please."));

print "<table class=\"outline margin width50\">";

print "<tr class=\"header1\"><th>".__("Name")."</th><th>".__("Actual")."</th><th>".__("Reported")."</th><th>&nbsp;</th></tr>";

print "<tr class=\"header0\"><th colspan=\"4\">".__("Counting user's posts&hellip;")."</th></tr>";
$qUsers = "select * from users";
$rUsers = Query($qUsers);
while($user = Fetch($rUsers))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".htmlspecialchars($user['name'])."</td>";
	$qPosts = "select count(*) from posts where user=".$user['id'];
	$posts = FetchResult($qPosts);
	print "<td>".$posts."</td><td>".$user['posts']."</td>";
	print "<td style=\"background: ".($posts==$user['posts'] ? "green" : "red").";\"></td>";
	print "</tr>";

	$qUser = "update users set posts=".$posts." where id=".$user['id']." limit 1";
	$rUser = Query($qUser);
	RecalculateKarma($user['id']);
}

print "<tr class=\"header0\"><th colspan=\"4\">".__("Counting thread replies&hellip;")."</th></tr>";
$qThreads = "select * from threads";
$rThreads = Query($qThreads);
while($thread = Fetch($rThreads))
{
	$thread['title'] = htmlspecialchars($thread['title']);
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$thread['title']."</td>";
	$qPosts = "select count(*) from posts where thread=".$thread['id'];
	$posts = FetchResult($qPosts);
	print "<td>".($posts-1)."</td><td>".$thread['replies']."</td>";
	print "<td style=\"background: ".($posts-1==$thread['replies'] ? "green" : "red").";\"></td>";
	print "</tr>";

	$qThread = "update threads set replies=".($posts-1)." where id=".$thread['id']." limit 1";
	$rThread = Query($qThread);
}

print "<tr class=\"header0\"><th colspan=\"4\">".__("Counting forum threads and posts&hellip;")."</th></tr>";
$qFora = "select * from forums";
$rFora = Query($qFora);
while($forum = Fetch($rFora))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$forum['title']."</td>";
	$qThreads = "select * from threads where forum=".$forum['id'];
	$rThreads = Query($qThreads);
	$threads = NumRows($rThreads);

	$postcount = 0;
	while($thread = Fetch($rThreads))
	{
		$qPosts = "select count(*) from posts where thread=".$thread['id'];
		$posts = FetchResult($qPosts);
		$postcount += $posts;
	}
	print "<td>".$threads." / ".$postcount."</td><td>".$forum['numthreads']." / ".$forum['numposts']."</td>";
	print "<td style=\"background: ".($threads==$forum['numthreads'] && $postcount==$forum['numposts'] ? "green" : "red").";\"></td>";
	print "</tr>";

	$qForum = "update forums set numposts=".$postcount.", numthreads=".$threads." where id=".$forum['id']." limit 1";
	$rForum = Query($qForum);
}

print "<tr class=\"header0\"><th colspan=\"4\">".__("All counters reset.")."</th></tr>";
print "</table>";




$rForum = Query("select * from forums");
while($forum = Fetch($rForum))
{
	print $forum['title']."<br/>";
	$rThread = Query("select * from threads where forum = ".$forum['id']." order by lastpostdate desc");
	$first = 1;
	while($thread = Fetch($rThread))
	{
		print "&raquo; ".htmlspecialchars($thread['title'])."<br/>";
		$lastPost = Fetch(Query("select * from posts where thread = ".$thread['id']." order by date desc limit 0,1"));
		print "&raquo; &raquo; Last post ID is ".$lastPost['id']." by user #".$lastPost['user']."<br/>";
		Query("update threads set lastpostid = ".$lastPost['id'].", lastposter = ".$lastPost['user'].", lastpostdate = ".$lastPost['date']." where id = ".$thread['id']);
		if($first)
			Query("update forums set lastpostid = ".$lastPost['id'].", lastpostuser = ".$lastPost['user'].", lastpostdate = ".$lastPost['date']." where id = ".$forum['id']);
		$first = 0;
	}
}

?>
