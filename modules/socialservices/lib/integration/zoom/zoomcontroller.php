<?php

namespace Bitrix\SocialServices\Integration\Zoom;

use Bitrix\Main\Result;
use Bitrix\Main\Service\MicroService\BaseSender;

class ZoomController extends BaseSender
{
	protected const DEFAULT_SERVICE_URL = "https://zoom.bitrix.info/";

	protected function getServiceUrl(): string
	{
		return defined("ZOOM_SERVICE_URL") ? ZOOM_SERVICE_URL : static::DEFAULT_SERVICE_URL;
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