<?php
namespace Bitrix\Pull;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
use Bitrix\Pull\Rest\GuestAuth;

if(!\Bitrix\Main\Loader::includeModule('rest'))
	return;

Loc::loadMessages(__FILE__);

class Rest extends \IRestService
{
	public static function onRestServiceBuildDescription()
	{
		return array(
			'pull' => array(
				'pull.application.config.get' =>  array('callback' => array(__CLASS__, 'applicationConfigGet')),
				'pull.application.event.add' =>  array('callback' => array(__CLASS__, 'applicationEventAdd')),
				'pull.application.push.add' =>  array('callback' => array(__CLASS__, 'applicationPushAdd')),
				'pull.watch.extend' =>  array('callback' => array(__CLASS__, 'watchExtend'), 'options' => array()),
			),
			'pull_channel' => array(
				'pull.config.get' =>  array('callback' => array(__CLASS__, 'configGet'), 'options' => array()),
				'pull.channel.public.get' =>  array('callback' => array(__CLASS__, 'channelPublicGet'), 'options' => array()),
				'pull.channel.public.list' =>  array('callback' => array(__CLASS__, 'channelPublicList'), 'options' => array()),
			),
			'mobile' => Array(
				'mobile.counter.types.get' => array('callback' => array(__CLASS__, 'counterTypesGet'), 'options' => array()),
				'mobile.counter.get' => array('callback' => array(__CLASS__, 'counterGet'), 'options' => array()),
				'mobile.counter.config.get' => array('callback' => array(__CLASS__, 'counterConfigGet'), 'options' => array()),
				'mobile.counter.config.set' => array('callback' => array(__CLASS__, 'counterConfigSet'), 'options' => array()),

				'mobile.push.types.get' => array('callback' => array(__CLASS__, 'pushTypesGet'), 'options' => array()),
				'mobile.push.config.get' => array('callback' => array(__CLASS__, 'pushConfigGet'), 'options' => array()),
				'mobile.push.config.set' => array('callback' => array(__CLASS__, 'pushConfigSet'), 'options' => array()),
				'mobile.push.status.get' =>  array('callback' => array(__CLASS__, 'pushStatusGet'), 'options' => array()),
				'mobile.push.status.set' =>  array('callback' => array(__CLASS__, 'pushStatusSet'), 'options' => array()),
				'mobile.push.smartfilter.status.get' =>  array('callback' => array(__CLASS__, 'pushSmartfilterStatusGet'), 'options' => array()),
				'mobile.push.smartfilter.status.set' =>  array('callback' => array(__CLASS__, 'pushSmartfilterStatusSet'), 'options' => array()),
			)
		);
	}

	public static function channelPublicGet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$type = \CPullChannel::TYPE_PRIVATE;
		if ($params['APPLICATION'] == 'Y')
		{
			$clientId = $server->getClientId();
			if (!$clientId)
			{
				throw new \Bitrix\Rest\RestException("Get application public channel available only for application authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_WRONG_REQUEST);
			}
			$type = $clientId;
		}

		$userId = intval($params['USER_ID']);

		$configParams = Array();
		$configParams['TYPE'] = $type;
		$configParams['USER_ID'] = $userId;
		$configParams['JSON'] = true;

		$config = \Bitrix\Pull\Channel::getPublicId($configParams);
		if (!$config)
		{
			throw new \Bitrix\Rest\RestException("Push & Pull server is not configured", "SERVER_ERROR", \CRestServer::STATUS_INTERNAL);
		}

		return $config;
	}

	public static function channelPublicList($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$type = \CPullChannel::TYPE_PRIVATE;
		if ($params['APPLICATION'] == 'Y')
		{
			$clientId = $server->getClientId();
			if (!$clientId)
			{
				throw new \Bitrix\Rest\RestException("Get application public channel available only for application authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_WRONG_REQUEST);
			}
			$type = $clientId;
		}

		$users = Array();
		if (is_string($params['USERS']))
		{
			$params['USERS'] = \CUtil::JsObjectToPhp($params['USERS']);
		}
		if (is_array($params['USERS']))
		{
			foreach ($params['USERS'] as $userId)
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$users[$userId] = $userId;
				}
			}
		}

