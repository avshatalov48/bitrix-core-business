<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im\Text;
use Bitrix\Im\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Chat\NotifyChat;
use Bitrix\Im\V2\Chat\GroupChat;
use Bitrix\Im\V2\Common\ContextCustomer;

class PushService
{
	use ContextCustomer;

	private SendingConfig $sendingConfig;

	/**
	 * @param SendingConfig|null $sendingConfig
	 */
	public function __construct(?SendingConfig $sendingConfig = null)
	{
		if ($sendingConfig === null)
		{
			$sendingConfig = new SendingConfig();
		}
		$this->sendingConfig = $sendingConfig;
	}

	public function isPullEnable(): bool
	{
		static $enable;
		if ($enable === null)
		{
			$enable = \Bitrix\Main\Loader::includeModule('pull');
		}
		return $enable;
	}


	//region Push Private chat

	/**
	 * @param PrivateChat $chat
	 * @param Message $message
	 * @param array $counters
	 * @return void
	 */
	public function sendPushPrivateChat(PrivateChat $chat, Message $message, array $counters = []): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$fromUserId = $message->getAuthorId();
		$toUserId = $chat->getCompanion()->getId();

		$pullMessage = [
			'module_id' => 'im',
			'command' => 'message',
			'params' => $this->formatPrivateMessage($message, $chat),
			'extra' => \Bitrix\Im\Common::getPullExtra(),
		];

		$pullMessageTo = $pullMessage;
		$pullMessageTo['params']['dialogId'] = $fromUserId;

		$pullMessageFrom = $pullMessage;
		$pullMessageFrom['params']['dialogId'] = $toUserId;

		$pullMessageFrom['params']['counter'] = $counters[$fromUserId] ?? 0;
		\Bitrix\Pull\Event::add($fromUserId, $pullMessageFrom);

