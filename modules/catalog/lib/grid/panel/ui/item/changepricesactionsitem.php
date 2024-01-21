<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item;

use Bitrix\Catalog\Grid\Access\ProductRightsChecker;
use Bitrix\Catalog\Helpers\Admin\IblockPriceChanger;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Action\Action;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;

/**
 * This action is used only for process the request, the `ChangePricesGroupChild` action is used for UI.
 *
 * @see \Bitrix\Catalog\Grid\Panel\UI\Item\Group\ChangePricesGroupChild
 */
final class ChangePricesActionsItem implements Action
{
	private int $iblockId;
	private ProductRightsChecker $rights;

	public function __construct(int $iblockId, ProductRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
	}

	/**
	 * @inheritDoc
	 */
	public static function getId(): string
	{
		return 'change_price';
	}

	/**
	 * @inheritDoc
	 */
	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result
	{
		if (empty($request->get('chprice_value_changing_price')))
		{
			return null;
		}

		if (!$this->rights->canEditPrices())
		{
			return null;
		}

		$ids = $request->getPost('ID');
		if (!is_array($ids))
		{
			return null;
		}

		[$elementIds, $sectionIds] = RowType::parseIndexList($ids);
		if (empty($elementIds) && empty($sectionIds))
		{
			return null;
		}

		$changerParams = [
			'PRICE_TYPE' => $request->get('chprice_id_price_type'),
			'UNITS' => $request->get('chprice_units'),
			'FORMAT_RESULTS' => $request->get('chprice_format_result'),
			'INITIAL_PRICE_TYPE' => $request->get('chprice_initial_price_type'),
			'RESULT_MASK' => $request->get('chprice_result_mask'),
			'DIFFERENCE_VALUE' => $request->get('chprice_difference_value'),
			'VALUE_CHANGING' => $request->get('chprice_value_changing_price'),
		];

		$changePrice = new IblockPriceChanger($changerParams, $this->iblockId);

		return $changePrice->updatePrices([
			'SECTIONS' => $sectionIds,
			'ELEMENTS' => $elementIds,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getControl(): ?array
	{
		return null;
	}
}
