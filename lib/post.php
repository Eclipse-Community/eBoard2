<?php
//  AcmlmBoard XD support - Post functions

include_once("write.php");

function LoadSmilies($byOrder = FALSE)
{
	global $smilies, $smiliesOrdered;
	$smiliesR = array
	(
		')' => '\)',
		'(' => '\(',
		'/' => '\/',
		'+' => '\+',
		'|' => '\|',
		'^' => '\^',
		'?' => '\?',
		'[' => '\[',
		']' => '\]',
		'<' => '\<',
		'>' => '\>',
		':' => '\:',
		']' => '\]',
		'.' => '\.',
		'\'' => '\\\'',
	);
	if($byOrder)
	{
		if(isset($smiliesOrdered))
			return;
		$rSmilies = Query("select * from smilies order by id asc");
		$smiliesOrdered = array();
		while($smiley = Fetch($rSmilies))
		{
			//foreach ($smiliesR as $old => $new)
			//	$smiley['code'] = str_replace($old, $new, $smiley['code']);
			$smiliesOrdered[] = $smiley;
		}
	}
	else
	{
		if(isset($smilies))
			return;
		$rSmilies = Query("select * from smilies order by length(code) desc");
		$smilies = array();
		while($smiley = Fetch($rSmilies))
		{
			//foreach ($smiliesR as $old => $new)
			//	$smiley['code'] = str_replace($old, $new, $smiley['code']);
			$smilies[] = $smiley;
		}
	}
}

function ApplySmilies($text)
{
	global $smilies;
	foreach($smilies as $s)
	{
		$text = preg_replace("/\b(".$s['code'].")\b/si", "<img src=\"img/smilies/".htmlentities($s['image'])."\" />", $text);
	}
	return $text;
}

function LoadBlocklayouts()
{
	global $blocklayouts, $loguserid;
	if(isset($blocklayouts))
		return;
	$rBlocks = Query("select * from blockedlayouts where blockee = ".$loguserid);
	while($block = Fetch($rBlocks))
		$blocklayouts[$block['user']] = 1;
	//$qBlock = "select * from blockedlayouts where user=".$post['uid']." and blockee=".$loguserid;
	//$rBlock = Query($qBlock);

}

function LoadRanks($rankset)
{
	global $ranks;
	if(isset($ranks[$rankset]))
		return;
	$ranks[$poster['rankset']] = array();
	$rRanks = Query("select * from ranks where rset=".$rankset." order by num");
	while($rank = Fetch($rRanks))
		$ranks[$rankset][$rank['num']] = $rank['text'];
}

function GetRank($poster)
{
	global $ranks;
	if($poster['rankset'] == 0)
		return "";
	LoadRanks($poster['rankset']);
	$thisSet = $ranks[$poster['rankset']];
	if(!is_array($thisSet))
		return "";
	$ret = "";
	foreach($thisSet as $num => $text)
	{
		if($num > $poster['posts'])
			return $ret;
		$ret = $text;
	}

	/*
	$qRank = "select text from ranks where rset=".$poster['rankset']." and num<=".$poster['posts']." order by num desc limit 1";
	$rRank = Query($qRank);
	$rank = Fetch($rRank);
	return $rank['text'];
	*/
}

function GetToNextRank($poster)
{
	global $ranks;
	if($poster['rankset'] == 0)
		return "";
	LoadRanks($poster['rankset']);
	$thisSet = $ranks[$poster['rankset']];
	if(!is_array($thisSet))
		return 0;
	$ret = 0;
	foreach($thisSet as $num => $text)
	{
		$ret = $num - $poster['posts'];
		if($num > $poster['posts'])
			return $ret;
	}

	/*
	if($poster['rankset'] == 0)
		return 0;
	$qRank = "select num from ranks where rset=".$poster['rankset']." and num > ".$poster['posts']." limit 1";
	$rRank = Query($qRank);
	if(NumRows($rRank))
	{
		$rank = Fetch($rRank);
		return $rank['num'] - $poster['posts'];
	}
	return 0;
	*/
}

/*
function MakeSpoiler($match)
{
	global $spoilers;
	$spoilers++;
	return "<div class=\"spoiler\"><button onclick=\"document.getElementById('spoiler".$spoilers."').className='';\">Spoiler</button><div class=\"spoiled\" id=\"spoiler".$spoilers."\">";
}
*/

