<?php
namespace Bitrix\Rest;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Rest\UserField\Callback;
use Bitrix\Rest\Lang;

Loc::loadMessages(__FILE__);

/**
 * Class PlacementTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> APP_ID int optional
 * <li> PLACEMENT string(255) mandatory
 * <li> PLACEMENT_HANDLER string(255) mandatory
 * <li> TITLE string(255) optional
 * <li> COMMENT string(255) optional
 * <li> DATE_CREATE datetime optional
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Placement_Query query()
 * @method static EO_Placement_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Placement_Result getById($id)
 * @method static EO_Placement_Result getList(array $parameters = array())
 * @method static EO_Placement_Entity getEntity()
 * @method static \Bitrix\Rest\EO_Placement createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\EO_Placement_Collection createCollection()
 * @method static \Bitrix\Rest\EO_Placement wakeUpObject($row)
 * @method static \Bitrix\Rest\EO_Placement_Collection wakeUpCollection($rows)
 */
class PlacementTable extends Main\Entity\DataManager
{
	public const PREFIX_EVENT_ON_AFTER_ADD = 'onAfterPlacementAdd::';
	public const PREFIX_EVENT_ON_AFTER_DELETE = 'onAfterPlacementDelete::';

	public const DEFAULT_USER_ID_VALUE = 0;

	const PLACEMENT_DEFAULT = 'DEFAULT';

	const ERROR_PLACEMENT_NOT_FOUND = 'ERROR_PLACEMENT_NOT_FOUND';
	const ERROR_PLACEMENT_MAX_COUNT = 'ERROR_PLACEMENT_MAX_COUNT';
	const ERROR_PLACEMENT_USER_MODE = 'ERROR_PLACEMENT_USER_MODE';

	const CACHE_TTL = 86400;
	const CACHE_DIR = 'rest/placement';

