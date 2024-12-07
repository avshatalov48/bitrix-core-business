<?php

namespace Bitrix\Location\StaticMap;

use Bitrix\Location\Geometry\Type\Point;

interface ISourceStaticMapService
{
	public function getStaticMap(Point $point, int $zoom, int $width, int $height): StaticMapResult;
}
