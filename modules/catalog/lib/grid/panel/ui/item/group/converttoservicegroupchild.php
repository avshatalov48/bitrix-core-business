<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item\Group;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Grid\ProductAction;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\BaseGroupChild;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loader::requireModule('iblock');

final class ConvertToServiceGroupChild extends BaseGroupChild
{
	public static function getId(): string
	{
		return 'convert_to_service';
	}

	public function getName(): string
	{
		return Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_CONVERT_TO_SERVICE_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows): ?Result
	{
		$result = new Result();

		if ($isSelectedAllRows)
		{
			$result->addErrors(
				$this->convertProductTypeByIds(true, [])->getErrors()
			);
		}
		else
		{
			$ids = $this->getRequestRows($request);
			if (empty($ids))
			{
				return null;
			}

			[$elementIds, $sectionIds] = RowType::parseIndexList($ids);

			if ($elementIds)
			{
				$result->addErrors(
					$this->convertProductTypeByIds(false, $elementIds)->getErrors()
				);
			}

			if ($sectionIds)
			{
				$result->addErrors(
					$this->convertProductTypeBySections($sectionIds)->getErrors()
				);
			}
		}

		return $result;
	}

	protected function getOnchange(): Onchange
	{
		$confirmMessage =
			State::isUsedInventoryManagement()
				? Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_CONVERT_TO_SERVICE_CONFIRM_WITH_INVENTORY')
				: Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_CONVERT_TO_SERVICE_CONFIRM_WITHOUT_INVENTORY')
		;

		return new Onchange([
			[
				'ACTION' => Actions::RESET_CONTROLS,
			],
			[
				'ACTION' => Actions::CREATE,
				'DATA' => [
					(new Snippet)->getSendSelectedButton($confirmMessage),
				],
			],
		]);
	}

	private function convertProductTypeByIds(bool $isSelectedAllRows, array $ids): Result
	{
		if ($isSelectedAllRows)
		{
			$ids = [];

			$rows = ProductTable::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'IBLOCK_ELEMENT.IBLOCK_ID' => $this->getIblockId(),
				],
			]);
			foreach ($rows as $row)
			{
				$ids[] = (int)$row['ID'];
			}
		}

		return ProductAction::convertToServiceElementList($this->getIblockId(), $ids);
	}

	private function convertProductTypeBySections(array $ids): Result
	{
		return ProductAction::convertToServiceSectionList($this->getIblockId(), $ids);
	}
}
