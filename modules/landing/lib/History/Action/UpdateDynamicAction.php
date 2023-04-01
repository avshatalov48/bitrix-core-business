<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Node;
use Bitrix\Main\Web\Json;

class UpdateDynamicAction extends BaseAction
{
	// now it is not used as separate js command, just as multy
	protected const JS_COMMAND = 'updateDynamic';

	public function execute(bool $undo = true): bool
	{
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];

		if ($this->params['block'] && is_array($value))
		{
			$block = new Block((int)$this->params['block']);
			$block->saveDynamicParams($value);

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

		$params['params']['dynamicParams'] = $params['params']['value'];
		$params['params']['dynamicState'] = [];
		foreach (array_keys($params['params']['dynamicParams']) as $selector)
		{
			$params['params']['dynamicState'][$selector] = true;
		}
		if (!$params['params']['dynamicState']['wrapper'])
		{
			!$params['params']['dynamicState']['wrapper'] = false;
		}

		return $params;
	}
}
