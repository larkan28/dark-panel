<?php

function execModule ()
{
    // Set global vars
    global $db, $view, $lang;
    
    // Submit: Cancel action
    if (isset($_POST['btn-cancelAction'])) 
    {
        $view->refreshModule("", FALSE);
        return;
    }

    // Get user id
    $user_id = $_GET['user_id'];

    // Validate user id
    if (!isValidID($user_id))
    {
        $view->refreshModule("error=invalid_user", FALSE);
        return;
    }

    // Get all user data
    $user_data = $db->user_GetDataID($user_id);

    // Validate user data
    if (empty($user_data))
    {
        $view->refreshModule("error=invalid_user", FALSE);
        return;
    }

    // Submit: Delete user
    if (isset($_POST['btn-deleteSubuser'])) 
    {
        // Delete privileges
        $db->subuser_DeleteAccess($user_id);

        // User deleted
        if ($db->user_Delete($user_id) === TRUE) {
            $view->refreshModule("success=delete_subuser", FALSE);
            return;
        }
        else {
            $view->refreshModule("error=delete_subuser", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();
    
    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Delete subuser form
    $form = new PanelForm("Delete");
    $form->formStart("index.php?m=sub-users&p=delete&user_id=" . $user_id);

    echo '<div class="alert alert-danger" role="alert">';
    echo sprintf($lang['confirm_del_subuser'], $user_data[$db::USER_ID], $user_data[$db::USER_NAME]);
    echo '</div>';

    $form->formButton("btn-success", "deleteSubuser", $lang['btn_yes'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_no']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>