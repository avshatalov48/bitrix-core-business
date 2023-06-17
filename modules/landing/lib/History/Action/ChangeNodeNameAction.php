<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Landing;
use Bitrix\Main\Web\Json;

class ChangeNodeNameAction extends BaseAction
{
	protected const JS_COMMAND = 'cnangeNodeName';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];
		if ($value)
		{
			if ($block->changeNodeName($value))
			{
				return $block->save();
			}
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		/**
		 * @var $block Block
		 */
		$block = $params['block'];

		return [
			'block' => $block->getId(),
			'lid' => $block->getLandingId(),
			'valueBefore' => $params['valueBefore'] ?? [],
			'valueAfter' => $params['valueAfter'] ?? [],
		];
	}

	/**
	 * @param bool $undo - if false - redo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		$params = parent::getJsCommand($undo);
		$value = $undo ? $params['params']['valueBefore'] : $params['params']['valueAfter'];

		foreach ($value as $sel => $valueItem)
		{
			foreach ($valueItem as $pos => $tag)
			{
				$selector = $sel . '@' . $pos;
				$params['params']['selector'] = $selector;
				$params['params']['value'] = $tag;
				break;
			}
			break;
		}

		unset(
			$params['params']['valueAfter'],
			$params['params']['valueBefore'],
		);

		return $params;
	}
}
