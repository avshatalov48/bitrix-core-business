<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;

$region = Application::getInstance()->getLicense()->getRegion();
$allowWizard = ($region === 'ru' || $region === 'by' || $region === 'kz');

$arWizardDescription = [
	'NAME' => GetMessage('WSL_DESCR_TITLE'),
	'DESCRIPTION' => GetMessage('WSL_DESCR_DESCRIPTION'),
	'ICON' => '',
	'COPYRIGHT' => GetMessage('WSL_DESCR_COPY'),
	'VERSION' => '1.0.0',
	'STEPS' =>
		$allowWizard
		? ['Step1', 'Step2', 'Step5', 'Step3', 'Step4', 'FinalStep', 'CancelStep']
		: ['StepNotAllowed']
	,
];
