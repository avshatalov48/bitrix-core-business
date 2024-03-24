<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\LogTable;

final class LiveFeedProvider extends AbstractProvider
{

	public function isAvailable(): bool
	{
		return true;
	}

	public function getTypeId(): string
	{
		return 'livefeed';
	}

	protected function fill(): void
	{
		$logIds = $this->getEntityIdsFromRecentActivityItems();
		$logs = [];
		if (!empty($logIds))
		{
			$logs = LogTable::query()
				->setSelect(['ID', 'TITLE'])
				->whereIn('ID', $logIds)
				->fetchAll()
			;
		}

		foreach ($logs as $log)
		{
			$this->addEntity((int)$log['ID'], $log);
		}

		foreach ($this->recentActivityDataItems as $item)
		{
			$log = $this->getEntity($item->getEntityId());

			if (empty($log))
			{
				continue;
			}

			$message = Loc::getMessage(
				'SONET_LIVEFEED_RECENT_ACTIVITY_DESCRIPTION',
				['#CONTENT#' => Emoji::decode($log['TITLE'])],
			);
			$item->setDescription($message);
		}
	}
}
