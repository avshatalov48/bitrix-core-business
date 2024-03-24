<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Fields\FieldTypeMask;

/**
 * Scalar entity field class for non-array and non-object data types
 * @package bitrix
 * @subpackage main
 */
abstract class ScalarField extends Field implements IStorable, ITypeHintable
{
	protected $is_primary;

	protected $is_unique;

	protected $is_required;

	protected $is_autocomplete;

	/** @var boolean Permission to use in filter */
	protected $is_private;

	/** @var boolean Can be null */
	protected $is_nullable;

	/** @var bool  */
	protected $is_binary = false;

	protected $column_name = '';

	/** @var null|callable|mixed  */
	protected $default_value;

	/**
	 * ScalarField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$this->is_primary = (isset($parameters['primary']) && $parameters['primary']);
		$this->is_unique = (isset($parameters['unique']) && $parameters['unique']);
		$this->is_required = (isset($parameters['required']) && $parameters['required']);
		$this->is_autocomplete = (isset($parameters['autocomplete']) && $parameters['autocomplete']);
		$this->is_private = (isset($parameters['private']) && $parameters['private']);
		$this->is_nullable = (isset($parameters['nullable']) && $parameters['nullable']);
		$this->is_binary = (isset($parameters['binary']) && $parameters['binary']);

		$this->column_name = $parameters['column_name'] ?? $this->name;
		$this->default_value = $parameters['default_value'] ?? null;
	}

	/**
	 * @return int
	 */
	public function getTypeMask()
	{
		return FieldTypeMask::SCALAR;
	}

	/**
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function configurePrimary($value = true)
	{
		$this->is_primary = (bool) $value;
		return $this;
	}

	public function isPrimary()
	{
		return $this->is_primary;
	}

	/**
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function configureRequired($value = true)
	{
		$this->is_required = (bool) $value;
		return $this;
	}

	public function isRequired()
	{
		return $this->is_required;
	}

	/**
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function configureUnique($value = true)
	{
		$this->is_unique = (bool) $value;
		return $this;
	}

	public function isUnique()
	{
		return $this->is_unique;
	}

	/**
	 * @param boolean $value
	 *
	 * @return $this
	 */
	public function configureAutocomplete($value = true)
	{
		$this->is_autocomplete = (bool) $value;
		return $this;
	}

	public function isAutocomplete()
	{
		return $this->is_autocomplete;
	}


	/**
	 * @param bool $value
	 *
	 * @return ScalarField
	 */
	public function configurePrivate($value = true)
	{
		$this->is_private = (bool) $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPrivate()
	{
		return $this->is_private;
	}

	/**
	 * @param bool $value
	 *
	 * @return ScalarField
	 */
	public function configureNullable($value = true)
	{
		$this->is_nullable = (bool) $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNullable()
	{
		return $this->is_nullable;
	}

	/**
	 * @param bool $value
	 *
	 * @return ScalarField
	 */
	public function configureBinary($value = true)
	{
		$this->is_binary = (bool) $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBinary()
	{
		return $this->is_binary;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function configureColumnName($value)
	{
		$this->column_name = $value;
		return $this;
	}

	public function getColumnName()
	{
		return $this->column_name;
	}

	/**
	 * @param string $column_name
	 */
	public function setColumnName($column_name)
	{
		$this->column_name = $column_name;
	}

	/**
	 * @param callable|mixed $value
	 *
	 * @return $this
	 */
	public function configureDefaultValue($value)
	{
		$this->default_value = $value;
		return $this;
	}

	/**
	 * @param array $row ORM data row in case of dependency value on other values
	 *
	 * @return callable|mixed|null
	 */
	public function getDefaultValue($row = null)
	{
		if (!is_string($this->default_value) && is_callable($this->default_value))
		{
			return call_user_func($this->default_value, $row);
		}
		else
		{
			return $this->default_value;
		}
	}

	public function isValueEmpty($value)
	{
		if ($value instanceof SqlExpression)
		{
			$value = $value->compile();
		}

		return ((string)$value === '');
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return $this->getNullableTypeHint('\\string');
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return $this->getNullableTypeHint('\\string');
	}

	/**
	 * @param string $type
	 * @return string
	 */
	protected function getNullableTypeHint(string $type): string
	{
		return $this->is_nullable ? '?' . $type : $type;
	}
}
