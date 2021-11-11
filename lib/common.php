<?php
// AcmlmBoard XD support - Main hub

include("wrapper.php");

// Leaving this error in until Niko's new installer is there to whine about it instead. -- Kawa
if(ini_get('register_globals'))
	die("<p>PHP, as it is running on this server, has the <code>register_globals</code> setting turned on. This is something of a security hazard, and is a <a href=\"http://en.wikipedia.org/wiki/Deprecation\" target=\"_blank\">deprecated function</a>. For more information on this topic, please refer to the <a href=\"http://php.net/manual/en/security.globals.php\" target=\"_blank\">PHP manual</a>.</p><p>At any rate, the ABXD messageboard software is designed to run with <code>register_globals</code> turned <em>off</em>. If your provider allows the use of <code>.htaccess</code> files, you can try adding the line <code>php_flag register_globals off</code> to an <code>.htaccess</code> file in your board's root directory, though we suggest placing it on your website root directory (often something like <code>public_html</code>). If not, ask your provider to edit <code>php.ini</code> accordingly and make the internet a little safer for all of us.</p>");

// I can't believe there are PRODUCTION servers that have E_NOTICE turned on. What are they THINKING? -- Kawa
// E_DEPRECATED is disabled, because mysql_ extension is now deprecated
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED | E_STRICT);

if(!is_file("lib/database.php"))
	die("You should <a href=\"install.php\">install</a> the board database first.");
	
// Deslash GPC variables if we have magic quotes on
if (get_magic_quotes_gpc())
{
	function AutoDeslash($val)
	{
		if (is_array($val))
			return array_map('AutoDeslash', $val);
		else if (is_string($val))
			return stripslashes($val);
	}
	
	$_REQUEST = array_map('AutoDeslash', $_REQUEST);
	$_GET = array_map('AutoDeslash', $_GET);
	$_POST = array_map('AutoDeslash', $_POST);
	$_COOKIE = array_map('AutoDeslash', $_COOKIE);
}

include("salt.php");

include("settings.php");
include("snippets.php");
if($ajax)
	$overallTidy = 0;
//if($overallTidy)
	ob_start("DoFooter");

date_default_timezone_set("GMT");
$timeStart = usectime();

if(!isset($title))
	$title = "";

//WARNING: These things need to be kept in a certain order of execution.

include("mysql.php");
//include("supersqlescape.php");
include("feedback.php");

$thisURL = $_SERVER['SCRIPT_NAME'];
if($q = $_SERVER['QUERY_STRING'])
	$thisURL .= "?$q";

include("loguser.php");
include "pluginsystem.php";

if(!isset($noViewCount))
	include("views.php");

include("tidy.php");
include("post.php");

include("css/themelist.php");
$themeFiles = array_keys($themes);
$themeNames = array_values($themes);
$theme = $loguser['theme'];
$themeFile = $theme.".css";
if(!file_exists("css/".$themeFile))
{
	$themeFile = $theme.".php";
	if(!file_exists("css/".$themeFile))
		$themeFile = "default.css";
}
$logopic = "img/themes/default/logo.gif";
if(file_exists("img/themes/".$theme."/logo.gif"))
	$logopic = "img/themes/".$theme."/logo.gif";

include("language.php");

if(!isset($noAutoHeader))
	include("header.php");


function justEscape($text)
{
	return mysql_real_escape_string($text);
}

function deSlashMagic($text)
{
	return $text;
}

//Simple version -- may expand later.
function CheckTableBreaks($text)
{
	$text = strtolower(CleanUpPost($text));
//	$openers = substr_count($text, "<table") + substr_count($text, "<div") + substr_count($text, "[quote");
//	$closers = substr_count($text, "</table>") + substr_count($text, "</div>") + substr_count($text, "[/quote]");
//	return ($openers != $closers);
	$tabO = substr_count($text, "<table");
	$tabC = substr_count($text, "</table>");
	$divO = substr_count($text, "<div");
	$divC = substr_count($text, "</div>");
	$quoO = substr_count($text, "[quote");
	$quoC = substr_count($text, "[/quote]");
	$spoO = substr_count($text, "[spoiler");
	$spoC = substr_count($text, "[/spoiler]");
	if($tabO != $tabC) return true;
	if($divO != $divC) return true;
	if($quoO != $quoC) return true;
	if($spoO != $spoC) return true;
	return false;
}

