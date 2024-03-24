<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;

final class MembershipProvider extends AbstractProvider
{

	public function isAvailable(): bool
	{
		return true;
	}

	public function getTypeId(): string
	{
		return 'membership';
	}

	protected function fill(): void
	{
		$userIds = $this->getEntityIdsFromRecentActivityItems();
		$users = [];
		if (!empty($userIds))
		{
			$users = UserTable::query()
				->setSelect(['ID', 'NAME', 'LAST_NAME'])
				->whereIn('ID', $userIds)
				->fetchAll()
			;
		}

		foreach ($users as $user)
		{
			$this->addEntity((int)$user['ID'], $user);
		}

		$culture = Application::getInstance()->getContext()->getCulture();
		$nameFormat = is_null($culture) ? '#NAME# #LAST_NAME#' : $culture->getNameFormat();

		foreach ($this->recentActivityDataItems as $item)
		{
			$user = $this->getEntity($item->getEntityId());

			if (empty($user))
			{
				continue;
			}

			$name = $user['NAME'] ?? '';
			$lastName = $user['LAST_NAME'] ?? '';

			$message = Loc::getMessage(
				'SONET_MEMBERSHIP_RECENT_ACTIVITY_DESCRIPTION',
				[
					'#CONTENT#' => trim(str_replace(
						['#NAME#', '#LAST_NAME#'],
						[$name, $lastName],
						$nameFormat,
					))
				],
			);
			$item->setDescription($message);
		}
	}
}