		if ($fromUserId != $toUserId)
		{
			$pullMessageTo['params']['counter'] = $counters[$toUserId] ?? 0;
			\Bitrix\Pull\Event::add($toUserId, $pullMessageTo);

			$pullMessageTo = $this->preparePushForPrivate($pullMessageTo);
			$pullMessageFrom = $this->preparePushForPrivate($pullMessageFrom);

			if ($this->sendingConfig->sendPush())
			{
				if ($message->getPushMessage())
				{
					$pullMessageTo['push']['message'] = $message->getPushMessage();
					$pullMessageTo['push']['advanced_params']['senderMessage'] = $message->getPushMessage();
					$pullMessageFrom['push']['message'] = $message->getPushMessage();
					$pullMessageFrom['push']['advanced_params']['senderMessage'] = $message->getPushMessage();
				}

				$pullMessageTo['push']['advanced_params']['counter'] = $counters[$toUserId] ?? 0;
				\Bitrix\Pull\Push::add($toUserId, $pullMessageTo);

				$pullMessageFrom['push']['advanced_params']['counter'] = $counters[$fromUserId] ?? 0;
				\Bitrix\Pull\Push::add($fromUserId, array_merge_recursive($pullMessageFrom, ['push' => [
					'skip_users' => [$fromUserId],
					'advanced_params' => [
						"notificationsToCancel" => ['IM_MESS'],
					],
					'send_immediately' => 'Y', // $this->sendingConfig->sendPushImmediately()
				]]));
			}
		}
	}

	/**
	 * @param Message $message
	 * @param PrivateChat $chat
	 * @return array
	 */
	private function formatPrivateMessage(Message $message, PrivateChat $chat): array
	{
		$fromUserId = $message->getAuthorId();
		$toUserId = $chat->getCompanion()->getId();

		$users = \CIMContactList::GetUserData([
			'ID' =>  [$toUserId, $fromUserId],
			'PHONES' => 'Y',
		]);

		return [
			'chatId' => $chat->getChatId(),
			'dialogId' => 0,
			'chat' => [],
			'lines' => null,
			'userInChat' => [],
			'userBlockChat' => [],
			'users' => !empty($users['users']) ? $users['users'] : null,
			'message' => [
				'id' => $message->getMessageId(),
				'templateId' => $message->getUuid(),
				'templateFileId' => $message->getFileUuid(),
				'prevId' => $chat->getPrevMessageId(),
				'chatId' => $chat->getChatId(),
				'senderId' => $fromUserId,
				'recipientId' => $toUserId,
				'system' => ($message->isSystem() ? 'Y' : 'N'),
				'date' => DateTime::createFromTimestamp(time()),// DATE_CREATE
				'text' => Text::parse($message->getMessage()),
				'textLegacy' => Text::parseLegacyFormat($message->getMessage()),
				'params' => $message->getParams()->toPullFormat(),
				'counter' => 0,
			],
			'files' => $message->getFilesDiskData(),
			'notify' => true,
		];
	}

	/**
	 * @param array $params
	 * @return array
	 */
	private function preparePushForPrivate(array $params): array
	{
		$pushText = $this->prepareMessageForPush($params['params']);
		unset($params['params']['message']['text_push']);

		if (isset($params['params']['system']) && $params['params']['system'] == 'Y')
		{
			$userName = '';
			$avatarUser = '';
		}
		else
		{
			$userName = User::getInstance($params['params']['message']['senderId'])->getFullName(false);
			$avatarUser = User::getInstance($params['params']['message']['senderId'])->getAvatar();
			if ($avatarUser && mb_strpos($avatarUser, 'http') !== 0)
			{
				$avatarUser = \Bitrix\Im\Common::getPublicDomain().$avatarUser;
			}
		}

		if ($params['params']['users'][$params['params']['message']['senderId']])
		{
			$params['params']['users'] = [
				$params['params']['message']['senderId'] => $params['params']['users'][$params['params']['message']['senderId']]
			];
		}
		else
		{
			$params['params']['users'] = [];
		}

		unset($params['extra']);

		array_walk_recursive($params, function(&$item, $key)
		{
			if (is_null($item))
			{
				$item = false;
			}
			else if ($item instanceof DateTime)
			{
				$item = date('c', $item->getTimestamp());
			}
		});

		$result = [];
		$result['module_id'] = 'im';
		$result['push'] = [];
		$result['push']['type'] = 'message';
		$result['push']['tag'] = 'IM_MESS_'.(int)$params['params']['message']['senderId'];
		$result['push']['sub_tag'] = 'IM_MESS';
		$result['push']['app_id'] = 'Bitrix24';
		$result['push']['message'] = $pushText;
		$result['push']['advanced_params'] = [
			'group' => 'im_message',
			'avatarUrl' => $avatarUser,
			'senderName' => $userName,
			'senderMessage' => $pushText,
			'data' => $this->prepareEventForPush($params['command'], $params['params']),
		];
		$result['push']['params'] = [
			'TAG' => 'IM_MESS_'.$params['params']['message']['senderId'],
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR. 'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => [
				'RECIPIENT_ID' => (int)$params['params']['message']['senderId'],
				'MESSAGE_ID' => $params['params']['message']['id']
			],
		];

		return $result;
	}

	//endregion


	//region Push in Group Chat

	/**
	 * @param GroupChat $chat
	 * @param Message $message
	 * @param array $counters
	 * @return void
	 */
	public function sendPushGroupChat(GroupChat $chat, Message $message, array $counters = []): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$fromUserId = $message->getAuthorId();

		$skippedRelations = [];
		$pushUserSkip = [];
		$pushUserSend = [];

		foreach ($chat->getRelations() as $relation)
		{
			if ($this->sendingConfig->addRecent() !== true)
			{
				$skippedRelations[$relation->getId()] = true;
				continue;
			}
			if ($relation->getUser()->getExternalAuthId() == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
			{
			}
			elseif ($relation->getUser()->isActive() !== true)
			{
				$skippedRelations[$relation->getId()] = true;
				continue;
			}

			if ($chat->getEntityType() == Chat::ENTITY_TYPE_LINE)
			{
				if ($relation->getUser()->getExternalAuthId() == 'imconnector')
				{
					$skippedRelations[$relation->getId()] = true;
					continue;
				}
			}

			if ($relation->getUserId() == $fromUserId)
			{
				\CPushManager::DeleteFromQueueBySubTag($fromUserId, 'IM_MESS');
			}
			elseif ($relation->getNotifyBlock() && !$this->sendingConfig->sendPushImmediately())
			{
				$pushUserSkip[] = $relation->getChatId();
				$pushUserSend[] = $relation->getChatId();
			}
			elseif (!$this->sendingConfig->sendPush())
			{
			}
			else
			{
				$pushUserSend[] = $relation['USER_ID'];
			}
		}

		$pullMessage = [
			'module_id' => 'im',
			'command' => 'messageChat',
			'params' => $this->getFormatGroupChatMessage($message, $chat),
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
		$events = [];
		foreach ($chat->getRelations() as $relation)
		{
			if (isset($skippedRelations[$relation->getId()]))
			{
				continue;
			}
			$events[$relation->getUserId()] = $pullMessage;
			$events[$relation->getUserId()]['params']['counter'] = $counters[$relation->getUserId()] ?? 0;
			$events[$relation->getUserId()]['groupId'] =
				'im_chat_'
				. $chat->getChatId()
				. '_'. $message->getMessageId()
				. '_'. $events[$relation['USER_ID']]['params']['counter']
			;
		}

		if ($chat->getType() == Chat::IM_TYPE_OPEN || $chat->getType() == Chat::IM_TYPE_OPEN_LINE)
		{
			$watchPullMessage = $pullMessage;
			$watchPullMessage['params']['message']['params']['NOTIFY'] = 'N';
			\CPullWatch::AddToStack('IM_PUBLIC_'. $chat->getChatId(), $watchPullMessage);
		}

		$groups = $this->getEventByCounterGroup($events);
		foreach ($groups as $group)
		{
			\Bitrix\Pull\Event::add($group['users'], $group['event']);

			$userList = array_intersect($pushUserSend, $group['users']);
			if (!empty($userList))
			{
				$pushParams = $group['event'];

				$pushParams = $this->preparePushForChat($pushParams);

				if ($this->sendingConfig->sendPushImmediately())
				{
					$pushParams['push']['important'] = 'Y';
				}

				$pushParams['skip_users'] = $pushUserSkip;

				if ($message->getPushMessage())
				{
					$pushParams['push']['message'] = $message->getPushMessage();
					$pushParams['push']['advanced_params']['senderMessage'] = $message->getPushMessage();
				}
				$pushParams['push']['advanced_params']['counter'] = $group['event']['params']['counter'];

				\Bitrix\Pull\Push::add($userList, $pushParams);
			}
		}
	}

	/**
	 * @param Message $message
	 * @param GroupChat $chat
	 * @return array
	 */
	private function getFormatGroupChatMessage(Message $message, GroupChat $chat): array
	{
		$fromUserId = $message->getAuthorId();

		$arUsers = \CIMContactList::GetUserData(Array(
			'ID' => $fromUserId,
			'PHONES' => 'Y',
		));

		$arChat = \CIMChat::GetChatData([
			'ID' => $chat->getChatId(),
			'USE_CACHE' => 'N',
		]);

		// todo: Replace it with Chat methods
		$pushParams = $message->getPushParams();
		$extraParamContext = $pushParams['CONTEXT'] ?? null;
		if (
			!empty($arUsers['users'])
			&& $extraParamContext == Chat::ENTITY_TYPE_LIVECHAT
			&& \Bitrix\Main\Loader::includeModule('imopenlines')
		)
		{
			[$lineId, $userId] = explode('|', $arChat['chat'][$chat->getChatId()]['entity_id']);
			$userCode = 'livechat|' . $lineId . '|' . $chat->getChatId() . '|' . $userId;
			unset($lineId, $userId);

			foreach ($arUsers['users'] as $userId => $userData)
			{
				$arUsers['users'][$userId] = \Bitrix\ImOpenLines\Connector::getOperatorInfo($pushParams['LINE_ID'], $userId, $userCode);
			}
		}

		return [
			'chatId' => $chat->getChatId(),
			'dialogId' => $chat->getDialogId(),
			'chat' => $arChat['chat'] ?? [],
			'lines' => $arChat['lines'][$chat->getChatId()] ?? null,
			'userInChat' => $arChat['userInChat'] ?? [],
			'userBlockChat' => $arChat['userChatBlockStatus'] ?? [],
			'users' => (is_array($arUsers) && is_array($arUsers['users'])) ? $arUsers['users'] : null,
			'message' => [
				'id' => $message->getMessageId(),
				'templateId' => $message->getUuid(),
				'templateFileId' => $message->getFileIds(),
				'prevId' => $chat->getPrevMessageId(),
				'chatId' => $chat->getChatId(),
				'senderId' => $fromUserId,
				'recipientId' => $chat->getDialogId(),
				'system' => ($message->isSystem() ? 'Y': 'N'),
				'date' => DateTime::createFromTimestamp(time()), // DATE_CREATE
				'text' => Text::parse($message->getMessage()),
				'textLegacy' => Text::parseLegacyFormat($message->getMessage()),
				'params' => $message->getParams()->toPullFormat(),
				'counter' => 0,
			],
			'files' => $message->getFilesDiskData(),
			'notify' => 'Y',
		];
	}

	/**
	 * @param array $events
	 * @param int $maxUserInGroup
	 * @return array
	 */
	private function getEventByCounterGroup(array $events, int $maxUserInGroup = 100): array
	{
		$groups = [];
		foreach ($events as $userId => $event)
		{
			$eventCode = $event['groupId'];
			if (!isset($groups[$eventCode]))
			{
				$groups[$eventCode]['event'] = $event;
			}
			$groups[$eventCode]['users'][] = $userId;
			$groups[$eventCode]['count'] = count($groups[$eventCode]['users']);
		}

		\Bitrix\Main\Type\Collection::sortByColumn($groups, ['count' => \SORT_DESC]);

		$count = 0;
		$finalGroup = [];
		foreach ($groups as $eventCode => $event)
		{
			if ($count >= $maxUserInGroup)
			{
				if (isset($finalGroup['other']))
				{
					$finalGroup['other']['users'] = array_unique(array_merge($finalGroup['other']['users'], $event['users']));
				}
				else
				{
					$finalGroup['other'] = $event;
					$finalGroup['other']['event']['params']['counter'] = 100;
				}
			}
			else
			{
				$finalGroup[$eventCode] = $event;
			}
			$count++;
		}

		\Bitrix\Main\Type\Collection::sortByColumn($finalGroup, ['count' => \SORT_ASC]);

		return $finalGroup;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	private function preparePushForChat(array $params): array
	{
		$pushText = $this->prepareMessageForPush($params['params']);
		unset($params['params']['message']['text_push']);

		$chatTitle = mb_substr(htmlspecialcharsback($params['params']['chat'][$params['params']['chatId']]['name']), 0, 32);
		$chatType = $params['params']['chat'][$params['params']['chatId']]['type'];
		$chatAvatar = $params['params']['chat'][$params['params']['chatId']]['avatar'];
		$chatTypeLetter = $params['params']['chat'][$params['params']['chatId']]['message_type'];


		if (($params['params']['system'] ?? null) === 'Y' || $params['params']['message']['senderId'] <= 0)
		{
			$avatarUser = '';
			$userName = '';
		}
		else
		{
			$userName = User::getInstance($params['params']['message']['senderId'])->getFullName(false);
			$avatarUser = User::getInstance($params['params']['message']['senderId'])->getAvatar();
			if ($avatarUser && mb_strpos($avatarUser, 'http') !== 0)
			{
				$avatarUser = \Bitrix\Im\Common::getPublicDomain().$avatarUser;
			}
		}

		if (
			isset(
				$params['params']['message']['senderId'],
				$params['params']['users'][$params['params']['message']['senderId']]
			)
			&& $params['params']['users'][$params['params']['message']['senderId']]
		)
		{
			$params['params']['users'] = [
				$params['params']['message']['senderId'] => $params['params']['users'][$params['params']['message']['senderId']]
			];
		}
		else
		{
			$params['params']['users'] = [];
		}

		if ($chatAvatar == '/bitrix/js/im/images/blank.gif')
		{
			$chatAvatar = '';
		}
		else if ($chatAvatar && mb_strpos($chatAvatar, 'http') !== 0)
		{
			$chatAvatar = \Bitrix\Im\Common::getPublicDomain().$chatAvatar;
		}

		unset($params['extra']);

		array_walk_recursive($params, function(&$item, $key)
		{
			if (is_null($item))
			{
				$item = false;
			}
			else if ($item instanceof DateTime)
			{
				$item = date('c', $item->getTimestamp());
			}
		});

		$result = [];
		$result['module_id'] = 'im';
		$result['push']['type'] = ($chatType === 'open'? 'openChat': $chatType);
		$result['push']['tag'] = 'IM_CHAT_'.intval($params['params']['chatId']);
		$result['push']['sub_tag'] = 'IM_MESS';
		$result['push']['app_id'] = 'Bitrix24';
		$result['push']['message'] = ($userName? $userName.': ': '').$pushText;
		$result['push']['advanced_params'] = [
			'group' => $chatType == 'lines'? 'im_lines_message': 'im_message',
			'avatarUrl' => $chatAvatar? $chatAvatar: $avatarUser,
			'senderName' => $chatTitle,
			'senderMessage' => ($userName? $userName.': ': '').$pushText,
			'senderCut' => mb_strlen($userName? $userName.': ' : ''),
			'data' => $this->prepareEventForPush($params['command'], $params['params'])
		];
		$result['push']['params'] = [
			'TAG' => 'IM_CHAT_'.$params['params']['chatId'],
			'CHAT_TYPE' => $chatTypeLetter? $chatTypeLetter: 'C',
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR.'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => [
				'RECIPIENT_ID' => 'chat'.$params['params']['chatId'],
				'MESSAGE_ID' => $params['params']['message']['id']
			],
		];

		return $result;
	}


	//endregion


	//region Notification Push

	public function sendPushNotification(NotifyChat $chat, Message $message, int $counter = 0, bool $sendNotifyFlash = true): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$toUserId = $chat->getAuthorId();

		$pullNotificationParams = $this->getFormatNotify($message, $counter);

		// We shouldn't send push, if it is disabled in notification settings.
		$needPush = \CIMSettings::GetNotifyAccess(
			$toUserId,
			$message->getNotifyModule(),
			$message->getNotifyEvent(),
			\CIMSettings::CLIENT_PUSH
		);

		if ($needPush)
		{
			// we prepare push params ONLY if there are no ADVANCED_PARAMS from outside.
			// If ADVANCED_PARAMS exists we must not change them.
			$pushParams = $message->getPushParams();
			if (isset($pushParams['ADVANCED_PARAMS']))
			{
				$advancedParams = $pushParams['ADVANCED_PARAMS'];
				unset($pushParams['ADVANCED_PARAMS']);
			}
			else
			{
				$advancedParams = $this->prepareAdvancedParamsForNotificationPush(
					$pullNotificationParams,
					$message->getPushMessage()
				);
			}

			\Bitrix\Pull\Push::add(
				$toUserId,
				[
					'module_id' => $message->getNotifyModule(),
					'push' => [
						'type' => $message->getNotifyEvent(),
						'message' => $message->getPushMessage(),
						'params' => $message->getPushParams() ?? ['TAG' => 'IM_NOTIFY'],
						'advanced_params' => $advancedParams,
						'important' => ($this->sendingConfig->sendPushImmediately() ? 'Y': 'N'),
						'tag' => $message->getNotifyTag(),
						'sub_tag' => $message->getNotifySubTag(),
						'app_id' => $message->getPushAppId() ?? '',
					]
				]
			);
		}

		//($message->isNotifyFlash() ?? false),
		if ($sendNotifyFlash)
		{
			\Bitrix\Pull\Event::add(
				$toUserId,
				[
					'module_id' => 'im',
					'command' => 'notifyAdd',
					'params' => $pullNotificationParams,
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
		}
	}

	public function sendPushNotificationFlash(NotifyChat $chat, Message $message, int $counter = 0): void
	{
		if (!$this->isPullEnable())
		{
			return;
		}

		$toUserId = $chat->getAuthorId();

		$pullNotificationParams = $this->getFormatNotify($message, $counter);

		\Bitrix\Pull\Event::add(
			$toUserId,
			[
				'module_id' => 'im',
				'command' => 'notifyAdd',
				'params' => $pullNotificationParams,
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]
		);
	}

	/**
	 * @param Message $message
	 * @param int $counter
	 * @return array
	 */
	private function getFormatNotify(Message $message, int $counter = 0): array
	{
		$messageText = Text::parse(
			Text::convertHtmlToBbCode($message->getMessage()),
			[
				'LINK' => 'Y', // (isset($arFields['HIDE_LINK']) && $arFields['HIDE_LINK'] === 'Y') ? 'N' : 'Y',
				'LINK_TARGET_SELF' => 'Y',
				'SAFE' => 'N',
				'FONT' => 'Y',
				'SMILES' => 'N',
			]
		);

		$notify = [
			'id' => $message->getMessageId(),
			'type' => $message->getNotifyType(),
			'date' => DateTime::createFromTimestamp(time()), // DATE_CREATE
			'silent' => 'N', //($arFields['NOTIFY_SILENT'] ?? null) ? 'Y' : 'N',
			'onlyFlash' => ($message->isNotifyFlash() ?? false),
			'link' => ($message->getNotifyLink() ?? ''),
			'text' => $messageText,
			'tag' => ($message->getNotifyTag() ? md5($message->getNotifyTag()) : ''),
			'originalTag' => $message->getNotifyTag(),
			'original_tag' => $message->getNotifyTag(),
			'read' => $message->isNotifyRead() !== null ? ($message->isNotifyRead() ? 'Y' : 'N') : null,
			'settingName' => $message->getNotifyModule(). '|'. $message->getNotifyEvent(),
			'params' => $message->getParams()->toPullFormat(),
			'counter' => $counter,
			'users' => [],
			'userId' => null,
			'userName' => null,
			'userColor' => null,
			'userAvatar' => null,
			'userLink' => null,
		];
		if ($message->getAuthorId())
		{
			$notify['users'][] = $message->getAuthor()->getArray([
				'JSON' => 'Y',
				'SKIP_ONLINE' => 'Y'
			]);

			$notify['userId'] = $message->getAuthorId();
			$notify['userName'] = $message->getAuthor()->getName();
			$notify['userColor'] = $message->getAuthor()->getColor();
			$notify['userAvatar'] = $message->getAuthor()->getAvatar();
			$notify['userLink'] = $message->getAuthor()->getProfile();
		}


		if ($message->getNotifyType() == \IM_NOTIFY_CONFIRM)
		{
			$notify['buttons'] = $message->getNotifyButtons();
		}
		else
		{
			$notify['title'] = htmlspecialcharsbx($message->getNotifyTitle());
		}

		return $notify;
	}

	/**
	 * @param array $params
	 * @param string|null $pushMessage
	 * @return array
	 */
	private function prepareAdvancedParamsForNotificationPush(array $params, ?string $pushMessage = null): array
	{
		if ($params['date'] instanceof DateTime)
		{
			$params['date'] = date('c', $params['date']->getTimestamp());
		}

		$params['text'] = $this->prepareMessageForPush(['message' => ['text' => $params['text']]]);

		$advancedParams = [
			'id' => 'im_notify',
			'group' => 'im_notify',
			'data' => $this->prepareNotificationEventForPush($params, $pushMessage)
		];

		if (isset($params['userName']))
		{
			$advancedParams['senderName'] = $params['userName'];
			if (isset($params['userAvatar']))
			{
				$advancedParams['avatarUrl'] = $params['userAvatar'];
			}
			$advancedParams['senderMessage'] = $pushMessage ?: $params['text'];
		}

		return $advancedParams;
	}

	/**
	 * Prepares data for push with encoding fields to numbers. Should be the same structure as for p&p event.
	 * Decoding is located on mobile side (extension "chat/dataconverter").
	 *
	 * @param array $event Array with the same data as for p&p event.
	 * @param string|null $pushMessage Push notification text.
	 *
	 * @return array
	 */
	private function prepareNotificationEventForPush(array $event, ?string $pushMessage = null): array
	{
		$result = [
			'cmd' => 'notifyAdd',
			'id' => (int)$event['id'],
			'type' => (int)$event['type'],
			'date' => (string)$event['date'],
			'tag' => (string)$event['tag'],
			'onlyFlash' => $event['onlyFlash'],
			'originalTag' => (string)$event['originalTag'],
			'settingName' => (string)$event['settingName'],
			'counter' => (int)$event['counter'],
			'userId' => (int)$event['userId'],
			'userName' => (string)$event['userName'],
			'userColor' => (string)$event['userColor'],
			'userAvatar' => (string)$event['userAvatar'],
			'userLink' => (string)$event['userLink'],
			'params' => $event['params'],
		];
		if (isset($event['buttons']))
		{
			$result['buttons'] = $event['buttons'];
		}

		// We need to save original text ("long") in result only if we have push text ("short").
		// "Long" text will be used to render push in notifications list.
		if (isset($pushMessage))
		{
			$result['text'] = $event['text'];
		}

		$fieldToIndex = [
			'id' => 1,
			'type' => 2,
			'date' => 3,
			'text' => 4,
			'tag' => 6,
			'onlyFlash' => 7,
			'originalTag' => 8,
			'settingName' => 9,
			'counter' => 10,
			'userId' => 11,
			'userName' => 12,
			'userColor' => 13,
			'userAvatar' => 14,
			'userLink' => 15,
			'params' => 16,
			'buttons' => 17,
		];

		return $this->changeKeysPushEvent($result, $fieldToIndex);
	}


	//endregion


	//region Common

	/**
	 * @param array $message
	 * @return string
	 */
	private function prepareMessageForPush(array $message): string
	{
		Message::loadPhrases();

		$messageText = $message['message']['text'];
		if (isset($message['message']['text_push']) && $message['message']['text_push'])
		{
			$messageText = $message['message']['text_push'];
		}
		else
		{
			if (isset($message['message']['params']['ATTACH']) && count($message['message']['params']['ATTACH']) > 0)
			{
				$attachText = $message['message']['params']['ATTACH'][0]['DESCRIPTION'];
				if (!$attachText)
				{
					$attachText = Text::getEmoji('attach').' '.Loc::getMessage('IM_MESSAGE_ATTACH');
				}

				$messageText .=
					(empty($messageText)? '': ' ')
					. $attachText
				;
			}

			if (isset($message['files']) && count($message['files']) > 0)
			{
				$file = array_values($message['files'])[0];

				if ($file['type'] === 'image')
				{
					$fileName = Text::getEmoji($file['type']).' '.Loc::getMessage('IM_MESSAGE_IMAGE');
				}
				else if ($file['type'] === 'audio')
				{
					$fileName = Text::getEmoji($file['type']).' '.Loc::getMessage('IM_MESSAGE_AUDIO');
				}
				else if ($file['type'] === 'video')
				{
					$fileName = Text::getEmoji($file['type']).' '.Loc::getMessage('IM_MESSAGE_VIDEO');
				}
				else
				{
					$fileName = Text::getEmoji('file', Loc::getMessage('IM_MESSAGE_FILE').':').' '.$file['name'];
				}

				$messageText .= trim($fileName);
			}
		}

		$codeIcon = Text::getEmoji('code', '['.Loc::getMessage('IM_MESSAGE_CODE').']');
		$quoteIcon = Text::getEmoji('quote', '['.Loc::getMessage('IM_MESSAGE_QUOTE').']');

		$messageText = str_replace("\n", ' ', $messageText);
		$messageText = preg_replace("/\[CODE\](.*?)\[\/CODE\]/si", ' '.$codeIcon.' ', $messageText);
		$messageText = preg_replace("/\[s\].*?\[\/s\]/i", '-', $messageText);
		$messageText = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $messageText);
		$messageText = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/i".\BX_UTF_PCRE_MODIFIER, "$1", $messageText);
		$messageText = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixs".\BX_UTF_PCRE_MODIFIER, "$2", $messageText);
		$messageText = preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", ['\Bitrix\Im\Text', 'modifyShortUserTag'], $messageText);
		$messageText = preg_replace("/\[USER=([0-9]+)( REPLACE)?](.+?)\[\/USER]/i", "$3", $messageText);
		$messageText = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $messageText);
		$messageText = preg_replace_callback("/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/i", ['\Bitrix\Im\Text', "modifySendPut"], $messageText);
		$messageText = preg_replace_callback("/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/i", ['\Bitrix\Im\Text', "modifySendPut"], $messageText);
		$messageText = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $messageText);
		$messageText = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $messageText);
		$messageText = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", ['\Bitrix\Im\Text', 'modifyIcon'], $messageText);
		$messageText = preg_replace('#\-{54}.+?\-{54}#s', ' '.$quoteIcon.' ', str_replace('#BR#', ' ', $messageText));
		$messageText = preg_replace('/^(>>(.*)(\n)?)/mi', ' '.$quoteIcon.' ', str_replace('#BR#', ' ', $messageText));
		$messageText = preg_replace("/\\[color\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/color\\]/is".\BX_UTF_PCRE_MODIFIER, "$2", $messageText);
		$messageText = preg_replace("/\\[size\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/size\\]/is".\BX_UTF_PCRE_MODIFIER, "$2", $messageText);

		return trim($messageText);
	}

	private function prepareEventForPush(string $command, array $event): array
	{
		$result = [
			'cmd' => $command,
			'chatId' => (int)$event['chatId'],
			'dialogId' => (string)$event['dialogId'],
			'counter' => (int)$event['counter'],
		];

		if ($event['notify'] !== true)
		{
			$result['notify'] = $event['notify'];
		}

		if (!empty($event['chat'][$event['chatId']]))
		{
			$eventChat = $event['chat'][$event['chatId']];

			$chat = [
				'id' => (int)$eventChat['id'],
				'name' => (string)$eventChat['name'],
				'owner' => (int)$eventChat['owner'],
				'color' => (string)$eventChat['color'],
				'type' => (string)$eventChat['type'],
				'date_create' => (string)$eventChat['date_create'],
			];

			if (
				!empty($eventChat['avatar'])
				&& $eventChat['avatar'] !== '/bitrix/js/im/images/blank.gif'
			)
			{
				$chat['avatar'] = $eventChat['avatar'];
			}
			if ($eventChat['call'])
			{
				$chat['call'] = (string)$eventChat['call'];
			}
			if ($eventChat['call_number'])
			{
				$chat['call_number'] = (string)$eventChat['call_number'];
			}
			if ($eventChat['entity_data_1'])
			{
				$chat['entity_data_1'] = (string)$eventChat['entity_data_1'];
			}
			if ($eventChat['entity_data_2'])
			{
				$chat['entity_data_2'] = (string)$eventChat['entity_data_2'];
			}
			if ($eventChat['entity_data_3'])
			{
				$chat['entity_data_3'] = (string)$eventChat['entity_data_3'];
			}
			if ($eventChat['entity_id'])
			{
				$chat['entity_id'] = (string)$eventChat['entity_id'];
			}
			if ($eventChat['entity_type'])
			{
				$chat['entity_type'] = (string)$eventChat['entity_type'];
			}
			if ($eventChat['extranet'])
			{
				$chat['extranet'] = true;
			}

			$result['chat'] = $chat;
		}

		if (!empty($event['lines']))
		{
			$result['lines'] = $event['lines'];
		}

		if (!empty($event['users'][$event['message']['senderId']]))
		{
			$eventUser = $event['users'][$event['message']['senderId']];

			$user = [
				'id' => (int)$eventUser['id'],
				'name' => (string)$eventUser['name'],
				'first_name' => (string)$eventUser['first_name'],
				'last_name' => (string)$eventUser['last_name'],
				'color' => (string)$eventUser['color'],
			];

			if (
				!empty($eventUser['avatar'])
				&& $eventUser['avatar'] !== '/bitrix/js/im/images/blank.gif'
			)
			{
				$user['avatar'] = (string)$eventUser['avatar'];
			}

			if ($eventUser['absent'])
			{
				$user['absent'] = true;
			}
			if (!$eventUser['active'])
			{
				$user['active'] = $eventUser['active'];
			}
			if ($eventUser['bot'])
			{
				$user['bot'] = true;
			}
			if ($eventUser['extranet'])
			{
				$user['extranet'] = true;
			}
			if ($eventUser['network'])
			{
				$user['network'] = true;
			}
			if ($eventUser['birthday'])
			{
				$user['birthday'] = $eventUser['birthday'];
			}
			if ($eventUser['connector'])
			{
				$user['connector'] = true;
			}
			if ($eventUser['external_auth_id'] !== 'default')
			{
				$user['external_auth_id'] = $eventUser['external_auth_id'];
			}
			if ($eventUser['gender'] === 'F')
			{
				$user['gender'] = 'F';
			}
			if ($eventUser['work_position'])
			{
				$user['work_position'] = (string)$eventUser['work_position'];
			}

			$result['users'] = $user;
		}

		if (!empty($event['files']))
		{
			foreach ($event['files'] as $key => $value)
			{
				$file = [
					'id' => (int)$value['id'],
					'extension' => (string)$value['extension'],
					'name' => (string)$value['name'],
					'size' => (int)$value['size'],
					'type' => (string)$value['type'],
					'image' => $value['image'],
					'urlDownload' => '',
					'urlPreview' => (new \Bitrix\Main\Web\Uri($value['urlPreview']))->deleteParams(['fileName'])->getUri(),
					'urlShow' => '',
				];
				if ($value['image'])
				{
					$file['image'] = $value['image'];
				}
				if ($value['progress'] !== 100)
				{
					$file['progress'] = (int)$value['progress'];
				}
				if ($value['status'] !== 'done')
				{
					$file['status'] = $value['status'];
				}

				$result['files'][$key] = $file;
			}
		}

		if (!empty($event['message']))
		{
			$eventMessage = $event['message'];

			$message = [
				'id' => (int)$eventMessage['id'],
				'date' => (string)$eventMessage['date'],
				'params' => $eventMessage['params'],
				'prevId' => (int)$eventMessage['prevId'],
				'senderId' => (int)$eventMessage['senderId'],
			];

			if (isset($message['params']['ATTACH']))
			{
				unset($message['params']['ATTACH']);
			}

			if ($eventMessage['system'] === 'Y')
			{
				$message['system'] = 'Y';
			}

			$result['message'] = $message;
		}

		$indexToNameMap = [
			'chat' => 1,
			'chatId' => 2,
			'counter' => 3,
			'dialogId' => 4,
			'files' => 5,
			'message' => 6,
			'users' => 8,
			'name' => 9,
			'avatar' => 10,
			'color' => 11,
			'notify' => 12,
			'type' => 13,
			'extranet' => 14,

			'date_create' => 20,
			'owner' => 21,
			'entity_id' => 23,
			'entity_type' => 24,
			'entity_data_1' => 203,
			'entity_data_2' => 204,
			'entity_data_3' => 205,
			'call' => 201,
			'call_number' => 202,
			'manager_list' => 209,
			'mute_list' => 210,

			'first_name' => 40,
			'last_name' => 41,
			'gender' => 42,
			'work_position' => 43,
			'active' => 400,
			'birthday' => 401,
			'bot' => 402,
			'connector' => 403,
			'external_auth_id' => 404,
			'network' => 406,


			'textLegacy' => 65,
			'date' => 61,
			'prevId' => 62,
			'params' => 63,
			'senderId' => 64,
			'system' => 601,

			'extension' => 80,
			'image' => 81,
			'progress' => 82,
			'size' => 83,
			'status' => 84,
			'urlDownload' => 85,
			'urlPreview' => 86,
			'urlShow' => 87,
			'width' => 88,
			'height' => 89,
		];

		return $this->changeKeysPushEvent($result, $indexToNameMap);
	}

	private function changeKeysPushEvent(array $object, array $map): array
	{
		$result = [];

		foreach($object as $key => $value)
		{
			$index = isset($map[$key]) ? $map[$key] : $key;
			if (is_null($value))
			{
				$value = "";
			}
			if (is_array($value))
			{
				$result[$index] = $this->changeKeysPushEvent($value, $map);
			}
			else
			{
				$result[$index] = $value;
			}
		}

		return $result;
	}

	//endregion
}