<?php
namespace GDO\Login;

use GDO\Core\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\Net\GDT_IP;
use GDO\User\GDT_User;

/**
 * Database table for login attempts.
 * 
 * @author gizmore
 * @version 6.11.0
 * @since 2.0
 */
final class GDO_LoginAttempt extends GDO
{
	public function gdoCached() { return false; }
	public function gdoColumns()
	{
		return [
			GDT_AutoInc::make('la_id'),
			GDT_IP::make('la_ip')->notNull(),
			GDT_User::make('la_user_id'),
			GDT_CreatedAt::make('la_time'),
		];
	}
	
}
