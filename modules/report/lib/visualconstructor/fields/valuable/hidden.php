<?php
namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

/**
 * Class Hidden, generate html element input with type hidden
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class Hidden extends BaseValuable
{

	const JS_EVENT_ON_CHANGE = 'onChange';

	/**
	 * Hidden field constructor.
	 *
	 * @param string $key Unique key.
	 */
	public function __construct($key)
	{
		parent::__construct($key);
		$this->setDefaultValue('');
	}


	/**
	 * Load field component with hidden template.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('hidden');
	}
}