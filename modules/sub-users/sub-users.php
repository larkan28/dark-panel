<?php

function execModule () 
{
    // Set global vars
    global $db, $view, $lang, $lang_err;

    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Back button
    echo '<a href="index.php?m=user-config"><button type="button" class="btn btn-primary">' . $lang['btn_back'] . '</button></a> ';

    // Create new sub-user
    echo '<a href="index.php?m=sub-users&p=new"><button type="button" class="btn btn-success">' . $lang['btn_newsubuser'] . '</button></a>';
    echo '<br><br>';
    
    // Get all sub-user
    $sub_users = $db->subuser_GetAll(_getSessionID());

    // Validate sub-user data
    if (empty($sub_users))
    {
        echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_sub_users'] . '</div>';
		return;	
    }

    // Require custom table class
    require_once(PATH_INCLUDES . "table.php");

    // Show all sub-users
	$table = new PanelTable(4);
	
	$table->tableStart("table-hover");
	$table->tableHead(array($lang['id'], $lang['username'], $lang['expir'], $lang['actions']));

	for ($i = 0; $i < count($sub_users); $i++)
	{
		$subuser_id = $sub_users[$i][$db::USER_ID];

		$action_edit = "<a href='index.php?m=sub-users&p=edit&user_id=" . $subuser_id . "'><button type='button' class='btn btn-primary btn-sm'>" . $lang['btn_edit'] . "</button></a>";
		$action_delete = "<a href='index.php?m=sub-users&p=delete&user_id=" . $subuser_id . "'><button type='button' class='btn btn-danger btn-sm'>" . $lang['btn_delete'] . "</button></a>";
		
		$table->tableRowStart();
		$table->tableRowValue($subuser_id, TRUE);
		$table->tableRowValue($sub_users[$i][$db::USER_NAME]);
		$table->tableRowValue("-");
		$table->tableRowValue($action_edit . ' ' . $action_delete, FALSE, 20);
		$table->tableRowEnd();
	}

    $table->tableEnd();

    // Show footer
    $view->webFooter();
}

?>