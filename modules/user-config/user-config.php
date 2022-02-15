<?php

function execModule () 
{
    // Set global vars
    global $db, $view, $lang;

    // Get user data
    $user_data = $view->getSessionData();

    // Get old values
    $old_mail = $user_data[$db::USER_MAIL];
    $old_pass = $user_data[$db::USER_PASS];
    $old_fname = $user_data[$db::USER_FNAME];
    $old_lname = $user_data[$db::USER_LNAME];

    // Submit: Save Configuration
    if (isset($_POST['btn-saveConfig'])) 
    {
        // Get input values
        $user_mail = sanitizeInput($_POST['formConfig_userMail']);
        $user_pass = sanitizeInput($_POST['formConfig_userPass']);
        $user_fname = sanitizeInput($_POST['formConfig_userFName']);
        $user_lname = sanitizeInput($_POST['formConfig_userLName']);

        // Password has
        $hash_pass = getUserPass($user_pass);

        // Compare old values
        if ($user_mail === $old_mail && (empty($hash_pass) || $hash_pass === $old_pass) && $user_fname === $old_fname && $user_lname === $old_lname)
        {
            $view->refreshModule("error=same_values");
            return;
        }
        
        // Validate email
        if (!isValidMail($user_mail))
        {
            $view->refreshModule("error=invalid_email");
            return;
        }

        // Validate new password
        if (!empty($user_pass) && strcmp($user_pass, $_POST['formConfig_confirmPass']) != 0)
        {
            $view->refreshModule("error=invalid_confirm_pw");
            return;
        }

        // Validate first name
        if (!empty($user_fname) && !preg_match("/^[a-zA-Z]*$/", $user_fname))
        {
            $view->refreshModule("error=invalid_fname");
            return;
        }

        // Validate first name
        if (!empty($user_lname) && !preg_match("/^[a-zA-Z]*$/", $user_lname))
        {
            $view->refreshModule("error=invalid_lname");
            return;
        }

        // Update values
        $user_data[$db::USER_MAIL] = setUserValue($old_mail, $user_mail);
        $user_data[$db::USER_PASS] = setUserValue($old_pass, $hash_pass);
        $user_data[$db::USER_FNAME] = setUserValue($old_fname, $user_fname);
        $user_data[$db::USER_LNAME] = setUserValue($old_lname, $user_lname);

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

    // Show username/dates
    echo '<div class="alert alert-primary" role="alert">';
    echo $lang['username'] . ': <b>' . $user_data[$db::USER_NAME] . '</b>';
    echo '</div>';

    // Show dates
    echo '<div class="alert alert-secondary" role="alert">';
    echo $lang['last_login'] . ': <b>' . $user_data[$db::USER_LOGDATE] . '</b>';
    echo '</div>';

	// Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Config user form
    $form = new PanelForm("Config");

    $form->formStart("index.php?m=user-config");
    $form->formField("text", "userFName", $lang['input_fname'], $old_fname);
    $form->formField("text", "userLName", $lang['input_lname'], $old_lname);
    $form->formField("text", "userMail", $lang['input_usermail'], $old_mail);
    $form->formField("password", "userPass", $lang['input_newpass']);
    $form->formField("password", "confirmPass", $lang['input_conpass']);
    $form->formButton("btn-success", "saveConfig", $lang['btn_apply']);
    $form->formEnd();

    // Get user access
    $access_flag = getUserAccess($user_data);

    // Sub-users only for user
    if (strcmp($access_flag, "user") == 0)
    {
        // Sub-users title
        echo '<hr><br>';
        echo '<h2>Sub-Users</h2>';
        echo '<hr>';

        // Show dates
        echo '<div class="alert alert-warning" role="alert">';
        echo sprintf($lang['info_subuser'], $db->subuser_Count($user_data[$db::USER_ID]));
        echo '</div>';

        // Sub-users button
        echo '<a href="index.php?m=sub-users"><button type="button" class="btn btn-primary">' . $lang['btn_edit'] . '</button></a>';
    }

    // Show footer
    $view->webFooter();
}

?>