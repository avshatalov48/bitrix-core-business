<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Class PathCacheTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PATH string(255)
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PathLang_Query query()
 * @method static EO_PathLang_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PathLang_Result getById($id)
 * @method static EO_PathLang_Result getList(array $parameters = array())
 * @method static EO_PathLang_Entity getEntity()
 * @method static \Bitrix\Translate\Index\Internals\EO_PathLang createObject($setDefaultValues = true)
 * @method static \Bitrix\Translate\Index\Internals\EO_PathLang_Collection createCollection()
 * @method static \Bitrix\Translate\Index\Internals\EO_PathLang wakeUpObject($row)
 * @method static \Bitrix\Translate\Index\Internals\EO_PathLang_Collection wakeUpCollection($rows)
 */

class PathLangTable extends DataManager
{
	use Index\Internals\BulkOperation;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_translate_path_lang';
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
			'PATH' => array(
				'data_type' => 'string',
			),
		);
	}

	/**
	 * Drops index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return void
	 */
	public static function purge(Translate\Filter $filter = null)
	{
		$relPath = isset($filter, $filter->path) ? $filter->path : '';

		if (!empty($relPath))
		{
			$relPath = rtrim($relPath, '/');

			static::bulkDelete(array('=%PATH' => $relPath .'%'));
		}
		else
		{
			static::bulkDelete();
		}
	}
}
