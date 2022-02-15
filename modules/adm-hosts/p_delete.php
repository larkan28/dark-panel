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

    // Submit: Delete host
    if (isset($_POST['btn-deleteHost'])) 
    {
        // Host deleted
        if ($db->host_Delete($host_id) === TRUE) {
            $view->refreshModule("success=delete_host", FALSE);
            return;
        }
        else {
            $view->refreshModule("error=delete_host", FALSE);
            return;
        }
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();
    
    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Delete host form
    $form = new PanelForm("Delete");
    $form->formStart("index.php?m=adm-hosts&p=delete&host_id=" . $host_id);

    echo '<div class="alert alert-danger" role="alert">';
    echo sprintf($lang['confirm_del_host'], $host_data[$db::HOST_ID], $host_data[$db::HOST_NAME], $host_data[$db::HOST_IP]);
    echo '</div>';

    $form->formButton("btn-success", "deleteHost", $lang['btn_yes'], " ");
    $form->formButton("btn-primary", "cancelAction", $lang['btn_no']);
    $form->formEnd();

    // Show footer
    $view->webFooter();
}

?>