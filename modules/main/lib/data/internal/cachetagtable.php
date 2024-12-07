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
 * @method static EO_CacheTag_Query query()
 * @method static EO_CacheTag_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CacheTag_Result getById($id)
 * @method static EO_CacheTag_Result getList(array $parameters = [])
 * @method static EO_CacheTag_Entity getEntity()
 * @method static \Bitrix\Main\Data\Internal\EO_CacheTag createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Data\Internal\EO_CacheTag_Collection createCollection()
 * @method static \Bitrix\Main\Data\Internal\EO_CacheTag wakeUpObject($row)
 * @method static \Bitrix\Main\Data\Internal\EO_CacheTag_Collection wakeUpCollection($rows)
 */
class CacheTagTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	public static function getTableName(): string
	{
		return 'b_cache_tag';
	}

	public static function getMap(): array
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureSize(8)
				->configureAutocomplete(),

			(new Fields\StringField('SITE_ID'))
				->configureRequired()
				->configureSize(2),

			(new Fields\StringField('CACHE_SALT'))
				->configureRequired()
				->configureSize(4),

			(new Fields\StringField('RELATIVE_PATH'))
				->configureRequired()
				->configureSize(255),

			(new Fields\StringField('TAG'))
				->configureRequired()
				->configureSize(100),
		];
	}

	public static function cleanTable(): void
	{
		\Bitrix\Main\Application::getConnection()->query('TRUNCATE TABLE ' . static::getTableName());
	}

	public static function isCacheable(): bool
	{
		return false;
	}
}