function MakeUserLink($matches)
{
	global $members;
	$id = (int)$matches[1];
	if(!isset($members[$id]))
	{
		$rUser = Query("select id, name, displayname, powerlevel, sex from users where id=".$id);
		if(NumRows($rUser))
			$members[$id] = Fetch($rUser);
		else
			return UserLink(array('id' => 0, 'name' => "Unknown User", 'sex' => 0, 'powerlevel' => -1));
	}
	return UserLink($members[$id]);
}

function ApplyNetiquetteToLinks($match)
{
	if (substr($match[1], 0, 7) != 'http://')
		return $match[0];

	if (stripos($match[1], 'http://'.$_SERVER['SERVER_NAME']) === 0)
		return $match[0];

	return $match[0].' target="_blank"';
}

function FilterJS($match)
{
	$url = html_entity_decode($match[2]);
	if (stristr($url, "javascript:"))
		return "";
	return $match[0];
}

function GetSyndrome($activity)
{
	include("syndromes.php");
	$soFar = "";
	foreach($syndromes as $minAct => $syndrome)
		if($activity >= $minAct)
			$soFar = "<em style=\"color: ".$syndrome[1].";\">".$syndrome[0]."</em><br />";
	return $soFar;
}

$text = "";
function code_block($matches) {
	//De-tabled [code] tag, based on BH's...
    $list  = array("<"   ,"\r"  ,"["    ,":"    ,")"    ,"_"    );
    $list2 = array("&lt;","<br/>","&#91;","&#58;","&#41;","&#95;");

	return '<code class="Code block">' . str_replace($list, $list2, $matches[1]) . '</code>';
}

