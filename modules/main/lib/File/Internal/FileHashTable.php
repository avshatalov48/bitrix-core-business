<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Main\File\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query;

/**
 * Class FileHashTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileHash_Query query()
 * @method static EO_FileHash_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FileHash_Result getById($id)
 * @method static EO_FileHash_Result getList(array $parameters = [])
 * @method static EO_FileHash_Entity getEntity()
 * @method static \Bitrix\Main\File\Internal\EO_FileHash createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\File\Internal\EO_FileHash_Collection createCollection()
 * @method static \Bitrix\Main\File\Internal\EO_FileHash wakeUpObject($row)
 * @method static \Bitrix\Main\File\Internal\EO_FileHash_Collection wakeUpCollection($rows)
 */
class FileHashTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_file_hash';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("FILE_ID"))
				->configurePrimary(true),

			(new Fields\IntegerField("FILE_SIZE")),

			(new Fields\StringField("FILE_HASH")),

			(new Fields\Relations\Reference(
				'FILE',
				\Bitrix\Main\FileTable::class,
				Query\Join::on('this.FILE_ID', 'ref.ID')
			))
				->configureJoinType(Query\Join::TYPE_INNER),
		];
	}
}
