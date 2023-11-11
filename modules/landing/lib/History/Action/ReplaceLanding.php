<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\File;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;

/**
 * Replace all blocks in landing by template
 */
class ReplaceLanding extends BaseAction
{
	protected const JS_COMMAND = 'replaceLanding';

	public function execute(bool $undo = true): bool
	{
		$landing = Landing::createInstance($this->params['lid']);
		if ($landing->exist())
		{
			foreach ($landing->getBlocks() as $block)
			{
				$accessBefore = $block->getAccess();
				$block->setAccess(Block::ACCESS_X);

				$landing->markDeletedBlock($block->getId(), true);

				$block->setAccess($accessBefore);
			}

			// return blocks
			$blocksToUndelete = $undo ? $this->params['blocksBefore'] : $this->params['blocksAfter'];
			foreach ($blocksToUndelete as $blockId)
			{
				$block = new Block($blockId);
				$accessBefore = $block->getAccess();
				$block->setAccess(Block::ACCESS_X);
				$landing->addBlockToCollection($block);

				$landing->markDeletedBlock($blockId, false);

				$block->setAccess($accessBefore);
			}

			// return additional fields
			$fields = $undo ? $this->params['additionalFieldsBefore'] : $this->params['additionalFieldsAfter'];
			Landing::saveAdditionalFields($this->params['lid'], $fields);

			if (
				Manager::isAutoPublicationEnabled()
				&& $landing->getError()->isEmpty()
			)
			{
				$landing->publication();
			}

			return true;
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		return [
			'lid' => (int)$params['lid'],
			'template' => $params['template'] ?? '',
			'blocksBefore' => $params['blocksBefore'] ?? [],
			'blocksAfter' => $params['blocksAfter'] ?? [],
			'additionalFieldsBefore' => $params['additionalFieldsBefore'] ?? [],
			'additionalFieldsAfter' => $params['additionalFieldsAfter'] ?? [],
		];
	}

	public function delete(): bool
	{
		if (!isset($this->params['blocksBefore']))
		{
			return false;
		}

		$blocks = $this->params['blocksBefore'];

		if (!empty($blocks))
		{
			$query = BlockTable::query()
				->setSelect(['ID', 'ACCESS'])
				->whereIn('ID', $blocks)
				->where('DELETED', '=', 'Y')
				->exec()
			;
			while ($block = $query->fetch())
			{
				if ($block['ACCESS'] === Block::ACCESS_X)
				{
					$blockId = (int)$block['ID'];
					BlockTable::delete($blockId);
					File::deleteFromBlock($blockId);
				}
			}
		}

		return parent::delete();
	}
}
