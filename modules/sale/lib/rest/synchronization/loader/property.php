<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


class Property extends Entity
{
	protected function getAdditionalFilterFileds()
	{
		return ['=ENTITY_REGISTRY_TYPE'=>$this->getRegistryType()];
	}
}