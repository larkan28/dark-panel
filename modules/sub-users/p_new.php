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

    // Submit: Create new user
    if (isset($_POST['btn-newSubuser'])) 
    {
        // Get input values
        $user_name = sanitizeInput($_POST['formNew_userName']);
        $user_pass = sanitizeInput($_POST['formNew_userPass']);
        $user_mail = sanitizeInput($_POST['formNew_userMail']);

        // Validate username
        if (empty($user_name))
        {
            $view->refreshModule("error=invalid_username");
            return;
        }

        // Validate email
        if (!isValidMail($user_mail))
        {
            $view->refreshModule("error=invalid_mail");
            return;
        }

        // Validate password
        if (empty($user_pass))
            $user_pass = generatePassword(7);

        // Hash password
        $hash_pass = password_hash($user_pass, PASSWORD_DEFAULT);

        // Subuser added
        if ($db->subuser_Add($user_name, $hash_pass, $user_mail, _getSessionID()) === TRUE)
        {
            $view->refreshModule("success=new_subuser&new_user=" . $user_name . "&new_pw=" . $user_pass, FALSE);
            return;
        }
        else 
        {
            $view->refreshModule("error=new_subuser", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // New sub-user form
    $form = new PanelForm("New");

    $form->formStart("index.php?m=sub-users&p=new");
    $form->formField("text", "userName", $lang['input_username']);
    $form->formField("password", "userPass", $lang['input_password'], "", "data-toggle='tooltip' title='" . $lang['tip_password'] . "'");
    $form->formField("text", "userMail", $lang['input_usermail']);
    $form->formButton("btn-success", "newSubuser", $lang['btn_create'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>