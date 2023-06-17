<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class IblockElementXmlProvider extends IblockElementProvider
{
	protected const ENTITY_ID = 'iblock-element-xml';

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