function htmlval($text)
{
	//$text = str_replace("\"", "&quot;", $text);
	//$text = str_replace("<", "&lt;", $text);
	//return $text;
	//return htmlentities($text, ENT_COMPAT, "UTF-8");
	return htmlspecialchars($text);
}

function filterPollColors($input)
{
/*
	$valid = "#0123456789ABCDEFabcdef";
	$output = "";
	for($i = 0; $i < strlen($input); $i++)
		if(strpos($valid, $input[$i]) !== FALSE)
			$output .= $input[$i];
	return $output;
*/
	return preg_replace("@[^#0123456789abcdef]@si", "", $input);
}

function RecalculateKarma($uid)
{
	$karma = 0;
	$karmaWeights = array(5, 10, 10, 15, 15);
	$qKarma = "select powerlevel, up from uservotes left join users on id=voter where uid=".$uid." and powerlevel > -1";
	$rKarma = Query($qKarma);
	while($k = Fetch($rKarma))
	{
		if($k['up'])
			$karma += $karmaWeights[$k['powerlevel']];
		else
			$karma -= $karmaWeights[$k['powerlevel']];
	}
	Query("update users set karma=".$karma." where id=".$uid);
	return $karma;
}

function ParseThreadTags(&$title)
{
	preg_match_all("/\[(.*?)\]/", $title, $matches);
	foreach($matches[1] as $tag)
	{
		$title = str_replace("[".$tag."]", "", $title);
		$tag = htmlentities(strip_tags(strtolower($tag)));
		
		//Start at a hue that makes "18" red.
		$hash = -105;
		for($i = 0; $i < strlen($tag); $i++)
			$hash += ord($tag[$i]);

		//That multiplier is only there to make "nsfw" and "18" the same color.
		$color = "hsl(".(($hash * 57) % 360).", 70%, 40%)";
		
		$tags .= "<span class=\"threadTag\" style=\"background-color: ".$color.";\">".$tag."</span>";
	}
	if($tags)
		$tags = " ".$tags;
	return $tags;
}

function cdate($format, $date = 0)
{
	global $loguser;
	if($date == 0)
		$date = time(); //gmmktime(); //removed for E_STRICT
	$hours = (int)($loguser['timezone']/3600);
	$minutes = floor(abs($loguser['timezone']/60)%60);
	$plusOrMinus = $hours < 0 ? "" : "+";
	$timeOffset = $plusOrMinus.$hours." hours, ".$minutes." minutes";
	return gmdate($format, strtotime($timeOffset, $date));
}

function GetFullURL()
{
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on")
		$pageURL .= "s";
	$pageURL .= "://";
	
	if ($_SERVER["SERVER_PORT"] != "80")
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

	return $pageURL;
}

