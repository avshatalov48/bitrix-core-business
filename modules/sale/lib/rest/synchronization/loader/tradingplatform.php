<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


use Bitrix\Sale\TradingPlatformTable;

class TradingPlatform extends Entity
{
	protected function getEntityTable()
	{
		return new TradingPlatformTable();
	}
}