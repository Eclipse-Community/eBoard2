<?php
//  AcmlmBoard XD - Post editing page
//  Access: users

include("lib/common.php");

$title = __("Edit post");

if(!$loguserid)
	Kill(__("You must be logged in to edit your posts."));

if($loguser['powerlevel'] < 0)
	Kill(__("Banned users can't edit their posts."));
	
$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill(__("Post ID unspecified."));

$pid = (int)$_GET['id'];
AssertForbidden("editPost", $pid);

$qPost = "select * from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision where id=".$pid;
$rPost = Query($qPost);
if(NumRows($rPost))
{
	$post = Fetch($rPost);
	$tid = $post['thread'];
} else
	Kill(__("Unknown post ID."));
	
if ($post['deleted'] && !CanMod($loguserid, $fid))
	Kill(__("This post has been deleted."));

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill(__("Unknown thread ID."));
AssertForbidden("viewThread", $tid);

$qFora = "select * from forums where id=".$thread['forum'];
$rFora = Query($qFora);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill(__("Unknown forum ID."));

if ($loguser['powerlevel'] < $forum['minpower'])
	Kill(__("You are not allowed to browse this forum."));
$fid = $forum['id'];
AssertForbidden("viewForum", $fid);

//-- Mark as New if last post is edited --
//print $thread['lastpostdate']."<br/>";
//print $post['date']."<br/>";
$wasLastPost = ($thread['lastpostdate'] == $post['date']);
//print (int)$wasLastPost;

$thread['title'] = htmlspecialchars($thread['title']);
$fid = $thread['forum'];

if((int)$_GET['delete'] == 1)
{
	if ($_GET['key'] != $key) Kill(__("No."));
	if(!CanMod($loguserid,$fid))
		Kill(__("You're not allowed to delete posts."));
	$qPosts = "update posts set deleted=1 where id=".$pid." limit 1";
	$rPosts = Query($qPosts);
	Redirect(__("Deleted!"), "thread.php?id=".$tid, __("the thread"));
	exit();
} elseif((int)$_GET['delete'] == 2)
{
	if ($_GET['key'] != $key) Kill(__("No."));
	if(!CanMod($loguserid,$fid))
		Kill(__("You're not allowed to undelete posts."));
	$qPosts = "update posts set deleted=0 where id=".$pid." limit 1";
	$rPosts = Query($qPosts);
	Redirect(__("Restored!"), "thread.php?id=".$tid, __("the thread"));
	exit();
}

if(!CanMod($loguserid, $fid) && $post['user'] != $loguserid)
	Kill(__("You are not allowed to edit posts."));

if($thread['closed'] && !CanMod($loguserid, $fid))
	Kill(__("This thread is closed."));
	
