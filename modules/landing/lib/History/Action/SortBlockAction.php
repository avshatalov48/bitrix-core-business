<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Landing;

class SortBlockAction extends BaseAction
{
	protected const JS_COMMAND = 'sortBlock';

	public function execute(bool $undo = true): bool
	{
		$up = (bool)$this->params['up'];
		$blockId = (int)$this->params['block'];
		$landing = Landing::createInstance($this->params['lid']);
		if ($landing->exist())
		{
			return ($up === $undo)
				? $landing->downBlock($blockId)
				: $landing->upBlock($blockId)
			;
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		return [
			'block' => (int)$params['block'],
			'lid' => (int)$params['lid'],
			'up' => (bool)$params['up'],
		];
	}

	/**
	 * @param bool $undo - if false - redo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		$params = parent::getJsCommand($undo);
		$params['params']['direction'] =
			($params['params']['up'] === $undo)
				? 'moveDown'
				: 'moveUp'
		;
		unset(
			$params['params']['lid'],
			$params['params']['up'],
		);

		return $params;
	}
}
