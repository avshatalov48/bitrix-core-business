<?php

namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

use Bitrix\Main\Localization\Loc;

/**
 * Drop down field rendered standard drop down
 * @package Bitrix\Report\VisualConstructor\Fields
 */
class DropDown extends BaseValuable
{
	const JS_EVENT_ON_CHANGE = 'onChange';
	private $options = array();

	/**
	 * Drop down field constructor.
	 *
	 * @param string $key Unique key.
	 */
	public function __construct($key)
	{
		parent::__construct($key);
		$options = $this->getDefaultOptions();
		$this->setOptions($options);
		$this->setLabel('Select: ');
		$this->setDefaultValue('__');
	}


	/**
	 * Load field component with baseselect template.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('baseselect');
	}


	/**
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return array('__' => Loc::getMessage('REPORT_DROP_DOWN_DEFAULT_VALUE_TITLE'));
	}

	/**
	 * Add option to end of option list.
	 *
	 * @param string $key Key for option.
	 * @param string $value Value For option.
	 * @return $this
	 */
	public function addOption($key, $value)
	{
		$this->options[$key]  = $value;
		return $this;
	}

	/**
	 * Add options to end of options list.
	 *
	 * @param array $options Key value pair array.
	 * @return void
	 */
	public function addOptions($options)
	{
		foreach ($options as $key => $value)
		{
			$this->options[$key] = $value;
		}
	}
	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Options list setter.
	 *
	 * @param array $options Key value pair array.
	 * @return void
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}

}