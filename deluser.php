<?php

include("lib/common.php");

if($loguser['powerlevel'] != 4)
	Kill(__("You're not the boss of me! There is nothing for you here."));

if(isset($_GET['id']))
{
	$id = (int)$_GET['id'];
	$user = Fetch(Query("SELECT * FROM users WHERE id = ".$id));
	if($user['id'] == 0)
		Kill(__("No such user."));
	if($user['powerlevel'] == 4)
		Kill(__("You cannot delete a Root user."));
	Kill(format(__("You are about to utterly destroy {0}. Are you sure?"), htmlspecialchars($user['name']))."<br/><form method=\"post\" action=\"deluser.php\"><input type=\"submit\" style=\"font-size: 150%; margin: 0.5em; padding: 0px 1em;\" name=\"action\" value=\"DO IT FAGGOT\" /><input type=\"hidden\" name=\"id\" value=\"".$id."\" /></form>", __("Oh boy."));	
}
else if(isset($_POST['id']) && isset($_POST['action']))
{
	$id = (int)$_POST['id'];
	$user = Fetch(Query("SELECT * FROM users WHERE id = ".$id));
}
else
{
	Kill(__("No user ID specified."));
}

Query("DELETE FROM blockedlayouts WHERE user = ".$id." OR blockee = ".$id);
Query("DELETE FROM forummods WHERE user = ".$id);
Query("DELETE FROM groupaffiliations WHERE uid = ".$id);
Query("DELETE FROM ignoredforums WHERE uid = ".$id);
Query("DELETE FROM usercomments WHERE uid = ".$id." OR cid = ".$id);
Query("DELETE FROM uservotes WHERE uid = ".$id." OR voter = ".$id);
Query("DELETE FROM users WHERE id = ".$id);

//Handle threads started by this user
$threads = Query("SELECT * FROM threads WHERE user = ".$id);
while($thread = Fetch($threads))
{
	if($thread['replies'] == 0)
	{
		//Simply delete thread and OP
		Query("DELETE FROM threads WHERE id = ".$thread['id']);
		$post = Fetch(Query("SELECT * FROM posts WHERE thread = ".$thread['id']));
		Query("DELETE FROM posts WHERE id = ".$post['id']);
		Query("DELETE FROM posts_text WHERE pid = ".$post['id']);
	}
	else
	{
		//Reassign thread's OP to next in line
		$post = Fetch(Query("SELECT * FROM posts WHERE thread = ".$thread['id']." ORDER BY date ASC LIMIT 1,1"));
		Query("UPDATE threads SET user = ".$post['user']." WHERE id = ".$thread['id']);
	}
}

$posts = Query("SELECT * FROM posts WHERE user = ".$id);
while($post = Fetch($posts))
{
	Query("DELETE FROM posts WHERE id = ".$post['id']);
	Query("DELETE FROM posts_text WHERE pid = ".$post['id']);
}

$pmsgs = Query("SELECT * FROM pmsgs WHERE userto = ".$id." OR userfrom = ".$id);
while($pmsg = Fetch($pmsgs))
{
	Query("DELETE FROM pmsgs WHERE id = ".$pmsg['id']);
	Query("DELETE FROM pmsgs_text WHERE pid = ".$pmsg['id']);
}

@unlink("img/avatars/".$id);
$moodies = Query("SELECT * FROM moodavatars WHERE uid = ".$id);
while($moodie = Fetch($moodies))
	@unlink("img/avatars/".$id."_".$mid);
Query("DELETE FROM moodavatars WHERE uid = ".$id);

$files = Query("SELECT * FROM uploader WHERE user = ".$id);
while($file = Fetch($files))
{
	if($file['private'])
		@unlink("uploader/".$id."/".$file['filename']);
	else
		@unlink("uploader/".$file['filename']);
}
@rmdir("uploader/".$id);
Query("DELETE FROM uploader WHERE user = ".$id);

Write(__("User wiped out. {0} was never here.")." <a href=\"recalc.php\">".__("Recalculate statistics")."</a>", $user['name']);

?>