<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Store extends Base
{

	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'TITLE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'ADDRESS'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'DESCRIPTION'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'GPS_N'=>[
				'TYPE'=>DataType::TYPE_FLOAT
			],
			'GPS_S'=>[
				'TYPE'=>DataType::TYPE_FLOAT
			],
			'IMAGE_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'LOCATION_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'DATE_MODIFY'=>[
				'TYPE'=>DataType::TYPE_DATETIME
			],
			'DATE_CREATE'=>[
				'TYPE'=>DataType::TYPE_DATETIME
			],
			'USER_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'MODIFIED_BY'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'PHONE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'SCHEDULE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'EMAIL'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'ISSUING_CENTER'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'SHIPPING_CENTER'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'SITE_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
		];
	}
}