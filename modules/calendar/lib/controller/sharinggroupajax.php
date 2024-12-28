<?php

namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\SectionAccessController;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sharing;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;

final class SharingGroupAjax extends Controller
{
	public function getAutoWiredParameters()
	{
		return [
			new ExactParameter(
				Section::class,
				'section',
				function(string $className, CurrentUser $currentUser, ?int $groupId = null): ?Section
				{
					if (!$groupId)
					{
						$this->addError(new Error('access denied'));

						return null;
					}
					$sections = (new Mappers\Section())->getMap([
						'OWNER_ID' => $groupId,
						'CAL_TYPE' => Dictionary::CALENDAR_TYPE['group'],
						'ACTIVE' => 'Y',
					]);
					if (!$sections->count())
					{
						$this->addError(new Error('access denied'));

						return null;
					}
					$section = $sections->fetch();

					$hasAccess = SectionAccessController::can(
						$currentUser->getId(),
						ActionDictionary::ACTION_SECTION_EDIT,
						$section->getId()
					);
					if (!$hasAccess)
					{
						$this->addError(new Error('access denied'));

						return null;
					}

					return $section;
				},
			),
		];
	}

	public function enableSharingAction(CurrentUser $currentUser, ?Section $section): ?array
	{
		if ($this->getErrors())
		{
			return null;
		}

		$sharing = new Sharing\SharingGroup($section->getOwner()->getId(), $currentUser->getId());
		$result = $sharing->enable();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $sharing->getLinkInfo();
	}

	public function disableSharingAction(CurrentUser $currentUser, ?Section $section): ?array
	{
		if ($this->getErrors())
		{
			return null;
		}

		$sharing = new Sharing\SharingGroup($section->getOwner()->getId(), $currentUser->getId());
		$result = $sharing->disable();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return [];
	}

	public function generateJointSharingLinkAction(CurrentUser $currentUser, array $memberIds, ?Section $section): ?array
	{
		if ($this->getErrors())
		{
			return null;
		}

		$sharing = new Sharing\SharingGroup($section->getOwner()->getId(), $currentUser->getId());
		$result = $sharing->generateGroupJointLink($memberIds);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result->getData();
	}

	public function enableAndGetSharingConfigAction(CurrentUser $currentUser, ?Section $section): ?array
	{
		if ($this->getErrors())
		{
			return null;
		}

		$sharing = new Sharing\SharingGroup($section->getOwner()->getId(), $currentUser->getId());
		if (!$sharing->isEnabled())
		{
			$result = $sharing->enable();

			if (!$result->isSuccess())
			{
				$this->addErrors($result->getErrors());

				return null;
			}
		}

		$portalCalendarConfig = \CCalendar::GetSettings();

		return [
			'link' => $sharing->getLinkInfo(),
			'userCalendarSettings' => [
				'week_holidays' => $portalCalendarConfig['week_holidays'],
				'week_start' => $portalCalendarConfig['week_start'],
				'work_time_start' => $portalCalendarConfig['work_time_start'],
				'work_time_end' => $portalCalendarConfig['work_time_end'],
			],
			'user' => (new Sharing\Sharing($currentUser->getId()))->getUserInfo(),
		];
	}
}
