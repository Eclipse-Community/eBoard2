<?php
//  AcmlmBoard XD - Thread submission/preview page
//  Access: users

include("lib/common.php");

$title = __("New thread");

AssertForbidden("makeThread");

if(!$loguserid) //Not logged in?
	Kill(__("You must be logged in to post."));

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill(__("Forum ID unspecified."));

$fid = (int)$_GET['id'];

if($loguser['powerlevel'] < 0)
	Kill(__("You're banned."));

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill(__("Unknown forum ID."));

if($forum['locked'])
	Kill(__("This forum is locked."));

if($forum['minpowerthread'] > $loguser['powerlevel'])
	Kill(__("You are not allowed to post threads in this forum."));

if(!isset($_POST['poll']) || isset($_GET['poll']))
	$_POST['poll'] = $_GET['poll'];

$isHidden = (int)($forum['minpower'] > 0);

$onlineUsers = OnlineUsers($fid);
write(
"
	<script type=\"text/javascript\" src=\"lib/jscolor/jscolor.js\"></script>
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");
if(!$noAjax)
	write(
"
	<script type=\"text/javascript\">
		onlineFID = {0};
		window.addEventListener(\"load\",  startOnlineUsers, false);
	</script>
	<div class=\"header0 cell1 center outline smallFonts\" style=\"overflow: auto;\">
		&nbsp;
		<span id=\"onlineUsers\">
			{1}
		</span>
		&nbsp;
	</div>
", $fid, $onlineUsers);

if($_POST['poll'])
	MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, __("New poll")=>""), $links);
else
	MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, __("New thread")=>""), $links);

if(isset($_POST['title']))
	$_POST['title'] = htmlentities2($_POST['title']);
if(isset($_POST['pollQuestion']))
	$_POST['pollQuestion'] = htmlentities2($_POST['pollQuestion']);
	//$_POST['pollQuestion'] = htmlentities2(stripslashes($_POST['pollQuestion']));

if($_POST['text'] && CheckTableBreaks($_POST['text']))
{
	$_POST['action'] = "";
	Alert(__("This post would break the board layout."), __("I'm sorry, Dave."));
}

if($_POST['text'] && $_POST['action'] != __("Preview"))
{
	$words = explode(" ", $_POST['text']);
	$wordCount = count($words);
	if($wordCount < 2)
	{
		$_POST['action'] = "";
		Alert(__("Your post is too short to have any real meaning. Try a little harder. Your post needs more than two words."), __("I'm sorry, Dave."));
	}
}

if($_POST['text'] && $_POST['action'] == __("Post"))
{
	$lastPost = time() - $loguser['lastposttime'];
	if($lastPost < 30)
	{
		$_POST['action'] = "";
		Alert(__("You're going too damn fast! Slow down a little."), __("Hold your horses."));
	}
}

