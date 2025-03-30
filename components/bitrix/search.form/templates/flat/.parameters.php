<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arTemplateParameters = [
	'USE_SUGGEST' => [
		'NAME' => GetMessage('TP_BSF_USE_SUGGEST'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
];
