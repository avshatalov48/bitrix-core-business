<?php

namespace Bitrix\Report\VisualConstructor;

/**
 * Interface IReportMultipleBiGroupedData
 * @package Bitrix\Report\VisualConstructor
 */
interface IReportMultipleBiGroupedData extends IReportData
{
	/**
	 * Array format for return this method:<br>
	 * array(
	 *      'items' => array(
	 *           array(
	 *              'firstGroupId' => 1,
	 *              'secondGroupId' => 2,
	 *              'title' => 'Some Title',
	 *              'value' => 1,
	 *              'targetUrl' => 'http://url.domain?params=param'
	 *          ),
	 *          array(
	 *              'firstGroupId' => 1,
	 *              'secondGroupId' => 2,
	 *              'title' => 'Some Title',
	 *              'value' => 2,
	 *              'targetUrl' => 'http://url.domain?params=param'
	 *          )
	 *      ),
	 *      'config' => array(
	 *          'firstGroupLabelsMap' => array(
	 *              '1' => array(
	 *                  'name' => 'Monday',
	 *                  'params' => array()
	 *              ),
	 *              '2' => array(
	 *                  'name' => 'Second Day of week',
	 *                  'params' => array()
	 *              ),
	 *          ),
	 *          'secondGroupLabelsMap' => array(
	 *              '01.01.1970' => array(
	 *                  'name' => 'Start of our internet evolution',
	 *                  'params' => array()
	 *              ),
	 *              '15' => array(
	 *                  'name' => 'Just a simple integer',
	 *                  'params' => array()
	 *              ),
	 *          ),
	 *          'reportTitle' => 'Some title for this report'
	 *      )
	 * )
	 * @return array
	 */
	public function getMultipleBiGroupedData();

	/**
	 * @return mixed
	 */
	public function getMultipleBiGroupedDemoData();
}