<?php
namespace Bitrix\Catalog\Url\AdminPage;

use Bitrix\Main;

class Registry
{
	/**
	 * Returns list of url builders for catalogs.
	 *
	 * @param Main\Event $event
	 * @return Main\EventResult
	 *
	 * @noinspection PhpUnused
	 */
	public static function getBuilderList(Main\Event $event): Main\EventResult
	{
		return new Main\EventResult(
			Main\EventResult::SUCCESS,
			[
				'\Bitrix\Catalog\Url\AdminPage\CatalogBuilder'
			],
			'catalog'
		);
	}
}