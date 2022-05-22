<?php
namespace GDO\Login;

use GDO\Core\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\User\GDT_User;
use GDO\Net\GDT_IP;
use GDO\DB\GDT_CreatedAt;

class GDO_LoginHistory extends GDO
{
	public function gdoCached() { return false; }
	public function gdoColumns()
	{
		return array(
			GDT_AutoInc::make('lh_id'),
			GDT_User::make('lh_user_id'),
			GDT_IP::make('lh_ip'),
			GDT_CreatedAt::make('lh_authenticated_at'),
		);
	}
}
