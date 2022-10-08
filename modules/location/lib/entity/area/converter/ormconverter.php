<?php

namespace Bitrix\Location\Entity\Area\Converter;

use Bitrix\Location\Entity\Area;
use Bitrix\Location\Geometry\Converter\Manager;
use Bitrix\Location\Model\AreaTable;
use Bitrix\Location\Model\EO_Area;

final class OrmConverter
{
	/**
	 * @param Area $area
	 * @return EO_Area
	 */
	public static function convertToOrm(Area $area): EO_Area
	{
		return AreaTable::createObject()
			->setType($area->getType())
			->setCode($area->getCode())
			->setSort($area->getCode())
			->setGeometry(
				Manager::makeConverter(Manager::FORMAT_GEOJSON)->write(
					$area->getGeometry()
				)
			);
	}

	/**
	 * @param EO_Area $area
	 * @return Area
	 */
	public static function convertFromOrm(EO_Area $area): Area
	{
		return (new Area())
			->setType($area->getType())
			->setCode($area->getCode())
			->setSort($area->getSort())
			->setGeometry(
				Manager::makeConverter(Manager::FORMAT_GEOJSON)->read(
					$area->getGeometry()
				)
			);
	}
}
