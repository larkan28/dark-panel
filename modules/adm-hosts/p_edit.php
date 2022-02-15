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

    // Get host id
    $host_id = $_GET['host_id'];

    // Validate host id
    if (!isValidID($host_id))
    {
        $view->refreshModule("error=invalid_host", FALSE);
        return;
    }

    // Get all host data
    $host_data = $db->host_GetData($host_id);

    // Validate host data
    if (empty($host_data))
    {
        $view->refreshModule("error=invalid_host", FALSE);
        return;
    }

    // Get old values
    $old_ip = $host_data[$db::HOST_IP];
    $old_name = $host_data[$db::HOST_NAME];

    // Submit: Edit host
    if (isset($_POST['btn-editHost'])) 
    {
        // Get form input
        $host_ip = sanitizeInput($_POST['formEdit_hostIP']);
        $host_name = sanitizeInput($_POST['formEdit_hostName']);

        // Compare old values
        if ($host_name === $old_name && $host_ip == $old_ip)
        {
            $view->refreshModule("host_id=" . $host_id . "&error=same_values");
            return;
        }

        // Check host name
        if (empty($host_name))
        {
            $view->refreshModule("host_id=" . $host_id . "&error=empty_host");
            return;
        }

        // Check ip address
        if (!isValidIP($host_ip)) 
        {
            $view->refreshModule("host_id=" . $host_id . "&error=invalid_ip");
            return;
        }

        // Update values
        $host_data[$db::HOST_IP] = setUserValue($old_ip, $host_ip);
        $host_data[$db::HOST_NAME] = setUserValue($old_name, $host_name);

        // Host edited
        if ($db->host_Edit($host_data) === TRUE) 
        {
            $view->refreshModule("success=edit_host", FALSE);
            return;
        }
        else 
        {
            $view->refreshModule("error=edit_host", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();
    
    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Edit host form
    $form = new PanelForm("Edit");

    $form->formStart("index.php?m=adm-hosts&p=edit&host_id=" . $host_id);
    $form->formField("text", "hostName", $lang['input_hostname'], $old_name);
    $form->formField("text", "hostIP", $lang['input_ip'], $old_ip);
    $form->formButton("btn-success", "editHost", $lang['btn_apply'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>