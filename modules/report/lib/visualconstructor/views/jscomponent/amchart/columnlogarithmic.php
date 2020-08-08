<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart;

use Bitrix\Main\Localization\Loc;

class ColumnLogarithmic extends Column
{
	const VIEW_KEY = "columnLogarithmic";
	const ENABLE_SORTING = false;

	/**
	 * Column view type constructor constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLabel(Loc::getMessage("REPORT_COLUMN_VIEW_LABEL_HISTOGRAM_LOGARITHMIC"));
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-bar.jpg');
	}

	public function getCompatibleViewTypes()
	{
		return [];
	}

	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $dataFromReport Parameters prepared in report handlers.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($dataFromReport)
	{
		$result = parent::handlerFinallyBeforePassToView($dataFromReport);
		$result['valueAxes'] = [
			[
				'logarithmic' => true,
				'integersOnly' => true,
				'minimum' => 1,
				'reversed' => false,
				'axisAlpha' => 0,
				'position' => 'left'
			]
		];

		return $result;
	}
}