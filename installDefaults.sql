INSERT INTO `categories` VALUES(1, 'Staff', 1, 0);
INSERT INTO `categories` VALUES(2, 'General', 0, 1);
INSERT INTO `categories` VALUES(3, 'Janitorial Services', 0, 2);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`, `minpower`, `minpowerthread`, `minpowerreply`) VALUES(1, 'Admin room', 'Staff discussion forum', 1, 1, 1, 1);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`) VALUES(2, 'General chat', 'Talk about serious stuff', 2);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`) VALUES(3, 'Off-Topic', 'Talk about other stuff', 2);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`, `minpowerthread`, `minpowerreply`) VALUES(4, 'Trash', '[trash]Where deleted threads go', 3, 3, 3);
INSERT INTO `ranks` (`rset`, `num`, `text`) VALUES
(1, 0, 'Non-poster'),
(1, 1, 'Newcomer'),
(1, 25, '<img src="img/ranks/gems/bronze.gif" alt="Bronze Coin" /> Bronze Coin'),
(1, 50, '<img src="img/ranks/gems/iron.gif" alt="Iron Coin" /> Iron Coin'),
(1, 75, '<img src="img/ranks/gems/silver.gif" alt="Silver Coin" /> Silver Coin'),
(1, 100, '<img src="img/ranks/gems/gold.gif" alt="Gold Coin" /> Gold Coin'),
(1, 200, '<img src="img/ranks/gems/bronzestar.gif" alt="Bronze Star" /> Bronze Star'),
(1, 300, '<img src="img/ranks/gems/ironstar.gif" alt="Iron Star" /> Iron Star'),
(1, 400, '<img src="img/ranks/gems/silverstar.gif" alt="Silver Star" /> Silver Star'),
(1, 500, '<img src="img/ranks/gems/goldstar.gif" alt="Gold Star" /> Gold Star'),
(1, 625, '<img src="img/ranks/gems/squareruby.gif" alt="Ruby Radiant" /> Ruby Radiant'),
(1, 750, '<img src="img/ranks/gems/squareorange.gif" alt="Calcite Radiant" /> Calcite Radiant'),
(1, 875, '<img src="img/ranks/gems/squaregreen.gif" alt="Emerald Radiant" /> Emerald Radiant'),
(1, 1000, '<img src="img/ranks/gems/squareblue.gif" alt="Sapphire Radiant" /> Sapphire Radiant'),
(1, 1250, '<img src="img/ranks/gems/squarepurple.gif" alt="Purple Sapphire Radiant" /> Purple Sapphire Radiant'),
(1, 1500, '<img src="img/ranks/gems/roundruby.gif" alt="Ruby Octagon" /> Ruby Octagon'),
(1, 1750, '<img src="img/ranks/gems/roundorange.gif" alt="Calcite Octagon" /> Calcite Octagon'),
(1, 2000, '<img src="img/ranks/gems/roundgreen.gif" alt="Emerald Octagon" /> Emerald Octagon'),
(1, 2250, '<img src="img/ranks/gems/roundblue.gif" alt="Sapphire Octagon" /> Sapphire Octagon'),
(1, 2500, '<img src="img/ranks/gems/roundpurple.gif" alt="Purple Sapphire Octagon" /> Purple Sapphire Octagon'),
(1, 3000, '<img src="img/ranks/gems/gemruby.gif" alt="Ruby Gem" /> Ruby Gem'),
(1, 3500, '<img src="img/ranks/gems/gemorange.gif" alt="Calcite Gem" /> Calcite Gem'),
(1, 4000, '<img src="img/ranks/gems/gemgreen.gif" alt="Emerald Gem" /> Emerald Gem'),
(1, 4500, '<img src="img/ranks/gems/gemblue.gif" alt="Sapphire Gem" /> Sapphire Gem'),
(1, 5000, '<img src="img/ranks/gems/gempurple.gif" alt="Purple Sapphire Gem" /> Purple Sapphire Gem');
INSERT INTO `ranksets` (`id`, `name`) VALUES (1, 'Coins and Gems');