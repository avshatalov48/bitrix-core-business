<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Service\GeoIp\Internal;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class GeonameTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Geoname_Query query()
 * @method static EO_Geoname_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Geoname_Result getById($id)
 * @method static EO_Geoname_Result getList(array $parameters = [])
 * @method static EO_Geoname_Entity getEntity()
 * @method static \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection createCollection()
 * @method static \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname wakeUpObject($row)
 * @method static \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection wakeUpCollection($rows)
 */
class GeonameTable extends Data\DataManager
{
	use Data\Internal\MergeTrait;

	public static function getTableName()
	{
		return 'b_geoname';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(),
			(new Fields\StringField('LANGUAGE_CODE'))
				->configurePrimary(),
			(new Fields\StringField('NAME')),
		];
	}

	public static function save(array $data): void
	{
		$existing = static::get(array_keys($data));

		foreach ($data as $geoid => $names)
		{
			if (is_array($names))
			{
				foreach ($names as $lang => $name)
				{
					if (!isset($existing[$geoid][$lang]) || $existing[$geoid][$lang] != $name)
					{
						$insert = [
							'ID' => $geoid,
							'LANGUAGE_CODE' => $lang,
							'NAME' => $name,
						];
						$update = [
							'NAME' => $name,
						];
						static::merge($insert, $update);
					}
				}
			}
		}
	}

	public static function get(array $ids): array
	{
		$existing = [];

		if (!empty($ids))
		{
			$query = static::query()
				->setSelect(['*'])
				->whereIn('ID', $ids)
				->exec()
			;

			while ($record = $query->fetch())
			{
				$existing[$record['ID']][$record['LANGUAGE_CODE']] = $record['NAME'];
			}
		}

		return $existing;
	}
}
