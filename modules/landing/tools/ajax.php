<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

if (
	isset($_GET['site']) &&
	preg_match('/^[a-z0-9_]+$/i', $_GET['site'])
)
{
	define('SITE_ID', $_GET['site']);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (\Bitrix\Main\Loader::includeModule('landing'))
{
	header('Content-Type: application/json');
	$ajaxResult = \Bitrix\Landing\PublicAction::ajaxProcessing();
	echo \Bitrix\Main\Web\Json::encode($ajaxResult);
}

\CMain::finalActions();