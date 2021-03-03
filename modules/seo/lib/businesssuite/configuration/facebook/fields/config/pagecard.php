<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Config;

final class PageCard extends AbstractConfigField
{

	const BUTTON_TEXT_BOOK = 'Book';
	const SEE_ALL_TEXT = 'see_all_text';

	private static function checkButtonText($value) : bool
	{
		return array_key_exists('cta_button_text',$value) && in_array($value['cta_button_text'],[
				static::BUTTON_TEXT_BOOK,
			]);
	}
	private static function checkSeeAllText($value) : bool
	{
		return array_key_exists('see_all_text',$value) && in_array($value['see_all_text'],[
				static::SEE_ALL_TEXT
			]);

	}
	private static function checkUrl($value) : bool
	{
		return array_key_exists('see_all_url',$value) &&
			is_string($value['see_all_url']) &&
			preg_match(static::URL_PATTERN,$value['see_all_url']);
	}

	protected static function checkValueFields($value): bool
	{
		return static::checkButtonText($value) && static::checkSeeAllText($value) && static::checkUrl($value);
	}

	protected static function setDefaultFields($value)
	{
		$value['see_all_text'] = static::SEE_ALL_TEXT;
		$value['cta_button_text'] = static::BUTTON_TEXT_BOOK;
		return $value;
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
		return array_merge(parent::getFields(),['cta_button_text','see_all_text','see_all_url']);
	}
}