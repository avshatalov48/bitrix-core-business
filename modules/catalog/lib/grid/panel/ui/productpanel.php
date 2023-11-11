<?php

namespace Bitrix\Catalog\Grid\Panel\UI;

use Bitrix\Catalog\Grid\Panel\UI\Item\ChangePricesActionsItem;
use Bitrix\Main\Grid\GridRequest;
use Bitrix\Main\Grid\GridResponse;
use Bitrix\Main\Grid\Panel\Panel;
use Bitrix\Main\Grid\UI\Response\GridResponseFactory;
use Bitrix\Main\Result;

class ProductPanel extends Panel
{
	/**
	 * @inheritDoc
	 *
	 * @param GridRequest $request
	 *
	 * @return GridResponse|null
	 */
	public function processRequest(GridRequest $request): ?GridResponse
	{
		$result = parent::processRequest($request);
		if (isset($result))
		{
			return $result;
		}

		$actionId = $request->getHttpRequest()->getPost('action');
		if ($actionId === 'change_price')
		{
			$action = $this->getActionById(ChangePricesActionsItem::getId());
			if (isset($action))
			{
				$result = $action->processRequest($request->getHttpRequest(), false);
				if ($result instanceof Result)
				{
					return (new GridResponseFactory)->createFromResult($result);
				}
			}
		}

		return null;
	}
}
