<?php

namespace Bitrix\Im\V2\Rest;

class PopupData
{
	/**
	 * @var PopupDataItem[]
	 */
	protected array $popupItems = [];

	/**
	 * @param PopupDataItem[] $popupDataItems
	 * @param string[]|PopupDataItem[] $excludedList
	 */
	public function __construct(array $popupDataItems, array $excludedList = [])
	{
		$this->popupItems = $popupDataItems;
		$this->filterItems($excludedList);
	}

	public function merge(self $popupData): self
	{
		foreach ($popupData->popupItems as $popupItem)
		{
			$this->mergeItem($popupItem);
		}

		return $this;
	}

	public function add(PopupDataItem $item): self
	{
		$this->mergeItem($item);

		return $this;
	}

	public function mergeFromEntity(RestConvertible $entity, array $excludedList = []): self
	{
		if ($entity instanceof PopupDataAggregatable)
		{
			return $this->merge($entity->getPopupData($excludedList));
		}

		return $this;
	}

	public function toRestFormat(): array
	{
		$result = [];

		foreach ($this->popupItems as $item)
		{
			$result[$item::getRestEntityName()] = $item->toRestFormat();
		}

		return $result;
	}

	private function mergeItem(PopupDataItem $popupItem): void
	{
		if (!isset($this->popupItems[$popupItem::getRestEntityName()]))
		{
			$this->popupItems[$popupItem::getRestEntityName()] = $popupItem;
		}
		else
		{
			$this->popupItems[$popupItem::getRestEntityName()]->merge($popupItem);
		}
	}

	/**
	 * @param string[]|PopupDataItem[] $excludedList
	 * @return void
	 */
	private function filterItems(array $excludedList): void
	{
		foreach ($excludedList as $excludedItem)
		{
			unset($this->popupItems[$excludedItem::getRestEntityName()]);
		}
	}
}