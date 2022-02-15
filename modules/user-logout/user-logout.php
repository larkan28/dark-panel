<?php

function execModule ()
{
	// Set global vars
	global $view;
	
	// Destroy session
	_destroySession();

	// Refresh page
	$view->refreshView("m=user-login&success=logout");
}

?>