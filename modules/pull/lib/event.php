<?php

namespace Bitrix\Pull;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Pull\DTO\Message;

class Event
{
	const SHARED_CHANNEL = 0;

	private static bool $backgroundContext = false;

	private static array $messages = [];
	private static array $deferredMessages = [];
	private static array $push = [];
	private static $error = false;

	public static function add($recipient, array $parameters, $channelType = \CPullChannel::TYPE_PRIVATE)
	{
		if (!isset($parameters['module_id']))
		{
			self::$error = new Error(__METHOD__, 'EVENT_PARAMETERS_FORMAT', Loc::getMessage('PULL_EVENT_PARAMETERS_FORMAT_ERROR'), $parameters);
			return false;
		}

		$badUnicodeSymbolsPath = Common::findInvalidUnicodeSymbols($parameters);
		if ($badUnicodeSymbolsPath)
		{
			$warning = 'Parameters array contains invalid UTF-8 characters by the path ' . $badUnicodeSymbolsPath;
			self::$error = new Error(__METHOD__, 'EVENT_BAD_ENCODING', $warning, $parameters);
			return false;
		}

		if (isset($parameters['command']) && !empty($parameters['command']))
		{
			if (!Config::isJsonRpcUsed() && (isset($parameters['user_params']) || isset($parameters['dictionary'])))
			{
				self::generateEventsForUsers($recipient, $parameters, $channelType);
			}
			else
			{
				$result = self::addEvent($recipient, $parameters, $channelType);
			}
		}
		else if (isset($parameters['push']) || isset($parameters['pushParamsCallback']))
		{
			$result = self::addPush($recipient, $parameters);
		}
		else
		{
			self::$error = new Error(__METHOD__, 'EVENT_PARAMETERS_FORMAT', Loc::getMessage('PULL_EVENT_PARAMETERS_FORMAT_ERROR'), $parameters);
			return false;
		}

		return $result;
	}

	private static function addEvent($recipient, $parameters, $channelType = \CPullChannel::TYPE_PRIVATE)
	{
		if (!is_array($recipient))
		{
			$recipient = [$recipient];
		}

		$entities = self::getEntitiesByType($recipient);
		if ($entities === null)
		{
			self::$error = new Error(__METHOD__, 'RECIPIENT_FORMAT', Loc::getMessage('PULL_EVENT_RECIPIENT_FORMAT_ERROR'), [
				'recipient' => $recipient,
				'eventParameters' => $parameters,
			]);

			return false;
		}

		$parameters = self::prepareParameters($parameters);
		if (!$parameters)
		{
			return false;
		}
		$parameters['channel_type'] = $channelType;

		if (empty($entities['users']) && empty($entities['channels']))
		{
			return true;
		}

		if (isset($parameters['push']))
		{
			$pushParameters = $parameters['push'];
			unset($parameters['push']);
		}
		else
		{
			$pushParameters = null;
		}

		if (isset($parameters['pushParamsCallback']))
		{
			$pushParametersCallback = $parameters['pushParamsCallback'];
			unset($parameters['pushParamsCallback']);
		}
		else
		{
			$pushParametersCallback = null;
		}

		if (isset($parameters['hasCallback']) && $parameters['hasCallback'])
		{
			self::addMessage(self::$deferredMessages, $entities['channels'], $entities['users'], $parameters);
		}
		else
		{
			self::addMessage(self::$messages, $entities['channels'], $entities['users'], $parameters);
		}

		if (
			self::$backgroundContext
			|| defined('BX_CHECK_AGENT_START') && !defined('BX_WITH_ON_AFTER_EPILOG')
		)
		{
			self::send();
		}

		if ($pushParameters || $pushParametersCallback)
		{
			if ($pushParameters)
			{
				$parameters['push'] = $pushParameters;
			}
			if ($pushParametersCallback)
			{
				$parameters['pushParamsCallback'] = $pushParametersCallback;
			}
			unset($parameters['channel_type']);
			self::addPush($entities['users'], $parameters);
		}

		return true;
	}

	private static function addMessage(array &$destination, array $channels, array $users, array $parameters)
	{
		$eventCode = self::getParamsCode($parameters);
		unset($parameters['hasCallback']);

		if (isset($destination[$eventCode]))
		{
			$waitingToReceiveUserList = $destination[$eventCode]['users'] ?? [];
			$newUserList = $users ?? [];
			$destination[$eventCode]['users'] = array_unique(array_merge($waitingToReceiveUserList, $newUserList));

			$waitingToReceiveChannelList = $destination[$eventCode]['channels'] ?? [];
			$newChannelList = $channels ?? [];
			$destination[$eventCode]['channels'] = array_unique(array_merge($waitingToReceiveChannelList, $newChannelList));
		}
		else
		{
			$destination[$eventCode] = [
				'event' => $parameters,
				'users' => array_unique($users),
				'channels' => array_unique($channels),
			];
		}
	}

