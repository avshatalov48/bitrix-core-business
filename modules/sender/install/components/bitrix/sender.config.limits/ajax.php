<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpRequest;

use Bitrix\Sender\Integration;

use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;

if (!Loader::includeModule('sender'))
{
	return;
}

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = Controller\Action::create('setLimitPercentage')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();

		$percentage = (int) $request->get('percentage');
		Integration\Bitrix24\Limitation\Limiter::setMonthlyLimitPercentage($percentage);
		$limiter = Integration\Bitrix24\Limitation\Limiter::getMonthly();

		$content->set(array(
			'percentage' => $limiter->getParameter('percentage'),
			'limit' => $limiter->getLimit(),
			'available' => $limiter->getAvailable(),
		));
	}
);
$checker = CommonAjax\Checker::getModifySettingsPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();