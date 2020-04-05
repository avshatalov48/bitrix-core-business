<?php
namespace Bitrix\Pull;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

if(!\Bitrix\Main\Loader::includeModule('rest'))
	return;

Loc::loadMessages(__FILE__);

class Rest extends \IRestService
{
	public static function onRestServiceBuildDescription()
	{
		return array(
			'pull' => array(
				'pull.application.config.get' =>  array('callback' => array(__CLASS__, 'applicationConfigGet'), 'options' => array('private' => true)),

				'pull.watch.extend' =>  array('callback' => array(__CLASS__, 'watchExtend'), 'options' => array()),
			),
			'pull_channel' => array(
				'pull.config.get' =>  array('callback' => array(__CLASS__, 'configGet'), 'options' => array()),
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

	public static function configGet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if (!method_exists('CRestServer', 'getAuthType'))
		{
			throw new \Bitrix\Rest\RestException("Please install rest 17.5.0 for use this method.", "NEED_UPDATE", \CRestServer::STATUS_INTERNAL);
		}

		if (!in_array($server->getAuthType(), Array(
			\Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE,
			\Bitrix\Rest\APAuth\Auth::AUTH_TYPE
		)))
		{
			throw new \Bitrix\Rest\RestException("Get access to Push & Pull config available only for session or webhook authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$configParams = Array();
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
}
