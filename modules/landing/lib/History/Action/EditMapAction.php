<?php

namespace Bitrix\Landing\History\Action;

use Bitrix\Landing\Block;
use Bitrix\Landing\Node;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;

class EditMapAction extends BaseAction
{
	protected const JS_COMMAND = 'editMap';

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
				Node\Map::saveNode($block, $selector, [
					$position => $value
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

		return [
			'block' => $block->getId(),
			'selector' => $params['selector'] ?: '',
			'position' => $params['position'] ?: 0,
			'lid' => $block->getLandingId(),
			'valueAfter' => $params['valueAfter'] ?: '',
			'valueBefore' => $params['valueBefore'] ?: '',
		];
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
		$params['params']['value'] = Encoding::convertEncoding($params['params']['value'], SITE_CHARSET, 'UTF-8');
		$params['params']['value'] = Json::decode($params['params']['value']);

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
		unset($oldParams['valueBefore'], $newParams['valueBefore']);

		return $oldParams === $newParams;
	}
}
