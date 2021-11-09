<?php

//$isFirefox = FALSE;
//$isIE6 = FALSE;

//$lastKnownBrowser = $_SERVER['HTTP_USER_AGENT'];
$lastKnownBrowser = "Unknown";

//Opera/9.80 (iPhone; Opera Mini/5.0.0176/764; U; en) Presto/2.4.15

$knownBrowsers = array
(
	"IE"=>"Internet Explorer",
	"Edge"=>"Microsoft Edge",
	"NetSurf"=>"NetSurf",
	"Netscape"=>"Netscape",
	"Opera"=>"Opera",
	"Opera Mini"=>"Opera Mini",
	"Otter"=>"Otter Browser",
	"Falkon"=>"Falkon",
	"PLAYSTATION 3" => "PlayStation 3",
	"PlayStation 4" => "PlayStation 4",
	//Goanna-based browsers
	"Mypal"=>"Mypal",
	"K-Meleon"=>"K-Meleon",
	"PaleMoon"=>"Pale Moon",
	//Gecko-based browsers
	"SeaMonkey"=>"SeaMonkey",
	"Waterfox"=>"Waterfox",
	"Firefox"=>"Firefox",
	//Blink-based browsers
	"OPR"=>"Opera",
	"360Chrome"=>"360 Extreme Explorer",
	"Yandex"=>"Yandex Browser",
	"Maxthon"=>"Maxthon",
	"Chrome"=>"Chrome",
	// Generic names
	"Trident"=>"Trident",
	"Goanna"=>"Goanna",
	"Gecko"=>"Gecko",
	"AppleWebKit"=>"AppleWebKit",
	"KHTML"=>"Konqueror",
	"Mozilla"=>"Mozilla",
);

$knownOSes = array
(
	// Windows
	"Windows 95"=>"Windows 95",
	"Windows 98"=>"Windows 98",
	"Windows NT 4.0"=>"Windows NT 4.0",
	"Windows NT 5.0"=>"Windows 2000",
	"Windows NT 5.5"=>"Windows Neptune", // UwU
	"Windows NT 5.1"=>"Windows XP",
	"Windows NT 5.2"=>"Windows Server 2003",
	"Windows NT 6.0"=>"Windows Vista",
	"Windows NT 6.1"=>"Windows 7",
	"Windows NT 6.2"=>"Windows 8",
	"Windows NT 6.3"=>"Windows 8.1",
	"Windows NT 6.4"=>"Windows 10 Preview",
	"Windows NT 10.0"=>"Windows 10",
	// Linux/BSD
	"Debian"=>"Debian",
	"Arch Linux"=>"Arch Linux",
	"Fedora"=>"Fedora",
	"Solus"=>"Solus",
	"Slackware"=>"Slackware",
	"Ubuntu"=>"Ubuntu",
	"Linux"=>"Linux %",
	"FreeBSD"=>"FreeBSD",
	"OpenBSD"=>"OpenBSD",
	"NetBSD"=>"NetBSD", // CRMX will like this
	// Mobile OS
	"iPad" => "iOS %",
	"iPod touch"=>"iOS %",
	"iPhone"=>"iOS %",
	"Mac OS X"=>"macOS %",
	"Android"=>"Android %", // descargar
);

$ua = $_SERVER['HTTP_USER_AGENT'];

foreach($knownBrowsers as $code => $name)
{
	if (strpos($ua, $code) !== FALSE)
	{
		//$version = substr($ua, strpos($ua, $code) + strlen($code), 6);
		//$version = preg_replace('/[^0-9,.]/','',$version);
		
		$versionStart = strpos($ua, $code) + strlen($code);
		$version = GetVersion($ua, $versionStart);

		//Opera Mini wasn't detected properly because of the Opera 10 hack.
		if (strpos($ua, "Opera/9.80") !== FALSE && $code != "Opera Mini" || $code == "Safari" && strpos($ua, "Version/") !== FALSE)
			$version = substr($ua, strpos($ua, "Version/") + 8);

		//$isFirefox = ($code == "Firefox");
		//$isIE6 = (strpos($ua, "MSIE 6.") !== FALSE);

		$lastKnownBrowser = $name." ".$version;
		break;
	}
}

$browserName = $name;
$browserVers = (float)$version;

$os = "";
foreach($knownOSes as $code => $name)
{
	if (strpos($ua, $code) !== FALSE)
	{
		$os = $name;
		
		if(strpos($name, "%") !== FALSE)
		{
			$versionStart = strpos($ua, $code) + strlen($code);
			$version = GetVersion($ua, $versionStart);
			$os = str_replace("%", $version, $os);
		}
	
		$lastKnownBrowser = format(__("{0} on {1}"), $lastKnownBrowser, $os);
		break;
	}
}

$lastKnownBrowser .= "<!-- ".htmlspecialchars($ua)." -->"; 

function GetVersion($ua, $versionStart)
{
	$numDots = 0;
	$version = "";
	for($i = $versionStart; $i < strlen($ua); $i++)
	{
		$ch = $ua[$i];
		if($ch == '_' && strpos($ua, "Mac OS X"))
			$ch = '.';
		if($ch == '.')
		{
			$numDots++;
			if($numDots == 3)
				break;
			$version .= '.';
		}
		else if(strpos("0123456789.-", $ch) !== FALSE)
			$version .= $ch;
		else if(strpos(":/", $ch) !== FALSE)
			continue;
		else if(!$numDots)
		{
			preg_match('/\G\w+/', $ua, $matches, 0, $versionStart + 1);
			return $matches[0];
		}
		else
			break;
	}
	return $version;
}

?>