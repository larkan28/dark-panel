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

    // Get game supported file
    $games_json = getGamesJson();

    // Validate games file
    if (empty($games_json))
    {
        $view->refreshModule("error=no_games", FALSE);
        return;
    }

    // Get all hosts
    $hosts = $db->host_GetAll();

    // Validate hosts
    if (empty($hosts))
    {
        $view->refreshModule("error=no_hosts", FALSE);
        return;
    }

    // Get server id
    $server_id = $_GET['server_id'];

    // Validate server id
    if (!isValidID($server_id))
    {
        $view->refreshModule("error=invalid_server", FALSE);
        return;
    }

    // Get server data
    $server_data = $db->server_GetData($server_id);

    // Validate server data
    if (empty($server_data))
    {
        $view->refreshModule("error=invalid_server", FALSE);
        return;
    }

    // Get owner data
    $owner_data = $db->user_GetDataID($server_data[$db::SERVER_OWNERID]);

    // Get old values
    $old_name = $server_data[$db::SERVER_NAME];
    $old_port = $server_data[$db::SERVER_PORT];
    $old_game = $server_data[$db::SERVER_GAMEID];
    $old_host = $server_data[$db::SERVER_HOSTID];
    $old_owner = $server_data[$db::SERVER_OWNERID];
    $old_slots = $server_data[$db::SERVER_MAXSLOTS];

    // Submit: Edit server
    if (isset($_POST['btn-editServer'])) 
    {
        // Get input values
        $server_name = sanitizeInput($_POST['formEdit_serverName']);
        $server_port = sanitizeInput($_POST['formEdit_serverPort']);
        $server_host = $_POST['formEdit_serverHost'];
        $server_owner = sanitizeInput($_POST['formEdit_serverOwner']);
        $server_slots = $_POST['formEdit_serverSlots'];

        // Compare old values
        if ($server_name === $old_name && $server_port == $old_port && $server_host == $old_host && $server_slots == $old_slots && (!empty($owner_data) && $server_owner === $owner_data[$db::USER_NAME]))
        {
            $view->refreshModule("server_id=" . $server_id . "&error=same_values");
            return;
        }

        // Check server name
        if (empty($server_name)) 
        {
            $view->refreshModule("server_id=" . $server_id . "&error=invalid_name");
            return;
        }

        // Check server owner
        if (empty($server_owner)) 
        {
            $view->refreshModule("server_id=" . $server_id . "&error=invalid_owner");
            return;
        }

        // Port has been changed
        if ($old_port != $server_port)
        {
            // Validate port number
            if (!isValidPort($server_port)) 
            {
                $view->refreshModule("server_id=" . $server_id . "&error=invalid_port");
                return;
            }

            // Check if port is already used
            if ($db->server_Exists($server_host, $server_port) === TRUE)
            {
                $view->refreshModule("server_id=" . $server_id . "&error=port_used");
                return;
            }
        }

        // Owner has been changed
        if (empty($owner_data) || strcmp($owner_data[$db::USER_NAME], $server_owner) != 0)
        {
            // Get new owner data
            $owner_data = $db->user_GetData($server_owner);

            // Validate new server owner
            if (empty($owner_data))
            {
                $view->refreshModule("server_id=" . $server_id . "&error=no_owner");
                return;
            }

            // Verifiy owner if not sub-user
            if (isSubUser($owner_data[$db::USER_PARENT]))
            {
                $view->refreshModule("server_id=" . $server_id . "error=no_sub_owner");
                return;
            }
        }

        // Update values
        $server_data[$db::SERVER_NAME] = setUserValue($old_name, $server_name);
        $server_data[$db::SERVER_PORT] = setUserValue($old_port, $server_port);
        $server_data[$db::SERVER_HOSTID] = setUserValue($old_port, $server_host);
        $server_data[$db::SERVER_OWNERID] = setUserValue($old_owner, $owner_data[$db::USER_ID]);
        $server_data[$db::SERVER_MAXSLOTS] = setUserValue($old_slots, $server_slots);

        // Server edited
        if ($db->server_Edit($server_data) === TRUE) 
        {
            $view->refreshModule("success=edit_server", FALSE);
            return;
        }
        else 
        {
            $view->refreshModule("error=edit_server", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Edit server form
    $form = new PanelForm("Edit");
    $form->formStart("index.php?m=adm-servers&p=edit&server_id=" . $server_id);

    // Server Game
    $form->formField("text", "serverGame", $lang['input_servergame'], $games_json[$old_game]['game_title'], "disabled");

    // Server host
    $form->formSelectStart("serverHost", $lang['input_serverhost']);

    for ($i = 0; $i < count($hosts); $i++)
    {
        $host_id = $hosts[$i][$db::HOST_ID];

        if ($host_id == $old_host)
            $form->formSelectOption($hosts[$i][$db::HOST_NAME] . " - " . $hosts[$i][$db::HOST_IP], $host_id, "selected");
        else
            $form->formSelectOption($hosts[$i][$db::HOST_NAME] . " - " . $hosts[$i][$db::HOST_IP], $host_id);
    }

    $form->formSelectEnd();

    // Server max slots
    $min_slots = $games_json[$old_game]['game_min_slots'];
    $max_slots = $games_json[$old_game]['game_max_slots'];

    $form->formSelectStart("serverSlots", $lang['input_serverslots']);

    for ($i = $min_slots; $i <= $max_slots; $i++)
    {
        if (($i % 2) == 0)
        {
            if ($i == $old_slots)
                $form->formSelectOption($i, $i, "selected");
            else
                $form->formSelectOption($i, $i);
        }
    }

    $form->formSelectEnd();

    // Server name/port
    $form->formField("text", "serverName", $lang['input_servername'], $old_name);
    $form->formField("text", "serverPort", $lang['input_serverport'], $old_port);

    if (empty($owner_data))
        $form->formField("text", "serverOwner", $lang['input_serverowner']);
    else
        $form->formField("text", "serverOwner", $lang['input_serverowner'], $owner_data[$db::USER_NAME]);

    $form->formButton("btn-success", "editServer", $lang['btn_apply'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>