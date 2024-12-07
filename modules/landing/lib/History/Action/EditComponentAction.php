<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Node;
use Bitrix\Main\Web\Json;

class EditComponentAction extends BaseAction
{
	protected const JS_COMMAND = 'editComponent';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$selector = $this->params['selector'] ?: '';
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];

		if ($selector)
		{
			$manifest = $block->getManifest();
			if (isset($manifest['nodes'][$selector]['extra']))
			{
				$doc = $block->getDom();
				Node\Component::saveNode($block, $selector, $value);
				$block->saveContent($doc->saveHTML());

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
			'selector' => $params['selector'] ?: '',
			'lid' => $block->getLandingId(),
			'valueAfter' => $params['valueAfter'] ?: [],
			'valueBefore' => $params['valueBefore'] ?: [],
		];
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
