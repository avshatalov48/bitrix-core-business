<?php
namespace Bitrix\Landing;

use Bitrix\Landing\History\ActionFactory;
use Bitrix\Landing\History\Action\BaseAction;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Internals\HistoryTable;
use Bitrix\Landing\Internals\LandingTable;
use Bitrix\Main\Type\DateTime;

class History
{
	/**
	 * Entity type landing.
	 */
	public const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Entity type designer block.
	 */
	public const ENTITY_TYPE_DESIGNER_BLOCK = 'D';

	// todo: max count
	// todo: lifetime

	public const AVAILABLE_TYPES = [
		self::ENTITY_TYPE_LANDING,
		self::ENTITY_TYPE_DESIGNER_BLOCK,
	];

	/**
	 * Activity flag
	 * @var bool
	 */
	protected static bool $isActive = false;

	/**
	 * If set multiply mode some actions will connected and changed as one step
	 * @var bool
	 */
	protected static bool $isMultiply = false;

	/**
	 * ID of multiply actions group, by default - ID of first action in group
	 * @var int|null
	 */
	protected static ?int $multiplyId = null;

	/**
	 * Because multiply step is one step, need increase step just once and save value
	 * @var int|null
	 */
	protected static ?int $multiplyStep = null;

	protected int $entityId;
	protected string $entityType = self::ENTITY_TYPE_LANDING;
	protected array $stack = [];
	protected int $step = 0;
	protected array $actions = [];

	/**
	 * Enable history for all
	 * @return void
	 */
	public static function activate(): void
	{
		self::$isActive = true;
	}

	/**
	 * Disable history for all
	 * @return void
	 */
	public static function deactivate(): void
	{
		self::$isActive = false;
	}

	public static function setMultiplyMode(): void
	{
		self::$isMultiply = true;
	}

	public static function unsetMultiplyMode(): void
	{
		self::$isMultiply = false;
	}

	/**
	 * Check enable or disable global history
	 * @return bool
	 */
	public static function isActive() :bool
	{
		return self::$isActive;
	}

	/**
	 * @param int $entityId
	 * @param string $entityType - one of constants ENTITY_TYPE_
	 */
	public function __construct(int $entityId, string $entityType)
	{
		if (!in_array($entityType, self::AVAILABLE_TYPES, true))
		{
			// todo :err or null
			return;
		}

		$this->entityId = $entityId;
		$this->entityType = $entityType;

		$this->loadStack();
		$this->loadStep();
	}

	protected function loadStack(): void
	{
		// todo: maybe cache
		$this->stack = [];

		$res = HistoryTable::query()
			->addSelect('*')
			->where('ENTITY_TYPE', '=', $this->entityType)
			->where('ENTITY_ID', '=', $this->entityId)
			->setOrder(['ID' => 'ASC'])
			->exec();
		$step = 1;
		$multyId = null;
		while ($row = $res->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			if (!is_array($row['ACTION_PARAMS']))
			{
				$this->fixBroken($row['ID']);
				continue;
			}

			$row['STEP'] = $step;
			$row['ENTITY_ID'] = (int)$row['ENTITY_ID'];
			$row['MULTIPLY_ID'] = (int)$row['MULTIPLY_ID'];

			if ($row['MULTIPLY_ID'])
			{
				if ($multyId && $multyId !== $row['MULTIPLY_ID'])
				{
					$multyId = null;
				}

				if (!$multyId)
				{
					// first multiply step
					$row['ACTION_PARAMS'] = [
						[
							'ACTION' => $row['ACTION'],
							'ACTION_PARAMS' => $row['ACTION_PARAMS'],
						]
					];
					$row['ACTION'] = ActionFactory::MULTIPLY_ACTION_NAME;
					$multyId = $row['MULTIPLY_ID'];
					$row['MULTIPLY'] = [$row['MULTIPLY_ID']];
					unset($row['MULTIPLY_ID']);
					$this->stack[$step] = $row;
				}
				else
				{
					$this->stack[$step - 1]['ACTION_PARAMS'][] = [
						'ACTION' => $row['ACTION'],
						'ACTION_PARAMS' => $row['ACTION_PARAMS'],
					];
					$this->stack[$step - 1]['MULTIPLY'][] = $row['ID'];
				}
			}
			else
			{
				$multyId = null;
				$this->stack[$step] = $row;
			}

			$step++;
		}
	}

