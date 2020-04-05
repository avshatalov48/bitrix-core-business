<?
global $DBType;

CModule::AddAutoloadClasses(
	'report',
	array(
		'CReport' => 'classes/general/report.php',
		'CReportHelper' => 'classes/general/report_helper.php',
		'BXUserException' => 'classes/general/report.php',
		'BXFormException' => 'classes/general/report.php',
		'Bitrix\Report\ReportTable' => 'lib/report.php',
		'\Bitrix\Report\ReportTable' => 'lib/report.php',
	)
);

CJSCore::RegisterExt('report', array(
	'js' => '/bitrix/js/report/js/report.js',
	'css' => '/bitrix/js/report/css/report.css',
	'lang' => BX_ROOT.'/modules/report/lang/'.LANGUAGE_ID.'/install/js/report.php',
	'rel' => array('core', 'popup', 'json', 'ajax')
));

CJSCore::RegisterExt('report_visual_constructor', array(
	'js' => array(
		'/bitrix/js/report/js/visualconstructor/core.js',
		'/bitrix/js/report/js/visualconstructor/circle.js',
		'/bitrix/js/report/js/visualconstructor/contenttypes.js',
		'/bitrix/js/report/js/visualconstructor/basefield.js',
		'/bitrix/js/report/js/visualconstructor/basereportconfigfield.js',
	),
	'css' => array(
		'/bitrix/js/report/css/visualconstructor/core.css',
	),
	'lang' => BX_ROOT.'/modules/report/lang/'.LANGUAGE_ID.'/install/js/visualconstructor/core.php',
	'rel' => array(
		'amcharts_pie',
		'amcharts_funnel',
		'amcharts_gauge',
		'amcharts_radar',
		'amcharts_serial',
		'amcharts_xy',
		'core',
		'json',
		'ajax',
		'report.dashboard'
	)
));

\Bitrix\Main\Page\Asset::getInstance()->addJsKernelInfo("report", array("/bitrix/js/report/js/report.js"));
