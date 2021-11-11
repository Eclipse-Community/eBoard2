<?php
include("lib/settings.php");
include("lib/snippets.php");
include("lib/mysql.php");
$newToday = FetchResult("select count(*) from posts where date > ".(time() - 86400));
$newLastHour = FetchResult("select count(*) from posts where date > ".(time() - 3600));
$stats = Plural($newToday,"new post")." today,<br />".$newLastHour." last hour.";
?><html>
<head>
<title><?php print $boardname; ?></title>
<style type="text/css">
	body
	{
		background: black url("img/themes/default/back.png");
		color: white;
		text-align: center;
		font-family: "Verdana", "Lucida Grande", sans-serif;
		font-size: 40pt;
	}
</style>
<meta http-equiv="preview-refresh" content="3600" />
</head>
<body>
<img src="img/themes/default/logo.gif" style="width: 90%; margin-top: 5%;" /><br />
<br />
<?php print $stats; ?>
</body>
</html>