	/**
	 * For some reasons history row can be broken.
	 * For consistency need remove row and decrease step.
	 * @param int $id
	 * @return bool
	 */
	protected function fixBroken(int $id): bool
	{
		$resDelete = HistoryTable::delete($id);
		if ($resDelete->isSuccess())
		{
			if ($this->entityType === self::ENTITY_TYPE_LANDING)
			{
				$landing = LandingTable::query()
					->addSelect('HISTORY_STEP')
					->where('ID', '=', $this->entityId)
					->exec()
					->fetch()
				;
				$currentStep = $landing['HISTORY_STEP'] ?? 0;
				$newStep = max(--$currentStep, 0);
				$resUpdate = LandingTable::update(
					$this->entityId,
					['HISTORY_STEP' => $newStep]
				);

				return $resUpdate->isSuccess();
			}

			if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
			{
				$block = BlockTable::query()
					->addSelect('HISTORY_STEP_DESIGNER')
					->where('ID', '=', $this->entityId)
					->exec()
					->fetch()
				;
				$currentStep = $block['HISTORY_STEP_DESIGNER'] ?? 0;
				$newStep = max(--$currentStep, 0);
				$resUpdate = BlockTable::update(
					$this->entityId,
					['HISTORY_STEP_DESIGNER' => $newStep]
				);

				return $resUpdate->isSuccess();
			}
		}

		return false;
	}

	protected function loadStep(): void
	{
		$this->step = 0;

		if ($this->entityType === self::ENTITY_TYPE_LANDING)
		{
			$landing = LandingTable::query()
				->addSelect('HISTORY_STEP')
				->where('ID', '=', $this->entityId)
				->exec()
				->fetch()
			;
			$this->step = $landing['HISTORY_STEP'] ?? 0;
		}

		if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
		{
			$block = BlockTable::query()
				->addSelect('HISTORY_STEP_DESIGNER')
				->where('ID', '=', $this->entityId)
				->exec()
				->fetch()
			;
			$this->step = $block['HISTORY_STEP_DESIGNER'] ?? 0;
		}
	}

	public function getStackCount(): int
	{
		return count($this->stack);
	}

	/**
	 * Get current step
	 * @return int
	 */
	public function getCurrentStep(): int
	{
		return $this->step;
	}

	public function push(string $actionName, array $params): bool
	{
		$actionName = strtoupper($actionName);

		$action = ActionFactory::getAction($actionName);
		if (!$action)
		{
			return false;
			// todo: or err
		}
		$action->setParams($params);

		$fields = [
			'ENTITY_TYPE' => $this->entityType,
			'ENTITY_ID' => $this->entityId,
			'ACTION' => $actionName,
			'ACTION_PARAMS' => $action->getParams(),
			'CREATED_BY_ID' => Manager::getUserId() ?: 1,
			'DATE_CREATE' => new DateTime,
		];

		// check duplicates
		if (
			!empty($this->stack[$this->step])
			&& ActionFactory::compareSteps($this->stack[$this->step], $fields)
		)
		{
			return false;
		}

		if (!$action->isNeedPush())
		{
			return true;
		}

		$stackCount = $this->getStackCount();
		if ($this->step < $stackCount)
		{
			for ($i = $this->step + 1; $i <= $stackCount; $i++)
			{
				$this->deleteStackItem($this->stack[$i]);
			}
		}

		// todo: check landing exists, check success

		$newStep =
			(self::$isMultiply && self::$multiplyStep !== null)
				? self::$multiplyStep
				: ++$this->step
		;
		$resUpdate = false;
		// todo: refactor
		if ($this->entityType === self::ENTITY_TYPE_LANDING)
		{
			$resUpdate = LandingTable::update($this->entityId, [
				'HISTORY_STEP' => ($newStep),
			]);
		}
		if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
		{
			$resUpdate = BlockTable::update($this->entityId, [
				'HISTORY_STEP_DESIGNER' => ($newStep),
			]);
		}
		$this->step = $newStep;
		self::$multiplyStep = $newStep;

		if ($resUpdate && $resUpdate->isSuccess())
		{
			if (self::$isMultiply && self::$multiplyId !== null)
			{
				$fields['MULTIPLY_ID'] = self::$multiplyId;
			}

			$resAdd = HistoryTable::add($fields);

			if (self::$isMultiply && self::$multiplyId === null)
			{
				self::$multiplyId = $resAdd->getId();
				HistoryTable::update(self::$multiplyId, [
					'MULTIPLY_ID' => self::$multiplyId,
				]);
			}

			return $resAdd->isSuccess();
		}

		return false;

		// todo: max limit
	}

