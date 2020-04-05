<?php

namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

use Bitrix\Main\Localization\Loc;

/**
 * Label Field included text field for title
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class LabelField extends BaseValuable
{
	private $mode;
	const JS_EVENT_ON_CHANGE = 'onChange';

	/**
	 * Label field constructor.
	 *
	 * @param string $key Unique key.
	 * @param string $mode Mode for render (small or big).
	 */
	public function __construct($key, $mode = 'small')
	{
		parent::__construct($key);
		$this->setLabel(Loc::getMessage('REPORT_DEFAULT_LABEL_OF_LABEL_FIELD'));
		$this->setDefaultValue('Title default Value');
		$this->setMode($mode);
	}


	/**
	 * Load field component with label or biglabel template.
	 *
	 * @return void
	 */
	public function printContent()
	{
		switch ($this->getMode())
		{
			case 'small':
				$this->includeFieldComponent('label');
				break;
			case 'big':
				$this->includeFieldComponent('biglabel');
				break;
			default:
				$this->includeFieldComponent('label');
		}
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * Mode setter.
	 *
	 * @param string $mode Mode value(small or big).
	 * @return void
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}


}