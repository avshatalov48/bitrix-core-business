<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Fileman\Block\Content;

use Bitrix\Main\Application;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

interface IConverter
{
	/**
	 * Check string.
	 *
	 * @param string $string String.
	 * @return bool
	 */
	public static function isValid($string);

	/**
	 * Parse string to an array of content blocks
	 *
	 * @param string $string String.
	 * @return BlockContent
	 */
	public static function toArray($string);
}