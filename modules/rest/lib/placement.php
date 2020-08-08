<?php
namespace Bitrix\Rest;

use Bitrix\Main;
use Bitrix\Rest\UserField\Callback;


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
 **/
class PlacementTable extends Main\Entity\DataManager
{
	const PLACEMENT_DEFAULT = 'DEFAULT';

	const ERROR_PLACEMENT_NOT_FOUND = 'ERROR_PLACEMENT_NOT_FOUND';
	const ERROR_PLACEMENT_MAX_COUNT = 'ERROR_PLACEMENT_MAX_COUNT';

	const CACHE_TTL = 86400;
	const CACHE_DIR = 'rest/placement';

	protected static $handlersListCache = array();

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
			'PLACEMENT' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'PLACEMENT_HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'GROUP_NAME' => array(
				'data_type' => 'string',
			),
			'TITLE' => array(
				'data_type' => 'string',
			),
			'COMMENT' => array(
				'data_type' => 'string',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
			),
			'ADDITIONAL' => array(
				'data_type' => 'string',
			),
			'REST_APP' => array(
				'data_type' => 'Bitrix\Rest\AppTable',
				'reference' => array('=this.APP_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns list of placement handlers. Use \Bitrix\Rest\PlacementTable::getHandlersList.
	 *
	 * @param string $placement Placement ID.
	 *
	 * @return Main\DB\Result
	 */
	public static function getHandlers($placement)
	{
		$dbRes = static::getList(array(
			'filter' => array(
				'=PLACEMENT' => $placement,
				'=REST_APP.ACTIVE' => AppTable::ACTIVE,
			),
			'select' => array(
				'ID', 'TITLE', 'GROUP_NAME', 'COMMENT', 'APP_ID', 'ADDITIONAL',
				'INSTALLED' => 'REST_APP.INSTALLED',
				'APP_NAME' => 'REST_APP.APP_NAME',
				'APP_ACCESS' => 'REST_APP.ACCESS',
			),
		));

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
		$connection = Main\Application::getConnection();

		$queryResult = $connection->query("DELETE FROM ".static::getTableName()." WHERE APP_ID='".intval($appId)."'");

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
	public static function getHandlersList($placement, $skipInstallCheck = false)
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
				$dbRes = static::getHandlers($placement);
				while($handler = $dbRes->fetch())
				{
					static::$handlersListCache[$placement][] = $handler;
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
		}

		$result = array_values($result);

		return $result;
	}

	public static function clearHandlerCache()
	{
		$cache = Main\Application::getInstance()->getManagedCache();
		$cache->cleanDir(static::CACHE_DIR);
		static::$handlersListCache = array();
	}

	public static function onBeforeUpdate(Main\Entity\Event $event)
	{
		return static::checkUniq($event);
	}

	public static function onBeforeAdd(Main\Entity\Event $event)
	{
		return static::checkUniq($event, true);
	}

	public static function onAfterAdd(Main\Entity\Event $event)
	{
		static::clearHandlerCache();
	}

	public static function onAfterUpdate(Main\Entity\Event $event)
	{
		static::clearHandlerCache();
	}

	public static function onAfterDelete(Main\Entity\Event $event)
	{
		static::clearHandlerCache();
	}

	protected static function getCacheId($placement)
	{
		return 'rest_placement_list|'.$placement.'|'.LANGUAGE_ID;
	}

	protected static function checkUniq(Main\Entity\Event $event, $add = false)
	{
		$result = new Main\Entity\EventResult();
		$data = $event->getParameter("fields");

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
}