<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'PAGE' => [
			'PARENT' => 'URL_TEMPLATES',
			'NAME' => GetMessage('SEARCH_FORM_PAGE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '#SITE_DIR#search/index.php',
		],
	],
];
