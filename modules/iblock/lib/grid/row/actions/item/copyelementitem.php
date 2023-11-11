<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item;

use Bitrix\Iblock\Grid\Row\Actions\Item\Helpers\WithUrl;
use Bitrix\Main\Grid\Row\Action\BaseAction;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

final class CopyElementItem extends BaseAction
{
	use WithUrl;

	public static function getId(): string
	{
		return 'copy_element';
	}

	public function processRequest(HttpRequest $request): ?Result
	{
		// only open url

		return null;
	}

	protected function getText(): string
	{
		return Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_COPY_ELEMENT_NAME');
	}

	public function getControl(array $rawFields): ?array
	{
		if (empty($this->url))
		{
			return null;
		}

		$this->onclick = "top.BX.SidePanel.Instance.open('" . $this->getUrlForOnclick() . "')";

		return parent::getControl($rawFields);
	}
}
