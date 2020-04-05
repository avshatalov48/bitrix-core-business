<?php

namespace Bitrix\Report\VisualConstructor\Config;


/**
 * Class Common
 * @package Bitrix\Report\VisualConstructor\Config
 */
final class Common
{
	const INTERNAL_MODULE_ID = 'report';
	const EVENT_REPORT_COLLECT = 'onReportsCollect';
	const EVENT_WIDGET_COLLECT = 'onWidgetCollect';
	const EVENT_DEFAULT_BOARDS_COLLECT = 'onDefaultBoardsCollect';
	const MODULE_NAME = 'report';
	const EVENT_CATEGORY_COLLECT = 'onReportCategoryCollect';
	const EVENT_VIEW_TYPE_COLLECT = 'onReportViewCollect';
	const EVENT_ANALYTIC_PAGE_COLLECT = 'onAnalyticPageCollect';
	const EVENT_ANALYTIC_PAGE_BATCh_COLLECT = 'onAnalyticPageBatchCollect';


	const UNIT_MEASUREMENT_COUNT = 'count';
	const UNIT_MEASUREMENT_PERCENTAGE = 'percentage';

	const MULTIPLE_REPORT_TYPE = 'multiple';
	const MULTIPLE_GROUPED_REPORT_TYPE = 'multipleGrouped';
	const MULTIPLE_BI_GROUPED_REPORT_TYPE = 'multipleBiGrouped';
	const SINGLE_REPORT_TYPE = 'single';

	const ONE_TO_ONE = 'oneToOne';
	const MANY_TO_ONE = 'manyToOne';
	const ONE_TO_MANY = 'oneToMany';
	const MANY_TO_MANY = 'manyToMany';


	public static $reportImplementationTypesMap = array(
		self::SINGLE_REPORT_TYPE => array(
			'interface' => 'Bitrix\Report\VisualConstructor\IReportSingleData',
			'method' => 'getSingleData',
			'demoMethod' => 'getSingleDemoData'
		),
		self::MULTIPLE_REPORT_TYPE => array(
			'interface' => 'Bitrix\Report\VisualConstructor\IReportMultipleData',
			'method' => 'getMultipleData',
			'demoMethod' => 'getMultipleDemoData'
		),
		self::MULTIPLE_GROUPED_REPORT_TYPE => array(
			'interface' => 'Bitrix\Report\VisualConstructor\IReportMultipleGroupedData',
			'method' => 'getMultipleGroupedData',
			'demoMethod' => 'getMultipleGroupedDemoData'
		),
		self::MULTIPLE_BI_GROUPED_REPORT_TYPE => array(
			'interface' => 'Bitrix\Report\VisualConstructor\IReportMultipleBiGroupedData',
			'method' => 'getMultipleBiGroupedData',
			'demoMethod' => 'getMultipleBiGroupedDemoData'
		),
	);
}