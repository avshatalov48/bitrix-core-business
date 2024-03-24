<?php

namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing;
use Bitrix\Landing\PublicActionResult;

/**
 * Work with history
 */
class History
{
	public static function getForLanding(int $lid): PublicActionResult
	{
		$result = new PublicActionResult();
		$histories = [];

		$historyMain = new Landing\History($lid, Landing\History::ENTITY_TYPE_LANDING);
		$histories[$lid] = [
			'stack' => $historyMain->getJsStack(),
			'step' => $historyMain->getStep(),
		];

		$landing = Landing\Landing::createInstance($lid);
		$isMultiArea = false;
		foreach ($landing->getAreas() as $areaLid)
		{
			$isMultiArea = true;
			$historyArea = new Landing\History($areaLid, Landing\History::ENTITY_TYPE_LANDING);

			$histories[$areaLid] = [
				'stack' => $historyArea->getJsStack(),
				'step' => $historyArea->getStep(),
			];
		}

		if ($isMultiArea)
		{
			// Find max step of all areas
			$maxStepId = 0;
			foreach ($histories as $history)
			{
				foreach ($history['stack'] as $item)
				{
					if ($item['current'])
					{
						if ($item['id'] > $maxStepId)
						{
							$maxStepId = $item['id'];
						}
					}
				}
			}

			// Make and sort complex multi area stack.
			$multiAreaStack = [];
			$multiAreaStep = 0;
			foreach ($histories as $history)
			{
				foreach ($history['stack'] as $item)
				{
					$multiAreaStack[$item['id']] = $item;

					// math new step
					if ($item['id'] <= $maxStepId)
					{
						$multiAreaStep++;
					}
				}
			}
			ksort($multiAreaStack);

			$result->setResult([
				'stack' => array_values($multiAreaStack),
				'step' => $multiAreaStep,
			]);
		}

		// Just single landing history
		else
		{
			$result->setResult($histories[$lid]);
		}

		return $result;
	}

	public static function getForDesignerBlock(int $blockId): PublicActionResult
	{
		$result = new PublicActionResult();
		$history = new Landing\History($blockId, Landing\History::ENTITY_TYPE_DESIGNER_BLOCK);

		$result->setResult([
			'stack' => $history->getJsStack(),
			'step' => $history->getStep(),
		]);

		return $result;
	}

	public static function undoLanding(int $lid): PublicActionResult
	{
		return self::undoForEntity(Landing\History::ENTITY_TYPE_LANDING, $lid);
	}

	public static function redoLanding(int $lid): PublicActionResult
	{
		return self::redoForEntity(Landing\History::ENTITY_TYPE_LANDING, $lid);
	}

	public static function undoDesignerBlock(int $blockId): PublicActionResult
	{
		return self::undoForEntity(Landing\History::ENTITY_TYPE_DESIGNER_BLOCK, $blockId);
	}

	public static function redoDesignerBlock(int $blockId): PublicActionResult
	{
		return self::redoForEntity(Landing\History::ENTITY_TYPE_DESIGNER_BLOCK, $blockId);
	}

	public static function pushDesignerBlock(int $blockId, string $action, array $data): PublicActionResult
	{
		return self::pushForEntity(Landing\History::ENTITY_TYPE_DESIGNER_BLOCK, $blockId, $action, $data);
	}

	public static function clearDesignerBlock(int $blockId): PublicActionResult
	{
		return self::clearForEntity(Landing\History::ENTITY_TYPE_DESIGNER_BLOCK, $blockId);
	}

	public static function clearFutureForLanding(int $landingId): PublicActionResult
	{
		return self::clearFutureForEntity(Landing\History::ENTITY_TYPE_LANDING, $landingId);
	}

	protected static function undoForEntity(string $entityType, int $entityId): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		Landing\Landing::setEditMode(true);

		if (in_array($entityType, Landing\History::AVAILABLE_TYPES))
		{
			$history = new Landing\History($entityId, $entityType);
			$command = $history->getJsCommand();
			if ($history->undo())
			{
				$result->setResult($command);
			}
			else
			{
				$error->addError(
					'HISTORY_UNDO_ERROR',
					"History operation Undo fail for entity {$entityType}_{$entityId}"
				);
				$result->setError($error);
			}
		}
		else
		{
			$error->addError(
				'HISTORY_WRONG_TYPE',
				'Wrong history entity type'
			);
			$result->setError($error);
		}

		return $result;
	}

	protected static function redoForEntity(string $entityType, int $entityId): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		Landing\Landing::setEditMode(true);

		if (in_array($entityType, Landing\History::AVAILABLE_TYPES))
		{
			$history = new Landing\History($entityId, $entityType);
			$command = $history->getJsCommand(false);
			if ($history->redo())
			{
				$result->setResult($command);
			}
			else
			{
				$error->addError(
					'HISTORY_REDO_ERROR',
					"History operation Redo fail for entity {$entityType}_{$entityId}"
				);
				$result->setError($error);
			}
		}
		else
		{
			$error->addError(
				'HISTORY_WRONG_TYPE',
				'Wrong history entity type'
			);
			$result->setError($error);
		}

		return $result;
	}

	protected static function pushForEntity(
		string $entityType,
		int $entityId,
		string $action,
		array $data
	): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		Landing\Landing::setEditMode(true);

		if (in_array($entityType, Landing\History::AVAILABLE_TYPES))
		{
			$history = new Landing\History($entityId, $entityType);
			if ($history->push($action, $data))
			{
				$result->setResult(true);
			}
			else
			{
				$error->addError(
					'HISTORY_PUSH_ERROR',
					"History operation Push fail for entity {$entityType}_{$entityId}"
				);
				$result->setError($error);
			}
		}
		else
		{
			$error->addError(
				'HISTORY_WRONG_TYPE',
				'Wrong history entity type'
			);
			$result->setError($error);
		}

		return $result;
	}

	protected static function clearForEntity(string $entityType, int $entityId): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		Landing\Landing::setEditMode(true);

		if (in_array($entityType, Landing\History::AVAILABLE_TYPES))
		{
			$history = new Landing\History($entityId, $entityType);
			if ($history->clear())
			{
				$result->setResult(true);
			}
			else
			{
				$error->addError(
					'HISTORY_CLEAR_ERROR',
					"History operation Clear fail for entity {$entityType}_{$entityId}"
				);
				$result->setError($error);
			}
		}
		else
		{
			$error->addError(
				'HISTORY_WRONG_TYPE',
				'Wrong history entity type'
			);
			$result->setError($error);
		}

		return $result;
	}

	protected static function clearFutureForEntity(string $entityType, int $entityId): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		Landing\Landing::setEditMode(true);

		if (in_array($entityType, Landing\History::AVAILABLE_TYPES))
		{
			$history = new Landing\History($entityId, $entityType);
			if ($history->clearFuture())
			{
				$result->setResult(true);
			}
			else
			{
				$error->addError(
					'HISTORY_CLEAR_FUTURE_ERROR',
					"History operation Clear future fail for entity {$entityType}_{$entityId}"
				);
				$result->setError($error);
			}
		}
		else
		{
			$error->addError(
				'HISTORY_WRONG_TYPE',
				'Wrong history entity type'
			);
			$result->setError($error);
		}

		return $result;
	}
}
