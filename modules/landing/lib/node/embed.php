<?php
namespace Bitrix\Landing\Node;

use Bitrix\Main\Web\DOM\StyleInliner;

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
			if (isset($resultList[$pos]))
			{
				if (isset($value['src']) && $value['src'])
				{
					if($resultList[$pos]->getTagName() === 'IFRAME')
					{
						$resultList[$pos]->setAttribute('src', $value['src']);
					}
					else
					{
						$resultList[$pos]->setAttribute('data-src', $value['src']);
					}
				}
				if (isset($value['source']) && $value['source'])
				{
					$resultList[$pos]->setAttribute('data-source', $value['source']);
				}
				if (isset($value['preview']) && $value['preview'])
				{
					$styles = [];
					foreach (StyleInliner::getStyle($resultList[$pos]) as $key => $stylesValue)
					{
						if ($key !== 'background' && $key !== 'background-image')
						{
							$styles[] = "{$key}: {$stylesValue};";
						}
					}
					$styles[] = "background-image: url('{$value['preview']}');";
					$styles = implode(' ', $styles);
					$resultList[$pos]->setAttribute('style', $styles);
					$resultList[$pos]->setAttribute('data-preview', $value['preview']);
				}
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
			$data[$pos] = array(
				'src' => $res->getAttribute('data-src') ?: $res->getAttribute('src'),
				'source' => $res->getAttribute('data-source'),
				'preview' => $res->getAttribute('data-preview')
			);
		}

		return $data;
	}
}