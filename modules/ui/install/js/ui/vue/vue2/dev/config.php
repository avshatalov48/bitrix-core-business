<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'rel' => [
		'main.core.events',
		'main.core',
		'rest.client',
		'pull.client',
	],
	'skip_core' => false,
];