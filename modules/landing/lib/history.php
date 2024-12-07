<?php

namespace Bitrix\Landing;

use Bitrix\Landing\History\ActionFactory;
use Bitrix\Landing\History\Action\BaseAction;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Internals\HistoryTable;
use Bitrix\Landing\Internals\HistoryStepTable;
use Bitrix\Landing\Internals\LandingTable;
use Bitrix\Main\Type\DateTime;

/**
 * Work with History
 */
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
	protected static bool $multiplyMode = false;

	/**
	 * ID of multiply actions group, by default - ID of first action in group
	 * @var int|null
	 */
	protected static ?int $multiplyId = null;

	// todo: $multiplyId and $multiplyStep - is no static. But need getInstance method and like a singletone style
	/**
	 * Because multiply step is one step, need increase step just once and save value
	 * @var int|null
	 */
	protected static ?int $multiplyStep = null;

	protected int $entityId;
	protected string $entityType = self::ENTITY_TYPE_LANDING;
	/**
	 * ID of stepTable row - save for optimisation. If null - row not exists (new item)
	 * @var int|null
	 */
	protected ?int $stepRowId = null;
	/**
	 * List of steps, grouped by multiply
	 * @var array
	 */
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
		self::$multiplyMode = true;
	}

	public static function unsetMultiplyMode(): void
	{
		self::$multiplyMode = false;
	}

	/**
	 * Check enable or disable global history
	 * @return bool
	 */
	public static function isActive(): bool
	{
		return self::$isActive;
	}

	/**
	 * @param int $entityId
	 * @param string $entityType - one of constants AVAILABLE_TYPES
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

		if ($this->step > $this->getStackCount())
		{
			$this->saveStep($this->getStackCount());
		}
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
			->exec()
		;
		$step = 1;
		$multyId = null;
		while ($row = $res->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			if (!is_array($row['ACTION_PARAMS']))
			{
				$this->fixBrokenStep($step, $row['ID']);
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
						],
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
	 * @param int $step number of broken step
	 * @param int $id ID of broken History row
	 * @return bool
	 */
	protected function fixBrokenStep(int $step, int $id): bool
	{
		$resDelete = HistoryTable::delete($id);
		if ($resDelete->isSuccess())
		{
			$currentStep = $this->loadStep();
			if ($step > $currentStep)
			{
				return true;
			}

			return $this->saveStep(max(--$currentStep, 0));
		}

		return false;
	}

	/**
	 * Delete all steps before chosen.
	 * @param int $step number of step in stack
	 * @return bool
	 */
	protected function clearBefore(int $step): bool
	{
		if (!isset($this->stack[$step]))
		{
			return false;
		}

		// if first step - can't delete nothing
		if ($this->step <= 1)
		{
			return true;
		}

		// delete only before current step
		if ($step >= $this->step)
		{
			$step = $this->step - 1;
		}

		for ($i = 1; $i <= $step; $i++)
		{
			if (!$this->deleteStep(1))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete chosen step and all after them.
	 * @param int $step number of step in stack
	 * @return bool
	 */
	protected function clearAfter(int $step): bool
	{
		if ($step >= $this->getStackCount())
		{
			return true;
		}

		// if last step - can't delete nothing
		$stackCount = $this->getStackCount();
		if ($this->step >= $stackCount)
		{
			return true;
		}

		// delete only after current step
		if ($step <= $this->step)
		{
			$step = $this->step + 1;
		}

		for ($i = $step; $i <= $stackCount; $i++)
		{
			if (!$this->deleteStep($step))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Clear steps after current. Can't redo now
	 * @return bool
	 */
	public function clearFuture(): bool
	{
		return $this->clearAfter($this->step);
	}

	/**
	 * Clear all history
	 * @return bool
	 */
	public function clear(): bool
	{
		$count = $this->getStackCount();
		for ($i = 0; $i < $count; $i++)
		{
			if (!$this->deleteStep(1))
			{
				return false;
			}
		}

		$this->stack = [];

		return true;
	}

	/**
	 * Remove one step by step number, with run action delete processing and save new step
	 * @param int $step
	 * @return bool
	 */
	protected function deleteStep(int $step): bool
	{
		if (!isset($this->stack[$step]))
		{
			return false;
		}

		$item = $this->stack[$step];
		$action = $this->getActionForStep($item['STEP'], false);
		if (!$action || !$action->delete())
		{
			return false;
		}

		if (isset($item['MULTIPLY']) && is_array($item['MULTIPLY']) && !empty($item['MULTIPLY']))
		{
			foreach ($item['MULTIPLY'] as $multyId)
			{
				$resDelete = HistoryTable::delete($multyId);
				if (!$resDelete->isSuccess())
				{
					return false;
				}
			}
		}
		else
		{
			$resDelete = HistoryTable::delete($item['ID']);
			if (!$resDelete->isSuccess())
			{
				return false;
			}
		}

		// update stack and step
		unset($this->stack[$step]);
		$this->resetStackSteps();
		if ($step <= $this->step)
		{
			return $this->saveStep($this->step - 1);
		}

		return true;
	}

	/**
	 * Re calculate steps after change stack
	 * @return void
	 */
	protected function resetStackSteps(): void
	{
		$newStack = [];
		$step = 1;
		foreach ($this->stack as $item)
		{
			$item['STEP'] = $step;
			$newStack[$step] = $item;
			$step++;
		}

		// todo: what about multiply step?

		$this->stack = $newStack;
	}


	/**
	 * Remove history records older X days. And save new step.
	 * @param int $days
	 * @return bool
	 */
	public function clearOld(int $days): bool
	{
		if ($days > 0)
		{
			$dateEnd = new DateTime();
			$dateEnd->add('-' . $days . ' days');

			$deleteBeforeStep = 0;
			foreach ($this->stack as $stackItem)
			{
				$dateCurrent = DateTime::createFromUserTime($stackItem['DATE_CREATE']);
				if ($dateEnd < $dateCurrent)
				{
					break;
				}
				$deleteBeforeStep = $stackItem['STEP'];
			}

			return $this->clearBefore($deleteBeforeStep);
		}

		return false;
	}

	public function getStackCount(): int
	{
		return count($this->stack);
	}

	/**
	 * Get step from table
	 * @return int
	 */
	protected function loadStep(): int
	{
		$this->step = 0;

		$step = HistoryStepTable::query()
			->addSelect('ID')
			->addSelect('STEP')
			->where('ENTITY_ID', '=', $this->entityId)
			->where('ENTITY_TYPE', '=', $this->entityType)
			->exec()
			->fetch()
		;
		// todo: del other entities row if exists
		if ($step)
		{
			$this->stepRowId = $step['ID'];
			$this->step = $step['STEP'];
		}
		else
		{
			$this->migrateStep();
		}

		return $this->step;
	}

	/**
	 * Add exists or add new step row
	 * @param int $step
	 * @return bool
	 */
	protected function saveStep(int $step): bool
	{
		$this->step = $step;

		if ($this->stepRowId)
		{
			$res = HistoryStepTable::update($this->stepRowId, ['STEP' => $step]);
		}
		else
		{
			$res = HistoryStepTable::add([
				'ENTITY_ID' => $this->entityId,
				'ENTITY_TYPE' => $this->entityType,
				'STEP' => $step,
			]);
		}

		if ($res->isSuccess())
		{
			$this->stepRowId = $res->getId();
			$this->step = $step;

			return true;
		}

		return false;
	}

	/**
	 * Move steps from old tables to new entity
	 * When will be updated all clients - can delete this method
	 * @return void
	 */
	private function migrateStep(): void
	{
		$oldStep = null;

		if ($this->entityType === self::ENTITY_TYPE_LANDING)
		{
			if (!array_key_exists('HISTORY_STEP', LandingTable::getMap()))
			{
				return;
			}

			$landing = LandingTable::query()
				->addSelect('HISTORY_STEP')
				->where('ID', '=', $this->entityId)
				->exec()
				->fetch()
			;
			$oldStep = $landing ? $landing['HISTORY_STEP'] : null;
		}

		if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
		{
			if (!array_key_exists('HISTORY_STEP_DESIGNER', BlockTable::getMap()))
			{
				return;
			}

			$block = BlockTable::query()
				->addSelect('HISTORY_STEP_DESIGNER')
				->where('ID', '=', $this->entityId)
				->exec()
				->fetch()
			;
			$oldStep = $block ? $block['HISTORY_STEP_DESIGNER'] : null;
		}

		$isNewStepExists = HistoryStepTable::query()
			->addSelect('ID')
			->addSelect('STEP')
			->where('ENTITY_ID', '=', $this->entityId)
			->where('ENTITY_TYPE', '=', $this->entityType)
			->exec()
			->fetch()
		;

		if ($oldStep && !$isNewStepExists)
		{
			$this->saveStep((int)$oldStep);
		}
	}

	/**
	 * Return stack of js commands for actions
	 * @return array
	 */
	public function getJsStack(): array
	{
		$result = [];
		foreach ($this->stack as $step => $stackItem)
		{
			$actionClass = ActionFactory::getActionClass($stackItem['ACTION']);
			$result[] = [
				'id' => $stackItem['ID'],
				'current' => $step === $this->step,
				'command' => (is_callable([$actionClass, 'getJsCommandName']))
					? call_user_func([$actionClass, 'getJsCommandName'])
					: ''
				,
				'entityId' => $this->entityId,
				'entityType' => $this->entityType,
			];
		}

		return $result;
	}

	/**
	 * Get current step
	 * @return int
	 */
	public function getStep(): int
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
			if (!$this->clearFuture())
			{
				return false;
			}
		}

		$nextStep =
			(self::$multiplyMode && self::$multiplyStep !== null)
				? self::$multiplyStep
				: $this->step + 1
		;

		if (!$this->saveStep($nextStep))
		{
			return false;
		}

		self::$multiplyStep = $nextStep;
		// todo: drop $multiplyStep after last element (when set multiply mode off)

		if (self::$multiplyMode && self::$multiplyId !== null)
		{
			$fields['MULTIPLY_ID'] = self::$multiplyId;
		}

		$resAdd = HistoryTable::add($fields);

		// save MULTIPLY_ID for first element in group
		if (self::$multiplyMode && self::$multiplyId === null)
		{
			self::$multiplyId = $resAdd->getId();
			HistoryTable::update(self::$multiplyId, [
				'MULTIPLY_ID' => self::$multiplyId,
			]);
		}

		return $resAdd->isSuccess();
	}

	public function undo(): bool
	{
		if ($this->canUndo())
		{
			self::deactivate();
			$action = $this->getActionForStep($this->step, true);
			if ($action && $action->execute())
			{
				return $this->saveStep($this->step - 1);
			}
		}

		return false;
	}

	protected function canUndo(): bool
	{
		return
			$this->step > 0
			&& $this->getStackCount() > 0
			&& $this->step <= $this->getStackCount()
		;
	}

	public function redo(): bool
	{
		if ($this->canRedo())
		{
			self::deactivate();
			$action = $this->getActionForStep($this->step + 1, false);
			if ($action && $action->execute(false))
			{
				return $this->saveStep($this->step + 1);
			}
		}

		return false;
	}

	protected function canRedo(): bool
	{
		return
			$this->step >= 0
			&& $this->getStackCount() > 0
			&& $this->step < $this->getStackCount()
		;
	}

	/**
	 * Get params for JS command for frontend changes
	 * @param bool $undo
	 * @return array
	 */
	public function getJsCommand(bool $undo = true): array
	{
		$action = $this->getActionForStep(
			$undo ? $this->step : ($this->step + 1),
			$undo
		);

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
		if (!isset($this->stack[$step]))
		{
			return null;
		}

		$stepItem = $this->stack[$step];
		$stepId = $stepItem['ID'];
		$direction = ActionFactory::getDirectionName($undo);
		if (isset($this->actions[$stepId][$direction]))
		{
			return $this->actions[$stepId][$direction];
		}

		$params = $stepItem['ACTION_PARAMS'];
		if ($this->entityType === self::ENTITY_TYPE_LANDING)
		{
			$params['lid'] = $this->entityId;
		}
		if ($this->entityType === self::ENTITY_TYPE_DESIGNER_BLOCK)
		{
			$params['blockId'] = $this->entityId;
		}

		$action = ActionFactory::getAction($stepItem['ACTION'], $undo);
		if (!$action)
		{
			return null;
		}

		$action->setParams($params, true);
		$this->actions[$stepId][$direction] = $action;

		return $action;
	}


}