<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Registry;

class Entity
{
	protected $params;
	protected $typeName;

	public function __construct($typeName, $params=[])
	{
		$this->typeName = $typeName;
		$this->params = $params;
	}

	protected function getAdditionalFilterFileds()
	{
		return [];
	}

	protected function getExternalNameField()
	{
		return 'XML_ID';
	}

	protected function getFields()
	{
		return ['ID', $this->getExternalNameField()];
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getFieldsByExternalId($xmlId)
	{
		if($xmlId === "")
		{
			return null;
		}

		$entity = $this->getEntityTable();

		if($r = $entity::getList([
			'select' => $this->getFields(),
			'filter' => array_merge(
				[
					$this->getExternalNameField() => $xmlId
				],
				$this->getAdditionalFilterFileds()
			),
			'order' => ['ID' => 'ASC']
			])->fetch()
		)
		{
			return $r['ID'];
		}

		return null;
	}

	public function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	protected function getEntityTable()
	{
		$instance = Registry::getInstance($this->getRegistryType());
		return $instance->get($this->typeName);
	}
}