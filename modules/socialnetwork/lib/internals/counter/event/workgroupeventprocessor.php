<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter\Event;

use Bitrix\Socialnetwork\Internals\Counter\Push\WorkgroupRequestsInSender;
use Bitrix\Socialnetwork\Internals\Counter\Push\WorkgroupRequestsOutSender;
use Bitrix\Socialnetwork\UserToGroupTable;

class WorkgroupEventProcessor
{
	/**
	 *
	 */
	public function process(): void
	{
		$events = (EventCollection::getInstance())->list();

		$requestInPushList = [];
		$requestOutPushList = [];

		foreach ($events as $event)
		{
			/* @var $event Event */
			$eventType = $event->getType();
			$groupId = $event->getGroupId();

			$requestWorkgroupEventsList = [
				EventDictionary::EVENT_WORKGROUP_USER_ADD,
				EventDictionary::EVENT_WORKGROUP_USER_UPDATE,
				EventDictionary::EVENT_WORKGROUP_USER_DELETE,
			];

			if (in_array($eventType, $requestWorkgroupEventsList, true))
			{
				$usedRolesList = $event->getUsedRoles();
				if (!in_array(UserToGroupTable::ROLE_REQUEST, $usedRolesList, true))
				{
					continue;
				}

				$initiatedByType = $event->getInitiatedByType();
				switch ($initiatedByType)
				{
					case UserToGroupTable::INITIATED_BY_USER:
						$requestInPushList[] = [
							'EVENT' => $eventType,
							'GROUP_ID' => $groupId,
						];
						break;
					case UserToGroupTable::INITIATED_BY_GROUP:
						$requestOutPushList[] = [
							'EVENT' => $eventType,
							'GROUP_ID' => $groupId,
						];
						break;
				}
			}
		}

		if (!empty($requestInPushList))
		{
			(new WorkgroupRequestsInSender())->send($requestInPushList);
		}

		if (!empty($requestOutPushList))
		{
			(new WorkgroupRequestsOutSender())->send($requestOutPushList);
		}

	}

	/**
	 * @return EventResourceCollection
	 */
	private function getResourceCollection(): EventResourceCollection
	{
		return EventResourceCollection::getInstance();
	}
}