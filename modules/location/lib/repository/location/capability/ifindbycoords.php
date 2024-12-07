<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;

interface IFindByCoords
{
	public function findByCoords(
		float $lat,
		float $lng,
		int $zoom,
		string $languageId
	): ?Location;
}
