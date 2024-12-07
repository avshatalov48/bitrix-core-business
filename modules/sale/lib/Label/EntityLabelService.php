<?php

namespace Bitrix\Sale\Label;

use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Internals\EntityLabelTable;
use Bitrix\Sale\Internals\EO_EntityLabel;

class EntityLabelService
{
	public function mark(Entity $entity, Label $label): void
	{
		$existingLabel = $this->getORMLabelForEntity($entity, $label->getName());
		if ($existingLabel)
		{
			$existingLabel->setLabelValue($label->getValue());
			$existingLabel->save();

			return;
		}

		$newLabel = EntityLabelTable::createObject();
		$newLabel
			->setEntityId($entity->getId())
			->setEntityType($entity::getRegistryEntity())
			->setLabelName($label->getName())
			->setLabelValue($label->getValue())
			->save()
		;
	}

	public function getLabelListForEntity(Entity $entity): array
	{
		$result = [];

		$labelList = EntityLabelTable::getList([
			'select' => ['LABEL_NAME', 'LABEL_VALUE'],
			'filter' => [
				'=ENTITY_ID' => $entity->getId(),
				'=ENTITY_TYPE' => $entity::getRegistryEntity(),
			]
		]);

		while ($row = $labelList->fetchObject())
		{
			$result[] = new Label($row->getLabelName(), $row->getLabelValue());
		}

		return $result;
	}

	public function getLabelForEntity(Entity $entity, string $labelName): ?Label
	{
		$label = $this->getORMLabelForEntity($entity, $labelName);

		if (!$label)
		{
			return null;
		}

		return new Label($labelName, $label->getLabelValue());
	}

	private function getORMLabelForEntity(Entity $entity, string $labelName): ?EO_EntityLabel
	{
		$label = EntityLabelTable::getList([
			'select' => ['LABEL_VALUE'],
			'filter' => [
				'=ENTITY_ID' => $entity->getId(),
				'=ENTITY_TYPE' => $entity::getRegistryEntity(),
				'=LABEL_NAME' => $labelName,
			],
			'limit' => 1,
		])->fetchObject();

		return $label ?? null;
	}
}
