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
		$convertedItem['~NAME'] = $item['NAME'];
		$convertedItem['NAME'] = htmlspecialcharsbx($convertedItem['~NAME']);
		$convertedItem['ACTIVE'] = (bool)$item['ACTIVE'];

		if (!empty($item['ATTRIBUTES']) && is_array($item['ATTRIBUTES']))
		{
			$convertedItem['~ATTRIBUTES'] = $item['ATTRIBUTES'];
			$convertedItem['ATTRIBUTES'] = $this->prepareAttributes($convertedItem['~ATTRIBUTES']);
		}

		if (!empty($item['CHILDREN']))
		{
			if (empty($convertedItem['ATTRIBUTES']['bx-hide-active']))
			{
				$convertedItem['ATTRIBUTES']['bx-hide-active'] = 'Y';
			}

			$convertedItem['~CHILDREN'] = $item['CHILDREN'];
			foreach ($item['CHILDREN'] as &$children)
			{
				$convertedItem['CHILDREN'][] = $this->prepareItem($children);
			}
		}

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
			$newKey = strtolower($key);
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
		else
		{
			$this->arResult['TITLE'] = (string)$this->arParams['TITLE'];
		}
		$this->arResult['ITEMS'] = array();
		$this->arResult['ID'] = !empty($this->arParams['ID']) ? $this->arParams['ID'] : '';
		foreach ($this->arParams['ITEMS'] as $item)
		{
			$this->arResult['ITEMS'][] = $this->prepareItem($item);
		}

		return $this->arResult;
	}

	/**
	 * Execute component.
	 *
	 * @return mixed|void
	 */
	public function executeComponent()
	{
		$this->arParams['FRAME'] = (isset($this->arParams['FRAME']) ? (bool)$this->arParams['FRAME'] : $this->isPageSliderContext());

		$this->prepareResult();
		$this->includeComponentTemplate();
	}
}