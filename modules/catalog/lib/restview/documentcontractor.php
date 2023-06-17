<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\Integration\View\DataType;

final class DocumentContractor extends Base
{
	public function getFields(): array
	{
		return [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'DOCUMENT_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'ENTITY_TYPE_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'ENTITY_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
		];
	}
}