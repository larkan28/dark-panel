<?php

function execModule () 
{
	// Set global vars
	global $db, $view, $lang, $lang_err;

	// Show header & body
    $view->webHeader();
    $view->webBody();

	// Get all hosts
	$hosts = $db->host_GetAll();

	// Validate host data
	if (empty($hosts))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_hosts'] . '</div>';
		return;	
	}

	// Get user data
	$user_data = $view->getSessionData();
	
	// Set servers var
	$servers = (array) null;

	// Get all servers
	if ($user_data[$db::USER_ISADMIN] == 1)
		$servers = $db->server_GetAll();
	else
		$servers = $db->server_GetAll($user_data[$db::USER_ID]);

	// Validate servers data
	if (empty($servers))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_my_servers'] . '</div>';
		return;	
	}

	// Get games supported
	$games_json = getGamesJson();

	// Require custom table class
    require_once(PATH_INCLUDES . "table.php");

    // All servers table
	$table = new PanelTable(5);
	
	$table->tableStart("table-hover");
	$table->tableHead(array($lang['status'], $lang['game'], $lang['name'], $lang['expir'], $lang['actions']));

	for ($i = 0; $i < count($servers); $i++)
	{
		$server_ip = getValueInBy($hosts, $db::HOST_ID, $servers[$i][$db::SERVER_HOSTID], $db::HOST_IP);
		$server_game = $servers[$i][$db::SERVER_GAMEID];

		$table->tableRowStart();

		if (getGameData($server_ip, $servers[$i][$db::SERVER_PORT]) === FALSE)
			$table->tableRowValue("<span class='offline-status'><i class='fa fa-circle'></span></i> <span>" . $lang['offline'] . "</span>");
		else
			$table->tableRowValue("<span class='online-status'><i class='fa fa-circle'></span></i> <span>" . $lang['online'] . "</span>");

		$table->tableRowValue("<img class='game-icon' src=" . $games_json[$server_game]['game_icon'] . "></img>" . $games_json[$server_game]['game_title']);
		$table->tableRowValue($servers[$i][$db::SERVER_NAME]);
		$table->tableRowValue("-");
		$table->tableRowValue("-", FALSE, 20);
		$table->tableRowEnd();
	}

	$table->tableEnd();

	// Show footer
    $view->webFooter();
}

?>