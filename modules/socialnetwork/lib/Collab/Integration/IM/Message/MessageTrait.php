<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\ShowHistoryOption;
use Bitrix\Socialnetwork\Collab\Integration\Intranet\ServiceContainer;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use CAllSocNetUser;
use CIMChat;
use CUser;

trait MessageTrait
{
	protected static array $users = [];

	protected function getCollab(int $collabId): Collab
	{
		$collab = CollabRegistry::getInstance()->get($collabId);
		if ($collab === null)
		{
			throw new ObjectNotFoundException('Collab not found');
		}

		return $collab;
	}

	protected function getWorkgroup(int $groupId): Workgroup
	{
		$workgroup = GroupRegistry::getInstance()->get($groupId);
		if ($workgroup === null)
		{
			throw new ObjectNotFoundException('Workgroup not found');
		}

		return $workgroup;
	}

	protected function getChat(): CIMChat
	{
		return new CIMChat(0);
	}

	protected function getName(int $senderId, int $recipientId, int $collabId): string
	{
		if ($senderId === $recipientId)
		{
			return "[USER={$recipientId}][/USER]";
		}

		$workgroup = $this->getWorkgroup($collabId);

		foreach ($workgroup->getSiteIds() as $siteId)
		{
			if (CAllSocNetUser::CanProfileView($this->senderId, $recipientId, $siteId))
			{
				return "[USER={$recipientId}][/USER]";
			}
		}

		$inviteService = ServiceContainer::getInstance()?->getUserService();

		$names = $inviteService?->getFormattedInvitationNameByIds([$recipientId]);

		return $names[$recipientId] ?? '';
	}

	protected function getUser(int $id): array
	{
		if (!isset(static::$users[$id]))
		{
			$user = CUser::getById($id)->fetch();
			static::$users[$id] = is_array($user) ? $user : [];
		}

		return static::$users[$id];
	}

	protected function getGenderSuffix(int $userId): string
	{
		$user = $this->getUser($userId);
		$gender = $user['PERSONAL_GENDER'] ?? '';

		return match($gender)
		{
			'F' => '_F',
			'M' => '_M',
			default => '_N',
		};
	}

	protected function addUsersToChat(int $collabId, int ...$userIds): void
	{
		$collab = $this->getCollab($collabId);

		$this->getChat()->addUser(
			$collab->getChatId(),
			$userIds,
			$collab->getOptionValue(ShowHistoryOption::DB_NAME) !== 'Y',
			true,
			true,
			false,
			false
		);
	}

	protected function deleteUsersFromChat(int $collabId, int ...$userIds): void
	{
		$collab = $this->getCollab($collabId);

		foreach ($userIds as $recipientId)
		{
			$this->getChat()->deleteUser($collab->getChatId(), $recipientId, false, true);
		}
	}

	protected function sendMessage(string $message, int $senderId, int $groupId, ?string $componentId = null): int
	{
		$fields = [
			'MESSAGE' => $message,
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'FROM_USER_ID' => $senderId,
			'SKIP_USER_CHECK' => 'Y',
			'TO_CHAT_ID' => $this->getWorkgroup($groupId)->getChatId(),
			'PARAMS' => [
				'NOTIFY' => 'N',
			],
		];

		if ($componentId)
		{
			$fields['PARAMS']['COMPONENT_ID'] = $componentId;
		}

		$chat = $this->getChat();

		return (int)$chat::addMessage($fields);
	}
}
