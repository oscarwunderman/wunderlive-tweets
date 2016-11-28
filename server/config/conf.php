<?php

define("CACHE_PATH", dirname(__FILE__).'/cache/');

define('P2I_KEY', 'e15821df6b361da7');

### TWITTER SHIT
define('TW_CONSUMER_KEY', 'M1VTzCnEvTzkVdivCbcTUVlfX');
define('TW_CONSUMER_SECRET', 'MoGSkz6U9jBrYuGfD5S6UtmBN5Die7DkgJhR9DNn3BDjIkMfEU');
define('TW_ACCESS_TOKEN', '2922535587-16mfC1EUSjKLzSAGjA1FyEETAqEV8dMm5NCdO5M');
define('TW_ACCESS_TOKEN_SECRET', 'JunWMci1i2tD6pnNGqkh1VDdCSddkxvlxIn5e2OUyieFC');

### TWITTER SHIT marpesiaTrabajo
/*
define('TW_CONSUMER_KEY', 'OvEpuCKx6B9IQoVQ6j4dWPJs2');
define('TW_CONSUMER_SECRET', 'Bgt4Gjdgr3mhUr6zmdYeuT7ayOIyiX8lfsM5HQB9xVOVB2cshB');
define('TW_ACCESS_TOKEN', '3055911993-o0llyK8AiDZRDs14MLnGKOd1fkpY977UQ7WW147');
define('TW_ACCESS_TOKEN_SECRET', 'kGEla26pMRrRhPgR3KXvZ08gfuxeDjNNWGr75rFo8oAfT');
*/

define("BRANDWATCH_USERNAME", "eugenia.diaz@wunderman.com");
define("BRANDWATCH_PASSWORD", "Wunderman_2016");
define("BRANDWATCH_TOKEN", "3c1f52c6-caf6-49cc-b153-cb7e91f9e386");
define("BRANDWATCH_PROJECTID", 1998175569);
define("BRANDWATCH_QUERYIDNIVEAGENERAL",1998514835);
define("BRANDWATCH_QUERYIDNIVEATW",1998401770);
define("BRANDWATCH_QUERYIDNIVEAFB",1998401771);
define("BRANDWATCH_QUERYIDNIVEAMENGENERAL",1998514830);
define("BRANDWATCH_QUERYIDNIVEAMENTWITTER",1998401773);
define("BRANDWATCH_QUERYIDNIVEAMENFACEBOOK",1998401772);

header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: http://wundermanlive.herokuapp.com');