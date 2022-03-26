<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;

class StoreSelector extends JsonController
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
				new ActionFilter\Scope(ActionFilter\Scope::AJAX),
			]
		);
	}

	protected function processBeforeAction(Action $action)
	{
		global $USER;
		if ($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_view'))
		{
			return parent::processBeforeAction($action);
		}

		return false;
	}

	/**
	 * @param int $productId
	 * @param array $options
	 * @return array|null
	 */
	public function getProductStoresAction(int $productId): array
	{
		$iterator = \CIBlockElement::GetList(
			[],
			[
				'ID' => $productId,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			false,
			false,
			['ID', 'IBLOCK_ID', 'TYPE']
		);
		$element = $iterator->Fetch();
		if (!$element)
		{
			return [];
		}

		$storeAmounts = StoreProductTable::getList([
			'filter' => [
				'=PRODUCT_ID' => $productId,
			],
			'select' => [
				'AMOUNT',
				'QUANTITY_RESERVED',
				'STORE_ID',
				'STORE_TITLE' => 'STORE.TITLE'
			]
		]);

		return $storeAmounts->fetchAll();
	}

	public function createStoreAction(string $name): ?array
	{
		$result = StoreTable::add([
			'TITLE' => $name,
			'ADDRESS' => $name,
		]);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return [];
		}

		return [
			'id' => $result->getId(),
		];
	}
}
