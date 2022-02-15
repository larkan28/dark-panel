<?php

// Module: Execute
function execModule () 
{
	// Set global vars
	global $db, $view, $lang, $lang_err;

	// Show header & body
    $view->webHeader();
    $view->webBody();

	// New host button
	echo '<a href="index.php?m=adm-hosts&p=new"><button type="button" class="btn btn-primary">' . $lang['btn_newhost'] . '</button></a>';
	echo '<br><br>';

	// Get all hosts from db
	$hosts = $db->host_GetAll();

	// Check if hosts is empty
	if (empty($hosts))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_hosts'] . '</div>';
		return;	
	}

	// Require custom table class
    require_once(PATH_INCLUDES . "table.php");

    // Show all hosts
	$table = new PanelTable(4);
	
	$table->tableStart("table-hover");
	$table->tableHead(array($lang['id'], $lang['name'], $lang['ip'], $lang['actions']));

	for ($i = 0; $i < count($hosts); $i++)
	{
		$action_edit = "<a href='index.php?m=adm-hosts&p=edit&host_id=" . $hosts[$i][$db::HOST_ID] . "'><button type='button' class='btn btn-primary btn-sm'>" . $lang['btn_edit'] . "</button></a>";
		$action_delete = "<a href='index.php?m=adm-hosts&p=delete&host_id=" . $hosts[$i][$db::HOST_ID] . "'><button type='button' class='btn btn-danger btn-sm'>" . $lang['btn_delete'] . "</button></a>";
		
		$table->tableRowStart();
		$table->tableRowValue($hosts[$i][$db::HOST_ID], TRUE);
		$table->tableRowValue($hosts[$i][$db::HOST_NAME]);
		$table->tableRowValue($hosts[$i][$db::HOST_IP]);
		$table->tableRowValue($action_edit . ' ' . $action_delete, FALSE, 20);
		$table->tableRowEnd();
	}

	$table->tableEnd();

	// Show footer
    $view->webFooter();
}

?>