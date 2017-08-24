<?php
return array(
'ft_login_form' => '[%s] Login',
'bind_ip' => 'Lock Session to IP?',
'btn_login' => 'Login',
'btn_logout' => 'Logout (%s)',
		
'msg_logged_out' => 'You are now logged out.',
'msg_authenticated' => 'Welcome back, you are now authenticated as %s.',

'err_login_failed' => 'Login failed. You have %s more attempt(s) until you are blocked for %s.',
'err_login_ban' => 'Please wait %s before you try again.',
#########
'mail_subj_login_threat' => '[%s] Login Attempts',
'mail_body_login_threat' => '
Hello %s,

There was a failed login attempt on %s from this IP.

%s

Please note that there will be no further messages for a while, in case your account is really under attack.

Kind Regards,
The %2$s Team
',
);
