<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;


abstract class AbstractConfigField implements IConfigField
{
	protected const URL_PATTERN = '%^((https://)|(www\.)|(http://))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i';

	protected abstract static function checkValueFields($value) : bool;

	protected static function getFields() : array
	{
		return ['enabled'];
	}

	protected static function filter(array $values)
	{
		$fields = static::getFields();
		return array_filter($values,function($key) use ($fields) {
			return in_array($key,$fields);
		},ARRAY_FILTER_USE_KEY);
	}

	protected static function setDefaultFields($value)
	{
		return $value;
	}

	protected static function setEnabled($value)
	{
		$value['enabled'] = true;
		return $value;
	}
	static function prepareValue($value)
	{
		if(is_array($value))
		{
			if(!static::checkEnabled($value))
			{
				return static::filter(static::setDefaultFields(static::setEnabled($value)));
			}
			elseif($value['enabled'])
			{
				return $value;
			}
		}
		return static::getDefaultValue();
	}
	/**
	 * @inheritDoc
	 */
	static function getDefaultValue()
	{
		return (static::required() ? ['enabled' => false] : null);
	}

	protected static function checkEnabled($value) : bool
	{
		return array_key_exists('enabled',$value) && is_bool($value['enabled']);
	}
	/**
	 * @inheritDoc
	 */
	static function checkValue($value): bool
	{
		if(!isset($value) && !static::required())
		{
			return true;
		}
		elseif(is_array($value) && static::checkEnabled($value))
		{
			return (!$value['enabled']? true : static::checkValueFields($value));
		}
		return false;
	}
}