if($_POST['action'] == __("Post"))
{
	$trimmedTitle = trim(str_replace('&nbsp;', ' ', $_POST['title']));
	if($_POST['text'] && $trimmedTitle != "")
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

		if($_POST['iconid'])
		{
			$_POST['iconid'] = (int)$_POST['iconid'];
			if($_POST['iconid'] < 255)
				$iconurl = "img/icons/icon".$_POST['iconid'].".png";
			else
				$iconurl = justEscape($_POST['iconurl']);
		}

		$mod = '0, 0';
		if(CanMod($loguserid, $forum['id']))
			$mod = (($_POST['lock'] == 'on') ? '1':'0').', '.(($_POST['stick'] == 'on') ? '1':'0');
		
		//	Guess who forgot to make sure not every thread was a poll? XD -- Arbe
		if($_POST['poll'])
		{
			$doubleVote = ($_POST['multivote']) ? 1 : 0;
			$qPoll = "insert into poll (question, doublevote) values ('".justEscape($_POST['pollQuestion'])."', ".$doubleVote.")";
			$rPoll = Query($qPoll);
			$pod = mysql_insert_id();
			for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
			{
				if($_POST['pollOption'.$pops])
				{
					$pollColor = filterPollColors($_POST['pollColor'.$pops]);
					$newID = FetchResult("SELECT id+1 FROM poll_choices WHERE (SELECT COUNT(*) FROM poll_choices p2 WHERE p2.id=poll_choices.id+1)=0 ORDER BY id ASC LIMIT 1");
					if($newID < 1) $newID = 1;
					$qPollOption = "insert into poll_choices (id, poll, choice, color) values (".$newID.", ".$pod.", '".justEscape($_POST['pollOption'.$pops])."', '".$pollColor."')";
					$rPollOption = Query($qPollOption);
				}
			}
		}
		else
			$pod = 0;
		//Yeah, that was me ^^; -- Kawa

		$newID = FetchResult("SELECT id+1 FROM threads WHERE (SELECT COUNT(*) FROM threads t2 WHERE t2.id=threads.id+1)=0 ORDER BY id ASC LIMIT 1");
		if($newID < 1) $newID = 1;

		$qThreads = "insert into threads (id, forum, user, title, icon, lastpostdate, lastposter, closed, sticky, poll) values (".$newID.",".$fid.",".$loguserid.",'".justEscape($_POST['title'])."','".$iconurl."',".time().",".$loguserid.", ".$mod.", ".$pod.")";
		$rThreads = Query($qThreads);
		$tid = mysql_insert_id();

		$qUsers = "update users set posts=".($loguser['posts']+1).", lastposttime=".time()." where id=".$loguserid." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num, options, mood) values (".$tid.",".$loguserid.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($loguser['posts']+1).", ".$options.", ".(int)$_POST['mood'].")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numthreads=".($forum['numthreads']+1).", numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$loguserid.", lastpostid=".$pid." where id=".$fid." limit 1";
		$rFora = Query($qFora);
		
		Query("update threads set lastpostid = ".$pid." where id = ".$tid);

		Report("New ".($_POST['poll'] ? "poll" : "thread")." by [b]".$loguser['name']."[/]: [b]".$_POST['title']."[/] (".$forum['title'].") -> [g]#HERE#?tid=".$tid, $isHidden);
		Redirect(__("Posted!"), "thread.php?id=".$tid, __("the thread"));
		exit();
	}
	else
	{
		if($trimmedTitle)
			Alert(__("Enter a message and try again."), __("Your post is empty."));
		else if($_POST['text'])
			Alert(__("Enter a thread title and try again."), __("Your thread is unnamed."));
		else
			Alert(__("Enter a message and a thread title and try again."), __("Your post is empty."));
	}
}

if($_POST['text'])
{
	//$prefill = htmlentities2(stripslashes($_POST['text']));
	$prefill = htmlentities2(deSlashMagic($_POST['text']));
	$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}
if($_POST['title'])
	$trefill = htmlentities2(deSlashMagic($_POST['title']));
	//$trefill = htmlentities2(stripslashes($_POST['title']));

