<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Landing;

class AddBlockAction extends BaseAction
{
	protected const JS_COMMAND = 'addBlock';

	public function execute(bool $undo = true): bool
	{
		$blockId = (int)$this->params['block'];
		$landing = Landing::createInstance($this->params['lid']);
		if (
			$landing->exist()
			&& $landing->markDeletedBlock($blockId, false)
		)
		{
			foreach ($landing->getBlocks() as $id => $block)
			{
				if ($id === $blockId)
				{
					$block->setSort($block->getSort() - 1);
					break;
				}
			}

			$landing->resortBlocks();

			return true;
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		/**
		 * @var $block Block
		 */
		$block = $params['block'];
		$blockSort = $block->getSort();
		$code = $block->getCode();
		$landing = Landing::createInstance($block->getLandingId());
		$previousId = 0;
		$previousSort = 0;
		foreach ($landing->getBlocks() as $bid => $b)
		{
			$currentSort = $b->getSort();
			if ($currentSort >= $previousSort && $currentSort < $blockSort)
			{
				$previousSort = $currentSort;
				$previousId = $bid;
			}
		}

		return [
			'block' => $block->getId(),
			'selector' => '#' . Block::getAnchor($block->getId()),
			'lid' => $block->getLandingId(),
			'code' => $code,
			'currentBlock' => $previousId,
			'insertBefore' => false,
		];
	}
}
