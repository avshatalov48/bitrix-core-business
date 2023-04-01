<?php
namespace Bitrix\Landing\Node;

use Bitrix\Landing\History;
use Bitrix\Main\Web\DOM\StyleInliner;

class Embed extends \Bitrix\Landing\Node
{
	protected const RATIO_CLASSES = [
		'embed-responsive-16by9',
		'embed-responsive-9by16',
		'embed-responsive-4by3',
		'embed-responsive-3by4',
		'embed-responsive-21by9',
		'embed-responsive-9by21',
		'embed-responsive-1by1',
	];

	protected const CONTAINER_CLASS = 'embed-responsive';

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
		$valueBefore = Embed::getNode($block, $selector);

		foreach ($data as $pos => $value)
		{
			if (isset($resultList[$pos]))
			{
				if (isset($value['src']) && $value['src'])
				{
					if ($resultList[$pos]->getTagName() === 'IFRAME')
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

				// set ratio
				if (
					isset($value['ratio'], $value['src'])
					&& $value['ratio'] && $value['src']
					&& $valueBefore['src'] !== $value['src']
					&& in_array($value['ratio'], self::RATIO_CLASSES)
				)
				{
					$containerNode = $resultList[$pos]->getParentNode();
					if ($containerNode && in_array(self::CONTAINER_CLASS, $containerNode->getClassList()))
					{
						$classes = $containerNode->getClassList();
						$classes = array_diff($classes, self::RATIO_CLASSES);
						$classes[] = $value['ratio'];
						$containerNode->setClassList($classes);
					}
				}

				// set preview image
                $styles = [];
                foreach (StyleInliner::getStyle($resultList[$pos]) as $key => $stylesValue)
                {
                    if ($key !== 'background' && $key !== 'background-image')
                    {
                        $styles[] = "{$key}: {$stylesValue};";
                    }
                }
				if (isset($value['preview']) && $value['preview'])
				{
					$styles[] = "background-image: url('{$value['preview']}');";
					$resultList[$pos]->setAttribute('data-preview', $value['preview']);
				}
				else
                {
                    $resultList[$pos]->removeAttribute('data-preview');
                }
                $styles = implode(' ', $styles);
                $resultList[$pos]->setAttribute('style', $styles);
			}

			if (History::isActive())
			{
				$history = new History($block->getLandingId(), History::ENTITY_TYPE_LANDING);
				$history->push('EDIT_EMBED', [
					'block' => $block,
					'selector' => $selector,
					'position' => (int)$pos,
					'valueBefore' => $valueBefore[$pos],
					'valueAfter' => $value,
				]);
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
			$ratio = '';
			$containerNode = $res->getParentNode();
			if ($containerNode && in_array(self::CONTAINER_CLASS, $containerNode->getClassList()))
			{
				$ratio = array_intersect($containerNode->getCLassList(), self::RATIO_CLASSES);
				$ratio = empty($ratio) ? '' : array_shift($ratio);
			}

			$data[$pos] = array(
				'src' => $res->getAttribute('data-src') ?: $res->getAttribute('src'),
				'source' => $res->getAttribute('data-source'),
				'preview' => $res->getAttribute('data-preview'),
				'ratio' => $ratio,
			);
		}

		return $data;
	}
}