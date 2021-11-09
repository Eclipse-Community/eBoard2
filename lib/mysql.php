<?php
//  AcmlmBoard XD support - MySQL database wrapper functions

include("database.php");

$queries = 0;

$dblink = mysql_connect($dbserv, $dbuser, $dbpass) or die("Could not connect to database.");
mysql_select_db($dbname);
unset($dbpass);

if (!function_exists('Query'))
{
	function CheckQuery($query)
	{
		$check = preg_replace("@'.*?[^\\\\]'@si", 'lolstring', $query);
		$check = preg_replace("@\".*?[^\\\\]\"@si", 'lolstring', $check);
		if (preg_match("@UPDATE\s+?users\s+?SET\s+?.*?`?(powerlevel|tempbanpl)`?\s*?=\s*?[\"']?\d+?[\"']?@si", $check))
			Report("Unauthorized user powerlevel change (".$query.")", 1, 2);
	}

	function Query($query)
	{
		global $queries, $loguser;
		if ($loguser['powerlevel'] < 3) CheckQuery($query);
		//write("#{0} - {1}<br/>", $queries, $query);
		$res = mysql_query($query) or die(mysql_error()."<br />Query was: <code>".$query."</code>");
		$queries++;
		return $res;
	}

	function Fetch($result)
	{
		$res = mysql_fetch_array($result);
		return $res;
	}

	function FetchResult($query, $row = 0, $field = 0)
	{
		$res = Query($query);
		if(mysql_num_rows($res) == 0) return -1;
		return mysql_result($res, $row, $field);
	}

	function NumRows($result)
	{
		return mysql_num_rows($result);
	}
}

?>
