<?php

namespace Bitrix\Location\StaticMap;

use Bitrix\Location\Entity\Source;
use Bitrix\Location\Geometry\Type\Point;

abstract class SourceStaticMapService implements ISourceStaticMapService
{
	protected Source $source;

	public function __construct(Source $source)
	{
		$this->source = $source;
	}

	abstract public function getStaticMap(
		Point $point,
		int $zoom,
		int $width,
		int $height
	): StaticMapResult;
}
