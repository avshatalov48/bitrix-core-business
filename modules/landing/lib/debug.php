<?php
namespace Bitrix\Landing;

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
}