		if (empty($users))
		{
			throw new \Bitrix\Rest\RestException("A wrong format for the USERS field is passed", "INVALID_FORMAT", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$configParams = Array();
		$configParams['TYPE'] = $type;
		$configParams['USERS'] = $users;
		$configParams['JSON'] = true;

		$config = \Bitrix\Pull\Channel::getPublicIds($configParams);
		if (!$config)
		{
			throw new \Bitrix\Rest\RestException("Push & Pull server is not configured", "SERVER_ERROR", \CRestServer::STATUS_INTERNAL);
		}

		return $config;
	}

	public static function applicationConfigGet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			throw new \Bitrix\Rest\RestException("Get access to application config available only for application authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$configParams = Array();
		$configParams['CACHE'] = $params['CACHE'] != 'N';
		$configParams['REOPEN'] = $params['REOPEN'] != 'N';
		$configParams['CUSTOM_TYPE'] = $clientId;
		$configParams['JSON'] = true;

		$config = \Bitrix\Pull\Config::get($configParams);
		if (!$config)
		{
			throw new \Bitrix\Rest\RestException("Push & Pull server is not configured", "SERVER_ERROR", \CRestServer::STATUS_INTERNAL);
		}

		return $config;
	}

	public static function applicationEventAdd($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			throw new \Bitrix\Rest\RestException("Get access to application config available only for application authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		global $USER;

		if (self::isAdmin())
		{
			$users = Array();
			if (is_string($params['USER_ID']))
			{
				$params['USER_ID'] = \CUtil::JsObjectToPhp($params['USER_ID']);
			}

			if (is_array($params['USER_ID']))
			{
				foreach ($params['USER_ID'] as $userId)
				{
					$userId = intval($userId);
					if ($userId > 0)
					{
						$users[$userId] = $userId;
					}
				}
				$users = array_values($users);
			}
			else
			{
				$users = (int)$params['USER_ID'];
			}
		}
		else
		{
			if (isset($params['USER_ID']))
			{
				if ($params['USER_ID'] === $USER->GetId())
				{
					$users = $USER->GetId();
				}
				else
				{
					throw new \Bitrix\Rest\RestException("Only admin can send notifications to other channels", "USER_ID_ACCESS_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
				}
			}
			else
			{
				$users = Event::SHARED_CHANNEL;
			}
		}

		if (isset($params['MODULE_ID']))
		{
			$moduleId = $params['MODULE_ID'];
			if (preg_match("/[^a-z0-9._]/", $moduleId))
			{
				throw new \Bitrix\Rest\RestException("Module ID format error", "MODULE_ID_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$moduleId = 'application';
		}

		$command = $params['COMMAND'];
		if (!preg_match("/^[\d\w:_|\.\-]+$/", $command))
		{
			throw new \Bitrix\Rest\RestException("Command format error", "COMMAND_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($params['PARAMS']))
		{
			if (is_string($params['PARAMS']))
			{
				$params['PARAMS'] = \CUtil::JsObjectToPhp($params['PARAMS']);
			}

			$eventParams = $params['PARAMS'];
			if (!is_array($eventParams))
			{
				throw new \Bitrix\Rest\RestException("Params format error", "PARAMS_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$eventParams = [];
		}

		Event::add($users, array(
			'module_id' => $moduleId,
			'command' => $command,
			'params' => $eventParams,
		), $clientId);

		return true;
	}

	public static function applicationPushAdd($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			throw new \Bitrix\Rest\RestException("Send push notifications available only for application authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		if (!self::isAdmin())
		{
			throw new \Bitrix\Rest\RestException("You do not have access to send push notifications", "ACCESS_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$users = Array();
		if (is_string($params['USER_ID']))
		{
			$params['USER_ID'] = \CUtil::JsObjectToPhp($params['USER_ID']);
		}

		if (is_array($params['USER_ID']))
		{
			foreach ($params['USER_ID'] as $userId)
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$users[$userId] = $userId;
				}
			}
			$users = array_values($users);
		}
		else
		{
			$users = (int)$params['USER_ID'];
		}

		if (!$params['TEXT'] || $params['TEXT'] == '')
		{
			throw new \Bitrix\Rest\RestException("Text can't be empty", "TEXT_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \Bitrix\Rest\AppTable::getList(
			array(
				'filter' => array(
					'=CLIENT_ID' => $clientId
				),
				'select' => array(
					'CODE',
					'APP_NAME',
					'APP_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
				)
			)
		)->fetch();
		if (empty($result['APP_NAME']))
		{
			throw new \Bitrix\Rest\RestException("For send push-notification application name can't be empty", "EMPTY_APP_NAME", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$appName = $result['APP_NAME'];

		$text = $params['TEXT'];

		$avatar = '';
		if ($params['AVATAR'])
		{
			$parsedUrl = new Main\Web\Uri($params['AVATAR']);
			$params['AVATAR'] = $parsedUrl->getUri();
			if ($params['AVATAR'])
			{
				$avatar = $params['AVATAR'];
			}
		}

		Push::add($users,
		[
			'module_id' => 'im',
			'push' =>
			[
				'message' => $text,
				'advanced_params' =>
				[
					"group"=> $clientId,
					"avatarUrl"=> $avatar,
					"senderName" => $appName,
					"senderMessage" => $text,
				],
			]
		]);

		return true;
	}

	public static function configGet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if ($server->getAuthType() === \Bitrix\Rest\OAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Method not available for OAuth authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		global $USER;
		$guestMode = defined("PULL_USER_ID") && (int)PULL_USER_ID != 0;
		if($server->getAuthType() === GuestAuth::AUTH_TYPE && $guestMode)
		{
			$userId = (int)PULL_USER_ID;
		}
		else if ($USER->IsAuthorized())
		{
			$userId = $USER->getId();
		}
		else
		{
			throw new \Bitrix\Rest\RestException("Method not available for guest session at the moment.", "AUTHORIZE_ERROR", \CRestServer::STATUS_FORBIDDEN);
		}

		$configParams = Array();
		$configParams['USER_ID'] = $userId;
		$configParams['CACHE'] = $params['CACHE'] != 'N';
		$configParams['REOPEN'] = $params['REOPEN'] != 'N';
		$configParams['JSON'] = true;

		$config = \Bitrix\Pull\Config::get($configParams);
		if (!$config)
		{
			throw new \Bitrix\Rest\RestException("Push & Pull server is not configured", "SERVER_ERROR", \CRestServer::STATUS_INTERNAL);
		}

		return $config;
	}

	public static function watchExtend($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if(is_string($params['TAGS']))
		{
			$params['TAGS'] = \CUtil::JsObjectToPhp($params['TAGS']);
		}

		global $USER;
		$userId = $USER->GetID();

		return \CPullWatch::Extend($userId, array_values($params['TAGS']));
	}

	public static function counterTypesGet($params, $n, \CRestServer $server)
	{
		$types = \Bitrix\Pull\MobileCounter::getTypes();

		if (isset($params['USER_VALUES']) && $params['USER_VALUES'] == 'Y')
		{
			$config = \Bitrix\Pull\MobileCounter::getConfig();
			foreach ($types as $type => $value)
			{
				$types[$type]['VALUE'] = $config[$type];
			}
		}

		$result = Array();
		foreach ($types as $type)
		{
			$result[] = array_change_key_case($type, CASE_LOWER);
		}

		return $result;
	}

	public static function counterGet($params, $n, \CRestServer $server)
	{
		return \Bitrix\Pull\MobileCounter::get();
	}

	public static function counterConfigGet($params, $n, \CRestServer $server)
	{
		$result = Array();
		$config = \Bitrix\Pull\MobileCounter::getConfig();
		foreach ($config as $type => $value)
		{
			$result[] = Array(
				'type' => $type,
				'value' => $value,
			);
		}

		return $result;
	}

	public static function counterConfigSet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if(is_string($params['CONFIG']))
		{
			$params['CONFIG'] = \CUtil::JsObjectToPhp($params['CONFIG']);
		}

		if (!is_array($params['CONFIG']) || empty($params['CONFIG']))
		{
			throw new \Bitrix\Rest\RestException("New config is not specified", "CONFIG_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		\Bitrix\Pull\MobileCounter::setConfig($params['CONFIG']);

		return true;
	}


	public static function pushTypesGet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$userConfig = Array();
		$config = \Bitrix\Pull\Push::getTypes();

		$withUserValues = false;
		if (isset($params['USER_VALUES']) && $params['USER_VALUES'] == 'Y')
		{
			$withUserValues = true;
			$userConfig = \Bitrix\Pull\Push::getConfig();
		}

		$result = Array();
		foreach ($config as $moduleId => $module)
		{

			$types = Array();
			foreach ($module['TYPES'] as $typeId => $typeConfig)
			{
				if ($withUserValues)
				{
					$typeConfig['VALUE'] = $userConfig[$moduleId][$typeId];
				}
				$types[] = array_change_key_case($typeConfig, CASE_LOWER);
			}
			$module['TYPES'] = $types;

			$result[] = array_change_key_case($module, CASE_LOWER);
		}

		\Bitrix\Main\Type\Collection::sortByColumn($result, array('module_id' => array(SORT_STRING, SORT_ASC)));

		return $result;
	}

	public static function pushConfigGet($params, $n, \CRestServer $server)
	{
		$result = array();
		$config = \Bitrix\Pull\Push::getConfig();
		if (!$config)
		{
			$config = Array();
		}

		foreach ($config as $moduleId => $module)
		{
			foreach ($module as $typeId => $typeValue)
			{
				$result[] = Array(
					'module_id' => $moduleId,
					'type' => $typeId,
					'active' => $typeValue
				);
			}
		}
		return $result;
	}

	public static function pushConfigSet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if(is_string($params['CONFIG']))
		{
			$params['CONFIG'] = \CUtil::JsObjectToPhp($params['CONFIG']);
		}

		if (!is_array($params['CONFIG']) || empty($params['CONFIG']))
		{
			throw new \Bitrix\Rest\RestException("New config is not specified", "CONFIG_ERROR", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$newConfig = Array();
		foreach ($params['CONFIG'] as $config)
		{
			if (
				!isset($config['module_id']) || empty($config['module_id'])
				|| !isset($config['type']) || empty($config['type'])
				|| !isset($config['active'])
			)
			{
				continue;
			}

			$newConfig[$config['module_id']][$config['type']] = (bool)$config['active'];
		}

		\Bitrix\Pull\Push::setConfig($newConfig);

		return true;
	}

	public static function pushStatusGet($params, $n, \CRestServer $server)
	{
		return \Bitrix\Pull\Push::getStatus();
	}

	public static function pushStatusSet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$status = (bool)$params['ACTIVE'];
		\Bitrix\Pull\Push::setStatus($status);

		return true;
	}

	public static function pushSmartfilterStatusGet($params, $n, \CRestServer $server)
	{
		return \Bitrix\Pull\PushSmartfilter::getStatus();
	}

	public static function pushSmartfilterStatusSet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$status = (bool)$params['ACTIVE'];

		\Bitrix\Pull\PushSmartfilter::setStatus($status);

		return true;
	}

	public static function notImplemented($params, $n, \CRestServer $server)
	{
		throw new \Bitrix\Rest\RestException("Method isn't implemented yet", "NOT_IMPLEMENTED", \CRestServer::STATUS_NOT_FOUND);
	}

	private static function isAdmin()
	{
		global $USER;
		return (
			isset($USER)
			&& is_object($USER)
			&& (
				$USER->isAdmin()
				|| Main\Loader::includeModule('bitrix24') && \CBitrix24::isPortalAdmin($USER->getID())
			)
		);
	}
}
