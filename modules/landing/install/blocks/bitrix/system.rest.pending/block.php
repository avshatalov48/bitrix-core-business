<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>

<?$APPLICATION->includeComponent(
	'bitrix:landing.rest.pending',
	'',
	[
		'BLOCK_ID' => 0,
		'DATA' => ''
	]
);?>