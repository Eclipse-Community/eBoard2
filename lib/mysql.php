<?php
//  AcmlmBoard XD support - MySQL database wrapper functions

include("database.php");

$queries = 0;

$dblink = mysqli_connect($dbserv, $dbuser, $dbpass, $dbname) or die("Could not connect to database.");
mysqli_set_charset($dblink, 'utf8mb4');
unset($dbpass);

if (!function_exists('Query'))
{

	function Query($query)
	{
		global $queries, $loguser;
		if ($loguser['powerlevel'] < 3) CheckQuery($query);
		//write("#{0} - {1}<br/>", $queries, $query);
		$res = mysqli_query($dblink, $query) or die(mysqli_error($dblink)."<br />Query was: <code>".$query."</code>");
		$queries++;
		return $res;
	}

	function Fetch($result)
	{
		$res = mysqli_fetch_array($result);
		return $res;
	}

	function mysqli_result($res, $row, $field=0) {
		$res->data_seek($row);
		$datarow = $res->fetch_array();
		return $datarow[$field];
	}
	function FetchResult($query, $row = 0, $field = 0)
	{
		$res = Query($query);
		if(mysqli_num_rows($res) == 0) return -1;
		return mysqli_result($res, $row, $field);
	}

	function NumRows($result)
	{
		return mysqli_num_rows($result);
	}
}

?>
