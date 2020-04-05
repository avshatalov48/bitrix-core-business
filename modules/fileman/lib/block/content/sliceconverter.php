<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Fileman\Block\Content;

use Bitrix\Main\Localization\Loc;
use Bitrix\Fileman\Block;

Loc::loadMessages(__FILE__);

class SliceConverter implements IConverter
{
	CONST SLICE_SECTION_ID = 'BX_BLOCK_EDITOR_EDITABLE_SECTION';

	/**
	 * Check string.
	 *
	 * @param string $string String.
	 * @return bool
	 */
	public static function isValid($string)
	{
		$result = true;
		$string = strtoupper($string);
		if(strpos($string, '<!--START ' . static::SLICE_SECTION_ID . '/') === false)
		{
			$result = false;
		}
		if(strpos($string, '<!--END ' . static::SLICE_SECTION_ID . '/') === false)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Parse string of sliced content to an array of content blocks.
	 *
	 * @param string $string String.
	 * @return BlockContent
	 */
	public static function toArray($string)
	{
		$blockContent = new BlockContent();
		$pattern = '#<!--START '
			. static::SLICE_SECTION_ID . '/([\w]+?)/([\w]+?)/-->'
			. '([\s\S,\n]*?)'
			. '<!--END ' . static::SLICE_SECTION_ID . '[/\w]+?-->#';

		$matches = array();
		if(preg_match_all($pattern, $string, $matches))
		{
			$matchesCount = count($matches[0]);
			for($i = 0; $i < $matchesCount; $i++)
			{
				$section = trim($matches[1][$i]);
				$place = trim($matches[2][$i]);
				$value = trim($matches[3][$i]);

				$blockContent->add($section, $place, $value);
			}
		}

		return $blockContent;
	}

	/**
	 * Convert block content to string.
	 *
	 * @param BlockContent $content Block content.
	 * @return string
	 */
	public static function toString(BlockContent $content)
	{
		$result = '';
		foreach ($content->getList() as $item)
		{
			$result .= '<!--START '
				. static::SLICE_SECTION_ID . "/{$item['type']}/{$item['place']}/-->\n"
				. $item['value'] . "\n"
				. '<!--END '
				. static::SLICE_SECTION_ID . "/{$item['type']}/{$item['place']}/-->\n";
		}

		return trim($result);
	}

	/**
	 * Sanitize.
	 *
	 * @param string $string String.
	 * @return string
	 */
	public static function sanitize($string)
	{
		if (!self::isValid($string))
		{
			return Block\Sanitizer::clean($string);
		}

		$content = self::toArray($string);
		$list = $content->getList();
		foreach ($list as $index => $item)
		{
			if ($item['type'] !== BlockContent::TYPE_BLOCKS)
			{
				continue;
			}

			$item['value'] = Block\Sanitizer::clean($item['value']);
			$list[$index] = $item;
		}

		$content->setList($list);
		return self::toString($content);
	}
}