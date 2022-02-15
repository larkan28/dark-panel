 <?php

function execModule () 
{
	// Set global vars
	global $db, $view, $lang, $lang_err;

	// Show header & body
    $view->webHeader();
    $view->webBody();

	// New user button
	echo '<a href="index.php?m=adm-users&p=new"><button type="button" class="btn btn-primary">' . $lang['btn_newuser'] . '</button></a>';
	echo '<br><br>';

	// Get all users from db
	$users = $db->user_GetAll();

	// Validate users data
	if (empty($users))
	{
		echo '<div class="alert alert-warning" role="alert">' . $lang_err['no_users'] . '</div>';
		return;	
	}

	// Require custom table class
    require_once(PATH_INCLUDES . "table.php");

    // All users table
	$table = new PanelTable(5);
	
	$table->tableStart("table-hover");
	$table->tableHead(array($lang['id'], $lang['username'], $lang['privileges'], $lang['expir'], $lang['actions']));

	for ($i = 0; $i < count($users); $i++)
	{
		$user_id = $users[$i][$db::USER_ID];

		$action_edit = "<a href='index.php?m=adm-users&p=edit&user_id=" . $user_id . "'><button type='button' class='btn btn-primary btn-sm'>" . $lang['btn_edit'] . "</button></a>";
		$action_delete = "<a href='index.php?m=adm-users&p=delete&user_id=" . $user_id . "'><button type='button' class='btn btn-danger btn-sm'>" . $lang['btn_delete'] . "</button></a>";
		
		$table->tableRowStart();
		$table->tableRowValue($user_id, TRUE);
		$table->tableRowValue($users[$i][$db::USER_NAME]);
		$table->tableRowValue($lang[getUserAccess($users[$i])]);
		$table->tableRowValue("-");
		$table->tableRowValue($action_edit . ' ' . $action_delete, FALSE, 20);
		$table->tableRowEnd();
	}

	$table->tableEnd();

	// Show footer
    $view->webFooter();
}

?>