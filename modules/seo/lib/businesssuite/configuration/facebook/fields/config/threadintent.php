<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;

final class ThreadIntent extends AbstractConfigField
{

	private static function checkUrl($value) : bool
	{
		return array_key_exists('cta_button_url',$value) &&
			is_string($value['cta_button_url']) &&
			preg_match(static::URL_PATTERN,$value['cta_button_url']);
	}

	protected static function checkValueFields($value): bool
	{
		return static::checkUrl($value);

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
		return array_merge(parent::getFields(),['cta_button_url','cta_button_text']);
	}
}