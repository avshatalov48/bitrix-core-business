<?php

use Bitrix\Calendar\Sync\Managers\PushManager;
use Bitrix\Im\Common;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Sender\PushSender;
use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

const NOT_CHECK_PERMISSIONS = true;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$getPut = function ()
{
	$result = '';

	if ($putdata = fopen('php://input', 'w'))
	{
		while ($data = fread($putdata, 1024)) {
			$result .= $data;
		}
		fclose($putdata);
	}
	return $result ? Json::decode($result) : [];
};

// this logic must have for validate of url
(function (): void {
	if (!empty($_REQUEST['validationToken']))
	{
		$token = htmlspecialcharsbx($_REQUEST['validationToken']);

		die($token);
	}
})();

Loader::includeModule('calendar');

$data = $getPut();

if (!empty($data['value']) && is_array($data['value']))
{
	foreach ($data['value'] as $changeData)
	{
		if (!empty($changeData['subscriptionId']) && !empty($changeData['clientState']))
		{
			$resourceId = htmlspecialcharsbx($changeData['subscriptionId']);
			$channelId = htmlspecialcharsbx($changeData['clientState']);

			try
			{
				if (\Bitrix\Calendar\Synchronization\Public\Service\SynchronizationFeature::isOn())
				{
					ServiceLocator::getInstance()
						->get(PushSender::class)
						->sendOffice365PushMessage($channelId, $resourceId)
					;
				}
				else
				{
					Loader::includeModule('dav');

					$manager = new PushManager();

					$manager->handlePush($channelId, $resourceId);
				}
			}
			catch (Throwable)
			{}
		}
	}
}

Application::getInstance()->end();
