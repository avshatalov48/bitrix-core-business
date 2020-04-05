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
