<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

use Bitrix\Intranet\Service\ServiceContainer;
use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\SocialNetwork\Collab\Analytics\CollabAnalytics;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\ShowHistoryOption;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use CAllSocNetUser;
use CIMChat;
use CUser;

class ActionMessageSender
{
	protected static array $users = [];

	protected Collab $collab;
	protected CIMChat $chat;
	protected ?ServiceContainer $container;

	protected int $senderId;

	/**
	 * @throws ObjectException
	 */
	public function __construct(int $collabId, int $senderId)
	{
		$collab = CollabRegistry::getInstance()->get($collabId);
		if ($collab === null)
		{
			throw new ObjectException('Collab not found');
		}

		$this->collab = $collab;
		$this->senderId = $senderId;

		$this->init();
	}

	public function sendActionMessage(ActionType $action, array $parameters = []): int
	{
		$message = $this->runAction($action, $parameters);

		if (empty($message))
		{
			return 0;
		}

		$fields = [
			'MESSAGE' => $message,
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'FROM_USER_ID' => $this->senderId,
			'SKIP_USER_CHECK' => 'Y',
			'TO_CHAT_ID' => $this->collab->getChatId(),
			'PARAMS' => [
				'NOTIFY' => 'N',
			],
		];

		return (int)CIMChat::addMessage($fields);
	}

	protected function runAction(ActionType $action, array $parameters = []): string
	{
		switch ($action)
		{
			case ActionType::InviteGuest:
				$recipientIds = $parameters['recipients'] ?? [];
				return $this->getInviteGuestMessage($recipientIds, $parameters);

			case ActionType::InviteUser:
				$recipientIds = $parameters['recipients'] ?? [];
				return $this->getInviteUserMessage($recipientIds, $parameters);

			case ActionType::AddUser:
				$recipientIds = $parameters['recipients'] ?? [];
				return $this->getAddUserMessage($recipientIds, $parameters);

			case ActionType::AddGuest:
				$recipientIds = $parameters['recipients'] ?? [];
				return $this->getAddGuestMessage($recipientIds, $parameters);

			case ActionType::ExcludeUser:
				$recipientIds = $parameters['recipients'] ?? [];
				return $this->getExcludeUserMessage($recipientIds);

			case ActionType::AcceptUser:
				$this->sendAcceptUserAnalytics();
				return $this->getAcceptUserMessage();

			case ActionType::CreateCollab:
				return $this->getCollabCreateMessage();

			case ActionType::JoinUser:
				return $this->getJoinUserMessage();

			case ActionType::LeaveUser:
				return $this->getLeaveUserMessage();

			case ActionType::CopyLink:
				CollabAnalytics::getInstance()->onCopyLink($this->senderId, $this->collab->getId());
				return $this->getCopyLinkMessage();

			case ActionType::RegenerateLink:
				return $this->getRegenerateLinkMessage();
		}

		return '';
	}

	private function sendAcceptUserAnalytics()
	{
		$res = \Bitrix\Intranet\Internals\InvitationTable::getList([
			'filter' => [
				'USER_ID' => $this->senderId,
			],
			'select' => ['ID', 'INVITATION_TYPE', 'INITIALIZED'],
			'limit' => 1
		]);
		$invitationFields = $res->fetch();

		if ($invitationFields && $invitationFields['INITIALIZED'] === 'Y')
		{
			return;
		}
		else if ($invitationFields && $invitationFields['INITIALIZED'] === 'N')
		{
			\Bitrix\Intranet\Internals\InvitationTable::update($invitationFields['ID'], [
				'INITIALIZED' => 'Y'
			]);
		}
		else
		{
			$invitationFields = [];
		}

		(new \Bitrix\Main\Event('intranet', 'onUserFirstInitialization', [
			'invitationFields' => $invitationFields,
			'userId' => $this->senderId
		]))->send();

	}

