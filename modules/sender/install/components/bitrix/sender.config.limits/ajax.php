<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Transport\TimeLimiter;

if (!Bitrix\Main\Loader::includeModule('sender'))
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
$actions[] = Controller\Action::create('switchTrackMailOption')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();

		$canTrackMail = $request->get('canTrackMail') === "true";

		Option::set('sender', 'track_mails', $canTrackMail ? 'Y' : 'N');

		$content->set(array(
			'canTrackMail' => $canTrackMail,
		));
	}
);
$actions[] = Controller\Action::create('setSendingTimeOption')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$sendingStart =  $request->get('sendingStart');
		$sendingEnd = $request->get('sendingEnd');

		$sendingStart = $sendingStart ?? TimeLimiter::DEFAULT_SENDING_START;
		$sendingEnd = $sendingEnd ?? TimeLimiter::DEFAULT_SENDING_END;

		$checkTime =  $sendingStart;
		$sendingStart = strtotime($sendingStart) > strtotime($sendingEnd) ? $sendingEnd : $sendingStart;
		$sendingEnd = strtotime($checkTime) > strtotime($sendingEnd) ? $checkTime : $sendingEnd;

		Option::set('sender', 'sending_start', $sendingStart);
		Option::set('sender', 'sending_end', $sendingEnd);
	}
);
$actions[] = Controller\Action::create('switchSendingTimeOption')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();

		$sendingTime = $request->get('sendingTime') === "true";

		Option::set('sender', 'sending_time', $sendingTime ? 'Y' : 'N');

		$content->set(array(
			'sendingTime' => $sendingTime,
		));
	}
);
$actions[] = Controller\Action::create('switchMailConsentOption')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();

		$mailConsent = $request->get('mailConsent') === "true";

		Option::set('sender', 'mail_consent', $mailConsent ? 'Y' : 'N');

		$content->set(array(
			'mailConsent' => $mailConsent,
		));
	}
);
$checker = CommonAjax\Checker::getModifySettingsPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();