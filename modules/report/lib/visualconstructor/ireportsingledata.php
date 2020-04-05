<?php

namespace Bitrix\Report\VisualConstructor;

/**
 * Interface IReportSingleData
 * @package Bitrix\Report\VisualConstructor
 */
interface IReportSingleData extends IReportData
{
	/**
	 * array with format
	 * array(
	 *     'title' => 'Some Title',
	 *     'value' => 0,
	 *     'targetUrl' => 'http://url.domain?params=param'
	 * )
	 * @return array
	 */
	public function getSingleData();


	/**
	 * @return array
	 */
	public function getSingleDemoData();
}