<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\File\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class FileVersionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileVersion_Query query()
 * @method static EO_FileVersion_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FileVersion_Result getById($id)
 * @method static EO_FileVersion_Result getList(array $parameters = [])
 * @method static EO_FileVersion_Entity getEntity()
 * @method static \Bitrix\Main\File\Internal\EO_FileVersion createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\File\Internal\EO_FileVersion_Collection createCollection()
 * @method static \Bitrix\Main\File\Internal\EO_FileVersion wakeUpObject($row)
 * @method static \Bitrix\Main\File\Internal\EO_FileVersion_Collection wakeUpCollection($rows)
 */
class FileVersionTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_file_version';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ORIGINAL_ID'))
				->configurePrimary(true),
			(new Fields\IntegerField('VERSION_ID')),
			(new Fields\ArrayField('META')),
		];
	}
}
