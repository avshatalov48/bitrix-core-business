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
