<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Main\Web\DOM;

class AddCardAction extends BaseAction
{
	protected const JS_COMMAND = 'addCard';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$block->cloneCard($this->params['selector'], $this->params['position'] - 1, $this->params['content']);

		return $block->save();
	}

	public static function enrichParams(array $params): array
	{
		/**
		 * @var $block Block
		 */
		$block = $params['block'];
		$selector = $params['selector'] ?: '';
		$position = (int)$params['position'] ?: 0;
		$content = '';
		if ($selector)
		{
			$doc = new DOM\Document();
			$doc->loadHTML($block->getContent());
			$resultList = $doc->querySelectorAll($selector);
			if (isset($resultList[$position]))
			{
				$content = $resultList[$position]->getOuterHTML();
			}
		}

		return [
			'block' => $block->getId(),
			'selector' => $selector,
			'lid' => $block->getLandingId(),
			'position' => $position,
			'content' => $content,
		];
	}
}
