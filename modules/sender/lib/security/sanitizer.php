<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Security;

/**
 * Class Sanitizer
 * @package Bitrix\Sender\Security
 */
class Sanitizer
{
	/**
	 * Clean html.
	 *
	 * @param string $html Html text.
	 * @return string|null
	 */
	public static function cleanHtml($html)
	{
		if (!$html || !is_string($html))
		{
			return null;
		}

		return preg_replace('/<(script|iframe)(.*?)>(.*?)(<\\/\\1.*?>)/is', '', $html);
	}

	/**
	 * Fix replaced style tags and attributes.
	 *
	 * @param string $html Html text.
	 * @return string|null
	 */
	public static function fixReplacedStyles($html)
	{
		return str_replace(
			[
				'<st yle ', '<st yle	',
				' st yle="','	st yle="',
				' st yle=\'','	st yle=\'',
			],
			[
				'<style ', '<style	',
				' style="','	style="',
				' style=\'','	style=\'',
			],
			$html
		);
	}
}