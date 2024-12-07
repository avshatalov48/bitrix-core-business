<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Service\StaticMapService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

class StaticMap extends Controller
{
	public function getAction(
		float $latitude,
		float $longitude,
		int $zoom,
		int $width,
		int $height
	): ?string
	{
		$requestMapResult = StaticMapService::getInstance()->getStaticMap(
			$latitude,
			$longitude,
			$zoom,
			$width,
			$height
		);

		if (!$requestMapResult->isSuccess())
		{
			$this->addErrors($requestMapResult->getErrors());

			return null;
		}

		$path = $requestMapResult->getPath();
		if (empty($path))
		{
			$this->addError(new Error('Service error'));

			return null;
		}

		return $path;
	}
}
