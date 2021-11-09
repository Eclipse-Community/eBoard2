<?php

$pluginSettings = array();
$plugins = array();
$pluginbuckets = array();

function registerSetting($settingname, $label, $check = false)
{
	//global $pluginSettings;
	//$pluginSettings[$settingname] = array("label" => $label, "value" => "", "check" => $check);
}

function getSetting($settingname, $useUser = false)
{
	global $pluginSettings, $user;
	if(!$useUser) //loguser
	{
		if(array_key_exists($settingname, $pluginSettings))
			return $pluginSettings[$settingname]["value"];
	}
	else if($user['pluginsettings'] != "");
	{
		$settings = unserialize($user['pluginsettings']);
		if(!is_array($settings))
			return "";
		if(array_key_exists($settingname, $settings))
			return stripslashes(urldecode($settings[$settingname]));
	}
	return "";
}

//assume this to prevent plugincide
if($misc['version'] < 220)
	$misc['version'] == 220;

$pluginsDir = @opendir("plugins");
if($pluginsDir !== FALSE)
{
	while(($plugin = readdir($pluginsDir)) !== FALSE)
	{
		if($plugin == "." || $plugin == "..") continue;
		if(is_dir("./plugins/".$plugin))
		{
			$plugins[$plugin] = array();
			$plugins[$plugin]['dir'] = $plugin;
			if(file_exists("./plugins/".$plugin."/plugin.settings"))
			{
				$settingsFile = file_get_contents("./plugins/".$plugin."/plugin.settings");
				$settings = explode("\n", $settingsFile);
				foreach($settings as $setting)
				{
					$setting = explode("=", $setting);
					if($setting[0][0] == "#") continue;
					if($setting[0][0] == "$")
						registerSetting(substr($setting[0],1), $setting[1]);
					else
						$plugins[$plugin][$setting[0]] = $setting[1];
					$minver = 220; //we introduced these plugins in 2.2.0 so assume this.
					if($setting[0] == "minversion")
						$minver = (int)$setting[1];
				}
				if($minver > $misc['version'])
				{
					Report(Format("Disabled plugin \"{0}\" -- meant for a later version.", $plugin), 1);
					rename("./plugins/".$plugin."/plugin.settings", "./plugins/".$plugin."/plugin.disabled");
					unset($plugins[$plugin]);
					continue;
				}
				$dir = "./plugins/".$plugins[$plugin]['dir'];
				$pdir = @opendir($dir);
				while($f = readdir($pdir))
				{
					if(substr($f, (strlen($f) - 4), 4) == ".php")
						$pluginbuckets[substr($f, 0, strlen($f) - 4)][] = $plugins[$plugin]['dir'];
				}
			}
			else
			{
				unset($plugins[$plugin]);
				continue;
			}
		}
	}
	
	$bucket = "extrasettings"; include("./lib/pluginloader.php");
} else mkdir("plugins");

if($loguser['pluginsettings'] != "")
{
	$settings = unserialize($loguser['pluginsettings']);
	if(!is_array($settings))
		$settings = array();
	foreach($settings as $setName => $setVal)
		if(array_key_exists($setName, $pluginSettings))
			$pluginSettings[$setName]["value"] = stripslashes(urldecode($setVal));
}

?>
