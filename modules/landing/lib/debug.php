<?php
namespace Bitrix\Landing;

use Bitrix\Main\ModuleManager;

class Debug
{
	/**
	 * Gets last query in ORM.
	 * @return string
	 */
	public static function q()
	{
		return \Bitrix\Main\Entity\Query::getLastQuery();
	}

	/**
	 * Logging in system log.
	 * @param string $itemId Log item id.
	 * @param mixed $itemDesc Log item description.
	 * @param string $typeId Log type id.
	 * @return void
	 */
	public static function log($itemId, $itemDesc, $typeId = 'LANDING_LOG')
	{
		if (is_array($itemDesc))
		{
			$itemDesc = print_r($itemDesc, true);
		}
		\CEventLog::add([
			'SEVERITY' => 'NOTICE',
			'AUDIT_TYPE_ID' => $typeId,
			'MODULE_ID' => 'landing',
			'ITEM_ID' => $itemId,
			'DESCRIPTION' => $itemDesc
		]);
	}

	/**
	 * Writes message to log file if permitted.
	 * @param string $message Message.
	 * @return void
	 */
	public static function logToFile(string $message): void
	{
		static $write = null;

		if ($write === null)
		{
			$write = defined('LANDING_FILE_WORK_LOG_TO_FILE') && LANDING_FILE_WORK_LOG_TO_FILE === true;

			if (!$write && ModuleManager::isModuleInstalled('bitrix24') && !Manager::isCloudDisable())
			{
				$write = true;
			}
		}

		if ($write)
		{
			AddMessage2Log($message, 'landing', 7);
		}
	}
}
