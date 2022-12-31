<?php

namespace Bitrix\Catalog\Access\Filter\Factory;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Filter\StoreDocumentFilter;
use Bitrix\Catalog\Access\Filter\StoreViewFilter;
use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\Filter\AccessFilter;
use Bitrix\Main\Access\Filter\FilterFactory;

class CatalogFilterFactory implements FilterFactory
{
	private array $actionToFilterClassMap;

	private static function getMap(): array
	{
		return [
			StoreDocumentFilter::class => [
				ActionDictionary::ACTION_STORE_DOCUMENT_CANCEL,
				ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT,
				ActionDictionary::ACTION_STORE_DOCUMENT_DELETE,
				ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
				ActionDictionary::ACTION_STORE_DOCUMENT_PERFORM,
				ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
			],
			StoreViewFilter::class => [
				ActionDictionary::ACTION_STORE_VIEW,
				ActionDictionary::ACTION_STORE_MODIFY,
			],
		];
	}

	private function getFilterClassByAction(string $action): ?string
	{
		if (!isset($this->actionToFilterClassMap))
		{
			$this->actionToFilterClassMap = [];

			foreach (self::getMap() as $filterClass => $itemActionNames)
			{
				foreach ($itemActionNames as $itemAction)
				{
					$this->actionToFilterClassMap[$itemAction] = $filterClass;
				}
			}
		}

		return $this->actionToFilterClassMap[$action] ?? null;
	}

	public function createFromAction(string $action, AccessibleController $controller): ?AccessFilter
	{
		$filterClassName = $this->getFilterClassByAction($action);
		if (!$filterClassName)
		{
			return null;
		}

		return new $filterClassName($controller);
	}
}
