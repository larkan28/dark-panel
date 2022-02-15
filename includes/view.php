<?php

class PanelView
{
    private $g_sessionData;

    private $mod_name;
    private $mod_file;
    private $mod_exec;
    private $mod_page;

    public function __construct ()
	{
        $g_sessionData = (array) null;
	}

    // Print View
    function printView ()
    {
		// Check if session is active
        if (_getSessionStatus() == TRUE)
        {
            // Set global vars
            global $db;

            // Get user data
            $this->g_sessionData = $db->user_GetDataID(_getSessionID());

            // Check user data
            if (empty($this->g_sessionData))
            {
                _destroySession();

                $this->refreshView("m=user-login");
				exit();
            }
        }
        else
        {
            $this->g_sessionData = (array) null;
        }
		
        // Check if module is setted
        if (isset($_GET['m']))
        {
            // Get module name
            $this->mod_name = $_GET['m'];

            // Get module files
            $this->mod_file = "modules/" . $this->mod_name . "/module.php";
            $this->mod_exec = "modules/" . $this->mod_name . "/" . $this->mod_name . ".php";

            // Verify module files
            if (!file_exists($this->mod_exec)) 
            {
                $this->refreshView("m=user-login&error=not_found");
                exit();
            }

            // Require "module.php" file
            require_once $this->mod_file;

            // Get user access flag
            $access_flag = getUserAccess($this->g_sessionData);

            // Check user access
            if (!hasModuleAccess($module_access, $access_flag))
            {
                $this->refreshView("m=user-login&error=no_access");
                exit();
            }

            // Check if module page is setted
            if (isset($_GET['p']))
            {
                $this->mod_page = $_GET['p'];
                $this->mod_exec = $module_folder . "/p_" . $this->mod_page . ".php";

                if (!file_exists($this->mod_exec))
                {
                    $this->refreshView("m=user-login&error=not_found");
                    exit();
                }
            }
        }
        else
        {
            // If session is not active, redirect to login
            if (_getSessionStatus() == FALSE)
            {
                $this->refreshView("m=user-login");
                exit();
            }
        }

        // Execute module
        if (isset($this->mod_exec))
        {
            require_once $this->mod_exec;

            if (!function_exists('execModule'))
            {
                $this->refreshView("m=user-login&error=missing_module");
                exit();
            }

			execModule();
        }
		else
		{
			$this->webHeader();
			$this->webBody();
			$this->webFooter();
		}
    }
	
	function webHeader ()
    {
        echo '<html><head>';
        echo '<title>Control Panel</title>';
                        
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-sacle=1.0, minimum-scale=1.0">';
                        
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">';
        echo '<link rel="stylesheet" href="css/style.css">';
                        
        echo '<script src="https://kit.fontawesome.com/032762803d.js" crossorigin="anonymous"></script>';
        echo '</head>';
    }
	
