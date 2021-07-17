<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Class PathTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PARENT_ID int
 * <li> PATH string(255)
 * <li> NAME string(255)
 * <li> MODULE_ID string(50) optional
 * <li> ASSIGNMENT string(50) optional
 * <li> DEPTH_LEVEL int optional
 * <li> SORT int optional
 * <li> IS_LANG bool optional
 * <li> IS_DIR bool optional
 * <li> INDEXED bool optional
 * <li> INDEXED_TIME datetime default 'CURRENT_TIMESTAMP'
 * <li> HAS_SETTINGS bool optional
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PathIndex_Query query()
 * @method static EO_PathIndex_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PathIndex_Result getById($id)
 * @method static EO_PathIndex_Result getList(array $parameters = array())
 * @method static EO_PathIndex_Entity getEntity()
 * @method static \Bitrix\Translate\Index\PathIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Translate\Index\PathIndexCollection createCollection()
 * @method static \Bitrix\Translate\Index\PathIndex wakeUpObject($row)
 * @method static \Bitrix\Translate\Index\PathIndexCollection wakeUpCollection($rows)
 */

class PathIndexTable extends DataManager
{
	use Index\Internals\BulkOperation;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_translate_path';
	}

	/**
	 * Returns class of Object for current entity.
	 *
	 * @return string
	 */
	public static function getObjectClass()
	{
		return Index\PathIndex::class;
	}

	/**
	 * Returns class of Object collection for current entity.
	 *
	 * @return string
	 */
	public static function getCollectionClass()
	{
		return Index\PathIndexCollection::class;
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
			'PARENT_ID' => array(
				'data_type' => 'integer',
			),
			'PATH' => array(
				'data_type' => 'string',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
			),
			'ASSIGNMENT' => array(
				'data_type' => 'enum',
				'values' => Translate\ASSIGNMENT_TYPES,
			),
			'DEPTH_LEVEL' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'SORT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'IS_LANG' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'IS_DIR' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'OBLIGATORY_LANGS' => array(
				'data_type' => 'string',
			),
			'INDEXED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'INDEXED_TIME' => array(
				'data_type' => 'datetime',
			),
			'FILE' => array(
				'data_type' => '\Bitrix\Translate\Index\Internals\FileIndexTable',
				'reference' => array(
					'=this.ID' => 'ref.PATH_ID'
				),
				'join_type' => 'LEFT',
			),
			'ANCESTORS' => array(
				'data_type' => '\Bitrix\Translate\Index\Internals\PathTreeTable',
				'reference' => array(
					'=this.ID' => 'ref.PARENT_ID',
				),
				'join_type' => 'INNER',
			),
			'DESCENDANTS' => array(
				'data_type' => '\Bitrix\Translate\Index\Internals\PathTreeTable',
				'reference' => array(
					'=this.ID' => 'ref.PATH_ID',
				),
				'join_type' => 'INNER',
			),
		);
	}



	/**
	 * @param ORM\Event $event Triggered ORM event.
	 *
	 * @return ORM\EventResult|void
	 */
	public static function onAfterAdd(ORM\Event $event)
	{
		$result = new ORM\EventResult();
		$primary = $event->getParameter('primary');
		$data = $event->getParameter('fields');

		if (isset($primary['ID'], $data['PARENT_ID']))
		{
			$nodeId = $primary['ID'];
			$parentId = $data['PARENT_ID'];
			$tableName = PathTreeTable::getTableName();
			$connection = Main\Application::getConnection();
			$connection->query("
				INSERT INTO {$tableName} (PARENT_ID, PATH_ID, DEPTH_LEVEL)
				SELECT PARENT_ID, '{$nodeId}', DEPTH_LEVEL + 1 FROM {$tableName} WHERE PATH_ID = '{$parentId}'
				UNION ALL 
				SELECT '{$nodeId}', '{$nodeId}', 0
			");
		}

		return $result;
	}

	/**
	 * @param ORM\Event $event Triggered ORM event.
	 *
	 * @return ORM\EventResult|void
	 */
	public static function onAfterDelete(ORM\Event $event)
	{
		$result = new ORM\EventResult();
		$primary = $event->getParameter('primary');

		if (isset($primary['ID']))
		{
			$nodeId = $primary['ID'];
			$tableName = PathTreeTable::getTableName();
			$connection = Main\Application::getConnection();
			$connection->query("DELETE FROM {$tableName} WHERE PATH_ID = '{$nodeId}'");
		}

		return $result;
	}

	/**
	 * Drop index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param bool $recursively Drop index recursively.
	 *
	 * @return void
	 */
	public static function purge(Translate\Filter $filter = null, $recursively = true)
	{
		if (($filterOut = static::processFilter($filter)) !== false)
		{
			if ($recursively)
			{
				if (!isset($filter, $filter->langId))
				{
					Index\Internals\PathTreeTable::purge($filter);
				}
				Index\Internals\FileIndexTable::purge($filter);
			}

			if (!isset($filter, $filter->langId))
			{
				static::bulkDelete($filterOut);
			}
		}
	}

	/**
	 * Processes filter params to convert them into orm type.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return array|bool
	 */
	public static function processFilter(Translate\Filter $filter = null)
	{
		$filterOut = array();

		if ($filter !== null && ($filter instanceof Translate\Filter || $filter instanceof \Traversable))
		{
			foreach ($filter as $key => $value)
			{
				if (empty($value) && $value !== '0')
				{
					continue;
				}

				if ($key === 'path')
				{
					$filterOut['=%PATH'] = $value.'%';
				}
				elseif ($key === 'pathId')
				{
					$filterOut['=ID'] = $value;
				}
				elseif ($key === 'indexedTime')
				{
					$filterOut['<INDEXED_TIME'] = $value;
				}
				else
				{
					if (static::getEntity()->hasField(trim($key, '<>!=@~%*')))
					{
						$filterOut[$key] = $value;
					}
				}
			}
		}

		return $filterOut;
	}
}