function CleanUpPost($postText, $poster = "", $noSmilies = false, $noBr = false)
{
	global $smilies, $text;
	static $orig, $repl;
	LoadSmilies();

	$s = $postText;
	$s = str_replace("\r\n","\n", $s);

	$s = EatThatPork($s);

	$s = preg_replace_callback("'\[user=([0-9]+)\]'si", "MakeUserLink", $s);

	$s = preg_replace_callback("'\[code\](.*?)\[/code\]'si", 'code_block',$s);

	$s = preg_replace("'\[b\](.*?)\[/b\]'si","<strong>\\1</strong>", $s);
	$s = preg_replace("'\[i\](.*?)\[/i\]'si","<em>\\1</em>", $s);
	$s = preg_replace("'\[u\](.*?)\[/u\]'si","<u>\\1</u>", $s);
	$s = preg_replace("'\[s\](.*?)\[/s\]'si","<del>\\1</del>", $s);

	$s = preg_replace("'<b>(.*?)\</b>'si","<strong>\\1</strong>", $s);
	$s = preg_replace("'<i>(.*?)\</i>'si","<em>\\1</em>", $s);
	$s = preg_replace("'<u>(.*?)\</u>'si","<span class=\"underline\">\\1</span>", $s);
	$s = preg_replace("'<s>(.*?)\</s>'si","<del>\\1</del>", $s);

	//Do we need this?
	$s = preg_replace("'\[c=([0123456789ABCDEFabcdef]+)\](.*?)\[/c\]'si","<span style=\"color: #\\1\">\\2</span>", $s);

	if($noBr == FALSE)
		$s = str_replace("\n","<br />", $s);

	//Blacklisted tags
	$badTags = array('script','iframe','frame','blink','textarea','noscript','meta','xmp','plaintext','marquee','embed','object');
	foreach($badTags as $tag)
	{
		$s = preg_replace("'<$tag(.*?)>'si", "&lt;$tag\\1>" ,$s);
		$s = preg_replace("'</$tag(.*?)>'si", "&lt;/$tag>", $s);
	}

	//Various other stuff

	$s = preg_replace("@(on)(\w+?\s*?)=@si", '$1$2&#x3D;', $s);
	$s = preg_replace("'javascript:'si","javascript<em></em>:>", $s);

	$s = str_replace("[spoiler]","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\">Show spoiler</button><div class=\"spoiled hidden\">", $s);
	$s = preg_replace("'\[spoiler=(.*?)\]'si","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\" class=\"named\">\\1</button><div class=\"spoiled hidden\">", $s);
	$s = str_replace("[/spoiler]","</div></div>", $s);

	$s = preg_replace("'\[url\](.*?)\[/url\]'si","<a href=\"\\1\">\\1</a>", $s);
	$s = preg_replace("'\[url=[\'\"]?(.*?)[\'\"]?\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[url=(.*?)\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[img\](.*?)\[/img\]'si","<a href=\"\\1\" alt=\"\"><img class=\"imgtag\" src=\"\\1\" alt=\"\"></a>", $s);
	$s = preg_replace("'\[img=(.*?)\](.*?)\[/img\]'si","<a href=\"\\1\" alt=\"\\2\" title=\"\\2\"><img class=\"imgtag\" src=\"\\1\" alt=\"\\2\" title=\"\\2\"></a>", $s);

	$s =  str_replace("[quote]","<blockquote><div><hr />", $s);
	$s =  str_replace("[/quote]","<hr /></div></blockquote>", $s);
	$s = preg_replace("'\[quote=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Posted by <a href=\"thread.php?pid=\\2#\\2\">\\1</a></i></small><hr />", $s);
	$s = preg_replace("'\[quote=(.*?)\]'si","<blockquote><div><small><i>Posted by \\1</i></small><hr />", $s);
	$s = preg_replace("'\[reply=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);

	$bucket = "bbCode"; include("./lib/pluginloader.php");

	$s = preg_replace_callback("@(href|src)\s*=\s*\"([^\"]+)\"@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*'([^']+)'@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*([^\s>]+)@si", "FilterJS", $s);

	$s = preg_replace("'>>([0-9]+)'si",">><a href=\"thread.php?pid=\\1#\\1\">\\1</a>", $s);

	if($poster)
		$s = preg_replace("'/me '","<b>* ".$poster."</b> ", $s);

	//Smilies
	if(!$noSmilies)
	{
		if (!isset($orig))
		{
			$orig = $repl = array();
			for ($i = 0; $i < count($smilies); $i++)
			{
				$orig[] = "/(?<=.\W|\W.|^\W)".preg_quote($smilies[$i]['code'], "/")."(?=.\W|\W.|\W$)/";
				$repl[] = "<img src=\"img/smilies/".$smilies[$i]['image']."\" />";
			}
		}
		$s = preg_replace($orig, $repl, " ".$s." ");
		$s = substr($s, 1, -1);
	}

	$s = preg_replace_callback("@<a[^>]+href\s*=\s*\"(.*?)\"@si", 'ApplyNetiquetteToLinks', $s);
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*'(.*?)'@si", 'ApplyNetiquetteToLinks', $s);
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*([^\"'][^\s>]*)@si", 'ApplyNetiquetteToLinks', $s);

	include("macros.php");
	foreach($macros as $macro => $img)
		$s = str_replace(":".$macro.":", "<img src=\"img/macros/".$img."\" alt=\":".$macro.":\" />", $s);

	return $s;
}

function ApplyTags($text, $tags)
{
	if(!stristr($text, "&"))
		return $text;
	$s = $text;
	foreach($tags as $tag => $val)
		$s = str_replace("&".$tag."&", $val, $s);
	if(is_numeric($tags['numposts']))
		$s = preg_replace_callback('@&(\d+)&@si', array(new MaxPosts($tags), 'max_posts_callback'), $s);
	else
		$s = preg_replace("'&(\d+)&'si", "preview", $s);
	return $s;
}

// hax for anonymous function
class MaxPosts {
	var $tags;
	function __construct($tags) {
		$this->tags = $tags;
	}

	function max_posts_callback($results) {
		return max($results[1] - $this->tags['numposts'], 0);
	}
}

$sideBarStuff = "";
$sideBarData = 0;
function MakePost($post, $thread, $forum, $ispm=0)
{
	global $loguser, $loguserid, $dateformat, $theme, $hacks, $isBot, $blocklayouts, $postText, $sideBarStuff, $sideBarData, $salt;

	$sideBarStuff = "";

	if(isset($_GET['pid']))
		$highlight = (int)$_GET['pid'];

	//$qBlock = "select * from blockedlayouts where user=".$post['uid']." and blockee=".$loguserid;
	//$rBlock = Query($qBlock);
	LoadBlockLayouts();
	$isBlocked = $blocklayouts[$post['uid']] /* NumRows($rBlock) */ | $post['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;
	$noSmilies = $post['options'] & 2;
	$noBr = $post['options'] & 4;

	if($post['deleted'] && !$ispm)
	{
		$meta = format(__("Posted on {0}"), cdate($dateformat,$post['date']));
		$links = "<ul class=\"pipemenu\"><li>".__("Post deleted")."</li>";
		if(CanMod($loguserid,$forum))
		{
			$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");
			if (IsAllowed("editPost", $post['id']))
				$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=2&amp;key=".$key."\">".__("Undelete")."</a></li>";
			$links .= "<li><a href=\"#\" onclick=\"ReplacePost(".$post['id'].",true); return false;\">".__("View")."</a></li>";
		}
		$links .= "<li>".format(__("ID: {0}"), $post['id'])."</li></ul>";
		write(
"
		<table class=\"post margin\" id=\"post{0}\">
			<tr>
				<td class=\"side userlink\" id=\"{0}\">
					{1}
				</td>
				<td class=\"smallFonts\" style=\"border-left: 0px none; border-right: 0px none;\">
					{2}
				</td>
				<td class=\"smallFonts right\" style=\"border-left: 0px none;\">
					{3}
				</td>
			</tr>
		</table>
",	$post['id'], UserLink($post, "uid"), $meta, $links
);
		return;
	}

	if($ispm == 1)
		$thread = $ispm;

	if($thread)
	{
		$links = "";
		if(!$ispm && !$isBot)
		{
			if ($post['unfoldhax'])
			{
				$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");
				$links = "<ul class=\"pipemenu\"><li>".__("Post deleted")."</li>";
				if (IsAllowed("editPost", $post['id']))
					$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=2&amp;key=".$key."\">".__("Undelete")."</a></li>";
				$links .= "<li><a href=\"#\" onclick=\"ReplacePost(".$post['id'].",false); return false;\">".__("Close")."</a></li>";
				$links .= "<li>".format(__("ID: {0}"), $post['id'])."</li></ul>";
			}
			else
			{
				$links .= "<ul class=\"pipemenu\"><li><a href=\"thread.php?pid=".$post['id']."#".$post['id']."\">".__("Link")."</a></li>";
				if($thread && $loguser['powerlevel'] > -1 && $forum != -2 && IsAllowed("makeReply", $thread))
					$links .= "<li><a href=\"newreply.php?id=".$thread."&amp;quote=".$post['id']."\">".__("Quote")."</a></li>";
				if(CanMod($loguserid, $forum) || $post['uid'] == $loguserid && $loguser['powerlevel'] > -1 && !$post['closed'] && IsAllowed("editPost", $post['id']))
					$links .= "<li><a href=\"editpost.php?id=".$post['id']."\">".__("Edit")."</a></li>";
				if(CanMod($loguserid, $forum) && IsAllowed("editPost", $post['id']))
				{
					$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");
					$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=1&amp;key=".$key."\">".__("Delete")."</a></li>";
				}
				if($forum != -2 && IsAllowed("makeReply", $thread))
					$links .= "<li>".format(__("ID: {0}"), "<a href=\"newreply.php?id=".$thread."&amp;link=".$post['id']."\">".$post['id']."</a>")."</li>";
				else
					$links .= "<li>".format(__("ID: {0}"), $post['id'])."</li>";
			}
		}

		$meta = format(__(!$ispm ? "Posted on {0}" : "Sent on {0}"), cdate($dateformat,$post['date']));
		//Threadlinks for listpost.php
		if($forum == -2)
			$meta .= " ".__("in")." <a href=\"thread.php?id=".$post['thread']."\">".htmlspecialchars($post['threadname'])."</a>";
		//Revisions
		if($post['revision'])
		{
			if(CanMod($loguserid, $forum))
				$meta .= " (<a href=\"javascript:void(0);\" onclick=\"showRevisions(".$post['id'].")\">".format(__("revision {0}"), $post['revision'])."</a>)";
			else
				$meta .= " (".format(__("revision {0}"), $post['revision']).")";
		}
		//</revisions>
	}
	else
		$meta = __("Sample post");

	if($forum==-1)
		$meta = __("Posted in")." <a href=\"thread.php?id=".$thread['id']."\">".htmlspecialchars($thread['title'])."</a>";

	//if($post['postbg'])
	//	$postbg = " style=\"background: url(".$post['postbg'].");\"";

	$sideBarStuff .= GetRank($post);
	if($sideBarStuff)
		$sideBarStuff .= "<br />";
	if($post['title'])
		$sideBarStuff .= strip_tags(CleanUpPost($post['title'], "", true), "<b><strong><i><em><span><s><del><img><a><br><small>")."<br />";
	else
	{
		$levelRanks = array(-1=>__("Banned"), 0=>"", 1=>__("Local mod"), 2=>__("Full mod"), 3=>__("Administrator"));
		$sideBarStuff .= $levelRanks[$post['powerlevel']]."<br />";
	}
	$sideBarStuff .= GetSyndrome($post['activity']);
	if($post['picture'])
	{
		if($post['mood'] > 0 && file_exists("img/avatars/".$post['uid']."_".$post['mood']))
			$sideBarStuff .= "<img src=\"img/avatars/".$post['uid']."_".$post['mood']."\" alt=\"\" />";
		else
			$sideBarStuff .= "<img src=\"".$post['picture']."\" alt=\"\" />";
	}
	else
		$sideBarStuff .= "<div style=\"width: 50px; height: 50px;\">&nbsp;</div>";

	$lastpost = ($post['lastposttime'] ? timeunits(time() - $post['lastposttime']) : "none");
	$lastview = timeunits(time() - $post['lastactivity']);

	if($post['num'] != "preview")
		$sideBarStuff .= "<br />\n".__("Posts:")." ".$post['num']."/".$post['posts'];
	else
		$sideBarStuff .= "<br />\n".__("Posts:")." ".$post['posts'];

	$sideBarStuff .= "<br />\n".__("Since:")." ".cdate($loguser['dateformat'], $post['regdate'])."<br />";

	$bucket = "sidebar"; include("./lib/pluginloader.php");

	$sideBarStuff .= "<br />\n".__("Last post:")." ".$lastpost;
	$sideBarStuff .= "<br />\n".__("Last view:")." ".$lastview;

	if($hacks['themenames'] == 3)
	{
		$sideBarStuff = "";
		$isBlocked = 1;
	}

	if($post['lastactivity'] > time() - 300)
$sideBarStuff .= "<br />\n".__("User is <strong>online</strong>");
	
	else
		$sideBarStuff .= "<br />\n".__("User is offline");

	if($post['id'] != "preview")
		$anchor = "<a name=\"".$post['id']."\" />";
	if(!$isBlocked)
	{
		$topBar1 = "topbar".$post['uid']."_1";
		$topBar2 = "topbar".$post['uid']."_2";
		$sideBar = "sidebar".$post['uid'];
		$mainBar = "mainbar".$post['uid'];
	}

	$tags = array();
	$rankHax = $post['posts'];
	//if($post['num'] == "preview")
	//	$post['num'] = $post['posts'];
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
		$postHeader = str_replace('$theme', $theme, ApplyTags(CleanUpPost($post['postheader'], "", $noSmilies, true, $noBr, true), $tags));

	$postText = ApplyTags(CleanUpPost($post['text'],$post['name'], $noSmilies, $noBr), $tags);

	$bucket = "postMangler"; include("./lib/pluginloader.php");

	if($post['signature'] && !$isBlocked)
	{
		$postFooter = ApplyTags(CleanUpPost($post['signature'], "", $noSmilies, true, $noBr, true), $tags);
		if(!$post['signsep'])
			$separator = "<br />_________________________<br />";
		else
			$separator = "<br />";
	}

	$postCode =
"
		<table class=\"post margin {14}\" id=\"post{13}\">
			<tr>
				<td class=\"side userlink {1}\">
					{0}
					{5}
				</td>
				<td class=\"meta right {2}\">
					<div style=\"float: left;\" id=\"meta_{13}\">
						{7}
					</div>
					<div style=\"float: left; display: none;\" id=\"dyna_{13}\">
						Hi.
					</div>
					{8}
				</td>
			</tr>
			<tr>
				<td class=\"side {3}\">
					<div class=\"smallFonts\">
						{6}
					</div>
				</td>
				<td class=\"post {4}\" id=\"post_{13}\">

					{9}

					{10}

					{12}
					{11}

				</td>
			</tr>
		</table>
";

	write($postCode,
			$anchor, $topBar1, $topBar2, $sideBar, $mainBar,
			UserLink($post, "uid"), $sideBarStuff, $meta, $links,
			$postHeader, $postText, $postFooter, $separator, $post['id'], $post['id'] == $highlight ? "highlightedPost" : "");

}


//Scans for any numerical entities that decode to the 7-bit printable ASCII range and removes them.
//This makes a last-minute hack impossible where a javascript: link is given completely in absurd and malformed entities.
function EatThatPork($s)
{
	$s = preg_replace_callback("/(&#)(x*)([a-f0-9]+(?![a-f0-9]))(;*)/i", "CheckKosher", $s);
	return $s;
}

function CheckKosher($matches)
{
	$num = ltrim($matches[3], "0");
	if($matches[2])
		$num = hexdec($num);
	if($num < 127)
		return ""; //"&#xA4;";
	else
		return "&#x".dechex($num).";"; //$matches[0];
}

?>
