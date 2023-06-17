<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\User;
use Bitrix\Im\Notify;
use Bitrix\Im\Color;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\Message\Send\MentionService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Bot\BotService;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Localization\Loc;
use CPullWatch;

class GroupChat extends Chat
{
	protected function getDefaultType(): string
	{
		return self::IM_TYPE_CHAT;
	}

	public function setType(string $type): Chat
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Allows to send mention notification.
	 * @return bool
	 */
	public function allowMention(): bool
	{
		return true;
	}

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		$options = [
			'SELECT' => ['ID', 'CHAT_ID', 'USER_ID', 'START_ID'],
			'FILTER' => ['USER_ID' => $userId, 'CHAT_ID' => $this->getChatId()],
			'LIMIT' => 1,
		];

		return $this->getRelations($options)->hasUser($userId, $this->getChatId());
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$paramsResult = $this->prepareParams($params);
		if ($paramsResult->isSuccess())
		{
			$params = $paramsResult->getResult();
		}
		else
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		$chat = new GroupChat($params);
		$chat->setExtranet($chat->checkIsExtranet());
		$chat->save();

		if (!$chat->getChatId())
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		$chat->sendGreetingMessage($this->getContext()->getUserId());

		if ($chat->getUserIds())
		{
			$chat->sendBanner($this->getContext()->getUserId());
		}

		if (!$chat->getUserIds())
		{
			$chat->sendBanner($this->getContext()->getUserId());
		}

		foreach ($chat->getUserIds() as $userId)
		{
			if ($chat->getAuthorId() == $userId)
			{
				$isManager = 'Y';
			}
			else
			{
				$isManager = in_array($userId, $params['MANAGERS']) ? 'Y' : 'N';
			}

			RelationTable::add([
				'CHAT_ID' => $chat->getChatId(),
				'MESSAGE_TYPE' => \IM_MESSAGE_CHAT,
				'USER_ID' => $userId,
				'STATUS' => \IM_STATUS_READ,
				'MANAGER' => $isManager,
			]);

			if (\Bitrix\Im\V2\Entity\User\User::getInstance($userId)->isBot())
			{
				\Bitrix\Im\Bot::changeChatMembers($chat->getChatId(), $userId);
				\Bitrix\Im\Bot::onJoinChat('chat' . $chat->getChatId(), [
					'CHAT_TYPE' => $chat->getType(),
					'MESSAGE_TYPE' => \IM_MESSAGE_CHAT,
					'BOT_ID' => $userId,
					'USER_ID' => $params['USER_ID'],
					'CHAT_AUTHOR_ID' => $chat->getAuthorId(),
					'CHAT_ENTITY_TYPE' => $chat->getEntityType(),
					'CHAT_ENTITY_ID' => $chat->getEntityId(),
					'ACCESS_HISTORY' => true,
				]);
			}
		}

		$chat->sendInviteMessage($this->getContext()->getUserId());
		$chat->sendDescriptionMessage();

		$chat->updateIndex();

