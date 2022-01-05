<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

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
