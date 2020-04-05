<?php
namespace Bitrix\Landing\Node;

use \Bitrix\Landing\File;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Web\DOM\StyleInliner;

class Img extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Img';
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
			// 2x – this for retina support

			$src = isset($value['src']) ? trim($value['src']) : '';
			$src2x = isset($value['src2x']) ? trim($value['src2x']) : '';
			$alt = isset($value['alt']) ? trim($value['alt']) : '';
			$url = isset($value['url']) ? trim($value['url']) : '';
			$id = isset($value['id']) ? intval($value['id']) : 0;
			$id2x = isset($value['id2x']) ? intval($value['id2x']) : 0;

			if (isset($resultList[$pos]))
			{
				if ($resultList[$pos]->getTagName() !== 'IMG')
				{
					$styles = StyleInliner::getStyle($resultList[$pos]);
					$oldStyles = [];
					$newStyles = [];
					// collect existing styles
					foreach ($styles as $key => $styleValue)
					{
						if ($key !== 'background' && $key !== 'background-image')
						{
							$oldStyles[] = "{$key}: {$styleValue};";
						}
					}
					// add images to bg
					if ($src)
					{
						// and one two additional bg
						$newStyles = [
							"background-image: url('{$src}');"
						];
						if ($src2x)
						{
							$newStyles = array_merge(
								$newStyles,
								[
									"background-image: -webkit-image-set(url('{$src}') 1x, url('{$src2x}') 2x);",
									"background-image: image-set(url('{$src}') 1x, url('{$src2x}') 2x);"
								]
							);
						}
					}
					// or remove exists
					else
					{
						foreach (['fileid', 'fileid2x'] as $dataCode)
						{
							$oldId = $resultList[$pos]->getAttribute(
								'data-' . $dataCode
							);
							if ($oldId > 0)
							{
								File::deleteFromBlock(
									$block->getId(),
									$oldId
								);
							}
						}
					}

					$style = array_merge($oldStyles, $newStyles);
					$style = implode(' ', $style);
					$resultList[$pos]->setAttribute('style', $style);
				}
				else
				{
					$resultList[$pos]->setAttribute('alt', $alt);
					$resultList[$pos]->setAttribute('src', $src);
					if ($src2x)
					{
						$resultList[$pos]->setAttribute('srcset', "{$src2x} 2x");
					}
				}
				if ($id)
				{
					$resultList[$pos]->setAttribute('data-fileid', $id);
				}
				if ($id2x)
				{
					$resultList[$pos]->setAttribute('data-fileid2x', $id2x);
				}
				if ($url)
				{
					$resultList[$pos]->setAttribute('data-pseudo-url', $url);
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
			if ($res->getTagName() !== 'IMG')
			{
				$styles = StyleInliner::getStyle($res);
				if (isset($styles['background-image']))
				{
					$src = $src2x = null;
					// try gets retina srcset
					if (
						preg_match_all(
							'/url\(\'*([^\']+)\'*\)\s*([\d]*x*)/is',
							$styles['background-image'],
							$matches
						)
					)
					{
						for ($i = 0, $c = count($matches[1]); $i < $c; $i++)
						{
							if ($matches[2][$i] == 2)
							{
								$src2x = $matches[1][$i];
							}
							else
							{
								$src = $matches[1][$i];
							}
						}
					}
					if ($src || $src2x)
					{
						$data[$pos] = [];
						if ($src)
						{
							$data[$pos]['src'] = $src;
						}
						if ($src2x)
						{
							$data[$pos]['src2x'] = $src2x;
						}
					}
				}
			}
			else
			{
				$src = $res->getAttribute('src');
				$srcSet = $res->getAttribute('srcset');

				$data[$pos] = array(
					'alt' => $res->getAttribute('alt'),
					'src' => Manager::getUrlFromFile($src),
				);
				if (preg_match('/[\,\s]*(.*?)\s+2x/is', $srcSet, $matches))
				{
					$data[$pos]['src2x'] = $matches[1];
				}
			}
			$pseudoUrl = $res->getAttribute('data-pseudo-url');
			if ($pseudoUrl)
			{
				$data[$pos]['data-pseudo-url'] = $pseudoUrl;
			}
		}

		return $data;
	}
}