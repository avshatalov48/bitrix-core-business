<?php

namespace Bitrix\Calendar\Access\Rule\Traits;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Util;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Core\Event;

trait SectionTrait
{
	private function isOwner(SectionModel $section, int $userId): bool
	{
		return
			$section->getType() === Event\Tools\Dictionary::CALENDAR_TYPE['user']
			&& $section->getOwnerId() === $userId
		;
	}

	private function isManager(SectionModel $section, int $userId): bool
	{
		$settings = \CCalendar::GetSettings(array('request' => false));

		return
			Loader::includeModule('intranet')
			&& $section->getType() === Event\Tools\Dictionary::CALENDAR_TYPE['user']
			&& ($settings['dep_manager_sub'] ?? false)
			&& Util::isManagerForUser($userId, $section->getOwnerId())
		;
	}
}