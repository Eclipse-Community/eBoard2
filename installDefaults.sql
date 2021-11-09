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
(1, 10, '<img src="img/ranks/mario/microgoomba.png" alt="Micro-Goomba" /> Micro-Goomba'),
(1, 20, '<img src="img/ranks/mario/goomba.png" alt="Goomba" /> Goomba'),
(1, 35, '<img src="img/ranks/mario/redgoomba.png" alt="Red Goomba" /> Red Goomba'),
(1, 50, '<img src="img/ranks/mario/redparagoomba.png" alt="Red Paragoomba" /> Red Paragoomba'),
(1, 65, '<img src="img/ranks/mario/paragoomba.png" alt="Paragoomba" /> Paragoomba'),
(1, 80, '<img src="img/ranks/mario/shyguy.png" alt="Shyguy" /> Shyguy'),
(1, 100, '<img src="img/ranks/mario/koopa.png" alt="Koopa" /> Koopa'),
(1, 120, '<img src="img/ranks/mario/redkoopa.png" alt="Red Koopa" /> Red Koopa'),
(1, 140, '<img src="img/ranks/mario/paratroopa.png" alt="Paratroopa" /> Paratroopa'),
(1, 160, '<img src="img/ranks/mario/redparatroopa.png" alt="Red Paratroopa" /> Red Paratroopa'),
(1, 180, '<img src="img/ranks/mario/cheepcheep.png" alt="Cheep-cheep" /> Cheep-cheep'),
(1, 200, '<img src="img/ranks/mario/redcheepcheep.png" alt="Red Cheep-cheep" /> Red Cheep-cheep'),
(1, 225, '<img src="img/ranks/mario/ninji.png" alt="Ninji" /> Ninji'),
(1, 250, '<img src="img/ranks/mario/flurry.png" alt="Flurry" /> Flurry'),
(1, 275, '<img src="img/ranks/mario/snifit.png" alt="Snifit" /> Snifit'),
(1, 300, '<img src="img/ranks/mario/porcupo.png" alt="Porcupo" /> Porcupo'),
(1, 325, '<img src="img/ranks/mario/panser.png" alt="Panser" /> Panser'),
(1, 350, '<img src="img/ranks/mario/mole.png" alt="Mole" /> Mole'),
(1, 375, '<img src="img/ranks/mario/buzzybeetle.png" alt="Buzzy Beetle" /> Buzzy Beetle'),
(1, 400, '<img src="img/ranks/mario/nipperplant.png" alt="Nipper Plant" /> Nipper Plant'),
(1, 425, '<img src="img/ranks/mario/bloober.png" alt="Bloober" /> Bloober'),
(1, 450, '<img src="img/ranks/mario/busterbeetle.png" alt="Buster Beetle" /> Buster Beetle'),
(1, 475, '<img src="img/ranks/mario/beezo.png" alt="Beezo" /> Beezo'),
(1, 500, '<img src="img/ranks/mario/bulletbill.png" alt="Bullet Bill" /> Bullet Bill'),
(1, 525, '<img src="img/ranks/mario/rex.png" alt="Rex" /> Rex'),
(1, 550, '<img src="img/ranks/mario/lakitu.png" alt="Lakitu" /> Lakitu'),
(1, 575, '<img src="img/ranks/mario/spiny.png" alt="Spiny" /> Spiny'),
(1, 600, '<img src="img/ranks/mario/bobomb.png" alt="Bob-Omb" /> Bob-Omb'),
(1, 625, '<img src="img/ranks/mario/drybones.png" alt="Dry Bones" /> Dry Bones'),
(1, 650, '<img src="img/ranks/mario/cobrat.png" alt="Cobrat" /> Cobrat'),
(1, 675, '<img src="img/ranks/mario/pokey.png" alt="Pokey" /> Pokey'),
(1, 700, '<img src="img/ranks/mario/spike.png" alt="Spike" /> Spike'),
(1, 725, '<img src="img/ranks/mario/melonbug.png" alt="Melon Bug" /> Melon Bug'),
(1, 750, '<img src="img/ranks/mario/lanternghost.png" alt="Lantern Ghost" /> Lantern Ghost'),
(1, 775, '<img src="img/ranks/mario/fuzzy.png" alt="Fuzzy" /> Fuzzy'),
(1, 800, '<img src="img/ranks/mario/bandit.png" alt="Bandit" /> Bandit'),
(1, 830, '<img src="img/ranks/mario/superkoopa.png" alt="Super Koopa" /> Super Koopa'),
(1, 860, '<img src="img/ranks/mario/redsuperkoopa.png" alt="Red Super Koopa" /> Red Super Koopa'),
(1, 900, '<img src="img/ranks/mario/boo.png" alt="Boo" /> Boo'),
(1, 925, '<img src="img/ranks/mario/boo.png" alt="Boo" /> Boo'),
(1, 950, '<img src="img/ranks/mario/fuzzball.png" alt="Fuzz Ball" /> Fuzz Ball'),
(1, 1000, '<img src="img/ranks/mario/boomerangbrother.png" alt="Boomerang Brother" /> Boomerang Brother'),
(1, 1050, '<img src="img/ranks/mario/hammerbrother.png" alt="Hammer Brother" /> Hammer Brother'),
(1, 1100, '<img src="img/ranks/mario/firebrother.png" alt="Fire Brother" /> Fire Brother'),
(1, 1150, '<img src="img/ranks/mario/firesnake.png" alt="Fire Snake" /> Fire Snake'),
(1, 1200, '<img src="img/ranks/mario/giantgoomba.png" alt="Giant Goomba" /> Giant Goomba'),
(1, 1250, '<img src="img/ranks/mario/giantkoopa.png" alt="Giant Koopa" /> Giant Koopa'),
(1, 1300, '<img src="img/ranks/mario/giantredkoopa.png" alt="Giant Red Koopa" /> Giant Red Koopa'),
(1, 1350, '<img src="img/ranks/mario/giantparatroopa.png" alt="Giant Paratroopa" /> Giant Paratroopa'),
(1, 1400, '<img src="img/ranks/mario/giantredparatroopa.png" alt="Giant Red Paratroopa" /> Giant Red Paratroopa'),
(1, 1450, '<img src="img/ranks/mario/chuck.png" alt="Chuck" /> Chuck'),
(1, 1500, '<img src="img/ranks/mario/thwomp.png" alt="Thwomp" /> Thwomp'),
(1, 1550, '<img src="img/ranks/mario/bossbass.png" alt="Boss Bass" /> Boss Bass'),
(1, 1600, '<img src="img/ranks/mario/volcanolotus.png" alt="Volcano Lotus" /> Volcano Lotus'),
(1, 1650, '<img src="img/ranks/mario/lavalotus.png" alt="Lava Lotus" /> Lava Lotus'),
(1, 1700, '<img src="img/ranks/mario/ptooie.png" alt="Ptooie" /> Ptooie'),
(1, 1800, '<img src="img/ranks/mario/sledgebrother.png" alt="Sledge Brother" /> Sledge Brother'),
(1, 1900, '<img src="img/ranks/mario/boomboom.png" alt="Boomboom" /> Boomboom'),
(1, 2000, '<img src="img/ranks/mario/birdo.png" alt="Birdo" /> Birdo'),
(1, 2100, '<img src="img/ranks/mario/redbirdo.png" alt="Red Birdo" /> Red Birdo'),
(1, 2200, '<img src="img/ranks/mario/greenbirdo.png" alt="Green Birdo" /> Green Birdo'),
(1, 2300, '<img src="img/ranks/mario/larrykoopa.png" alt="Larry Koopa" /> Larry Koopa'),
(1, 2400, '<img src="img/ranks/mario/mortonkoopa.png" alt="Morton Koopa" /> Morton Koopa'),
(1, 2500, '<img src="img/ranks/mario/wendykoopa.png" alt="Wendy Koopa" /> Wendy Koopa'),
(1, 2600, '<img src="img/ranks/mario/iggykoopa.png" alt="Iggy Koopa" /> Iggy Koopa'),
(1, 2700, '<img src="img/ranks/mario/roykoopa.png" alt="Roy Koopa" /> Roy Koopa'),
(1, 2800, '<img src="img/ranks/mario/lemmykoopa.png" alt="Lemmy Koopa" /> Lemmy Koopa'),
(1, 2900, '<img src="img/ranks/mario/ludwigvonkoopa.png" alt="Ludwig Von Koopa" /> Ludwig Von Koopa'),
(1, 3000, '<img src="img/ranks/mario/triclyde.png" alt="Triclyde" /> Triclyde'),
(1, 3100, '<img src="img/ranks/mario/magikoopa.png" alt="Magikoopa" /> Magikoopa'),
(1, 3200, '<img src="img/ranks/mario/wart.png" alt="Wart" /> Wart'),
(1, 3300, '<img src="img/ranks/mario/babybowser.png" alt="Baby Bowser" /> Baby Bowser'),
(1, 3400, '<img src="img/ranks/mario/kingbowserkoopa.png" alt="King Bowser Koopa" /> King Bowser Koopa'),
(1, 3500, '<img src="img/ranks/mario/yoshi.png" alt="Yoshi" /> Yoshi'),
(1, 3600, '<img src="img/ranks/mario/yellowyoshi.png" alt="Yellow Yoshi" /> Yellow Yoshi'),
(1, 3700, '<img src="img/ranks/mario/blueyoshi.png" alt="Blue Yoshi" /> Blue Yoshi'),
(1, 3800, '<img src="img/ranks/mario/redyoshi.png" alt="Red Yoshi" /> Red Yoshi'),
(1, 3900, '<img src="img/ranks/mario/kingyoshi.png" alt="King Yoshi" /> King Yoshi'),
(1, 4000, '<img src="img/ranks/mario/babymario.png" alt="Baby Mario" /> Baby Mario'),
(1, 4100, '<img src="img/ranks/mario/luigi.png" alt="Luigi" /> Luigi'),
(1, 4200, '<img src="img/ranks/mario/mario.png" alt="Mario" /> Mario'),
(1, 4300, '<img src="img/ranks/mario/superluigi.png" alt="Super Luigi" /> Super Luigi'),
(1, 4400, '<img src="img/ranks/mario/supermario.png" alt="Super Mario" /> Super Mario'),
(1, 4500, '<img src="img/ranks/mario/fireluigi.png" alt="Fire Luigi" /> Fire Luigi'),
(1, 4600, '<img src="img/ranks/mario/firemario.png" alt="Fire Mario" /> Fire Mario'),
(1, 4700, '<img src="img/ranks/mario/capeluigi.png" alt="Cape Luigi" /> Cape Luigi'),
(1, 4800, '<img src="img/ranks/mario/capemario.png" alt="Cape Mario" /> Cape Mario'),
(1, 4900, '<img src="img/ranks/mario/starluigi.png" alt="Star Luigi" /> Star Luigi'),
(1, 5000, '<img src="img/ranks/mario/starmario.png" alt="Star Mario" /> Star Mario');

INSERT INTO `ranksets` (`id`, `name`) VALUES (1, 'Mario');
