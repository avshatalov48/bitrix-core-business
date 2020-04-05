<?php
namespace Bitrix\Landing\Node;

class Link extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Link';
	}

	/**
	 * Allowed or not this target.
	 * @param string $target Type of target.
	 * @return boolean
	 */
	protected static function isAllowedTarget($target)
	{
		return in_array($target, array('_self', '_blank', '_popup'));
	}

	/**
	 * Allowed attrs.
	 * @return array
	 */
	protected static function allowedAttrs()
	{
		return array('data-embed', 'data-url');
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
			$text = isset($value['text']) ? trim($value['text']) : '';
			$href = isset($value['href']) ? trim($value['href']) : '';
			$target = isset($value['target']) ? trim(strtolower($value['target'])) : '';
			$attrs = isset($value['attrs']) ? (array)$value['attrs'] : array();

			if (isset($resultList[$pos]))
			{
				if (trim($resultList[$pos]->getTextContent()) != '' && $text != '')
				{
					$text = \htmlspecialcharsbx($text);
					$resultList[$pos]->setInnerHTML($text);
				}
				if ($href != '')
				{
					$resultList[$pos]->setAttribute('href', $href);
				}
				if (self::isAllowedTarget($target))
				{
					$resultList[$pos]->setAttribute('target', $target);
				}
				if (!empty($attrs))
				{
					$allowedAttrs = self::allowedAttrs();
					foreach ($attrs as $code => $val)
					{
						if ($val && in_array($code, $allowedAttrs))
						{
							$resultList[$pos]->setAttribute($code, $val);
						}
					}
				}
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
			$data[$pos] = array(
				'text' => $res->getInnerHTML(),
				'href' => $res->getAttribute('href'),
				'target' => $res->getAttribute('target'),
				'attrs' => array(
					'data-embed' => $res->getAttribute('data-embed'),
					'data-url' => $res->getAttribute('data-url')
				)
			);
		}

		return $data;
	}
}