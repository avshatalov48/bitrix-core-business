<?php

namespace Bitrix\Im\V2\Message\Send\Push;

use Bitrix\Im\Text;
use Bitrix\Im\User;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Chat\CopilotChat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class MobilePush
{
	protected Message $message;
	protected Message\Send\SendingConfig $config;
	protected bool $importantPush;

	public function __construct(Message $message, Message\Send\SendingConfig $config)
	{
		$this->message = $message;
		$this->config = $config;
		$this->importantPush = $this->message->getChat()->getEntityType() === 'ANNOUNCEMENT';
	}

	public function sendForGroupMessage(array $group): void
	{
		[$pushUserSend, $pushUserSkip] = $this->getPushUsers();
		$userList = array_intersect($pushUserSend, $group['users']);
		if (empty($userList) || !$this->needToSend())
		{
			return;
		}

		$pushParams = $group['event'];
		$pushParams = $this->preparePushForChat($pushParams);

		if ($this->importantPush)
		{
			$pushParams['push']['important'] = 'Y';
		}

		$pushParams['skip_users'] = $pushUserSkip;

		if ($this->message->getPushMessage())
		{
			$pushParams['push']['message'] = $this->message->getPushMessage();
			$pushParams['push']['advanced_params']['senderMessage'] = $this->message->getPushMessage();
		}
		$pushParams['push']['advanced_params']['counter'] = $group['event']['params']['counter'];

		\Bitrix\Pull\Push::add($userList, $pushParams);
	}

	public function sendForPrivateMessage(int $userId, array $push): void
	{
		if (!$this->config->sendPush())
		{
			return;
		}

		$preparedPush = $this->preparePushForPrivate($push);
		if ($this->message->getPushMessage())
		{
			$preparedPush['push']['message'] = $this->message->getPushMessage();
			$preparedPush['push']['advanced_params']['senderMessage'] = $this->message->getPushMessage();
		}

		$preparedPush['push']['advanced_params']['counter'] = $push['params']['counter'];
		if ($userId === $this->message->getAuthorId())
		{
			$preparedPush = array_merge_recursive($preparedPush, [
				'push' => [
					'skip_users' => [$userId],
					'advanced_params' => [
						"notificationsToCancel" => ['IM_MESS'],
					],
					'send_immediately' => 'Y',
					]
				]
			);
		}

		\Bitrix\Pull\Push::add($userId, $preparedPush);
	}

	protected function needToSend(): bool
	{
		$chat = $this->message->getChat();

		if ($chat instanceof CopilotChat)
		{
			return $this->needToSendFromCopilot();
		}
		if ($chat instanceof CommentChat)
		{
			return false;
		}

		return true;
	}

	protected function needToSendFromCopilot(): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('mobile'))
		{
			return false;
		}

		/** @see \Bitrix\Mobile\AppTabs\Chat::isCopilotMobileEnabled */
		return \Bitrix\Main\Config\Option::get('immobile', 'copilot_mobile_chat_enabled', 'N') === 'Y';
	}

	protected function getPushUsers(): array
	{
		$pushUserSkip = [];
		$pushUserSend = [];
		$activeUserRelations = $this->message->getChat()->getRelationsForSendMessage();

		foreach ($activeUserRelations as $relation)
		{
			if ($relation->getUserId() === $this->message->getAuthorId())
			{
				continue;
			}
			if ($relation->getNotifyBlock() && !$this->importantPush)
			{
				$pushUserSkip[] = $relation->getUserId();
				$pushUserSend[] = $relation->getUserId();
			}
			elseif ($this->config->sendPush())
			{
				$pushUserSend[] = $relation->getUserId();
			}
		}

		return [$pushUserSend, $pushUserSkip];
	}

	protected function preparePushForChat(array $params): array
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

	protected function preparePushForPrivate(array $params): array
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

				if ($attachText === \CIMMessageParamAttach::SKIP_MESSAGE)
				{
					$attachText = '';
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
		$messageText = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/iu", "$1", $messageText);
		$messageText = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixsu", "$2", $messageText);
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
		$messageText = preg_replace("/\\[color\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/color\\]/isu", "$2", $messageText);
		$messageText = preg_replace("/\\[size\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/size\\]/isu", "$2", $messageText);

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
}