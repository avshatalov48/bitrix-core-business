<?php

namespace Bitrix\Im\Call\Integration;

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\CallUser;
use Bitrix\Im\Common;
use Bitrix\Im\Dialog;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
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

		$params = ['ID' => $chatId];

		global $USER;
		if ($USER instanceof \CUser)
		{
			$params['USER_ID'] = (int)$USER->getId();
		}

		$result = \CIMChat::GetChatData($params);

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
			!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
			&& \Bitrix\Main\Loader::includeModule('socialnetwork')
		)
		{
			if (
				\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_CALL) == \CIMSettings::PRIVACY_RESULT_CONTACT
				&& \CSocNetUser::IsFriendsAllowed()
				&& !\CSocNetUserRelations::IsFriends($this->entityId, $userId)
			)
			{
				return false;
			}

			if (
				\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_CALL, $this->entityId) === \CIMSettings::PRIVACY_RESULT_CONTACT
				&& \CSocNetUser::IsFriendsAllowed()
				&& !\CSocNetUserRelations::IsFriends($this->entityId, $userId)
			)
			{
				return false;
			}
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

	public function onUserAdd($userId): bool
	{
		if (!$this->canExtendChat())
		{
			return false;
		}
		if ($this->chatFields['message_type'] == IM_MESSAGE_PRIVATE)
		{
			$chat = new \CIMChat();

			$users = $this->chatUsers;
			$users[] = $userId;

			$chatId = $chat->add(['USERS' => $users]);
			if (!$chatId)
			{
				return false;
			}

			if ($this->call)
			{
				$this->call->setAssociatedEntity(static::getEntityType(), 'chat'.$chatId);
				// todo: remove when the calls are supported in the mobile
				if ($this->call->getAssociatedEntity())
				{
					$this->call->getAssociatedEntity()->onCallCreate();
				}
			}
		}
		else
		{
			$chat = new \CIMChat();
			$chatId = \Bitrix\Im\Dialog::getChatId($this->getEntityId());
			$chat->addUser($chatId, $userId);
		}

		return true;
	}

	public function onExistingUsersInvite($userIds): bool
	{
		if (isset($this->chatFields['message_type']) && $this->chatFields['message_type'] === IM_MESSAGE_PRIVATE)
		{
			return true;
		}

		if (!$this->canExtendChat())
		{
			return false;
		}

		$chat = new \CIMChat();
		$chatId = \Bitrix\Im\Dialog::getChatId($this->getEntityId());

		return $chat->addUser($chatId, $userIds);
	}

	public function onStateChange($state, $prevState)
	{
		$initiatorId = $this->call->getInitiatorId();
		$initiator = \Bitrix\Im\User::getInstance($initiatorId);
		if ($state === Call::STATE_INVITING && $prevState === Call::STATE_NEW)
		{
			// todo: return the call method when the calls are supported in the mobile
			//$this->sendMessagesCallStart();
		}
		elseif($state === Call::STATE_FINISHED)
		{
			$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_FINISHED_V2", [
				'#CALL_DURATION#' => $this->getCallDuration(),
			]);
			$mute = true;
			$skipCounterInc = true;

			$userIds = array_values(array_filter($this->call->getUsers(), function($userId) use ($initiatorId)
			{
				return $userId != $initiatorId;
			}));

			$componentParams = [
				'MESSAGE_TYPE' => 'FINISH',
				'CALL_ID' => $this->call->getId(),
				'INITIATOR_ID' => $this->call->getActionUserId(),
			];

			if(count($userIds) == 1)
			{
				$otherUser = \Bitrix\Im\User::getInstance($userIds[0]);
				$otherUserState = $this->call->getUser($userIds[0]) ? $this->call->getUser($userIds[0])->getState() : '';

				if ($otherUserState == CallUser::STATE_DECLINED)
				{
					$componentParams['MESSAGE_TYPE'] = 'DECLINED';
					$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_USER_DECLINED_V2_" . $otherUser->getGender(), [
						'#NAME#' => $otherUser->getFullName(false)
					]);
				}
				else if ($otherUserState == CallUser::STATE_BUSY)
				{
					$componentParams['MESSAGE_TYPE'] = 'BUSY';
					$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_USER_BUSY_" . $otherUser->getGender(), [
						'#NAME#' => $otherUser->getFullName(false)
					]);
					$mute = false;
					$skipCounterInc = false;
				}
				else if ($otherUserState == CallUser::STATE_UNAVAILABLE || $otherUserState == CallUser::STATE_CALLING)
				{
					$componentParams['MESSAGE_TYPE'] = 'MISSED';
					$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_MISSED", [
						'#NAME#' => $otherUser->getFullName(false)
					]);
					$mute = false;
					$skipCounterInc = false;
				}
			}

			$componentParams['MESSAGE_TEXT'] = $message;
			$this->sendMessageDeferred($message, $mute, $skipCounterInc, $componentParams);
		}
	}

	public function onCallCreate(): bool
	{
		$this->sendMessagesCallStart();

		return true;
	}

	public function sendMessagesCallStart(): void
	{
		$message = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_STARTED_V2", [
			"#ID#" => $this->call->getId()
		]);
		$componentParams = [
			'MESSAGE_TYPE' => 'START', /** @see \Bitrix\Call\NotifyService::MESSAGE_TYPE_START */
			'CALL_ID' => $this->call->getId(),
			'MESSAGE_TEXT' => $message,
		];

		$this->sendMessageDeferred($message, self::MUTE_MESSAGE, true, $componentParams);
	}

	public function sendMessageDeferred($message, $muted = false, $skipCounterInc = false, $componentParams = [])
	{
		Application::getInstance()->addBackgroundJob([$this, 'sendMessage'], [$message, $muted, $skipCounterInc, $componentParams]);
	}

	public function isBroadcast()
	{
		return $this->chatFields['entity_type'] === \Bitrix\Im\Alias::ENTITY_TYPE_VIDEOCONF
			&& $this->chatFields['entity_data_1'] === 'BROADCAST'
		;
	}

	public function sendMessage($message, $muted = false, $skipCounterInc = false, $componentParams = [])
	{
		$initiator = $this->getCall()->getInitiatorId();
		if (isset($componentParams['INITIATOR_ID']))
		{
			$initiator = $componentParams['INITIATOR_ID'];
		}

		$chatId = $this->call->getChatId();
		if (!empty($this->call->getParentId()))
		{
			//todo: Remove it
			$chatId = \Bitrix\Im\Dialog::getChatId($this->getEntityId());
		}

		\CIMMessenger::add([
			'TO_CHAT_ID' => $chatId,
			'MESSAGE_TYPE' => $this->isPrivateChat() ? IM_MESSAGE_PRIVATE : IM_MESSAGE_CHAT,
			'FROM_USER_ID' => $initiator,
			'MESSAGE' => $message,
			'PUSH' => 'N',
			'SKIP_COUNTER_INCREMENTS' => $skipCounterInc ? 'Y' : 'N',
			'PARAMS' => [
				'NOTIFY' => $muted ? 'N': 'Y',
				'COMPONENT_ID' => 'CallMessage', /** @see \Bitrix\Call\NotifyService::MESSAGE_COMPONENT_ID */
				'COMPONENT_PARAMS' => $componentParams,
			]
		]);
	}

	public function toArray($initiatorId = 0)
	{
		if($initiatorId == 0)
		{
			$initiatorId = $this->initiatorId;
		}

		return [
			'type' => $this->getEntityType(),
			'id' => (string)$this->getEntityId($initiatorId), //todo: Cast to string for compatibility with immobile. Remove it in a while
			'name' => $this->getName($initiatorId),
			'avatar' => $this->getAvatar($initiatorId),
			'avatarColor' => $this->getAvatarColor($initiatorId),
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

	public function getCallDuration(): string
	{
		$interval = $this->call->getStartDate()->getDiff($this->call->getEndDate());

		[$hours, $minutes, $seconds] = explode(' ', $interval->format('%H %I %S'));
		$result = [];

		if ((int) $hours > 0)
		{
			$result[] = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_DURATION_HOURS", [
				"#HOURS#" => (int) $hours
			]);
		}

		if ((int) $minutes > 0)
		{
			$result[] = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_DURATION_MINUTES", [
				"#MINUTES#" => (int) $minutes
			]);
		}

		if ((int) $seconds > 0 && !(int) $hours > 0)
		{
			$result[] = Loc::getMessage("IM_CALL_INTEGRATION_CHAT_CALL_DURATION_SECONDS", [
				"#SECONDS#" => (int) $seconds
			]);
		}

		return implode(" ", $result);
	}
}