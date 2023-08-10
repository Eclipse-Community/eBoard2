<?php
//  AcmlmBoard XD support - Handy snippets
include_once("language.php");
include_once("write.php");

function OptimizeLayouts($text)
{
	$bucket = array();

	// Save the tags in the temp array and remove them from where they were originally
	$regexps = array("@<style(.*?)</style(.*?)>(\r?\n?)@si", "@<link(.*?)>(\r?\n?)@si", "@<script(.*?)</script(.*?)>(\r?\n?)@si");
	foreach ($regexps as $regexp)
	{
		preg_match_all($regexp, $text, $temp, PREG_PATTERN_ORDER);
		$text = preg_replace($regexp, "", $text);
		$bucket = array_merge($bucket, $temp[0]);
	}

	// Remove duplicates
	$bucket = array_unique($bucket);

	// Put the tags back
	$newStyles = "<!-- head tags -->".implode("", $bucket)."<!-- /head tags -->";
	$text = str_replace("</head>", $newStyles."</head>", $text);
	$text = str_replace("<recaptcha", "<script", $text);
	return $text;
}

function usectime()
{
	$t = gettimeofday();
	return $t['sec'] + ($t['usec'] / 1000000);
}

function DoFooter($buffer)
{
	global $noFooter, $timeStart, $queries, $overallTidy, $boardname, $title, $dblink, $ajax, $footerButtons, $footerExtensionsA, $footerExtensionsB;

	if(!$noFooter)
	{
		//if(function_exists("runBucket")) runBucket("footerButtons");

		$footer = format(
"
		<div class=\"footer\">
			Powered by <a href=\"https://github.com/Eclipse-Community/eBoard2\">eBoard2</a>, version 2.3a (2023-08-09)<br />
			By K4sum1, Kouto, et al<br />
			ABXD by Dirbaio, xfix, Kawa, StapleButter, Nina, et al<br />
			AcmlmBoard © Jean-François Lapointe<br />
			".__("Page rendered in {0} seconds with {1}.")."<br />
			{3}
			{2}
		</div>
	</div>
</body>
</html>
",	sprintf("%1.3f",usectime()-$timeStart), Plural($queries, __("MySQL query")),
	$footerButtons, __("<!-- English translation by Kawa -->"));
	}

	$boardTitle = htmlval($boardname);
	if($title != "")
		$boardTitle .= " &raquo; ".$title;

	$raw = $buffer.$footerExtensionsA.$footer.$footerExtensionsB;
	$raw = str_replace("<title>[[BOARD TITLE HERE]]</title>", "<title>".$boardTitle."</title>", $raw);
	if(!$ajax)
		$raw = OptimizeLayouts($raw);

	mysql_close($dblink);

	if(!$overallTidy)
	{
		return $raw;
	}

	$tidyConfig = array
	(
		"show-body-only"=>0,
		"output-xhtml"=>1,
		"doctype"=>"transitional",
		"logical-emphasis"=>1,
		"alt-text"=>"",
		"drop-proprietary-attributes"=>1,
		"wrap"=>0,
		"preserve-entities"=>1,
		"indent"=>1,
		"input-encoding"=>"utf8",
		"char-encoding"=>"utf8",
		"output-encoding"=>"utf8",
		"new-blocklevel-tags"=>"video",
	);

	//if(function_exists(OptimizeLayouts))
	//	$raw = OptimizeLayouts($raw);
	$clean = tidy_repair_string($raw, $tidyConfig);

	$clean = str_replace("class=\"required", "required=\"required\" class=\"", $clean);
	$textareaFixed = str_replace("\r", "", $clean);
	$textareaFixed = str_replace(" </text", "</text", $textareaFixed);
	$textareaFixed = str_replace("\n</text", "</text", $textareaFixed);
	//$textareaFixed = str_replace("\n</text", "</text", $textareaFixed);
	return $textareaFixed;
}

