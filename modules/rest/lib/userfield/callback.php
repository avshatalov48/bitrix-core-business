<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 26.09.17
 * Time: 12:37
 */

namespace Bitrix\Rest\UserField;


use Bitrix\Main\EventManager;
use Bitrix\Rest\Api\UserFieldType;
use Bitrix\Rest\PlacementTable;

class Callback
{
	const USER_TYPE_ID_PREFIX = 'rest';

	protected static $descriptionCache = null;

	public static function __callStatic($handlerCode, $arguments)
	{
		$userTypeDescription = static::getUserTypeDescription($handlerCode);

		if($userTypeDescription === null)
		{
			static::unbindByCode($handlerCode);
		}

		return $userTypeDescription;
	}

	public static function bind($fields)
	{
		global $USER_FIELD_MANAGER;

		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'rest', __CLASS__, static::getUserTypeId($fields));

		$USER_FIELD_MANAGER->CleanCache();
		static::$descriptionCache = null;
		\Bitrix\Rest\PlacementTable::clearHandlerCache();
	}

	public static function unbind($fields)
	{
		static::unbindByCode(static::getUserTypeId($fields));
	}

	public static function unbindByCode($handlerCode)
	{
		global $USER_FIELD_MANAGER;

		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'rest', __CLASS__, $handlerCode);

		$USER_FIELD_MANAGER->CleanCache();
		static::$descriptionCache = null;
		\Bitrix\Rest\PlacementTable::clearHandlerCache();
	}

	protected static function getUserTypeDescription($placementHandlerCode)
	{
		if(static::$descriptionCache === null)
		{
			static::$descriptionCache = array();

			$placementHandlerList = PlacementTable::getHandlersList(UserFieldType::PLACEMENT_UF_TYPE, true);
			foreach($placementHandlerList as $placementInfo)
			{
				static::$descriptionCache[static::getUserTypeId($placementInfo)] = array(
					'USER_TYPE_ID' => static::getUserTypeId($placementInfo),
					'CLASS_NAME' => '\Bitrix\Rest\UserField\Type',
					'DESCRIPTION' => $placementInfo['TITLE'],
					'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
					'VIEW_CALLBACK' => array('\Bitrix\Rest\UserField\Type', 'getPublicView'),
					'EDIT_CALLBACK' => array('\Bitrix\Rest\UserField\Type', 'getPublicEdit'),
				);
			}
		}

		return array_key_exists($placementHandlerCode, static::$descriptionCache)
			? static::$descriptionCache[$placementHandlerCode]
			: null;
	}

	public static function getUserTypeId($userTypeInfo)
	{
		return static::USER_TYPE_ID_PREFIX.'_'.$userTypeInfo['APP_ID'].'_'.$userTypeInfo['ADDITIONAL'];
	}
}