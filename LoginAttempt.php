<?php
namespace GDO\Login;

use GDO\DB\GDO;
use GDO\DB\GDO_AutoInc;
use GDO\DB\GDO_CreatedAt;
use GDO\Net\GDO_IP;
use GDO\User\GDO_User;
/**
 * Database table for login attempts.
 * 
 * @author gizmore
 * @since 2.0
 *
 */
final class LoginAttempt extends GDO
{
	public function gdoCached() { return false; }
	public function gdoColumns()
	{
		return array(
			GDO_AutoInc::make('la_id'),
			GDO_IP::make('la_ip')->notNull(),
			GDO_User::make('la_user_id'),
			GDO_CreatedAt::make('la_time'),
		);
	}
}
