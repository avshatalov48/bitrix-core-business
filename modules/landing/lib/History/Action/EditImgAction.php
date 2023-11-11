<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Node;
use Bitrix\Main\Web\Json;

class EditImgAction extends BaseAction
{
	protected const JS_COMMAND = 'editImage';

	public function execute(bool $undo = true): bool
	{
		$block = new Block((int)$this->params['block']);
		$selector = $this->params['selector'] ?: '';
		$position = (int)($this->params['position'] ?: 0);
		$value = $undo ? $this->params['valueBefore'] : $this->params['valueAfter'];

		if ($selector)
		{
			$doc = $block->getDom();
			$resultList = $doc->querySelectorAll($selector);
			if (isset($resultList[$position]))
			{
				Node\Img::saveNode($block, $selector, [
					$position => $value,
				]);

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

		unset($params['valueAfter']['type']);
		if (!$params['src2x'])
		{
			unset($params['src2x']);
		}
		if (!$params['src2x'])
		{
			unset($params['src2x']);
		}

		return [
			'block' => $block->getId(),
			'selector' => $params['selector'] ?: '',
			'position' => $params['position'] ?: 0,
			'lid' => $block->getLandingId(),
			'valueAfter' => $params['valueAfter'] ?: '',
			'valueBefore' => $params['valueBefore'] ?: '',
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

		$params['params']['selector'] .= '@' . $params['params']['position'];
		$params['params']['value'] =
			$undo
				? $params['params']['valueBefore']
				: $params['params']['valueAfter']
		;
		if (isset($params['params']['value']['url']))
		{
			$params['params']['value']['url'] = Json::decode($params['params']['value']['url']);
		}

		unset(
			$params['params']['valueAfter'],
			$params['params']['valueBefore'],
			$params['params']['position'],
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
		// todo: or compare just file ids?
		unset(
			$oldParams['valueBefore'], $newParams['valueBefore'],
			$oldParams['type'], $newParams['type']
		);

		return $oldParams === $newParams;
	}
}
