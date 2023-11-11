<?php

namespace Bitrix\Catalog\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;

class BarcodeProvider extends CatalogProvider
{
	public function prepareColumns(): array
	{
		$result = [];

		$result['BARCODE'] = $this->createColumn('BARCODE', [
			'type' => Grid\Column\Type::HTML,
			'name' => Loc::getMessage('BARCODE_COLUMN_PROVIDER_FIELD_BARCODE'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'select' => [
				'BARCODE',
				'PRODUCT_ID',
			],
			'sort' => false,
			'align' => 'right',
		]);

		return $result;
	}
}
