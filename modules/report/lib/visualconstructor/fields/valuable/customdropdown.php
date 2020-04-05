<?php

namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

/**
 * Imitation DropDown field with popup
 *
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class CustomDropDown extends DropDown
{
	/**
	 * Custom dopr down constructor.
	 *
	 * @param string $key Unique key.
	 */
	public function __construct($key)
	{
		parent::__construct($key);
	}


	/**
	 * Load field component with selectwithpopup template.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('selectwithpopup');
	}
}