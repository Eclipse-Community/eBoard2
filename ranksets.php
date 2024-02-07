<?php
//  AcmlmBoard XD - Source file for addranks.php
//  Access: N/A

//Rank images should match the rank text, but without punctuation or spaces.
//Possible settings for a rankset include:
// "name" -- required, name displayed in choices
// "directory" -- name of rankset image directory, defaults to name
// "notolower" -- bool, disables str_tolower() calls, e.g. canonical Zelda set
// "noimages" -- bool, disables automajickal conversion to <img> tags
// "splitlines" -- bool, enables <br /> insertion
//Note that any rank below 10 posts is assumed text only as if noimages was set.

$ranks = array
(
	array
	(
		"name" => "Coins and Gems",
		"ranks" => array
		(
			0 => "Non-poster",
			1 => "Newcomer",
			25 => "Bronze Coin",
			50 => "Iron Coin",
			75 => "Silver Coin",
			100 => "Gold Coin",
			200 => "Bronze Star",
			300 => "Iron Star",
			400 => "Silver Star",
			500 => "Gold Star",
			625 => "Ruby Radiant",
			750 => "Calcite Radiant",
			875 => "Emerald Radiant",
			1000 => "Sapphire Radiant",
			1250 => "Purple Sapphire Radiant",
			1500 => "Ruby Octagon",
			1750 => "Calcite Octagon",
			2000 => "Emerald Octagon",
			2250 => "Sapphire Octagon",
			2500 => "Purple Sapphire Octagon",
			3000 => "Ruby Gem",
			3500 => "Calcite Gem",
			4000 => "Emerald Gem",
			4500 => "Sapphire Gem",
			5000 => "Purple Sapphire Gem",
		)
	),
);
?>
