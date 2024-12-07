<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arWizardDescription = [
	'NAME' => GetMessage('PGWIZ_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('PGWIZ_DESCR_DESCRIPTION'),
	'ICON' => '',
	'COPYRIGHT' => GetMessage('PGWIZ_DESCR_COPYRIGHT'),
	'VERSION' => '1.0.0',
	'STEPS' => [
		'CPgCheckStep',
		'CPgCreateDatabaseStep',
		'CPgUserStep',
		'CPgConnectionAddStep',
		'CPgConnectionStep',
		'CPgDatabaseCheckStep',
		'CPgCopyStep',
		'CPgSetupConnectionStep',
		'CPgFinalStep',
		'CPgCancelStep',
	],
];
