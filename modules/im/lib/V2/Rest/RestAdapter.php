<?php

namespace Bitrix\Im\V2\Rest;

class RestAdapter
{
	/**
	 * @var array<RestConvertible>
	 */
	protected array $entities = [];
	protected ?PopupData $additionalPopupData = null;

	public function __construct(RestConvertible ...$entities)
	{
		$this->entities = $entities ?? [];
	}

	public function toRestFormat(array $options = []): array
	{
		$popupData = new PopupData([]);

		foreach ($this->entities as $entity)
		{
			if ($entity instanceof PopupDataAggregatable)
			{
				$popupData->merge($entity->getPopupData($options['POPUP_DATA_EXCLUDE'] ?? []));
			}
		}

		if (isset($this->additionalPopupData))
		{
			$popupData->merge($this->additionalPopupData);
		}

		$rest = $popupData->toRestFormat($options);

		if (empty($rest))
		{
			if (count($this->entities) === 1)
			{
				return $this->entities[0]->toRestFormat($options);
			}
		}

		foreach ($this->entities as $entity)
		{
			$rest[$entity::getRestEntityName()] = $entity->toRestFormat($options);
		}

		return $rest;
	}

	public function setAdditionalPopupData(PopupData $popupData): self
	{
		$this->additionalPopupData = $popupData;

		return $this;
	}

	public function addEntities(RestConvertible ...$entities): self
	{
		foreach ($entities as $entity)
		{
			$this->entities[] = $entity;
		}

		return $this;
	}
}