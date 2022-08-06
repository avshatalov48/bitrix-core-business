<?php
define('BX_PULL_SKIP_INIT', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->IncludeComponent(
	'bitrix:landing.pub',
	'',
	array(
	),
	null,
	array(
		'HIDE_ICONS' => 'Y'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');