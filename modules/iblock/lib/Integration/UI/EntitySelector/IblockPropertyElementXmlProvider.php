<?php

namespace Bitrix\Iblock\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class IblockPropertyElementXmlProvider extends IblockPropertyElementProvider
{
	public const ENTITY_ID = 'iblock-property-element-xml';

	/**
	 * Add to dialog object search results.
	 *
	 * @param SearchQuery $searchQuery Query object.
	 * @param Dialog $dialog Dialog object.
	 * @return void
	 */
	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$filter = [];

		$query = $searchQuery->getQuery();
		if ($query !== '')
		{
			$filter = $this->getQueryFilter($query);
		}

		$elements = $this->getElements($filter, self::ELEMENTS_LIMIT);
		if (count($elements) === self::ELEMENTS_LIMIT)
		{
			$searchQuery->setCacheable(false);
		}
		foreach ($elements as $element)
		{
			$dialog->addItem(
				$this->makeItem($element)
			);
		}
	}

	private function getQueryFilter(string $query): array
	{
		return [
			[
				'LOGIC' => 'OR',
				'%XML_ID' => $query,
				'*SEARCHABLE_CONTENT' => $query,
			],
		];
	}

	protected function makeItem(array $element, string $propertyType = ''): Item
	{
		$itemParams = [
			'id' => $element['ID'] ?? null,
			'entityId' => self::ENTITY_ID,
			'title' => $element['NAME'] ?? null,
			'subtitle' => $element['XML_ID'] ?? null,
			'description' => $element['DETAIL_TEXT'] ?? null,
			'avatar' => $element['PREVIEW_PICTURE'] ?? null,
			'customData' => [
				'xmlId' => $element['XML_ID'] ?? null,
			],
		];

		return new Item($itemParams);
	}
}
