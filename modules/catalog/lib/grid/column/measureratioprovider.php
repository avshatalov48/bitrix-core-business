<?php

namespace Bitrix\Catalog\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;

class MeasureRatioProvider extends CatalogProvider
{
	public function prepareColumns(): array
	{
		$result = [];

		$result['RATIO'] = $this->createColumn('RATIO', [
			'type' => Grid\Column\Type::FLOAT,
			'name' => Loc::getMessage('MEASURE_RATIO_COLUMN_PROVIDER_FIELD_RATIO'),
			'title' => Loc::getMessage('MEASURE_RATIO_COLUMN_PROVIDER_FIELD_TITLE_RATIO'),
			'necessary' => false,
			'editable' => $this->allowProductEdit(),
			'multiple' => false,
			'select' => [
				'RATIO',
				'PRODUCT_ID',
			],
			'sort' => false,
			'align' => 'right',
		]);

		return $result;
	}
}
