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

    // Get host data
    $host_data = $db->host_GetData($server_data[$db::SERVER_HOSTID]);

    // Validate host data
    if (empty($host_data))
    {
        $view->refreshModule("error=invalid_host", FALSE);
        return;
    }

    // Submit: Delete server
    if (isset($_POST['btn-deleteServer'])) 
    {
        // Server deleted
        if ($db->server_Delete($server_id) === TRUE) {
            $view->refreshModule("success=delete_server", FALSE);
            return;
        }
        else {
            $view->refreshModule("error=delete_server", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();
    
    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Delete server form
    $form = new PanelForm("Delete");
    $form->formStart("index.php?m=adm-servers&p=delete&server_id=" . $server_id);

    echo '<div class="alert alert-danger" role="alert">';
    echo sprintf($lang['confirm_del_server'], $server_data[$db::SERVER_ID], getGameTitle($server_data[$db::SERVER_GAMEID]), $host_data[$db::HOST_IP], $server_data[$db::SERVER_PORT]);
    echo '</div>';

    $form->formButton("btn-success", "deleteServer", $lang['btn_yes'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_no']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>