	function webBody ()
    {
        // Set global vars
        global $db, $lang, $lang_err, $lang_success;

        // Start body
        echo '<body>';

        // If session exists, show navigation
        if (_getSessionStatus() == TRUE)
        {
            echo '<div class="page-wrapper chiller-theme toggled">';

            // Show navigation
            echo '<a id="show-sidebar" class="btn btn-sm btn-dark" href="#">';
            echo '<i class="fas fa-bars"></i>';
            echo '</a>';

            echo '<nav id="sidebar" class="sidebar-wrapper">';
            echo '<div class="sidebar-content">';
            echo '<div class="sidebar-brand">';
            echo '<a href="index.php">Panel de Control</a>';
            echo '<div id="close-sidebar">';
            echo '<i class="fas fa-times"></i>';
            echo '</div>';
            echo '</div>';

            echo '<div class="sidebar-header">';
            echo '<div class="user-pic">';
            echo '<img class="img-responsive img-rounded" src="images/user.png" alt="User picture">';
            echo '</div>';
            echo '<div class="user-info">';
            echo '<span class="user-name">' . $this->g_sessionData[$db::USER_NAME] . '</span>';
            echo '<span class="user-role">' . $lang[getUserAccess($this->g_sessionData)] . '</span>';
            echo '<span class="user-status">';
            echo '<i class="fa fa-circle"></i><span>Online</span>';
            echo '</span>';
            echo '</div>';
            echo '</div>';
                            
            echo '<div class="sidebar-menu">';
            echo '<ul>';

            $this->showNavigation();
            
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            echo '</nav>';
        }
        else
        {
            echo '<div class="page-wrapper chiller-theme">';
        }

        // Start main content
        echo '<main class="page-content"><div class="container-fluid">';

        // Show title
        if (isset($this->mod_name))
        {
            require $this->mod_file;

            if (_getSessionStatus() == TRUE)
                echo '<h2>' . $module_title . '</h2>';
            else
                echo '<h2 class="text-center">' . $module_title . '</h2>';

            if ($module_showsub == TRUE && isset($this->mod_page))
            {
                echo '<span class="badge badge-primary">';
                echo $module_title . ' > ' . $module_page[$this->mod_page];
                echo '</span>';
            }
        }
        else
            echo '<h2>Home</h2>';

        echo '<hr>';

        // Show module content
        echo '<div class="row">';
        echo '<div class="form-group col-md-12">';

        // Show errors, messages & warnings
        if (isset($_GET['error']))
            echo '<div class="alert alert-danger" role="alert">' . $lang_err[$_GET['error']] . '</div>';

        if (isset($_GET['success']))
            echo '<div class="alert alert-success" role="alert">' . $lang_success[$_GET['success']] . '</div>';

        if (isset($_GET['new_user']) && isset($_GET['new_pw']))
        {
            echo '<div class="alert alert-success" role="alert">';
            echo sprintf($lang['info_newuser'], $_GET['new_user'], $_GET['new_pw']);
            echo '</div>';
        }
    }
	
	function webFooter ()
    {
        echo '</div>';
        echo '</div><hr>';

        echo '<footer class="text-center">';
        echo '<div class="mb-2">';
        echo '<small>Dark Panel © 2020 Copyright - Credits by LarKan</small>';
        echo '</div>';
        echo '</footer>';

        echo '</div></main>';
        echo '</div>';
        echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>';
        echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>';

        echo '</body>';
        echo '<script src="js/panel.js"></script>';
        echo '</html>';
    }
	
	function showNavigation ()
    {
        // Set global vars
        global $db, $lang;

        // Get web modules
        $web_modules = getModulesXML();
                          
        // Show modules on navigation
        foreach ($web_modules->section as $section)
        {
            if (strcmp($section['access'], "admin") == 0 && $this->g_sessionData[$db::USER_ISADMIN] == 0)
                continue;

            echo '<li class="header-menu">';
            echo '<span>' . $lang['sec_' . $section['lang_name']] . '</span>';
            echo '</li>';
                                        
            foreach ($section->option as $nav_option) 
            {
                echo '<li>';
                echo '<a href="index.php?m=' . $nav_option['file'] . '">';
                echo '<i class="fa fa-' . $nav_option['icon'] . '"></i>';
                echo '<span>' . $lang['nav_' . $nav_option['lang_name']] . '</span>';
                echo '</a>';
                echo '</li>';
            }
        }
    }

    // Get/Sets
    function refreshModule ($get_params = "", $include_page = TRUE, $php_file = "")
    {
        if (empty($php_file))
            $php_file = "index.php";

        if (empty($get_params))
            header("Location: " . $php_file . "?" . $this->getModuleUrl($include_page));
        else
            header("Location: " . $php_file . "?" . $this->getModuleUrl($include_page) . "&" . $get_params);
    }
	
    function refreshView ($get_params = "", $php_file = "")
    {
        if (empty($php_file))
            $php_file = "index.php";

        if (empty($get_params))
            header("Location: " . $php_file);
        else
            header("Location: " . $php_file . "?" . $get_params);
    }
	
	function getModuleUrl ($include_page = TRUE)
    {
        if (!isset($_GET['m']))
            return FALSE;

        if ($include_page == TRUE && isset($_GET['p']))
            return "m=" . $_GET['m'] . "&p=" . $_GET['p'];

        return "m=" . $_GET['m'];
    }

    function getCurrModule ()
    {
        if (!isset($_GET['m']))
            return FALSE;

        return $_GET['m'];
    }

    function getSessionData ()
    {
        return $this->g_sessionData;
    }
}

?>