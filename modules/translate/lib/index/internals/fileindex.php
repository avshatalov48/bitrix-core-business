<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Class FileTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PATH_ID int mandatory
 * <li> LANG_ID string(2) mandatory
 * <li> FULL_PATH string(255) mandatory
 * <li> CHECK_HASH string optional
 * <li> PHRASE_COUNT int optional
 * <li> MISSING_COUNT int optional
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileIndex_Query query()
 * @method static EO_FileIndex_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_FileIndex_Result getById($id)
 * @method static EO_FileIndex_Result getList(array $parameters = array())
 * @method static EO_FileIndex_Entity getEntity()
 * @method static \Bitrix\Translate\Index\FileIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Translate\Index\FileIndexCollection createCollection()
 * @method static \Bitrix\Translate\Index\FileIndex wakeUpObject($row)
 * @method static \Bitrix\Translate\Index\FileIndexCollection wakeUpCollection($rows)
 */

class FileIndexTable extends DataManager
{
	use Index\Internals\BulkOperation;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_translate_file';
	}

	/**
	 * Returns class of Object for current entity.
	 *
	 * @return string
	 */
	public static function getObjectClass()
	{
		return Index\FileIndex::class;
	}

	/**
	 * Returns class of Object collection for current entity.
	 *
	 * @return string
	 */
	public static function getCollectionClass()
	{
		return Index\FileIndexCollection::class;
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
			'PATH_ID' => array(
				'data_type' => 'integer',
			),
			'LANG_ID' => array(
				'data_type' => 'string',
			),
			'FULL_PATH' => array(
				'data_type' => 'string',
			),
			'PHRASE_COUNT' => array(
				'data_type' => 'integer',
			),
			'INDEXED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'INDEXED_TIME' => array(
				'data_type' => 'datetime',
			),
			'PHRASE' => array(
				'data_type' => '\Bitrix\Translate\Index\Internals\PhraseIndexTable',
				'reference' => array(
					'=this.ID' => 'ref.FILE_ID',
					'=this.LANG_ID' => 'ref.LANG_ID',
				),
				'join_type' => 'LEFT',
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
				Index\Internals\FileDiffTable::purge($filter);
				Index\Internals\PhraseIndexTable::purge($filter);
			}

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
				elseif ($key === 'langId')
				{
					$filterOut['=LANG_ID'] = $value;
				}
				elseif ($key === 'fileId')
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
