<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Common;
use Bitrix\Im\Dialog;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Chat\GeneralChat;
use Bitrix\Im\V2\Chat\OpenChat;
use Bitrix\Im\V2\Chat\OpenLineChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Controller\Chat\Pin;
use Bitrix\Im\V2\Controller\Filter\ChatTypeFilter;
use Bitrix\Im\V2\Controller\Filter\CheckAvatarId;
use Bitrix\Im\V2\Controller\Filter\CheckAvatarIdInFields;
use Bitrix\Im\V2\Controller\Filter\CheckChatAccess;
use Bitrix\Im\V2\Controller\Filter\CheckChatAddParams;
use Bitrix\Im\V2\Controller\Filter\CheckChatCanPost;
use Bitrix\Im\V2\Controller\Filter\CheckChatManageUpdate;
use Bitrix\Im\V2\Controller\Filter\CheckChatOwner;
use Bitrix\Im\V2\Controller\Filter\CheckChatUpdate;
use Bitrix\Im\V2\Controller\Filter\CheckDisappearingDuration;
use Bitrix\Im\V2\Controller\Filter\ExtendPullWatchPrefilter;
use Bitrix\Im\V2\Controller\Filter\UpdateStatus;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Link\Pin\PinCollection;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\MessageService;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Intranet\ActionFilter\IntranetUser;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;

