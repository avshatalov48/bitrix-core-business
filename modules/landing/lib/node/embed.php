<?php
namespace Bitrix\Landing\Node;

class Embed extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Embed';
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
			if (isset($resultList[$pos]))
			{
				if (isset($value['src']) && $value['src'])
				{
					$resultList[$pos]->setAttribute('src', $value['src']);
				}
				if (isset($value['source']) && $value['source'])
				{
					$resultList[$pos]->setAttribute('data-source', $value['source']);
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
				'src' => $res->getAttribute('src'),
				'data-source' => $res->getAttribute('data-source')
			);
		}

		return $data;
	}
}