<?php
use \Bitrix\Landing\Zip;

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if (
	($siteId = $request->get('siteId')) &&
	\Bitrix\Main\Loader::includeModule('landing')
)
{
	if (Zip\Config::serviceEnabled())
	{
		Zip\Site::export($siteId);
		return true;
	}
}

\CMain::finalActions();