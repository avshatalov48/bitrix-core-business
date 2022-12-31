<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Error;

class StoreSelector extends JsonController
{
	private AccessController $accessController;

	/**
	 * @inheritDoc
	 */
	protected function init()
	{
		parent::init();

		$this->accessController = AccessController::getCurrent();
	}

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
		if (
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			|| $this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
		)
		{
			return parent::processBeforeAction($action);
		}

		$this->addError(new Error('Access denied'));

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

		$filter = [
			'=PRODUCT_ID' => $productId,
		];

		$accessFilter = $this->accessController->getEntityFilter(
			ActionDictionary::ACTION_STORE_VIEW,
			StoreProductTable::class
		);
		if ($accessFilter)
		{
			$filter[] = $accessFilter;
		}

		$storeAmounts = StoreProductTable::getList([
			'filter' => $filter,
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
		if (!$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY))
		{
			$this->addError(new Error('Access denied'));
			return null;
		}

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
