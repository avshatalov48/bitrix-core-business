<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;

final class MessengerChat extends AbstractConfigField
{
	private static function checkUrl($url) : bool
	{
		return
			is_string($url) &&
			preg_match('/^((https:\/\/)|(http:\/\/)){1}[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}$/i',$url);
	}
	private static function checkDomains($value)
	{
		if (array_key_exists('domains', $value) && is_array($value['domains']) && !empty($value['domains']))
		{
			foreach ($value['domains'] as $url)
			{
				if(!static::checkUrl($url))
				{
					return false;
				}
			}
			return true;
		}
		return false;

	}

	static function prepareValue($value)
	{
		if(is_array($value) && array_key_exists('domains', $value) && is_array($value['domains']))
		{
			$value['domains'] = array_values(array_filter($value['domains']));
		}
		return parent::prepareValue($value);
	}

	protected static function checkValueFields($value): bool
	{
		return static::checkDomains($value);
	}

	/**
	 * @inheritDoc
	 */
	static function available(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	static function required(): bool
	{
		return false;
	}

	protected static function getFields() : array
	{
		return array_merge(parent::getFields(),['domains']);
	}
}