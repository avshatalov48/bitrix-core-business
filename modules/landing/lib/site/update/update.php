<?php
namespace Bitrix\Landing\Site\Update;

abstract class Update
{
	/**
	 * Entry point. Returns true on success.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	abstract public static function update(int $siteId): bool;

	/**
	 * Returns site's data.
	 * @param int $siteId Site id.
	 * @return array|null
	 */
	protected static function getId(int $siteId): ?array
	{
		return \Bitrix\Landing\Site::getList(['filter' => ['ID' => $siteId]])->fetch();
	}
}
