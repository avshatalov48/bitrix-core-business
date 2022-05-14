<?php

namespace Bitrix\Rest;

use \Bitrix\Main\Application;

/**
 * Class Helper
 * @package Bitrix\Rest
 */
class Helper
{
	/**
	 * Recoveries rests agents.
	 * @return string
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 */
	public static function recoveryAgents(): string
	{
		$connection = Application::getConnection();
		$connection->query(
			"UPDATE b_agent SET ACTIVE = 'Y', RETRY_COUNT = 0, RUNNING='N'
					WHERE MODULE_ID = 'rest' and RETRY_COUNT > 2 and ACTIVE = 'N';"
		);

		return '\Bitrix\Rest\Helper::recoveryAgents();';
	}
}
