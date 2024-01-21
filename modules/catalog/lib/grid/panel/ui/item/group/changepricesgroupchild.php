<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item\Group;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Action\Group\GroupChildAction;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loader::requireModule('iblock');

/**
 * For correct work, the grid must have the `showChangePriceDialog` method.
 *
 * This action is used only for the UI, the `ChangePricesActionsItem` action is used to process the request.
 *
 * @see \Bitrix\Catalog\Grid\Panel\UI\Item\ChangePricesActionsItem
 */
final class ChangePricesGroupChild extends GroupChildAction
{
	public static function getId(): string
	{
		return 'change_price';
	}

	public function getName(): string
	{
		return Loc::getMessage('CATALOG_GRID_PANEL_UI_PRODUCT_ACTION_CHANGE_PRICES_NAME');
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result
	{
		return null;
	}

	protected function getOnchange(): Onchange
	{
		return new Onchange([
			[
				'ACTION' => Actions::RESET_CONTROLS,
			],
			[
				'ACTION' => Actions::CREATE,
				'DATA' => [
					(new Snippet)->getApplyButton([
						'ONCHANGE' => [
							[
								'ACTION' => Actions::CALLBACK,
								'DATA' => [
									[
										'JS' => 'Grid.showChangePriceDialog()',
									]
								],
							],
						],
					]),
				],
			],
		]);
	}
}