	protected function getCopyLinkMessage(): string
	{
		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_COPY_LINK' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId),
			],
		);
	}

	protected function getRegenerateLinkMessage(): string
	{
		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_REGENERATE_LINK' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId),
			],
		);
	}

	protected function getLeaveUserMessage(): string
	{
		$this->chat->deleteUser($this->collab->getChatId(), $this->senderId, false, true);

		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_USER_LEAVE' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId),
			],
		);
	}

	protected function getJoinUserMessage(): string
	{
		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_USER_JOIN' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId),
			],
		);
	}

	protected function getCollabCreateMessage(): string
	{
		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_COLLAB_CREATE'  . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId),
			],
		);
	}

	protected function getAcceptUserMessage(): string
	{
		$this->chat->addUser(
			$this->collab->getChatId(),
			$this->senderId,
			$this->collab->getOptionValue(ShowHistoryOption::DB_NAME) !== 'Y',
			true,
			true
		);

		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_USER_ACCEPT' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId),
			],
		);
	}

	protected function getInviteUserMessage(array $recipientIds, array $parameters = []): string
	{
		$parameters['skipChat'] = $parameters['skipChat'] ?? true;

		return $this->getAddMessageByType('INVITE_USER', $recipientIds, $parameters);
	}

	protected function getInviteGuestMessage(array $recipientIds, array $parameters = []): string
	{
		return $this->getAddMessageByType('INVITE', $recipientIds, $parameters);
	}

	protected function getAddGuestMessage(array $recipientIds, array $parameters = []): string
	{
		return $this->getAddMessageByType('ADD_GUEST', $recipientIds, $parameters);
	}

	protected function getAddUserMessage(array $recipientIds, array $parameters = []): string
	{
		return $this->getAddMessageByType('ADD', $recipientIds, $parameters);
	}

	protected function getExcludeUserMessage(array $recipientIds): string
	{
		if (empty($recipientIds))
		{
			return '';
		}

		$recipientNames = [];
		foreach ($recipientIds as $recipientId)
		{
			$recipientNames[] = $this->getName($recipientId);
			$this->chat->deleteUser($this->collab->getChatId(), $recipientId, false, true);
		}

		$userNames = implode(', ', $recipientNames);
		$senderName = $this->getName($this->senderId);

		return (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_USER_EXCLUDE' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $senderName,
				'#RECIPIENT#' => $userNames,
			],
		);
	}

	protected function getAddMessageByType(string $type, array $recipientIds, array $parameters = []): string
	{
		if (empty($recipientIds))
		{
			return '';
		}

		$skipChat = $parameters['skipChat'] ?? false;

		if (!$skipChat)
		{
			$this->chat->addUser(
				$this->collab->getChatId(),
				$recipientIds,
				$this->collab->getOptionValue(ShowHistoryOption::DB_NAME) !== 'Y',
				true,
				true
			);
		}

		$recipientNames = [];
		foreach ($recipientIds as $recipientId)
		{
			if ($recipientId === $this->senderId)
			{
				$this->sendActionMessage(ActionType::AcceptUser);

				continue;
			}

			$recipientNames[] = $this->getName($recipientId);
		}

		if (empty($recipientNames))
		{
			return '';
		}

		$userNames = implode(', ', $recipientNames);
		$senderName = $this->getName($this->senderId);

		$count = count($recipientNames);
		$key = 'SOCIALNETWORK_COLLAB_CHAT_USER_' . $type . $this->getGenderSuffix($this->senderId);
		$key .= $count > 1 ? '_MANY' : '';

		return (string)Loc::getMessage(
			$key,
			[
				'#SENDER_NAME#' => $senderName,
				'#RECIPIENT#' => $userNames,
			],
		);
	}

	protected function getName(int $userId): string
	{
		if ($this->senderId === $userId)
		{
			return "[USER={$userId}][/USER]";
		}

		foreach ($this->collab->getSiteIds() as $siteId)
		{
			if (CAllSocNetUser::CanProfileView($this->senderId, $userId, $siteId))
			{
				return "[USER={$userId}][/USER]";
			}
		}

		$names = $this->container->inviteService()->getFormattedInvitationNameByIds([$userId]);

		return $names[$userId] ?? '';
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

	protected function init(): void
	{
		$this->chat = new CIMChat(0);
		$this->container = \Bitrix\Socialnetwork\Collab\Integration\Intranet\ServiceContainer::getInstance();
	}
}