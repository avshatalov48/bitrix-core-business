<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

Loc::loadMessages(__FILE__);

/**
 * Class UiTileListComponent
 */
class UiTileListComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? $this->arParams['ID'] : '';
		$this->arParams['SHOW_BUTTON_ADD'] = isset($this->arParams['SHOW_BUTTON_ADD']) ? (bool) $this->arParams['SHOW_BUTTON_ADD'] : false;
		$this->arParams['BUTTON_ADD_CAPTION'] = isset($this->arParams['BUTTON_ADD_CAPTION']) ? $this->arParams['BUTTON_ADD_CAPTION'] : '';
		$this->arParams['LIST'] = (isset($this->arParams['LIST']) && is_array($this->arParams['LIST']))
			? $this->arParams['LIST']
			: [];
	}

	protected function prepareResult()
	{
		$this->arResult['LIST'] = [];
		$list = $this->arParams['LIST'];
		foreach ($list as $item)
		{
			$id = isset($item['id']) ? $item['id'] : null;
			if (!isset($item['name']) || !$item['name'])
			{
				continue;
			}
			if (!isset($item['data']) || !$item['data'])
			{
				if (!$id)
				{
					continue;
				}
			}

			$this->arResult['LIST'][] = array(
				'name' => $item['name'],
				'data' => isset($item['data']) ? $item['data'] : [],
				'id' => $id,
				'iconClass' => isset($item['iconClass']) ? $item['iconClass'] : null,
				'iconColor' => isset($item['iconColor']) ? $item['iconColor'] : null,
				'selected' => isset($item['selected']) ? (bool) $item['selected'] : false,
				'bgcolor' => isset($item['bgcolor']) ? $item['bgcolor'] : null,
				'color' => isset($item['color']) ? $item['color'] : null,
				'comingSoon' => isset($item['comingSoon']) && $item['comingSoon'] === true,
				'badgeNew' => isset($item['badgeNew']) && $item['badgeNew'] === true,
				'button' => isset($item['button']) && $item['button'] === true,
			);
		}

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}