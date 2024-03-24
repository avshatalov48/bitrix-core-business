<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arWizardDescription = [
	'NAME' => GetMessage('UTFWIZ_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('UTFWIZ_DESCR_DESCRIPTION'),
	'ICON' => '',
	'COPYRIGHT' => GetMessage('UTFWIZ_DESCR_COPYRIGHT'),
	'VERSION' => '1.0.5',
	'STEPS' => [
		'CUtf8BackupWarningStep',
		'CUtf8CheckStep',
		'CUtf8DatabaseCheckStep',
		'CUtf8DatabaseConvertStep',
		'CUtf8DatabaseConnectionStep',
		'CUtf8SerializeFixStep',
		'CUtf8FileConvertStep',
		'CUtf8CacheResetStep',
		'CUtf8FinalStep',
		'CUtf8CancelStep',
	],
];
