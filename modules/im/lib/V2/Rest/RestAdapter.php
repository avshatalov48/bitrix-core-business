<?php

namespace Bitrix\Im\V2\Rest;

use Bitrix\Im\V2\TariffLimit\DateFilterable;
use Bitrix\Im\V2\TariffLimit\Limit;
use Bitrix\Im\V2\TariffLimit\TariffLimitPopupItem;

class RestAdapter implements RestConvertible
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
		$this->processTariffLimit();
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

	public function addAdditionalPopupData(PopupData $popupData): self
	{
		if (!isset($this->additionalPopupData))
		{
			$this->additionalPopupData = $popupData;
		}
		else
		{
			$this->additionalPopupData->merge($popupData);
		}

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

	protected function processTariffLimit(): void
	{
		$limit = Limit::getInstance();
		$hasTariffLimit = false;
		$isLimitExceeded = false;

		foreach ($this->entities as $key => $entity)
		{
			if (!$entity instanceof DateFilterable)
			{
				continue;
			}

			$hasTariffLimit = true;

			if (!$limit->hasRestrictions())
			{
				break;
			}

			if (!$limit->shouldFilterByDate($entity))
			{
				continue;
			}

			$result = $entity->filterByDate($limit->getLimitDate());

			if ($result->wasFiltered())
			{
				$isLimitExceeded = true;
				$this->entities[$key] = $result->getResult();
			}
		}

		if ($hasTariffLimit)
		{
			$popupData = new PopupData([new TariffLimitPopupItem($isLimitExceeded)]);
			$this->addAdditionalPopupData($popupData);
		}
	}

	public static function getRestEntityName(): string
	{
		return 'result';
	}
}