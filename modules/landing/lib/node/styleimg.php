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
					$styles = StyleInliner::getStyle($resultList[$pos]);
					if (!isset($styles['background']) && !isset($styles['background-image']))
					{
						if ($id)
						{
							$resultList[$pos]->setAttribute('data-fileid', $id);
							if ($id2x)
							{
								$resultList[$pos]->setAttribute('data-fileid2x', $id2x);
								// todo: save src in data too
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

								// $resultList[$pos]->removeAttribute($attribute);
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
						if ($fileId2x = $res->getAttribute('data-fileid2x'))
						{
							// todo: check if file exists by ID
							// todo: check $files = File::getFilesFromBlock($block->getId());
							$data[$pos]['id2x'] = $fileId2x;
						}
					}
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