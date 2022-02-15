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

    // Submit: Create new server
    if (isset($_POST['btn-newServer'])) 
    {
        // Get input values
        $server_name = sanitizeInput($_POST['formNew_serverName']);
        $server_game = $_POST['formNew_serverGame'];
        $server_host = $_POST['formNew_serverHost'];
        $server_port = sanitizeInput($_POST['formNew_serverPort']);
        $server_owner = sanitizeInput($_POST['formNew_serverOwner']);
        $server_slots = $_POST['formNew_serverSlots'];

        // Validate server name
        if (empty($server_name))
        {
            $view->refreshModule("error=invalid_sname");
            return;
        }

        // Validate port number
        if (!isValidPort($server_port))
        {
            $view->refreshModule("error=invalid_port");
            return;
        }

        // Get host array index
        $host_index = isValidIndexIn($hosts, $server_host);

        // Validate server host
        if ($host_index === FALSE)
        {
            $view->refreshModule("error=invalid_host");
            return;
        }

        // Validate server owner
        if (empty($server_owner))
        {
            $view->refreshModule("error=invalid_owner");
            return;
        }

        // Check if port is already used
        if ($db->server_Exists($server_host, $server_port) === TRUE)
        {
            $view->refreshModule("error=port_used");
            return;
        }

        // Get user data if exists
        $user_data = $db->user_GetData($server_owner);

        // Check user data
        if (empty($user_data))
        {
            // Try add new user
            $user_data = $db->user_Add($server_owner);

            // Validate new user
            if (empty($user_data))
            {
                $view->refreshModule("error=add_user");
                return;
            }

            // Cache new user data
            $new_username = $user_data[0];
            $new_password = $user_data[1];

            // Get new user id
            $user_data = $db->user_GetData($new_username);
        }
        else
        {
            if ($user_data[$db::USER_PARENT] != 0)
            {
                $view->refreshModule("error=no_sub_owner");
                return;
            }
        }

        $user_id = $user_data[0];

        // Server added
        if ($db->server_Add($server_name, $server_port, $server_slots, $server_game, $server_host, $user_id) === TRUE)
        {
            if (isset($new_username) && isset($new_password))
                $view->refreshModule("success=new_server&new_user=" . $new_username . "&new_pw=" . $new_password, FALSE);
            else
                $view->refreshModule("success=new_server", FALSE);

            return;
        }
        else
        {
            $view->refreshModule("error=new_server", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // New server form
    $form = new PanelForm("New");

    // Check game type
    if (isset($_GET['game_type']))
    {
        // Get game selected
        $game_type = $_GET['game_type'];
        $game_valid = FALSE;

        // Compare games id
        foreach ($games_json as $game => $game_data)
        {
            if (strcmp($game, $game_type) == 0)
            {
                $game_valid = TRUE;
                break;
            }
        }

        // Validate game type
        if ($game_valid == FALSE)
        {
            $view->refreshModule("error=invalid_game", FALSE);
            return;
        }

        $form->formStart("index.php?m=adm-servers&p=new&game_type=" . $game_type);

        // Server game type
        $form->formSelectStart("serverGame", $lang['input_servergame'], "sel-GameType");
        $form->formSelectOption("-", "-");

        foreach ($games_json as $game => $game_data)
        {
            if (strcmp($game, $game_type) == 0)
                $form->formSelectOption($game_data['game_title'], $game, "selected");
            else
                $form->formSelectOption($game_data['game_title'], $game);
        }

        $form->formSelectEnd();

        // Server host
        $form->formSelectStart("serverHost", $lang['input_serverhost']);

        for ($i = 0; $i < count($hosts); $i++)
            $form->formSelectOption($hosts[$i][$db::HOST_NAME] . " - " . $hosts[$i][$db::HOST_IP], $hosts[$i][$db::HOST_ID]);

        $form->formSelectEnd();

        // Server client
        $form->formSelectStart("serverClient", $lang['input_serverclient']);
        $form->formSelectOption("Default", "default");
        $form->formSelectEnd();

        // Server max slots
        $min_slots = $games_json[$game_type]['game_min_slots'];
        $max_slots = $games_json[$game_type]['game_max_slots'];

        $form->formSelectStart("serverSlots", $lang['input_serverslots']);

        for ($i = $min_slots; $i <= $max_slots; $i++)
        {
            if (($i % 2) == 0)
                $form->formSelectOption($i, $i);
        }

        $form->formSelectEnd();

        // Set automatic port
        $auto_port = "";

        // Get all servers from db
        $servers = $db->server_GetAll();

        // Try get automatic port
        if (!empty($servers))
            $auto_port = getFreePort($servers, $game_type);
        else
            $auto_port = $games_json[$game_type]['game_start_port'];

        // Server name/port/owner
        $form->formField("text", "serverName", $lang['input_servername'], $lang['input_servername']);
        $form->formField("text", "serverPort", $lang['input_serverport'], $lang['input_serverport'], $auto_port);
        $form->formField("text", "serverOwner", $lang['input_serverowner'], $lang['input_serverowner']);

        $form->formButton("btn-success", "newServer", $lang['btn_create'], " ");
    }
    else
    {
        $form->formStart("index.php?m=adm-servers&p=new");
        $form->formSelectStart("serverGame", $lang['input_servergame'], "sel-GameType");
        $form->formSelectOption("-", "-", "selected");

        foreach ($games_json as $game => $game_data)
            $form->formSelectOption($game_data['game_title'], $game);

        $form->formSelectEnd();
    }
    
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>