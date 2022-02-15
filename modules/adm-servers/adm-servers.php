<?php

// Module: Execute
function execModule () 
{
	// Set global vars
	global $db, $view, $lang, $lang_err;

	// Get game supported file
    $games_json = getGamesJson();

    // Check file
    if (empty($games_json))
    {
        $view->refreshModule("error=no_games", FALSE);
        return;
    }

	// Show header & body
    $view->webHeader();
    $view->webBody();

	// New host button
	echo '<a href="index.php?m=adm-servers&p=new"><button type="button" class="btn btn-primary">' . $lang['btn_newserver'] . '</button></a>';
	echo '<br><br>';

	// Get all servers
	$servers = $db->server_GetAll();

	// Validate servers data
	if (empty($servers))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_servers'] . '</div>';
		return;
	}

	// Get all hosts
	$hosts = $db->host_GetAll();

	// Validate host data
	if (empty($hosts))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_hosts'] . '</div>';
		return;	
	}

	// Get all users
	$users = $db->user_GetAll();

	// Validate host data
	if (empty($users))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_users'] . '</div>';
		return;	
	}

	// Require custom table class
    require_once(PATH_INCLUDES . "table.php");

    // Show all servers
	$table = new PanelTable(7);
	
	$table->tableStart("table-hover");
	$table->tableHead(array($lang['id'], $lang['game'], $lang['name'], $lang['ip_port'], $lang['owner'], $lang['expir'], $lang['actions']));

	for ($i = 0; $i < count($servers); $i++)
	{
		$server_id = $servers[$i][$db::SERVER_ID];
		$server_ip = getValueInBy($hosts, $db::HOST_ID, $servers[$i][$db::SERVER_HOSTID], $db::HOST_IP);
		$server_owner = getValueInBy($users, $db::USER_ID, $servers[$i][$db::SERVER_OWNERID], $db::USER_NAME);

		$action_edit = "<a href='index.php?m=adm-servers&p=edit&server_id=" . $server_id . "'><button type='button' class='btn btn-primary btn-sm'>" . $lang['btn_edit'] . "</button></a>";
		$action_delete = "<a href='index.php?m=adm-servers&p=delete&server_id=" . $server_id . "'><button type='button' class='btn btn-danger btn-sm'>" . $lang['btn_delete'] . "</button></a>";

		$table->tableRowStart();
		$table->tableRowValue($server_id, TRUE);
		$table->tableRowValue(getGameTitle($servers[$i][$db::SERVER_GAMEID]));
		$table->tableRowValue($servers[$i][$db::SERVER_NAME]);
		$table->tableRowValue($server_ip . ":" . $servers[$i][$db::SERVER_PORT]);
		$table->tableRowValue($server_owner);
		$table->tableRowValue("-");
		$table->tableRowValue($action_edit . ' ' . $action_delete, FALSE, 20);
		$table->tableRowEnd();
	}

	$table->tableEnd();

	// Show footer
    $view->webFooter();
}

?>