	private static function generateEventsForUsers($recipients, $parameters, $channelType = \CPullChannel::TYPE_PRIVATE)
	{
		if (!is_array($recipients))
		{
			$recipients = [$recipients];
		}
		if (is_array($parameters['dictionary']))
		{
			$dictionary = $parameters['dictionary'];
			unset($parameters['dictionary']);
			$parameters['params'] = array_merge($parameters['params'], $dictionary);
		}

		$processed = [];
		if (is_array($parameters['user_params']))
		{
			$params = $parameters['params'];
			$paramsByUser = $parameters['user_params'];
			unset($parameters['user_params']);

			foreach ($recipients as $recipient)
			{
				if (isset($paramsByUser[$recipient]) && is_array($paramsByUser[$recipient]))
				{
					$userParams = $parameters;
					$userParams['params'] = array_merge($params, $paramsByUser[$recipient]);
					self::addEvent($recipient, $userParams, $channelType);

					$processed[] = $recipient;
				}
			}
		}
		$left = array_diff($recipients, $processed);
		if (!empty($left))
		{
			self::addEvent($left, $parameters, $channelType);
		}
	}

	private static function addPush($users, $parameters)
	{
		if (!\CPullOptions::GetPushStatus())
		{
			self::$error = new Error(__METHOD__, 'PUSH_DISABLED', Loc::getMessage('PULL_EVENT_PUSH_DISABLED_ERROR'), [
				'recipient' => $users,
				'eventParameters' => $parameters,
			]);

			return false;
		}
		if (!is_array($users))
		{
			$users = [$users];
		}

		foreach ($users as $id => $entity)
		{
			$entity = intval($entity);
			if ($entity <= 0)
			{
				unset($users[$id]);
			}
		}

		if (empty($users))
		{
			self::$error = new Error(__METHOD__, 'RECIPIENT_FORMAT', Loc::getMessage('PULL_EVENT_RECIPIENT_FORMAT_ERROR'), [
				'recipient' => $users,
				'eventParameters' => $parameters,
			]);

			return false;
		}

		if (isset($parameters['skip_users']))
		{
			if (!isset($parameters['push']['skip_users']))
			{
				$parameters['push']['skip_users'] = [];
			}
			$parameters['push']['skip_users'] = array_merge($parameters['skip_users'], $parameters['push']['skip_users']);
		}

		if (!empty($parameters['push']['type']))
		{
			foreach ($users as $userId)
			{
				if (!\Bitrix\Pull\Push::getConfigTypeStatus($parameters['module_id'], $parameters['push']['type'], $userId))
				{
					$parameters['push']['skip_users'][] = $userId;
				}
			}
		}

		$parameters = self::preparePushParameters($parameters);
		if (!$parameters)
		{
			return false;
		}

		$pushCode = self::getParamsCode($parameters['push']);
		if (self::$push[$pushCode])
		{
			self::$push[$pushCode]['users'] = array_unique(array_merge(self::$push[$pushCode]['users'], array_values($users)));
		}
		else
		{
			$hasPushCallback = $parameters['hasPushCallback'];
			unset($parameters['hasPushCallback']);

			self::$push[$pushCode]['push'] = $parameters['push'];
			self::$push[$pushCode]['extra'] = $parameters['extra'];
			self::$push[$pushCode]['hasPushCallback'] = $hasPushCallback;
			self::$push[$pushCode]['users'] = array_unique(array_values($users));
		}

		if (
			self::$backgroundContext
			|| defined('BX_CHECK_AGENT_START') && !defined('BX_WITH_ON_AFTER_EPILOG')
		)
		{
			self::send();
		}

		return true;
	}

	private static function processDeferredMessages()
	{
		foreach (self::$deferredMessages as $eventCode => $message)
		{
			$callback = $message['event']['paramsCallback'];
			if (Main\Loader::includeModule($callback['module_id']) && method_exists($callback['class'], $callback['method']))
			{
				$messageParameters = call_user_func_array([$callback['class'], $callback['method']], [$callback['params']]);
				self::addMessage(self::$messages, $message['users'], $message['channels'], $messageParameters);
			}
		}
		self::$deferredMessages = [];
	}

