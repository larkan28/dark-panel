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

    // Submit: Recovery password
    if (isset($_POST['btn-recoveryPass'])) 
    {
        $user_mail = $_POST['formRecovery_userMail'];

        // Validate email
        if (empty($user_mail) || !filter_var($user_mail, FILTER_VALIDATE_EMAIL)) 
        {
            $view->refreshModule("error=invalid_email");
            return;
        }
    }
    
    // Show header & body
    $view->webHeader();
    $view->webBody();

    // Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Card recovery: Start
    echo '<div class="card mx-auto" style="width: 22rem;">';
    echo '<article class="card-body">';
    echo '<h4 class="card-title text-center mb-4 mt-1">' . $lang['form_recovery'] . '</h4><hr>';

    // Recovery password form
    $form = new PanelForm("Recovery");
    $form->formStart("index.php?m=user-login&p=recovery");
    $form->formFieldIcon("text", "userMail", $lang['input_usermail'], "envelope");
    $form->formButton("btn-success btn-block", "recoveryPass", $lang['btn_submit']);
    $form->formButton("btn-primary btn-block", "cancelAction", $lang['btn_back']);
    $form->formEnd();

    // Card recovery: End
    echo '</article>';
    echo '</div>';

    // Show footer
    $view->webFooter();
}

?>