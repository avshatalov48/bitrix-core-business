<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\History\ActionFactory;
use Bitrix\Landing\Node;
use Bitrix\Main\Web\Json;

class MultiplyAction extends BaseAction
{
	protected const JS_COMMAND = 'multiply';

	public function execute(bool $undo = true): bool
	{
		foreach ($this->params as $param)
		{
			if (is_array($param) && $param['ACTION'] && $param['ACTION_PARAMS'])
			{
				$action = ActionFactory::getAction($param['ACTION'], $undo);
				if ($action)
				{
					$action->setParams($param['ACTION_PARAMS'], true);
					$action->execute($undo);
				}
			}
		}

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
		$command = parent::getJsCommand($undo);
		$command['params'] = [];

		foreach ($this->params as $param)
		{
			if (is_array($param) && $param['ACTION'] && $param['ACTION_PARAMS'])
			{
				$action = ActionFactory::getAction($param['ACTION'], $undo);
				if ($action)
				{
					$action->setParams($param['ACTION_PARAMS'], true);
					$command['params'][] = $action->getJsCommand($undo);
				}
			}
		}

		return $command;
	}
}
