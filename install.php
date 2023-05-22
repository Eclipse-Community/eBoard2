<?php

$noViewCount = TRUE;
$noOnlineUsers = TRUE;
$noFooter = TRUE;
error_reporting(~E_NOTICE);

function runBucket($blar)
{
	;
}
function IsAllowed()
{
	return false;
}
function cdate($format, $date = 0)
{
	global $loguser;
	if($date == 0)
		$date = time();
	$hours = (int)($loguser['timezone']/3600);
	$minutes = floor(abs($loguser['timezone']/60)%60);
	$plusOrMinus = $hours < 0 ? "" : "+";
	$timeOffset = $plusOrMinus.$hours." hours, ".$minutes." minutes";
	return gmdate($format, strtotime($timeOffset, $date));
}
$loguser['fontsize'] = 80;
$themeFile = "default.css";
include("lib/snippets.php");
include("lib/settings.php");
$logopic = "img/themes/default/logo.gif";
$overallTidy = 0;
unset($misc['porabox']);
$title = "Installation";
//ob_start("DoFooter");
$timeStart = usectime();
include("lib/feedback.php");
include("lib/header.php");
include("lib/write.php");
@mkdir("img/avatars");
@mkdir("img/minipics");
@mkdir("uploader");


if(is_file("lib/database.php")) {
	include("lib/database.php");
	// It's update! Check if the version is really newer.
	include("lib/mysql.php");
	$misc = Fetch(Query("select * from misc"));
	//if($misc['version'] >= 230) {
	//	Kill("Updating to current version?");
	//}
	mysqli_close($dblink);
}
else {
	$dbserv = "localhost";
}

if(isset($_GET['delete']))
{
	unlink("install.php") or Kill("Could not delete installation script.");
	Redirect("Installation file removed.","./","the main page");
}

