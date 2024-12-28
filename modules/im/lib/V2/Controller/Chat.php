<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Dialog;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Chat\GeneralChat;
use Bitrix\Im\V2\Chat\GroupChat;
use Bitrix\Im\V2\Chat\OpenChannelChat;
use Bitrix\Im\V2\Chat\OpenChat;
use Bitrix\Im\V2\Chat\OpenLineChat;
use Bitrix\Im\V2\Chat\Param\Params;
use Bitrix\Im\V2\Permission;
use Bitrix\Im\V2\Chat\Update\UpdateFields;
use Bitrix\Im\V2\Controller\Chat\Pin;
use Bitrix\Im\V2\Controller\Filter\ChatTypeFilter;
use Bitrix\Im\V2\Controller\Filter\CheckActionAccess;
use Bitrix\Im\V2\Controller\Filter\CheckChatAccess;
use Bitrix\Im\V2\Controller\Filter\CheckFileAccess;
use Bitrix\Im\V2\Controller\Filter\ExtendPullWatchPrefilter;
use Bitrix\Im\V2\Controller\Filter\UpdateStatus;
use Bitrix\Im\V2\Entity\File\ChatAvatar;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Chat\Update\UpdateService;
use Bitrix\Intranet\ActionFilter\IntranetUser;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\Converter;

