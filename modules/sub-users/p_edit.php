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

    // Get sub-user id
    $subuser_id = $_GET['user_id'];

    // Validate sub-user id
    if (!isValidID($subuser_id))
    {
        $view->refreshModule("error=invalid_user", FALSE);
        return;
    }

    // Get sub-user data
    $subuser_data = $db->user_GetDataID($subuser_id);

    // Validate sub-user data
    if (empty($subuser_data))
    {
        $view->refreshModule("error=invalid_user", FALSE);
        return;
    }

    // Get servers data
    $servers = $db->server_GetAll(_getSessionID());

    // Validate servers data
    if (empty($servers))
    {
        $view->refreshModule("error=no_my_servers", FALSE);
        return;
    }

    // Get hosts data
    $hosts = $db->host_GetAll();

    // Validate hosts data
    if (empty($hosts))
    {
        $view->refreshModule("error=no_hosts", FALSE);
        return;
    }

    // Check server id
    if (isset($_GET['server_id']))
    {
        // Get server id
        $server_id = $_GET['server_id'];

        // Validate server id
        if (!isValidID($server_id))
        {
            $view->refreshModule("error=invalid_server", FALSE);
            return;
        }

        // Get server array index
        $server_index = isValidIndexIn($servers, $server_id);

        // Validate server array index
        if ($server_index === FALSE)
        {
            $view->refreshModule("error=invalid_server", FALSE);
            return;
        }

        // Validate server host
        if (isValidIndexIn($hosts, $servers[$server_index][$db::SERVER_HOSTID]) === FALSE)
        {
            $view->refreshModule("error=invalid_host", FALSE);
            return;
        }
    }

    // Get old values
    $old_name = $subuser_data[$db::USER_NAME];
    $old_pass = $subuser_data[$db::USER_PASS];
    $old_mail = $subuser_data[$db::USER_MAIL];

    // Submit: Edit subuser
    if (isset($_POST['btn-editSubuser'])) 
    {
        // Get input values
        $user_name = sanitizeInput($_POST['formEdit_userName']);
        $user_pass = sanitizeInput($_POST['formEdit_userPass']);
        $user_mail = sanitizeInput($_POST['formEdit_userMail']);

        // Hash password
        $hash_pass = getUserPass($user_pass);

        // Compare access flag
        $same_access = FALSE;

        // Validate server id and access
        if (isset($server_id))
        {
            // Validate server access
            if (isset($_POST['formEdit_accessServer']))
            {
                // Set user access
                $user_access = "";

                // Create new privileges
                if (isset($_POST['formEdit_allowStart']))
                    $user_access = appendAccess($user_access, "start");

                if (isset($_POST['formEdit_allowStop']))
                    $user_access = appendAccess($user_access, "stop");

                // Check new privileges
                if (empty($user_access))
                {
                    $view->refreshModule("user_id=" . $subuser_id . "&error=no_access_set");
                    return;
                }
            }

            // Get current access
            $old_access = $db->subuser_AllAccess($subuser_id);

            // Validate old access
            if (!empty($old_access))
            {
                // Get correct access index
                $index_access = isValidIndexIn($old_access, $server_id, $db::SUBUSER_SERVERID);

                // Validate access index
                if ($index_access === FALSE)
                {
                    // Delete previous access for avoid loop error
                    $db->subuser_DeleteAccess($subuser_id, $server_id);

                    // Redirect to error
                    $view->refreshModule("user_id=" . $subuser_id . "&error=invalid_server");
                    return;
                }

                // Compare old access
                if (isset($user_access) && strcmp($old_access[$index_access][$db::SUBUSER_PRIVILEGES], $user_access) == 0)
                    $same_access = TRUE;
            }
        }
        else
            $same_access = TRUE;

        // Validate username
        if (empty($user_name))
        {
            $view->refreshModule("user_id=" . $subuser_id . "&error=invalid_username");
            return;
        }

        // Validate email
        if (!isValidMail($user_mail))
        {
            $view->refreshModule("user_id=" . $subuser_id . "&error=invalid_mail");
            return;
        }

        // Compare old values
        if ($user_name === $old_name && (empty($hash_pass) || $hash_pass === $old_pass) && $user_mail === $old_mail && $same_access == TRUE)
        {
            $view->refreshModule("user_id=" . $subuser_id . "&error=same_values");
            return;
        }

        // Update values
        $subuser_data[$db::USER_NAME] = setUserValue($old_name, $user_name);
        $subuser_data[$db::USER_PASS] = setUserValue($old_pass, $hash_pass);
        $subuser_data[$db::USER_MAIL] = setUserValue($old_mail, $user_mail);

        // Query success flag
        $query_flag = $db->user_Edit($subuser_data);

        // If new access is set, edit/add it
        if (isset($user_access))
            $query_flag = $db->subuser_SetAccess($subuser_id, $server_id, $user_access);
        else
        {
            // If old access is set and sub-user have some access, delete it
            if (isset($old_access) && !empty($old_access))
                $query_flag = $db->subuser_DeleteAccess($subuser_id, $server_id);
        }

        // Subuser edited
        if ($query_flag === TRUE) 
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

    // Edit sub-user form
    $form = new PanelForm("Edit");

    // Check server id
    if (isset($server_id))
    {
        $form->formStart("index.php?m=sub-users&p=edit&user_id=" . $subuser_id . "&server_id=" . $server_id);

        // User fields
        $form->formField("text", "userName", $lang['input_username'], $old_name);
        $form->formField("password", "userPass", $lang['input_password']);
        $form->formField("text", "userMail", $lang['input_usermail'], $old_mail);

        // Server privileges
        $form->formSelectStart("userPrivileges", $lang['input_privileges'], "sel-ServerID");
        $form->formSelectOption("-", $subuser_id . "," . "-");

        // Show all servers owned
        for ($i = 0; $i < count($servers); $i++)
        {
            $server_ip = getValueInBy($hosts, $db::HOST_ID, $servers[$i][$db::SERVER_HOSTID], $db::HOST_IP);
            $sarray_id = $servers[$i][$db::SERVER_ID];

            if ($sarray_id == $server_id)
                $form->formSelectOption($server_ip . ":" . $servers[$i][$db::SERVER_PORT], $subuser_id . "," . $sarray_id, "selected");
            else
                $form->formSelectOption($server_ip . ":" . $servers[$i][$db::SERVER_PORT], $subuser_id . "," . $sarray_id);
        }
        
        $form->formSelectEnd();

        // Get current privileges
        $user_access = $db->subuser_AllAccess($subuser_id);

        // Check privileges
        if (empty($user_access))
        {
            $form->formCheck("accessServer", $lang['access_server'], "", "check-AccessServer");
            $form->formCheck("allowStart", $lang['allow_start'], "disabled", "check-AccessAllow");
            $form->formCheck("allowStop", $lang['allow_stop'], "disabled", "check-AccessAllow");
        }
        else
        {
            // Loop in all sub-user access
            for ($i = 0; $i < count($user_access); $i++)
            {
                // Find correct server access
                if ($server_id == $user_access[$i][$db::SUBUSER_SERVERID])
                {
                    $form->formCheck("accessServer", $lang['access_server'], "checked", "check-AccessServer");

                    // Get sub-user privileges
                    $privileges = explode(",", $user_access[$i][$db::SUBUSER_PRIVILEGES]);

                    // Start access
                    if (hasActionAccess($privileges, "start"))
                        $form->formCheck("allowStart", $lang['allow_start'], "checked", "check-AccessAllow");
                    else
                        $form->formCheck("allowStart", $lang['allow_start'], "", "check-AccessAllow");

                    // Stop access
                    if (hasActionAccess($privileges, "stop"))
                        $form->formCheck("allowStop", $lang['allow_stop'], "checked", "check-AccessAllow");
                    else
                        $form->formCheck("allowStop", $lang['allow_stop'], "", "check-AccessAllow");

                    break;
                }
            }
        }

        echo '<br>';
    }
    else
    {
        $form->formStart("index.php?m=sub-users&p=edit&user_id=" . $subuser_id);

        // User fields
        $form->formField("text", "userName", $lang['input_username'], $old_name);
        $form->formField("password", "userPass", $lang['input_password']);
        $form->formField("text", "userMail", $lang['input_usermail'], $old_mail);

        // Server privileges
        $form->formSelectStart("userPrivileges", $lang['input_privileges'], "sel-ServerID");
        $form->formSelectOption("-", $subuser_id . "," . "-", "selected");

        for ($i = 0; $i < count($servers); $i++)
        {
            $server_id = $servers[$i][$db::SERVER_ID];
            $server_ip = getValueInBy($hosts, $db::HOST_ID, $servers[$i][$db::SERVER_HOSTID], $db::HOST_IP);

            $form->formSelectOption($server_ip . ":" . $servers[$i][$db::SERVER_PORT], $subuser_id . "," . $server_id);
        }

        $form->formSelectEnd();
    }

    $form->formButton("btn-success", "editSubuser", $lang['btn_apply'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>