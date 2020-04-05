<?php
namespace Bitrix\Landing\Node;

class Text extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Text';
	}

	/**
	 * Sanitize bad html.
	 * @param string $str Very bad html.
	 * @return string
	 */
	private static function sanitize($str)
	{
		static $sanitizer = null;

		if ($sanitizer === null)
		{
			$sanitizer = new \CBXSanitizer;
			$sanitizer->addTags(array(
				'b' => array(),
				'i' => array(),
				'u' => array(),
				'br' => array(),
				'li' => array('class'),
				'p' => array('style'),
				'a' => array('href', 'target', 'data-embed', 'data-url'),
				'span' => array('style'),
				'font' => array('color')
			));
			$sanitizer->deleteSanitizedTags(false);
			$sanitizer->applyHtmlSpecChars(true);
		}

		return $sanitizer->sanitizeHtml($str);
	}

	/**
	 * Save data for this node.
	 * @param \Bitrix\Landing\Block &$block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveNode(\Bitrix\Landing\Block &$block, $selector, array $data)
	{
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);

		foreach ($data as $pos => $value)
		{
			$value = trim($value);
			if (isset($resultList[$pos]))
			{
				$value = self::sanitize($value);
				// clear some amp
				$value = preg_replace('/&amp;([^\s]{1})/is', '&$1', $value);
				$resultList[$pos]->setInnerHTML(!$value ? ' ' : $value);
			}
		}
	}

	/**
	 * Get data for this node.
	 * @param \Bitrix\Landing\Block &$block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getNode(\Bitrix\Landing\Block &$block, $selector)
	{
		$data = array();
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);

		foreach ($resultList as $pos => $res)
		{
			$data[$pos] = $res->getInnerHTML();
		}

		return $data;
	}
}