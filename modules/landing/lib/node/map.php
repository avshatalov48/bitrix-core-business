<?php
namespace Bitrix\Landing\Node;

use Bitrix\Landing\History;

class Map extends \Bitrix\Landing\Node
{
	protected const ATTR_MAP = 'data-map';

	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Node.Map';
	}

	/**
	 * Save data for this node.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveNode(\Bitrix\Landing\Block $block, $selector, array $data)
	{
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);

		foreach ($data as $pos => $value)
		{
			if (isset($resultList[$pos]) && $value)
			{
				if (History::isActive())
				{
					$history = new History($block->getLandingId(), History::ENTITY_TYPE_LANDING);
					$history->push('EDIT_MAP', [
						'block' => $block,
						'selector' => $selector,
						'position' => (int)$pos,
						'valueBefore' => $resultList[$pos]->getAttributes()[self::ATTR_MAP]->getValue(),
						'valueAfter' => $value,
					]);
				}

				$resultList[$pos]->setAttribute(
					self::ATTR_MAP,
					$value
				);
			}
		}
	}

	/**
	 * Get data for this node.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getNode(\Bitrix\Landing\Block $block, $selector)
	{
		$data = array();
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);

		foreach ($resultList as $pos => $res)
		{
			$data[$pos] = $res->getAttribute(self::ATTR_MAP);
		}

		return $data;
	}
}