class Chat extends BaseController
{
	public function configureActions()
	{
		return [
			'add' => [
				'+prefilters' => [
					new IntranetUser(),
					new CheckChatAddParams(),
					new CheckAvatarIdInFields(),
				],
			],
			'update' => [
				'+prefilters' => [
					new IntranetUser(),
					new CheckChatAddParams(),
					new CheckAvatarIdInFields(),
				],
			],
			'setOwner' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
					new CheckChatOwner(),
				]
			],
			'setTitle' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setDescription' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setColor' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setAvatarId' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
					new CheckAvatarId(),
				]
			],
			'setAvatar' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'addUsers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'deleteUser' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setManagers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setManageUsersAdd' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
			'setManageUsersDelete' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
			'setManageUI' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
			'setManageSettings' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
			'setDisappearingDuration' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
					new CheckDisappearingDuration(),
				]
			],
			'setCanPost' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatCanPost(),
					new CheckChatUpdate(),
				]
			],
			'load' => [
				'+prefilters' => [
					new ExtendPullWatchPrefilter(),
				],
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
			'loadInContext' => [
				'+prefilters' => [
					new ExtendPullWatchPrefilter(),
				],
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
			'join' => [
				'+prefilters' => [
					new ChatTypeFilter([OpenChat::class, OpenLineChat::class, GeneralChat::class]),
				],
			],
			'extendPullWatch' => [
				'+prefilters' => [
					new ChatTypeFilter([OpenChat::class, OpenLineChat::class]),
				],
			],
			'read' => [
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
			'readAll' => [
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
		];
	}

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Im\V2\Chat::class,
			'chat',
			function ($className, $id) {
				return \Bitrix\Im\V2\Chat::getInstance((int)$id);
			}
		);
	}

	/**
	 * @restMethod im.v2.Chat.shallowLoad
	 */
	public function shallowLoadAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		return $this->toRestFormat($chat);
	}

	/**
	 * @restMethod im.v2.Chat.load
	 */
	public function loadAction(
		\Bitrix\Im\V2\Chat $chat,
		CurrentUser $user,
		int $messageLimit = Chat\Message::DEFAULT_LIMIT,
		int $pinLimit = Pin::DEFAULT_LIMIT
	): ?array
	{
		$result = $this->load($chat, $user, $messageLimit, $pinLimit);

		if (!empty($this->getErrors()))
		{
			return null;
		}

		return $result;
	}

	/**
	 * @restMethod im.v2.Chat.loadInContext
	 */
	public function loadInContextAction(
		Message $message,
		CurrentUser $user,
		int $messageLimit = Chat\Message::DEFAULT_LIMIT,
		int $pinLimit = Pin::DEFAULT_LIMIT
	): ?array
	{
		$result = $this->load($message->getChat(), $user, $messageLimit, $pinLimit, $message);

		if (!empty($this->getErrors()))
		{
			return null;
		}

		return $result;
	}

	/**
	 * @restMethod im.v2.Chat.get
	 */
	public function getAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		return (new RestAdapter($chat))->toRestFormat(['POPUP_DATA_EXCLUDE' => [UserPopupItem::class]]);
	}

	/**
	 * @restMethod im.v2.Chat.listShared
	 */
	public function listSharedAction(array $filter, int $limit = self::DEFAULT_LIMIT, int $offset = 0): ?array
	{
		if (!isset($filter['userId']))
		{
			$this->addError(new ChatError(ChatError::USER_ID_EMPTY_ERROR));

			return null;
		}
		$userId = (int)$filter['userId'];
		$chats = \Bitrix\Im\V2\Chat::getSharedChatsWithUser($userId, $this->getLimit($limit), $offset);
		\Bitrix\Im\V2\Chat::fillRole($chats);
		$result = ['chats' => []];

		foreach ($chats as $chat)
		{
			$result['chats'][] = $chat->toRestFormat(['CHAT_SHORT_FORMAT' => true, 'CHAT_WITH_DATE_MESSAGE' => true]);
		}

		return $result;
	}

	/**
	 * @restMethod im.v2.Chat.getDialogId
	 * @internal
	 */
	public function getDialogIdAction(string $externalId): ?array
	{
		$chatId = Dialog::getChatId($externalId);

		if ($chatId === false || $chatId === 0)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		return ['dialogId' => "chat{$chatId}"];
	}

	/**
	 * @restMethod im.v2.Chat.read
	 */
	public function readAction(\Bitrix\Im\V2\Chat $chat, string $onlyRecent = 'N'): ?array
	{
		$result = $chat->read($this->convertCharToBool($onlyRecent));

		return $this->convertKeysToCamelCase($result->getResult());
	}

	/**
	 * @restMethod im.v2.Chat.readAll
	 */
	public function readAllAction(CurrentUser $user): ?array
	{
		\Bitrix\Im\V2\Chat::readAllChats((int)$user->getId());

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.unread
	 */
	public function unreadAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		Recent::unread($chat->getDialogId(), true);

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.startRecordVoice
	 */
	public function startRecordVoiceAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		$chat->startRecordVoice();

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.add
	 */
	public function addAction(array $fields): ?array
	{
		$fields['type'] = $this->getValidatedType($fields['type'] ?? null);

		if (
			!isset($fields['entityType'])
			|| $fields['entityType'] !== 'VIDEOCONF'
			|| !isset($fields['conferencePassword'])
		)
		{
			unset($fields['conferencePassword']);
		}

		$data = self::recursiveWhiteList($fields, \Bitrix\Im\V2\Chat::AVAILABLE_PARAMS);
		$result = ChatFactory::getInstance()->addChat($data);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return $this->convertKeysToCamelCase($result->getResult());
	}

	//region Update Chat
	/**
	 * @restMethod im.v2.Chat.update
	 */
	public function updateAction(\Bitrix\Im\V2\Chat $chat, array $fields)
	{
		$currentUser = $this->getCurrentUser();
		$userId = isset($currentUser) ? $currentUser->getId() : null;
		$relation = $chat->getSelfRelation();

		$changeSettings = false;
		$changeUI = false;
		if (!($chat instanceof PrivateChat))
		{
			if ($chat->getAuthorId() === (int)$userId)
			{
				$changeSettings = true;
				$changeUI = true;
			}
			elseif ($relation->getManager())
			{
				if ($chat->getManageSettings() === \Bitrix\Im\V2\Chat::MANAGE_RIGHTS_MANAGERS)
				{
					$changeSettings = true;
				}
				if ($chat->getManageUI() === \Bitrix\Im\V2\Chat::MANAGE_RIGHTS_MANAGERS)
				{
					$changeUI = true;
				}
			}
			else
			{
				if ($chat->getManageUI() === \Bitrix\Im\V2\Chat::MANAGE_RIGHTS_MEMBER)
				{
					$changeUI = true;
				}
			}
		}

		if ($changeSettings)
		{
			if (isset($fields['entityType']))
			{
				$chat->setEntityType($fields['entityType']);
			}
			if (isset($fields['entityId']))
			{
				$chat->setEntityId($fields['entityId']);
			}
			if (isset($fields['entityData1']))
			{
				$chat->setEntityData1($fields['entityData1']);
			}
			if (isset($fields['entityData2']))
			{
				$chat->setEntityData2($fields['entityData2']);
			}
			if (isset($fields['entityData3']))
			{
				$chat->setEntityData3($fields['entityData3']);
			}
			if (isset($fields['ownerId']))
			{
				$chat->setAuthorId($fields['ownerId']);
			}
			if (isset($fields['manageUsersAdd']))
			{
				$chat->setManageUsersAdd($fields['manageUsersAdd']);
			}
			if (isset($fields['manageUsersDelete']))
			{
				$chat->setManageUsersDelete($fields['manageUsersDelete']);
			}
			if (isset($fields['manageUI']))
			{
				$chat->setManageUI($fields['manageUI']);
			}
			if (isset($fields['manageSettings']))
			{
				$chat->setManageSettings($fields['manageSettings']);
			}
			if (isset($fields['canPost']))
			{
				$chat->setCanPost($fields['canPost']);
			}
			if (isset($fields['managers']))
			{
				$chat->setManagers($fields['managers']);
			}
		}

		if ($changeUI)
		{
			if (isset($fields['title']))
			{
				$chat->setTitle($fields['title']);
			}
			if (isset($fields['description']))
			{
				$chat->setDescription($fields['description']);
			}
			if (isset($fields['color']))
			{
				$chat->setColor($fields['color']);
			}
			if (isset($fields['avatar']) && $fields['avatar'])
			{
				if (is_numeric((string)$fields['avatar']))
				{
					$this->setAvatarIdAction($chat, $fields['avatar']);
				}
				else
				{
					$this->setAvatarAction($chat, $fields['avatar']);
				}
			}
		}

		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	//region Manage Users
	/**
	 * @restMethod im.v2.Chat.addUsers
	 */
	public function addUsersAction(\Bitrix\Im\V2\Chat $chat, array $userIds, ?string $hideHistory = null): ?array
	{
		$hideHistoryBool = $hideHistory === null ? null : $this->convertCharToBool($hideHistory, true);
		$chat->addUsers($userIds, [], $hideHistoryBool);

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.join
	 */
	public function joinAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		$chat->join();

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.extendPullWatch
	 */
	public function extendPullWatchAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		if ($chat instanceof OpenChat || $chat instanceof OpenLineChat)
		{
			$chat->extendPullWatch();
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.deleteUser
	 */
	public function deleteUserAction(\Bitrix\Im\V2\Chat $chat, int $userId): ?array
	{
		$result = $chat->deleteUser($userId);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return ['result' => true];
	}
	//endregion

	//region Manage UI
	/**
	 * @restMethod im.v2.Chat.setTitle
	 */
	public function setTitleAction(\Bitrix\Im\V2\Chat $chat, string $title)
	{
		$chat->setTitle($title);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setDescription
	 */
	public function setDescriptionAction(\Bitrix\Im\V2\Chat $chat, string $description)
	{
		$chat->setDescription($description);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setColor
	 */
	public function setColorAction(\Bitrix\Im\V2\Chat $chat, string $color)
	{
		$result = $chat->validateColor();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		$chat->setColor($color);
		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setAvatarId
	 */
	public function setAvatarIdAction(\Bitrix\Im\V2\Chat $chat, int $avatarId)
	{
		$result = $chat->updateAvatarId($avatarId);

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.setAvatar
	 */
	public function setAvatarAction(\Bitrix\Im\V2\Chat $chat, string $avatarBase64)
	{
		$result = $chat->updateAvatar($avatarBase64);

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return ['avatarId' => $result->getResult()];
	}
	//endregion

	//region Manage Settings
	/**
	 * @restMethod im.v2.Chat.setDisappearingDuration
	 */
	public function setDisappearingDurationAction(\Bitrix\Im\V2\Chat $chat, int $hours)
	{
		$result = Message\Delete\DisappearService::disappearChat($chat, $hours);
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setOwner
	 */
	public function setOwnerAction(\Bitrix\Im\V2\Chat $chat, int $ownerId)
	{
		$chat->setAuthorId($ownerId);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManagers
	 */
	public function setManagersAction(\Bitrix\Im\V2\Chat $chat, array $userIds)
	{
		$chat->setManagers($userIds);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManageUsersAdd
	 */
	public function setManageUsersAddAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageUsersAdd($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManageUsersDelete
	 */
	public function setManageUsersDeleteAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageUsersDelete($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManageUI
	 */
	public function setManageUIAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageUI($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManageSettings
	 */
	public function setManageSettingsAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageSettings($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();

	}

	/**
	 * @restMethod im.v2.Chat.setCanPost
	 */
	public function setCanPostAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setCanPost($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.pin
	 */
	public function pinAction(\Bitrix\Im\V2\Chat $chat, CurrentUser $user): ?array
	{
		Recent::pin($chat->getDialogId(), true, $user->getId());

		if (Recent::isLimitError())
		{
			$this->addError(new ChatError(ChatError::MAX_PINNED_CHATS_ERROR));

			return null;
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.unpin
	 */
	public function unpinAction(\Bitrix\Im\V2\Chat $chat, CurrentUser $user): ?array
	{
		Recent::pin($chat->getDialogId(), false, $user->getId());

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.sortPin
	 */
	public function sortPinAction(\Bitrix\Im\V2\Chat $chat, int $newPosition, CurrentUser $user): ?array
	{
		if ($newPosition <= 0 || $newPosition > Recent::getPinLimit())
		{
			$this->addError(new ChatError(ChatError::INVALID_PIN_POSITION));

			return null;
		}

		Recent::sortPin($chat, $newPosition, $user->getId());

		return ['result' => true];
	}

	private function load(\Bitrix\Im\V2\Chat $chat, CurrentUser $user, int $messageLimit, int $pinLimit, ?Message $targetMessage = null): array
	{
		$messageLimit = $this->getLimit($messageLimit);
		$pinLimit = $this->getLimit($pinLimit);
		$messageService = new MessageService($targetMessage ?? $chat->getLoadContextMessage());
		$messages = $messageService->getMessageContext($messageLimit, Message::REST_FIELDS)->getResult();
		$pins = PinCollection::find(
			['CHAT_ID' => $chat->getChatId(), 'START_ID' => $chat->getStartId() ?: null],
			['ID' => 'DESC'],
			$pinLimit
		);
		$restAdapter = new RestAdapter($chat, $messages, $pins);

		$rest = $restAdapter->toRestFormat();

		return $messageService->fillContextPaginationData($rest, $messages, $messageLimit);
	}

	private function getValidatedType(?string $type): string
	{
		if ($type === 'CHANNEL')
		{
			return \Bitrix\Im\V2\Chat::IM_TYPE_CHANNEL;
		}
		if ($type === 'COPILOT')
		{
			return \Bitrix\Im\V2\Chat::IM_TYPE_COPILOT;
		}

		return \Bitrix\Im\V2\Chat::IM_TYPE_CHAT;
	}
	//endregion
	//endregion
}