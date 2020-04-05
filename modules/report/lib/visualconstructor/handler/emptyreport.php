<?php

namespace Bitrix\Report\VisualConstructor\Handler;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\IReportMultipleBiGroupedData;
use Bitrix\Report\VisualConstructor\IReportMultipleData;
use Bitrix\Report\VisualConstructor\IReportMultipleGroupedData;
use Bitrix\Report\VisualConstructor\IReportSingleData;

/**
 * Class EmptyReport
 * @package Bitrix\Report\VisualConstructor\Handler
 */
class EmptyReport extends BaseReport implements IReportMultipleBiGroupedData, IReportMultipleGroupedData, IReportMultipleData, IReportSingleData
{

	/**
	 * BaseReport constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTitle(Loc::getMessage('EMPTY_REPORT_HANDLER'));
	}

	/**
	 * Collecting form elements for configuration form.
	 *
	 * @return void
	 */
	public function collectFormElements()
	{
		parent::collectFormElements();
		$groupingField = $this->getFormElement('groupingBy');
		if ($groupingField)
		{
			$this->removeFormElement($groupingField);
		}

		$calculateField = $this->getFormElement('calculate');
		if ($calculateField)
		{
			$this->removeFormElement($calculateField);
		}


	}

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
	public function getMultipleBiGroupedData()
	{
		return array();
	}

	/**
	 * @return mixed
	 */
	public function getMultipleBiGroupedDemoData()
	{
		return array();
	}

	/**
	 * array with format
	 * array(
	 *     'items' => array(
	 *            array(
	 *                'label' => 'Some Title',
	 *                'value' => 5,
	 *                'targetUrl' => 'http://url.domain?params=param'
	 *          )
	 *     )
	 * )
	 * @return array
	 */
	public function getMultipleData()
	{
		return array();
	}

	/**
	 * @return array
	 */
	public function getMultipleDemoData()
	{
		return array();
	}

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
	public function getMultipleGroupedData()
	{
		return array();
	}

	/**
	 * @return array
	 */
	public function getMultipleGroupedDemoData()
	{
		return array();
	}

	/**
	 * @return array
	 */
	public function getSingleDemoData()
	{
		return array();
	}


	/**
	 * @return array
	 */
	public function getSingleData()
	{
		$data = array();
		$colorFieldValue = $this->getFormElement('color');

		$data['title'] = $this->getFormElement('label')->getValue();
		$data['config']['color'] = $colorFieldValue ? $colorFieldValue->getValue() : '#ffffff';
		return $data;
	}

	/**
	 * Called every time when calculate some report result before passing some concrete handler, such us getMultipleData or getSingleData.
	 * Here you can get result of configuration fields of report, if report in widget you can get configurations of widget.
	 *
	 * @return mixed
	 */
	public function prepare()
	{
		return null;
	}
}