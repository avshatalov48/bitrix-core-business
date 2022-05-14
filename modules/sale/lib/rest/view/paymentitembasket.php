<?php

namespace Bitrix\Sale\Rest\View;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class PaymentItemBasket extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'PAYMENT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'BASKET_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'QUANTITY'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'DATE_INSERT'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::READONLY]
			]
		];
	}

	protected function getRewriteFields(): array
	{
		return [
			'BASKET_ID'=>[
				'REFERENCE_FIELD'=>'ENTITY_ID'
			]
		];
	}
}