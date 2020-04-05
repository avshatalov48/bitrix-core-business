<?php

namespace Bitrix\Report\VisualConstructor\Handler;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\IReportSingleData;

/**
 * Report handler class where instance of class oparate with values of other reports in context of one widget.
 *
 * @package Bitrix\Report\VisualConstructor\Handler
 */
class Formula extends BaseReport implements IReportSingleData
{
	/**
	 * BaseReport constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTitle(Loc::getMessage('FORMULA_REPORT_HANDLER_TILE'));

	}

	/**
	 * Prepare/calculate data for report.
	 * @return mixed
	 */
	public function prepare()
	{
		return 'temp data';
	}

	/**
	 * array with format
	 * array(
	 *     'title' => 'Some Title',
	 *     'value' => 0,
	 *     'targetUrl' => 'http://url.domain?params=param'
	 * )
	 * @return array
	 */
	public function getSingleData()
	{
		return array(
			'title' => $this->getFormElement('label')->getValue(),
			'value' => $this->getCalculatedData(),
			'config' => array(
				'color' => $this->getFormElement('color')->getValue()
			)
		);
	}

	/**
	 * @return array
	 */
	public function getSingleDemoData()
	{
		return array(
			'value' => 7
		);
	}
}