<?php

namespace Bitrix\Location\Repository;

use Bitrix\Location\Entity;
use Bitrix\Location\Model\AreaTable;
use Bitrix\Main\ORM;
use Bitrix\Location\Model\EO_Area;

class AreaRepository
{
	/**
	 * @param array $args
	 * @return Entity\Area[]
	 */
	public function findByArguments(array $args): array
	{
		$result = [];

		$areasList = AreaTable::getList($args);
		while ($area = $areasList->fetchObject())
		{
			$result[] = Entity\Area\Converter\OrmConverter::convertFromOrm($area);
		}

		return $result;
	}

	/**
	 * @param string $type
	 * @param string|null $code
	 * @return Entity\Area|null
	 */
	public function findByTypeAndCode(string $type, ?string $code = null): ?Entity\Area
	{
		/** @var EO_Area $area */
		$area = AreaTable::getList(['filter' => [
			'=TYPE' => $type,
			'=CODE' => $code,
		]])->fetchObject();

		return $area ? Entity\Area\Converter\OrmConverter::convertFromOrm($area) : null;
	}

	/**
	 * @param Entity\Area $area
	 * @return ORM\Data\Result
	 */
	public function store(Entity\Area $area)
	{
		return Entity\Area\Converter\OrmConverter::convertToOrm($area)->save();
	}
}