	private static function executePushEvent($parameters)
	{
		if (!self::$backgroundContext && $parameters['hasPushCallback'])
		{
			return null;
		}

		$data = [];
		if ($parameters['hasPushCallback'])
		{
			$callback = $parameters['push']['pushParamsCallback'];
			Main\Loader::includeModule($callback['module_id']);
			if (method_exists($callback['class'], $callback['method']))
			{
				$data = call_user_func_array(
					[
						$callback['class'],
						$callback['method'],
					],
					[
						$callback['params'],
					]
				);
			}
		}
		else
		{
			$data = $parameters['push'];
		}

		$data['message'] = str_replace("\n", " ", trim($data['message']));
		$data['params'] = $data['params'] ?? [];
		$data['advanced_params'] = $data['advanced_params'] ?? [];
		$data['advanced_params']['extra'] = $parameters['extra'] ?? [];
		$data['badge'] = isset($data['badge']) ? (int)$data['badge'] : '';
		$data['sound'] = $data['sound'] ?? '';
		$data['tag'] = $data['tag'] ?? '';
		$data['sub_tag'] = $data['sub_tag'] ?? '';
		$data['app_id'] = $data['app_id'] ?? '';
		$data['send_immediately'] = $data['send_immediately'] == 'Y' ? 'Y' : 'N';
		$data['important'] = $data['important'] == 'Y' ? 'Y' : 'N';

		$users = [];
		foreach ($parameters['users'] as $userId)
		{
			$users[] = $userId;
		}

		if (empty($users))
		{
			return true;
		}

		$manager = new \CPushManager();
		$manager->AddQueue([
			'USER_ID' => $users,
			'SKIP_USERS' => is_array($data['skip_users']) ? $data['skip_users'] : [],
			'MESSAGE' => $data['message'],
			'EXPIRY' => $data['expiry'],
			'PARAMS' => $data['params'],
			'ADVANCED_PARAMS' => $data['advanced_params'],
			'BADGE' => $data['badge'],
			'SOUND' => $data['sound'],
			'TAG' => $data['tag'],
			'SUB_TAG' => $data['sub_tag'],
			'APP_ID' => $data['app_id'],
			'SEND_IMMEDIATELY' => $data['send_immediately'],
			'IMPORTANT' => $data['important'],
		]);

		return true;
	}

	public static function send()
	{
		if (self::$backgroundContext)
		{
			self::processDeferredMessages();
		}

		static::executeEvents();
		static::executePushEvents();

		return true;
	}

	public static function executeEvents(): Main\Result
	{
		$result = new Main\Result();
		if (empty(self::$messages))
		{
			return $result;
		}

		if (!\CPullOptions::GetQueueServerStatus())
		{
			self::$messages = [];

			return $result;
		}

		self::fillChannels(self::$messages);

		if (Config::isJsonRpcUsed())
		{
			$messageList = self::convertEventsToMessages(self::$messages);
			$sendResult = JsonRpcTransport::sendMessages($messageList);
			if ($sendResult->isSuccess())
			{
				self::$messages = [];
			}
			else
			{
				$result->addErrors($sendResult->getErrors());
			}
		}
		else
		{
			if (Config::isProtobufUsed())
			{
				$sendResult = ProtobufTransport::sendMessages(self::$messages);
				if (!$sendResult->isSuccess())
				{
					$result->addErrors($sendResult->getErrors());
				}
			}
			else
			{
				self::sendEventsLegacy();
			}

			self::$messages = [];
		}

		return $result;
	}


	public static function executePushEvents()
	{
		foreach (self::$push as $pushCode => $event)
		{
			$result = self::executePushEvent($event);
			if (!is_null($result))
			{
				unset(self::$push[$pushCode]);
			}
		}
	}

