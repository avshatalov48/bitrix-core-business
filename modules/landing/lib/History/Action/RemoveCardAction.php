<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;

class RemoveCardAction extends AddCardAction
{
	protected const JS_COMMAND = 'removeCard';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$block->removeCard($this->params['selector'], $this->params['position']);

		return $block->save();
	}
}
