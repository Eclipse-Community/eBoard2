<?php
//  AcmlmBoard XD support - Login support

$bots = array(
	"Microsoft URL Control",
	"msnbot",
	"Yahoo! Slurp",
	"Googlebot",
	"Mediapartners-Google",
	"yetibot@naver.com",
	"Twiceler",
	"YandexBot",
	"Baiduspider",
	"facebook",
	"bot","spider", //catch-all
);

$isBot = 0;
if(str_replace($bots,"x",$_SERVER['HTTP_USER_AGENT']) != $_SERVER['HTTP_USER_AGENT'])
	$isBot = 1;

include("browsers.php");

//Check the amount of users right now for the records
$qMisc = "select * from misc";
$rMisc = Query($qMisc);
$misc = Fetch($rMisc);
if ($misc['version'] < 226)
{
	header('Location: install.php');
	exit;
}
$qOnlineUsers = "select id, powerlevel, sex, name from users where lastactivity > ".(time()-300)." or lastposttime > ".(time()-300)." order by name";
$rOnlineUsers = Query($qOnlineUsers);
$qRecords = ""; //Thanks for inspiring me to check this out, Blackhole ;)
$onlineUsers = "";
$onlineUserCt = 0;
while($onlineUser = Fetch($rOnlineUsers))
{
	$onlineUsers .= ($onlineUserCt ? ", " : "").UserLink($onlineUser);
	$onlineUserCt++;
}
if($onlineUserCt > $misc['maxusers'])
{
	$qRecords = "maxusers = ".$onlineUserCt.", maxusersdate = ".time().", maxuserstext = '".justEscape($onlineUsers)."'";
}
//Check the amount of posts for the record
$qNewToday = "select count(*) from posts where date > ".(time() - 86400);
$newToday = FetchResult($qNewToday);
$qNewLastHour = "select count(*) from posts where date > ".(time() - 3600);
$newLastHour = FetchResult($qNewLastHour);
if($newToday > $misc['maxpostsday'])
{
	if($qRecords) $qRecords .= ", ";
	$qRecords .= "maxpostsday = ".$newToday.", maxpostsdaydate = ".time();
}
if($newLastHour > $misc['maxpostshour'])
{
	if($qRecords) $qRecords .= ", ";
	$qRecords .= "maxpostshour = ".$newLastHour.", maxpostshourdate = ".time();
}
if($qRecords)
{
	$qRecords = "update misc set ".$qRecords;
	$rRecords = Query($qRecords);
}

//Delete oldies visitor from the guest list. We may re-add him/her later.
$qGuests = "delete from guests where ip='".$_SERVER['REMOTE_ADDR']."' or date < ".(time()-300);
$rGuests = Query($qGuests);

//Lift dated Tempbans
$qTempban = "update users set powerlevel = tempbanpl, tempbantime = 0 where tempbantime != 0 and tempbantime < ".time();
$rTempban = Query($qTempban);

//Lift dated IP Bans
$qIPBan = "delete from ipbans where date != 0 and date < ".time();
$rIPBan = Query($qIPBan);

//Do IP Ban check
$qIPBan = "select * from ipbans where instr('".$_SERVER['REMOTE_ADDR']."', ip)=1";
$rIPBan = Query($qIPBan);
if(NumRows($rIPBan))
{
	$ipban = Fetch($rIPBan);
	print "You have been ".($ipban['date'] ? "" : "<strong>permanently</strong> ")."IP-banned from this board".($ipban['date'] ? " until ".gmdate("M jS Y, G:i:s",$ipban['date'])." (GMT). That's ".TimeUnits($ipban['date']-time())." left" : "").". Attempting to get around this in any way will result in worse things.";
	exit();
}

if(FetchResult("select count(*) from proxybans where instr('".$_SERVER['REMOTE_ADDR']."', ip)=1"))
	die("No.");


$logdata = unserialize(base64_decode($_COOKIE['logdata']));
$loguserid = (int)$logdata['loguserid'];
$loguserbull = $logdata['bull'];

$wantGuest = TRUE;

if($loguserid) //Are we logged in?
{
	//$qLogUser = "select * from users where id=".(int)$loguserid." and password='".justEscape($loguserpw)."'";
	$qLogUser = "select * from users where id=".(int)$loguserid;
	$rLogUser = Query($qLogUser);
	if(NumRows($rLogUser)) //We have at least one result.
	{
		$loguser = Fetch($rLogUser);

		//Bullcheck
		$ourbull = hash('sha256', $loguser['id'].$loguser['password'].$salt.$loguser['pss'], FALSE);
		if($loguserbull == $ourbull)
		{
			$rLastView = "update users set lastactivity=".time().", lastip='".$_SERVER['REMOTE_ADDR']."', lasturl='".justEscape($thisURL)."', lastknownbrowser='".justEscape($lastKnownBrowser)."' where id=".$loguserid;
			if(!$noOnlineUsers)
				$qLastView = Query($rLastView);

			$dateformat = $loguser['dateformat'].", ".$loguser['timeformat'];

			$wantGuest = FALSE;
		}
	}
}

if($wantGuest)
{
	$qGuest = "insert into guests (date, ip, lasturl, useragent, bot) values (".time().", '".$_SERVER['REMOTE_ADDR']."', '".justEscape($thisURL)."', '".justEscape($_SERVER['HTTP_USER_AGENT'])."', ".$isBot.")";
 	if(!$noOnlineUsers)
 		$rGuest = Query($qGuest);

	$loguser = array("name"=>"", "powerlevel"=>0, "threadsperpage"=>50, "postsperpage"=>20, "theme"=>"default", "dateformat"=>"m-d-y", "timeformat"=>"h:i A", "fontsize"=>80, "timezone"=>0, "blocklayouts"=>$noGuestLayouts);
	$loguserid = 0;
}

if($hacks['forcetheme'] != "" && !isset($loguser['theme']))
	$loguser['theme'] = $hacks['forcetheme'];

?>
