<?php
namespace GDO\Login;

use GDO\DB\GDO;
use GDO\DB\GDO_AutoInc;
use GDO\User\GDO_User;
use GDO\Net\GDO_IP;
use GDO\DB\GDO_CreatedAt;

class LoginHistory extends GDO
{
	public function gdoCached() { return false; }
	public function gdoColumns()
	{
		return array(
			GDO_AutoInc::make('lh_id'),
			GDO_User::make('lh_user_id'),
			GDO_IP::make('lh_ip'),
			GDO_CreatedAt::make('lh_authenticated_at'),
		);
	}
}
