<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\File;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Landing;

class RemoveBlockAction extends AddBlockAction
{
	protected const JS_COMMAND = 'removeBlock';

	public function execute(bool $undo = true): bool
	{
		$landing = Landing::createInstance($this->params['lid']);
		if (
			$landing->exist()
			&& $landing->markDeletedBlock((int)$this->params['block'], true)
		)
		{
			$landing->resortBlocks();

			return true;
		}

		return false;
	}

	public function delete(): bool
	{
		if (!isset($this->params['block']))
		{
			return false;
		}

		$blockId = (int)$this->params['block'];
		$query = BlockTable::query()
			->setSelect(['ID', 'ACCESS'])
			->where('ID', '=', $blockId)
			->where('DELETED', '=', 'Y')
			->exec()
		;
		$block = $query->fetch();
		if (
			$block
			&& $block['ACCESS'] === Block::ACCESS_X
		)
		{
			BlockTable::delete($blockId);
			File::deleteFromBlock($blockId);
		}

		return parent::delete();
	}
}