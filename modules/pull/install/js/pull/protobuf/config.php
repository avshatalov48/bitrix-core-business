<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => [
		'/bitrix/js/pull/protobuf/protobuf.js',
		'/bitrix/js/pull/protobuf/model.js'
	],
	'skip_core' => true,
);