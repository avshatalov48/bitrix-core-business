<?php

namespace Bitrix\Im\Call\Integration;

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\CallUser;
use Bitrix\Im\Common;
use Bitrix\Im\Dialog;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;

class Chat extends AbstractEntity
{
	protected $chatId;
	public $chatFields;
	protected $chatUsers = [];

	const MUTE_MESSAGE = true;

	public function __construct(Call $call, $entityId)
	{
		parent::__construct($call, $entityId);

		if(Common::isChatId($entityId) || (int)$entityId > 0)
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($entityId, $this->initiatorId);
		}
		else
		{
			throw new ArgumentException("Invalid chat id {$entityId}");
		}

		$result = \CIMChat::GetChatData([
			'ID' => $chatId,
			'USER_ID' => $this->initiatorId
		]);

		if ($result['chat'][$chatId])
		{
			$this->chatFields = $result['chat'][$chatId];
		}
		if (is_array($result['userInChat'][$chatId]))
		{
			$users = $result['userInChat'][$chatId];
			$activeRealUsers = UserTable::getList([
				'select' => ['ID'],
				'filter' => [
					'ID' => $users,
					'=ACTIVE' => 'Y',
					[
						'LOGIC' => 'OR',
						'=IS_REAL_USER' => 'Y',
						'=EXTERNAL_AUTH_ID' => \Bitrix\Im\Call\Auth::AUTH_TYPE,
					]

				]
			])->fetchAll();
			$this->chatUsers = array_column($activeRealUsers, 'ID');
		}
		$this->chatId = $chatId;
	}

	/**
	 * Returns associated entity type.
	 *
	 * @return string
	 */
	public function getEntityType()
	{
		return EntityType::CHAT;
	}

	public function getEntityId($currentUserId = 0)
	{
		if($this->chatFields['message_type'] != IM_MESSAGE_PRIVATE || $currentUserId == 0)
		{
			return $this->entityId;
		}
		else
		{
			return $this->call->getInitiatorId() == $currentUserId ? $this->entityId : $this->call->getInitiatorId();
		}
	}

	public function getChatId()
	{
		return $this->chatId;
	}

	/**
	 * Returns list of users in the chat
	 *
	 * @return array
	 */
	public function getUsers()
	{
		return $this->chatUsers;
	}

	/**
	 * Returns true is user has access to the associated chat and false otherwise.
	 *
	 * @param int $userId
	 * @return bool
	 */
	public function checkAccess(int $userId): bool
	{
		if (Common::isChatId($this->entityId))
		{
			return Dialog::hasAccess($this->entityId, $userId);
		}

		// one-to-one dialog
		return ($userId === (int)$this->entityId || $userId === (int)$this->initiatorId);
	}

	/**
	 * Returns true is user can call users in the associated chat and false otherwise.
	 *
	 * @param int $userId
	 * @return bool
 	*/
	public function canStartCall(int $userId): bool
	{
		if (Common::isChatId($this->entityId))
		{
			return Dialog::hasAccess($this->entityId, $userId);
		}

		if (
			\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_CALL) == \CIMSettings::PRIVACY_RESULT_CONTACT
			&& \CModule::IncludeModule('socialnetwork')
			&& \CSocNetUser::IsFriendsAllowed()
			&& !\CSocNetUserRelations::IsFriends($this->entityId, $userId)
		)
		{
			return false;
		}

		if (
			\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_CALL, $this->entityId) === \CIMSettings::PRIVACY_RESULT_CONTACT
			&& \CModule::IncludeModule('socialnetwork')
			&& \CSocNetUser::IsFriendsAllowed()
			&& !\CSocNetUserRelations::IsFriends($this->entityId, $userId)
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns associated entity name.
	 *
	 * @param int $currentUserId Id of the user.
	 * @return string|false
	 */
	public function getName($currentUserId)
	{
		if(!$this->chatFields)
		{
			return false;
		}

		if($this->chatFields['message_type'] === IM_MESSAGE_PRIVATE && count($this->chatUsers) === 2)
		{
			return \Bitrix\Im\User::getInstance($this->getEntityId($currentUserId))->getFullName();
		}
		if($this->chatFields['message_type'] !== IM_MESSAGE_PRIVATE)
		{
			return $this->chatFields['name'];
		}

		return false;
	}

	public function getAvatar($currentUserId)
	{
		if(!$this->chatFields)
		{
			return false;
		}

		if($this->chatFields['message_type'] === IM_MESSAGE_PRIVATE && count($this->chatUsers) === 2)
		{
			return \Bitrix\Im\User::getInstance($this->getEntityId($currentUserId))->getAvatarHr();
		}
		if($this->chatFields['message_type'] !== IM_MESSAGE_PRIVATE)
		{
			return $this->chatFields['avatar'];
		}

		return false;
	}

	public function getAvatarColor($currentUserId)
	{
		if(!$this->chatFields)
		{
			return false;
		}

		if($this->chatFields['message_type'] === IM_MESSAGE_PRIVATE && count($this->chatUsers) === 2)
		{
			return \Bitrix\Im\User::getInstance($this->getEntityId($currentUserId))->getColor();
		}
		if($this->chatFields['message_type'] !== IM_MESSAGE_PRIVATE)
		{
			return $this->chatFields['color'];
		}

		return false;
	}

	public function isPrivateChat(): bool
	{
		return $this->chatFields && $this->chatFields['message_type'] === IM_MESSAGE_PRIVATE;
	}

	public function onUserAdd($userId)
	{
		if (!$this->canExtendChat())
		{
			return false;
		}
		if($this->chatFields['message_type'] == IM_MESSAGE_PRIVATE)
		{
			$chat = new \CIMChat();

			$users = $this->chatUsers;
			$users[] = $userId;

			$chatId = $chat->add(['USERS' => $users]);
			if (!$chatId)
			{
				return false;
			}

			if($this->call)
			{
				$this->call->setAssociatedEntity(static::getEntityType(), 'chat'.$chatId);
			}
		}
		else
		{
			$chat = new \CIMChat();
			$chatId = \Bitrix\Im\Dialog::getChatId($this->getEntityId());
			$result = $chat->addUser($chatId, $userId);
		}

		return true;
	}

	public function onStateChange($state, $prevState)
	{
		$initiatorId = $this->call->getInitiatorId();
		$initiator = \Bitrix\Im\User::getInstance($initiatorId);
		if($state === Call::STATE_INVITING && $prevState === Call::STATE_NEW)
		{
			$this->sendMessageDeferred(Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_STARTED", [
				"#ID#" => '[B]'.$this->call->getId().'[/B]'
			]), self::MUTE_MESSAGE);
		}
		else if($state === Call::STATE_FINISHED)
		{
			$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_FINISHED");
			$mute = true;

			$userIds = array_values(array_filter($this->call->getUsers(), function($userId) use ($initiatorId)
			{
				return $userId != $initiatorId;
			}));

			if(count($userIds) == 1)
			{
				$otherUser = \Bitrix\Im\User::getInstance($userIds[0]);
				$otherUserState = $this->call->getUser($userIds[0]) ? $this->call->getUser($userIds[0])->getState() : '';
				if ($otherUserState == CallUser::STATE_DECLINED)
				{
					$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_USER_DECLINED_" . $otherUser->getGender(), [
						'#NAME#' => $otherUser->getFullName(false)
					]);
				}
				else if ($otherUserState == CallUser::STATE_BUSY)
				{
					$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_USER_BUSY_" . $otherUser->getGender(), [
						'#NAME#' => $otherUser->getFullName(false)
					]);
					$mute = false;
				}
				else if ($otherUserState == CallUser::STATE_UNAVAILABLE || $otherUserState == CallUser::STATE_CALLING)
				{
					$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_MISSED_" . $initiator->getGender(), [
						'#NAME#' => $initiator->getFullName(false)
					]);
					$mute = false;
				}
			}

			$this->sendMessageDeferred($message, $mute);
		}
	}

	public function sendMessageDeferred($message, $muted = false)
	{
		Application::getInstance()->addBackgroundJob([$this, 'sendMessage'], [$message, $muted]);
	}

	public function isBroadcast()
	{
		return $this->chatFields['entity_type'] === \Bitrix\Im\Alias::ENTITY_TYPE_VIDEOCONF
			&& $this->chatFields['entity_data_1'] === 'BROADCAST'
		;
	}

	public function sendMessage($message, $muted = false)
	{
		\CIMMessenger::add([
			'DIALOG_ID' => $this->entityId,
			'FROM_USER_ID' => $this->getCall()->getInitiatorId(),
			'MESSAGE' => $message,
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'PARAMS' => [
				'NOTIFY' => $muted? 'N': 'Y',
			]
		]);
	}

	public function toArray($currentUserId = 0)
	{
		if($currentUserId == 0)
		{
			$currentUserId = $this->initiatorId;
		}

		return [
			'type' => $this->getEntityType(),
			'id' => $this->getEntityId($currentUserId),
			'name' => $this->getName($currentUserId),
			'avatar' => $this->getAvatar($currentUserId),
			'avatarColor' => $this->getAvatarColor($currentUserId),
			'advanced' => [
				'chatType' => $this->chatFields['type'],
				'entityType' => $this->chatFields['entity_type'],
				'entityId' => $this->chatFields['entity_id'],
				'entityData1' => $this->chatFields['entity_data_1'],
				'entityData2' => $this->chatFields['entity_data_2'],
				'entityData3' => $this->chatFields['entity_data_3']
			]
		];
	}

	public function canExtendChat(): bool
	{
		if (!$this->chatFields)
		{
			return false;
		}
		if ($this->chatFields['message_type'] === IM_MESSAGE_PRIVATE)
		{
			return true;
		}
		$entityType = $this->chatFields['entity_type'];
		$options = \CIMChat::GetChatOptions();
		return (bool)($options[$entityType]['EXTEND'] ?? true);
	}
}