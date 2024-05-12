<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Class UISidepanelMenuComponent
 */
class UISidepanelMenuComponent extends \CBitrixComponent
{
	/**
	 * Check page has been opened in slider
	 *
	 * @return bool
	 */
	protected function isPageSliderContext()
	{
		return $this->request->get('IFRAME') === 'Y';
	}

	/**
	 * Prepare menu item for view
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function prepareItem(array $item)
	{
		$convertedItem = array();
		$convertedItem['~NAME'] = $item['NAME'] ?? '';
		$convertedItem['NAME'] = isset($item['NAME_HTML']) ? $item['NAME_HTML'] : htmlspecialcharsbx($convertedItem['~NAME']);
		$convertedItem['ACTIVE'] = isset($item['ACTIVE']) ? (bool)$item['ACTIVE'] : false;
		$convertedItem['NOTICE'] = isset($item['NOTICE']) ? (bool)$item['NOTICE'] : false;
		$convertedItem['LABEL'] = isset($item['LABEL']) ? $item['LABEL'] : '';
		$convertedItem['DISABLED'] = isset($item['DISABLED']) ? (bool)$item['DISABLED'] : false;

		if (!empty($item['ATTRIBUTES']) && is_array($item['ATTRIBUTES']))
		{
			$convertedItem['~ATTRIBUTES'] = $item['ATTRIBUTES'];
			$convertedItem['ATTRIBUTES'] = $this->prepareAttributes($convertedItem['~ATTRIBUTES']);
		}

		if (empty($item['CHILDREN']))
		{
			$convertedItem['OPERATIVE'] = isset($item['OPERATIVE']) ? (bool)$item['OPERATIVE'] : true;
		}
		else
		{
			$convertedItem['OPERATIVE'] = isset($item['OPERATIVE']) ? (bool)$item['OPERATIVE'] : false;
			$convertedItem['~CHILDREN'] = $item['CHILDREN'];
			foreach ($item['CHILDREN'] as &$children)
			{
				$convertedItem['CHILDREN'][] = $this->prepareItem($children);
			}
		}
		$convertedItem['ATTRIBUTES']['bx-operative'] = ($convertedItem['OPERATIVE'] ? 'Y' : 'N');

		return $convertedItem;
	}

	/**
	 * Prepare attribute array items for output
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function prepareAttributes(array $attributes)
	{
		$result = array();

		foreach ($attributes as $key => $attributeValue)
		{
			$newKey = mb_strtolower($key);
			$newKey = str_replace('_', '-', $newKey);

			if (is_array($attributeValue))
			{
				$newAttribute = $this->prepareAttributes($attributeValue);
			}
			else
			{
				$newAttribute = $attributeValue;
			}

			$result[$newKey] = $newAttribute;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function prepareResult()
	{
		if(isset($this->arParams['TITLE_HTML']))
		{
			$this->arResult['TITLE'] = (string)$this->arParams['~TITLE_HTML'];
		}
		elseif (isset($this->arParams['TITLE']))
		{
			$this->arResult['TITLE'] = (string)$this->arParams['TITLE'];
		}
		else
		{
			$this->arResult['TITLE'] = '';
		}

		$this->arResult['ITEMS'] = array();
		$this->arResult['ID'] = !empty($this->arParams['ID']) ? $this->arParams['ID'] : '';
		foreach ($this->arParams['ITEMS'] as $item)
		{
			$this->arResult['ITEMS'][] = $this->prepareItem($item);
		}

		$this->arResult['VIEW_TARGET'] = $this->arParams['VIEW_TARGET'] ?? 'left-panel';

		return $this->arResult;
	}

	/**
	 * Execute component.
	 *
	 * @return mixed|void
	 */
	public function executeComponent()
	{
		$this->arParams['FRAME'] = (
			isset($this->arParams['FRAME']) ? (bool)$this->arParams['FRAME'] : $this->isPageSliderContext()
		);

		$this->arParams['ITEMS'] = (
			isset($this->arParams['ITEMS']) && is_array($this->arParams['ITEMS']) ? $this->arParams['ITEMS'] : []
		);

		$this->prepareResult();
		$this->includeComponentTemplate();
	}
}