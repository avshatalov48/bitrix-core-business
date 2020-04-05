<?php
namespace Bitrix\Report\VisualConstructor;

/**
 * Interface IReportMultipleGroupedData
 * @package Bitrix\Report\VisualConstructor
 */
interface IReportMultipleGroupedData extends IReportData
{
	/**
	 * Array format for return this method:<br>
	 * array(
	 *      'items' => array(
	 *           array(
	 *              'groupBy' => 01.01.1970 or 15 etc.
	 *              'title' => 'Some Title',
	 *              'value' => 1,
	 *              'targetUrl' => 'http://url.domain?params=param'
	 *          ),
	 *          array(
	 *              'groupBy' => 01.01.1970 or 15 etc.
	 *              'title' => 'Some Title',
	 *              'value' => 2,
	 *              'targetUrl' => 'http://url.domain?params=param'
	 *          )
	 *      ),
	 *      'config' => array(
	 *          'groupsLabelMap' => array(
	 *              '01.01.1970' => 'Start of our internet evolution'
	 *              '15' =>  'Just a simple integer'
	 *          ),
	 *          'reportTitle' => 'Some title for this report'
	 *      )
	 * )
	 * @return array
	 */
	public function getMultipleGroupedData();

	/**
	 * @return array
	 */
	public function getMultipleGroupedDemoData();

}