<?php
namespace Bitrix\Landing\Field;

class Textarea extends \Bitrix\Landing\Field\Text
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
		?>
		<textarea <?
		?><?= isset($params['additional']) ? $params['additional'] . ' ' : ''?><?
		?><?= isset($params['id']) ? 'id="' . \htmlspecialcharsbx($params['id']) . '" ' : ''?><?
		?><?= $this->maxlength > 0 ? 'maxlength="'. $this->maxlength . '" ' : ''?><?
		?><?= $this->placeholder != '' ? 'placeholder="'. $this->placeholder . '" ' : ''?><?
		?>class="<?= isset($params['class']) ? \htmlspecialcharsbx($params['class']) : ''?>" <?
		?>data-code="<?= \htmlspecialcharsbx($this->code)?>" <?
		?>name="<?= \htmlspecialcharsbx(isset($params['name_format'])
				? str_replace('#field_code#', $this->code, $params['name_format'])
				: $this->code)?>" <?
		?><?= (isset($params['disabled']) && $params['disabled']) ? ' disabled ' : ''?><?
		?> ><?= \htmlspecialcharsbx($this->value ? $this->value : $this->default)?></textarea>
		<?
	}

	/**
	 * Gets true, if current value is empty.
	 * @return bool
	 */
	public function isEmptyValue()
	{
		return $this->value === '';
	}
}
