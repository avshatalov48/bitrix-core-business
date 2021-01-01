<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'block' => [
		'name' => 'New CRM-Form',
		'section' => 'other',
		'dynamic' => false,
		'subtype' => 'form',
		'type' => ['page', 'store', 'smn'],
	],
	'cards' => [],
	'nodes' => [],
	'style' => [
		'block' => [
			'type' => ['display'],
		],
		'nodes' => [],
	],
];