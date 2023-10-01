<?php
namespace Bitrix\Sale\Internals;

use	Bitrix\Main\Entity\DataManager,
	Bitrix\Main\Entity\Validator;

/**
 * Class YandexSettingsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_YandexSettings_Query query()
 * @method static EO_YandexSettings_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_YandexSettings_Result getById($id)
 * @method static EO_YandexSettings_Result getList(array $parameters = [])
 * @method static EO_YandexSettings_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_YandexSettings createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_YandexSettings_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_YandexSettings wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_YandexSettings_Collection wakeUpCollection($rows)
 */
class YandexSettingsTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_yandex_settings';
	}

	public static function getMap()
	{
		return array(
			'SHOP_ID' => array(
				'required' => true,
				'primary' => true,
				'data_type' => 'integer',
			),
			'CSR' => array(
				'data_type' => 'text',
			),
			'SIGN' => array(
				'data_type' => 'text',
			),
			'CERT' => array(
				'data_type' => 'text',
			),
			'PKEY' => array(
				'data_type' => 'text',
			),
			'PUB_KEY' => array(
				'data_type' => 'text',
			)
		);
	}
}
