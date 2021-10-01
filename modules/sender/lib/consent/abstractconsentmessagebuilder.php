<?php

namespace Bitrix\Sender\Consent;

abstract class AbstractConsentMessageBuilder implements iConsentMessageBuilder
{
	protected $fields = [];

	/**
	 * filter input parameters
	 * @param array $fieldForConsent
	 *
	 * @return array
	 */
	protected static abstract function filterFields(array $fieldForConsent) : array;
	
	/**
	 * check input parameters
	 * @param array|null $fields
	 *
	 * @return bool
	 */
	protected static abstract function checkRequireFields(?array $fields) : bool;

	public function __construct(?array $fields = null)
	{
		if(is_array($fields))
		{
			$this->setFields($fields);
		}
	}
	
	/**
	 * set builders parameters
	 * @param array|null $fieldForConsent
	 *
	 * @return $this
	 */
	public function setFields(?array $fieldForConsent): AbstractConsentMessageBuilder
	{
		$fields = $this->filterFields($fieldForConsent);
		if($this->checkRequireFields($fields))
		{
			$this->fields = $fields;
			return $this;
		}
		throw new \InvalidArgumentException("");
	}

	/**
	 * set builder parameter
	 * @param string $field
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function set(string $field, $value)
	{
		return $this->fields[$field] = $value;
	}

	/**
	 * get builder parameter
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function get(string $field)
	{
		return $this->fields[$field];
	}

	/**
	 * build consent message
	 */
	public abstract function buildMessage();
}