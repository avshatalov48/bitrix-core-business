<?php

namespace Bitrix\Im\V2\Rest;

class RestAdapter
{
	protected RestConvertible $entity;
	protected ?PopupData $additionalPopupData = null;

	public function __construct(RestConvertible $entity)
	{
		$this->entity = $entity;
	}

	public function toRestFormat(array $options = []): array
	{
		$popupData = new PopupData([]);

		if ($this->entity instanceof PopupDataAggregatable)
		{
			$popupData->merge($this->entity->getPopupData());
		}

		if (isset($this->additionalPopupData))
		{
			$popupData->merge($this->additionalPopupData);
		}

		$rest = $popupData->toRestFormat();

		if (empty($rest))
		{
			return $this->entity->toRestFormat($options);
		}

		$rest[$this->entity::getRestEntityName()] = $this->entity->toRestFormat($options);

		return $rest;
	}

	public function setAdditionalPopupData(PopupData $popupData): self
	{
		$this->additionalPopupData = $popupData;

		return $this;
	}
}