	/**
	 * Clear row(s) from table, and do action delete processing
	 * @param array $item
	 * @return bool
	 */
	protected function deleteStackItem(array $item): bool
	{
		$action = $this->getActionForStep($item['STEP'], true);
		if (!$action || !$action->delete())
		{
			return false;
		}

		if (isset($item['MULTIPLY']) && is_array($item['MULTIPLY']) && !empty($item['MULTIPLY']))
		{
			foreach ($item['MULTIPLY'] as $multyId)
			{
				$resDelete = HistoryTable::delete($multyId);
			}
		}
		else
		{
			$resDelete = HistoryTable::delete($item['ID']);
		}

		// todo: del from stack

		if ($resDelete->isSuccess())
		{
			unset($this->stack[$item['STEP']]);

			return true;
		}

		return false;
	}

	public function undo(): bool
	{
		// todo :canundo?

		self::deactivate();
		$action = $this->getActionForStep($this->step, true);
		if ($action && $action->execute())
		{
			// todo: what if return false?
			if ($this->entityType === self::ENTITY_TYPE_LANDING)
			{
				$resUpdate = LandingTable::update($this->entityId, [
					'HISTORY_STEP' => (--$this->step)
				]);

				return $resUpdate->isSuccess();
			}

			if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
			{
				$resUpdate = BlockTable::update($this->entityId, [
					'HISTORY_STEP_DESIGNER' => (--$this->step)
				]);

				return $resUpdate->isSuccess();
			}
		}

		return false;
	}


	public function redo(): bool
	{
		// todo :canundo?

		self::deactivate();
		$action = $this->getActionForStep($this->step, false);
		if ($action && $action->execute(false))
		{
			if ($this->entityType === self::ENTITY_TYPE_LANDING)
			{
				$resUpdate = LandingTable::update($this->entityId, [
					'HISTORY_STEP' => (++$this->step)
				]);

				return $resUpdate->isSuccess();
			}

			if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
			{
				$resUpdate = BlockTable::update($this->entityId, [
					'HISTORY_STEP_DESIGNER' => (++$this->step)
				]);

				return $resUpdate->isSuccess();
			}
		}

		return false;
	}

	/**
	 * Get params for JS command for frontend changes
	 * @param bool $undo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		$action = $this->getActionForStep($this->step, $undo);

   		return $action ? $action->getJsCommand($undo) : [];
	}

	/**
	 * Create and save in stack action object by step number
	 * @param int $step
	 * @param bool $undo
	 * @return BaseAction|null
	 */
	protected function getActionForStep(int $step, bool $undo): ?BaseAction
	{
		$step = $undo ? $step : ++$step;
		if (isset($this->actions[$step]))
		{
			return $this->actions[$step];
		}

		$current = $this->stack[$step];
		$params = $current['ACTION_PARAMS'];
		if ($this->entityType === self::ENTITY_TYPE_LANDING)
		{
			$params['lid'] = $this->entityId;
		}
		if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
		{
			$params['blockId'] = $this->entityId;
		}

		$action = ActionFactory::getAction($current['ACTION'], $undo);
		if (!$action)
		{
			return null;
		}

		$action->setParams($params, true);
		$this->actions[$step] = $action;

		return $action;
	}

	public function clear(): bool
	{
		// save stack because steps will be deleted in process
		$stackSave = $this->stack;
		foreach ($stackSave as $stackItem)
		{
			if (!$this->deleteStackItem($stackItem))
			{
				return false;
			}

		}

		unset($stackSave);

		$this->step = 0;
		if ($this->entityType === self::ENTITY_TYPE_LANDING)
		{
			$resUpdate = LandingTable::update($this->entityId, [
				'HISTORY_STEP' => 0,
			]);

			return $resUpdate->isSuccess();
		}

		if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
		{
			$resUpdate = BlockTable::update($this->entityId, [
				'HISTORY_STEP_DESIGNER' => 0,
			]);

			return $resUpdate->isSuccess();
		}

		return false;
	}
}