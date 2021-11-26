<?php

namespace Bitrix\Landing\Node;

use Bitrix\Landing\Block;
use Bitrix\Main\Web\DOM\Node;
use Bitrix\Main\Web\DOM\StyleInliner;

class Style
{
	public static function getStyle(Block $block, string $selector): array
	{
		$data = [];
		$resultList = self::getNodesBySelector($block, $selector);

		foreach ($resultList as $pos => $res)
		{
			if ($res->getNodeType() === $res::ELEMENT_NODE)
			{
				$classList = trim($res->getAttribute('class'));
				if ($classList)
				{
					$data['classList'][$pos] = $classList;
				}

				$styles = StyleInliner::getStyle($res);
				$stylesPrepared = [];
				foreach ($styles as $key => $style)
				{
					if ($style && $key !== 'background-image')
					{
						$stylesPrepared[$key] = $style;
					}
				}
				if (!empty($stylesPrepared))
				{
					$data['style'][$pos] = $stylesPrepared;

				}
			}
		}

		return $data;
	}

	public static function getNodesBySelector(Block $block, string $selector): array
	{
		$doc = $block->getDom();

		// prepare wrapper
		$wrapper = '#' . $block->getAnchor($block->getId());
		if ($selector === '#wrapper')
		{
			$selector = '#block' . $block->getId();
		}

		// nodes for get
		if ($selector === $wrapper)
		{
			$wrapperNode = [];
			foreach ($doc->getChildNodesArray() as $node)
			{
				if ($node->getNodeType() === Node::ELEMENT_NODE)
				{
					$wrapperNode[] = $node;
					break;
				}
			}

			return $wrapperNode;
		}

		return $doc->querySelectorAll($selector);
	}
}