	protected static $handlersListCache = [];
	private static $beforeDeleteList = [];

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_placement';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'APP_ID' => array(
				'data_type' => 'integer',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'PLACEMENT' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'PLACEMENT_HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
			),
			/**
			 * @deprecated
			 * Use LANG.GROUP_NAME
			 */
			'GROUP_NAME' => array(
				'data_type' => 'string',
			),
			'ICON_ID' => array(
				'data_type' => 'integer',
			),
			/**
			 * @deprecated
			 * Use LANG.TITLE
			 */
			'TITLE' => array(
				'data_type' => 'string',
			),
			/**
			 * @deprecated
			 * Use LANG.DESCRIPTION
			 */
			'COMMENT' => array(
				'data_type' => 'string',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
			),
			'ADDITIONAL' => array(
				'data_type' => 'string',
			),
			'OPTIONS' => new ArrayField('OPTIONS'),
			'REST_APP' => array(
				'data_type' => 'Bitrix\Rest\AppTable',
				'reference' => array('=this.APP_ID' => 'ref.ID'),
			),
			(new OneToMany(
				'LANG_ALL',
				\Bitrix\Rest\PlacementLangTable::class,
				'PLACEMENT'
			))->configureJoinType('left'),
		);
	}

	private static function getUserFilter($userId)
	{
		if (is_null($userId))
		{
			global $USER;
			if ($USER instanceof \CUser)
			{
				$userId = (int)$USER->getId();
			}
		}

		if ($userId > 0)
		{
			$result = [
				static::DEFAULT_USER_ID_VALUE,
				$userId,
			];
		}
		else
		{
			$result = static::DEFAULT_USER_ID_VALUE;
		}

		return $result;
	}

	/**
	 * Returns list of placement handlers. Use \Bitrix\Rest\PlacementTable::getHandlersList.
	 *
	 * @param string $placement Placement ID.
	 *
	 * @return Main\DB\Result
	 */
	public static function getHandlers($placement, int $userId = null)
	{
		$dbRes = static::getList(
			[
				'filter' => [
					'=PLACEMENT' => $placement,
					'=REST_APP.ACTIVE' => AppTable::ACTIVE,
					'=USER_ID' => static::getUserFilter($userId),
				],
				'select' => [
					'ID',
					'ICON_ID',
					'TITLE',
					'GROUP_NAME',
					'OPTIONS',
					'COMMENT',
					'APP_ID',
					'ADDITIONAL',
					'INSTALLED' => 'REST_APP.INSTALLED',
					'APP_NAME' => 'REST_APP.APP_NAME',
					'APP_ACCESS' => 'REST_APP.ACCESS',
				],
			]
		);

		return $dbRes;
	}

	/**
	 * Removes all application placement handlers.
	 *
	 * @param int $appId Application ID.
	 *
	 * @return Main\DB\Result
	 */
	public static function deleteByApp($appId)
	{
		PlacementLangTable::deleteByApp((int) $appId);
		$connection = Main\Application::getConnection();

		$res = static::getList(
			[
				'filter' => [
					'=APP_ID' => (int)$appId,
				],
				'select' => [
					'ID',
					'APP_ID',
					'PLACEMENT',
					'PLACEMENT_HANDLER',
					'ICON_ID',
					'ADDITIONAL',
					'OPTIONS',
				],
				'limit' => 1,
			]
		);
		$eventList = [];
		while ($placement = $res->fetch())
		{
			$eventList[] = new Event(
				'rest',
				static::PREFIX_EVENT_ON_AFTER_DELETE . $placement['PLACEMENT'],
				[
					'ID' => $placement['ID'],
					'PLACEMENT' => $placement['PLACEMENT'],
				]
			);
		}

		$queryResult = $connection->query("DELETE FROM ".static::getTableName()." WHERE APP_ID='".intval($appId)."'");

		foreach ($eventList as $event)
		{
			EventManager::getInstance()->send($event);
		}

		static::clearHandlerCache();

		return $queryResult;
	}

	/**
	 * Returns cached list of placement handlers.
	 *
	 * @param string $placement Placement ID
	 * @param bool $skipInstallCheck Optional flag to allow placement from apps with unfinished install
	 *
	 * @return array
	 */
	public static function getHandlersList($placement, $skipInstallCheck = false, int $userId = null)
	{
		if(!array_key_exists($placement, static::$handlersListCache))
		{
			static::$handlersListCache[$placement] = array();

			$cache = Main\Application::getInstance()->getManagedCache();
			if($cache->read(static::CACHE_TTL, static::getCacheId($placement), static::CACHE_DIR))
			{
				static::$handlersListCache = $cache->get(static::getCacheId($placement));
			}
			else
			{
				$res = static::getHandlers($placement, $userId);
				foreach ($res->fetchCollection() as $handler)
				{
					$id = $handler->getId();
					$app = $handler->getRestApp();
					$placementItem = [
						'ID' => $id,
						'APP_ID' => $handler->getAppId(),
						'USER_ID' => $handler->getUserId(),
						'ICON_ID' => $handler->getIconId(),
						'ADDITIONAL' => $handler->getAdditional(),
						'TITLE' => '',
						/**
						 * @deprecated
						 * Use DESCRIPTION
						 */
						'COMMENT' => '',
						'DESCRIPTION' => '',
						'GROUP_NAME' => '',
						'OPTIONS' => $handler->getOptions(),
						'INSTALLED' => $app->getInstalled(),
						'APP_NAME' => $app->getAppName(),
						'APP_ACCESS' => $app->getAccess(),
						'LANG_ALL' => [],
					];

					if ($placementItem['ICON_ID'] > 0 && ($file = \CFile::GetFileArray($placementItem['ICON_ID'])))
					{
						$placementItem['ICON'] = array_change_key_case($file, CASE_LOWER);
					}

					$handler->fillLangAll();
					if (!is_null($handler->getLangAll()))
					{
						foreach ($handler->getLangAll() as $lang)
						{
							$placementItem['LANG_ALL'][$lang->getLanguageId()] = [
								'TITLE' => $lang->getTitle(),
								'DESCRIPTION' => $lang->getDescription(),
								'GROUP_NAME' => $lang->getGroupName(),
							];
						}
					}
					static::$handlersListCache[$placement][] = $placementItem;
				}

				$cache->set(static::getCacheId($placement), static::$handlersListCache);
			}
		}

		$result = static::$handlersListCache[$placement];

		foreach($result as $key => $handler)
		{
			if(!$skipInstallCheck && $handler['INSTALLED'] === AppTable::NOT_INSTALLED)
			{
				unset($result[$key]);
			}
			elseif(
				$placement !== Api\UserFieldType::PLACEMENT_UF_TYPE
				&& !\CRestUtil::checkAppAccess($handler['APP_ID'], array(
					'ACCESS' => $handler['APP_ACCESS']
				)
				)
			)
			{
				unset($result[$key]);
			}
			else
			{
				$result[$key] = Lang::mergeFromLangAll($handler);
				if (empty($result[$key]['TITLE']))
				{
					$result[$key]['TITLE'] = static::getDefaultTitle($handler['ID']);
				}
			}
		}

		$result = array_values($result);

		return $result;
	}

	/**
	 * Return default placements title
	 * @param int $placementId
	 * @param null $language
	 *
	 * @return string|null
	 */
	public static function getDefaultTitle(int $placementId, $language = null): ?string
	{
		return Loc::getMessage(
			'REST_PLACEMENT_DEFAULT_TITLE',
			[
				'#ID#' => $placementId,
			],
			$language
		);
	}

	public static function clearHandlerCache()
	{
		$cache = Main\Application::getInstance()->getManagedCache();
		$cache->cleanDir(static::CACHE_DIR);
		static::$handlersListCache = array();
		if (defined('BX_COMP_MANAGED_CACHE'))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->clearByTag('intranet_menu_binding');
		}
	}

	public static function onBeforeUpdate(Main\Entity\Event $event)
	{
		$result = static::checkUniq($event);
		static::modifyFields($event, $result);
		return $result;
	}

	public static function onBeforeAdd(Main\Entity\Event $event)
	{
		$result = static::checkUniq($event, true);
		static::modifyFields($event, $result);
		return $result;
	}

	public static function onBeforeDelete(Main\Entity\Event $event)
	{
		$result = new Main\ORM\EventResult();
		$id = $event->getParameter('id');
		$id = (int)$id['ID'];
		$res = static::getList(
			[
				'filter' => [
					'=ID' => $id,
				],
				'select' => [
					'ID',
					'APP_ID',
					'PLACEMENT',
					'PLACEMENT_HANDLER',
					'ICON_ID',
					'ADDITIONAL',
					'OPTIONS',
				],
				'limit' => 1,
			]
		);
		if ($placement = $res->fetch())
		{
			static::$beforeDeleteList[$placement['ID']] = $placement;
			if ((int)$placement['ICON_ID'] > 0)
			{
				\CFile::Delete((int)$placement['ICON_ID']);
			}
		}
		PlacementLangTable::deleteByPlacement($id);

		return $result;
	}

	public static function onAfterAdd(Main\Entity\Event $event)
	{
		$fields = $event->getParameter('fields');
		if (!empty($fields['PLACEMENT']) && (int)$fields['APP_ID'] > 0)
		{
			$app = AppTable::getByClientId((int)$fields['APP_ID']);
			if ($app['ACTIVE'] === AppTable::ACTIVE && $app['INSTALLED'] === AppTable::INSTALLED)
			{
				$data = new Event(
					'rest',
					static::PREFIX_EVENT_ON_AFTER_ADD . $fields['PLACEMENT'],
					[
						'ID' => $event->getParameter('id'),
						'PLACEMENT' => $fields['PLACEMENT'],
					]
				);
				EventManager::getInstance()->send($data);
			}
		}

		static::clearHandlerCache();
	}

	public static function onAfterUpdate(Main\Entity\Event $event)
	{
		static::clearHandlerCache();
	}

	public static function onAfterDelete(Main\Entity\Event $event)
	{
		$id = $event->getParameter('id');
		$id = (int)$id['ID'];
		if ($id > 0)
		{
			$data = new Event(
				'rest',
				static::PREFIX_EVENT_ON_AFTER_DELETE . static::$beforeDeleteList[$id]['PLACEMENT'],
				[
					'ID' => $id,
					'PLACEMENT' => static::$beforeDeleteList[$id]['PLACEMENT'],
				]
			);
			unset(static::$beforeDeleteList[$id]);
			EventManager::getInstance()->send($data);
		}

		static::clearHandlerCache();
	}

	protected static function getCacheId($placement)
	{
		return 'rest_placement_list|'.$placement.'|'.LANGUAGE_ID;
	}

	protected static function checkUniq(Main\Entity\Event $event, $add = false)
	{
		$result = new Main\Entity\EventResult();
		$data = $event->getParameter('fields');

		$filter = array(
			'=APP_ID' => $data['APP_ID'],
			'=PLACEMENT' => $data['PLACEMENT'],
			'=PLACEMENT_HANDLER' => $data['PLACEMENT_HANDLER'],
		);

		if(!empty($data['ADDITIONAL']))
		{
			$filter = array(
				'LOGIC' => 'OR',
				array('=ADDITIONAL' => $data['ADDITIONAL']),
				$filter
			);
		}

		$dbRes = static::getList(array(
			'filter' => $filter,
			'select' => array('ID')
		));

		if($dbRes->fetch())
		{
			$result->addError(new Main\Entity\EntityError(
				"Handler already binded"
			));
		}
		elseif($add)
		{
			$result->modifyFields(array(
				"DATE_CREATE" => new Main\Type\DateTime(),
			));
		}

		return $result;
	}

	private static function modifyFields(Main\ORM\Event $event, Main\ORM\EventResult $result)
	{
		if ($result->getType() !== Main\Entity\EventResult::ERROR)
		{
			$fieldChanged = [];
			$data = array_merge($event->getParameter('fields'), $result->getModified());
			if (array_key_exists('ICON', $data))
			{
				if ($str = \CFile::CheckImageFile($data['ICON']))
				{
					$result->addError(new Main\ORM\Fields\FieldError(static::getEntity()->getField('ICON_ID'), $str));
				}
				else
				{
					\CFile::ResizeImage($data['ICON'], [
						'width' => Main\Config\Option::get('rest', 'icon_size', 100),
						'height' => Main\Config\Option::get('rest', 'icon_size', 100)]);
					$data['ICON']['MODULE_ID'] = 'rest';
					if ($id = $event->getParameter('id'))
					{
						$id = is_integer($id) ? $id : $id['ID'];
						if ($id > 0 && ($icon = PlacementTable::getById($id)->fetchObject()))
						{
							$data['ICON']['old_file'] = $icon->getIconId();
						}
					}
					if (\CFile::SaveForDB($data, 'ICON', 'rest/placementicon'))
					{
						$fieldChanged['ICON_ID'] = $data['ICON'];
					}
				}
				$result->unsetField('ICON');
			}

			if (array_key_exists('DESCRIPTION', $data))
			{
				$fieldChanged['COMMENT'] = $data['DESCRIPTION'];
				$result->unsetField('DESCRIPTION');
			}

			if (!empty($fieldChanged))
			{
				$result->modifyFields(array_merge($result->getModified(), $fieldChanged));
			}
		}

		return $result;
	}

}