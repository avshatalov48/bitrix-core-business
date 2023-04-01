<?php

namespace Bitrix\Landing\History\Action;

class RemoveNodeAction extends BaseAction
{
	protected const JS_COMMAND = 'removeNode';

	public function execute(bool $undo = true): bool
	{
		return true;
	}

	public static function enrichParams(array $params): array
	{
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
				: $params['params']['valueAfter']
		;

		unset(
			$params['params']['valueAfter'],
			$params['params']['valueBefore'],
		);

		return $params;
	}
}
