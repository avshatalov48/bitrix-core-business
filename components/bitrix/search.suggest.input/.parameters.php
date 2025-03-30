<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'NAME' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BSSI_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'q',
		],
		'VALUE' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BSSI_VALUE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
		'INPUT_SIZE' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BSSI_INPUT_SIZE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '40',
		],
		'DROPDOWN_SIZE' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BSSI_DROPDOWN_SIZE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '10',
		],
	],
];
