<?php

namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing;
use Bitrix\Landing\PublicActionResult;

class History
{
	public static function getForLanding(int $lid): PublicActionResult
	{
		$result = new PublicActionResult();
		$history = new Landing\History($lid, Landing\History::ENTITY_TYPE_LANDING);
		$result->setResult([
			'stackCount' => $history->getStackCount(),
			'step' => $history->getCurrentStep(),
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
}
