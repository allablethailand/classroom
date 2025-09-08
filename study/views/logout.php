<?php
    session_start();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    function getSubdomain() {
		$host = $_SERVER['HTTP_HOST'];
		$subdomain = preg_replace('/\.?origami\.(life|local)$/', '', $host);
		return (!empty($subdomain) && $subdomain !== 'www' && $subdomain !== 'dev') ? $subdomain : '';
	}
	$subdomain = getSubdomain();
	if($subdomain) {
		$page = '/';
	} else {
		if($_SESSION['tenant']) {
			$page = '/'.$_SESSION['tenant'];
		} else {
			$page = '/';
		}
	}
	session_destroy();
	echo '<script language="javascript">window.location="'.$page.'";</script>';
?>