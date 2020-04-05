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
				$value = \Bitrix\Landing\Manager::sanitize($value, $bad);
				// clear some amp
				$value = preg_replace('/&amp;([^\s]{1})/is', '&$1', $value);
				$value = str_replace(
					' bxstyle="',
					' style="',
					$value
				);
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
			$data[$pos] = str_replace(
				' style="',
				' bxstyle="',
				$data[$pos]
			);
		}

		return $data;
	}
}