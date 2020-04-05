<?php
namespace Bitrix\Main\Replica;

class Bind
{
	/**
	 * Initializes replication process on main side.
	 *
	 * @return void
	 */
	public function start()
	{
		\Bitrix\Replica\Client\HandlersManager::register(new UrlMetadataHandler());
	}
}
