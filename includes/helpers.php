<?php

// FUNCS: Session
function _openSession ($user_id)
{
    $_SESSION['userID'] = $user_id;
}

function _langSession ($lang_id)
{
    if (empty($lang_id))
        return;

    $lang_file = PATH_LANG . "lang_" . $lang_id . ".php";

    if (file_exists($lang_file))
        $_SESSION['langID'] = $lang_id;
}

function _destroySession ()
{
    session_unset();
    session_destroy();
}

function _getSessionID ()
{
    if (isset($_SESSION['userID']))
        return $_SESSION['userID'];

    return 0;
}

function _getSessionStatus ()
{
    return isset($_SESSION['userID']) ? TRUE : FALSE;
}

function _getLangFile ()
{
    if (isset($_SESSION['langID']))
        return PATH_LANG . "lang_" . $_SESSION['langID'] . ".php";

    return PATH_LANG . "lang_en.php";
}

function _getLangID ()
{
    if (isset($_SESSION['langID']))
        return $_SESSION['langID'];

    return "en";
}

// FUNCS: MySQL
function createDatabaseConnection () 
{
    if (function_exists('mysqli_connect'))
        require_once("mysql_funcs.php");
    else
        die("MySQL not installed");

    $database = new MySQL_DataBase();
    $connection = $database->connectMySQL();
        
    if ($connection === TRUE)
        return $database;

    return $connection;
}

// FUNCS: External Files
function getGamesJson ()
{
    $games_file = file_get_contents("games/supported.json");

    if ($games_file === FALSE)
        return (array) null;

    $games_json = json_decode($games_file, TRUE);

    if ($games_json === null)
        return (array) null;

    return $games_json;
}

function getModulesXML ()
{
    return simplexml_load_file(PATH_MODULES . "modules.xml");
}

function getLangsXML ()
{
    return simplexml_load_file(PATH_LANG . "languages.xml");
}

// FUNCS: Modules
function getDefaultModule ()
{
    $web_modules = getModulesXML();

    foreach ($web_modules->section as $section) {                 
        foreach ($section->option as $nav_option) {
            if (isset($nav_option['default']))
                return $nav_option['file'];
        }
    }

    return "";
}

function getUserAccess ($user_data)
{
    if (empty($user_data) || _getSessionStatus() == FALSE)
        return "notlogged";

    if ($user_data[7] == 1)
        return "admin";

    return $user_data[1] != 0 ? "sub_user" : "user";
}

function hasModuleAccess ($module_access, $user_access)
{
    $access = explode(",", $module_access);

    for ($i = 0; $i < count($access); $i++)
    {
        if (strcmp($access[$i], $user_access) == 0)
            return TRUE;
    }

    return FALSE;
}

function hasActionAccess ($access_list, $action)
{
    for ($i = 0; $i < count($access_list); $i++)
    {
        if (strcmp($access_list[$i], $action) == 0)
            return TRUE;
    }

    return FALSE;
}

function isSubUser ($parent_id)
{
    return ($parent_id != 0);
}

// FUNCS: General
function sanitizeInput ($str) 
{
	$str = str_replace('\"', '', $str);
	$str = str_replace("\'", "", $str);
		
	$str = str_replace('"', '', $str);
	$str = str_replace("'", "", $str);
	
	$str = trim($str);
	$str = strip_tags($str);
	
	return $str;
}

function appendAccess ($input, $new_access)
{
    if (empty($input))
        return $new_access;

    return $input . "," . $new_access;
}

function getGameData ($server_ip, $server_port)
{
    require PATH_PROTOCOL . "SourceQuery/bootstrap.php";
    
    define('SQ_SERVER_ADDR', $server_ip);
    define('SQ_SERVER_PORT', $server_port);
    define('SQ_TIMEOUT',     1);
    define('SQ_ENGINE',      SourceQuery::GOLDSOURCE);
        
    $Query = new SourceQuery();

    try
    {
        $Query->Connect(SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE);
        return $Query;
    }
    catch (Exception $e)
    {
        return FALSE;
    }
    finally
    {
        $Query->Disconnect();
    }

    return FALSE;
}

function getGameTitle ($game_type)
{
    $games_json = getGamesJson();

    if (empty($games_json))
        return "N/A";

    return $games_json[$game_type]['game_title'];
}

function getFreePort ($servers, $server_game)
{
    $games_json = getGamesJson();

    if (empty($games_json))
        return "";

    $start_port = $games_json[$server_game]['game_start_port'];

    for ($i = $start_port; $i <= 65535; $i++)
    {
        $is_free = TRUE;

        for ($j = 0; $j < count($servers); $j++)
        {
            if ($i == $servers[$j][2])
            {
                $is_free = FALSE;
                break;
            }
        }

        if ($is_free == TRUE)
            return $i;
    }

    return "";
}

function getValueIn ($arr_a, $arr_b, $id_a, $id_b, $return_id)
{
    for ($i = 0; $i < count($arr_a); $i++)
    {
        for ($j = 0; $j < count($arr_b); $j++)
        {
            if ($arr_a[$i][$id_a] == $arr_b[$j][$id_b])
                return $arr_b[$j][$return_id];
        }
    }

    return FALSE;
}

function getValueInBy ($arr, $id_arr, $value, $return_id)
{
    for ($i = 0; $i < count($arr); $i++)
    {
        if ($value == $arr[$i][$id_arr])
            return $arr[$i][$return_id];
    }

    return "N/A";
}

function getUserPass ($input, $generate_new = FALSE)
{
    if (empty($input))
    {
        if ($generate_new == TRUE)
            return generatePassword(7);

        return $input;
    }

    return password_hash($input, PASSWORD_DEFAULT);
}

function setUserValue ($old_value, $new_value)
{
    if (empty($new_value))
        return $old_value;
    
    return $new_value;
}

function isValidID ($id)
{
    if (empty($id) || !is_numeric($id) || $id < 1)
        return FALSE;

    return TRUE;
}

function isValidIP ($ip)
{
    if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP))
        return FALSE;

    return TRUE;
}

function isValidPort ($port)
{
    if (empty($port) || !is_numeric($port) || strlen($port) > 5 || $port < 0 || $port > 65535)
        return FALSE;

    return TRUE;
}

function isValidMail ($mail)
{
    if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL))
        return FALSE;

    return TRUE;
}

function isValidIndexIn ($array, $id, $index_id = 0)
{
    for ($i = 0; $i < count($array); $i++)
    {
        if ($array[$i][$index_id] == $id)
            return $i;
    }

    return FALSE;
}

function generatePassword ($password_length) 
{
    $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
    return substr(str_shuffle($data), 0, $password_length);
}

?>