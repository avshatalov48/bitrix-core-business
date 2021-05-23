<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

Loc::loadMessages(__FILE__);

class UiTileSelectorComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['MULTIPLE'] = isset($this->arParams['MULTIPLE']) ? (bool) $this->arParams['MULTIPLE'] : true;
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['CAN_REMOVE_TILES'] = isset($this->arParams['CAN_REMOVE_TILES']) ? (bool) $this->arParams['CAN_REMOVE_TILES'] : true;
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : '';
		$this->arParams['ID'] = isset($this->arParams['ID']) ? $this->arParams['ID'] : '';
		$this->arParams['LIST'] = isset($this->arParams['LIST']) ? $this->arParams['LIST'] : null;
		$this->arParams['BUTTON_SELECT_CAPTION'] = isset($this->arParams['BUTTON_SELECT_CAPTION']) ? $this->arParams['BUTTON_SELECT_CAPTION'] : null;
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['FIRE_CLICK_EVENT'] = isset($this->arParams['FIRE_CLICK_EVENT']) ? $this->arParams['FIRE_CLICK_EVENT'] : '';
		$this->arParams['LOCK'] = isset($this->arParams['LOCK']) ? (bool) $this->arParams['LOCK'] : false;

		if (isset($this->arParams['SHOW_BUTTON_ADD']))
		{
			$this->arParams['SHOW_BUTTON_ADD'] = (bool) $this->arParams['SHOW_BUTTON_ADD'];
		}
		else
		{
			$this->arParams['SHOW_BUTTON_ADD'] = false;
		}

		if (isset($this->arParams['SHOW_BUTTON_SELECT']))
		{
			$this->arParams['SHOW_BUTTON_SELECT'] = (bool) $this->arParams['SHOW_BUTTON_SELECT'];
		}
		else
		{
			$this->arParams['SHOW_BUTTON_SELECT'] = true;
		}

		if (isset($this->arParams['DUPLICATES']))
		{
			$this->arParams['DUPLICATES'] = (bool) $this->arParams['DUPLICATES'];
		}
		else
		{
			$this->arParams['DUPLICATES'] = false;
		}
	}

	protected function prepareResult()
	{
		$this->arResult['LIST'] = array();
		$list = is_array($this->arParams['LIST']) ? $this->arParams['LIST'] : array();
		$tileIds = array();
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


			if (!$this->arParams['DUPLICATES'] && in_array($id, $tileIds))
			{
				continue;
			}

			$tileIds[] = $id;
			$this->arResult['LIST'][] = array(
				'name' => $item['name'],
				'data' => $item['data'],
				'id' => $id,
				'bgcolor' => isset($item['bgcolor']) ? $item['bgcolor'] : null,
				'color' => isset($item['color']) ? $item['color'] : null,
			);

			if (!$this->arParams['MULTIPLE'])
			{
				break;
			}
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