function GetRainbowColor()
{
	$stime = gettimeofday();
	$h = (($stime[usec] / 5) % 600);
	if($h < 100)
	{
		$r = 255;
		$g = 155 + $h;
		$b = 155;
	}
	else if($h < 200)
	{
		$r = 255 - $h + 100;
		$g = 255;
		$b = 155;
	}
	else if($h < 300)
	{
		$r = 155;
		$g = 255;
		$b = 155 + $h - 200;
	}
	else if($h < 400)
	{
		$r = 155;
		$g = 255 - $h + 300;
		$b = 255;
	}
	else if($h < 500)
	{
		$r = 155 + $h - 400;
		$g = 155;
		$b = 255;
	}
	else
	{
		$r = 255;
		$g = 155;
		$b = 255 - $h + 500;
	}
	return substr(dechex($r * 65536 + $g * 256 + $b), -6);
}

function UserLink($user, $field = "id")
{
	global $hacks;

	$fpow = $user['powerlevel'];
	$fsex = $user['sex'];
	$fname = ($user['displayname'] ? $user['displayname'] : $user['name']);
	$fname = htmlspecialchars($fname);
	if($fpow < 0) $fpow = -1;

	if($hacks['alwayssamepower'])
		$fpow = $hacks['alwayssamepower'] - 1;
	if($hacks['alwayssamesex'])
		$fsex = $hacks['alwayssamesex'];

	$classing = " class=\"nc" . $fsex . (($fpow < 0) ? "x" : $fpow)."\"";

	$levels = array(-1 => " [".__("banned")."]", 0 => "", 1 => " [".__("local mod")."]", 2 => " [".__("full mod")."]", 3 => " [".__("admin")."]", 4 => " [".__("root")."]", 5 => " [".__("system")."]");

	$bucket = "userLink"; include('lib/pluginloader.php');

	$userlink = format("<a href=\"profile.php?id={0}\"><span{1} title=\"{3} ({0}){4}\">{2}</span></a>", $user[$field], $classing, $fname, str_replace(" ", "&nbsp;", htmlspecialchars($user['name'])), $levels[$user['powerlevel']]);
	return $userlink;
}

function CanMod($userid, $fid)
{
	global $loguser;
	if($loguser['powerlevel'] > 1)
		return 1;
	if($loguser['powerlevel'] == 1)
	{
		$qMods = "select * from forummods where forum=".$fid." and user=".$userid;
		$rMods = Query($qMods);
		if(NumRows($rMods))
			return 1;
	}
	return 0;
}

