<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item\Group;

use Bitrix\Catalog\Grid\ProductAction;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers\ItemFinder;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\BaseGroupChild;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loader::requireModule('iblock');

final class ConvertToProductGroupChild extends BaseGroupChild
{
	use ItemFinder;

	public static function getId(): string
	{
		return 'convert_to_product';
	}

	public function getName(): string
	{
		return Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_CONVERT_TO_PRODUCT_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter = null): ?Result
	{
		$result = new Result();

		[$elementIds, $sectionIds] = $this->prepareItemIds($request, $isSelectedAllRows, $filter);

		if ($elementIds)
		{
			$result->addErrors(
				ProductAction::convertToProductElementList($this->getIblockId(), $elementIds)->getErrors()
			);
		}

		if ($sectionIds)
		{
			$result->addErrors(
				ProductAction::convertToProductSectionList($this->getIblockId(), $sectionIds)->getErrors()
			);
		}

		return $result;
	}

	protected function getOnchange(): Onchange
	{
		$confirmMessage = Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_CONVERT_TO_PRODUCT_CONFIRM');

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
}
