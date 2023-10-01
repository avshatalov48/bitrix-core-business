<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class FacebookConversionParamsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FacebookConversionParams_Query query()
 * @method static EO_FacebookConversionParams_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FacebookConversionParams_Result getById($id)
 * @method static EO_FacebookConversionParams_Result getList(array $parameters = [])
 * @method static EO_FacebookConversionParams_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_FacebookConversionParams createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_FacebookConversionParams_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_FacebookConversionParams wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_FacebookConversionParams_Collection wakeUpCollection($rows)
 */
class FacebookConversionParamsTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_facebook_conversion_params';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'EVENT_NAME' => [
				'data_type' => 'string',
				'required' => true,
			],
			'LID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'ENABLED' => [
				'data_type' => 'string',
				'required' => true,
			],
			'PARAMS' => [
				'data_type' => 'string',
				'required' => true,
			],
		];
	}
}
