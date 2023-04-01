<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Node;
use Bitrix\Main\Web\Json;

class ChangeAnchorAction extends BaseAction
{
	// now it is not used as separate js command, just as multy
	protected const JS_COMMAND = 'changeAnchor';

	public function execute(bool $undo = true): bool
	{
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];

		if ($this->params['block'] && $value)
		{
			$block = new Block((int)$this->params['block']);
			$block->setAnchor($value);

			return $block->save();
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		$params['block'] = $params['block']->getId();

		return $params;
	}

	/**
	 * @param bool $undo - if false - redo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		$params = parent::getJsCommand($undo);

		$params['params']['value'] =
			$undo
				? $params['params']['valueBefore']
				: $params['params']['valueAfter'];

		unset(
			$params['params']['valueAfter'],
			$params['params']['valueBefore'],
		);

		return $params;
	}

	/**
	 * Check if params duplicated with previously step
	 * @param array $oldParams
	 * @param array $newParams
	 * @return bool
	 */
	public static function compareParams(array $oldParams, array $newParams): bool
	{
		unset($oldParams['valueBefore'], $newParams['valueBefore']);

		return $oldParams === $newParams;
	}
}
