<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use \Bitrix\Main\Web\DOM;

class EditStyleAction extends BaseAction
{
	protected const JS_COMMAND = 'updateStyle';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$selector = $this->params['selector'];
		$position = $this->params['position'];
		$isWrapper = $this->params['isWrapper'];
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];

		if ($selector)
		{
			if ($position >= 0 && !$isWrapper)
			{
				$selector = $selector . '@' . $position;
			}
			$data = [
				$selector => [
					'classList' => explode(' ', $value['className']),
					'style' => $value['style'],
					'affect' => $this->params['affect'],
				],
			];

			return $block->setClasses($data) && $block->save();
		}

		return false;
	}

	public static function enrichParams(array $params): array
	{
		/**
		 * @var $block Block
		 */
		$block = $params['block'];

		$getValue = static function($content) {
			$doc = new DOM\Document();
			$doc->loadHTML($content);
			$children = $doc->getChildNodesArray();
			$node = array_pop($children);

			return $node
				? [
					'className' => $node->getClassName() ?: '',
					'style' => DOM\StyleInliner::getStyle($node, true),
					'styleString' => $node->getAttribute('style') ?: '',
				]
				: [];
		};

		return [
			'block' => $block->getId(),
			'selector' => $params['selector'] ?: '',
			'isWrapper' => $params['isWrapper'] ?? false,
			'position' => $params['position'] ?? -1,
			'affect' => $params['affect'] ?: [],
			'lid' => $block->getLandingId(),
			'valueBefore' => $getValue($params['contentBefore']),
			'valueAfter' => $getValue($params['contentAfter']),
		];
	}

	public function isNeedPush(): bool
	{
		// todo: move to another actions
		return
			parent::isNeedPush()
			&& $this->params['valueBefore'] !== $this->params['valueAfter'];
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
		$params['params']['value']['style'] = $params['params']['value']['styleString'];

		unset(
			$params['params']['valueAfter'],
			$params['params']['valueBefore'],
			$params['params']['value']['styleString'],
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
