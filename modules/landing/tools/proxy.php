<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$app = \Bitrix\Main\Application::getInstance();
$request = $app->getContext()->getRequest();

$proxy = new \Bitrix\Main\UI\ImageEditor\Proxy(
	$request->get('url'),
	['*']
);

$proxy->output();