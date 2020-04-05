<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => Array(
		'/bitrix/js/rest/client/rest.client.js',
	),
	'skip_core' => true,
	'rel' => array('promise'),
);