	private static function sendEventsLegacy()
	{
		foreach (self::$messages as $eventCode => $event)
		{
			if (\Bitrix\Pull\Log::isEnabled())
			{
				// TODO change code after release - $parameters['hasCallback']
				$currentHits = ceil(count($event['channels']) / \CPullOptions::GetCommandPerHit());
				$hitCount += $currentHits;

				$currentChannelCount = count($event['channels']);
				$channelCount += $currentChannelCount;

				$currentMessagesBytes = self::getBytes($event['event']) + self::getBytes($event['channels']);
				$messagesBytes += $currentMessagesBytes;
				$logs[] = 'Command: ' . $event['event']['module_id'] . '/' . $event['event']['command'] . '; Hits: ' . $currentHits . '; Channel: ' . $currentChannelCount . '; Bytes: ' . $currentMessagesBytes . '';
			}

			if (empty($event['channels']))
			{
				continue;
			}

			$data = [
				'module_id' => $event['event']['module_id'],
				'command' => $event['event']['command'],
				'params' => is_array($event['event']['params']) ? $event['event']['params'] : [],
				'extra' => $event['event']['extra'],
			];
			$options = ['expiry' => $event['event']['expiry']];

			if (\CPullChannel::Send($event['channels'], \Bitrix\Pull\Common::jsonEncode($data), $options))
			{
				unset(self::$messages[$eventCode]);
			}
		}

		if ($logs && \Bitrix\Pull\Log::isEnabled())
		{
			if (count($logs) > 1)
			{
				$logs[] = 'Total - Hits: ' . $hitCount . '; Channel: ' . $channelCount . '; Messages: ' . $messagesCount . '; Bytes: ' . $messagesBytes . '';
			}

			if (count($logs) > 1 || $hitCount > 1 || $channelCount > 1 || $messagesBytes > 1000)
			{
				$logTitle = '!! Pull messages stats - important !!';
			}
			else
			{
				$logTitle = '-- Pull messages stats --';
			}

			\Bitrix\Pull\Log::write(implode("\n", $logs), $logTitle);
		}
	}

	public static function onAfterEpilog()
	{
		Main\Application::getInstance()->addBackgroundJob([__CLASS__, "sendInBackground"]);
		return true;
	}

	public static function sendInBackground()
	{
		self::$backgroundContext = true;
		self::send();
	}

	public static function fillChannels(array &$messages)
	{
		foreach ($messages as $key => &$message)
		{
			$users = $message['users'] ?? [];
			if (!empty($messages[$key]['channels']) && is_array($messages[$key]['channels']))
			{
				$messages[$key]['channels'] = array_merge($messages[$key]['channels'], self::getChannelIds($users, $message['event']['channel_type']));
			}
			else
			{
				$messages[$key]['channels'] = self::getChannelIds($users, $message['event']['channel_type']);
			}
			unset($message['event']['channel_type']);
		}
	}

	public static function getChannelIds(array $users, $type = \CPullChannel::TYPE_PRIVATE)
	{
		$result = [];
		foreach ($users as $userId)
		{
			$data = \CPullChannel::Get($userId, true, false, $type);
			if ($data)
			{
				$result[] = $data['CHANNEL_ID'];
			}
		}

		return $result;
	}

	public static function getUserIds(array $channels)
	{
		$result = array_fill_keys($channels, null);
		$orm = \Bitrix\Pull\Model\ChannelTable::getList([
			'select' => ['USER_ID', 'CHANNEL_ID', 'USER_ACTIVE' => 'USER.ACTIVE'],
			'filter' => [
				'=CHANNEL_ID' => $channels,
			],
		]);
		while ($row = $orm->fetch())
		{
			if ($row['USER_ID'] > 0 && $row['USER_ACTIVE'] !== 'N')
			{
				$result[$row['CHANNEL_ID']] = $row['USER_ID'];
			}
			else
			{
				unset($result[$row['CHANNEL_ID']]);
			}
		}

		return $result;
	}

