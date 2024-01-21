<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Security;

use Bitrix\Main\Loader;
use Bitrix\Fileman;

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
	 * @param string $previousHtml Previous html text.
	 * @param User $user User instance.
	 * @return string|null
	 */
	public static function sanitizeHtml($html, $previousHtml = '', User $user = null)
	{
		$html = self::cleanHtml($html);
		return self::removePhp($html, $previousHtml, $user);
	}

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

		$html = preg_replace('/<(script|iframe)(.*?)>(.*?)(<\\/\\1.*?>)/is', '', $html);
		if (Loader::includeModule('fileman'))
		{
			$html = Fileman\Block\Content\SliceConverter::sanitize($html);
		}

		return $html;
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

	/**
	 * Fix template styles.
	 *
	 * @param string $html Html text.
	 * @return string|null
	 */
	public static function fixTemplateStyles($html)
	{
		$html = str_replace('<body class="">{}', '<body class="">', $html);
		$html = str_replace('</style>{}', '</style>', $html);

		if (!$html)
		{
			return $html;
		}

		$html = preg_replace('/<st yle.*?>(.*?)<\/style>/is', '</style>', $html);
		$html = preg_replace('/<\/style>([\s]*?)<\/style>/is', '</style>', $html);

		return $html;
	}

	/**
	 * Remove php from string with checking operations edit_php and lpa_template_edit
	 *
	 * @param string $string String.
	 * @param User $user User instance.
	 * @return bool
	 */
	public static function removePhp($string = '', $previousString, User $user = null)
	{
		$user = $user ?: User::current();
		Loader::includeModule('fileman');
		return Fileman\Block\EditorMail::removePhpFromHtml(
			$string,
			$previousString,
			$user->canEditPhp(),
			$user->canUseLpa()
		);
	}
}