		$result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);

		return $result;
	}

	protected function filterParams(array $params): array
	{
		if (isset($params['USER_ID']))
		{
			$params['USER_ID'] = (int)$params['USER_ID'];
		}
		else
		{
			$params['USER_ID'] = $this->getContext()->getUserId();
		}

		if (isset($params['AUTHOR_ID']))
		{
			$params['AUTHOR_ID'] = (int)$params['AUTHOR_ID'];
		}

		if (isset($params['OWNER_ID']))
		{
			$params['AUTHOR_ID'] = (int)$params['OWNER_ID'];
		}

		foreach (['USERS', 'MANAGERS'] as $paramName)
		{
			if (!isset($params[$paramName]) || !is_array($params[$paramName]))
			{
				$params[$paramName] = [];
			}
			else
			{
				$params[$paramName] = filter_var(
					$params[$paramName],
					FILTER_VALIDATE_INT,
					[
						'flags' => FILTER_REQUIRE_ARRAY,
						'options' => ['min_range' => 1]
					]
				);

				foreach ($params[$paramName] as $key => $paramValue)
				{
					if (!is_int($paramValue))
					{
						unset($params[$paramName][$key]);
					}
				}
			}
		}

		$params['SKIP_ADD_MESSAGE'] = isset($params['SKIP_ADD_MESSAGE']) && $params['SKIP_ADD_MESSAGE'] === 'Y';

		return $params;
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();
		$params = $this->filterParams($params);

		if (!isset($params['AUTHOR_ID']))
		{
			$params['AUTHOR_ID'] = $this->getContext()->getUserId();
		}

		if (!isset($params['OWNER_ID']))
		{
			$params['OWNER_ID'] = $this->getContext()->getUserId();
		}

		$params['USERS'] = array_unique(array_merge($params['USERS'], [$params['AUTHOR_ID']]));
		$params['USER_COUNT'] = count($params['USERS']);

		if (
			isset($params['AVATAR'])
			&& $params['AVATAR']
			&& !is_numeric((string)$params['AVATAR'])
		)
		{
			$params['AVATAR'] = \CRestUtil::saveFile($params['AVATAR']);
			$imageCheck = (new \Bitrix\Main\File\Image($params['AVATAR']["tmp_name"]))->getInfo();
			if(
				!$imageCheck
				|| !$imageCheck->getWidth()
				|| $imageCheck->getWidth() > 5000
				|| !$imageCheck->getHeight()
				|| $imageCheck->getHeight() > 5000
			)
			{
				$params['AVATAR'] = null;
			}

			if (!$params['AVATAR'] || mb_strpos($params['AVATAR']['type'], "image/") !== 0)
			{
				$params['AVATAR'] = null;
			}
			else
			{
				$params['AVATAR'] = \CFile::saveFile($params['AVATAR'], 'im');
			}
		}

		return $result->setResult($params);
	}

	public function checkTitle(): Result
	{
		if (!$this->getTitle())
		{
			$this->setTitle($this->generateTitle());
		}

		return new Result;
	}

	protected function generateTitle(): string
	{
		if (Color::isEnabled() && $this->getColor())
		{
			$colorCodeKey = 'im_chat_color_' . $this->getColor();
			$colorCodeCount = \CGlobalCounter::GetValue($colorCodeKey, \CGlobalCounter::ALL_SITES);
			if ($colorCodeCount >= Color::MAX_COLOR_COUNT)
			{
				$colorCodeCount = 0;
				\CGlobalCounter::Set($colorCodeKey, 1, \CGlobalCounter::ALL_SITES, '', false);
			}

			$chatTitle = Loc::getMessage('IM_CHAT_NAME_FORMAT', [
				'#COLOR#' => Color::getName($this->getColor()),
				'#NUMBER#' => ++$colorCodeCount,
			]);
			\CGlobalCounter::Set($colorCodeKey, $colorCodeCount, \CGlobalCounter::ALL_SITES, '', false);
		}
		else
		{
			$userIds = [];
			if ($this->getUserIds() && count($this->getUserIds()))
			{
				$userIds = $this->getUserIds();
			}
			$userIds = \CIMContactList::PrepareUserIds($userIds);
			$users = \CIMContactList::GetUserData([
				'ID' => array_values($userIds),
				'DEPARTMENT' => 'N',
				'USE_CACHE' => 'N'
			]);

			$usersNames = [];
			foreach ($users['users'] as $user)
			{
				$usersNames[] = htmlspecialcharsback($user['name']);
			}

			$chatTitle = Loc::getMessage('IM_CHAT_NAME_FORMAT_USERS', [
				'#USERS_NAMES#' => implode(', ', $usersNames),
			]);
		}

		return mb_substr($chatTitle, 0, 255);
	}

	protected function sendPushReadOpponent(MessageCollection $messages, int $lastId): array
	{
		$pushMessage = parent::sendPushReadOpponent($messages, $lastId);
		CPullWatch::AddToStack("IM_PUBLIC_{$this->chatId}", $pushMessage);

		return $pushMessage;
	}

	protected function sendGreetingMessage(?int $authorId = null)
	{
		if (!$authorId)
		{
			$authorId = $this->getAuthorId();
		}
		$author = \Bitrix\Im\V2\Entity\User\User::getInstance($authorId);

		$messageVariant = 'CHAT';
		if ($this->getEntityType())
		{
			$messageVariant = $this->getEntityType();
		}
		$messageCode = 'IM_' . $messageVariant . '_CREATE_' . $author->getGender();

		$messageText = Loc::getMessage(
			$messageCode,
			[
				'#USER_NAME#' => htmlspecialcharsback($author->getName())
			]
		);

		\CIMMessage::Add([
			'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => $author->getId(),
			'MESSAGE' => $messageText,
			'SYSTEM' => 'Y',
			'PUSH' => 'N'
		]);

		if ($authorId !== $this->getAuthorId())
		{
			$messageText = Loc::getMessage(
				'IM_CHAT_APPOINT_OWNER_' . $author->getGender(),
				[
					'#USER_1_NAME#' => htmlspecialcharsback($author->getName()),
					'#USER_2_NAME#' => htmlspecialcharsback($this->getAuthor()->getName())
				]
			);

			\CIMMessage::Add([
				'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
				'TO_CHAT_ID' => $this->getChatId(),
				'FROM_USER_ID' => $author->getId(),
				'MESSAGE' => $messageText,
				'SYSTEM' => 'Y',
				'PUSH' => 'N'
			]);
		}
	}

	protected function sendBanner(?int $authorId = null): void
	{
		if (!$authorId)
		{
			$authorId = $this->getAuthorId();
		}
		$author = \Bitrix\Im\V2\Entity\User\User::getInstance($authorId);

		if (
			in_array($this->getType(), [self::IM_TYPE_CHAT, self::IM_TYPE_OPEN], true)
			&& empty($this->getEntityType())
		)
		{
			\CIMMessage::Add([
				'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
				'TO_CHAT_ID' => $this->getChatId(),
				'FROM_USER_ID' => $author->getId(),
				'MESSAGE' => Loc::getMessage('IM_CHAT_CREATE_WELCOME'),
				'SYSTEM' => 'Y',
				'PUSH' => 'N',
				'PARAMS' => [
					'COMPONENT_ID' => 'ChatCreationMessage',
				]
			]);
		}
	}

	protected function sendInviteMessage(?int $authorId = null): void
	{
		if (!$authorId)
		{
			$authorId = $this->getAuthorId();
		}
		$author = \Bitrix\Im\V2\Entity\User\User::getInstance($authorId);

		$userIds = array_unique($this->getUserIds());
		if (count($userIds) < 2)
		{
			return;
		}

		$userIds = \CIMContactList::PrepareUserIds($userIds);
		$users = \CIMContactList::GetUserData([
			'ID' => array_values($userIds),
			'DEPARTMENT' => 'N',
			'USE_CACHE' => 'N'
		]);

		if (!isset($users['users']) || count($users['users']) < 2)
		{
			return;
		}

		$usersNames = [];

		if ($authorId !== $this->getAuthorId())
		{
			$usersNames[] = htmlspecialcharsback($this->getAuthor()->getName());
		}

		foreach ($users['users'] as $user)
		{
			if ($user['name'] !== $author->getName())
			{
				$usersNames[] = htmlspecialcharsback($user['name']);
			}
		}

		$messageText = Loc::getMessage(
			'IM_CHAT_JOIN_' . $author->getGender(),
			[
				'#USER_1_NAME#' => htmlspecialcharsback($author->getName()),
				'#USER_2_NAME#' => implode(', ', array_unique($usersNames))
			]
		);

		\CIMMessage::Add([
			'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => $author->getId(),
			'MESSAGE' => $messageText,
			'SYSTEM' => 'Y',
		]);
	}

	/**
	 * Provides message sending process.
	 *
	 * @param Message|string|array $message
	 * @param SendingConfig|array|null $sendingConfig
	 * @return Result
	 */
	public function sendMessage($message, $sendingConfig = null): Result
	{
		$result = new Result;

		if (!$this->getChatId())
		{
			return $result->addError(new ChatError(ChatError::WRONG_TARGET_CHAT));
		}

		if (!$message instanceof Message)
		{
			$message = new Message($message);
		}

		$message
			->setRegistry($this->messageRegistry)
			->setContext($this->context)
			->setChatId($this->getChatId())
			->setNotifyModule('im')
			->setNotifyEvent(Notify::EVENT_GROUP)
		;

		// config for sending process
		if ($sendingConfig instanceof SendingConfig)
		{
			$sendingServiceConfig = $sendingConfig;
		}
		else
		{
			$sendingServiceConfig = new SendingConfig();
			if (is_array($sendingConfig))
			{
				$sendingServiceConfig->fill($sendingConfig);
			}
		}
		// sending process
		$sendService = new SendingService($sendingServiceConfig);
		$sendService->setContext($this->context);


		// check duplication by UUID
		if (
			!$message->isSystem()
			&& $message->getUuid()
		)
		{
			$checkUuidResult = $sendService->checkDuplicateByUuid($message);
			if (!$checkUuidResult->isSuccess())
			{
				return $result->addErrors($checkUuidResult->getErrors());
			}
			$data = $checkUuidResult->getResult();
			if (!empty($data['messageId']))
			{
				return $result->setResult($checkUuidResult->getResult());
			}
		}

		// author from current context
		if (
			!$message->getAuthorId()
			&& !$message->isSystem()
		)
		{
			$message->setAuthorId($this->getContext()->getUserId());
		}

		// Extranet cannot send system
		if (
			$message->isSystem()
			&& $message->getAuthorId()
			&& User::getInstance($message->getAuthorId())->isExtranet()
		)
		{
			$message->markAsSystem(false);
		}

		// permissions
		if (
			!$sendingServiceConfig->skipUserCheck()
			&& !$sendingServiceConfig->convertMode()
			&& !$message->isSystem()
		)
		{
			if (!$this->hasAccess($message->getAuthorId()))
			{
				return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
			}
		}

		// fire event `im:OnBeforeChatMessageAdd` before message send
		$eventResult = $sendService->fireEventBeforeMessageSend($this, $message);
		if (!$eventResult->isSuccess())
		{
			// cancel sending by event
			return $result->addErrors($eventResult->getErrors());
		}

		// check for empty message
		if (
			!$message->getMessage()
			&& !$message->hasFiles()
			&& !$message->getParams()->isSet(Params::ATTACH)
		)
		{
			return $result->addError(new MessageError(MessageError::EMPTY_MESSAGE));
		}

		if ($sendingServiceConfig->keepConnectorSilence())
		{
			$message->getParams()->get(Params::STYLE_CLASS)->setValue('bx-messenger-content-item-system');
		}


		// Replacements / DateLink
		if ($sendingServiceConfig->generateUrlPreview())
		{
			$message->parseDates();
		}

		// Emoji
		$message->checkEmoji();

		// BB codes with disk files
		$message->uploadFileFromText();

		// Format attached files
		$message->formatFilesMessageOut();

		// Save + Save Params
		$saveResult = $message->save();
		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$sendService->updateMessageUuid($message);

		// Unread
		$readService = new ReadService($message->getAuthorId());
		$readService->markMessageUnread($message, $this->getRelations());

		// Chat message counter
		$this
			->setLastMessageId($message->getMessageId())
			->incrementMessageCount()
			->save()
		;

		// Recent
		if ($sendingServiceConfig->addRecent())
		{
			$readService->markRecentUnread($message);
		}

		// Counters
		$counters = [];
		if ($sendingServiceConfig->sendPush())
		{
			$counters = $readService->getCountersForUsers($message, $this->getRelations());
		}

		// fire event `im:OnAfterMessagesAdd`
		$sendService->fireEventAfterMessageSend($this, $message);

		// Recent
		if (
			$sendingServiceConfig->addRecent()
			&& !$sendingServiceConfig->skipAuthorAddRecent()// Do not add author into recent list in case of self message chat.
		)
		{
			$this->riseInRecent($message);
		}

		// Rich
		if ($sendingServiceConfig->generateUrlPreview())
		{
			// generate preview or set bg job
			$message->generateUrlPreview();
		}

		if ($this->getParentMessageId())
		{
			$this->updateParentMessageCount();
		}

		// send Push
		if ($sendingServiceConfig->sendPush())
		{
			$pushService = new PushService($sendingServiceConfig);
			$pushService->sendPushGroupChat($this, $message, $counters);
		}

		// Mentions
		if (!$message->isSystem())
		{
			$mentionService = new MentionService($sendingServiceConfig);
			$mentionService->setContext($this->context);
			$mentionService->sendMentions($this, $message);
		}

		// Run message command
		$botService = new BotService($sendingServiceConfig);
		$botService->setContext($this->context);
		$botService->runMessageCommand($this, $message);

		// Links
		(new UrlService())->saveUrlsFromMessage($message);

		// search
		$message->updateSearchIndex();

		$result->setResult(['messageId' => $message->getMessageId()]);

		return $result;
	}

	protected function sendDescriptionMessage(?int $authorId = null): void
	{
		if (!$this->getDescription())
		{
			return;
		}

		if (!$authorId)
		{
			$authorId = $this->getAuthorId();
		}
		$author = \Bitrix\Im\V2\Entity\User\User::getInstance($authorId);

		\CIMMessage::Add([
			'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => $author->getId(),
			'MESSAGE' => htmlspecialcharsback($this->getDescription()),
		]);
	}
}
