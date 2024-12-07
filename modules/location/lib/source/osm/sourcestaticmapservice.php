<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\StaticMap\StaticMapResult;
use Bitrix\Main\Error;
use Bitrix\Location\StaticMap;

final class SourceStaticMapService extends StaticMap\SourceStaticMapService
{
	private Api\Api $api;

	public function __construct(OsmSource $source)
	{
		parent::__construct($source);

		$this->api = new Api\Api($source);
	}

	public function getStaticMap(Point $point, int $zoom, int $width, int $height): StaticMapResult
	{
		$result = new StaticMapResult();

		$staticMapResult = $this->api->getStaticMap(
			$point->getLat(),
			$point->getLng(),
			$zoom,
			$width,
			$height
		);

		if ($staticMapResult['status'] !== 'success')
		{
			$result->addError(new Error('Service request error'));

			return $result;
		}

		return $result
			->setContent($staticMapResult['data'])
			->setMimeType('image/png')
		;
	}
}
