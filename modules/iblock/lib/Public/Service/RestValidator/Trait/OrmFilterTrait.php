<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Trait;

use Bitrix\Main\ORM\Entity;

trait OrmFilterTrait
{
	abstract public function setFields(array $fields);

	abstract public function setScalarFields(array $fields);

	abstract protected function getEntity(): Entity;

	protected function getUnprocessedFields(): array
	{
		return [];
	}

	protected function getProcessedFields(): array
	{
		$result = [];

		$unprocessedFields = $this->getUnprocessedFields();
		$entity = $this->getEntity();
		foreach ($entity->getScalarFields() as $field)
		{
			$name = $field->getColumnName();
			if (in_array($name, $unprocessedFields, true))
			{
				continue;
			}
			$result[] = $name;
		}

		return $result;
	}

	protected function getProcessedScalarFields(): array
	{
		return [];
	}

	protected function init(): void
	{
		$this->setFields($this->getProcessedFields());
		$this->setScalarFields($this->getProcessedScalarFields());
	}
}
