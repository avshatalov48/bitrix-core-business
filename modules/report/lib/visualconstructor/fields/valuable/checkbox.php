<?php
namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

/**
 * Simple checkbox field
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class CheckBox extends BaseValuable
{

	/**
	 * Constructor for checkbox field.
	 *
	 * @param string $key Unique key.
	 */
	public function __construct($key)
	{
		parent::__construct($key);
	}

	/**
	 * Load field component with checkbox template.
	 * And print it.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('checkbox');
	}

}