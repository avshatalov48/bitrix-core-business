<?php
namespace Bitrix\Pull;

use Bitrix\Main\Localization\Loc;

class Event
{
	const SHARED_CHANNEL = 0;

	private static $messages = array();
	private static $push = array();
	private static $error = false;

	public static function add($recipient, $parameters, $channelType = \CPullChannel::TYPE_PRIVATE)
	{
		if (!isset($parameters['module_id']))
		{
			self::$error = new Error(__METHOD__, 'EVENT_PARAMETERS_FORMAT', Loc::getMessage('PULL_EVENT_PARAMETERS_FORMAT_ERROR'), $parameters);
			return false;
		}

		if (isset($parameters['command']) && !empty($parameters['command']))
		{
			$result = self::addEvent($recipient, $parameters, $channelType);
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
			$recipient = Array($recipient);
		}

		$entities = self::getEntitiesByType($recipient);
		if (!$entities)
		{
			self::$error = new Error(__METHOD__, 'RECIPIENT_FORMAT', Loc::getMessage('PULL_EVENT_RECIPIENT_FORMAT_ERROR'), Array(
				'recipient' => $recipient,
				'eventParameters' => $parameters
			));

			return false;
		}

		$parameters = self::prepareParameters($parameters);
		if (!$parameters)
		{
			return false;
		}

		$request = Array('users'=>array(), 'channels'=>array());
		if (!empty($entities['users']))
		{
			$request['users'] = self::getChannelIds($entities['users'], $channelType);
		}

		if (!empty($entities['channels']))
		{
			$request['channels'] = self::getUserIds($entities['channels']);
		}

		$channels = array_merge($request['users'], $request['channels']);
		if (empty($channels))
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

		$eventCode = self::getParamsCode($parameters);
		if (self::$messages[$eventCode])
		{
			self::$messages[$eventCode]['users'] = array_unique(array_merge(self::$messages[$eventCode]['users'], array_values($channels)));
			self::$messages[$eventCode]['channels'] = array_unique(array_merge(self::$messages[$eventCode]['channels'], array_keys($channels)));
		}
		else
		{
			$hasCallback = $parameters['hasCallback'];
			unset($parameters['hasCallback']);

			self::$messages[$eventCode]['event'] = $parameters;
			self::$messages[$eventCode]['hasCallback'] = $hasCallback;
			self::$messages[$eventCode]['users'] = array_unique(array_values($channels));
			self::$messages[$eventCode]['channels'] = array_unique(array_keys($channels));
		}

		if (defined('BX_CHECK_AGENT_START') && !defined('BX_WITH_ON_AFTER_EPILOG'))
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
			self::addPush(array_values($request['users']), $parameters);
		}

