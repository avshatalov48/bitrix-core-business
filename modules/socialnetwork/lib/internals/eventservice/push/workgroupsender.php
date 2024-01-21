<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\EventService\Push;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Internals\EventService\Event\WorkgroupEvent;

class WorkgroupSender
{
	private ?array $subscribedUsers = null;

	public function __construct()
	{

	}

	/**
	 * @param array $pushList
	 */
	public function send(array $pushList, array $notVisibleGroupsUsers): void
	{
		if (
			!ModuleManager::isModuleInstalled('pull')
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		$subscribedUsers = $this->getSubscribedUsers();

		foreach ($pushList as $push)
		{
			$pushCommand = PushEventDictionary::getPushEventType($push['EVENT']);
			$groupId = (int)$push['GROUP_ID'];
			$userId = (int)($push['USER_ID'] ?? null);

			if (empty($pushCommand))
			{
				continue;
			}

			$pushParams = [
				'module_id' => 'socialnetwork',
				'command' => $pushCommand,
				'params' => [ 'GROUP_ID' => $groupId, 'USER_ID' => $userId ],
			];

			$recipients = (
				isset($notVisibleGroupsUsers[$groupId])
					? array_intersect($subscribedUsers, $notVisibleGroupsUsers[$groupId])
					: $subscribedUsers
			);

				PushSender::sendPersonalEvent($recipients, $pushCommand, $pushParams);
		}
	}

	public function sendForUserAddedAndRemoved(Event $event, array $notVisibleGroupsUsers): void
	{
		$eventData = $event->getData();
		$groupId = $event->getGroupId();
		$userId = $event->getUserId();

		$pushParams = [
			'module_id' => 'socialnetwork',
			'command' => $event->getType(),
			'params' => [ 'GROUP_ID' => $groupId, 'USER_ID' => $userId, ],
		];

		if (($eventData['ROLE'] ?? null) === UserToGroupTable::ROLE_REQUEST)
		{
			PushService::addEvent([ $eventData['USER_ID'] ], $pushParams);
		}
		else
		{
			$subscribedUsers = $this->getSubscribedUsers();

			if (!array_key_exists('USER_ID', $eventData))
			{
				$eventData['USER_ID'] = [];
			}

			if (!is_array($eventData['USER_ID']))
			{
				$eventData['USER_ID'] = [ $eventData['USER_ID'] ];
			}

			$recipients = (
				isset($notVisibleGroupsUsers[$groupId])
					? array_intersect($subscribedUsers, array_merge($eventData['USER_ID'], $notVisibleGroupsUsers[$groupId]))
					: $subscribedUsers
			);

			PushService::addEvent($recipients, $pushParams);
		}
	}

	private function getSubscribedUsers(): array
	{
		if ($this->subscribedUsers === null)
		{
			$query = \Bitrix\Pull\Model\WatchTable::query();
			$query->addSelect('USER_ID');
			$query->where('TAG', PullDictionary::PULL_WORKGROUPS_TAG);
			$this->subscribedUsers = array_column($query->fetchAll(), 'USER_ID');
		}

		return $this->subscribedUsers;
	}
}
