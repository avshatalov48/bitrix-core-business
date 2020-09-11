<?php
namespace Bitrix\Landing;

abstract class Field
{
	/**
	 * Field id.
	 * @var string
	 */
	protected $id;

	/**
	 * Field code.
	 * @var string
	 */
	protected $code;

	/**
	 * Field value.
	 * @var string
	 */
	protected $value;

	/**
	 * Field title.
	 * @var string
	 */
	protected $title;

	/**
	 * Default value.
	 * @var string
	 */
	protected $default;

	/**
	 * Field help.
	 * @var string
	 */
	protected $help;

	/**
	 * Searchable flag of field.
	 * @var bool
	 */
	protected $searchable = false;

	/**
	 * Modificator, which called within getting value.
	 * @var callable
	 */
	protected $fetchModificator = null;

	/**
	 * Class constructor.
	 * @param string $code Field code.
	 * @param array $params Field params.
	 */
	public function __construct($code, array $params = array())
	{
		$this->code = mb_strtoupper($code);
		$this->value = null;
		$this->id = isset($params['id']) ? $params['id'] : '';
		$this->title = isset($params['title']) ? $params['title'] : '';
		$this->default = isset($params['default']) ? $params['default'] : null;
		$this->help = isset($params['help']) ? $params['help'] : '';
		$this->searchable = isset($params['searchable']) && $params['searchable'] === true;
		$this->fetchModificator = isset($params['fetch_data_modification']) ? $params['fetch_data_modification'] : null;
	}

	/**
	 * Gets true, if current value is empty.
	 * @return bool
	 */
	public function isEmptyValue()
	{
		return $this->value === null;
	}

	/**
	 * Set value to the field.
	 * @param string $value Value.
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Get value of the field.
	 * @return mixed
	 */
	public function getValue()
	{
		if (is_callable($this->fetchModificator))
		{
			return call_user_func_array(
				$this->fetchModificator,
				[$this->value]
			);
		}
		return $this->value;
	}

	/**
	 * Get help value of the field.
	 * @return string
	 */
	public function getHelpValue()
	{
		return $this->help;
	}

	/**
	 * Returns searchable flag of field.
	 * @return bool
	 */
	public function isSearchable()
	{
		return $this->searchable;
	}

	/**
	 * Set new code of the field..
	 * @param string $code Code.
	 * @return void
	 */
	public function setCode($code)
	{
		$this->code = mb_strtoupper($code);
	}

	/**
	 * Get code of the field.
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Get label (title) of the field.
	 * @return mixed
	 */
	public function getLabel()
	{
		return $this->title;
	}

	/**
	 * Get type.
	 * @return string
	 */
	public function getType()
	{
		$class = explode('\\', get_called_class());
		return mb_strtolower(array_pop($class));
	}

	/**
	 * Magic method return value as string.
	 * @return string
	 */
	public function __toString()
	{
		return $this->value ? (string)$this->value : (string)$this->default;
	}

	/**
	 * Vew field.
	 * @param array $params Array params:
	 * name_format - format for field name (for example ADDITIONAL[#field_code#])
	 * class - css-class for this element
	 * additional - some additional params as is.
	 * @return html
	 */
	abstract public function viewForm(array $params = array());
}