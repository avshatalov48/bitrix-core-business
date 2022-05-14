<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Fileman\Block;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Sanitizer
 * @package Bitrix\Fileman\Block
 */
class Sanitizer
{
	/**
	 * Clean Html.
	 *
	 * @param string $html Html.
	 * @return string
	 */
	public static function clean($html)
	{
		$tags = self::getTags() + array(
			'html' => array('xmlns'),
			'head' => array(),
			'body' => array(),
			'meta' => array('content', 'name', 'http-equiv'),
			'title' => array(),
			'style' => array(Editor::STYLIST_TAG_ATTR, 'type'),
			'link' => array('type', 'rel', 'href'),
		);

		$commonAttributes = self::getCommonAttributes();
		foreach ($tags as $tagName => $attributes)
		{
			$tags[$tagName] = array_merge($attributes, $commonAttributes);
		}

		$sanitizer = new \CBXSanitizer();
		$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
		$sanitizer->addTags($tags);
		$sanitizer->allowAttributes([
			Editor::BLOCK_PHP_ATTR => [
				'tag' => function ()
				{
					return true;
				},
				'content' => function ()
				{
					return true;
				},
			]
		]);
		$sanitizer->applyDoubleEncode(false);

		$storedMap = self::replacePhpToTags($html);
		$html = $sanitizer->sanitizeHtml($html);
		self::replaceTagsToPhp($html, $storedMap);

		return $html;
	}

	protected static function getCommonAttributes()
	{
		return array(
			Editor::BLOCK_PHP_ATTR,
			'style', 'id', 'class', 'color', 'align', 'valign',
			'height', 'width', 'title', 'style', 'class',
			'dir', 'role',
			Editor::BLOCK_PLACE_ATTR,
			'data-bx-block-editor-block-type'
		);
	}

	protected static function getTags()
	{
		$tags = array(
			'a'	=> array('href', 'title','name','style','id','class','shape','coords','alt','target'),
			'b'	=> array('style','id','class'),
			'br' => array('style','id','class'),
			'big' => array('style','id','class'),
			'blockquote' => array('title','style','id','class'),
			'caption' => array('style','id','class'),
			'code' => array('style','id','class'),
			'del' => array('title','style','id','class'),
			'div' => array('title','style','id','class','align'),
			'dt' => array('style','id','class'),
			'dd' => array('style','id','class'),
			'font' => array('color','size','face','style','id','class'),
			'h1' => array('style','id','class','align'),
			'h2' => array('style','id','class','align'),
			'h3' => array('style','id','class','align'),
			'h4' => array('style','id','class','align'),
			'h5' => array('style','id','class','align'),
			'h6' => array('style','id','class','align'),
			'hr' => array('style','id','class'),
			'i' => array('style','id','class'),
			'img' => array('src','alt','height','width','title'),
			'ins' => array('title','style','id','class'),
			'li' => array('style','id','class'),
			'map' => array('shape','coords','href','alt','title','style','id','class','name'),
			'ol' => array('style','id','class'),
			'p'	=> array('style','id','class','align'),
			'pre' => array('style','id','class'),
			's'	=> array('style','id','class'),
			'small'	=> array('style','id','class'),
			'strong' => array('style','id','class'),
			'span' => array('title','style','id','class','align'),
			'sub' => array('style','id','class'),
			'sup' => array('style','id','class'),
			'table' => array('border','width','style','id','class','cellspacing','cellpadding'),
			'tbody'	=> array('align','valign','style','id','class'),
			'td' => array('width','height','style','id','class','align','valign','colspan','rowspan'),
			'tfoot' => array('align','valign','style','id','class','align','valign'),
			'th' => array('width','height','style','id','class','colspan','rowspan'),
			'thead'	=> array('align','valign','style','id','class'),
			'tr' => array('align','valign','style','id','class'),
			'u'	=> array('style','id','class'),
			'ul' => array('style','id','class'),
			'php' => array('id'),
		);

		return $tags;
	}

	private static function replacePhpToTags(&$html)
	{
		if(!preg_match_all('/(<\?[\W\w\n]*?\?>)/i', $html, $matches, PREG_SET_ORDER))
		{
			return [];
		}

		if (!is_array($matches))
		{
			return [];
		}

		$stored = [];
		$counter = 0;
		foreach($matches as $key => $value)
		{
			$counter++;
			$stored["<php id=\"{$counter}\"></php>"] = $value[0];
		}

		$html = str_replace(
			array_values($stored),
			array_keys($stored),
			$html
		);

		return $stored;
	}

	private static function replaceTagsToPhp(&$html, array $stored = [])
	{
		$html = str_replace(
			array_keys($stored),
			array_values($stored),
			$html
		);

		return $html;
	}
}