function MakeCrumbs($path, $links)
{
	foreach($path as $text=>$link)
	{
		$link = str_replace("&","&amp;",$link);
		if($link)
		{
			$sep = strpos($text, '<TAGS>');
			if ($sep === FALSE)
			{
				$title = $text;
				$tags = '';
			}
			else
			{
				$title = substr($text, 0, $sep);
				$tags = ' '.substr($text, $sep+6);
			}
			$crumbs .= "<a href=\"".$link."\">".$title."</a>".$tags." &raquo; ";
		}
		else
			$crumbs .= str_replace('<TAGS>', '', $text). " &raquo; ";
	}
	$crumbs = substr($crumbs, 0, strlen($crumbs) - 8);

	write(
"
<div class=\"margin\">
	<div style=\"float: right;\">
		<ul class=\"pipemenu smallFonts\">
			{0}
		</ul>
	</div>
	{1}
</div>
", $links, $crumbs);
}


function TimeUnits($sec)
{
	if($sec <    60) return "$sec sec.";
	if($sec <  3600) return floor($sec/60)." min.";
	if($sec < 86400) return floor($sec/3600)." hour".($sec >= 7200 ? "s" : "");
	return floor($sec/86400)." day".($sec >= 172800 ? "s" : "");
}

function DoPrivateMessageBar()
{
	global $loguserid, $loguser, $dateformat;

	if($loguserid)
	{
		$qUnread = "select count(*) from pmsgs where userto = ".$loguserid." and msgread=0 and drafting=0";
		$unread= FetchResult($qUnread);
		$content = "";
		if($unread)
		{
			$pmNotice = $loguser['usebanners'] ? "id=\"pmNotice\" " : "";
			$qLast = "select * from pmsgs where userto = ".$loguserid." and msgread=0 order by date desc limit 0,1";
			$rLast = Query($qLast);
			$last = Fetch($rLast);
			$qUser = "select * from users where id = ".$last['userfrom'];
			$rUser = Query($qUser);
			$user = Fetch($rUser);
			$content .= format(
"
		".__("You have {0}{1}. {2}Last message{1} from {3} on {4}."),
			Plural($unread, format(__("new {0}private message"), "<a href=\"private.php\">")),
			"</a>",
			format("<a href=\"showprivate.php?id={0}\">", $last['id']),
			UserLink($user), cdate($dateformat, $last['date']));
		}

		if($loguser['newcomments'])
		{
			$content .= format(
"
		".__("You {0} have new comments in your {1}profile{2}."),
			$content != "" ? "also" : "",
			format("<a href=\"profile.php?id={0}\">", $loguserid),
			"</a>");
		}

		if($content)
			write(
"
	<div {0} class=\"outline margin header0 cell0 smallFonts\">
		{1}
	</div>
", $pmNotice, $content);
	}
}

function DoSmileyBar($taname = "text")
{
	global $smiliesOrdered;
	$expandAt = 26;
	LoadSmilies(TRUE);

	write(
"
	<div class=\"PoRT margin\" style=\"width: 90%\">
		<div class=\"errort\">
			<strong>".__("Smilies")."</strong>
		</div>
		<div class=\"errorc cell0\" id=\"smiliesContainer\">
");
	if(count($smiliesOrdered) > $expandAt)
		write("<button class=\"expander\" id=\"smiliesExpand\" onclick=\"expandSmilies();\">&#x25BC;</button>");
	print "<div class=\"smilies\" id=\"commonSet\">";
	for($i = 0; $i < count($smiliesOrdered) - 1; $i++)
	{
		if($i == $expandAt)
			print "</div><div class=\"smilies\" id=\"expandedSet\">";
		$s = $smiliesOrdered[$i];
		print "<img src=\"img/smilies/".$s['image']."\" alt=\"".htmlentities($s['code'])."\" title=\"".htmlentities($s['code'])."\" onclick=\"insertSmiley(' ".str_replace("'", "\'", $s['code'])." ');\" />";
	}
	write("
			</div>
		</div>
	</div>
");
}

function DoPostHelp()
{
	write("
	<div class=\"PoRT margin\" style=\"width: 90%;\">
		<div class=\"errort\"><strong>".__("Post help")."</strong></div>
		<div class=\"errorc cell0\">
			<button class=\"expander\" id=\"postHelpExpand\" onclick=\"expandPostHelp();\">&#x25BC;</button>
			<div id=\"commonHelp\" class=\"left\">
				<h4>".__("Presentation")."</h4>
				[b]&hellip;[/b] &mdash; <strong>".__("bold type")."</strong> <br />
				[i]&hellip;[/i] &mdash; <em>".__("italic")."</em> <br />
				[u]&hellip;[/u] &mdash; <span class=\"underline\">".__("underlined")."</span> <br />
				[s]&hellip;[/s] &mdash; <del>".__("strikethrough")."</del><br />
			</div>
			<div id=\"expandedHelp\" class=\"left\">
				[code]&hellip;[/code] &mdash; <code>".__("code block")."</code> <br />
				[spoiler]&hellip;[/spoiler] &mdash; ".__("spoiler block")." <br />
				[spoiler=&hellip;]&hellip;[/spoiler] <br />
				[source]&hellip;[/source] &mdash; ".__("colorcoded block, assuming C#")." <br />
				[source=&hellip;]&hellip;[/source] &mdash; ".__("colorcoded block, specific language")."<sup title=\"bnf, c, cpp, csharp, html4strict, irc, javascript, lolcode, lua, mysql, php, qbasic, vbnet, xml\">[".__("which?")."]</sup> <br />
	");
	$bucket = "postHelpPresentation"; include("./lib/pluginloader.php");
	write("
				<br />
				<h4>".__("Links")."</h4>
				[img]http://&hellip;[/img] &mdash; ".__("insert image")." <br />
				[url]http://&hellip;[/url] <br />
				[url=http://&hellip;]&hellip;[/url] <br />
				>>&hellip; &mdash; ".__("link to post by ID")." <br />
				[user=##] &mdash; ".__("link to user's profile by ID")." <br />
	");
	$bucket = "postHelpLinks"; include("./lib/pluginloader.php");
	write("
				<br />
				<h4>".__("Quotations")."</h4>
				[quote]&hellip;[/quote] &mdash; ".__("untitled quote")."<br />
				[quote=&hellip;]&hellip;[/quote] &mdash; ".__("\"Posted by &hellip;\"")." <br />
				[quote=\"&hellip;\" id=\"&hellip;\"]&hellip;[/quote] &mdash; \"".__("\"Post by &hellip;\" with link by post ID")." <br />
	");
	$bucket = "postHelpQuotations"; include("./lib/pluginloader.php");
	write("
				<br />
				<h4>".__("Embeds")."</h4>
	");
	$bucket = "postHelpEmbeds"; include("./lib/pluginloader.php");
	write("
			</div>
			<br />
			".__("Most plain HTML also allowed.")."
		</div>
	</div>
	");
}

function OnlineUsers($forum = 0, $update = true)
{
	global $loguserid;
	$forumClause = "";
	$browseLocation = __("online");

	if ($update)
	{
		if ($loguserid)
			Query("UPDATE users SET lastforum=".$forum." WHERE id=".$loguserid);
		else
			Query("UPDATE guests SET lastforum=".$forum." WHERE ip='".$_SERVER['REMOTE_ADDR']."'");
	}

	if($forum)
	{
		$forumClause = " and lastforum=".$forum;
		$forumName = FetchResult("SELECT title FROM forums WHERE id=".$forum);
		$browseLocation = format(__("browsing {0}"), $forumName);
	}

	$rOnlineUsers = Query("select id,name,displayname,sex,powerlevel,lastactivity,lastposttime,minipic from users where (lastactivity > ".(time()-300)." or lastposttime > ".(time()-300).")".$forumClause." order by name");
	$onlineUsers = "";
	$onlineUserCt = 0;
	while($user = Fetch($rOnlineUsers))
	{
		$bucket = "userMangler"; include("./lib/pluginloader.php");
		$loggedIn = ($user['lastpost'] <= $user['lastview']);
		$userLink = UserLink($user);
		if($user['minipic'])
			$userLink = "<a href=\"profile.php?id=".$user['id']."\"><img src=\"".$user['minipic']."\" alt=\"\" class=\"minipic\"></a>&nbsp;".$userLink;
		if(!$loggedIn)
			$userLink = "(".$userLink.")";
		$onlineUsers.=($onlineUserCt ? ", " : "").$userLink;
		$onlineUserCt++;
	}
	//$onlineUsers = $onlineUserCt." "user".(($onlineUserCt > 1 || $onlineUserCt == 0) ? "s" : "")." ".$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;
	$onlineUsers = Plural($onlineUserCt, __("user"))." ".$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;

	$guests = FetchResult("select count(*) from guests where bot=0 and date > ".(time() - 300).$forumClause);
	$bots = FetchResult("select count(*) from guests where bot=1 and date > ".(time() - 300).$forumClause);

	if($guests)
		$onlineUsers .= " | ".Plural($guests,__("guest"));
	if($bots)
		$onlineUsers .= " | ".Plural($bots,__("bot"));

	return $onlineUsers;
}

?>
