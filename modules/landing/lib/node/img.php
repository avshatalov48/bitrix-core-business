<?php

namespace Bitrix\Landing\Node;

use \Bitrix\Landing\File;
use Bitrix\Landing\History;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Web\DOM\StyleInliner;
use \Bitrix\Landing\Node;

class Img extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Node.Img';
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
		$valueBefore = static::getNode($block, $selector);
		$files = null;

		foreach ($data as $pos => $value)
		{
			// 2x - this for retina support
			$src = (isset($value['src']) && is_string($value['src'])) ? trim($value['src']) : '';
			$src2x = (isset($value['src2x']) && is_string($value['src2x'])) ? trim($value['src2x']) : '';
			$alt = (isset($value['alt']) && is_string($value['alt'])) ? trim($value['alt']) : '';
			$id = isset($value['id']) ? intval($value['id']) : 0;
			$id2x = isset($value['id2x']) ? intval($value['id2x']) : 0;
			$isLazy = isset($value['isLazy']) && $value['isLazy'] === 'Y';

			if ($src)
			{
				$src = str_replace('http://', 'https://', $src);
			}
			if ($src2x)
			{
				$src2x = str_replace('http://', 'https://', $src2x);
			}

			if (isset($value['url']))
			{
				$url = is_array($value['url'])
					? json_encode($value['url'])
					: $value['url'];
			}
			else
			{
				$url = '';
			}

			if (isset($resultList[$pos]))
			{
				// check permissions to this file ids
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
				// update in content
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
							"background-image: url('{$src}');",
						];
						if ($src2x)
						{
							$newStyles = array_merge(
								$newStyles,
								[
									"background-image: -webkit-image-set(url('{$src}') 1x, url('{$src2x}') 2x);",
									"background-image: image-set(url('{$src}') 1x, url('{$src2x}') 2x);",
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

					// for lazyload
					if ($isLazy)
					{
						$resultList[$pos]->setAttribute('data-lazy-bg', 'Y');
						$lazyOrigSrc = ($value['lazyOrigSrc'] ?? null);
						if ($lazyOrigSrc)
						{
							$resultList[$pos]->setAttribute('data-src', $lazyOrigSrc);
						}
						$lazyOrigSrc2x = ($value['lazyOrigSrc2x'] ?? null);
						if ($lazyOrigSrc2x)
						{
							$resultList[$pos]->setAttribute('data-src2x', $lazyOrigSrc2x);
						}
						$lazyOrigStyle = ($value['lazyOrigStyle'] ?? null);
						if ($lazyOrigStyle)
						{
							$resultList[$pos]->setAttribute('data-style', $lazyOrigStyle);
						}
					}
				}
				else
				{
					$resultList[$pos]->setAttribute('alt', $alt);
					$resultList[$pos]->setAttribute('src', $src);
					if ($src2x)
					{
						$resultList[$pos]->setAttribute('srcset', "{$src2x} 2x");
					}
					else
					{
						$resultList[$pos]->setAttribute('srcset', '');
					}

					// for lazyload
					if ($isLazy)
					{
						$resultList[$pos]->setAttribute('data-lazy-img', 'Y');
						$resultList[$pos]->setAttribute('loading', 'lazy');
						if ($lazyOrigSrc = $value['lazyOrigSrc'])
						{
							$resultList[$pos]->setAttribute('data-src', $lazyOrigSrc);
						}
						if ($lazyOrigSrcset = $value['lazyOrigSrcset'])
						{
							$resultList[$pos]->setAttribute('data-srcset', $lazyOrigSrcset);
						}
					}
				}
				$id
					? $resultList[$pos]->setAttribute('data-fileid', $id)
					: $resultList[$pos]->removeAttribute('data-fileid')
				;
				$id2x
					? $resultList[$pos]->setAttribute('data-fileid2x', $id2x)
					: $resultList[$pos]->removeAttribute('data-fileid2x')
				;
				$url
					? $resultList[$pos]->setAttribute('data-pseudo-url', $url)
					: $resultList[$pos]->removeAttribute('data-pseudo-url')
				;

				if (History::isActive())
				{
					$history = new History($block->getLandingId(), History::ENTITY_TYPE_LANDING);
					$history->push('EDIT_IMG', [
						'block' => $block,
						'selector' => $selector,
						'position' => (int)$pos,
						'valueBefore' => $valueBefore[$pos],
						'valueAfter' => $value,
					]);
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
		$data = [];
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);
		if (!$resultList)
		{
			$resultList = Node\Style::getNodesBySelector($block, $selector);
		}

		foreach ($resultList as $pos => $res)
		{
			$data[$pos] = [
				'src' => '',
				'src2x' => '',
				'id' => null,
				'id2x' => null,
				'alt' => '',
				'isLazy' => 'N',
			];

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
							if ($matches[2][$i] === '2x')
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
						if ($src)
						{
							$data[$pos]['src'] = Manager::getUrlFromFile($src);
						}
						if ($src2x)
						{
							$data[$pos]['src2x'] = Manager::getUrlFromFile($src2x);
						}
					}

					// for lazyload
					if (
						($isLazy = $res->getAttribute('data-lazy-bg'))
						&& $isLazy === 'Y'
					)
					{
						$data[$pos]['isLazy'] = 'Y';
						if ($lazyOrigSrc = $res->getAttribute('data-src'))
						{
							$data[$pos]['lazyOrigSrc'] = $lazyOrigSrc;
						}
						if ($lazyOrigSrc2x = $res->getAttribute('data-src2x'))
						{
							$data[$pos]['lazyOrigSrc2x'] = $lazyOrigSrc2x;
						}
						if ($lazyOrigStyle = $res->getAttribute('data-style'))
						{
							$data[$pos]['lazyOrigStyle'] = $lazyOrigStyle;
						}
					}
				}
			}
			else
			{
				$src = $res->getAttribute('src');
				$srcSet = $res->getAttribute('srcset');

				$data[$pos]['src'] = Manager::getUrlFromFile($src);
				$data[$pos]['alt'] = $res->getAttribute('alt');

				if (preg_match('/[\,\s]*(.*?)\s+2x/is', $srcSet, $matches))
				{
					$data[$pos]['src2x'] = Manager::getUrlFromFile($matches[1]);
				}

				// for lazyload
				$isLazy = $res->getAttribute('data-lazy-img');
				if ($isLazy === 'Y')
				{
					$data[$pos]['isLazy'] = 'Y';
					$lazyOrigSrc = $res->getAttribute('data-src');
					if ($lazyOrigSrc)
					{
						$data[$pos]['lazyOrigSrc'] = $lazyOrigSrc;
					}
					$lazyOrigSrcset = $res->getAttribute('data-srcset');
					if ($lazyOrigSrcset)
					{
						if (
							preg_match('/([^ ]+) 2x/i', $lazyOrigSrcset, $matches)
							&& $matches[1]
						)
						{
							$data[$pos]['lazyOrigSrc2x'] = $matches[1];
						}
						// comment just for changes
						$data[$pos]['lazyOrigSrcset'] = $lazyOrigSrcset;
					}
				}
			}

			if ($val = $res->getAttribute('data-pseudo-url'))
			{
				$data[$pos]['url'] = $val;
			}

			if ($val = $res->getAttribute('data-fileid'))
			{
				$data[$pos]['id'] = $val;
			}

			if (
				(isset($data[$pos]['src2x']) || isset($data[$pos]['lazyOrigSrc2x']))
				&& ($val = $res->getAttribute('data-fileid2x'))
			)
			{
				$data[$pos]['id2x'] = $val;
			}
		}

		return $data;
	}

	/**
	 * This node may participate in searching.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getSearchableNode($block, $selector)
	{
		$searchContent = [];

		$nodes = self::getNode($block, $selector);
		foreach ($nodes as $node)
		{
			if (!isset($node['alt']))
			{
				continue;
			}
			$node['alt'] = self::prepareSearchContent($node['alt']);
			if ($node['alt'] && !in_array($node['alt'], $searchContent))
			{
				$searchContent[] = $node['alt'];
			}
		}

		return $searchContent;
	}

	/**
	 * Change node type if is styleImg type.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $node Selector.
	 * @return array
	 */
	public static function changeNodeType(array $node, \Bitrix\Landing\Block $block): array
	{
		$matches = [];
		$pattern = '/' . substr($node['code'], 1) . '[^\"]*/i';
		if (preg_match($pattern, $block->getContent(), $matches) === 1)
		{
			$pattern = '/[\s]?g-bg-image[\s]?/i';
			if (preg_match($pattern, $matches[0]) === 1)
			{
				$node['type'] = 'styleimg';
				$node['handler'] = StyleImg::getHandlerJS();
			}
		}

		return $node;
	}
}