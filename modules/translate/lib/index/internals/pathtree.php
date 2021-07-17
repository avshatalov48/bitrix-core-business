<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Class PathTreeTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PARENT_ID int
 * <li> PATH_ID int
 * <li> DEPTH_LEVEL int
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PathTree_Query query()
 * @method static EO_PathTree_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PathTree_Result getById($id)
 * @method static EO_PathTree_Result getList(array $parameters = array())
 * @method static EO_PathTree_Entity getEntity()
 * @method static \Bitrix\Translate\Index\Internals\EO_PathTree createObject($setDefaultValues = true)
 * @method static \Bitrix\Translate\Index\Internals\EO_PathTree_Collection createCollection()
 * @method static \Bitrix\Translate\Index\Internals\EO_PathTree wakeUpObject($row)
 * @method static \Bitrix\Translate\Index\Internals\EO_PathTree_Collection wakeUpCollection($rows)
 */

class PathTreeTable extends DataManager
{
	use Index\Internals\BulkOperation;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_translate_path_tree';
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
			'PATH_ID' => array(
				'data_type' => 'integer',
			),
			'DEPTH_LEVEL' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'PATH' => array(
				'data_type' => '\Bitrix\Translate\Index\Internals\PathIndexTable',
				'reference' => array(
					'=this.PATH_ID' => 'ref.ID',
				),
				'join_type' => 'INNER',
			),
		);
	}



	/**
	 * Drop index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return void
	 */
	public static function purge(Translate\Filter $filter = null)
	{
		if (($filterOut = static::processFilter($filter)) !== false)
		{
			static::bulkDelete($filterOut);
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
					$filterOut['=%PATH.PATH'] = $value.'%';
				}
				elseif ($key === 'pathId')
				{
					$filterOut['=PATH_ID'] = $value;
				}
				elseif ($key === 'parentId')
				{
					$filterOut['=PARENT_ID'] = $value;
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
