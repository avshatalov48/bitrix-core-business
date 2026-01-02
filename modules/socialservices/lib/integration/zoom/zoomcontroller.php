<?php

namespace Bitrix\SocialServices\Integration\Zoom;

use Bitrix\Main\License\UrlProvider;
use Bitrix\Main\Result;
use Bitrix\Main\Service\MicroService\BaseSender;

class ZoomController extends BaseSender
{
	protected function getServiceUrl(): string
	{
		$domain = (new UrlProvider())->getTechDomain();
		$defaultServiceUrl = 'https://zoom.' . $domain . '/';
		return defined("ZOOM_SERVICE_URL") ? ZOOM_SERVICE_URL : $defaultServiceUrl;
	}

	public function registerZoomUser(array $userData): Result
	{
		$sendData = [
			'externalUserId' => $userData['externalUserId'],
			'externalAccountId' => $userData['externalAccountId'],
			'socServLogin' => $userData['socServLogin'],
		];

		return $this->performRequest("zoomcontroller.portalreceiver.registerzoomuser", $sendData);
	}
}