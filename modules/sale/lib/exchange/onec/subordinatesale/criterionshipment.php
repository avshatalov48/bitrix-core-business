<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


use Bitrix\Sale\Exchange\Entity\SubordinateSale\EntityImportFactory;

class CriterionShipment extends \Bitrix\Sale\Exchange\OneC\CriterionShipment
{
	protected function entityFactoryCreate($typeId)
	{
		return EntityImportFactory::create($typeId);
	}
}