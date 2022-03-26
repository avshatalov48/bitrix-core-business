<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class DocumentElement extends Base
{
	/**
	 * Returns entity fields.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY,
				]
			],
			'DOC_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::IMMUTABLE,
				]
			],
			'STORE_FROM'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'STORE_TO'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'ELEMENT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::IMMUTABLE,
				]
			],
			'AMOUNT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'PURCHASING_PRICE'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
		];
	}
}