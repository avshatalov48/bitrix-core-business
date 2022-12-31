<?php

namespace Bitrix\Catalog\Integration\Report\Filter;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\DateType;

class StoreSaleFilter extends BaseFilter
{
	public const REPORT_INTERVAL_FIELD_NAME = 'REPORT_INTERVAL';

	protected static function getStoreFilterContext(): string
	{
		return 'report_store_sale_filter_stores';
	}

	protected static function getProductFilterContext(): string
	{
		return 'report_store_sale_filter_products';
	}

	public static function getFieldsList()
	{
		$fieldsList = parent::getFieldsList();
		$fieldsList[static::REPORT_INTERVAL_FIELD_NAME] = static::getReportIntervalField();

		return $fieldsList;
	}

	/**
	 * Returns report interval filter field settings
	 *
	 * @return array
	 */
	public static function getReportIntervalField(): array
	{
		return [
			'id' => static::REPORT_INTERVAL_FIELD_NAME,
			'name' => Loc::getMessage('SALE_FILTER_REPORT_INTERVAL_TITLE'),
			'default' => true,
			'type' => 'date',
			'required' => true,
			'valueRequired' => true,
			'exclude' => [
				DateType::NONE,
				DateType::CURRENT_DAY,
				DateType::CURRENT_WEEK,
				DateType::YESTERDAY,
				DateType::TOMORROW,
				DateType::PREV_DAYS,
				DateType::NEXT_DAYS,
				DateType::NEXT_WEEK,
				DateType::NEXT_MONTH,
				DateType::LAST_MONTH,
				DateType::LAST_WEEK,
				DateType::EXACT,
			]
		];
	}

	/**
	 * @return array
	 */
	public static function getPresetsList()
	{
		$presets = [];

		$presets['filter_last_month'] = [
			'name' => Loc::getMessage('SALE_FILTER_REPORT_INTERVAL_MONTH_PRESET_TITLE'),
			'fields' => [
				static::REPORT_INTERVAL_FIELD_NAME . '_datesel' => DateType::CURRENT_MONTH,
			],
			'default' => true,
		];

		return $presets;
	}
}
