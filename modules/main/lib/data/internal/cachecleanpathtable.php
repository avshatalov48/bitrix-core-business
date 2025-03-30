<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Data\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class CacheTagTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CacheCleanPath_Query query()
 * @method static EO_CacheCleanPath_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CacheCleanPath_Result getById($id)
 * @method static EO_CacheCleanPath_Result getList(array $parameters = [])
 * @method static EO_CacheCleanPath_Entity getEntity()
 * @method static \Bitrix\Main\Data\Internal\EO_CacheCleanPath createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Data\Internal\EO_CacheCleanPath_Collection createCollection()
 * @method static \Bitrix\Main\Data\Internal\EO_CacheCleanPath wakeUpObject($row)
 * @method static \Bitrix\Main\Data\Internal\EO_CacheCleanPath_Collection wakeUpCollection($rows)
 */
class CacheCleanPathTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	public static function getTableName(): string
	{
		return 'b_cache_clean_path';
	}

	public static function getMap(): array
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureSize(8)
				->configureAutocomplete(),

			(new Fields\StringField('PREFIX'))->configureRequired(),
			(new Fields\DatetimeField('CLEAN_FROM')),
			(new Fields\IntegerField('CLUSTER_GROUP'))
		];
	}

	public static function isCacheable(): bool
	{
		return false;
	}
}