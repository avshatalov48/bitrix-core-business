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
	public static function getTableName(): string
	{
		return 'b_translate_file';
	}

	/**
	 * Returns class of Object for current entity.
	 *
	 * @return string
	 */
	public static function getObjectClass(): string
	{
		return Index\FileIndex::class;
	}

	/**
	 * Returns class of Object collection for current entity.
	 *
	 * @return string
	 */
	public static function getCollectionClass(): string
	{
		return Index\FileIndexCollection::class;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'PATH_ID' => [
				'data_type' => 'integer',
			],
			'LANG_ID' => [
				'data_type' => 'string',
			],
			'FULL_PATH' => [
				'data_type' => 'string',
			],
			'PHRASE_COUNT' => [
				'data_type' => 'integer',
			],
			'INDEXED' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
				'default_value' => 'N',
			],
			'INDEXED_TIME' => [
				'data_type' => 'datetime',
			],
			'PATH' => [
				'data_type' => Index\Internals\PathIndexTable::class,
				'reference' => [
					'=this.PATH_ID' => 'ref.ID',
				],
				'join_type' => 'INNER',
			],
		];
	}


	/**
	 * Drop index.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 *
	 * @return void
	 */
	public static function purge(?Translate\Filter $filter = null): void
	{
		Index\Internals\PhraseIndexTable::purge($filter);

		$filterOut = static::processFilter($filter);

		static::bulkDelete($filterOut);
	}

	/**
	 * Processes filter params to convert them into orm type.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 *
	 * @return array
	 */
	public static function processFilter(?Translate\Filter $filter = null): array
	{
		$filterOut = [];

		if ($filter !== null)
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
