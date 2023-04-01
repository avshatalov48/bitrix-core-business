<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;

class UpdateContentAction extends BaseAction
{
	protected const JS_COMMAND = 'updateContent';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		if ($block->exist())
		{
			$content = $undo ? $this->params['contentBefore'] : $this->params['contentAfter'];
			$block->saveContent($content, $this->params['designed']);

			return $block->save();
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		return [
			'block' => $params['block'],
			'lid' => $params['contentAfter'],
			'contentAfter' => $params['contentAfter'] ?: '',
			'contentBefore' => $params['contentBefore'] ?: '',
			'designed' => (bool)$params['designed'],
		];
	}

	public function isNeedPush(): bool
	{
		return
			parent::isNeedPush()
			&& $this->params['contentBefore'] !== $this->params['contentAfter'];
	}

	/**
	 * @param bool $undo - if false - redo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		$params = parent::getJsCommand($undo);

		$params['params']['content'] =
			$undo
				? $params['params']['contentBefore']
				: $params['params']['contentAfter']
		;

		unset(
			$params['params']['contentAfter'],
			$params['params']['contentBefore'],
			$params['params']['designed'],
		);

		return $params;
	}
}