function Report($stuff, $hidden = 0, $severity = 0)
{
	//$here = "http://helmet.kafuka.org/nikoboard";
	$full = GetFullURL();
	$here = substr($full, 0, strrpos($full, "/"))."/";
	
	if ($severity == 2)
		$req = "'".justEscape(base64_encode(serialize($_REQUEST)))."'";
	else
		$req = 'NULL';
	
	Query("insert into reports (ip,user,time,text,hidden,severity,request) 
		values ('".$_SERVER['REMOTE_ADDR']."', ".(int)$loguserid.", ".time().", '".justEscape(str_replace("#HERE#", $here, $stuff))."', ".$hidden.", ".$severity.", ".$req.")");
	Query("delete from reports where time < ".(time() - (60*60*24*30)));
}

function AssertForbidden($to, $specifically = 0)
{
	global $loguser, $forbidden;
	if(!isset($forbidden))
		$forbidden = explode(" ", $loguser['forbiddens']);
	
	$caught = 0;
	if(in_array($to, $forbidden))
		$caught = 1;
	else
	{
		$specific = $to."[".$specifically."]";
		if(in_array($specific, $forbidden))
			$caught = 2;
	}
	
	if($caught)
	{
		$not = __("You are not allowed to {0}.");
		$messages = array
		(
			"addRanks" => __("add new ranks"),
			"blockLayouts" => __("block layouts"),
			"deleteComments" => __("delete usercomments"),
			"editCats" => __("edit the forum categories"),
			"editForum" => __("edit the forum list"),
			"editIPBans" => __("edit the IP ban list"),
			"editMods" => __("edit Local Moderator assignments"),
			"editMoods" => __("edit your mood avatars"),
			"editPoRA" => __("edit the PoRA box"),
			"editPost" => __("edit posts"),
			"editProfile" => __("edit your profile"),
			"editSettings" => __("edit the board settings"),
			"editSmilies" => __("edit the smiley list"),
			"editThread" => __("edit threads"),
			"editUser" => __("edit users"),
			"haveCookie" => __("have a cookie"),
			"listPosts" => __("see all posts by a given user"),
			"makeComments" => __("post usercomments"),
			"makeReply" => __("reply to threads"),
			"makeThread" => __("start new threads"),
			"optimize" => __("optimize the tables"),
			"purgeRevs" => __("purge old revisions"),
			"recalculate" => __("recalculate the board counters"),
			"search" => __("use the search function"),
			"sendPM" => __("send private messages"),
			"snoopPM" => __("view other users' private messages"),
			"useUploader" => __("upload files"),
			"viewAdminRoom" => __("see the admin room"),
			"viewAvatars" => __("see the avatar library"),
			"viewCalendar" => __("see the calendar"),
			"viewForum" => __("view fora"),
			"viewLKB" => __("see the Last Known Browser table"),
			"viewMembers" => __("see the memberlist"),
			"viewOnline" => __("see who's online"),
			"viewPM" => __("view private messages"),
			"viewProfile" => __("view user profiles"),
			"viewRanks" => __("see the rank lists"),
			"viewRecords" => __("see the top scores and DB usage"),
			"viewThread" => __("read threads"),
			"viewUploader" => __("see the uploader"),
			"vote" => __("vote"),
		);
		$messages2 = array
		(
			"viewForum" => __("see this forum"),
			"viewThread" => __("read this thread"),
			"makeReply" => __("reply in this thread"),
			"editUser" => __("edit this user"),
		);
		$bucket = "forbiddens"; include("./lib/pluginloader.php");
		if($caught == 2 && array_key_exists($to, $messages2))
			Kill(format($not, $messages2[$to]), __("Permission denied."));
		Kill(format($not, $messages[$to]), __("Permission denied."));
	}
}

function IsAllowed($to, $specifically = 0)
{
	global $loguser, $forbidden;
	if(!isset($forbidden))
		$forbidden = explode(" ", $loguser['forbiddens']);
	if(in_array($to, $forbidden))
		return FALSE;
	else
	{
		$specific = $to."[".$specifically."]";
		if(in_array($specific, $forbidden))
			return FALSE;
	}
	return TRUE;
}

function SendSystemPM($to, $message, $title)
{
	global $systemUser;
	
	//Don't send system PMs if no System user was set
	if($systemUser == 0)
		return;

	$qPM = "insert into pmsgs (userto, userfrom, date, ip, msgread) values (".$to.", ".$systemUser.", ".time().", '127.0.0.1', 0)";
	$rPM = Query($qPM);
	$pid = mysql_insert_id();
	$qPM = "insert into pmsgs_text (pid, text, title) values (".$pid.", '".justEscape($message)."', '".justEscape($title)."')";
	$rPM = Query($qPM);
	
	//print "PM sent.";
}

function Shake()
{
	$cset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$salt = "";
	$chct = strlen($cset) - 1;
	while (strlen($salt) < 16)
		$salt .= $cset[mt_rand(0, $chct)];
	return $salt;
}

function IniValToBytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last)
    {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function BytesToSize($size, $retstring = '%01.2f&nbsp;%s')
{
	$sizes = array('B', 'KiB', 'MiB');
	$lastsizestring = end($sizes);
	foreach($sizes as $sizestring)
	{
		if($size < 1024)
			break;
		if($sizestring != $lastsizestring)
			$size /= 1024;
	}
	if($sizestring == $sizes[0])
		$retstring = '%01d %s'; // Bytes aren't normally fractional
	return sprintf($retstring, $size, $sizestring);
}

include("write.php");
$bucket = "init"; include('lib/pluginloader.php');

?>