write("
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");

if($_POST['text'] && CheckTableBreaks($_POST['text']))
{
	$_POST['action'] = "";
	Alert(__("This post would break the board layout."), __("I'm sorry, Dave."));
}

if(!isset($_POST['action']))
{
	$_POST['nopl'] = $post['options'] & 1;
	$_POST['nosm'] = $post['options'] & 2;
	$_POST['nobr'] = $post['options'] & 4;
}

if($_POST['action'] == __("Edit"))
{
	if ($_POST['key'] != $key) Kill(__("No."));
	
	if($_POST['text'])
	{
		$post = htmlentities2(deSlashMagic($_POST['text']));
		$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
		TidyPost($post);
		$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
		$post = mysql_real_escape_string($post);

		$options = 0;
		if($_POST['nopl']) $options |= 1;
		if($_POST['nosm']) $options |= 2;
		if($_POST['nobr']) $options |= 4;

		$qRev = "select max(revision) from posts_text where pid=".$pid;
		$rRev = Query($qRev);
		$rev = Fetch($rRev);
		$rev = $rev[0]; //note: no longer a fetched row.
		$rev++;
		$qPostsText = "insert into posts_text (pid,text,revision) values (".$pid.", '".$post."', ".$rev.")";
		$rPostsText = Query($qPostsText);

		$qPosts = "update posts set options='".$options."', mood=".(int)$_POST['mood'].", currentrevision = currentrevision + 1 where id=".$pid." limit 1";
		$rPosts = Query($qPosts);

		//Update thread lastpostdate if we edited the last post
		if($wasLastPost)
		{
			$qThreads = "update threads set lastpostdate=".time()." where id=".$tid." limit 1";
			$qPosts = "update posts set date=".time()." where id=".$pid." limit 1";
			$rThreads = Query($qThreads);
			$rPosts = Query($qPosts);
		}

		if($forum['minpower'] < 1)
			Report("Post edited by [b]".$loguser['name']."[/] in [b]".$thread['title']."[/] (".$forum['title'].") -> [g]#HERE#?pid=".$pid);
		Redirect(__("Edited!"), "thread.php?pid=".$pid."#".$pid, __("the thread"));
		exit();
	}
	else
		Alert(__("Enter a message and try again."), __("Your post is empty."));
}

if($_POST['text'])
{
	//$prefill = htmlentities2(stripslashes($_POST['text']));
	$prefill = htmlentities2(deSlashMagic($_POST['text']));
	$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}

if($_POST['action'] == __("Preview"))
{
	$qUser = "select * from users where id=".$post['user'];
	$rUser = Query($qUser);
	if(NumRows($rUser))
		$user = Fetch($rUser);
	else
		Kill(__("Unknown user ID."));
	$bucket = "userMangler"; include("./lib/pluginloader.php");

	if($_POST['text'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = $post['num'];
		$previewPost['id'] = $pid;
		$previewPost['uid'] = $post['user'];
		$copies = explode(",","title,name,displayname,picture,sex,powerlevel,avatar,postheader,signature,signsep,posts,regdate,lastactivity,lastposttime,rankset");
		foreach($copies as $toCopy)
			$previewPost[$toCopy] = $user[$toCopy];
		$previewPost['options'] = 0;
		if($_POST['nopl']) $previewPost['options'] |= 1;
		if($_POST['nosm']) $previewPost['options'] |= 2;
		if($_POST['nobr']) $previewPost['options'] |= 4;
		$previewPost['mood'] = (int)$_POST['mood'];
		MakePost($previewPost, 0, $fid);
	}
	else
		Alert(__("Enter a message and try again."), __("Your post is empty."));
}

//if(!$_POST['text']) $_POST['text'] = $post['text'];
//if($_POST['text']) $prefill = htmlval(deSlashMagic($_POST['text']));
if(!$_POST['text']) $prefill = $post['text'];
else $prefill = deSlashMagic($_POST['text']);

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";
if($_POST['nosm'])
	$nosm = "checked=\"checked\"";
if($_POST['nobr'])
	$nobr = "checked=\"checked\"";

if(!isset($_POST['mood']))
	$_POST['mood'] = $post['mood'];
if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = Format("<option {0}value=\"0\">".__("[Default avatar]")."</option>\n", $moodSelects[0]);
$rMoods = Query("select mid, name from moodavatars where uid=".$post['user']." order by mid asc");
while($mood = Fetch($rMoods))
	$moodOptions .= Format("<option {0}value=\"{1}\">{2}</option>\n", $moodSelects[$mood['mid']], $mood['mid'], htmlval($mood['name']));

Write(
"
	<table style=\"width: 100%;\">
		<tr>
			<td style=\"vertical-align: top; border: none;\">
				<form action=\"editpost.php\" method=\"post\">
					<table class=\"outline margin width100\">
						<tr class=\"header1\">
							<th colspan=\"2\">
								".__("Edit Post")."
							</th>
						</tr>
						<tr class=\"cell0\">
							<td>
								".__("Post")."
							</td>
							<td>
								<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">{0}</textarea>
							</td>
						</tr>
						<tr class=\"cell2\">
							<td></td>
							<td>
								<input type=\"submit\" name=\"action\" value=\"".__("Edit")."\" /> 
								<input type=\"submit\" name=\"action\" value=\"".__("Preview")."\" />
								<select size=\"1\" name=\"mood\">
									{1}
								</select>
								<label>
									<input type=\"checkbox\" name=\"nopl\" {3} />&nbsp;".__("Disable post layout", 1)."
								</label>
								<label>
									<input type=\"checkbox\" name=\"nosm\" {4} />&nbsp;".__("Disable smilies", 1)."
								</label>
								<label>
									<input type=\"checkbox\" name=\"nobr\" {5} />&nbsp;".__("Disable auto-<br>", 1)."
								</label>
								<input type=\"hidden\" name=\"id\" value=\"{2}\" />
								<input type=\"hidden\" name=\"key\" value=\"{6}\" />
							</td>
						</tr>
					</table>
				</form>
			</td>
			<td style=\"width: 200px; vertical-align: top; border: none;\">
",	htmlval($prefill), $moodOptions, $pid, $nopl, $nosm, $nobr, $key);

DoSmileyBar();
DoPostHelp();

Write(
"
			</td>
		</tr>
	</table>
");

$qPosts = "select ";
$qPosts .=
	"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname, users.rankset, users.powerlevel, users.sex, users.posts";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where thread=".$tid." and deleted=0 order by date desc limit 0, 20";

$rPosts = Query($qPosts);
if(NumRows($rPosts))
{
	$posts = "";
	while($post = Fetch($rPosts))
	{
		$cellClass = ($cellClass+1) % 2;

		$poster = $post;
		$poster['id'] = $post['uid'];

		$nosm = $post['options'] & 2;
		$nobr = $post['options'] & 4;

		$posts .= Format(
"
		<tr>
			<td class=\"cell2\" style=\"width: 15%; vertical-align: top;\">
				{1}
			</td>
			<td class=\"cell{0}\">
				<button style=\"float: right;\" onclick=\"insertQuote({2});\">".__("Quote")."</button>
				<button style=\"float: right;\" onclick=\"insertChanLink({2});\">".__("Link")."</button>
				{3}
			</td>
		</tr>
",	$cellClass, UserLink($poster), $post['id'], CleanUpPost($post['text'], $poster['name'], $nosm, $nobr));
	}
	Write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"2\">".__("Thread review")."</th>
		</tr>
		{0}
	</table>
",	$posts);
}

MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, __("Edit post")=>""), $links);

?>
