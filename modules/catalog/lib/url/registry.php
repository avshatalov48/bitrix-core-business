<?php
namespace Bitrix\Catalog\Url;

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
				AdminPage\CatalogBuilder::class,
				ShopBuilder::class,
				InventoryBuilder::class,
			],
			'catalog'
		);
	}
}
