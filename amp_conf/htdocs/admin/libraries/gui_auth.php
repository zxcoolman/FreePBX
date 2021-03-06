<?php 
// Set language, needs to be set here for full localization of the gui
set_language();

//dbug('sess', $_SESSION);
//dbug('server', $_SERVER);

   
//promt for a password if there there is no user set
if (!isset($_SESSION['AMP_user'])) {
	//|| (isset($_SESSION['AMP_user']->username) && $_SESSION['AMP_user']->username != $_SERVER['PHP_AUTH_USER'])) {
	//if we dont have a username/pass promt for one
	if (!$username || !$password) {
		switch(strtolower($amp_conf['AUTHTYPE'])) {
			case 'database':
				$no_auth = 	load_view($amp_conf['VIEW_LOGIN']);
			break;
			case 'webserver':
				header('HTTP/1.0 401 Unauthorized');
			case 'none':
				break;
		}
	}
	
	//test credentials
	switch (strtolower($amp_conf['AUTHTYPE'])) {
		case 'webserver':
			// handler for apache doing authentication
			$_SESSION['AMP_user'] = new ampuser($_SERVER['PHP_AUTH_USER']);
			if ($_SESSION['AMP_user']->username == $amp_conf['AMPDBUSER']) {
				// admin user, grant full access
				$_SESSION['AMP_user']->setAdmin();
			} else {
				unset($_SESSION['AMP_user']);
				//header('HTTP/1.0 401 Unauthorized');
			}
			break;
		case 'none':
			$_SESSION['AMP_user'] = new ampuser($amp_conf['AMPDBUSER']);
			$_SESSION['AMP_user']->setAdmin();
			break;
		case 'database':
		default:
			// not logged in, and have provided a user/pass
			$_SESSION['AMP_user'] = new ampuser($username);
			if (!$_SESSION['AMP_user']->checkPassword(sha1($password))) {
				// failed, one last chance -- fallback to amportal.conf db admin user
				if ($amp_conf['AMP_ACCESS_DB_CREDS']
					&& $username == $amp_conf['AMPDBUSER'] 
					&& $password == $amp_conf['AMPDBPASS']
				) {
					// password succesfully matched amportal.conf db admin user, set admin access
					$_SESSION['AMP_user']->setAdmin();
				} else {
					// password failed and admin user fall-back failed
					unset($_SESSION['AMP_user']);
					$no_auth = 	load_view($amp_conf['VIEW_LOGIN']);
				}
			} 
			break;
	}
	
}
if (isset($_SESSION['AMP_user'])) {
	define('FREEPBX_IS_AUTH', 'TRUE');
}
?>