<?php
namespace Bitrix\Landing\Node;

use Bitrix\Landing\Block;
use Bitrix\Landing\File;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Node;
use Bitrix\Main\Web\DOM;

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

	protected const STYLES_VARIABLES_WITH_FILES = [
		'1x' => [
			'--bg-url' => "url('#url#')",
		],
		'2x' => [
			'--bg-url-2x' => "url('#url#')",
		],
	];
	protected const STYLES_URL_MARKER = '#url#';
	protected const STYLES_URL_REGEXP = '/url\([\'"]?([^\'")]+)[\'")]?\)/i';

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

			if (isset($resultList[$pos]))
			{
				// update in content
				if ($resultList[$pos]->getTagName() !== 'IMG')
				{
					$styles = DOM\StyleInliner::getStyle($resultList[$pos]);
					if (isset($styles['--bg-url']))
					{
						$styles['background-image'] = '';
					}
					if (!isset($styles['background']))
					{
						if ($id)
						{
							$stylesChanged = false;
							$resultList[$pos]->setAttribute('data-fileid', $id);
							foreach (self::STYLES_VARIABLES_WITH_FILES['1x'] as $var => $pattern)
							{
								if (isset($styles[$var]))
								{
									$fileArray = \CFile::GetFileArray($id);
									$styles[$var] = str_replace(
										self::STYLES_URL_MARKER,
										$fileArray['SRC'],
										$pattern
									);
									$stylesChanged = true;
								}
							}

							if ($id2x)
							{
								$resultList[$pos]->setAttribute('data-fileid2x', $id2x);
								foreach (self::STYLES_VARIABLES_WITH_FILES['2x'] as $var => $pattern)
								{
									if (isset($styles[$var]))
									{
										$fileArray = \CFile::GetFileArray($id2x);
										$styles[$var] = str_replace(
											self::STYLES_URL_MARKER,
											$fileArray['SRC'],
											$pattern
										);
										$stylesChanged = true;
									}
								}
							}

							if (!$stylesChanged && false)
							{
								$classList = $resultList[$pos]->getAttribute('class');
								if (!stripos($classList, 'g-bg-image'))
								{
									$classList .= ' g-bg-image';
								}
								$resultList[$pos]->setAttribute('class', $classList);
								$fileArray1x = \CFile::GetFileArray($id2x);
								$src1x = $fileArray1x['SRC'];
								$styles['--bg-url'] = "url('{$src1x}');";
								$fileArray2x = \CFile::GetFileArray($id2x);
								$src2x = $fileArray2x['SRC'];
								$styles['--bg-url-2x'] = "url('{$src2x}');";
								$stylesChanged = true;
							}

							if ($stylesChanged)
							{
								DOM\StyleInliner::setStyle($resultList[$pos], $styles);
							}
						}
						else
						{
							foreach (['fileid', 'fileid2x'] as $dataCode)
							{
								$attribute = 'data-' . $dataCode;
								$oldId = $resultList[$pos]->getAttribute($attribute);
								if ($oldId > 0)
								{
									File::deleteFromBlock(
										$block->getId(),
										$oldId
									);
								}
							}
						}
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
		$data = [];
		$resultList = Node\Style::getNodesBySelector($block, $selector);

		foreach ($resultList as $pos => $res)
		{
			if ($res->getTagName() !== 'IMG')
			{
				$isLazy =
					$res->getAttribute('data-lazy-styleimg')
					&& $res->getAttribute('data-lazy-styleimg') === 'Y'
				;
				$nodeData =
					$isLazy
						? self::getNodeDataLazy($res)
						: self::getNodeData($res)
				;
				if ($nodeData)
				{
					$files = File::getFilesFromBlock($block->getId());
					if (!in_array($nodeData['id'], $files))
					{
						continue;
					}
					if ($nodeData['id2x'] && !in_array($nodeData['id'], $files))
					{
						unset($nodeData['id2x'], $nodeData['src2x'], $nodeData['lazyOrigSrc2x']);
					}

					$data[$pos] = $nodeData;
				}
			}
		}

		return $data;
	}

	protected static function getNodeData(DOM\Node $node): ?array
	{
		$data = null;

		$styles = DOM\StyleInliner::getStyle($node);
		if (
			(!isset($styles['background']) || $styles['background'] === '')
			&& (!isset($styles['background-image']) || $styles['background-image'] === '')
		)
		{
			$fileId = (int)$node->getAttribute('data-fileid');
			if ($fileId)
			{
				$data = [];
				$data['id'] = $fileId;
				$data['src'] = self::getSrcFromStyles($styles, '1x');

				$fileId2x = (int)$node->getAttribute('data-fileid2x');
				if ($fileId2x)
				{
					$data['id2x'] = $fileId2x;
					$data['src2x'] = self::getSrcFromStyles($styles, '2x');
				}
			}
		}

		return $data;
	}

	protected static function getNodeDataLazy(DOM\Node $node): ?array
	{
		$data = null;

		$styles = $node->getAttribute('data-style');
		if ($styles)
		{
			$stylesConverted = [];
			foreach (explode(';', $styles) as $style)
			{
				// can has mode then one ':', can't use explode!
				$separator = strpos( $style, ':');
				$key = substr($style, 0, $separator);
				$val = substr($style, $separator + 1);
				$stylesConverted[$key] = trim($val);
			}

			$fileId = (int)$node->getAttribute('data-fileid');
			if ($fileId)
			{
				$data = ['isLazy' => 'Y'];

				$data['id'] = $fileId;
				$data['lazyOrigSrc'] = self::getSrcFromStyles($stylesConverted, '1x');

				$fileId2x = (int)$node->getAttribute('data-fileid2x');
				if ($fileId2x)
				{
					$data['id2x'] = $fileId2x;
					$data['lazyOrigSrc2x'] = self::getSrcFromStyles($stylesConverted, '2x');
				}
			}
		}

		return $data;
	}

	/**
	 * Find src in inline styles variables
	 * @param array $styles
	 * @param string $resolution
	 * @return string|null
	 */
	protected static function getSrcFromStyles(array $styles, string $resolution): ?string
	{
		if (array_key_exists($resolution, self::STYLES_VARIABLES_WITH_FILES))
		{
			foreach (self::STYLES_VARIABLES_WITH_FILES[$resolution] as $var => $pattern)
			{
				$matches = [];
				if (
					$styles[$var]
					&& preg_match(self::STYLES_URL_REGEXP, $styles[$var], $matches)
					&& $matches[1]
				)
				{
					return Manager::getUrlFromFile($matches[1]);
				}
			}
		}

		return null;
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