class Chat extends BaseController
{
	public function configureActions()
	{
		return [
			'add' => [
				'+prefilters' => [
					new CheckActionAccess(
						Permission\GlobalAction::CreateChat,
						fn (Base $filter) => [
							'TYPE' => $this->getValidatedType($filter->getAction()->getArguments()['fields']['type'] ?? ''),
							'ENTITY_TYPE' => $filter->getAction()->getArguments()['entityType'] ?? null,
						]
					),
					new CheckFileAccess(['fields', 'avatar']),
				],
			],
			'update' => [
				'+prefilters' => [
					new CheckFileAccess(['fields', 'avatar']),
					new CheckActionAccess(Permission\Action::Update),
					new ChatTypeFilter([GroupChat::class]),
				],
			],
			'delete' => [
				'+prefilters' => [
					new CheckActionAccess(Permission\Action::Delete),
				],
			],
			'updateAvatar' => [
				'+prefilters' => [
					new CheckFileAccess(['avatar']),
					new CheckActionAccess(Permission\Action::ChangeAvatar),
				],
			],
			'setOwner' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeOwner),
				]
			],
			'setTitle' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::Rename),
				]
			],
			'setDescription' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeDescription),
				]
			],
			'setColor' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeColor),
				]
			],
			'setAvatarId' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeAvatar),
					new CheckFileAccess(['avatarId']),
				]
			],
			'setAvatar' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeAvatar),
				]
			],
			'addUsers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::Extend),
				]
			],
			'deleteUser' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(
						Permission\Action::Kick,
						fn (Base $filter) => (int)($filter->getAction()->getArguments()['userId'] ?? 0)
					),
				]
			],
			'setManagers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeManagers),
				]
			],
			'addManagers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeManagers),
					new ChatTypeFilter([GroupChat::class]),
				]
			],
			'deleteManagers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeManagers),
					new ChatTypeFilter([GroupChat::class]),
				]
			],
			'setManageUsersAdd' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeRight),
				]
			],
			'setManageUsersDelete' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeRight),
				]
			],
			'setManageUI' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeRight),
				]
			],
			'setManageSettings' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeRight),
				]
			],
			'setDisappearingDuration' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeRight),
				]
			],
			'setManageMessages' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckActionAccess(Permission\Action::ChangeRight),
				]
			],
			'load' => [
				'+prefilters' => [
					new ExtendPullWatchPrefilter(),
				],
			],
			'loadInContext' => [
				'+prefilters' => [
					new ExtendPullWatchPrefilter(),
				],
			],
			'join' => [
				'+prefilters' => [
					new ChatTypeFilter([OpenChat::class, OpenLineChat::class, GeneralChat::class, OpenChannelChat::class, CommentChat::class]),
				],
			],
			'extendPullWatch' => [
				'+prefilters' => [
					new ChatTypeFilter([OpenChat::class, OpenLineChat::class, ChannelChat::class]),
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
		int $messageLimit = Chat\Message::DEFAULT_LIMIT,
		int $pinLimit = Pin::DEFAULT_LIMIT,
		string $ignoreMark = 'N'
	): ?array
	{
		$result = $this->load($chat, $messageLimit, $pinLimit, $this->convertCharToBool($ignoreMark));

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
		int $messageLimit = Chat\Message::DEFAULT_LIMIT,
		int $pinLimit = Pin::DEFAULT_LIMIT
	): ?array
	{
		$result = $this->load($message->getChat(), $messageLimit, $pinLimit, false, $message);

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
		\Bitrix\Im\V2\Chat::fillSelfRelations($chats);
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

		if (isset($fields['copilotMainRole']))
		{
			$fields['chatParams'][] = [
				'paramName' => Params::COPILOT_MAIN_ROLE,
				'paramValue' => $fields['copilotMainRole']
			];
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
	public function updateAction(GroupChat $chat, array $fields)
	{
		$converter = new Converter(Converter::TO_SNAKE | Converter::TO_UPPER | Converter::KEYS);
		$updateService = new UpdateService($chat, UpdateFields::create($converter->process($fields)));

		$result = $updateService->updateChat();
		if (!$result->isSuccess())
		{
			$this->addError($result->getErrors()[0]);

			return null;
		}

		return ['result' => true];
	}

	//region Delete Chat

	/**
	 * @restMethod im.v2.Chat.delete
	 * @throws \Exception
	 */
	public function deleteAction(\Bitrix\Im\V2\Chat $chat)
	{
		$result = $chat->deleteChat();

		if (!$result->isSuccess())
		{
			$this->addError($result->getErrors()[0]);

			return null;
		}

		return ['result' => true];
	}

	//region Manage Users
	/**
	 * @restMethod im.v2.Chat.addUsers
	 */
	public function addUsersAction(\Bitrix\Im\V2\Chat $chat, array $userIds, ?string $hideHistory = null): ?array
	{
		$hideHistoryBool = $hideHistory === null ? null : $this->convertCharToBool($hideHistory, true);
		$chat->addUsers($userIds, new AddUsersConfig(hideHistory: $hideHistoryBool));

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
		if ($chat instanceof OpenChat || $chat instanceof OpenLineChat || $chat instanceof ChannelChat)
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
	 * @restMethod im.v2.Chat.updateAvatar
	 */
	public function updateAvatarAction(\Bitrix\Im\V2\Chat $chat, string $avatar)
	{
		$result = (new ChatAvatar($chat))->update($avatar);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.setAvatarId
	 */
	public function setAvatarIdAction(\Bitrix\Im\V2\Chat $chat, int $avatarId)
	{
		$result = (new ChatAvatar($chat))->update($avatarId);

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
		$result = (new ChatAvatar($chat))->update($avatarBase64);

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
	 * @restMethod im.v2.Chat.addManagers
	 */
	public function addManagersAction(GroupChat $chat, array $userIds): ?array
	{
		$chat->addManagers($userIds);

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.deleteManagers
	 */
	public function deleteManagersAction(GroupChat $chat, array $userIds): ?array
	{
		$chat->deleteManagers($userIds);

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.setManageUsersAdd
	 */
	public function setManageUsersAddAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageUsersAdd(mb_strtoupper($rightsLevel));
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
		$chat->setManageUsersDelete(mb_strtoupper($rightsLevel));
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
		$chat->setManageUI(mb_strtoupper($rightsLevel));
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
		$chat->setManageSettings(mb_strtoupper($rightsLevel));
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();

	}

	/**
	 * @restMethod im.v2.Chat.setManageMessages
	 */
	public function setManageMessagesAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageMessages(mb_strtoupper($rightsLevel));
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

	private function getValidatedType(?string $type): string
	{
		return match ($type)
		{
			'CHANNEL' => \Bitrix\Im\V2\Chat::IM_TYPE_CHANNEL,
			'COPILOT' => \Bitrix\Im\V2\Chat::IM_TYPE_COPILOT,
			'COLLAB' => \Bitrix\Im\V2\Chat::IM_TYPE_COLLAB,
			default => \Bitrix\Im\V2\Chat::IM_TYPE_CHAT,
		};
	}
	//endregion
	//endregion
}
