<?php
namespace Bitrix\Landing\Node;

use Bitrix\Landing\Block;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Manager;
use Bitrix\Landing\Node;
use \Bitrix\Main\Web\DOM\StyleInliner;

/**
 * Fake node for images, then add in design. Not using in edit panel!
 */
class StyleImg extends Node
{
	public const STYLES_WITH_IMAGE = [
		'background',
		'block-default',
		'block-border',
	];

	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.StyleImg';
	}

	/**
	 * Save data for this node.
	 * @param Block $block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveNode(Block $block, $selector, array $data)
	{
		$resultList = Node\Style::getNodesBySelector($block, $selector);
		$files = null;

		foreach ($data as $pos => $value)
		{
			$id = isset($value['id']) ? (int)$value['id'] : 0;
			$id2x = isset($value['id2x']) ? (int)$value['id2x'] : 0;

			if ($id || $id2x)
			{
				if ($files === null)
				{
					$files = File::getFilesFromBlock($block->getId());
				}
				if (!in_array($id, $files))
				{
					$id = 0;
				}
				if (!in_array($id2x, $files))
				{
					$id2x = 0;
				}
			}

			// todo: lazy?
			// $isLazy = isset($value['isLazy']) && $value['isLazy'] === 'Y';
			if (isset($resultList[$pos]))
			{
				// check permissions to this file ids

				// update in content
				if ($resultList[$pos]->getTagName() !== 'IMG')
				{
					$styles = StyleInliner::getStyle($resultList[$pos]);
					if (!isset($styles['background']) && !isset($styles['background-image']))
					{
						if ($id)
						{
							$resultList[$pos]->setAttribute('data-fileid', $id);
						}
						// else
						// {
						// 	$resultList[$pos]->removeA('data-fileid', $id)
						// }

						if ($id2x)
						{
							$resultList[$pos]->setAttribute('data-fileid2x', $id2x);
						}
						// todo: if empty values - del attrs
						// todo: del old files
					}
				}
			}
		}
	}

	/**
	 * Get data for this node.
	 * @param Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getNode(Block $block, $selector): array
	{
		$data = array();
		$resultList = Node\Style::getNodesBySelector($block, $selector);

		foreach ($resultList as $pos => $res)
		{
			if ($res->getTagName() !== 'IMG')
			{
				$styles = StyleInliner::getStyle($res);
				if (!isset($styles['background']) && !isset($styles['background-image']))
				{
					// $src = $src2x = null;

					if ($fileId = $res->getAttribute('data-fileid'))
					{
						// todo: check if file exists by ID
						// todo: check $files = File::getFilesFromBlock($block->getId());
						$data[$pos]['id'] = $fileId;
					}
					if ($fileId2x = $res->getAttribute('data-fileid2x'))
					{
						// todo: check if file exists by ID
						// todo: check $files = File::getFilesFromBlock($block->getId());
						$data[$pos]['id2x'] = $fileId2x;
					}

					// todo: lazyload?

					// // try gets retina srcset
					// if (
					// 	preg_match_all(
					// 		'/url\(\'*([^\']+)\'*\)\s*([\d]*x*)/is',
					// 		$styles['background-image'],
					// 		$matches
					// 	)
					// )
					// {
					// 	for ($i = 0, $c = count($matches[1]); $i < $c; $i++)
					// 	{
					// 		if ($matches[2][$i] == 2)
					// 		{
					// 			$src2x = $matches[1][$i];
					// 		}
					// 		else
					// 		{
					// 			$src = $matches[1][$i];
					// 		}
					// 	}
					// }
					// if ($src || $src2x)
					// {
					// 	$data[$pos] = [];
					// 	if ($src)
					// 	{
					// 		$data[$pos]['src'] = Manager::getUrlFromFile($src);
					// 	}
					// 	if ($src2x)
					// 	{
					// 		$data[$pos]['src2x'] = Manager::getUrlFromFile($src2x);
					// 	}
					// }
					//
					// // for lazyload
					// if(
					// 	($isLazy = $res->getAttribute('data-lazy-bg'))
					// 	&& $isLazy === 'Y'
					// )
					// {
					// 	$data[$pos]['isLazy'] = 'Y';
					// 	if($lazyOrigSrc = $res->getAttribute('data-src'))
					// 	{
					// 		$data[$pos]['lazyOrigSrc'] = $lazyOrigSrc;
					// 	}
					// 	if($lazyOrigSrc2x = $res->getAttribute('data-src2x'))
					// 	{
					// 		$data[$pos]['lazyOrigSrc2x'] = $lazyOrigSrc2x;
					// 	}
					// 	if($lazyOrigStyle = $res->getAttribute('data-style'))
					// 	{
					// 		$data[$pos]['lazyOrigStyle'] = $lazyOrigStyle;
					// 	}
					// }
				}
			}


		}

		return $data;
	}

	/**
	 * This node may participate in searching.
	 * @param Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getSearchableNode($block, $selector): array
	{
		return [];
	}
}