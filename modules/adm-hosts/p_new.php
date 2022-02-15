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

    // Submit: Create new host
    if (isset($_POST['btn-newHost'])) 
    {
        $host_name = sanitizeInput($_POST['formNew_hostName']);
        $host_ip = sanitizeInput($_POST['formNew_hostIP']);

        // Validate name
        if (empty($host_name)) 
        {
            $view->refreshModule("error=invalid_hname");
            return;
        }

        // Validate ip
        if (!isValidIP($host_ip)) 
        {
            $view->refreshModule("error=invalid_ip");
            return;
        }

        // Host added
        if ($db->host_Add($host_name, $host_ip) === TRUE) 
        {
            $view->refreshModule("success=new_host", FALSE);
            return;
        }
        else 
        {
            $view->refreshModule("error=new_host", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // New host form
    $form = new PanelForm("New");

    $form->formStart("index.php?m=adm-hosts&p=new");
    $form->formField("text", "hostName", $lang['input_hostname']);
    $form->formField("text", "hostIP", $lang['input_ip']);
    $form->formButton("btn-success", "newHost", $lang['btn_create'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>