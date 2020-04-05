<?php
namespace Bitrix\Landing\Field;

class Checkbox extends \Bitrix\Landing\Field
{
	/**
	 * Vew field.
	 * @param array $params Array params:
	 * name - field name
	 * class - css-class for this element
	 * additional - some additional params as is.
	 * @return void
	 */
	public function viewForm(array $params = array())
	{
		$name = \htmlspecialcharsbx(isset($params['name_format'])
				? str_replace('#field_code#', $this->code, $params['name_format'])
				: $this->code);
		?>
		<input type="hidden" name="<?= $name?>" value="N" />
		<input type="checkbox" <?
		?><?= isset($params['additional']) ? $params['additional'] . ' ' : ''?><?
		?><?= isset($params['id']) ? 'id="' . \htmlspecialcharsbx($params['id']) . '" ' : ''?><?
		?>class="<?= isset($params['class']) ? \htmlspecialcharsbx($params['class']) : ''?>" <?
		?>data-code="<?= \htmlspecialcharsbx($this->code)?>" <?
		?>name="<?= $name?>" <?
		?>value="Y"<?if ($this->value == 'Y'){?> checked="checked"<?}?> <?
		?> />
		<?
	}

	/**
	 * Magic method return value as string.
	 * @return string
	 */
	public function __toString()
	{
		return $this->value == 'Y' ? 'Y' : 'N';
	}
}
