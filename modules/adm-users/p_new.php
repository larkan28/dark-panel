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
    if (isset($_POST['btn-newUser'])) 
    {
        // Get input values
        $user_name = sanitizeInput($_POST['formNew_userName']);
        $user_pass = sanitizeInput($_POST['formNew_userPass']);
        $user_mail = sanitizeInput($_POST['formNew_userMail']);
        $user_admin = $_POST['formNew_userAdmin'];

        // Validate username
        if (empty($user_name))
        {
            $view->refreshModule("error=invalid_username");
            return;
        }

        // Validate email
        if (!isValidMail($user_mail))
        {
            $view->refreshModule("error=invalid_email");
            return;
        }

        // Validate password
        if (empty($user_pass))
            $user_pass = generatePassword(7);

        // Hash password
        $hash_pass = password_hash($user_pass, PASSWORD_DEFAULT);

        // User added
        if ($db->user_Add($user_name, $hash_pass, $user_admin, $user_mail) === TRUE) 
        {
            $view->refreshModule("success=new_user&new_user=" . $user_name . "&new_pw=" . $user_pass, FALSE);
            return;
        }
        else
        {
            $view->refreshModule("error=new_user", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // New user form
    $form = new PanelForm("New");

    $form->formStart("index.php?m=adm-users&p=new");
    $form->formField("text", "userName", $lang['input_username']);
    $form->formField("password", "userPass", $lang['input_password'], "", "data-toggle='tooltip' title='" . $lang['tip_password'] . "'");
    $form->formField("text", "userMail", $lang['input_usermail']);
    $form->formSelectStart("userAdmin", $lang['input_privileges']);
    $form->formSelectOption($lang['user'], "0");
    $form->formSelectOption($lang['admin'], "1");
    $form->formSelectEnd();
    $form->formButton("btn-success", "newUser", $lang['btn_create'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>