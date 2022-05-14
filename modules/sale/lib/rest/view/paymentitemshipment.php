<?php

namespace Bitrix\Sale\Rest\View;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class PaymentItemShipment extends Base
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
			'SHIPMENT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
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
			'SHIPMENT_ID'=>[
				'REFERENCE_FIELD'=>'ENTITY_ID'
			]
		];
	}
}