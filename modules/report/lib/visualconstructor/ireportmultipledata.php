<?php
namespace Bitrix\Report\VisualConstructor;

/**
 * Interface IReportSingleData
 * @package Bitrix\Report\VisualConstructor
 */
interface IReportMultipleData extends IReportData
{
	/**
	 * array with format
	 * array(
	 *     'items' => array(
	 *     	    array(
	 *     		    'label' => 'Some Title',
	 *     		    'value' => 5,
	 *     		    'targetUrl' => 'http://url.domain?params=param'
	 *          )
	 *     )
	 * )
	 * @return array
	 */
	public function getMultipleData();

	/**
	 * @return array
	 */
	public function getMultipleDemoData();

}