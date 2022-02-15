<?php

function execModule () 
{
    // Set global vars
    global $db, $view, $lang;

    // Submit: Login user
    if (isset($_POST['btn-loginUser']))
    {
        $user_name = $_POST['formLogin_userName'];
        $user_pass = $_POST['formLogin_userPass'];

        // Validate username
        if (empty($user_name))
        {
            $view->refreshModule("error=invalid_username");
            exit();
        }

        // Validate password
        if (empty($user_pass))
        {
            $view->refreshModule("error=invalid_password");
            exit();
        }

        // Try login user
        $login_id = $db->user_Login($user_name, $user_pass);

        // User login
        if ($login_id === FALSE)
        {
            $view->refreshModule("error=login");
            exit();
        }
        else 
        {
            _openSession($login_id);

            $view->refreshView("success=login");
            exit();
        }
    }

    // Submit: Recovery password
    if (isset($_POST['btn-recoveryPass']))
    {
        $view->refreshModule("p=recovery");
        exit();
    }

    // Show header & body
    $view->webHeader();
    $view->webBody();

	// Require custom form class
    require_once(PATH_INCLUDES . "form.php");

    // Card login: Start
    echo '<div class="card mx-auto" style="width: 22rem;">';
    echo '<article class="card-body">';
    echo '<h4 class="card-title text-center mb-4 mt-1">' . $lang['form_login'] . '</h4><hr>';

    // Web languages
    $web_langs = getLangsXML();

    // Login user form
    $form = new PanelForm("Login");

    $form->formStart("index.php?m=user-login");
    $form->formFieldIcon("text", "userName", $lang['input_username'], "user");
    $form->formFieldIcon("password", "userPass", $lang['input_password'], "lock");
    $form->formSelectStartIcon("userAdmin", "globe-americas", "sel-CurrLang");

    foreach ($web_langs->lang as $language)
    {
        if (strcmp($language['value'], _getLangID()) == 0)
            $form->formSelectOption($language['name'], $language['value'], "selected");
        else
            $form->formSelectOption($language['name'], $language['value']);
    }

    $form->formSelectEndIcon();
    $form->formButton("btn-primary btn-block", "loginUser", $lang['btn_login']);
    $form->formButton("btn-secondary btn-block", "recoveryPass", $lang['btn_forgot']);
    $form->formEnd();

    // Card login: End
    echo '</article>';
    echo '</div>';

    // Show footer
    $view->webFooter();
}

?>