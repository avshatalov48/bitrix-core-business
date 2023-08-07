<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


class PaySystem extends Entity
{
	protected function getEntityTable()
	{
		return new \Bitrix\Sale\PaySystem\Manager;
	}

	protected function getAdditionalFilterFileds()
	{
		return ['=ENTITY_REGISTRY_TYPE'=>$this->getRegistryType()];
	}
}