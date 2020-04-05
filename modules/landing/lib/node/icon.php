<?php
namespace Bitrix\Landing\Node;

class Icon extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Icon';
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
			$classList = (isset($value['classList']) && is_array($value['classList']))
						? $value['classList']
						: (array)$value;
			$className = implode(' ', $classList);
			$url = isset($value['url']) ? trim($value['url']) : '';

			if ($classList)
			{
				if (isset($resultList[$pos]))
				{
					$resultList[$pos]->setAttribute('class', $className);
					if ($url)
					{
						$resultList[$pos]->setAttribute('data-pseudo-url', $url);
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
				'classList' => [$res->getAttribute('class')]
			);
			$pseudoUrl = $res->getAttribute('data-pseudo-url');
			if ($pseudoUrl)
			{
				$data[$pos]['data-pseudo-url'] = $pseudoUrl;
			}
		}

		return $data;
	}
}