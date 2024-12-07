<?php

namespace Bitrix\Location\Source\Osm;

class NodeTypeMap
{
	private static $map = [
		'relation' => 'R',
		'way' => 'W',
		'node' => 'N',
	];

	public static function getShortNodeTypeCode(string $longNodeTypeCode): string
	{
		return self::$map[$longNodeTypeCode] ?? '';
	}
}
