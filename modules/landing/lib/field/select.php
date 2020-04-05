<?php
namespace Bitrix\Landing\Field;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Select extends \Bitrix\Landing\Field
{
	/**
	 * Select options.
	 * @var array
	 */
	protected $options = array();

	/**
	 * Multiple list.
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Class constructor.
	 * @param string $code Field code.
	 * @param array $params Field params.
	 */
	public function __construct($code, array $params = array())
	{
		$this->code = strtoupper($code);
		$this->value = null;
		$this->id = isset($params['id']) ? $params['id'] : '';
		$this->title = isset($params['title']) ? $params['title'] : '';
		$this->multiple = isset($params['multiple']) && $params['multiple'];
		$this->help = isset($params['help']) ? $params['help'] : '';
		$this->options = isset($params['options']) ? (array)$params['options'] : array();
	}

	/**
	 * Multiple or not current field.
	 * @return bool
	 */
	public function isMulti()
	{
		return $this->multiple;
	}

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
		<select <?
				?><?= isset($params['additional']) ? $params['additional'] . ' ' : '';?><?
				?><?= isset($params['id']) ? 'id="' . \htmlspecialcharsbx($params['id']) . '" ' : '';?><?
				?><?= $this->multiple ? 'multiple="multiple" size="3" ' : '';?><?
				?>class="<?= isset($params['class']) ? \htmlspecialcharsbx($params['class']) : '';?>" <?
				?>name="<?= $this->getName($params["name_format"]);?><?= $this->multiple ? '[]' : '';?>" <?
		?> />
		<?foreach ($this->options as $code => $val):?>
			<option value="<?= \htmlspecialcharsbx($code);?>"<?
			echo in_array($code, (array) $this->value) ? ' selected="selected"' : ''
			?>>
				<?= \htmlspecialcharsbx($val);?>
			</option>
		<?endforeach;?>
		</select>
		<?
	}

	/**
	 * Magic method return value as string.
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->value;
	}
	
	
	/**
	 * Create select name in correct format
	 * @return string
	 */
	public function getName($nameFormat)
	{
		return  \htmlspecialcharsbx(isset($nameFormat)
			? str_replace('#field_code#', $this->code, $nameFormat)
			: $this->code);
	}
	
	
	/**
	 * Get options as array
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}
}