	private static function prepareParameters(array $parameters)
	{
		if (empty($parameters['command']))
		{
			self::$error = new Error(__METHOD__, 'EVENT_PARAMETERS_FORMAT', Loc::getMessage('PULL_EVENT_PARAMETERS_FORMAT_ERROR'), $parameters);
			return false;
		}

		$parameters['module_id'] = mb_strtolower($parameters['module_id']);
		$parameters['expiry'] = (int)($parameters['expiry'] ?? 86400);

		if (isset($parameters['paramsCallback']))
		{
			if (empty($parameters['paramsCallback']['class']) || empty($parameters['paramsCallback']['method']))
			{
				self::$error = new Error(__METHOD__, 'EVENT_CALLBACK_FORMAT', Loc::getMessage('PULL_EVENT_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}

			if (empty($parameters['paramsCallback']['module_id']))
			{
				$parameters['paramsCallback']['module_id'] = $parameters['module_id'];
			}

			Main\Loader::includeModule($parameters['paramsCallback']['module_id']);

			if (!method_exists($parameters['paramsCallback']['class'], $parameters['paramsCallback']['method']))
			{
				self::$error = new Error(__METHOD__, 'EVENT_CALLBACK_NOT_FOUND', Loc::getMessage('PULL_EVENT_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}
			if (!isset($parameters['paramsCallback']['params']))
			{
				$parameters['paramsCallback']['params'] = [];
			}

			$parameters['params'] = [];
			$parameters['hasCallback'] = true;
		}
		else
		{
			if (!isset($parameters['params']) || !is_array($parameters['params']))
			{
				$parameters['params'] = [];
			}
		}

		$parameters['extra']['server_time'] ??= date('c');
		$parameters['extra']['server_time_unix'] ??= microtime(true);

		$parameters['extra']['server_name'] = Option::get('main', 'server_name', $_SERVER['SERVER_NAME']);
		$parameters['extra']['revision_web'] = PULL_REVISION_WEB;
		$parameters['extra']['revision_mobile'] = PULL_REVISION_MOBILE;

		return $parameters;
	}

	private static function preparePushParameters(array $parameters)
	{
		$parameters['module_id'] = mb_strtolower($parameters['module_id']);

		if (isset($parameters['pushParamsCallback']))
		{
			if (
				empty($parameters['pushParamsCallback']['class'])
				|| empty($parameters['pushParamsCallback']['method'])
			)
			{
				self::$error = new Error(__METHOD__, 'EVENT_PUSH_CALLBACK_FORMAT', Loc::getMessage('PULL_EVENT_PUSH_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}

			if (empty($parameters['pushParamsCallback']['module_id']))
			{
				$parameters['pushParamsCallback']['module_id'] = $parameters['module_id'];
			}

			Main\Loader::includeModule($parameters['pushParamsCallback']['module_id']);

			if (!method_exists($parameters['pushParamsCallback']['class'], $parameters['pushParamsCallback']['method']))
			{
				self::$error = new Error(__METHOD__, 'EVENT_PUSH_CALLBACK_NOT_FOUND', Loc::getMessage('PULL_EVENT_PUSH_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}
			if (!isset($parameters['pushParamsCallback']['params']))
			{
				$parameters['pushParamsCallback']['params'] = [];
			}
			$parameters['push']['pushParamsCallback'] = $parameters['pushParamsCallback'];
			$parameters['hasPushCallback'] = true;
		}
		else
		{
			$parameters['hasPushCallback'] = false;
			$parameters['pushParamsCallback'] = [];

			if (isset($parameters['badge']) && $parameters['badge'] == 'Y')
			{
				$parameters['send_immediately'] = 'Y';
				unset($parameters['badge']);
			}

			if (empty($parameters['push']))
			{
				self::$error = new Error(__METHOD__, 'EVENT_PUSH_PARAMETERS_FORMAT', Loc::getMessage('PULL_EVENT_PUSH_PARAMETERS_FORMAT_ERROR'), $parameters);
				return false;
			}
		}

		if (!isset($parameters['extra']['server_time']))
		{
			$parameters['extra']['server_time'] = date('c');
		}
		if (!$parameters['extra']['server_time_unix'])
		{
			$parameters['extra']['server_time_unix'] = microtime(true);
		}

		return $parameters;
	}

	public static function getParamsCode($params)
	{
		if (isset($params['groupId']) && !empty($params['groupId']))
		{
			return md5($params['groupId']);
		}
		else
		{
			$paramsWithoutTime = $params;

			unset($paramsWithoutTime['extra']['server_time']);
			unset($paramsWithoutTime['extra']['server_time_unix']);
			unset($paramsWithoutTime['advanced_params']['filterCallback']);

			return serialize($paramsWithoutTime);
		}
	}

	private static function getEntitiesByType(array $recipientList): ?array
	{
		$result = [
			'users' => [],
			'channels' => [],
			'count' => 0,
		];
		foreach ($recipientList as $entity)
		{
			if ($entity instanceof \Bitrix\Pull\Model\Channel)
			{
				$result['channels'][] = $entity->getPrivateId();
				$result['count']++;
			}
			else if (self::isChannelEntity($entity))
			{
				$result['channels'][] = $entity;
				$result['count']++;
			}
			else
			{
				$result['users'][] = intval($entity);
				$result['count']++;
			}
		}

		return $result['count'] > 0 ? $result : null;
	}

	private static function getBytes($variable)
	{
		$bytes = 0;

		if (is_string($variable))
		{
			$bytes += mb_strlen($variable);
		}
		else if (is_array($variable))
		{
			foreach ($variable as $value)
			{
				$bytes += self::getBytes($value);
			}
		}
		else
		{
			$bytes += mb_strlen((string)$variable);
		}

		return $bytes;
	}

	private static function isChannelEntity($entity)
	{
		return is_string($entity) && mb_strlen($entity) == 32;
	}

	/**
	 * @param array $events
	 * @return \Bitrix\Pull\DTO\Message[]
	 */
	private static function convertEventsToMessages(array $events): array
	{
		return array_map(
			function ($event) {
				return Message::fromEvent($event);
			},
			$events
		);
	}

	public static function getLastError()
	{
		return self::$error;
	}

}