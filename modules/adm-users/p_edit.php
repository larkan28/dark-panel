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

    // Get user data
    $user_data = $db->user_GetDataID($user_id);

    // Validate user data
    if (empty($user_data))
    {
        $view->refreshModule("error=invalid_user", FALSE);
        return;
    }

    // Get old values
    $old_name = $user_data[$db::USER_NAME];
    $old_mail = $user_data[$db::USER_MAIL];
    $old_pass = $user_data[$db::USER_PASS];
    $old_admin = $user_data[$db::USER_ISADMIN];

    // Submit: Edit user
    if (isset($_POST['btn-editUser'])) 
    {
        // Get input values
        $user_name = sanitizeInput($_POST['formEdit_userName']);
        $user_pass = sanitizeInput($_POST['formEdit_userPass']);
        $user_mail = sanitizeInput($_POST['formEdit_userMail']);
        $user_admin = $_POST['formEdit_userAdmin'];

        // Hash password
        $hash_pass = getUserPass($user_pass);

        // Compare old values
        if ($user_name === $old_name && (empty($hash_pass) || $hash_pass === $old_pass) && $user_mail === $old_mail && $user_admin == $old_admin)
        {
            $view->refreshModule("user_id=" . $user_id . "&error=same_values");
            return;
        }

        // Validate username
        if (empty($user_name))
        {
            $view->refreshModule("user_id=" . $user_id . "&error=invalid_username");
            return;
        }

        // Validate email
        if (!isValidMail($user_mail)) 
        {
            $view->refreshModule("user_id=" . $user_id . "&error=invalid_email");
            return;
        }

        // Update values
        $user_data[$db::USER_NAME] = setUserValue($old_name, $user_name);
        $user_data[$db::USER_PASS] = setUserValue($old_pass, $hash_pass);
        $user_data[$db::USER_MAIL] = setUserValue($old_mail, $user_mail);
        $user_data[$db::USER_ISADMIN] = setUserValue($old_admin, $user_admin);

        // User edited
        if ($db->user_Edit($user_data) === TRUE) 
        {
            $view->refreshModule("success=edit_user", FALSE);
            return;
        }
        else
        {
            $view->refreshModule("error=edit_user", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();
    
    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Edit user form
    $form = new PanelForm("Edit");

    $form->formStart("index.php?m=adm-users&p=edit&user_id=" . $user_id);
    $form->formField("text", "userName", $lang['input_username'], $old_name);
    $form->formField("password", "userPass", $lang['input_password']);
    $form->formField("text", "userMail", $lang['input_usermail'], $old_mail);

    if (isSubUser($user_data[$db::USER_PARENT]))
    {
        $form->formSelectStart("userAdmin", $lang['input_privileges'], "", "disabled");
        $form->formSelectOption($lang['sub_user'], "0", "selected");
    }
    else
    {
        $form->formSelectStart("userAdmin", $lang['input_privileges']);

        if ($old_admin == 0)
        {
            $form->formSelectOption($lang['user'], "0", "selected");
            $form->formSelectOption($lang['admin'], "1");
        }
        else
        {
            $form->formSelectOption($lang['user'], "0");
            $form->formSelectOption($lang['admin'], "1", "selected");
        }
    }
        
    $form->formSelectEnd();
    $form->formButton("btn-success", "editUser", $lang['btn_apply'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>