		return true;
	}

	private static function addPush($users, $parameters)
	{
		if (!\CPullOptions::GetPushStatus())
		{
			self::$error = new Error(__METHOD__, 'PUSH_DISABLED', Loc::getMessage('PULL_EVENT_PUSH_DISABLED_ERROR'), Array(
				'recipient' => $users,
				'eventParameters' => $parameters
			));

			return false;
		}
		if (!is_array($users))
		{
			$users = Array($users);
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
			self::$error = new Error(__METHOD__, 'RECIPIENT_FORMAT', Loc::getMessage('PULL_EVENT_RECIPIENT_FORMAT_ERROR'), Array(
				'recipient' => $users,
				'eventParameters' => $parameters
			));

			return false;
		}

		if (isset($parameters['skip_users']))
		{
			if (!isset($parameters['push']['skip_users']))
			{
				$parameters['push']['skip_users'] = Array();
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

		if (defined('BX_CHECK_AGENT_START') && !defined('BX_WITH_ON_AFTER_EPILOG'))
		{
			self::send();
		}

		return true;
	}

	private static function executeEvent($parameters)
	{
		if (!defined('BX_PULL_EPILOG_AFTER') && $parameters['hasCallback'])
		{
			return null;
		}

		if ($parameters['hasCallback'])
		{
			\Bitrix\Main\Loader::includeModule($parameters['event']['paramsCallback']['module_id']);
			if (
				method_exists($parameters['event']['paramsCallback']['class'], $parameters['event']['paramsCallback']['method']))
			{
				$parameters['event']['params'] = call_user_func_array(
					array(
						$parameters['event']['paramsCallback']['class'],
						$parameters['event']['paramsCallback']['method']
					),
					Array(
						$parameters['event']['paramsCallback']['params']
					)
				);
				unset($parameters['event']['paramsCallback']);
			}
		}

		if (empty($parameters['channels']))
		{
			return true;
		}

		return \CPullStack::AddByChannel($parameters['channels'], $parameters['event']);
	}

	private static function executePushEvent($parameters)
	{
		if (!defined('BX_PULL_EPILOG_AFTER') && $parameters['hasPushCallback'])
		{
			return null;
		}

		$data = Array();
		if ($parameters['hasPushCallback'])
		{
			\Bitrix\Main\Loader::includeModule($parameters['push']['pushParamsCallback']['module_id']);
			if (method_exists($parameters['push']['pushParamsCallback']['class'], $parameters['push']['pushParamsCallback']['method']))
			{
				$data = call_user_func_array(
					array(
						$parameters['push']['pushParamsCallback']['class'],
						$parameters['push']['pushParamsCallback']['method']
					),
					Array(
						$parameters['push']['pushParamsCallback']['params']
					)
				);
			}
		}
		else
		{
			$data = $parameters['push'];
		}

		$data['message'] = str_replace("\n", " ", trim($data['message']));
		$data['params'] = isset($data['params'])? $data['params']: Array();
		$data['advanced_params'] = isset($data['advanced_params'])? $data['advanced_params']: Array();
		$data['advanced_params']['extra'] = $parameters['extra']? $parameters['extra']: Array();
		$data['badge'] = isset($data['badge'])? intval($data['badge']): '';
		$data['sound'] = isset($data['sound'])? $data['sound']: '';
		$data['tag'] = isset($data['tag'])? $data['tag']: '';
		$data['sub_tag'] = isset($data['sub_tag'])? $data['sub_tag']: '';
		$data['app_id'] = isset($data['app_id'])? $data['app_id']: '';
		$data['send_immediately'] = $data['send_immediately'] == 'Y'? 'Y': 'N';

		$users = Array();
		foreach ($parameters['users'] as $userId)
		{
			$users[] = $userId;
		}

		if (empty($users))
		{
			return true;
		}

		$manager = new \CPushManager();
		$manager->AddQueue(Array(
			'USER_ID' => $users,
			'SKIP_USERS' => is_array($data['skip_users'])? $data['skip_users']: Array(),
			'MESSAGE' => $data['message'],
			'PARAMS' => $data['params'],
			'ADVANCED_PARAMS' => $data['advanced_params'],
			'BADGE' => $data['badge'],
			'SOUND' => $data['sound'],
			'TAG' => $data['tag'],
			'SUB_TAG' => $data['sub_tag'],
			'APP_ID' => $data['app_id'],
			'SEND_IMMEDIATELY' => $data['send_immediately'],
		));

		return true;
	}

	public static function send()
	{
		$hitCount = 0;
		$channelCount = 0;
		$messagesCount = count(self::$messages);
		$messagesBytes = 0;
		$logs = Array();

		foreach (self::$messages as $eventCode => $event)
		{
			if (\Bitrix\Pull\Log::isEnabled())
			{
				// TODO change code after release - $parameters['hasCallback']
				$currentHits = ceil(count($event['channels'])/\CPullOptions::GetCommandPerHit());
				$hitCount += $currentHits;

				$currentChannelCount = count($event['channels']);
				$channelCount += $currentChannelCount;

				$currentMessagesBytes = self::getBytes($event['event'])+self::getBytes($event['channels']);
				$messagesBytes += $currentMessagesBytes;
				$logs[] = 'Command: '.$event['event']['module_id'].'/'.$event['event']['command'].'; Hits: '.$currentHits.'; Channel: '.$currentChannelCount.'; Bytes: '.$currentMessagesBytes.'';
			}

			$result = self::executeEvent($event);
			if (!is_null($result))
			{
				unset(self::$messages[$eventCode]);
			}
		}

		if ($logs && \Bitrix\Pull\Log::isEnabled())
		{
			if (count($logs) > 1)
			{
				$logs[] = 'Total - Hits: '.$hitCount.'; Channel: '.$channelCount.'; Messages: '.$messagesCount.'; Bytes: '.$messagesBytes.'';
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

		foreach (self::$push as $pushCode => $event)
		{
			$result = self::executePushEvent($event);
			if (!is_null($result))
			{
				unset(self::$push[$pushCode]);
			}
		}

		return true;
	}

	public static function onAfterEpilog()
	{
		define('BX_PULL_EPILOG_AFTER', true);

		if(defined("BX_FORK_AGENTS_AND_EVENTS_FUNCTION"))
		{
			if(\CMain::forkActions(array(__CLASS__, "send")))
			{
				return true;
			}
		}

		self::send();

		return true;
	}

	public static function getChannelIds($users, $type = \CPullChannel::TYPE_PRIVATE)
	{
		if (!is_array($users))
		{
			$users = Array($users);
		}

		$result = Array();
		foreach ($users as $userId)
		{
			if ($userId === 0 && $type == \CPullChannel::TYPE_PRIVATE)
			{
				$channelType = \CPullChannel::TYPE_SHARED;
			}

			$data = \CPullChannel::Get($userId, true, false, $type);
			if ($data)
			{
				$result[$data['CHANNEL_ID']] = $userId;
			}
		}

		return $result;
	}

	public static function getUserIds($channels)
	{
		if (!is_array($channels))
		{
			$channels = Array($channels);
		}

		$result = Array();
		$orm = \Bitrix\Pull\Model\ChannelTable::getList(array(
			'select' => Array('USER_ID', 'CHANNEL_ID', 'USER_ACTIVE' => 'USER.ACTIVE'),
			'filter' => Array(
				'=CHANNEL_ID' => $channels
 			)
		));
		while ($row = $orm->fetch())
		{
			if ($row['USER_ID'] > 0 && $row['USER_ACTIVE'] == 'N')
			{
				continue;
			}

			$result[$row['CHANNEL_ID']] = $row['USER_ID'];
		}

		return $result;
	}

	private static function prepareParameters($parameters)
	{
		if (
			!isset($parameters['command']) || empty($parameters['command'])
		)
		{
			self::$error = new Error(__METHOD__, 'EVENT_PARAMETERS_FORMAT', Loc::getMessage('PULL_EVENT_PARAMETERS_FORMAT_ERROR'), $parameters);
			return false;
		}

		$parameters['module_id'] = strtolower($parameters['module_id']);
		if (isset($parameters['expire']))
		{
			$parameters['expire'] = intval($parameters['expire']);
		}
		else
		{
			$parameters['expire'] = 86400;
		}

		if (isset($parameters['paramsCallback']))
		{
			if (
				empty($parameters['paramsCallback']['class'])
				|| empty($parameters['paramsCallback']['method'])
			)
			{
				self::$error = new Error(__METHOD__, 'EVENT_CALLBACK_FORMAT', Loc::getMessage('PULL_EVENT_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}

			if (empty($parameters['paramsCallback']['module_id']))
			{
				$parameters['paramsCallback']['module_id'] = $parameters['module_id'];
			}

			\Bitrix\Main\Loader::includeModule($parameters['paramsCallback']['module_id']);

			if (!method_exists($parameters['paramsCallback']['class'], $parameters['paramsCallback']['method']))
			{
				self::$error = new Error(__METHOD__, 'EVENT_CALLBACK_NOT_FOUND', Loc::getMessage('PULL_EVENT_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}
			if (!isset($parameters['paramsCallback']['params']))
			{
				$parameters['paramsCallback']['params'] = array();
			}

			$parameters['params'] = Array();
			$parameters['hasCallback'] = true;
		}
		else
		{
			$parameters['hasCallback'] = false;
			$parameters['paramsCallback'] = Array();

			if (
				!isset($parameters['params'])
				|| !is_array($parameters['params'])
			)
			{
				$parameters['params'] = Array();
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

	private static function preparePushParameters($parameters)
	{
		$parameters['module_id'] = strtolower($parameters['module_id']);

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

			\Bitrix\Main\Loader::includeModule($parameters['pushParamsCallback']['module_id']);

			if (!method_exists($parameters['pushParamsCallback']['class'], $parameters['pushParamsCallback']['method']))
			{
				self::$error = new Error(__METHOD__, 'EVENT_PUSH_CALLBACK_NOT_FOUND', Loc::getMessage('PULL_EVENT_PUSH_CALLBACK_FORMAT_ERROR'), $parameters);
				return false;
			}
			if (!isset($parameters['pushParamsCallback']['params']))
			{
				$parameters['pushParamsCallback']['params'] = array();
			}
			$parameters['push']['pushParamsCallback'] = $parameters['pushParamsCallback'];
			$parameters['hasPushCallback'] = true;
		}
		else
		{
			$parameters['hasPushCallback'] = false;
			$parameters['pushParamsCallback'] = Array();

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

			return serialize($paramsWithoutTime);
		}
	}

	private static function getEntitiesByType($recipient)
	{
		$result = Array(
			'users' => Array(),
			'channels' => Array(),
			'count' => 0,
		);
		foreach ($recipient as $entity)
		{
			if (self::isChannelEntity($entity))
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

		return $result['count'] > 0? $result: false;
	}

	private static function getBytes($variable)
	{
		$bytes = 0;

		if (is_string($variable))
		{
			$bytes += strlen($variable);
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
			$bytes += strlen((string)$variable);
		}

		return $bytes;
	}

	private static function isChannelEntity($entity)
	{
		return is_string($entity) && strlen($entity) == 32;
	}

	public static function getLastError()
	{
		return self::$error;
	}

}