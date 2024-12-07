<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait\EntityLoadTrait;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;

final class MembershipProvider extends AbstractProvider
{
	use EntityLoadTrait;

	public function isAvailable(): bool
	{
		return true;
	}

	public function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['membership'];
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
