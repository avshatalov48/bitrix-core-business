<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Class PhraseTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILE_ID int mandatory
 * <li> PATH_ID int mandatory
 * <li> LANG_ID string(2) mandatory
 * <li> CODE string(255) mandatory
 * <li> PHRASE string optional
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PhraseIndex_Query query()
 * @method static EO_PhraseIndex_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PhraseIndex_Result getById($id)
 * @method static EO_PhraseIndex_Result getList(array $parameters = array())
 * @method static EO_PhraseIndex_Entity getEntity()
 * @method static \Bitrix\Translate\Index\PhraseIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Translate\Index\PhraseIndexCollection createCollection()
 * @method static \Bitrix\Translate\Index\PhraseIndex wakeUpObject($row)
 * @method static \Bitrix\Translate\Index\PhraseIndexCollection wakeUpCollection($rows)
 */

class PhraseIndexTable extends DataManager
{
	use Index\Internals\BulkOperation;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_translate_phrase';
	}

	/**
	 * Returns class of Object for current entity.
	 *
	 * @return string
	 */
	public static function getObjectClass(): string
	{
		return Index\PhraseIndex::class;
	}

	/**
	 * Returns class of Object collection for current entity.
	 *
	 * @return string
	 */
	public static function getCollectionClass(): string
	{
		return Index\PhraseIndexCollection::class;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		static $fields;
		if ($fields === null)
		{
			$fields = [
				'ID' => [
					'data_type' => 'integer',
					'primary' => true,
					'autocomplete' => true,
				],
				'FILE_ID' => [
					'data_type' => 'integer',
				],
				'PATH_ID' => [
					'data_type' => 'integer',
				],
				'LANG_ID' => [
					'data_type' => 'string',
				],
				'CODE' => [
					'data_type' => 'string',
				],
				'FILE' => [
					'data_type' => Index\Internals\FileIndexTable::class,
					'reference' => [
						'=this.FILE_ID' => 'ref.ID',
					],
					'join_type' => 'INNER',
				],
				'PATH' => [
					'data_type' => Index\Internals\PathIndexTable::class,
					'reference' => [
						'=this.PATH_ID' => 'ref.ID',
					],
					'join_type' => 'INNER',
				],
			];

			foreach (Translate\Config::getEnabledLanguages() as $langId)
			{
				$fields['PHRASE_' . mb_strtoupper($langId)] = [
					'data_type' => Index\Internals\PhraseFts::getFtsEntityClass($langId),
					'reference' => [
						'=this.ID' => 'ref.ID',
					],
					'join_type' => 'LEFT',
				];
			}
		}

		return $fields;
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
		$langs = isset($filter, $filter->langId) ? $filter->langId : Translate\Config::getEnabledLanguages();
		foreach ($langs as $langId)
		{
			$ftsClass = Index\Internals\PhraseFts::getFtsEntityClass($langId);
			$ftsClass::purge($filter);
		}

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
				elseif ($key === 'fileId')
				{
					$filterOut['=FILE_ID'] = $value;
				}
				elseif ($key === 'pathId')
				{
					$filterOut['=PATH_ID'] = $value;
				}
				elseif ($key === 'langId')
				{
					$filterOut['=LANG_ID'] = $value;
				}
				elseif ($key === 'indexedTime')
				{
					$filterOut['<FILE.INDEXED_TIME'] = $value;
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
