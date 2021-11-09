<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>[[BOARD TITLE HERE]]</title>
	<meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta name="description" content="<?php print $metaDescription; ?>" />
	<meta name="keywords" content="<?php print $metaKeywords; ?>" />
	<meta name="viewport" content="width=device-width" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="css/common.css" />
	<link rel="stylesheet" type="text/css" href="css/<?php print $themeFile; ?>" id="theme_css" />
	<script type="text/javascript" src="lib/tricks.js"></script>
	<script type="text/javascript" src="lib/jquery.js"></script>
	<?php
		$bucket = "pageHeader"; include("./lib/pluginloader.php");
		$bucket = "footer"; include("./lib/pluginloader.php");
	?>
</head>
<body style="font-size: <?php print $loguser['fontsize']; ?>%;">
	<div class="outline margin width100" id="header">
		<table>
			<tr>
				<td colspan="3" class="cell0">
					<!-- Board header goes here -->
					<table>
						<tr>
							<?php if($misc['porabox']) { ?>
							<td style="border: 0px none;">
								<a href="./">
									<img src="<?php print htmlspecialchars($logopic); ?>" alt="<?php print htmlspecialchars($logoalt); ?>" title="<?php print htmlspecialchars($logotitle); ?>" id="theme_banner" style="padding: 8px;" />
								</a>
							</td>
							<td style="border: 0px none;">
								<div class="PoRT nom">
									<div class="errort">
										<strong><?php print $misc['poratitle']; ?></strong>
									</div>
									<div class="errorc cell2 left">
										<?php print CleanUpPost($misc['porabox'], "", true, true); ?>
									</div>
								</div>
							</td>
							<?php } else { ?>
							<td style="border: 0px none; text-align: center;">
								<a href="./">
									<img src="<?php print htmlspecialchars($logopic); ?>" alt="<?php print htmlspecialchars($logoalt); ?>" title="<?php print htmlspecialchars($logotitle); ?>" id="theme_banner" style="padding: 8px;" />
								</a>
							</td>
							<?php } ?>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="cell1">
				<td rowspan="2" class="cell1 smallFonts" style="text-align: center; width: 10%;">
					<?php print __("Views:"); ?> <span id="viewCount"><?php print number_format($misc['views']); ?></span>
				</td>
				<td class="smallFonts" style="text-align: center; width: 80%;">
					<ul class="pipemenu">
						<?php
							if($loguser['powerlevel'] > 2 && IsAllowed("viewAdminRoom"))
								print "<li><a href=\"admin.php\">".__("Admin")."</a></li>";
							print "<li><a href=\"index.php\">".__("Main")."</a></li>";
							print "<li><a href=\"faq.php\">".__("FAQ")."</a></li>";
							if(IsAllowed("viewUploader"))
								print "<li><a href=\"uploader.php\">".__("Uploader")."</a></li>";
							if(IsAllowed("viewMembers"))
								print "<li><a href=\"memberlist.php\">".__("Member list")."</a></li>";
							if(IsAllowed("viewRanks"))
								print "<li><a href=\"ranks.php\">".__("Ranks")."</a></li>";
							if(IsAllowed("viewOnline"))
								print "<li><a href=\"online.php\">".__("Online users")."</a></li>";
							if(IsAllowed("search"))
								print "<li><a href=\"search.php\">".__("Search")."</a></li>";
							print "<li><a href=\"lastposts.php\">".__("Last posts")."</a></li>";

							$bucket = "topMenu"; include("./lib/pluginloader.php");
						?>
					</ul>
				</td>
				<td rowspan="2" class="cell1 smallFonts" style="text-align: center; width: 10%;">
					<?php print cdate($dateformat)."\n"; ?>
				</td>
			</tr>
			<tr class="cell2">
				<td class="smallFonts" style="text-align: center">
					<?php
						if($loguserid)
						{
							print UserLink($loguser).": ";
							print "<ul class=\"pipemenu\">";
							print "<li><a href=\"#\" onclick=\"if(confirm('".__("Are you sure you want to log out?")."')) document.forms[0].submit();\">".__("Log out")."</a></li>";

							if(IsAllowed("editProfile"))
								print "<li><a href=\"editprofile.php\">".__("Edit profile")."</a></li>";
							if(IsAllowed("viewPM"))
								print "<li><a href=\"private.php\">".__("Private messages")."</a></li>";
							if(IsAllowed("editMoods"))
								print "<li><a href=\"editavatars.php\">".__("Mood avatars")."</a></li>";

							$bucket = "bottomMenu"; include("./lib/pluginloader.php");

							if(!isset($_POST['id']) && isset($_GET['id']))
								$_POST['id'] = (int)$_GET['id'];

							if(strpos($_SERVER['SCRIPT_NAME'], "forum.php"))
								print "<li><a href=\"index.php?fid=".$_POST['id']."&amp;action=markasread\">".__("Mark forum read")."</a></li>";
							elseif(strpos($_SERVER['SCRIPT_NAME'], "index.php"))
								print "<li><a href=\"index.php?action=markallread\">".__("Mark all forums read")."</a></li>";
							
						}
						else
						{
							print "<ul class=\"pipemenu\">";
							print "<li><a href=\"register.php\">".__("Register")."</a></li>";
							print "<li><a href=\"login.php\">".__("Log in")."</a></li>";
							}
						?>
					</ul>
				</td>
			</tr>
		</table>
	</div>
	<form action="login.php" method="post" id="logout">
		<div style="display: none;">
			<input type="hidden" name="action" value="logout" />
		</div>
	</form>
	<div id="body">
