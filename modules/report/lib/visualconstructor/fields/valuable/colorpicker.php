<?php

namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

use Bitrix\Main\Localization\Loc;

/**
 * ColorPicker field whit preview
 *
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class ColorPicker extends BaseValuable
{
	const JS_EVENT_ON_SELECT = 'onSelect';
	private $mode;
	private $pickerFieldHidden = false;

	/**
	 * Color picker constructor.
	 *
	 * @param string $key Unique key.
	 * @param string $mode Mode which define in which template will render this field.
	 */
	public function __construct($key, $mode = 'simple')
	{
		parent::__construct($key);
		$this->setLabel(Loc::getMessage('REPORT_DEFAULT_COLOR_FIELD_LABEL'));
		$this->setDefaultValue('inherit');
		$this->setMode($mode);
	}


	/**
	 * Load field component with simplecolorpicker or colorpicker template.
	 * And print it.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$templateName = $this->getMode() == 'simple' ? 'simplecolorpicker' : 'colorpicker';
		$this->includeFieldComponent($templateName);
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
	 * @param string $mode Mode value.
	 * @return void
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}

	/**
	 * Check is color picker is hidden.
	 * In this mode color picker will not dislay in form, but input field will exist.
	 *
	 * @return bool
	 */
	public function isPickerFieldHidden()
	{
		return $this->pickerFieldHidden;
	}

	/**
	 * Setter for hide marker.
	 *
	 * @param bool $pickerFieldHidden Marker hidden or not picker field.
	 * @return void
	 */
	public function setPickerFieldHidden($pickerFieldHidden)
	{
		$this->pickerFieldHidden = $pickerFieldHidden;
	}


}