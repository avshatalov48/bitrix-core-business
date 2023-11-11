<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Landing;

class EditAttributesAction extends BaseAction
{
	protected const JS_COMMAND = 'editAttributes';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$selector = $this->params['selector'];
		$position = $this->params['position'];
		$attribute = $this->params['attribute'];
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];

		// todo: what if wrapper node?
		if ($selector && isset($value) && $attribute)
		{
			$landingModeBefore = Landing::getEditMode();
			Landing::setEditMode();
			$block->setAttributes([
				$selector => [
					$position => [
						$attribute => $value,
					],
				],
			]);
			Landing::setEditMode($landingModeBefore);

			return $block->getError()->isEmpty() && $block->save();
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
			'selector' => $params['selector'] ?: '',
			'position' => $params['position'] ?? -1,
			'attribute' => $params['attribute'] ?? '',
			'isWrapper' => $params['isWrapper'] ?? false,
			'valueBefore' => $params['valueBefore'],
			'valueAfter' => $params['valueAfter'],
		];
	}

	public function isNeedPush(): bool
	{
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
