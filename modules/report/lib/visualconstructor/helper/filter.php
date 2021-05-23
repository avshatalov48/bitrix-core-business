<?php
namespace Bitrix\Report\VisualConstructor\Helper;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\DateType;

/**
 * Class Filter
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Filter
{
	protected $filterId;

	/**
	 * Base filter constructor.
	 * @param string $boardId Board id for which construct filter.
	 * @return void.
	 */
	public function __construct($boardId)
	{
		$this->filterId = $this->createFilterId($boardId);
	}

	/**
	 * @return string
	 */
	public function getFilterId(): string
	{
		return $this->filterId;
	}

	/**
	 * @return array
	 */
	public function getFilterParameters()
	{
		return array(
			"FILTER_ID" => $this->filterId,
			"COMMON_PRESETS_ID" => $this->filterId . "_presets",
			"FILTER" => static::getFieldsList(),
			"DISABLE_SEARCH" => true,
			"FILTER_PRESETS" => static::getPresetsList(),
			"ENABLE_LABEL" => true,
			'ENABLE_LIVE_SEARCH' => false,
			'RESET_TO_DEFAULT_MODE' => true,
			'VALUE_REQUIRED_MODE' => false
		);
	}

	public static function createFilterId($boardId)
	{
		return 'report_board_' . $boardId . '_filter';
	}

	/**
	 * @return array
	 */
	public static function getFieldsList()
	{
		return array(
			'TIME_PERIOD' => array(
				'id' => 'TIME_PERIOD',
				'name' => Loc::getMessage('REPORTS_TIME_PERIOD'),
				'type' => 'date',
				'default' => true
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getPresetsList()
	{
		return  array(
			'filter_current_month' => array(
				'name' => Loc::getMessage('REPORT_BOARD_CURRENT_MONTH_PRESET_TITLE'),
				'fields' => array(
					'TIME_PERIOD_datesel' => DateType::CURRENT_MONTH,
				),
				'default' => true,
			),
		);
	}

	public function getStringList()
	{
		return [];
	}
}