if(!isset($_POST['action']))
{
	//Begin Pyra-proofing...
	$test = @fopen("test.txt", "w");
	if($test === FALSE)
		Kill(format("PHP does not seem to have write access to this directory ({0}). This is required for proper functionality. Please contact your hosting provider for information on how to make the current directory writable.", $_SERVER['DOCUMENT_ROOT']), "Filesystem permission error");
	else
	{
		fclose($test);
		unlink("test.txt");
	}
	$test = @fopen("lib/test.txt", "w");
	if($test === FALSE)
		Kill(format("PHP does not seem to have write access to the /{1} directory ({0}/{1}). This is required for proper functionality. Please contact your hosting provider for information on how to make that directory writable.", $_SERVER['DOCUMENT_ROOT'], "lib"), "Filesystem permission error");
	else
	{
		fclose($test);
		unlink("lib/test.txt");
	}
	$test = @fopen("img/avatars/test.txt", "w");
	if($test === FALSE)
		Kill(format("PHP does not seem to have write access to the /{1} directory ({0}/{1}). This is required for proper functionality. Please contact your hosting provider for information on how to make that directory writable.", $_SERVER['DOCUMENT_ROOT'], "img/avatars"), "Filesystem permission error");
	else
	{
		fclose($test);
		unlink("img/avatars/test.txt");
	}

	write(
"
	<form action=\"install.php\" method=\"post\">
		<table class=\"outline margin width50\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Installation options
				</th>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dbs\">Database server</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"dbs\" name=\"dbserv\" style=\"width: 98%;\" value=\"{0}\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dbn\">Database name</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"dbn\" name=\"dbname\" style=\"width: 98%;\" value=\"{3}\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dun\">Database user name</label>
				</td>
				<td class=\"cell1\">
					<input type=\"text\" id=\"dun\" name=\"dbuser\" style=\"width: 98%;\" value=\"{1}\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"dpw\">Database user password</label>
				</td>
				<td class=\"cell1\">
					<input type=\"password\" id=\"dpw\" name=\"dbpass\" style=\"width: 98%;\" value=\"{2}\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					Options
				</td>
				<td class=\"cell1\">
					<label>
						<input type=\"checkbox\" id=\"b\" name=\"addbase\" />
						Add starting forums and the usual Super Mario rankset
					</label>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"Install\" />
				</td>
			</tr>
			<tr class=\"cell2\">
				<td colspan=\"2\">
					<strong>Warning</strong> &mdash;
					When updating, <em>back up your database</em> before you press the \"Install\" button and don't check the \"add starting forums\" box.
				</td>
			</tr>
		</table>
	</form>
", $dbserv, $dbuser, $dbpass, $dbname);

}
else if($_POST['action'] == "Install")
{
	print "<div class=\"outline faq\">";
	print "Trying to connect to database&hellip;<br />";
	$dbserv = $_POST['dbserv'];
	$dbuser = $_POST['dbuser'];
	$dbpass = $_POST['dbpass'];
	$dbname = $_POST['dbname'];
	//2005: no such server
	//1045: no such user
	$dblink = mysqli_connect($dbserv, $dbuser, $dbpass, $dbname) or Kill(mysqli_errno($dblink) == 2005 ? format("Could not connect to any database server at {0}. Usually, the database server runs on the same system as the web server, in which case \"localhost\" would suffice. If not, the server could be (temporarily) offline, nonexistant, or maybe you entered a full URL instead of just a hostname (\"http://www.mydbserver.com\" instead of just \"mydb.com\").", $dbserv) : format("The database server has rejected your username and/or password."), "Database connectivity error");
	mysqli_set_charset($dblink, 'utf8mb4');
	mysqli_query($dblink, "SET NAMES 'utf8mb4';");
	mysqli_query($dblink, "SET CHARACTER SET 'utf8mb4';");
	mysqli_query($dblink, "SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci';");
	
	print "Writing database configuration file&hellip;<br />";
	$dbcfg = @fopen("lib/database.php", "w+") or Kill(format("Could not open \"lib/{0}.php\" for writing. This has been checked for earlier, so if you see this error now, something very strange is going on.", "database"), "Mysterious filesystem permission error");
	fwrite($dbcfg, "<?php\n");
	fwrite($dbcfg, "//  AcmlmBoard XD support - Database settings\n\n");
	fwrite($dbcfg, '$dbserv = ' . var_export($dbserv, true) . ";\n");
	fwrite($dbcfg, '$dbuser = ' . var_export($dbuser, true) . ";\n");
	fwrite($dbcfg, '$dbpass = ' . var_export($dbpass, true) . ";\n");
	fwrite($dbcfg, '$dbname = ' . var_export($dbname, true) . ";\n");
	fwrite($dbcfg, "\n?>");
	fclose($dbcfg);

	include("lib/mysql.php");

	print "Detecting Tidy support&hellip; ";
	$tidy = (int)function_exists('tidy_repair_string');
	if($tidy)
		print "available.<br />";
	else
		print "not available.<br />";

	$shakeIt = false;
	if(!is_file("lib/salt.php"))
		$shakeIt = true;
	$miscStat = Query("show table status from ".$dbname." like 'misc'");
	if(NumRows($miscStat) == 0)
		$shakeIt = true;
	else
	{
		$shakeIt = false;
		$misc = Fetch(Query("select * from misc"));
		if($misc['version'] < 220)
		{
			$shakeIt = true;
			$sltf = @fopen("lib/salt.php", "w+") or Kill(format("Could not open \"lib/{0}.php\" for writing. This has been checked for earlier, so if you see this error now, something very strange is going on.", "salt"), "Mysterious filesystem permission error");
			fwrite($sltf, "<?php \$salt = \"sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH\" ?>\n");
			fclose($sltf);
		}
	}
	if($shakeIt)
	{
		print "Generating security salt&hellip;<br />";
		$cset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		$salt = "";
		$chct = strlen($cset) - 1;
		while (strlen($salt) < 16)
			$salt .= $cset[mt_rand(0, $chct)];
		$sltf = @fopen("lib/salt.php", "w+") or Kill(format("Could not open \"lib/{0}.php\" for writing. This has been checked for earlier, so if you see this error now, something very strange is going on.", "salt"), "Mysterious filesystem permission error");
		fwrite($sltf, "<?php \$salt = \"".$salt."\" ?>\n");
		fclose($sltf);
	}


	print "Writing board configuration file&hellip;<br />";
	include("lib/settings.php");
	$hax = @fopen("lib/settings.php", "w") or Kill(format("Could not open \"lib/{0}.php\" for writing. This has been checked for earlier, so if you see this error now, something very strange is going on.", "settings"), "Mysterious filesystem permission error");
	fputs($hax, "<?php\n");
	fputs($hax, "//Generated and parsed by the Board Settings admin panel.\n");
	fputs($hax, "\n");
	fputs($hax, "//Settings\n");
	fputs($hax, "\$boardname = ".var_export($boardname,true).";\n");
	fputs($hax, "\$logoalt = ".var_export($logoalt,true).";\n");
	fputs($hax, "\$logotitle = ".var_export($logotitle,true).";\n");
	fputs($hax, "\$dateformat = ".var_export($dateformat,true).";\n");
	fputs($hax, "\$autoLockMonths = ".var_export((int)$autoLockMonths,true).";\n");
	fputs($hax, "\$warnMonths = ".var_export((int)$warnMonths,true).";\n");
	fputs($hax, "\$customTitleThreshold = ".var_export((int)$customTitleThreshold,true).";\n");
	fputs($hax, "\$viewcountInterval = ".var_export((int)$viewcountInterval,true).";\n");
	fputs($hax, "\$overallTidy = ".var_export((int)$tidy,true).";\n");
	fputs($hax, "\$noAjax = ".var_export((int)$noAjax,true).";\n");
	fputs($hax, "\$noGuestLayouts = ".var_export((int)$noGuestLayouts,true).";\n");
	fputs($hax, "\$theWord = ".var_export($theWord,true).";\n");
	fputs($hax, "\$systemUser = ".var_export((int)$systemUser,true).";\n");
	fputs($hax, "\$minWords = ".var_export((int)$minWords,true).";\n");
	fputs($hax, "\$minSeconds = ".var_export((int)$minSeconds,true).";\n");
	fputs($hax, "\$uploaderCap = ".var_export((int)$uploaderCap,true).";\n");
	fputs($hax, "\$uploaderMaxFileSize = ".var_export((int)$uploaderMaxFileSize,true).";\n");
	fputs($hax, "\$uploaderWhitelist = ".var_export($uploaderWhitelist,true).";\n");
	fputs($hax, "\$mailResetFrom = ".var_export($mailResetFrom,true).";\n");
	fputs($hax, "\$lastPostsTimeLimit = ".var_export((int)$lastPostsTimeLimit,true).";\n");
	fputs($hax, "\n");
	fputs($hax, "//Hacks\n");
	fputs($hax, "\$hacks['forcetheme'] = ".var_export($hacks['forcetheme'],true).";\n");
	fputs($hax, "\$hacks['themenames'] = ".var_export((int)$hacks['themenames'],true).";\n");
	fputs($hax, "\n");
	fputs($hax, "//Profile Preview Post\n");
	fputs($hax, "\$profilePreviewText = ".var_export($profilePreviewText,true).";\n");
	fputs($hax, "\n");
	fputs($hax, "//Meta\n");
	fputs($hax, "\$metaDescription = ".var_export($metaDescription,true).";\n");
	fputs($hax, "\$metaKeywords = ".var_export($metaKeywords,true).";\n");
	fputs($hax, "\n");
	fputs($hax, "//RSS\n");
	fputs($hax, "\$feedname = ".var_export($feedname,true).";\n");
	fputs($hax, "\$rssblurb = ".var_export($rssblurb,true).";\n");
	fputs($hax, "\n");
	fputs($hax, "?>");
	fclose($hax);

	print "Creating/updating tables&hellip;<br />";
	//Query("DROP TABLE IF EXISTS `smilies`");
	Upgrade();

	print "Adding bare neccesities&hellip;<br />";
	$misc = Query("select * from misc");
	if(NumRows($misc) == 0)
		Query("INSERT INTO `misc` (`views`, `hotcount`, `porabox`, `poratitle`, `milestone`, `maxuserstext`) VALUES (0, 30, '', 'Points of Required Attention', 'Nothing yet.', 'Nobody yet.');");
	Query("UPDATE `misc` SET `version` = 230");
	$smilies = Query("select * from smilies");
	if(NumRows($smilies) == 0)
		Query("
	INSERT INTO `smilies` (`code`, `image`) VALUES
	(':)', 'smile.png'),
	(';)', 'wink.png'),
	(':D', 'biggrin.png'),
	('o_o', 'blank.png'),
	(':awsum:', 'awsum.png'),
	('-_-', 'annoyed.png'),
	('o_O', 'bigeyes.png'),
	(':LOL:', 'lol.png'),
	(':O', 'jawdrop.png'),
	(':(', 'frown.png'),
	(';_;', 'cry.png'),
	('>:', 'mad.png'),
	('O_O', 'eek.png'),
	('8-)', 'glasses.png'),
	('^_^', 'cute.png'),
	('^^;;;', 'cute2.png'),
	('>_<', 'yuck.png'),
	('<_<', 'shiftleft.png'),
	('>_>', 'shiftright.png'),
	('@_@', 'dizzy.png'),
	('^~^', 'angel.png'),
	('>:)', 'evil.png'),
	('x_x', 'sick.png'),
	(':P', 'tongue.png'),
	(':S', 'wobbly.png'),
	(':[', 'vamp.png'),
	('~:o', 'baby.png'),
	(':YES:', 'yes.png'),
	(':NO:', 'no.png'),
	('<3', 'heart.png'),
	(':3', 'colonthree.png'),
	(':up:', 'approve.png'),
	(':down:', 'deny.png'),
	(':durr:', 'durrr.png'),
	('^^;', 'embarras.png'),
	(':barf:', 'barf.png'),
	('._.', 'ashamed.png'),
	('''.''', 'umm.png'),
	('''_''', 'downcast.png'),
	(':big:', 'teeth.png'),
	(':lawl:', 'lawl.png'),
	(':ninja:', 'ninja.png'),
	(':pirate:', 'pirate.png'),
	('D:', 'outrage.png'),
	(':sob:', 'sob.png'),
	(':XD:', 'xd.png'),
	(':yum:', 'yum.png');
");
	print "Reticulating uploader and usercomments where needed&hellip;<br />";
	Query("update `uploader` set `date` = `id` where `date` = 0;");
	Query("update `usercomments` set `date` = `id` where `date` = 0;");

	//Import("installTables.sql");
	if($_POST['addbase'])
	{
		print "Creating starting fora&hellip;<br />";
		Import("installDefaults.sql");
		rename("lib/settings_template.php","lib/settings_template.php");
	}

	print "<h3>Your board has been set up.</h3>";
	print "Things for you to do now:";
	print "<ul>";
	print "<li><a href=\"register.php\">Register your account</a> &mdash; the first to register gets to be Root.</li>";
	print "<li>Check out the <a href=\"admin.php\">administrator's toolkit</a>.</li>";
	print "<li><a href=\"install.php?delete=1\">Delete</a> the installation script.</li>";
	print "</ul>";
	//print "The installation script, being a security hazard if left alone, has been removed and replaced by the actual board index.";

	print "</div>";
}

//SQL importer based on KusabaX installer
function Import($sqlFile)
{
	$handle = fopen($sqlFile, "r");
	$data = fread($handle, filesize($sqlFile));
	fclose($handle);

	$sqlData = explode("\n", $data);
	//Filter out the comments and empty lines...
	foreach ($sqlData as $key => $sql)
		if (strstr($sql, "--") || strlen($sql) == 0)
			unset($sqlData[$key]);
	$data = implode("",$sqlData);
	$sqlData = explode(";",$data);
	foreach($sqlData as $sql)
	{
		if(strlen($sql) === 0)
			continue;
		if(strstr($sql, "CREATE TABLE `"))
		{
			$pos1 = strpos($sql, '`');
			$pos2 = strpos($sql, '`', $pos1 + 1);
			$tableName = substr($sql, $pos1+1, ($pos2-$pos1)-1);
			print "<li>".$tableName."</li>";
		}
		$query = str_replace("SEMICOLON", ";", $sql);
		Query($query);
	}
}

function deSlashMagic($text)
{
	if(get_magic_quotes_gpc())
		return stripslashes($text);
	else
		return $text;
}


function Upgrade()
{
	global $dbname;
	include("installSchema.php");
	foreach($tables as $table => $tableSchema)
	{
		print "<li>";
		print $table."&hellip;";
		$tableStatus = Query("show table status from ".$dbname." like '".$table."'");
		$numRows = NumRows($tableStatus);
		if($numRows == 0)
		{
			print " creating&hellip;";
			$create = "create table `".$table."` (\n";
			$comma = "";
			foreach($tableSchema['fields'] as $field => $type)
			{
				$create .= $comma."\t`".$field."` ".$type;
				$comma = ",\n";
			}
			if(isset($tableSchema['special']))
				$create .= ",\n\t".$tableSchema['special'];
			$create .= "\n) ENGINE=MyISAM;";
			//print "<pre>".$create."</pre>";
			Query($create);
		}
		else
		{
			//print " checking&hellip;";
			//$tableStatus = mysql_fetch_assoc($tableStatus);
			//print "<pre>"; print_r($tableStatus); print "</pre>";
			$primaryKey = "";
			$changes = 0;
			$foundFields = array();
			$scan = Query("show columns from `".$table."`");
			while($field = mysqli_fetch_assoc($scan))
			{
				$fieldName = $field['Field'];
				$foundFields[] = $fieldName;
				$type = $field['Type'];
				if($field['Null'] == "NO")
					$type .= " NOT NULL";
				//if($field['Default'] != "")
				if($field['Extra'] == "auto_increment")
					$type .= " AUTO_INCREMENT";
				else
					$type .= " DEFAULT '".$field['Default']."'";
				if($field['Key'] == "PRI")
					$primaryKey = $fieldName;
				if(array_key_exists($fieldName, $tableSchema['fields']))
				{
					$wantedType = $tableSchema['fields'][$fieldName];
					if(strcasecmp($wantedType, $type))
					{
						print " \"".$fieldName."\" not correct type&hellip;";
						if($fieldName == "id")
						{
							print_r($field);
							print "{ ".$type." }";
						}
						Query("ALTER TABLE `".$table."` CHANGE `".$fieldName."` `".$fieldName."` ".$wantedType);
						$changes++;
					}
				}
			}
			foreach($tableSchema['fields'] as $fieldName => $type)
			{
				if(!in_array($fieldName, $foundFields))
				{
					print " \"".$fieldName."\" missing&hellip;";
					Query("ALTER TABLE `".$table."` ADD `".$fieldName."` ".$type);
					$changes++;
				}
			}
			if($changes == 0)
				print " OK.";
		}
		print "</li>";
	}
}

?>
