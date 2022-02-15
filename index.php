<?php

// Session start
session_start();

// Path defines
define("PATH_PROTOCOL", "protocol/");
define("PATH_INCLUDES", "includes/");
define("PATH_MODULES", "modules/");
define("PATH_LANG", "lang/");

// Require main classes
require_once(PATH_INCLUDES . "helpers.php");
require_once(PATH_INCLUDES . "view.php");

// Create MySQL connection
$db = createDatabaseConnection();

// Check MySQL connection
if (!$db instanceof MySQL_DataBase)
    die("Can't connect to MySQL Database");

// Check language id
if (isset($_GET['lang']))
    _langSession($_GET['lang']);

// Require language file
require_once(_getLangFile());

// Create View
$view = new PanelView();
$view->printView();

?>