<?php

namespace Bitrix\Landing\History\Action;

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
}