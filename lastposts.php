<?php
include("lib/common.php");

$hours = $lastPostsTimeLimit;

$qPosts = "select ";
$qPosts .=
	"posts.id, posts.date, users.id as uid, users.name, users.displayname, users.powerlevel, users.sex, threads.title as ttit, forums.title as ftit";
$qPosts .= 
	" from posts 
	left join users on users.id = posts.user 
	left join threads on threads.id = posts.thread 
	left join forums on threads.forum = forums.id
	left join categories on categories.id = forums.catid";
$qPosts .= " where forums.minpower <= ".$loguser['powerlevel']." and categories.minpower <= ".$loguser['powerlevel']." and posts.date >= ".(time() - ($hours * 60*60))." order by date desc limit 0, 100";

$rPosts = Query($qPosts);
while($post = Fetch($rPosts))
{
	$c = ($c+1) % 2;
	
	$theList = ""; //init variable
	
	$theList .= format(
"
	<tr class=\"cell{5}\">
		<td>
			{3}
		</td>
		<td>
			{4}
		</td>
		<td>
			{2}
		</td>
		<td>
			{1}
		</td>
		<td>
			&raquo; <a href=\"thread.php?pid={0}#{0}\">{0}</a>
		</td>
	</tr>
", $post['id'], cdate($dateformat,$post['date']), UserLink($post, "uid"), $post['ftit'], htmlspecialchars($post['ttit']), $c);
}

if($theList == "")
	$theList = format(
"
	<tr class=\"cell1\">
		<td colspan=\"5\" style=\"text-align: center\">
			".__("Nothing has been posted in the last {0}.")."
		</td>
	</tr>
", Plural($hours, __("hour")));

write(
"
<table class=\"margin outline\">
	<tr class=\"header0\">
		<th colspan=\"5\">".__("Last posts")."</th>
	</tr>
	<tr class=\"header1\">
		<th>".__("Forum")."</th>
		<th>".__("Thread")."</th>
		<th>".__("User")."</th>
		<th>".__("Date")."</th>
		<th></th>
	</tr>
	{0}
</table>
", $theList);

?>
