<?php

namespace Bitrix\Catalog\Integration\Crm;

use Bitrix\Catalog\ProductCompilationTable;
use Bitrix\Main\Event;

class EventsHandler
{
	public static function OnCrmDealRecoverFromRecycleBin(Event $event): void
	{
		$entityId = $event->getParameter('entityId');
		$newEntityId = $event->getParameter('newEntityId');
		$compilationIterator = ProductCompilationTable::getList([
			'select' => ['ID'],
			'filter' => ['=DEAL_ID' => $entityId],
		]);
		while ($compilation = $compilationIterator->fetch())
		{
			ProductCompilationTable::update((int)$compilation['ID'], ['DEAL_ID' => $newEntityId]);
		}
	}

	public static function OnCrmDealEraseFromRecycleBin(Event $event): void
	{
		$entityId = $event->getParameter('entityId');
		$compilationIterator = ProductCompilationTable::getList([
			'select' => ['ID'],
			'filter' => ['=DEAL_ID' => $entityId],
		]);
		while ($compilation = $compilationIterator->fetch())
		{
			ProductCompilationTable::delete((int)$compilation['ID']);
		}
	}
}
