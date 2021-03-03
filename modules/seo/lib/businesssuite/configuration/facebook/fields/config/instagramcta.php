<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;

final class InstagramCta extends AbstractConfigField
{

	const BUTTON_TEXT_RESERVE = 'Reserve';
	const BUTTON_TEXT_BOOK = 'Book Now';
	const BUTTON_TEXT_BUY = 'Buy Now';

	private static function checkButtonText($value) : bool
	{
		return array_key_exists('cta_button_text',$value) && in_array($value['cta_button_text'],[
				static::BUTTON_TEXT_BOOK,
				static::BUTTON_TEXT_RESERVE,
				static::BUTTON_TEXT_BUY
			]);

	}
	private static function checkUrl($value) : bool
	{
		return array_key_exists('cta_button_url',$value) &&
			is_string($value['cta_button_url']) &&
			preg_match('%^((https://)|(www\.)|(http://))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i',$value['cta_button_url']);
	}

	protected static function checkValueFields($value) : bool
	{
		return static::checkButtonText($value) && static::checkUrl($value);
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