if($_POST['action'] == __("Preview"))
{
	if($_POST['text'] && $_POST['title'])
	{
		if($_POST['poll'])
		{
			$options = array();
			$noColors = 0;
			$defaultColors = array(
				"#000000","#0000B6","#00B600","#00B6B6","#B60000","#B600B6","#B66700","#B6B6B6",
				"#676767","#6767FF","#67FF67","#67FFFF","#FF6767","#FF67FF","#FFFF67","#FFFFFF",);
			for($i = 0; $i < $_POST['pollOptions']; $i++)
			{
				$options[] = array("choice"=>$_POST['pollOption'.$i], "color"=>$_POST['pollColor'.$i]);
			}
			$totalVotes = count($options);
			foreach($options as $option)
			{
				if($option['color'] == "")
					$option['color'] = $defaultColors[($pops + 9) % 16];
				
				$votes = 1;

				$cellClass = ($cellClass+1) % 2;
				$label = format("{1}", $pc[$pops], $option['choice']);

				$bar = "";
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
		</table>
	", $cellClass, $_POST['pollQuestion'], $pollLines);
		}
	
		$previewPost['text'] = $prefill;
		$previewPost['num'] = $loguser['posts']+1;
		$previewPost['posts'] = $loguser['posts']+1;
		$previewPost['id'] = "???";
		$previewPost['options'] = 0;
		if($_POST['nopl']) $previewPost['options'] |= 1;
		if($_POST['nosm']) $previewPost['options'] |= 2;
		if($_POST['nobr']) $previewPost['options'] |= 4;
		$previewPost['mood'] = (int)$_POST['mood'];
		$previewPost['uid'] = $loguserid;
		$copies = explode(",","title,name,displayname,picture,sex,powerlevel,avatar,postheader,signature,signsep,regdate,lastactivity,lastposttime");
		foreach($copies as $toCopy)
			$previewPost[$toCopy] = $loguser[$toCopy];
		MakePost($previewPost, 0, $fid);
	} else
	{
		if($_POST['title'])
			Alert(__("Enter a message and try again."), __("Your post is empty."));
		else if($_POST['text'])
			Alert(__("Enter a thread title and try again."), __("Your thread is unnamed."));
		else
			Alert(__("Enter a message and a thread title and try again."), __("Your post is empty."));	
	}
}

if(!$_POST['text']) $_POST['text'] = $post['text'];
if($_POST['text']) $prefill = htmlval(deSlashMagic($_POST['text']));
if($_POST['title']) $trefill = htmlval(deSlashMagic($_POST['title']));

if(!isset($_POST['iconid']))
	$_POST['iconid'] = 0;

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";
if($_POST['nosm'])
	$nosm = "checked=\"checked\"";
if($_POST['nobr'])
	$nobr = "checked=\"checked\"";

$iconNoneChecked = ($_POST['iconid'] == 0) ? "checked=\"checked\"" : "";
$iconCustomChecked = ($_POST['iconid'] == 255) ? "checked=\"checked\"" : "";

$i = 1;
$icons = "";
while(is_file("img/icons/icon".$i.".png"))
{
	$checked = ($_POST['iconid'] == $i) ? "checked=\"checked\" " : "";
	$icons .= format(
"
							<label>
								<input type=\"radio\" {0} name=\"iconid\" value=\"{1}\" />
								<img src=\"img/icons/icon{1}.png\" alt=\"Icon {1}\" onclick=\"javascript:void()\" />
							</label>
", $checked, $i);
	$i++;
}

write(
"
	<table style=\"width: 100%;\">
		<tr>
			<td style=\"vertical-align: top; border: none;\">
				<form action=\"newthread.php\" method=\"post\">
					<table class=\"outline margin width100\">
						<tr class=\"header1\">
							<th colspan=\"2\">
								{0}
							</th>
						</tr>
						<tr class=\"cell0\">
							<td>
								<label for=\"tit\">
									".__("Title")."
								</label>
							</td>
							<td>
								<input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"{1}\" />
							</td>
						</tr>
						<tr class=\"cell1\">
							<td>
								".__("Icon")."
							</td>
							<td class=\"threadIcons\">
								<label>
									<input type=\"radio\" {2} name=\"iconid\" value=\"0\" /> 
									<span>".__("None")."</span>
								</label> 
								{3}
								<br />
								<label>
									<input type=\"radio\" {4} name=\"iconid\" value=\"255\" /> 
									<span>".__("Custom")."</span>
								</label> 
								<input type=\"text\" id=\"iconurl\" name=\"iconurl\" style=\"width: 50%;\" maxlength=\"100\" value=\"{5}\" />
							</td>
						</tr>
",	($_POST['poll'] ? __("New poll") : __("New thread")), $trefill, $iconNoneChecked, $icons, $iconCustomChecked,
	htmlval(deSlashMagic($_POST['iconurl'])));

if($_POST['poll'])
{
	$first = true;
	$pollOptions = "";
	for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
	{
		$cellClass = ($cellClass+1) % 2;
		$fixed = htmlval(deSlashMagic($_POST['pollOption'.$pops]));
		$pollOptions .= format(
"
						<tr class=\"cell{0}\">
							<td>
								<label for=\"p{1}\">".__("Option {2}")."</label>
							</td>
							<td>
								<input type=\"text\" id=\"p{1}\" name=\"pollOption{1}\" value=\"{3}\" style=\"width: 50%;\" maxlength=\"40\" >&nbsp;
								<label>
									".__("Color", 1)."&nbsp;
									<input type=\"text\" name=\"pollColor{1}\" value=\"{4}\" size=\"10\" maxlength=\"7\" class=\"color {hash:true,required:false,pickerFaceColor:'black',pickerFace:3,pickerBorder:0,pickerInsetColor:'black',pickerPosition:'left',pickerMode:'HVS'}\" />
								</label>
								{5}
							</td>
						</tr>
",	$cellClass, $pops, $pops + 1, $fixed,
	filterPollColors($_POST['pollColor'.$pops]), ($first ? "&nbsp;(#rrggbb)" : ""));
		$first = false;
	}

	write(
"
						<tr class=\"cell0\">
							<td>
								<label for=\"pq\">
									".__("Poll question")."
								</label>
							</td>
							<td>
								<input type=\"text\" id=\"pq\" name=\"pollQuestion\" value=\"{0}\" style=\"width: 98%;\" maxlength=\"100\" />
							</td>
						</tr>
						<tr class=\"cell1\">
							<td>
								<label for=\"pn\">
									".__("Number of options")."
								</label>
							</td>
							<td>
								<input type=\"text\" id=\"pn\" name=\"pollOptions\" value=\"{1}\" size=\"2\" maxlength=\"2\" />
							</td>
						</tr>
						{2}
", htmlval(deSlashMagic($_POST['pollQuestion'])), $_POST['pollOptions'], $pollOptions);
}

if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">".__("[Default avatar]")."</option>\n";
$rMoods = Query("select mid, name from moodavatars where uid=".$loguserid." order by mid asc");
while($mood = Fetch($rMoods))
	$moodOptions .= format(
"
	<option {0} value=\"{1}\">{2}</option>
",	$moodSelects[$mood['mid']], $mood['mid'], htmlval($mood['name']));

if(CanMod($loguserid, $forum['id']))
{
	$mod = "\n\n<!-- Mod options -->\n";
	$mod .= "<label><input type=\"checkbox\" name=\"lock\">&nbsp;".__("Close thread", 1)."</label>\n";
	$mod .= "<label><input type=\"checkbox\" name=\"stick\">&nbsp;".__("Sticky", 1)."</label>\n";
	$mod .= "<!-- More could follow -->\n\n";
}

if(!$_POST['poll'] || $_POST['pollOptions'])
	$postButton = "<input type=\"submit\" name=\"action\" value=\"".__("Post")."\" /> ";
if($_POST['poll'])
	$multivote = "<label><input type=\"checkbox\" ".($_POST['multivote'] ? "checked=\"checked\"" : "")." name=\"multivote\" />&nbsp;".__("Multivote", 1)."</label>";

write(
"
						<tr class=\"cell0\">
							<td>
								<label for=\"post\">
									Post
								</label>
							</td>
							<td>
								<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">{0}</textarea>
							</td>
						</tr>
						<tr class=\"cell2\">
							<td></td>
							<td>
								{1}
								<input type=\"submit\" name=\"action\" value=\"".__("Preview")."\" />
								<select size=\"1\" name=\"mood\">
									{2}
								</select>
								<label>
									<input type=\"checkbox\" name=\"nopl\" {3} />&nbsp;".__("Disable post layout", 1)."
								</label>
								<label>
									<input type=\"checkbox\" name=\"nosm\" {4} />&nbsp;".__("Disable smilies", 1)."
								</label>
								<label>
									<input type=\"checkbox\" name=\"nobr\" {8} />&nbsp;".__("Disable auto-<br>", 1)."
								</label>
								<input type=\"hidden\" name=\"id\" value=\"{5}\" />
								<input type=\"hidden\" name=\"poll\" value=\"{6}\" />
								{7}
								{9}
							</td>
						</tr>
					</table>
				</form>
			</td>
			<td style=\"width: 200px; vertical-align: top; border: none;\">
",	$prefill, $postButton, $moodOptions, $nopl, $nosm, $fid, stripslashes($_POST['poll']), $multivote, $nobr, $mod);

DoSmileyBar();
DoPostHelp();

write("
			</td>
		</tr>
	</table>
");

if($_POST['poll'])
	MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, __("New poll")=>""), $links);
else
	MakeCrumbs(array(__("Main")=>"./", $forum['title']=>"forum.php?id=".$fid, __("New thread")=>""), $links);

?>