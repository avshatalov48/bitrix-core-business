<?php

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